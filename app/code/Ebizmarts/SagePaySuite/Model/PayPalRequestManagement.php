<?php

namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\Encryption\EncryptorInterface;

class PayPalRequestManagement implements \Ebizmarts\SagePaySuite\Api\PayPalManagementInterface
{
    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    private $quoteRepository;

    /** @var \Ebizmarts\SagePaySuite\Model\Config */
    private $sagePayConfig;

    /** @var \Magento\Checkout\Model\Session */
    private $checkoutSession;

    /** @var \Magento\Quote\Model\Quote */
    private $quote;

    /** @var \Magento\Framework\UrlInterface */
    private $coreUrl;

    /** @var \Ebizmarts\SagePaySuite\Helper\Data */
    private $suiteHelper;

    /** @var \Ebizmarts\SagePaySuite\Helper\Request */
    private $requestHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\Post */
    private $postApi;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\Result */
    private $result;

    /** @var \Ebizmarts\SagePaySuite\Helper\Checkout */
    private $checkoutHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Logger\Logger */
    private $suiteLogger;

    /** @var \Magento\Quote\Model\QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Config $config,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Ebizmarts\SagePaySuite\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Helper\Request $requestHelper,
        \Ebizmarts\SagePaySuite\Helper\Checkout $checkoutHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\UrlInterface $coreUrl,
        \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result,
        \Ebizmarts\SagePaySuite\Model\Api\Post $postApi,
        EncryptorInterface $encryptor
    ) {
        $this->quoteRepository    = $quoteRepository;
        $this->sagePayConfig      = $config;
        $this->suiteHelper        = $suiteHelper;
        $this->checkoutSession    = $checkoutSession;
        $this->suiteLogger        = $suiteLogger;
        $this->requestHelper      = $requestHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->coreUrl            = $coreUrl;
        $this->checkoutHelper     = $checkoutHelper;
        $this->result             = $result;
        $this->postApi            = $postApi;
        $this->encryptor          = $encryptor;

        $this->sagePayConfig->setMethodCode($this->getMethodCode());
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInformationAndPlaceOrder($cartId)
    {
        try {
            //prepare quote
            $quote = $this->getQuoteById($cartId);
            $quote->collectTotals();
            $quote->reserveOrderId()->save();

            $this->quote = $quote;

            //generate POST request
            $requestData = $this->generateRequest();

            //send POST to Sage Pay
            $postResponse = $this->postApi->sendPost(
                $requestData,
                $this->getServiceURL(),
                ["PPREDIRECT"],
                'Invalid response from PayPal'
            );

            //set payment info for save order
            $payment = $quote->getPayment();
            $payment->setMethod($this->getMethodCode());

            //save order with pending payment
            $order = $this->checkoutHelper->placeOrder($quote);

            if ($order) {
                //set pre-saved order flag in checkout session
                $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY, $order->getId());
                $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER, 1);

                //set payment data
                $payment = $order->getPayment();

                $transactionId = str_replace(["}", "{"], [""], $postResponse["data"]["VPSTxId"]);
                $payment->setTransactionId($transactionId);

                $payment->setLastTransId($transactionId);
                $payment->setAdditionalInformation('vendorTxCode', $requestData["VendorTxCode"]);
                $payment->setAdditionalInformation('vendorname', $this->sagePayConfig->getVendorname());
                $payment->setAdditionalInformation('mode', $this->sagePayConfig->getMode());
                $payment->setAdditionalInformation('paymentAction', $this->sagePayConfig->getSagepayPaymentAction());
                $payment->setAdditionalInformation('securityKey', $postResponse["data"]["SecurityKey"]);
                $payment->save();

                //prepare response
                $this->result->setSuccess(true);
                $this->result->setResponse($postResponse);
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Unable to save Opayo order'));
            }

            $this->result->setSuccess(true);
            $this->result->setResponse($postResponse);
        } catch (Api\ApiException $apiException) {
            $this->suiteLogger->logException($apiException, [__METHOD__, __LINE__]);

            $this->result->setSuccess(false);
            $this->result->setErrorMessage(
                __('Something went wrong while generating the Opayo request: %1', $apiException->getUserMessage())
            );
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);

            $this->result->setSuccess(false);
            $this->result->setErrorMessage(
                __('Something went wrong while generating the Opayo request: %1', $e->getMessage())
            );
        }

        return $this->result;
    }

    /**
     * @return array
     */
    private function generateRequest()
    {
        $data                 = [];
        $data["VPSProtocol"]  = $this->sagePayConfig->getVPSProtocol();
        $data["TxType"]       = $this->sagePayConfig->getSagepayPaymentAction();
        $data["Vendor"]       = $this->sagePayConfig->getVendorname();
        $data["VendorTxCode"] = $this->suiteHelper->generateVendorTxCode($this->quote->getReservedOrderId());
        $data["Description"]  = __("Magento ecom transaction.");

        //referrer id
        $data["ReferrerID"] = $this->requestHelper->getReferrerId();

        if ($this->sagePayConfig->getBasketFormat() != Config::BASKETFORMAT_DISABLED) {
            $forceXmlBasket = $this->sagePayConfig->isPaypalForceXml();

            $basket = $this->requestHelper->populateBasketInformation($this->quote, $forceXmlBasket);
            $data   = array_merge($data, $basket);
        }

        $data["CardType"] = "PAYPAL";

        //populate payment amount information
        $data = array_merge($data, $this->requestHelper->populatePaymentAmountAndCurrency($this->quote));

        $data = $this->requestHelper->unsetBasketXMLIfAmountsDontMatch($data);

        //address information
        $data = array_merge($data, $this->requestHelper->populateAddressInformation($this->quote));

        $data["PayPalCallbackURL"] = $this->getCallbackUrl();
        $data["BillingAgreement"]  = (int)$this->sagePayConfig->getPaypalBillingAgreement();

        return $data;
    }

    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }

    public function getQuoteIdMaskFactory()
    {
        return $this->quoteIdMaskFactory;
    }

    /**
     * @inheritDoc
     */
    public function getQuoteById($cartId)
    {
        return $this->getQuoteRepository()->get($cartId);
    }

    private function getServiceURL()
    {
        if ($this->sagePayConfig->getMode()== \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE) {
            return \Ebizmarts\SagePaySuite\Model\Config::URL_DIRECT_POST_LIVE;
        } else {
            return \Ebizmarts\SagePaySuite\Model\Config::URL_DIRECT_POST_TEST;
        }
    }

    private function getCallbackUrl()
    {
        $url = $this->coreUrl->getUrl('sagepaysuite/paypal/processing', [
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);

        $url .= "?quoteid=" . urlencode($this->encryptor->encrypt($this->quote->getId()));

        return $url;
    }

    /**
     * @return string
     */
    private function getMethodCode()
    {
        return \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL;
    }
}
