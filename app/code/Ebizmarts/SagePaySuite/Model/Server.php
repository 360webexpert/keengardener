<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Api\PaymentOperations;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Payment\Model\InfoInterface;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;

/**
 * Sage Pay Suite SERVER model
 */
class Server extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var string
     */
    protected $_code = \Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER;// @codingStandardsIgnoreLine

    /**
     * @var string
     */
    protected $_infoBlockType = 'Ebizmarts\SagePaySuite\Block\Info';// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;// @codingStandardsIgnoreLine

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;// @codingStandardsIgnoreLine

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $_config;

    protected $_isInitializeNeeded = true;// @codingStandardsIgnoreLine

    /** @var \Ebizmarts\SagePaySuite\Model\Payment */
    private $paymentOps;

    /**
     * @var Api\Shared|\Ebizmarts\SagePaySuite\Model\Api\Shared
     */
    private $sharedApi;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $reportingApi;

    /**
     * Server constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Payment $paymentOps
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param Config $config
     * @param Api\Shared $sharedApi
     * @param Api\Reporting $reportingApi
     * @param \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Ebizmarts\SagePaySuite\Model\Payment $paymentOps,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Ebizmarts\SagePaySuite\Model\Config $config,
        \Ebizmarts\SagePaySuite\Model\Api\Shared $sharedApi,
        \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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

        $this->_config             = $config;
        $this->_config->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);
        $this->paymentOps          = $paymentOps;

        $this->sharedApi           = $sharedApi;

        $this->reportingApi        = $reportingApi;
    }

    /**
     * Set initialized flag to capture payment
     */
    public function markAsInitialized()
    {
        $this->_isInitializeNeeded = false;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->paymentOps->capture($payment, $amount);
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\Object|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->paymentOps->refund($payment, $amount);
        return $this;
    }

    /**
     * Check void availability
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @internal param \Magento\Framework\Object $payment
     */
    public function canVoid()
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            return false;
        }

        return $this->_canVoid;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this|\Magento\Payment\Model\Method\AbstractMethod
     * @throws LocalizedException
     */
    public function cancel(InfoInterface $payment)
    {
        if ($this->canVoid()) {
            $this->void($payment);
        }
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @return $this|\Magento\Payment\Model\Method\AbstractMethod
     * @throws LocalizedException
     */
    public function void(InfoInterface $payment)
    {
        $transactionId = $payment->getLastTransId();

        try {
            $order              = $payment->getOrder();
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid($transactionId, $order->getStoreId());

            if ((int)$transactionDetails->txstateid === PaymentOperations::DEFERRED_AWAITING_RELEASE) {
                if ($order->canInvoice()) {
                    $this->sharedApi->abortDeferredTransaction($transactionDetails);
                }
            }
            elseif ((int)$transactionDetails->txstateid === PaymentOperations::AUTHENTICATED_AWAITING_AUTHORISE){
                $this->sharedApi->cancelAuthenticatedTransaction($transactionDetails);
            }
            else {
                $this->sharedApi->voidTransaction($transactionDetails);
            }
        } catch (ApiException $apiException) {
            if ($this->exceptionCodeIsInvalidTransactionState($apiException)) {
                //unable to void transaction
                throw new LocalizedException(
                    __('Unable to VOID Opayo transaction %1: %2', $transactionId, $apiException->getUserMessage())
                );
            } else {
                $this->_logger->critical($apiException);
                throw $apiException;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                __('Unable to VOID Opayo transaction %1: %2', $transactionId, $e->getMessage())
            );
        }

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
        $order = $payment->getOrder();
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
        return $this->_config->getPaymentAction();
    }

    /**
     * @param $apiException
     * @return bool
     */
    private function exceptionCodeIsInvalidTransactionState($apiException)
    {
        return $apiException->getCode() == ApiException::INVALID_TRANSACTION_STATE;
    }
}
