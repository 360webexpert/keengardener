<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\Store;
use Magento\Sales\Model\Order;

class Fraud extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Config
     */
    private $_config;

    /**
     * @var TransportBuilder
     */
    private $_mailTransportBuilder;

    /**
     * \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $_reportingApi;

    /**
     * @var InvoiceService
     */
    private $invoiceServiceFactory;

    /**
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;

    /**
     * Fraud constructor.
     * @param Context $context
     * @param Config $config
     * @param TransportBuilder $mailTransportBuilder
     * @param Reporting $reportingApi
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param InvoiceService $invoiceService
     */
    public function __construct(
        Context $context,
        Config $config,
        TransportBuilder $mailTransportBuilder,
        Reporting $reportingApi,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Service\InvoiceServiceFactory $invoiceService
    ) {
    
        parent::__construct($context);
        $this->_config               = $config;
        $this->_mailTransportBuilder = $mailTransportBuilder;
        $this->_reportingApi         = $reportingApi;
        $this->invoiceServiceFactory = $invoiceService;
        $this->transactionFactory    = $transactionFactory;
    }

    /**
     * @param $transaction
     * @param $payment
     * @return array
     */
    public function processFraudInformation($transaction, $payment)
    {
        $sagepayVpsTxId = $transaction->getTxnId();

        $logData = ["VPSTxId" => $sagepayVpsTxId];

        //flag test transactions (no actions taken with test orders)
        if ($payment->getAdditionalInformation("mode") &&
            $payment->getAdditionalInformation("mode") == Config::MODE_TEST
        ) {
            /**
             *  TEST TRANSACTION
             */

            $transaction->setSagepaysuiteFraudCheck(1);
            $transaction->save();
            $logData["Action"] = "Marked as TEST";
        } else {

            /**
             * LIVE TRANSACTION
             */

            //get transaction data from sagepay
            $response = $this->_reportingApi->getFraudScreenDetail($sagepayVpsTxId);

            if ($response->getErrorCode() == "0000") {
                if ($this->fraudCheckAvailable($response)) {
                    //mark payment as fraud
                    if ($this->transactionIsFraud($response)) {
                        $payment->setIsFraudDetected(true);
                        $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
                        $payment->save();
                        $logData["Action"] = "Marked as FRAUD.";
                    }

                    //mark as checked
                    $transaction->setSagepaysuiteFraudCheck(1);
                    $transaction->save();

                    /**
                     * process fraud actions
                     */

                    //auto-invoice
                    $autoInvoiceActioned = $this->_processAutoInvoice(
                        $transaction,
                        $payment,
                        $this->isPassedFraudCheck($response)
                    );
                    if (!empty($autoInvoiceActioned)) {
                        $logData["Action"] = $autoInvoiceActioned;
                    }

                    //notification
                    /*$notificationActioned = $this->_notification($transaction, $payment,
                        $fraudscreenrecommendation,
                        $fraudid,
                        $fraudcodedetail,
                        $fraudprovidername,
                        $rules);
                    if (!empty($notificationActioned)) {
                        $logData["Notification"] = $notificationActioned;
                    }*/

                    /**
                     * END process fraud actions
                     */

                    /**
                     * save fraud information in the payment as the transaction
                     * additional info of the transactions does not seem to be working
                     */
                    $this->saveFraudInformation($response, $payment);

                    $logData = array_merge($logData, $this->getFraudInformationToLog($response));
                } else {
                    $recommendation = $this->getFraudScreenRecommendation($response);

                    //save the "not checked" or "no result" status
                    $payment->setAdditionalInformation("fraudscreenrecommendation", $recommendation);
                    $payment->save();

                    $logData["fraudscreenrecommendation"] = $recommendation;
                }
            } else {
                $responseErrorCodeShow = "INVALID";
                if ($response->getErrorCode()) {
                    $responseErrorCodeShow = $response->getErrorCode();
                }
                $logData["ERROR"] = "Invalid Response: " . $responseErrorCodeShow;
            }
        }

        return $logData;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return string
     */
    private function getFraudScreenRecommendation($fraudData)
    {
        $recommendation = '';

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $recommendation = $fraudData->getFraudScreenRecommendation();
        } else if ($fraudprovidername == 'T3M') {
            $recommendation = $fraudData->getThirdmanAction();
        }

        return $recommendation;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function isPassedFraudCheck($fraudData)
    {
        $passed = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $passed = $fraudData->getFraudScreenRecommendation() == Config::REDSTATUS_ACCEPT;
        } else if ($fraudprovidername == 'T3M') {
            $passed = $fraudData->getThirdmanAction() == Config::T3STATUS_OK;
        }

        return $passed;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return array
     */
    private function getFraudInformationToLog($fraudData)
    {
        $logData = [];

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $fraudscreenrecommendation = $fraudData->getFraudScreenRecommendation();
            $fraudid                   = $fraudData->getFraudId();
            $fraudcode                 = $fraudData->getFraudCode();
            $fraudcodedetail           = $fraudData->getFraudCodeDetail();
        } elseif ($fraudprovidername == 'T3M') {
            $fraudscreenrecommendation = $fraudData->getThirdmanAction();
            $fraudid                   = $fraudData->getThirdmanId();
            $fraudcode                 = $fraudData->getThirdmanScore();
            $fraudcodedetail           = $fraudData->getThirdmanAction();
            $logData["fraudrules"]     = $fraudData->getThirdmanRulesAsArray();
        }

        $logData["fraudscreenrecommendation"] = $fraudscreenrecommendation;
        $logData["fraudid"]                   = $fraudid;
        $logData["fraudcode"]                 = $fraudcode;
        $logData["fraudcodedetail"]           = $fraudcodedetail;
        $logData["fraudprovidername"]         = $fraudprovidername;

        return $logData;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @param $payment
     */
    private function saveFraudInformation($fraudData, $payment)
    {
        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $fraudscreenrecommendation = $fraudData->getFraudScreenRecommendation();
            $fraudid                   = $fraudData->getFraudId();
            $fraudcode                 = $fraudData->getFraudCode();
            $fraudcodedetail           = $fraudData->getFraudCodeDetail();
        } else if ($fraudprovidername == 'T3M') {
            $fraudscreenrecommendation = $fraudData->getThirdmanAction();
            $fraudid                   = $fraudData->getThirdmanId();
            $fraudcode                 = $fraudData->getThirdmanScore();
            $fraudcodedetail           = $fraudData->getThirdmanAction();
            $payment->setAdditionalInformation("fraudrules", $fraudData->getThirdmanRulesAsArray());
        }

        $payment->setAdditionalInformation("fraudscreenrecommendation", $fraudscreenrecommendation);
        $payment->setAdditionalInformation("fraudid", $fraudid);
        $payment->setAdditionalInformation("fraudcode", $fraudcode);
        $payment->setAdditionalInformation("fraudcodedetail", $fraudcodedetail);
        $payment->setAdditionalInformation("fraudprovidername", $fraudprovidername);
        $payment->save();
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function transactionIsFraud($fraudData)
    {
        $isFraud = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $isFraud = $fraudData->getFraudScreenRecommendation() == Config::REDSTATUS_DENY;
        } else if ($fraudprovidername == 'T3M') {
            $isFraud = $fraudData->getThirdmanAction() == Config::T3STATUS_REJECT;
        }

        return $isFraud;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function fraudCheckAvailable($fraudData)
    {
        $providerChecked = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == 'ReD') {
            $providerChecked = $fraudData->getFraudScreenRecommendation() != Config::REDSTATUS_NOTCHECKED;
        } else if ($fraudprovidername == 'T3M') {
            $providerChecked = $fraudData->getThirdmanAction() != Config::T3STATUS_NORESULT;
        }

        return $providerChecked;
    }

    private function _processAutoInvoice(
        Transaction $transaction,
        \Magento\Sales\Model\Order\Payment $payment,
        $passedFraudCheck
    ) {
        $state = $payment->getOrder()->getState();
        //auto-invoice authorized order for full amount if ACCEPT or OK
        if ($passedFraudCheck &&
            $this->configAutoInvoice() &&
            $this->transactionIsAuth($transaction) &&
            $this->transactionIsNotClosed($transaction) &&
            $state !== Order::STATE_HOLDED
        ) {
            $order = $payment->getOrder();
            $invoiceService = $this->invoiceServiceFactory->create();
            $invoice = $invoiceService->prepareInvoice($order, []);

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }

            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);

            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->transactionFactory->create();
            $transactionSave->addObject($invoice);
            $transactionSave->addObject($invoice->getOrder());
            $transactionSave->save();

            return "Captured online, invoice #{$invoice->getId()} generated.";
        } else {
            return false;
        }
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    protected function transactionIsAuth(Transaction $transaction): bool
    {
        return $transaction->getTxnType() == Transaction::TYPE_AUTH;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    protected function transactionIsNotClosed(Transaction $transaction): bool
    {
        return (bool)$transaction->getIsTransactionClosed() == false;
    }

    /**
     * @return bool
     */
    protected function configAutoInvoice(): bool
    {
        return (bool)$this->_config->getAutoInvoiceFraudPassed() == true;
    }

    /**
     * @param Transaction $transaction
     * @param Order\Payment $payment
     * @param $fraudscreenrecommendation
     * @param $fraudid
     * @param $fraudcodedetail
     * @param $fraudprovidername
     * @param $rules
     * @return bool|string
     * @codeCoverageIgnore
     */
    private function notification(
        Transaction $transaction,
        \Magento\Sales\Model\Order\Payment $payment,
        $fraudscreenrecommendation,
        $fraudid,
        $fraudcodedetail,
        $fraudprovidername,
        $rules
    ) {
    
        if ((string)$this->_config->getNotifyFraudResult() != 'disabled') {
            if (((string)$this->_config->getNotifyFraudResult() == "medium_risk" &&
                    ($fraudscreenrecommendation == Config::REDSTATUS_DENY ||
                        $fraudscreenrecommendation == Config::REDSTATUS_CHALLENGE ||
                        $fraudscreenrecommendation == Config::T3STATUS_REJECT ||
                        $fraudscreenrecommendation == Config::T3STATUS_HOLD))
                ||
                ((string)$this->_config->getNotifyFraudResult() == "high_risk" &&
                    ($fraudscreenrecommendation == Config::REDSTATUS_DENY ||
                        $fraudscreenrecommendation == Config::T3STATUS_REJECT))
            ) {
                $template = "sagepaysuite_fraud_notification";
                $transport = $this->_mailTransportBuilder->setTemplateIdentifier($template)
                    ->addTo(
                        $this->scopeConfig->getValue(
                            'trans_email/ident_sales/email',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )
                    ->setFrom(
                        $this->scopeConfig->getValue(
                            "contact/email/sender_email_identity",
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )
                    ->setTemplateOptions(['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                        'store' => Store::DEFAULT_STORE_ID])
                    ->setTemplateVars([
                        'transaction_id' => $transaction->getTransactionId(),
                        'order_id' => $payment->getOrder()->getIncrementId(),
                        'vps_tx_id' => $transaction->getTxnId(),
                        'fraud_id' => $fraudid,
                        'recommendation' => $fraudscreenrecommendation,
                        'detail' => $fraudcodedetail,
                        'provider' => $fraudprovidername,
                        'rules' => $rules
                    ])
                    ->getTransport();
                $transport->sendMessage();

                $identSales = $this->scopeConfig->getValue(
                    'trans_email/ident_sales/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                return "Email sent to " . $identSales;
            }
        }

        return false;
    }
}
