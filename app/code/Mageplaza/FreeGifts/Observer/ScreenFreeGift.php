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
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class ScreenFreeGift
 * @package Mageplaza\FreeGifts\Observer
 */
class ScreenFreeGift extends AbstractObserver
{
    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function setExternalQuote($quote)
    {
        $this->setQuote($quote);

        return $this;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $this->setQuote($this->_checkoutSession->getQuote());
        $quoteItems = $this->_quote->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            if ((int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID) && $quoteItem->getQty() > 1) {
                $quoteItem->setQty(1);
            }
        }

        $this->freeGift();
    }
}
