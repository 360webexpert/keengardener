<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Payment;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * SagePaySuite Paypal integration
 */
class Paypal extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = Config::METHOD_PAYPAL;  // @codingStandardsIgnoreLine

    /**
     * @var string
     */
    protected $_infoBlockType = 'Ebizmarts\SagePaySuite\Block\Info';  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;  // @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;  // @codingStandardsIgnoreLine

    /**
     * @var Config
     */
    private $config;

    /** @var bool */
    private $isInitializeNeeded = true;

    /** @var Payment */
    private $paymentOps;

    /**
     * Paypal constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Payment $paymentOps
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param Config $config
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Payment $paymentOps,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Config $config,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->config      = $config;
        $this->config->setMethodCode(Config::METHOD_PAYPAL);
        $this->paymentOps = $paymentOps;
    }

    /**
     * Capture payment
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->paymentOps->capture($payment, $amount);
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\Object|InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->paymentOps->refund($payment, $amount);
        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function initialize($paymentAction, $stateObject)
    {
        //disable sales email
        $payment = $this->getInfoInstance();
        $order   = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $this->paymentOps->setOrderStateAndStatus($payment, $paymentAction, $stateObject);

        $stateObject->setIsNotified(false);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Return magento payment action
     *
     * @return mixed
     */
    public function getConfigPaymentAction()
    {
        return $this->config->getPaymentAction();
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     * @api
     */
    public function isInitializeNeeded()
    {
        return $this->isInitializeNeeded;
    }

    /**
     * Set initialized flag to capture payment
     */
    public function markAsInitialized()
    {
        $this->isInitializeNeeded = false;
    }
}
