<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ConfigProvider;

class PaypalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\ConfigProvider\Paypal
     */
    private $paypalConfigProviderModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $paypalModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Paypal')
            ->disableOriginalConstructor()
            ->getMock();
        $paypalModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);

        $paymentHelperMock = $this
            ->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentHelperMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($paypalModelMock);

        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $suiteHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $suiteHelperMock->expects($this->any())
            ->method('getSagePayConfig')
            ->willReturn($this->configMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paypalConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Paypal',
            [
                "paymentHelper" => $paymentHelperMock,
                'suiteHelper' => $suiteHelperMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testGetConfig()
    {
        $this->assertEquals(
            [
                'payment' => [
                    'ebizmarts_sagepaysuitepaypal' => [
                        'licensed' => null,
                        'mode' => null
                    ],
                ]
            ],
            $this->paypalConfigProviderModel->getConfig()
        );
    }

    public function testMethodNotAvailable()
    {
        $paypalModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Paypal')
            ->disableOriginalConstructor()
            ->getMock();
        $paypalModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(false);

        $paymentHelperMock = $this
            ->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentHelperMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($paypalModelMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paypalConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Paypal',
            [
                "paymentHelper" => $paymentHelperMock,
            ]
        );

        $this->assertEquals([], $this->paypalConfigProviderModel->getConfig());
    }
}
