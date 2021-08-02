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

namespace Mageplaza\FreeGifts\Plugin\Totals;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Subtotal;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Gift\Subtotal as GiftSubtotal;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class AroundCollectSubtotal
 * @package Mageplaza\FreeGifts\Plugin
 */
class AroundCollectSubtotal extends AbstractPlugin
{
    /**
     * @var GiftSubtotal
     */
    protected $_giftSubtotal;

    /**
     * AroundCollectSubtotal constructor.
     *
     * @param HelperRule $helperRule
     * @param GiftSubtotal $giftSubtotal
     */
    public function __construct(
        HelperRule $helperRule,
        GiftSubtotal $giftSubtotal
    ) {
        $this->_giftSubtotal = $giftSubtotal;
        parent::__construct($helperRule);
    }

    /**
     * @param Subtotal $subject
     * @param callable $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return GiftSubtotal
     * @SuppressWarnings("Unused")
     */
    public function aroundCollect(
        Subtotal $subject,
        callable $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $hasGift = false;
        $items = $shippingAssignment->getItems();
        foreach ($items as $item) {
            $quoteItem = $item instanceof AddressItem
                ? $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId())
                : $item;

            if ($ruleId = $quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
                $item->setData('mpfreegifts_rule', $this->_helperRule->getRuleById($ruleId));
                $hasGift = true;
            }
        }

        if ($hasGift && $this->isEnabled()) {
            return $this->_giftSubtotal->collect($quote, $shippingAssignment, $total);
        }

        return $proceed($quote, $shippingAssignment, $total);
    }
}
