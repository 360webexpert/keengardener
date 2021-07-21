<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Order;

use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\OrderRepository;

class SyncFromApi extends \Magento\Backend\App\AbstractAction
{

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $reportingApi;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Fraud
     */
    private $fraudHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $suiteHelper;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
     */
    private $transactionRepository;

    /**
     * SyncFromApi constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi
     * @param OrderRepository $orderRepository
     * @param Logger $suiteLogger
     * @param \Ebizmarts\SagePaySuite\Helper\Fraud $fraudHelper
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Ebizmarts\SagePaySuite\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Helper\Fraud $fraudHelper,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
    ) {

        parent::__construct($context);
        $this->reportingApi          = $reportingApi;
        $this->orderRepository       = $orderRepository;
        $this->suiteLogger           = $suiteLogger;
        $this->fraudHelper           = $fraudHelper;
        $this->suiteHelper           = $suiteHelper;
        $this->transactionRepository = $transactionRepository;
    }

    public function execute()
    {
        try {
            //get order id
            $orderId = $this->getRequest()->getParam("order_id");

            if (!empty($orderId)) {
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
            } else {
                throw new ValidatorException(__('Unable to sync from API: Invalid order id.'));
            }

            $transactionIdDirty = $payment->getLastTransId();

            $transactionId = $this->suiteHelper->clearTransactionId($transactionIdDirty);

            if ($transactionId != null) {
                $transactionDetails = $this->reportingApi
                    ->getTransactionDetailsByVpstxid($transactionId, $order->getStoreId());
            } else {
                $vendorTxCode = $payment->getAdditionalInformation("vendorTxCode");
                $transactionDetails = $this->reportingApi
                    ->getTransactionDetailsByVendorTxCode($vendorTxCode, $order->getStoreId());
            }

            if ($this->issetTransactionDetails($transactionDetails)) {
                $payment->setLastTransId((string)$transactionDetails->vpstxid);
                $payment->setAdditionalInformation('vendorTxCode', (string)$transactionDetails->vendortxcode);
                $payment->setAdditionalInformation('statusDetail', (string)$transactionDetails->status);

                if (isset($transactionDetails->securitykey)){
                    $payment->setAdditionalInformation('securityKey', (string)$transactionDetails->securitykey);
                }
                
                if (isset($transactionDetails->threedresult)) {
                    $payment->setAdditionalInformation('threeDStatus', (string)$transactionDetails->threedresult);
                }
                $payment->save();
            }

            //update fraud status
            if (!empty($payment->getLastTransId())) {
                $transaction = $this->transactionRepository
                                ->getByTransactionId($payment->getLastTransId(), $payment->getId(), $order->getId());
                if ($transaction !== false && $this->isFraudNotChecked($transaction)) {
                    $this->fraudHelper->processFraudInformation($transaction, $payment);
                }
            }

            $this->messageManager->addSuccess(__('Successfully synced from Opayo\'s API'));
        } catch (ApiException $apiException) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $apiException->getTraceAsString(), [__METHOD__, __LINE__]);
            $this->messageManager->addError(__($this->cleanExceptionString($apiException)));
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
            $this->messageManager->addError(__('Something went wrong: %1', $e->getMessage()));
        }

        if (!empty($order)) {
            $this->_redirect($this->_backendUrl->getUrl('sales/order/view/', ['order_id' => $order->getId()]));
        } else {
            $this->_redirect($this->_backendUrl->getUrl('sales/order/index/', []));
        }
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function isFraudNotChecked($transaction)
    {
        return (bool)$transaction->getSagepaysuiteFraudCheck() === false;
    }

    /**
     * @return bool
     */
    public function issetTransactionDetails($transactionDetails)
    {
        return isset($transactionDetails->vpstxid) && isset($transactionDetails->vendortxcode) && isset($transactionDetails->status);
    }

    /**
     * This function replaces the < and > symbols, this is necessary for the exception to be showed correctly
     * to the customer at the backend.
     * @param $apiException
     * @return string|string[]
     */
    public function cleanExceptionString($apiException)
    {
        return str_replace(">", "", str_replace("<","", $apiException->getUserMessage()));
    }
}
