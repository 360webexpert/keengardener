<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class PaypalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Paypal
     */
    private $paypalModel;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    private $paymentOpsMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentOpsMock = $this->createMock(\Ebizmarts\SagePaySuite\Model\Payment::class);

        $this->paypalModel = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Paypal::class)
            ->setConstructorArgs(
                [
                    'context' => $this->createMock(\Magento\Framework\Model\Context::class),
                    'registry' => $this->createMock(\Magento\Framework\Registry::class),
                    'extensionFactory' => $this->createMock('\Magento\Framework\Api\ExtensionAttributesFactory'),
                    'customAttributeFactory' => $this->createMock('\Magento\Framework\Api\AttributeValueFactory'),
                    'paymentOps' => $this->paymentOpsMock,
                    'paymentData' => $this->createMock(\Magento\Payment\Helper\Data::class),
                    'scopeConfig' => $this->createMock('\Magento\Framework\App\Config\ScopeConfigInterface'),
                    'logger' => $this->createMock(\Magento\Payment\Model\Method\Logger::class),
                    'config' => $this->configMock,
                    'resource' => null,
                    'resourceCollection' => null,
                    'data' => [],
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
        $paymentMock->expects($this->any())
            ->method('getLastTransId')
            ->willReturn(1);
        $paymentMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->with('paymentAction')
            ->willReturn(Config::ACTION_DEFER);

        $this->paymentOpsMock->expects($this->once())->method('capture')->with($paymentMock, 100);

        $this->paypalModel
            ->setMethodsExcept(['capture'])
            ->getMock()->capture($paymentMock, 100);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error capturing Sage Pay transaction 11: 22
     */
    public function testCaptureError()
    {
        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('getLastTransId')
            ->will($this->returnValue(2));
        $paymentMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->with('paymentAction')
            ->will($this->returnValue(Config::ACTION_AUTHENTICATE));

        $this->paymentOpsMock->expects($this->once())->method('capture')->with($paymentMock, 100)
        ->willThrowException(
            new LocalizedException(__('There was an error capturing Sage Pay transaction 11: 22'))
        );

        $this->paypalModel
            ->setMethodsExcept(['capture'])
            ->getMock()->capture($paymentMock, 100);
    }

    public function testRefund()
    {
        $paymentMock = $this->createMock('Magento\Sales\Model\Order\Payment');

        $this->paymentOpsMock->expects($this->once())->method('refund')->with($paymentMock, 100);

        $this->assertInstanceOf(\Ebizmarts\SagePaySuite\Model\Paypal::class, $this->paypalModel
            ->setMethodsExcept(['refund'])
            ->getMock()->refund($paymentMock, 100));
    }

    /**
     * @expectedExceptionMessage There was an error refunding Sage Pay transaction
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testRefundError()
    {
        $paymentMock = $this->createMock('Magento\Sales\Model\Order\Payment');

        $this->paymentOpsMock->expects($this->once())->method('refund')->with($paymentMock, 100)
            ->willThrowException(
                new LocalizedException(__('There was an error refunding Sage Pay transaction '.self::TEST_VPSTXID))
            );

        $this->paypalModel
            ->setMethodsExcept(['refund'])
            ->getMock()
            ->refund($paymentMock, 100);
    }

    public function testGetConfigPaymentAction()
    {
        $this->configMock->expects($this->once())->method('getPaymentAction');

        $this->paypalModel->setMethodsExcept(['getConfigPaymentAction'])->getMock()->getConfigPaymentAction();
    }
}
