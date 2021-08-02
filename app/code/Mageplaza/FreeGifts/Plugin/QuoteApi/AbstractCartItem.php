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

namespace Mageplaza\FreeGifts\Plugin\QuoteApi;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Mageplaza\FreeGifts\Api\Data\FreeGiftItemInterface as FreeGiftItem;
use Mageplaza\FreeGifts\Api\Data\FreeGiftItemInterfaceFactory;
use Mageplaza\FreeGifts\Api\ProductGiftFactory;
use Mageplaza\FreeGifts\Api\ProductGiftInterface;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AbstractCartItem
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
abstract class AbstractCartItem
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var FreeGiftItemInterfaceFactory
     */
    protected $_freeGiftItem;

    /**
     * @var ProductGiftInterface
     */
    protected $_productGift;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * AbstractCartItem constructor.
     *
     * @param HelperRule $helperRule
     * @param ItemFactory $itemFactory
     * @param FreeGiftItemInterfaceFactory $freeGiftItem
     * @param ProductGiftInterface $productGift
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        HelperRule $helperRule,
        ItemFactory $itemFactory,
        FreeGiftItemInterfaceFactory $freeGiftItem,
        ProductGiftInterface $productGift,
        QuoteFactory $quoteFactory
    ) {
        $this->_helperRule   = $helperRule;
        $this->_itemFactory  = $itemFactory;
        $this->_freeGiftItem = $freeGiftItem;
        $this->_productGift  = $productGift;
        $this->_quoteFactory = $quoteFactory;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helperRule->getHelperData()->isEnabled();
    }

    /**
     * @param array $data
     *
     * @return FreeGiftItem
     */
    public function setFreeGiftItem($data)
    {
        $freeGiftItem = $this->_freeGiftItem->create();

        return $freeGiftItem->setIsFreeGift($data[FreeGiftItem::IS_FREE_GIFT])
            ->setRuleId($data[FreeGiftItem::RULE_ID])
            ->setFreeGiftMessage($data[FreeGiftItem::FREE_GIFT_MESSAGE])
            ->setAllowNotice($data[FreeGiftItem::ALLOW_NOTICE]);
    }

    /**
     * @param CartItemInterface[] $result
     *
     * @return CartItemInterface[] Array of items.
     */
    public function getList($result)
    {
        if (!$this->isEnabled()) {
            return $result;
        }

        $data        = [];
        $itemFactory = $this->_itemFactory->create();
        foreach ($result as $item) {
            $quoteItem = $itemFactory->load($item->getItemId());
            if ($ruleId = (int)$quoteItem->getData(HelperRule::QUOTE_RULE_ID)) {
                $data = [
                    FreeGiftItem::IS_FREE_GIFT      => true,
                    FreeGiftItem::RULE_ID           => $ruleId,
                    FreeGiftItem::FREE_GIFT_MESSAGE => $this->_helperRule->getRuleById($ruleId)->getNoticeContent(),
                    FreeGiftItem::ALLOW_NOTICE      => $this->_helperRule->getRuleById($ruleId)->getAllowNotice()
                ];
            }
            $extAttr = $item->getExtensionAttributes();
            if ($extAttr !== null && count($data)) {
                $extAttr->setMpFreeGifts($this->setFreeGiftItem($data));
            }
        }

        return $result;
    }

    /**
     * @param CartItemInterface $result
     * @param CartItemInterface $cartItem
     *
     * @return CartItemInterface
     * @SuppressWarnings("Unused")
     */
    public function save($result, $cartItem)
    {
        if (!$this->isEnabled() ||
            !$cartItem->getExtensionAttributes() ||
            !$cartItem->getExtensionAttributes()->getMpFreeGiftsAdd()) {
            return $result;
        }

        $addGifts = $cartItem->getExtensionAttributes()->getMpFreeGiftsAdd();
        if (!is_array($addGifts) || count($addGifts) === 0) {
            return $result;
        }

        $giftResponses = [];
        $quote         = $this->_quoteFactory->create()->loadActive($result->getQuoteId());

        foreach ($addGifts as $gift) {
            if (!$gift->getQuoteId()) {
                $gift->setQuoteId($quote->getId());
            }

            $addGiftResult   = $this->_productGift->setCollectTotals(false)->addGift($gift);
            $giftResponses[] = $addGiftResult;
        }

        if ($result->getExtensionAttributes() && count($giftResponses)) {
            $quote->getShippingAddress()->unsetData('cached_items_all');
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            $result->getExtensionAttributes()->setMpFreeGiftsResponse($giftResponses);
        }

        return $result;
    }
}
