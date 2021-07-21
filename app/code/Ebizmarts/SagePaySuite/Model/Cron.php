<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Helper\Fraud;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use \Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use \Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud as FraudModel;
use \Ebizmarts\SagePaySuite\Helper\Data;
use \Ebizmarts\SagePaySuite\Model\Api\Reporting;

class Cron
{
    const TIMED_OUT_TXSTATEID = 8;
    const TRANSACTION_NOT_FOUND = "0043";

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
         * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface;
     */
    private $transactionRepository;

    /**
     * @var Fraud
     */
    private $fraudHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud;
     */
    private $fraudModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Data
     */
    private $suiteHelper;

    /**
     * @var Reporting
     */
    private $reportingApi;

    /**
     * Cron constructor.
     * @param Logger $suiteLogger
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param CollectionFactory $orderCollectionFactory
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Fraud $fraudHelper
     * @param FraudModel $fraudModel
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Data $suiteHelper
     * @param Reporting $reportingApi
     */
    public function __construct(
        Logger $suiteLogger,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        ObjectManagerInterface $objectManager,
        Config $config,
        CollectionFactory $orderCollectionFactory,
        TransactionRepositoryInterface $transactionRepository,
        Fraud $fraudHelper,
        FraudModel $fraudModel,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        Data $suiteHelper,
        Reporting $reportingApi
    ) {
        $this->suiteLogger            = $suiteLogger;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->objectManager          = $objectManager;
        $this->config                 = $config;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transactionRepository  = $transactionRepository;
        $this->fraudHelper            = $fraudHelper;
        $this->fraudModel             = $fraudModel;
        $this->criteriaBuilder        = $criteriaBuilder;
        $this->filterBuilder          = $filterBuilder;
        $this->suiteHelper            = $suiteHelper;
        $this->reportingApi           = $reportingApi;
    }

    /**
     * Cancel Sage Pay orders in "pending payment" state after a period of time
     */
    public function cancelPendingPaymentOrders()
    {
        $orderIds = $this->fraudModel->getOrderIdsToCancel();

        if (!count($orderIds)) {
            return $this;
        }

        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => implode(',', $orderIds)])
            ->load();

        /** @var $_order Order */
        foreach ($orderCollection as $_order) {
            $orderId = $_order->getEntityId();

            try {
                /** @var Payment $payment */
                $payment = $_order->getPayment();
                if ($payment !== null) {
                    $transactionId = $this->suiteHelper->clearTransactionId($payment->getLastTransId());

                    $transactionDetails = $this->getTransactionDetails($transactionId, $_order, $payment);

                    if ((int)$transactionDetails->txstateid === self::TIMED_OUT_TXSTATEID) {
                        $_order->cancel()->save(); //@codingStandardsIgnoreLine
                        $this->logCancelledPayment($orderId);
                    }
                } else {
                    $this->logErrorPaymentNotFound($orderId);
                }
            } catch (ApiException $apiException) {
                $this->apiExceptionToLog($apiException, $orderId);
            } catch (\Exception $e) {
                $this->logGeneralException($orderId, $e);
            }
        }
        return $this;
    }

    /**
     * Check transaction fraud result and action based on result
     * @throws LocalizedException
     */
    public function checkFraud()
    {
        $transactions = $this->fraudModel->getShadowPaidPaymentTransactions();

        foreach ($transactions as $_transaction) {
            try {
                $transaction = $this->transactionRepository->get($_transaction["transaction_id"]);
                $logData = [];

                $payment = $this->orderPaymentRepository->get($transaction->getPaymentId());
                if ($payment === null) {
                    throw new LocalizedException(
                        __('Payment not found for this transaction.')
                    );
                }

                //process fraud information
                $logData = $this->fraudHelper->processFraudInformation($transaction, $payment);
            } catch (ApiException $apiException) {
                $logData["ERROR"] = $apiException->getUserMessage();
                $logData["Trace"] = $apiException->getTraceAsString();
            } catch (\Exception $e) {
                $logData["ERROR"] = $e->getMessage();
                $logData["Trace"] = $e->getTraceAsString();
            }

            //log
            $this->suiteLogger->sageLog(Logger::LOG_CRON, $logData, [__METHOD__, __LINE__]);
        }
        return $this;
    }

    /**
     * @param $orderId
     */
    private function logCancelledPayment($orderId)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                "OrderId" => $orderId,
                "Result"  => "CANCELLED : No payment received."
            ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     */
    private function logErrorPaymentNotFound($orderId)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                "OrderId" => $orderId,
                "Result"  => "ERROR : No payment found."
            ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $apiException
     */
    private function logApiException($orderId, $apiException)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                "OrderId" => $orderId,
                "Result"  => $apiException->getUserMessage(),
                "Stack"   => $apiException->getTraceAsString()
            ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $e
     */
    private function logGeneralException($orderId, $e)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                "OrderId" => $orderId,
                "Result"  => $e->getMessage(),
                "Trace"   => $e->getTraceAsString()
            ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $orderId
     * @param $apiException
     */
    private function logTransactionNotFound($orderId, $apiException)
    {
        $this->suiteLogger->sageLog(Logger::LOG_CRON, [
            "OrderId" => $orderId,
            "Result"  => $apiException->getUserMessage() . " The transaction might still be in process"
        ], [__METHOD__, __LINE__]);
    }

    /**
     * @param $transactionId
     * @param Order $_order
     * @param Payment $payment
     * @return mixed
     * @throws ApiException
     */
    public function getTransactionDetails($transactionId, Order $_order, Payment $payment)
    {
        if ($transactionId != null) {
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid($transactionId, $_order->getStoreId());
        } else {
            $vendorTxCode = $payment->getAdditionalInformation('vendorTxCode');
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVendorTxCode(
                $vendorTxCode,
                $_order->getStoreId()
            );
        }
        return $transactionDetails;
    }

    /**
     * @param $apiException
     * @return bool
     */
    public function checkIfTransactionNotFound($apiException)
    {
        return $apiException->getCode() === self::TRANSACTION_NOT_FOUND;
    }

    /**
     * @param $apiException
     * @param int $orderId
     */
    private function apiExceptionToLog($apiException, int $orderId)
    {
        if ($this->checkIfTransactionNotFound($apiException)) {
            $this->logTransactionNotFound($orderId, $apiException);
        } else {
            $this->logApiException($orderId, $apiException);
        }
    }
}
