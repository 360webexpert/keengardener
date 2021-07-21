<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\PI;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\MotoManagement;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Controller\ResultFactory;

class Request extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var Quote
     */
    private $quoteSession;

    /** @var MotoManagement */
    private $requester;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager */
    private $piRequestManagerDataFactory;

    /**
     * Request constructor.
     * @param Context $context
     * @param Config $config
     * @param Quote $quoteSession
     * @param MotoManagement $requester
     * @param PiRequestManagerFactory $piReqManagerFactory
     */
    public function __construct(
        Context $context,
        Config $config,
        Quote $quoteSession,
        MotoManagement $requester,
        PiRequestManagerFactory $piReqManagerFactory
    ) {
        parent::__construct($context);
        $this->config       = $config;
        $this->quoteSession = $quoteSession;
        $this->quote        = $this->quoteSession->getQuote();

        $this->requester                   = $requester;
        $this->piRequestManagerDataFactory = $piReqManagerFactory;
    }

    public function execute()
    {
        /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager $data */
        $data = $this->piRequestManagerDataFactory->create();
        $data->setMode($this->config->getMode());
        $data->setVendorName($this->config->getVendorname());
        $data->setPaymentAction($this->config->getSagepayPaymentAction());
        $data->setMerchantSessionKey($this->getRequest()->getPost('merchant_session_key'));
        $data->setCardIdentifier($this->getRequest()->getPost('card_identifier'));
        $data->setCcExpMonth($this->getRequest()->getPost('card_exp_month'));
        $data->setCcExpYear($this->getRequest()->getPost('card_exp_year'));
        $data->setCcLastFour($this->getRequest()->getPost('card_last4'));
        $data->setCcType($this->getRequest()->getPost('card_type'));
        $data->setJavascriptEnabled($this->getRequest()->getPost('javascript_enabled'));
        $data->setAcceptHeaders($this->getRequest()->getPost('accept_headers'));
        $data->setLanguage($this->getRequest()->getPost('language'));
        $data->setUserAgent($this->getRequest()->getPost('user_agent'));
        $data->setJavaEnabled($this->getRequest()->getPost('java_enabled'));
        $data->setColorDepth($this->getRequest()->getPost('color_depth'));
        $data->setScreenWidth($this->getRequest()->getPost('screen_width'));
        $data->setScreenHeight($this->getRequest()->getPost('screen_height'));
        $data->setTimezone($this->getRequest()->getPost('timezone'));

        $this->requester->setRequestData($data);
        $this->requester->setQuote($this->quote);

        $response = $this->requester->placeOrder();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response->__toArray());
        return $resultJson;
    }
}
