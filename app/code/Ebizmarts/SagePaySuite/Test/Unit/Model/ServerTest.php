<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Ebizmarts\SagePaySuite\Model\Api\PaymentOperations;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Server;

class ServerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var Server
     */
    private $serverModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    private $paymentOpsMock;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Shared|\PHPUnit_Framework_MockObject_MockObject
     */

    private $objectManagerHelper;

    private $suiteHelperMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentOpsMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serverModel = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Server',
            [
                "config"     => $this->configMock,
                "paymentOps" => $this->paymentOpsMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testCapture()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentOpsMock
            ->expects($this->once())
            ->method('capture')
            ->with($paymentMock, 100);

        $this->serverModel->capture($paymentMock, 100);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error.
     */
    public function testCaptureERROR()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase("There was an error.")
        );

        $this->paymentOpsMock
            ->expects($this->once())
            ->method('capture')
            ->with($paymentMock, 100)
            ->willThrowException($exception);

        $this->serverModel->capture($paymentMock, 100);
    }

    public function testMarkAsInitialized()
    {
        $this->serverModel->markAsInitialized();
        $this->assertEquals(
            false,
            $this->serverModel->isInitializeNeeded()
        );
    }

    public function testRefund()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentOpsMock
            ->expects($this->once())
            ->method('refund')
            ->with($paymentMock, 100);

        $this->serverModel->refund($paymentMock, 100);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error in Refunding.
     */
    public function testRefundERROR()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase("Error in Refunding.")
        );

        $this->paymentOpsMock
            ->expects($this->once())
            ->method('refund')
            ->with($paymentMock, 100)
            ->willThrowException($exception);

        $this->serverModel->refund($paymentMock, 100);
    }

    public function testCancel()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $serverModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Server')
            ->setMethods(['void', 'canVoid'])
            ->disableOriginalConstructor()
            ->getMock();
        $serverModelMock
            ->expects($this->once())
            ->method('canVoid')
            ->willReturn(true);
        $serverModelMock
            ->expects($this->once())
            ->method('void')
            ->willReturnSelf();

        $serverModelMock->cancel($paymentMock);
    }

    public function testCancelError()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $serverModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Server')
            ->setMethods(['void', 'canVoid'])
            ->disableOriginalConstructor()
            ->getMock();
        $serverModelMock
            ->expects($this->once())
            ->method('canVoid')
            ->willReturn(false);
        $serverModelMock
            ->expects($this->never())
            ->method('void');

        $serverModelMock->cancel($paymentMock);
    }

    public function testAbortDeferTransaction()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $sharedApiMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Api\Shared')
            ->disableOriginalConstructor()
            ->getMock();

        $transactionDetails            = new \stdClass;
        $transactionDetails->txstateid = PaymentOperations::DEFERRED_AWAITING_RELEASE;

        $reportingApiMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Api\Reporting')
            ->disableOriginalConstructor()
            ->getMock();
        $reportingApiMock
            ->expects($this->once())
            ->method('getTransactionDetailsByVpstxid')
            ->willReturn($transactionDetails);

        $this->serverModel = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Server',
            [
                "config"     => $this->configMock,
                "paymentOps" => $this->paymentOpsMock,
                "sharedApi"    => $sharedApiMock,
                "reportingApi" => $reportingApiMock
            ]
        );
        $orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $orderMock
            ->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);
        $paymentMock
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $this->serverModel->void($paymentMock);
    }

    public function testVoid()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $sharedApiMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Api\Shared')
            ->disableOriginalConstructor()
            ->getMock();

        $transactionDetails            = new \stdClass;
        $transactionDetails->txstateid = PaymentOperations::SUCCESSFULLY_AUTHORISED;

        $reportingApiMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Api\Reporting')
            ->disableOriginalConstructor()
            ->getMock();
        $reportingApiMock
            ->expects($this->once())
            ->method('getTransactionDetailsByVpstxid')
            ->willReturn($transactionDetails);

        $this->serverModel = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Server',
            [
                "config"     => $this->configMock,
                "paymentOps" => $this->paymentOpsMock,
                "sharedApi"    => $sharedApiMock,
                "reportingApi" => $reportingApiMock
            ]
        );

        $orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $paymentMock
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->serverModel->void($paymentMock);
    }

    public function testInitialize()
    {
        $orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('setCanSendNewEmailFlag')
            ->with(false);

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $stateMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(["offsetExists","offsetGet","offsetSet","offsetUnset","setStatus","setIsNotified"])
            ->disableOriginalConstructor()
            ->getMock();
        $stateMock->expects($this->once())
            ->method('setIsNotified')
            ->with(false);

        $this->serverModel->setInfoInstance($paymentMock);
        $this->serverModel->initialize("", $stateMock);
    }

    public function testGetConfigPaymentAction()
    {
        $this->configMock->expects($this->once())
            ->method('getPaymentAction');
        $this->serverModel->getConfigPaymentAction();
    }
}
