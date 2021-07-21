<?php

namespace Ebizmarts\SagePaySuite\Controller\PI;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Callback3Dv2 extends Action implements CsrfAwareActionInterface
{
    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    /** @var ThreeDSecureCallbackManagement */
    private $requester;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager */
    private $piRequestManagerDataFactory;

    /** @var Session */
    private $checkoutSession;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var CustomerSession */
    private $customerSession;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Logger */
    private $suiteLogger;

    /**
     * Callback3Dv2 constructor.
     * @param Context $context
     * @param Config $config
     * @param LoggerInterface $logger
     * @param ThreeDSecureCallbackManagement $requester
     * @param PiRequestManagerFactory $piReqManagerFactory
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param CryptAndCodeData $cryptAndCode
     * @param RecoverCart $recoverCart
     * @param OrderLoader $orderLoader
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param Logger $suiteLogger
     */
    public function __construct(
        Context $context,
        Config $config,
        LoggerInterface $logger,
        ThreeDSecureCallbackManagement $requester,
        PiRequestManagerFactory $piReqManagerFactory,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        QuoteRepository $quoteRepository,
        CryptAndCodeData $cryptAndCode,
        RecoverCart $recoverCart,
        OrderLoader $orderLoader,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Logger $suiteLogger
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->config->setMethodCode(Config::METHOD_PI);
        $this->logger                      = $logger;
        $this->checkoutSession             = $checkoutSession;
        $this->orderRepository             = $orderRepository;
        $this->quoteRepository             = $quoteRepository;
        $this->requester                   = $requester;
        $this->piRequestManagerDataFactory = $piReqManagerFactory;
        $this->cryptAndCode                = $cryptAndCode;
        $this->recoverCart                 = $recoverCart;
        $this->orderLoader                 = $orderLoader;
        $this->customerSession             = $customerSession;
        $this->customerRepository          = $customerRepository;
        $this->suiteLogger                 = $suiteLogger;
    }

    public function execute()
    {
        try {
            $quoteIdEncrypted = $this->getRequest()->getParam("quoteId");
            $quoteIdFromParams = $this->cryptAndCode->decodeAndDecrypt($quoteIdEncrypted);
            $quote = $this->quoteRepository->get((int)$quoteIdFromParams);
            $order = $this->orderLoader->loadOrderFromQuote($quote);
            $customerId = $order->getCustomerId();
            if ($customerId != null) {
                $this->logInCustomer($customerId);
            }
            $orderId = (int)$order->getId();

            $payment = $order->getPayment();

            /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager $data */
            $data = $this->piRequestManagerDataFactory->create();
            $data->setTransactionId($payment->getLastTransId());
            $data->setCres($this->getRequest()->getPost('cres'));
            $data->setVendorName($this->config->getVendorname());
            $data->setMode($this->config->getMode());
            $data->setPaymentAction($this->config->getSagepayPaymentAction());

            $this->requester->setRequestData($data);

            $this->setRequestParamsForConfirmPayment($orderId, $order);

            $response = $this->requester->placeOrder();

            if ($response->getErrorMessage() === null) {
                $this->javascriptRedirect('checkout/onepage/success');
            } else {
                $this->messageManager->addError($response->getErrorMessage());
                $this->javascriptRedirect('checkout/cart');
            }
        } catch (ApiException $apiException) {
            $this->recoverCart->setShouldCancelOrder(true)->execute();
            $this->logger->critical($apiException);
            $this->messageManager->addError($apiException->getUserMessage());
            $this->javascriptRedirect('checkout/cart');
        } catch (\Exception $e) {
            $this->recoverCart->setShouldCancelOrder(true)->execute();
            $this->logger->critical($e);
            $this->messageManager->addError(__("Something went wrong: %1", $e->getMessage()));
            $this->javascriptRedirect('checkout/cart');
        }
    }

    private function javascriptRedirect($url)
    {
        //redirect to success via javascript
        $this
            ->getResponse()
            ->setBody(
                '<script>window.top.location.href = "'
                . $this->_url->getUrl($url, ['_secure' => true])
                . '";</script>'
            );
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

    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function setRequestParamsForConfirmPayment(int $orderId, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $orderId = $this->encryptAndEncode((string)$orderId);
        $quoteId = $this->encryptAndEncode((string)$order->getQuoteId());

        $this->getRequest()->setParams([
                'orderId' => $orderId,
                'quoteId' => $quoteId
            ]);
    }

    /**
     * @param $data
     * @return string
     */
    public function encryptAndEncode($data)
    {
        return $this->cryptAndCode->encryptAndEncode($data);
    }

    /**
     * @param $customerId
     */
    public function logInCustomer($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }
    }
}
