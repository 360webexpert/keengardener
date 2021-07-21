<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\Session as SagePaySession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class RecoverCart
{
    const ORDER_ERROR_MESSAGE   = "Order not available";
    const QUOTE_ERROR_MESSAGE   = "Quote not available";
    const GENERAL_ERROR_MESSAGE = "Not possible to recover quote";

    /** @var Session */
    private $checkoutSession;

    /** @var Logger */
    private $suiteLogger;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var bool */
    private $_shouldCancelOrder;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * RecoverCart constructor.
     * @param Session $checkoutSession
     * @param Logger $suiteLogger
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteFactory $quoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $checkoutSession,
        Logger $suiteLogger,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->checkoutSession   = $checkoutSession;
        $this->suiteLogger       = $suiteLogger;
        $this->orderRepository   = $orderRepository;
        $this->quoteFactory      = $quoteFactory;
        $this->quoteRepository   = $quoteRepository;
        $this->messageManager    = $messageManager;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $order = $this->getOrder();

        if ($this->verifyIfOrderIsValid($order)) {
            $quote = $this->checkoutSession->getQuote();
            if (!empty($quote)) {
                if ($this->_shouldCancelOrder) {
                    $this->tryCancelOrder($order);
                }
                try {
                    $this->cloneQuoteAndReplaceInSession($order);
                } catch (LocalizedException $e) {
                    $this->logExceptionAndShowError(self::GENERAL_ERROR_MESSAGE, $e);
                } catch (NoSuchEntityException $e) {
                    $this->logExceptionAndShowError(self::GENERAL_ERROR_MESSAGE, $e);
                }
                $this->removeFlag();
            } else {
                $this->addError(self::QUOTE_ERROR_MESSAGE);
            }
        } else {
            $this->addError(self::ORDER_ERROR_MESSAGE);
        }
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function cloneQuoteAndReplaceInSession(OrderInterface $order)
    {
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $items = $quote->getAllVisibleItems();
        $customer = $quote->getCustomer();

        $newQuote = $this->quoteFactory->create();
        $newQuote->setStoreId($quote->getStoreId());
        $newQuote->setIsActive(1);
        $newQuote->setReservedOrderId(null);
        $newQuote->setCustomer($customer);

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId(), false, $quote->getStoreId(), true);
                $request = $item->getBuyRequest();

                $newQuote->addProduct($product, $request);
            } catch (NoSuchEntityException $e) {
                $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
            }
        }

        $shippingAddress = $newQuote->getShippingAddress();
        $shippingAddress->unsetData('cached_items_all');   
        $newQuote->collectTotals();
        $this->quoteRepository->save($newQuote);

        $this->checkoutSession->replaceQuote($newQuote);
    }

    /**
     * @return OrderInterface|null
     */
    private function getOrder()
    {
        /** Get order if it was pre-saved but not completed */
        $presavedOrderId = $this->checkoutSession->getData(SagePaySession::PRESAVED_PENDING_ORDER_KEY);

        if (!empty($presavedOrderId)) {
            $order = $this->orderRepository->get($presavedOrderId);
        } else {
            $order = null;
        }

        return $order;
    }

    /**
     * @param $order
     * @return bool
     */
    private function verifyIfOrderIsValid($order)
    {
        return $order !== null &&
            $order->getId() !== null;
    }

    private function removeFlag()
    {
        $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);
        $this->checkoutSession->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);
    }

    /**
     * @param bool $shouldCancelOrder
     * @return $this
     */
    public function setShouldCancelOrder(bool $shouldCancelOrder)
    {
        $this->_shouldCancelOrder = $shouldCancelOrder;
        return $this;
    }

    /**
     * @param $message
     */
    private function addError($message)
    {
        $this->removeFlag();
        $this->messageManager->addError(__($message));
    }

    /**
     * @param $message
     * @param $exception
     */
    private function logExceptionAndShowError($message, $exception)
    {
        $this->addError($message);
        $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getTraceAsString(), [__METHOD__, __LINE__]);
    }

    /**
     * @param OrderInterface $order
     */
    private function tryCancelOrder(OrderInterface $order)
    {
        try {
            $state = $order->getState();
            if ($state === Order::STATE_PENDING_PAYMENT) {
                //The order might be cancelled on Controller/Server/Notify. This checks if the order is not cancelled before trying to cancel it.
                $order->cancel()->save();
            } elseif ($state !== Order::STATE_CANCELED) {
                $this->suiteLogger->sageLog(Logger::LOG_REQUEST, "Incorrect state found on order " . $order->getIncrementId() . " when trying to cancel it. State found: " . $state, [__METHOD__, __LINE__]);
            }
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
        }
    }
}
