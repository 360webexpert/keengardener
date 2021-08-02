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

namespace Mageplaza\FreeGifts\Observer;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class QuoteRemoveItem
 * @package Mageplaza\FreeGifts\Observer
 */
class QuoteRemoveItem implements ObserverInterface
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * QuoteRemoveItem constructor.
     *
     * @param HelperRule $helperRule
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        HelperRule $helperRule,
        CheckoutSession $checkoutSession
    ) {
        $this->_helperRule = $helperRule;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperRule->getHelperData()->isEnabled()) {
            return;
        }

        /** @var QuoteItem $quoteItem */
        $quoteItem = $observer->getEvent()->getDataByKey('quote_item');

        $ruleId = (int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID);
        $saveDeleted = $this->_checkoutSession->getFreeGiftsDeleted();
        if ($ruleId) {
            $giftId = (int)$quoteItem->getData('product_id');
            $deleteGifts = $saveDeleted === null ? [] : $saveDeleted;
            $deleteGifts[$ruleId][] = $giftId;
            $this->_checkoutSession->setFreeGiftsDeleted($deleteGifts);
        }
    }
}
