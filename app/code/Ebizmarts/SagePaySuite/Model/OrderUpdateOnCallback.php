<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Model\Order\Payment\TransactionFactory;

class OrderUpdateOnCallback
{
    /** @var Order */
    private $order;

    /** @var Config */
    private $config;

    /** @var OrderSender */
    private $orderEmailSender;

    /** @var InvoiceSender */
    private $invoiceEmailSender;

    /** @var ClosedForActionFactory */
    private $actionFactory;

    /** @var TransactionFactory */
    private $transactionFactory;

    /** @var TransactionRepository */
    private $transactionRepository;

    public function __construct(
        Config $config,
        OrderSender $orderEmailSender,
        InvoiceSender $invoiceEmailSender,
        ClosedForActionFactory $actionFactory,
        TransactionFactory $transactionFactory,
        TransactionRepository $transactionRepository
    ) {
        $this->config = $config;
        $this->orderEmailSender = $orderEmailSender;
        $this->invoiceEmailSender = $invoiceEmailSender;
        $this->actionFactory = $actionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param string $transactionId
     * @throws AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function confirmPayment(string $transactionId)
    {
        if ($this->order === null) {
            throw new \Exception("Invalid order. Cant confirm payment.");
        }

        $payment = $this->order->getPayment();

        $transactionExists = $this->transactionRepository
            ->getByTransactionId($transactionId, $payment->getId(), $this->order->getId());
        if ($transactionExists !== false) {
            throw new AlreadyExistsException(__('Transaction already exists.'));
        }

        $sagePayPaymentAction = $this->config->getSagepayPaymentAction();
        if ($sagePayPaymentAction != 'DEFERRED' && $sagePayPaymentAction != 'AUTHENTICATE') {
            $payment->getMethodInstance()->markAsInitialized();
        }

        $this->order->place()->save();

        if ((bool)$payment->getAdditionalInformation('euroPayment') !== true) {
            //don't send email if EURO PAYMENT as it was already sent
            $this->orderEmailSender->send($this->order);
            $this->sendInvoiceNotification();
        }

        /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
        $actionClosed = $this->actionFactory->create(['paymentAction' => $sagePayPaymentAction]);
        list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

        /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($transactionId);
        $transaction->setOrderId($this->order->getEntityId());
        $transaction->setTxnType($action);
        $transaction->setPaymentId($payment->getId());
        $transaction->setIsClosed($closed);
        $transaction->save();

        //update invoice transaction id
        $this->order->getInvoiceCollection()
            ->setDataToAll('transaction_id', $payment->getLastTransId())
            ->save();
    }

    public function sendInvoiceNotification()
    {
        if ($this->invoiceConfirmationIsEnable() && $this->paymentActionIsCapture()) {
            $invoices = $this->order->getInvoiceCollection();
            if ($invoices->count() > 0) {
                $this->invoiceEmailSender->send($invoices->getFirstItem());
            }
        }
    }

    /**
     * @return bool
     */
    private function paymentActionIsCapture()
    {
        $sagePayPaymentAction = $this->config->getSagepayPaymentAction();
        return $sagePayPaymentAction === Config::ACTION_PAYMENT;
    }

    /**
     * @return bool
     */
    private function invoiceConfirmationIsEnable()
    {
        return (string)$this->config->getInvoiceConfirmationNotification() === "1";
    }
}
