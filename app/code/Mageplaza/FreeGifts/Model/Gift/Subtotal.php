<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Model\Gift;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteValidator;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Rule as RuleModel;

/**
 * Class Subtotal
 * @package Mageplaza\FreeGifts\Model\Gift
 */
class Subtotal extends AbstractTotal
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * Sales data
     *
     * @var QuoteValidator
     */
    protected $_quoteValidator;

    /**
     * Subtotal constructor.
     *
     * @param QuoteValidator $quoteValidator
     * @param HelperRule $helperRule
     */
    public function __construct(
        QuoteValidator $quoteValidator,
        HelperRule $helperRule
    ) {
        $this->_helperRule = $helperRule;
        $this->_quoteValidator = $quoteValidator;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this|QuoteSubtotal
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        $virtualAmount = 0;
        $baseVirtualAmount = 0;
        $this->_setTotal($total);
        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        /** @var Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $this->_setAddress($address);
        $address->setTotalQty(0);

        $items = $shippingAssignment->getItems();
        /** @var AddressItem|Item $item */
        foreach ($items as $item) {
            if ($this->_initItem($address, $item) && $item->getQty() > 0) {
                if ($item->getProduct()->isVirtual()) {
                    $virtualAmount += $item->getRowTotal();
                    $baseVirtualAmount += $item->getBaseRowTotal();
                }
            } else {
                $this->_removeItem($address, $item);
            }
        }

        $total->setBaseVirtualAmount($baseVirtualAmount);
        $total->setVirtualAmount($virtualAmount);
        $this->_quoteValidator->validateQuoteAmount($quote, $total->getSubtotal());
        $this->_quoteValidator->validateQuoteAmount($quote, $total->getBaseSubtotal());
        $address->setSubtotal($total->getSubtotal());
        $address->setBaseSubtotal($total->getBaseSubtotal());

        return $this;
    }

    /**
     * Address item initialization
     *
     * @param Address $address
     * @param AddressItem|Item $item
     *
     * @return bool
     */
    protected function _initItem($address, $item)
    {
        $quoteItem = $item instanceof AddressItem
            ? $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId())
            : $item;
        $product = $quoteItem->getProduct();
        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        } elseif (!$product || !$product->isVisibleInCatalog()) {
            return false;
        }

        $giftPrice = $product->getPrice();
        /** @var RuleModel $giftRule */
        $giftRule = $item->getDataByKey('mpfreegifts_rule');
        if ($giftRule) {
            $gifts = $giftRule->getGiftArray();
            $giftId = $quoteItem->getParentItem()
                ? $quoteItem->getParentItem()->getProductId()
                : $quoteItem->getProductId();
            $giftPrice = $this->_helperRule->getHelperGift()->getGiftPrice(
                $gifts[$giftId]['discount'],
                (float)$gifts[$giftId]['gift_price'],
                $quoteItem->getProduct()->getFinalPrice()
            );
        }

        $quoteItem->setConvertedPrice(null);
        $originalPrice = $giftPrice;

        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $giftRule
                ? $giftPrice
                : $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                    $quoteItem->getParentItem()->getProduct(),
                    $quoteItem->getParentItem()->getQty(),
                    $product,
                    $quoteItem->getQty()
                );

            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
        }
        if (!$quoteItem->getParentItem()) {
            $finalPrice = $giftRule ? $giftPrice : $product->getFinalPrice($quoteItem->getQty());
            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }

        return true;
    }

    /**
     * @param AddressItem|Item $item
     * @param int $finalPrice
     * @param int $originalPrice
     *
     * @return $this
     */
    protected function _calculateRowTotal($item, $finalPrice, $originalPrice)
    {
        if (!$originalPrice) {
            $originalPrice = $finalPrice;
        }
        $item->setPrice($finalPrice)->setBaseOriginalPrice($originalPrice);
        $item->calcRowTotal();

        return $this;
    }

    /**
     * @param Address $address
     * @param AddressItem|Item $item
     *
     * @return $this
     */
    protected function _removeItem($address, $item)
    {
        $itemId = $item instanceof AddressItem ? $item->getQuoteItemId() : $item->getId();
        $address->removeItem($item->getId());
        if ($address->getQuote()) {
            $address->getQuote()->removeItem($itemId);
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Address\Total $total
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, AddressTotal $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $total->getSubtotal()
        ];
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return __('Subtotal');
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'subtotal';
    }
}
