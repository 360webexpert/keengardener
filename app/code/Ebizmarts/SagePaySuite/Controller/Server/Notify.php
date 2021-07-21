<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Server;

use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\InvalidSignatureException;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Ebizmarts\SagePaySuite\Model\Token;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\Exception;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use \Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Sales\Model\Order;
use function urlencode;

class Notify extends Action implements CsrfAwareActionInterface
{

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /** @var OrderSender */
    private $orderSender;

    /** @var Config */
    private $config;

    /** @var Quote */
    private $quote;

    /** @var \Magento\Sales\Model\Order */
    private $order;

    /** @var array */
    private $postData;

    /** @var Token */
    private $tokenModel;

    /** @var OrderUpdateOnCallback */
    private $updateOrderCallback;

    /** @var Data */
    private $suiteHelper;

    /** @var QuoteRepository */
    private $cartRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var OrderLoader
     */
    private $orderLoader;

    /**
     * Notify constructor.
     * @param Context $context
     * @param Logger $suiteLogger
     * @param OrderSender $orderSender
     * @param Config $config
     * @param Token $tokenModel
     * @param OrderUpdateOnCallback $updateOrderCallback
     * @param Data $suiteHelper
     * @param QuoteRepository $cartRepository
     * @param EncryptorInterface $encryptor
     * @param OrderLoader $orderLoader
     */
    public function __construct(
        Context $context,
        Logger $suiteLogger,
        OrderSender $orderSender,
        Config $config,
        Token $tokenModel,
        OrderUpdateOnCallback $updateOrderCallback,
        Data $suiteHelper,
        QuoteRepository $cartRepository,
        EncryptorInterface $encryptor,
        OrderLoader $orderLoader
    ) {
        parent::__construct($context);

        $this->suiteLogger         = $suiteLogger;
        $this->updateOrderCallback = $updateOrderCallback;
        $this->orderSender         = $orderSender;
        $this->config              = $config;
        $this->tokenModel          = $tokenModel;
        $this->suiteHelper         = $suiteHelper;
        $this->cartRepository      = $cartRepository;
        $this->encryptor           = $encryptor;
        $this->orderLoader         = $orderLoader;

        $this->config->setMethodCode(Config::METHOD_SERVER);
    }

    public function execute()
    {
        //get data from request
        $this->postData = $this->getRequest()->getPost();

        $storeId = $this->getRequest()->getParam("_store");
        $quoteId = $this->encryptor->decrypt($this->getRequest()->getParam("quoteid"));

        //log response
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $this->postData, [__METHOD__, __LINE__]);

        try {
            $this->quote = $this->cartRepository->get($quoteId, [$storeId]);

            $order = $this->orderLoader->loadOrderFromQuote($this->quote);

            $this->order = $order;
            $payment     = $order->getPayment();

            $status = $this->postData->Status;
            $statusDetail = $this->postData->StatusDetail;
            $transactionId = $this->suiteHelper->removeCurlyBraces($this->postData->VPSTxId);

            try {
                $this->validateSignature($payment);
            } catch (InvalidSignatureException $signatureException) {
                return $this->returnInvalid(
                    __("Something went wrong: %1", $signatureException->getMessage()),
                    $this->quote->getId()
                );
            }

            if (!empty($transactionId) && $payment->getLastTransId() == $transactionId) { //validate transaction id
                $payment->setAdditionalInformation('statusDetail', $statusDetail);
                $payment->setAdditionalInformation('AVSCV2', $this->postData->{'AVSCV2'});
                $payment->setAdditionalInformation('AddressResult', $this->postData->{'AddressResult'});
                $payment->setAdditionalInformation('PostCodeResult', $this->postData->{'PostCodeResult'});
                $payment->setAdditionalInformation('CV2Result', $this->postData->{'CV2Result'});
                $payment->setAdditionalInformation('3DSecureStatus', $this->postData->{'3DSecureStatus'});
                if (isset($this->postData->{'BankAuthCode'})) {
                    $payment->setAdditionalInformation('bankAuthCode', $this->postData->{'BankAuthCode'});
                }
                if (isset($this->postData->{'TxAuthNo'})) {
                    $payment->setAdditionalInformation('txAuthNo', $this->postData->{'TxAuthNo'});
                }
                $payment->setCcType($this->postData->CardType);
                $payment->setCcLast4($this->postData->Last4Digits);
                $payment->setCcExpMonth(substr($this->postData->ExpiryDate, 0, 2));
                $payment->setCcExpYear(substr($this->postData->ExpiryDate, 2));
                $payment->save();
            } else {
                throw new Exception(__('Invalid transaction id'));
            }

            $this->persistToken($order);

            if ($status == "ABORT") { //Transaction canceled by customer
                //cancel pending payment order
                $state = $order->getState();
                if ($state === Order::STATE_PENDING_PAYMENT) {
                    //The order might be cancelled on Model/recoverCart if SagePay takes too long in sending the notify. This checks if the order is not cancelled before trying to cancel it.
                    $this->cancelOrder($order);
                } elseif ($state !== Order::STATE_CANCELED) {
                    $this->suiteLogger->sageLog(Logger::LOG_REQUEST, "Incorrect state found on order " . $order->getIncrementId() . " when trying to cancel it. State found: " . $state, [__METHOD__, __LINE__]);
                }
                return $this->returnAbort($this->quote->getId());
            } elseif ($status == "OK" || $status == "AUTHENTICATED" || $status == "REGISTERED") {
                $this->updateOrderCallback->setOrder($this->order);

                try {
                    $this->updateOrderCallback->confirmPayment($transactionId);
                } catch (AlreadyExistsException $ex) {
                    $this->suiteLogger->sageLog(Logger::LOG_REQUEST, "Sage Pay retry. $transactionId", [__METHOD__, __LINE__]);
                }

                return $this->returnOk();
            } elseif ($status == "PENDING") {
                //Transaction in PENDING state (this is just for Euro Payments)

                $payment->setAdditionalInformation('euroPayment', true);

                //send order email
                $this->orderSender->send($this->order);

                return $this->returnOk();
            } else { //Transaction failed with NOTAUTHED, REJECTED or ERROR
                //cancel pending payment order
                $this->cancelOrder($order);

                return $this->returnInvalid(
                    __(
                        "Payment was not accepted, please try another payment method. Status: %1, %2",
                        $status,
                        $statusDetail
                    ),
                    $this->quote->getId()
                );
            }
        } catch (NoSuchEntityException $nse) {
            return $this->returnInvalid(__("Unable to find quote"));
        } catch (ApiException $apiException) {
            $this->suiteLogger->logException($apiException, [__METHOD__, __LINE__]);

            //cancel pending payment order
            if (isset($order)) {
                $this->cancelOrder($order);
            }

            return $this->returnInvalid(__("Something went wrong: %1", $apiException->getUserMessage()));
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);

            //cancel pending payment order
            if (isset($order)) {
                $this->cancelOrder($order);
            }

            return $this->returnInvalid(__("Something went wrong: %1", $e->getMessage()), $this->quote->getId());
        }
    }

    private function getVPSSignatureString($payment)
    {
        return $this->postData->VPSTxId .
        $this->postData->VendorTxCode .
        $this->postData->Status .
        (property_exists($this->postData, 'TxAuthNo') === true ? $this->postData->TxAuthNo : '') .
        strtolower($payment->getAdditionalInformation('vendorname')) .
        $this->postData->AVSCV2 .
        $payment->getAdditionalInformation('securityKey') .
        $this->postData->AddressResult .
        $this->postData->PostCodeResult .
        $this->postData->CV2Result .
        $this->postData->GiftAid .
        $this->postData->{'3DSecureStatus'} .
        (property_exists($this->postData, 'CAVV') === true ? $this->postData->CAVV : '') .
        $this->postData->AddressStatus .
        $this->postData->PayerStatus .
        $this->postData->CardType .
        $this->postData->Last4Digits .
        (property_exists($this->postData, 'DeclineCode') === true ? $this->postData->DeclineCode : '') .
        $this->postData->ExpiryDate .
        (property_exists($this->postData, 'FraudResponse') === true ? $this->postData->FraudResponse : '') .
        (property_exists($this->postData, 'BankAuthCode') === true ? $this->postData->BankAuthCode : '');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function cancelOrder($order)
    {
        try {
            $order->cancel()->save();
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
        }
    }

    private function returnAbort($quoteId = null)
    {
        $strResponse = 'Status=OK' . "\r\n";
        $strResponse .= 'StatusDetail=Transaction ABORTED successfully' . "\r\n";
        $strResponse .= 'RedirectURL=' . $this->getAbortRedirectUrl($quoteId) . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($strResponse);

        //log our response
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $strResponse, [__METHOD__, __LINE__]);
    }

    private function returnOk()
    {
        $strResponse = 'Status=OK' . "\r\n";
        $strResponse .= 'StatusDetail=Transaction completed successfully' . "\r\n";
        $strResponse .= 'RedirectURL=' . $this->getSuccessRedirectUrl() . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($strResponse);

        //log our response
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $strResponse, [__METHOD__, __LINE__]);
    }

    private function returnInvalid($message = 'Invalid transaction, please try another payment method', $quoteId = null)
    {
        $strResponse = 'Status=INVALID' . "\r\n";
        $strResponse .= 'StatusDetail=' . $message . "\r\n";
        $strResponse .= 'RedirectURL=' . $this->getFailedRedirectUrl($message, $quoteId) . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($strResponse);

        //log our response
        $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $strResponse, [__METHOD__, __LINE__]);
    }

    private function getAbortRedirectUrl($quoteId = null)
    {
        $url = $this->_url->getUrl('*/*/cancel', [
            '_secure' => true,
            '_store' => $this->getRequest()->getParam('_store')
        ]);

        $quoteId = $this->encryptor->encrypt($quoteId);
        $url .= "?quote=" . urlencode($quoteId) . "&message=Transaction cancelled by customer";

        return $url;
    }

    private function getSuccessRedirectUrl()
    {
        $url = $this->_url->getUrl('*/*/redirectToSuccess', [
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);

        $url .= "?quoteid=" . urlencode($this->encryptor->encrypt($this->quote->getId()));

        return $url;
    }

    private function getFailedRedirectUrl($message, $quoteId = null)
    {
        $url = $this->_url->getUrl('*/*/cancel', [
            '_secure' => true,
            '_store' => $this->getRequest()->getParam('_store')
        ]);

        $quoteId = $this->encryptor->encrypt($quoteId);
        $url .= "?message=" . $message . "&quote=" . urlencode($quoteId);

        return $url;
    }

    /**
     * @param $payment
     * @throws InvalidSignatureException
     */
    private function validateSignature($payment)
    {
        $localMd5Hash = hash('md5', $this->getVPSSignatureString($payment));

        if (strtoupper($localMd5Hash) !== $this->postData->VPSSignature) {
            $this->suiteLogger->sageLog(
                Logger::LOG_REQUEST,
                "INVALID SIGNATURE: " . $this->getVPSSignatureString($payment),
                [__METHOD__, __LINE__]
            );
            throw new InvalidSignatureException(__('Invalid VPS Signature'));
        }
    }

    /**
     * @param $order
     */
    private function persistToken($order)
    {
        if (isset($this->postData->Token)) {
            //save token

            $this->tokenModel->saveToken(
                $order->getCustomerId(),
                $this->postData->Token,
                $this->postData->CardType,
                $this->postData->Last4Digits,
                substr($this->postData->ExpiryDate, 0, 2),
                substr($this->postData->ExpiryDate, 2),
                $this->config->getVendorname()
            );
        }
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
