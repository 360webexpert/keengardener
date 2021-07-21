<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;

class OrderUpdateOnCallbackTest extends \PHPUnit\Framework\TestCase
{
    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    private $configMock;

    /** @var OrderSender|\PHPUnit_Framework_MockObject_MockObject */
    private $orderEmailSenderMock;

    /** @var InvoiceSender|\PHPUnit_Framework_MockObject_MockObject */
    private $invoiceEmailSenderMock;

    /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $actionFactoryMock;

    /** @var \Magento\Sales\Model\Order\Payment\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $transactionFactoryMock;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $transactionRepositoryMock;

    /** @var ObjectManagerHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerHelper;



    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->orderEmailSenderMock       = $this->getMockBuilder(OrderSender::class)->disableOriginalConstructor()->getMock();
        $this->invoiceEmailSenderMock = $this->getMockBuilder(InvoiceSender::class)->disableOriginalConstructor()->getMock();
        $this->actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')->disableOriginalConstructor()->getMock();
        $this->transactionFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')->disableOriginalConstructor()->getMock();
        $this->transactionRepositoryMock = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid order. Cant confirm payment.
     */
    public function testConfirmPaymentNoOrderSet()
    {
        /** @var OrderUpdateOnCallback $sut */
        $sut = $this->objectManagerHelper->getObject(
            OrderUpdateOnCallback::class,
            [
                'config' => $this->configMock,
                'orderEmailSender' => $this->orderEmailSenderMock,
                'actionFactory' => $this->actionFactoryMock,
                'transactionFactory' => $this->transactionFactoryMock,
                'transactionRepository' => $this->transactionRepositoryMock,
            ]
        );

        $sut->confirmPayment("test-transaction-id");
    }

    /**
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     * @expectedExceptionMessage Transaction already exists.
     */
    public function testConfirmPaymentGatewayRetry()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('getId')->willReturn(564);

        $this->transactionRepositoryMock->expects($this->once())->method('getByTransactionId')
            ->with("test-transaction-id", 564, 123)->willReturn(
                $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock()
            );

        /** @var OrderUpdateOnCallback $sut */
        $sut = $this->objectManagerHelper->getObject(
            OrderUpdateOnCallback::class,
            [
                'config' => $this->configMock,
                'orderEmailSender' => $this->orderEmailSenderMock,
                'actionFactory' => $this->actionFactoryMock,
                'transactionFactory' => $this->transactionFactoryMock,
                'transactionRepository' => $this->transactionRepositoryMock,
            ]
        );

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getId')->willReturn(123);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $sut->setOrder($orderMock);

        $sut->confirmPayment("test-transaction-id");
    }

    public function testSendInvoiceNotification()
    {
        $formModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(789);
        $paymentMock
            ->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('euroPayment')
            ->willReturn(false);
        $paymentMock
            ->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($formModelMock);
        $paymentMock
            ->expects($this->once())
            ->method('getLastTransId')
            ->willReturn(532);

        $this->transactionRepositoryMock
            ->expects($this->once())
            ->method('getByTransactionId')
            ->with("test-transaction-id", 789, 645)
            ->willReturn(false);

        $this->configMock
            ->expects($this->exactly(2))
            ->method('getSagepayPaymentAction')
            ->willReturn(Config::ACTION_PAYMENT);

        /** @var OrderUpdateOnCallback $sut */
        $sut = $this->objectManagerHelper->getObject(
            OrderUpdateOnCallback::class,
            [
                'config' => $this->configMock,
                'orderEmailSender' => $this->orderEmailSenderMock,
                'invoiceEmailSender' => $this->invoiceEmailSenderMock,
                'actionFactory' => $this->actionFactoryMock,
                'transactionFactory' => $this->transactionFactoryMock,
                'transactionRepository' => $this->transactionRepositoryMock,
            ]
        );

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(645);
        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $orderMock
            ->expects($this->once())
            ->method('place')
            ->willReturnSelf();

        $this->configMock
            ->expects($this->once())
            ->method('getInvoiceConfirmationNotification')
            ->willReturn("1");

        $invoiceCollectionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->setMethods(['setDataToAll', 'save', 'getFirstItem', 'count'])
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceCollectionMock
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $orderMock
            ->expects($this->exactly(2))
            ->method('getInvoiceCollection')
            ->willReturn($invoiceCollectionMock);

        $invoiceMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceCollectionMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($invoiceMock);

        $this->invoiceEmailSenderMock
            ->expects($this->once())
            ->method('send')
            ->with($invoiceMock)
            ->willReturn(true);

        $actionClosedMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config\ClosedForAction::class)
            ->setMethods(['getActionClosedForPaymentAction'])
            ->disableOriginalConstructor()
            ->getMock();
        $actionClosedMock
            ->expects($this->once())
            ->method('getActionClosedForPaymentAction')
            ->willReturn(['capture', true]);

        $this->actionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($actionClosedMock);

        $transactionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->setMethods(['setOrderPaymentObject', 'setTxnId', 'setOrderId', 'setTxnType', 'setPaymentId', 'setIsClosed', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock
            ->expects($this->once())
            ->method('setOrderPaymentObject')
            ->willReturn($paymentMock);
        $transactionMock
            ->expects($this->once())
            ->method('setTxnId')
            ->willReturn(1234567);
        $transactionMock
            ->expects($this->once())
            ->method('setOrderId')
            ->willReturn($orderMock);
        $transactionMock
            ->expects($this->once())
            ->method('setPaymentId')
            ->willReturn($paymentMock);
        $transactionMock
            ->expects($this->once())
            ->method('setIsClosed')
            ->willReturn($actionClosedMock);

        $this->transactionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($transactionMock);

        $transactionMock
            ->expects($this->once())
            ->method('save')
            ->willReturn($transactionMock);

        $invoiceCollectionMock
            ->expects($this->once())
            ->method('setDataToAll')
            ->with('transaction_id', 532)
            ->willReturnSelf();

        $invoiceCollectionMock
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();




        $sut->setOrder($orderMock);

        $sut->confirmPayment("test-transaction-id");
    }
}
