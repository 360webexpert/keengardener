<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ConfigProvider;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\ConfigProvider\Form
     */
    private $formConfigProviderModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $formModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $formModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);

        $paymentHelperMock = $this
            ->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentHelperMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($formModelMock);

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
        $this->formConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Form',
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
                    'ebizmarts_sagepaysuiteform' => [
                        'licensed' => null,
                        'mode' => null
                    ],
                ]
            ],
            $this->formConfigProviderModel->getConfig()
        );
    }

    public function testMethodNoAvailable()
    {
        $formModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $formModelMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $paymentHelperMock = $this
            ->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentHelperMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($formModelMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->formConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Form',
            [
                "paymentHelper" => $paymentHelperMock
            ]
        );

        $this->assertEquals([], $this->formConfigProviderModel->getConfig());
    }
}
