<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Helper;

use Ebizmarts\SagePaySuite\Helper\Data;
use \Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class DataTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;
    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    private $moduleVersionMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
        ->setMethods(['gmtTimestamp', 'gmtDate'])
        ->disableOriginalConstructor()
        ->getMock();
        $dateTimeMock->expects($this->any())->method('gmtTimestamp')->willReturn('1456419355');
        $dateTimeMock->expects($this->any())->method('gmtDate')->willReturn('2016-02-25-085555');

        $this->moduleVersionMock = $this->getMockBuilder(ModuleVersion::class)->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->dataHelper = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Data',
            [
                'config'   => $this->configMock,
                'dateTime' => $dateTimeMock,
                'moduleVersion' => $this->moduleVersionMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testClearTransactionId()
    {
        $uncleanTransactionId = self::TEST_VPSTXID . "-capture";

        $this->assertSame(
            self::TEST_VPSTXID,
            $this->dataHelper->clearTransactionId($uncleanTransactionId)
        );
    }

    public function testObtainMajorAndMinorVersionFromVersionNumber()
    {
        $this->assertEquals("1.1", $this->dataHelper->obtainMajorAndMinorVersionFromVersionNumber("1.1.0"));
        $this->assertEquals("1.3", $this->dataHelper->obtainMajorAndMinorVersionFromVersionNumber("1.3.1"));
        $this->assertEquals("2.1", $this->dataHelper->obtainMajorAndMinorVersionFromVersionNumber("2.1.41"));
        $this->assertEquals("100.1", $this->dataHelper->obtainMajorAndMinorVersionFromVersionNumber("100.1.0"));
    }

    /**
     * @dataProvider verifyDataProvider
     */
    public function testVerify($data)
    {
        $this->moduleVersionMock
            ->expects($this->once())
            ->method('getModuleVersion')
            ->with('Ebizmarts_SagePaySuite')
            ->willReturn($data['Ebizmarts_SagePaySuite']['setup_version']);

        $this->configMock->expects($this->any())
            ->method('getStoreDomain')
            ->will($this->returnValue("http://www.example.com"));
        $this->configMock->expects($this->any())
            ->method('getLicense')
            ->will($this->returnValue("010b6116a7a99954fd2f3ad27e9706b2b5f5f51c"));

        $this->assertEquals(
            $data['expected'],
            $this->dataHelper->verify()
        );
    }

    public function verifyDataProvider()
    {
        return [
            'test normal' => [
                [
                    'Ebizmarts_SagePaySuite' => [
                        'setup_version' => '2.0.1'
                    ],
                    'expected' => false
                ]
            ],
            'test invalid' => [
                [
                    'Ebizmarts_SagePaySuite' => [
                        'setup_version' => '1.0.3'
                    ],
                    'expected' => true
                ]
            ]
        ];
    }

    /**
     * @dataProvider generateVendorTxCodeDataProvider
     */
    public function testGenerateVendorTxCode($data)
    {
        $this->assertEquals(
            $data['expected'],
            $this->dataHelper->generateVendorTxCode($data['order_id'], $data['action'])
        );
    }

    public function generateVendorTxCodeDataProvider()
    {
        return [
            'test PAYMENT' => [
                [
                    'order_id' => '1000000000001',
                    'action'   => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT,
                    'expected' => '1000000000001-2016-02-25-085555145641935'
                ]
            ],
            'test REFUND' => [
                [
                    'order_id' => '1000000000002',
                    'action'   => \Ebizmarts\SagePaySuite\Model\Config::ACTION_REFUND,
                    'expected' => 'R1000000000002-2016-02-25-08555514564193'
                ]
            ],
            'test AUTHORISE' => [
                [
                    'order_id' => '1000000000004',
                    'action'   => 'AUTHORISE',
                    'expected' => 'A1000000000004-2016-02-25-08555514564193'
                ]
            ],
            'test REPEAT' => [
                [
                    'order_id' => '100000005687',
                    'action'   => 'REPEAT',
                    'expected' => 'RT100000005687-2016-02-25-08555514564193'
                ]
            ],
            'test REPEAT PI' => [
                [
                    'order_id' => '100000005688',
                    'action'   => 'Repeat',
                    'expected' => 'RT100000005688-2016-02-25-08555514564193'
                ]
            ],
            'test REPEATDEFERRED' => [
                [
                    'order_id' => '000000004',
                    'action'   => 'REPEATDEFERRED',
                    'expected' => 'RT000000004-2016-02-25-0855551456419355'
                ]
            ],
            'test PAYMENT with prefix' => [
                [
                    'order_id' => 'EBIZ#10=0+0.0-0_0{0}010',
                    'action'   => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT,
                    'expected' => 'EBIZ1000.0-0_0{0}010-2016-02-25-08555514'
                ]
            ]
        ];
    }

    public function testGetSagePayConfig()
    {
        $this->dataHelper = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Data',
            [
                'config'   => $this->configMock,
            ]
        );

        $this->assertInstanceOf('\Ebizmarts\SagePaySuite\Model\Config', $this->dataHelper->getSagePayConfig());
    }

    public function testRemoveCurlyBraces()
    {
        $this->dataHelper = $this->objectManagerHelper->getObject('Ebizmarts\SagePaySuite\Helper\Data');

        $t1 = "{asd-asd}";

        $this->assertEquals("asd-asd", $this->dataHelper->removeCurlyBraces($t1));
    }

    /**
     * @param $bool
     * @param $code
     * @dataProvider methodCodeIsSagePayProvider
     */
    public function testMethodCodeIsSagePay($bool, $code)
    {
        $this->dataHelper = $this->objectManagerHelper->getObject('Ebizmarts\SagePaySuite\Helper\Data');

        //this method does not test for PI because its not needed on the creditmemo.
        $this->assertEquals($bool, $this->dataHelper->methodCodeIsSagePay($code));
    }

    public function methodCodeIsSagePayProvider()
    {
        return [
            [true, 'sagepaysuiteform'],
            [false, 'authorize_net'],
            [true, 'sagepaysuiterepeat'],
            [true, 'sagepaysuiteserver'],
            [false, 'paypal'],
            [true, 'sagepaysuitepaypal'],
            [false, 'sagepaydirectpro'],
            [false, 'sagepaysuitepi']
        ];
    }

    public function testObtainAdminConfigurationScopeCodeFromRequestDefault()
    {
        $requestObjectMock = $this
            ->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestObjectMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['store'], ['website'])
            ->willReturnOnConsecutiveCalls(null, null);

        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['getStoreId', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestObjectMock);

        $this->assertEquals(
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $dataHelperMock->obtainAdminConfigurationScopeCodeFromRequest()
        );
    }

    public function testObtainAdminConfigurationScopeCodeFromRequestWebsite()
    {
        $requestObjectMock = $this
            ->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestObjectMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['store'], ['website'])
            ->willReturnOnConsecutiveCalls(null, 'website');

        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['getStoreId', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestObjectMock);

        $this->assertEquals(
            ScopeInterface::SCOPE_WEBSITE,
            $dataHelperMock->obtainAdminConfigurationScopeCodeFromRequest()
        );
    }

    public function testObtainAdminConfigurationScopeCodeFromRequestStore()
    {
        $requestObjectMock = $this
            ->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestObjectMock
            ->expects($this->once())
            ->method('getParam')
            ->withConsecutive(['store'])
            ->willReturnOnConsecutiveCalls('store');

        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['getStoreId', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestObjectMock);

        $this->assertEquals(
            ScopeInterface::SCOPE_STORE,
            $dataHelperMock->obtainAdminConfigurationScopeCodeFromRequest()
        );
    }

    /**
     * @dataProvider adminScopeIdProvider
     */
    public function testObtainAdminConfigurationScopeIdFromRequest($data)
    {
        $requestObjectMock = $this
            ->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(
                [
                    'getStoreId',
                    'getRequest',
                    'obtainConfigurationScopeCodeFromRequest',
                    'isConfigurationScopeStore',
                    'isConfigurationScopeWebsite'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestObjectMock);

        $dataHelperMock
            ->expects($this->once())
            ->method('obtainConfigurationScopeCodeFromRequest')
            ->willReturn($data['scopeCode']);

        $dataHelperMock
            ->expects($this->exactly($data['expectsIsConfigurationScopeStore']))
            ->method('isConfigurationScopeStore')
            ->with($data['scopeCode'])
            ->willReturn($data['isConfigurationScopeStore']);

        $dataHelperMock
            ->expects($this->exactly($data['expectsIsConfigurationScopeWebsite']))
            ->method('isConfigurationScopeWebsite')
            ->with($data['scopeCode'])
            ->willReturn($data['isConfigurationScopeWebsite']);

        $requestObjectMock
            ->expects($this->exactly($data['expectsGetParam']))
            ->method('getParam')
            ->willReturn($data['scopeId']);

        $this->assertEquals(
            $data['scopeId'],
            $dataHelperMock->obtainAdminConfigurationScopeIdFromRequest()
        );
    }

    public function adminScopeIdProvider()
    {
        return [
            'test default config' => [
                [
                    'scopeCode' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'expectsIsConfigurationScopeStore' => 1,
                    'expectsIsConfigurationScopeWebsite' => 1,
                    'expectsGetParam' => 0,
                    'scopeId' => Store::DEFAULT_STORE_ID,
                    'isConfigurationScopeStore' => false,
                    'isConfigurationScopeWebsite' => false
                ]
            ],
            'test store' => [
                [
                    'scopeCode' => ScopeInterface::SCOPE_STORE,
                    'expectsIsConfigurationScopeStore' => 1,
                    'expectsIsConfigurationScopeWebsite' => 0,
                    'expectsGetParam' => 1,
                    'scopeId' => 1,
                    'isConfigurationScopeStore' => true,
                    'isConfigurationScopeWebsite' => false
                ]
            ],
            'test website' => [
                [
                    'scopeCode' => ScopeInterface::SCOPE_WEBSITE,
                    'expectsIsConfigurationScopeStore' => 1,
                    'expectsIsConfigurationScopeWebsite' => 1,
                    'expectsGetParam' => 1,
                    'scopeId' => 1,
                    'isConfigurationScopeStore' => false,
                    'isConfigurationScopeWebsite' => true
                ]
            ]
        ];
    }

    public function testObtainConfigurationScopeCodeFromRequest() {
        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(
                [
                    'getAreaCode'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Data::FRONTEND);

        $this->assertEquals(
            ScopeInterface::SCOPE_STORE,
            $dataHelperMock->obtainConfigurationScopeCodeFromRequest()
        );
    }

    public function testObtainConfigurationScopeIdFromRequest() {
        $dataHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(
                [
                    'getAreaCode',
                    'getStoreId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelperMock
            ->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Data::FRONTEND);

        $dataHelperMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertEquals(
            1,
            $dataHelperMock->obtainConfigurationScopeIdFromRequest()
        );
    }
}
