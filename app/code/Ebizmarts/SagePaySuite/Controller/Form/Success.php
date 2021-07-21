<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Form;

use Ebizmarts\SagePaySuite\Model\Form;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Ebizmarts\SagePaySuite\Helper\Data as SuiteHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\OrderRepository;
use Ebizmarts\SagePaySuite\Model\Config;

class Success extends Action
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var Form
     */
    private $formModel;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /** @var OrderSender */
    private $orderSender;

    /** @var OrderUpdateOnCallback */
    private $updateOrderCallback;

    /**
     * @var SuiteHelper
     */
    private $suiteHelper;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /** @var OrderLoader */
    private $orderLoader;

    /**
     * Success constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param Logger $suiteLogger
     * @param Form $formModel
     * @param OrderSender $orderSender
     * @param OrderUpdateOnCallback $updateOrderCallback
     * @param SuiteHelper $suiteHelper
     * @param EncryptorInterface $encryptor
     * @param QuoteRepository $quoteRepository
     * @param OrderLoader $orderLoader
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Logger $suiteLogger,
        Form $formModel,
        OrderSender $orderSender,
        OrderUpdateOnCallback $updateOrderCallback,
        SuiteHelper $suiteHelper,
        EncryptorInterface $encryptor,
        QuoteRepository $quoteRepository,
        OrderLoader $orderLoader
    )
    {

        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->suiteLogger = $suiteLogger;
        $this->formModel = $formModel;
        $this->orderSender = $orderSender;
        $this->updateOrderCallback = $updateOrderCallback;
        $this->suiteHelper = $suiteHelper;
        $this->encryptor = $encryptor;
        $this->quoteRepository = $quoteRepository;
        $this->orderLoader         = $orderLoader;
    }

    /**
     * FORM success callback
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            $request = $this->getRequest();
            $crypt = $request->getParam("crypt");
            $response = $this->formModel->decodeSagePayResponse($crypt);

            if (!isset($response["VPSTxId"])) {
                throw new LocalizedException(__('Invalid response from Opayo.'));
            }

            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);
            $quoteIdEncrypted = $request->getParam("quoteid");
            $quoteIdFromParams = $this->encryptor->decrypt($quoteIdEncrypted);
            $this->quote = $this->quoteRepository->get((int)$quoteIdFromParams);

            $this->order = $this->orderLoader->loadOrderFromQuote($this->quote);

            $transactionId = $response["VPSTxId"];
            $transactionId = $this->suiteHelper->removeCurlyBraces($transactionId); //strip brackets
            $payment = $this->order->getPayment();
            $vendorTxCode = $payment->getAdditionalInformation("vendorTxCode");

            $isDuplicated = $payment->getAdditionalInformation("Status") == Config::OK_STATUS;

            if (!$isDuplicated) {
                if (!empty($transactionId) && ($vendorTxCode == $response['VendorTxCode'])) {
                    foreach ($response as $name => $value) {
                        $payment->setTransactionAdditionalInfo($name, $value);
                        $payment->setAdditionalInformation($name, $value);
                    }

                    $payment->setLastTransId($transactionId);
                    $payment->setCcType($response['CardType']);
                    $payment->setCcLast4($response['Last4Digits']);

                    if (isset($response["ExpiryDate"])) {
                        $payment->setCcExpMonth(substr($response["ExpiryDate"], 0, 2));
                        $payment->setCcExpYear(substr($response["ExpiryDate"], 2));
                    }

                    $payment->save();
                } else {
                    throw new \Magento\Framework\Validator\Exception(__('Invalid transaction id.'));
                }
            }

            $redirect = 'sagepaysuite/form/failure';
            $status   = $response['Status'];

            if ($status == Config::OK_STATUS || $status == Config::AUTHENTICATED_STATUS || $status == Config::REGISTERED_STATUS) {
                $this->updateOrderCallback->setOrder($this->order);

                try {
                    $this->updateOrderCallback->confirmPayment($transactionId);
                } catch (AlreadyExistsException $ex) {
                    $this->suiteLogger->sageLog(Logger::LOG_REQUEST, "Sage Pay retry. $transactionId", [__METHOD__, __LINE__]);
                }
                $redirect = 'checkout/onepage/success';
            } elseif ($status == Config::PENDING_STATUS) {
                //Transaction in PENDING state (this is just for Euro Payments)
                $payment->setAdditionalInformation('euroPayment', true);

                //send order email
                $this->orderSender->send($this->order);

                $redirect = 'checkout/onepage/success';
            } elseif ($isDuplicated) {
                $redirect = 'checkout/onepage/success';
            }

            $quoteId = $this->quote->getId();
            //prepare session to success page
            $this->checkoutSession->start();
            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastQuoteId($quoteId);
            $this->checkoutSession->setLastSuccessQuoteId($quoteId);
            $this->checkoutSession->setLastOrderId($this->order->getId());
            $this->checkoutSession->setLastRealOrderId($this->order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($this->order->getStatus());
            $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY, null);
            $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER, 0);

            return $this->_redirect($redirect);
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e);
            $this->_redirectToCartAndShowError(
                __('Your payment was successful but the order was NOT created, please contact us: %1', $e->getMessage())
            );
        }
    }

    /**
     * Redirect customer to shopping cart and show error message
     *
     * @param string $errorMessage
     * @return void
     */
    private function _redirectToCartAndShowError($errorMessage)
    {
        $this->messageManager->addError($errorMessage);
        $this->_redirect('checkout/cart');
    }
}
