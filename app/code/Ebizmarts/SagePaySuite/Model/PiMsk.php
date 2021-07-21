<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

class PiMsk implements \Ebizmarts\SagePaySuite\Api\PiMerchantInterface
{
    /** @var \Ebizmarts\SagePaySuite\Model\Api\PIRest */
    private $piRestApi;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\ResultInterface */
    private $result;

    /** @var \Ebizmarts\SagePaySuite\Model\Logger\Logger */
    private $log;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /** @var \Magento\Quote\Model\QuoteFactory */
    private $quoteFactory;

    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Api\PIRest $piRestApi,
        \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result,
        \Ebizmarts\SagePaySuite\Model\Logger\Logger $log,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->piRestApi = $piRestApi;
        $this->result    = $result;
        $this->log       = $log;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function getSessionKey(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        try {
            if (null === $quote) {
                $quote = $this->getDummyQuote();
            }

            $merchantSession = $this->piRestApi->generateMerchantKey($quote);

            $this->result->setSuccess(true);
            $this->result->setResponse($merchantSession->getMerchantSessionKey());
        } catch (\Ebizmarts\SagePaySuite\Model\Api\ApiException $apiException) {
            $this->result->setSuccess(false);
            $this->result->setErrorMessage(__($apiException->getUserMessage()));
            $this->log->logException($apiException, [__METHOD__, __LINE__]);
        } catch (\Exception $e) {
            $this->result->setSuccess(false);
            $this->result->setErrorMessage(__('Something went wrong while generating the merchant session key.'));
            $this->log->logException($e, [__METHOD__, __LINE__]);
        }

        return $this->result;
    }

    private function getDummyQuote()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        return $quote;
    }
}
