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
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CartAddComplete
 * @package Mageplaza\FreeGifts\Observer
 */
class CartAddComplete extends AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Request $request */
        $request = $observer->getEvent()->getDataByKey('request');
        if ($this->isEnabled()) {
            if ($request->getParam('mpfreegifts')) {
                $rules = $request->getParam('mpfreegifts');
                $this->setQuote($this->_checkoutSession->getQuote());

                foreach ($rules as $ruleId => $gifts) {
                    $limit                  = $this->_helperRule->getRuleById($ruleId)->getMaxGift();
                    $this->_cartAddComplete = true;
                    $this->addGift($this->prepareGifts($gifts), $ruleId, $limit);
                }

                $this->_quote->save();
                $this->_quote->setTotalsCollectedFlag(false);
                $this->_quote->collectTotals();
            }

            if ($this->notification->getRuleCartPage() && $this->helperData->getAllowReminder()) {
                $this->messageManager->addComplexSuccessMessage(
                    'addReminderMessage', ['message' => $this->helperData->getNotifyMessage()]);
            }

        }
    }

    /**
     * @param array $gifts
     *
     * @return array
     */
    public function prepareGifts($gifts)
    {
        foreach ($gifts as $giftId => $gift) {
            $gifts[$giftId]['id'] = $giftId;
            if (isset($gift['options'])) {
                $options = $this->convertStringQuery($gift['options']);
                if (isset($options['super_attribute'])) {
                    $gifts[$giftId]['super_attribute'] = $options['super_attribute'];
                }
                if (isset($options['options'])) {
                    $gifts[$giftId]['options'] = $options['options'];
                }
            }
        }

        return $gifts;
    }

    /**
     * @param string $options
     *
     * @return array
     */
    public function convertStringQuery($options)
    {
        $this->_zendUri->setQuery($options);

        return $this->_zendUri->getQueryAsArray();
    }
}
