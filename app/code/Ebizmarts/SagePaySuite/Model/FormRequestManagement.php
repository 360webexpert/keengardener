<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\Data\FormResultInterface;
use Ebizmarts\SagePaySuite\Api\FormManagementInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Helper\Request;
use Ebizmarts\SagePaySuite\Model\FormCrypt;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class FormRequestManagement implements FormManagementInterface
{

    /** @var FormResultInterface  */
    private $result;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $suiteHelper;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * Sage Pay Suite Request Helper
     * @var Request
     */
    private $requestHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /** @var Checkout */
    private $checkoutHelper;

    private $transactionVendorTxCode;

    private $formCrypt;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        Config $config,
        Data $suiteHelper,
        Logger\Logger $suiteLogger,
        Request $requestHelper,
        FormResultInterface $result,
        Checkout $checkoutHelper,
        Session $checkoutSession,
        CustomerSession $customerSession,
        CartRepositoryInterface $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        UrlInterface $coreUrl,
        FormCrypt $formCrypt,
        EncryptorInterface $encryptor
    ) {

        $this->result             = $result;
        $this->quoteRepository    = $quoteRepository;
        $this->config             = $config;
        $this->suiteHelper        = $suiteHelper;
        $this->checkoutSession    = $checkoutSession;
        $this->customerSession    = $customerSession;
        $this->suiteLogger        = $suiteLogger;
        $this->requestHelper      = $requestHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->url                = $coreUrl;
        $this->formCrypt          = $formCrypt;
        $this->checkoutHelper     = $checkoutHelper;
        $this->encryptor          = $encryptor;

        $this->config->setMethodCode(Config::METHOD_FORM);
    }

    /**
     * @param $cartId
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function getEncryptedRequest($cartId)
    {
        try {
            $this->quote = $this->getQuoteById($cartId);
            $this->quote->collectTotals();
            $this->quote->reserveOrderId();
            $this->quote->save();

            $vendorname = $this->config->getVendorname();
            $this->transactionVendorTxCode = $this->suiteHelper->generateVendorTxCode(
                $this->quote->getReservedOrderId()
            );

            //set payment info for save order
            $payment = $this->quote->getPayment();
            $payment->setMethod(Config::METHOD_FORM);

            //save order with pending payment
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->checkoutHelper->placeOrder();
            if ($order->getEntityId()) {
                //set pre-saved order flag in checkout session
                $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY, $order->getId());
                $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER, 1);

                //set payment data
                $payment = $order->getPayment();
                $payment->setAdditionalInformation('vendorTxCode', $this->transactionVendorTxCode);
                $payment->setAdditionalInformation('vendorname', $vendorname);
                $payment->setAdditionalInformation('mode', $this->config->getMode());
                $payment->setAdditionalInformation('paymentAction', $this->config->getSagepayPaymentAction());
                $payment->save();

                $this->result->setSuccess(true);
                $this->result->setRedirectUrl($this->getFormRedirectUrl());
                $this->result->setVpsProtocol($this->config->getVPSProtocol());
                $this->result->setTxType($this->config->getSagepayPaymentAction());
                $this->result->setVendor($vendorname);
                $this->result->setCrypt($this->generateFormCrypt());
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Unable to save Opayo order'));
            }
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);

            $this->result->setSuccess(false);
            $this->result->setErrorMessage(__("Something went wrong: %1", $e->getMessage()));
        }

        return $this->result;
    }

    /**
     * @return string
     */
    private function getFormRedirectUrl()
    {
        $url = Config::URL_FORM_REDIRECT_LIVE;

        if ($this->config->getMode()== Config::MODE_TEST) {
            $url = Config::URL_FORM_REDIRECT_TEST;
        }

        return $url;
    }

    private function generateFormCrypt()
    {

        $encryptedPassword = $this->config->getFormEncryptedPassword();

        if (empty($encryptedPassword)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid FORM encrypted password.'));
        }

        $data = [];
        $data['VendorTxCode'] = $this->transactionVendorTxCode;
        $data['Description']  = $this->requestHelper->getOrderDescription();

        //referrer id
        $data["ReferrerID"] = $this->requestHelper->getReferrerId();

        if ($this->config->getBasketFormat() != Config::BASKETFORMAT_DISABLED) {
            $data = array_merge($data, $this->requestHelper->populateBasketInformation($this->quote));
        }

        $encryptedQuoteId = $this->encryptor->encrypt($this->quote->getId());

        $data['SuccessURL'] = $this->url->getUrl('sagepaysuite/form/success', [
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);
        $data['SuccessURL'] .= '?quoteid=' . urlencode($encryptedQuoteId);

        $data['FailureURL'] = $this->url->getUrl('sagepaysuite/form/failure', [
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);
        $data['FailureURL'] .= '?quoteid=' . urlencode($encryptedQuoteId);

        //email details
        $data['VendorEMail']  = $this->config->getFormVendorEmail();
        $data['SendEMail']    = $this->config->getFormSendEmail();
        $data['EmailMessage'] = substr($this->config->getFormEmailMessage(), 0, 7500);

        //populate payment amount information
        $data = array_merge($data, $this->requestHelper->populatePaymentAmountAndCurrency($this->quote));

        $data = $this->requestHelper->unsetBasketXMLIfAmountsDontMatch($data);

        //populate address information
        $data = array_merge($data, $this->requestHelper->populateAddressInformation($this->quote));

        //3D rules
        $data["Apply3DSecure"] = $this->config->get3Dsecure();

        //Avs/Cvc rules
        $data["ApplyAVSCV2"]   = $this->config->getAvsCvc();

        //gif aid
        $data["AllowGiftAid"]  = (int)$this->config->isGiftAidEnabled();

        //log request
        $this->suiteLogger->sageLog(Logger\Logger::LOG_REQUEST, $data, [__METHOD__, __LINE__]);

        $preCryptString = '';
        foreach ($data as $field => $value) {
            if ($value != '') {
                $preCryptString .= ($preCryptString == '') ? "$field=$value" : "&$field=$value";
            }
        }

        return $this->encryptRequest($encryptedPassword, $preCryptString);
    }

    /**
     * @param string $encryptedPassword
     * @param string $preCryptString
     * @return string
     */
    private function encryptRequest($encryptedPassword, $preCryptString)
    {
        $this->formCrypt->initInitializationVectorAndKey($encryptedPassword);

        $crypt = $this->formCrypt->encrypt($preCryptString);

        return $crypt;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteById($cartId)
    {
        return $this->quoteRepository->get($cartId);
    }

    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }

    public function getQuoteIdMaskFactory()
    {
        return $this->quoteIdMaskFactory;
    }
}
