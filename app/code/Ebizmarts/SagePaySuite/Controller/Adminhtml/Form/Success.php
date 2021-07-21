<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Form;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

class Success extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Checkout
     */
    private $checkoutHelper;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Form
     */
    private $formModel;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    private $quoteManagement;

    /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory */
    private $actionFactory;

    /**
     * Success constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ebizmarts\SagePaySuite\Model\Config $config
     * @param Logger $suiteLogger
     * @param \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory
     * @param \Ebizmarts\SagePaySuite\Helper\Checkout $checkoutHelper
     * @param \Ebizmarts\SagePaySuite\Model\Form $formModel
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory $actionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ebizmarts\SagePaySuite\Model\Config $config,
        Logger $suiteLogger,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Ebizmarts\SagePaySuite\Helper\Checkout $checkoutHelper,
        \Ebizmarts\SagePaySuite\Model\Form $formModel,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory $actionFactory
    ) {

        parent::__construct($context);
        $this->config = $config;
        $this->config->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM);
        $this->formModel          = $formModel;
        $this->suiteLogger        = $suiteLogger;
        $this->quoteSession       = $quoteSession;
        $this->actionFactory      = $actionFactory;
        $this->checkoutHelper     = $checkoutHelper;
        $this->quoteManagement    = $quoteManagement;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * FORM success callback
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $order = null;

        try {
            //decode response
            $response = $this->formModel->decodeSagePayResponse($this->getRequest()->getParam("crypt"));
            if (!isset($response["VPSTxId"])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid response from Opayo'));
            }

            //log response
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);

            $this->quote = $this->quoteSession->getQuote();

            $transactionId = $response["VPSTxId"];
            $transactionId = str_replace("{", "", str_replace("}", "", $transactionId)); //strip brackets

            //import payment info for save order
            $payment = $this->quote->getPayment();
            $payment->setMethod(\Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM);
            $payment->setTransactionId($transactionId);
            $payment->setCcType($response["CardType"]);
            $payment->setCcLast4($response["Last4Digits"]);
            if (isset($response["ExpiryDate"])) {
                $payment->setCcExpMonth(substr($response["ExpiryDate"], 0, 2));
                $payment->setCcExpYear(substr($response["ExpiryDate"], 2));
            }
            if (isset($response["3DSecureStatus"])) {
                $payment->setAdditionalInformation('threeDStatus', $response["3DSecureStatus"]);
            }
            $payment->setAdditionalInformation('statusDetail', $response["StatusDetail"]);
            $payment->setAdditionalInformation('vendorTxCode', $response["VendorTxCode"]);
            $payment->setAdditionalInformation('vendorname', $this->config->getVendorname());
            $payment->setAdditionalInformation('mode', $this->config->getMode());
            $payment->setAdditionalInformation('paymentAction', $this->config->getSagepayPaymentAction());

            $order = $this->quoteManagement->submit($this->quote);

            //an order may be created
            if ($order) {
                //send email
                $this->checkoutHelper->sendOrderEmail($order);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Can not create order'));
            }

            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->getMethodInstance()->markAsInitialized();
            $order->place()->save();

            /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
            $actionClosed = $this->actionFactory->create(['paymentAction' => $this->config->getSagepayPaymentAction()]);
            list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

            //create transaction record
            $transaction = $this->transactionFactory->create();
            $transaction->setOrderPaymentObject($payment);
            $transaction->setTxnId($transactionId);
            $transaction->setOrderId($order->getEntityId());
            $transaction->setTxnType($action);
            $transaction->setPaymentId($payment->getId());
            $transaction->setIsClosed($closed);
            $transaction->save();

            //update invoice transaction id
            $order->getInvoiceCollection()
                ->setDataToAll('transaction_id', $payment->getLastTransId())
                ->save();

            //add success url to response
            $route = 'sales/order/view';
            $param['order_id'] = $order->getId();
            $url = $this->_backendUrl->getUrl($route, $param);
            $this->_redirect($url);

            return;
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);

            if ($order) {
                $this->messageManager->addError($e->getMessage());
                $route = 'sales/order/view';
                $param['order_id'] = $order->getId();
                $url = $this->_backendUrl->getUrl($route, $param);
            } else {
                $this->messageManager
                    ->addError("Your payment was successful but the order was NOT created: " . $e->getMessage());
                $route = 'sales/order/view';
                $url = $this->_backendUrl->getUrl($route, []);
            }

            $this->_redirect($url);
        }
    }
}
