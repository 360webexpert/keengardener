<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ConfigProvider;

class ServerTest extends \PHPUnit\Framework\TestCase
{
    private $serverModelMock;
    private $paymentHelperMocj;
    private $suiteHelperMocj;
    private $customerSessionMock;
    private $tokenModelMock;
    /**
     * @var \Ebizmarts\SagePaySuite\Model\ConfigProvider\Server
     */
    private $serverConfigProviderModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->serverModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Server')->disableOriginalConstructor()->getMock();


        $this->paymentHelperMocj = $this->getMockBuilder('Magento\Payment\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->paymentHelperMocj->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($this->serverModelMock);

        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->suiteHelperMocj = $this->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Data')->disableOriginalConstructor()->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')->disableOriginalConstructor()->getMock();

        $this->tokenModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Token')->disableOriginalConstructor()->getMock();
        $this->tokenModelMock->expects($this->any())
            ->method('getCustomerTokens')
            ->willReturn([
                "token_id" => 1
            ]);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serverConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Server',
            [
                "config"          => $this->configMock,
                "paymentHelper"   => $this->paymentHelperMocj,
                'suiteHelper'     => $this->suiteHelperMocj,
                'customerSession' => $this->customerSessionMock,
                "tokenModel"      => $this->tokenModelMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testMethodNoAvailable()
    {
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);

        $serverModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Server')
            ->disableOriginalConstructor()
            ->getMock();
        $serverModelMock
            ->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $paymentHelperMock = $this
            ->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentHelperMock
            ->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($serverModelMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serverConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Server',
            [
                "paymentHelper"   => $paymentHelperMock,
            ]
        );

        $this->assertEquals([], $this->serverConfigProviderModel->getConfig());
    }

    public function testGetConfig()
    {
        $this->serverModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $this->serverModelMock
            ->expects($this->once())
            ->method('getConfigData')
            ->with('profile')
            ->willReturn('1');

        $this->customerSessionMock->expects($this->never())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->configMock->expects($this->once())
            ->method('isTokenEnabled')
            ->willReturn(false);

        $this->assertEquals(
            [
                'payment' => [
                    'ebizmarts_sagepaysuiteserver' => [
                        'licensed' => null,
                        'token_enabled' => false,
                        'tokens' => null,
                        'max_tokens' => $this->configMock->getMaxTokenPerCustomer(),
                        'mode' => null,
                        'low_profile' => '1'
                    ]
                ]
            ],
            $this->serverConfigProviderModel->getConfig()
        );
    }

    public function testGetConfigWithToken()
    {
        $this->serverModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $this->serverModelMock
            ->expects($this->once())
            ->method('getConfigData')
            ->with('profile')
            ->willReturn('1');

        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->configMock->expects($this->once())
            ->method('isTokenEnabled')
            ->willReturn(true);

        $this->assertEquals(
            [
                'payment' => [
                    'ebizmarts_sagepaysuiteserver' => [
                        'licensed' => null,
                        'token_enabled' => true,
                        'tokens' => [
                            "token_id" => 1
                        ],
                        'max_tokens' => $this->configMock->getMaxTokenPerCustomer(),
                        'mode' => null,
                        'low_profile' => '1'
                    ]
                ]
            ],
            $this->serverConfigProviderModel->getConfig()
        );
    }

    public function testGetConfigWithTokenNoCustomer()
    {
        $this->serverModelMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $this->serverModelMock
            ->expects($this->once())
            ->method('getConfigData')
            ->with('profile')
            ->willReturn('1');

        $this->configMock->expects($this->once())
            ->method('isTokenEnabled')
            ->willReturn(true);

        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(null);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serverConfigProviderModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ConfigProvider\Server',
            [
                "config"          => $this->configMock,
                "paymentHelper"   => $this->paymentHelperMocj,
                'suiteHelper'     => $this->suiteHelperMocj,
                'customerSession' => $this->customerSessionMock,
                "tokenModel"      => $this->tokenModelMock
            ]
        );

        $this->assertEquals(
            [
                'payment' => [
                    'ebizmarts_sagepaysuiteserver' => [
                        'licensed' => null,
                        'token_enabled' => false,
                        'tokens' => null,
                        'max_tokens' => $this->configMock->getMaxTokenPerCustomer(),
                        'mode' => null,
                        'low_profile' => '1'
                    ]
                ]
            ],
            $this->serverConfigProviderModel->getConfig()
        );
    }
}
