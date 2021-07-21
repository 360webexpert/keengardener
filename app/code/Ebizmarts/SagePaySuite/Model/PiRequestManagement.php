<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\Data\PiRequest as PiDataRequest;

class PiRequestManagement implements \Ebizmarts\SagePaySuite\Api\PiManagementInterface
{
    /** @var Config */
    private $config;

    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    private $quoteRepository;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager */
    private $piRequestManagerDataFactory;

    /** @var \Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement */
    private $requester;

    /** @var \Magento\Quote\Model\QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Config $config,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory $piReqManagerFactory,
        \Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement $requester,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->config = $config;
        $this->config->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PI);

        $this->requester                   = $requester;
        $this->quoteRepository             = $quoteRepository;
        $this->quoteIdMaskFactory          = $quoteIdMaskFactory;
        $this->piRequestManagerDataFactory = $piReqManagerFactory;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndPlaceOrder($cartId, PiDataRequest $requestData)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager $data */
        $data = $this->piRequestManagerDataFactory->create();
        $data->setMode($this->config->getMode());
        $data->setVendorName($this->config->getVendorname());
        $data->setPaymentAction($this->config->getSagepayPaymentAction());
        $data->setMerchantSessionKey($requestData->getMerchantSessionKey());
        $data->setCardIdentifier($requestData->getCardIdentifier());
        $data->setCcExpMonth($requestData->getCcExpMonth());
        $data->setCcExpYear($requestData->getCcExpYear());
        $data->setCcLastFour($requestData->getCcLastFour());
        $data->setCcType($requestData->getCcType());

        $data->setJavascriptEnabled($requestData->getJavascriptEnabled());
        $data->setLanguage($requestData->getLanguage());
        $data->setUserAgent($requestData->getUserAgent());
        $data->setAcceptHeaders($requestData->getAcceptHeaders());
        $data->setJavaEnabled($requestData->getJavaEnabled());
        $data->setColorDepth($requestData->getColorDepth());
        $data->setScreenHeight($requestData->getScreenHeight());
        $data->setScreenWidth($requestData->getScreenWidth());
        $data->setTimezone($requestData->getTimezone());

        $this->requester->setRequestData($data);
        $this->requester->setQuote($this->getQuoteById($cartId));

        return $this->requester->placeOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteById($cartId)
    {
        return $this->getQuoteRepository()->get($cartId);
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
