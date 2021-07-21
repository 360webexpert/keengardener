<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Helper\Request;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\Phrase;

/**
 * Sage Pay Pi API
 */
class Pi implements PaymentOperations
{
    /** @var \Ebizmarts\SagePaySuite\Model\Api\Reporting */
    private $reportingApi;

    /** @var Data */
    private $suiteHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\PIRest */
    private $piRestApi;

    /**
     * Pi constructor.
     * @param Data $suiteHelper
     * @param \Ebizmarts\SagePaySuite\Model\Api\PIRest $piRestApi
     * @param \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi
     */
    public function __construct(
        Data $suiteHelper,
        PIRest $piRestApi,
        Reporting $reportingApi
    ) {
        $this->suiteHelper         = $suiteHelper;
        $this->piRestApi           = $piRestApi;
        $this->reportingApi        = $reportingApi;
    }

    public function captureDeferredTransaction($vpsTxId, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $result = null;

        $vpsTxId = $this->suiteHelper->clearTransactionId($vpsTxId);
        $transaction = $this->reportingApi->getTransactionDetailsByVpstxid($vpsTxId, $order->getStoreId());

        if ($this->validateTxStateId($transaction)) {
            throw new ApiException(__('Cannot capture deferred transaction, transaction state is invalid.'));
        }
        $txStateId = (int)$transaction->txstateid;
        if ($txStateId === PaymentOperations::DEFERRED_AWAITING_RELEASE) {
            $result = $this->piRestApi->release($vpsTxId, $amount);
        } else {
            if ($txStateId === PaymentOperations::SUCCESSFULLY_AUTHORISED) {
                $data                 = [];
                $data['VendorTxCode'] = $this->suiteHelper->generateVendorTxCode('', Config::ACTION_REPEAT_PI);
                $data['Description']  = 'REPEAT deferred transaction from Magento.';
                $data['Currency']     = (string)$transaction->currency;
                $data['Amount']       = $amount * 100;
                $result               = $this->repeatTransaction($vpsTxId, $data, $order, Config::ACTION_REPEAT_PI);
            }
        }

        return $result;
    }

    public function repeatTransaction($vpstxid, $quote_data, \Magento\Sales\Api\Data\OrderInterface $order, $paymentAction = Config::ACTION_REPEAT)
    {
        return $this->piRestApi->repeat(
            $quote_data['VendorTxCode'],
            $vpstxid,
            $quote_data['Currency'],
            $quote_data['Amount'],
            $quote_data['Description']
        );
    }

    public function authorizeTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        throw new \Exception("not implented.");
    }

    public function refundTransaction($vpstxid, $amount, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        throw new \Exception("not implented.");
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function validateTxStateId($transaction)
    {
        return !property_exists($transaction, "txstateid");
    }
}
