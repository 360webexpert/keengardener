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

namespace Mageplaza\FreeGifts\Block\Cart;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Source\Apply;

/**
 * Class Notification
 * @package Mageplaza\FreeGifts\Block\Cart
 */
class Notification extends CheckoutCart
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::cart/notification.phtml';

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRuleCartPage()
    {
        $this->removeInvalidItem();
        $validItemRules = [];
        $validCartRules = $this->_helperRule->setExtraData(true)->setApply(Apply::CART)->getValidatedRules();
        $this->_registry->unregister('mpfreegifts_cart_rules');
        $this->_registry->unregister('mpfreegifts_item_rules');

        $quoteItems = $this->getQuoteVisibleItems();
        foreach ($quoteItems as $item) {
            $itemProduct = $this->_helperRule->getHelperGift()->getProductById($item->getData('product_id'));
            $rules = $this->_helperRule->setExtraData(true)->setApply(Apply::ITEM)
                ->setProduct($itemProduct)->getValidatedRules();
            foreach ($rules as $rule) {
                $validItemRules[$item->getItemId()][] = $rule;
            }
        }

        $this->_registry->register('mpfreegifts_cart_rules', $validCartRules);
        $this->_registry->register('mpfreegifts_item_rules', $validItemRules);

        return (bool)count($validCartRules) || count($validItemRules);
    }

    /**
     * @return mixed
     */
    public function allowHideNotify()
    {
        return $this->getHelperData()->getHideNotification();
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->_checkoutSession->getQuoteId();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getHelperData()->isEnabled();
    }

    /**
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function removeInvalidItem()
    {
        $quote = $this->_checkoutSession->getQuote();
        $quoteItems = $this->getQuoteVisibleItems();
        $validRuleIds = array_keys($this->_helperRule->setExtraData(false)->getAllValidRules());
        $saveQuote = false;

        foreach ($quoteItems as $quoteItem) {
            $itemRuleId = (int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID);
            if ($itemRuleId && !in_array($itemRuleId, $validRuleIds, true)) {
                $this->_giftItem->removeAndDelete($quoteItem);
                $saveQuote = true;
            }
        }

        if ($saveQuote) {
            $quote->save();
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
        }
    }

    /**
     * @return QuoteItem[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteVisibleItems()
    {
        return $this->_checkoutSession->getQuote()->getAllVisibleItems();
    }
}
