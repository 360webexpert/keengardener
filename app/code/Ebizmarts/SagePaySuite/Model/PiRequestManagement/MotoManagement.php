<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/27/17
 * Time: 12:18 PM
 */

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Exception;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Magento\Sales\Model\Order\Payment\TransactionFactory;

class MotoManagement extends RequestManagement
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var RequestInterface */
    private $httpRequest;

    /** @var UrlInterface */
    private $backendUrl;
    private $logger;

    /** @var \Magento\Sales\Model\Order */
    private $order;

    /** @var EmailSender */
    private $emailSender;

    private $actionFactory;

    /** @var TransactionFactory */
    private $transactionFactory;

    public function __construct(
        Checkout $checkoutHelper,
        PIRest $piRestApi,
        SagePayCardType $ccConvert,
        PiRequest $piRequest,
        Data $suiteHelper,
        PiResultInterface $result,
        ObjectManagerInterface $objectManager,
        RequestInterface $httpRequest,
        UrlInterface $backendUrl,
        Logger $suiteLogger,
        EmailSender $emailSender,
        ClosedForActionFactory $actionFactory,
        TransactionFactory $transactionFactory
    ) {
        parent::__construct(
            $checkoutHelper,
            $piRestApi,
            $ccConvert,
            $piRequest,
            $suiteHelper,
            $result
        );
        $this->objectManager = $objectManager;
        $this->httpRequest   = $httpRequest;
        $this->backendUrl    = $backendUrl;
        $this->logger        = $suiteLogger;
        $this->emailSender   = $emailSender;
        $this->actionFactory      = $actionFactory;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getIsMotoTransaction()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function placeOrder()
    {
        try {
            $order = $this
                ->getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->httpRequest->getPost('order'));

            if ($paymentData = $this->httpRequest->getPost('payment')) {
                $paymentData["cc_type"] = $this->ccConverter->convert($this->getRequestData()->getCcType());
                $this->getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $this->getOrderCreateModel()->getQuote()->collectTotals();

            $order->setSendConfirmation(0);
            $order = $order->createOrder();

            if ($order) {
                $this->order = $order;

                $this->pay();

                $this->processPayment();

                $this->_confirmPayment($order);

                $this->emailSender->send($order);

                //add success url to response
                $url = $this->backendUrl->getUrl('sales/order/view', ['order_id' => $order->getId()]);

                $this->getResult()->setSuccess(true);
                $this->getResult()->setResponse($url);
            } else {
                throw new Exception(__('Unable to save Opayo order.'));
            }
        } catch (ApiException $apiException) {
            $this->logger->logException($apiException, [__METHOD__, __LINE__]);
            $this->getResult()->setSuccess(false);
            $this->getResult()->setErrorMessage(__("Something went wrong: %1", $apiException->getUserMessage()));
        } catch (\Exception $e) {
            $this->logger->logException($e, [__METHOD__, __LINE__]);
            $this->getResult()->setSuccess(false);
            $this->getResult()->setErrorMessage(__("Something went wrong: %1", $e->getMessage()));
        }

        return $this->getResult();
    }

    public function getPayment()
    {
        return $this->order->getPayment();
    }

    /**
     * Retrieve order create model
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    private function getOrderCreateModel()
    {
        return $this->objectManager->get('Magento\Sales\Model\AdminOrder\Create');
    }

    private function _confirmPayment($order)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($this->getPayResult()->getTransactionId());
        $payment->setLastTransId($this->getPayResult()->getTransactionId());

        //leave transaction open in case defer or authorize
        if ($this->isPaymentActionDeferred()) {
            $payment->setIsTransactionClosed(0);
        }

        $payment->save();

        $paymentAction = $this->getRequestData()->getPaymentAction();
        if ($paymentAction === Config::ACTION_PAYMENT_PI) {
            $payment->getMethodInstance()->markAsInitialized();
        }

        $order->place()->save();

        if ($this->isPaymentActionDeferred()) {
            /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
            $actionClosed = $this->actionFactory->create(['paymentAction' => $paymentAction]);
            list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

            /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $this->transactionFactory->create();
            $transaction->setOrderPaymentObject($payment);
            $transaction->setTxnId($this->getPayResult()->getTransactionId());
            $transaction->setOrderId($order->getEntityId());
            $transaction->setTxnType($action);
            $transaction->setPaymentId($payment->getId());
            $transaction->setIsClosed($closed);
            $transaction->save();
        }
    }

    /**
     * @return bool
     */
    private function isPaymentActionDeferred()
    {
        return $this->getRequestData()->getPaymentAction() == Config::ACTION_DEFER_PI;
    }
}
