<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use const DIRECTORY_SEPARATOR;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $configModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->scopeConfigMock = $this
            ->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $storerMock = $this
            ->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $storerMock->expects($this->any())
            ->method("getBaseCurrencyCode")
            ->willReturn("USD");
        $storerMock->expects($this->any())
            ->method("getDefaultCurrencyCode")
            ->willReturn("EUR");
        $storerMock->expects($this->any())
            ->method("getCurrentCurrencyCode")
            ->willReturn("GBP");
        $storerMock->expects($this->any())
            ->method("getId")
            ->willReturn(1);

        $storeManagerMock = $this
            ->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->any())
            ->method("getStore")
            ->willReturn($storerMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config',
            [
                'scopeConfig'  => $this->scopeConfigMock,
                'storeManager' => $storeManagerMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testSetConfigurationStoreId()
    {
        $this->configModel->setConfigurationScopeId(59);
        \PHPUnit\Framework\Assert::assertAttributeEquals(59, "configurationScopeId", $this->configModel);
    }

    public function testIsMethodActiveMoto()
    {
        $this->configModel->setMethodCode('sagepaysuiteform');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/sagepaysuiteform/active_moto',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->isMethodActiveMoto()
        );
    }

    public function testDevMinify()
    {
        $configFilePath = BP . DIRECTORY_SEPARATOR . 'app/code/Ebizmarts/SagePaySuite/etc/config.xml';
        $xmlData = \file_get_contents($configFilePath); //@codingStandardsIgnoreLine
        $xml = new \SimpleXMLElement($xmlData);
        $this->assertObjectHasAttribute('dev', $xml->default);
        $this->assertEquals($xml->default->dev->js->minify_exclude->sagepaysuitepi, "sagepay");
    }
    
    public function testIsMethodActive()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM . '/active',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->isMethodActive()
        );
    }

    public function testGetVPSProtocol()
    {
        $this->assertEquals(
            \Ebizmarts\SagePaySuite\Model\Config::VPS_PROTOCOL,
            $this->configModel->getVPSProtocol()
        );
    }

    public function testGetFormSendEmail()
    {
        $this->configModel->setMethodCode('sagepaysuiteform');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/sagepaysuiteform/send_email',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(1);

        $this->assertEquals(1, $this->configModel->getFormSendEmail());
    }

    public function testIsPaypalForceXml()
    {
        $this->configModel->setMethodCode('sagepaysuitepaypal');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/sagepaysuitepaypal/force_xml',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(1);

        $this->assertEquals(1, $this->configModel->isPaypalForceXml());
    }

    public function testGetFormVendorEmail()
    {
        $this->configModel->setMethodCode('sagepaysuiteform');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/sagepaysuiteform/vendor_email',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn("me@example.com:me+1@example.com");

        $this->assertEquals("me@example.com:me+1@example.com", $this->configModel->getFormVendorEmail());
    }

    public function testGetFormEmailMessage()
    {
        $this->configModel->setMethodCode('sagepaysuiteform');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/sagepaysuiteform/email_message',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.");

        $this->assertEquals(
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.",
            $this->configModel->getFormEmailMessage()
        );
    }

    /**
     * @dataProvider getSagepayPaymentActionDataProvider
     */
    public function testGetSagepayPaymentAction($data)
    {
        $this->configModel->setMethodCode($data["code"]);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . $data["code"] . '/payment_action',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn($data["payment_action"]);

        $this->assertEquals(
            $data["expect"],
            $this->configModel->getSagepayPaymentAction()
        );
    }

    public function getSagepayPaymentActionDataProvider()
    {
        return [
            'test with pi' => [
                [
                    'code'           => \Ebizmarts\SagePaySuite\Model\Config::METHOD_PI,
                    'payment_action' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT_PI,
                    'expect'         => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT_PI
                ]
            ],
            'test without form' => [
                [
                    'code'           => \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM,
                    'payment_action' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT,
                    'expect'         => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT
                ]
            ]
        ];
    }

    /**
     * @dataProvider getPaymentActionDataProvider
     */
    public function testGetPaymentAction($data)
    {
        $this->configModel->setMethodCode($data["code"]);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . $data["code"] . '/payment_action',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn($data["payment_action"]);

        $this->assertEquals(
            $data["expect"],
            $this->configModel->getPaymentAction()
        );
    }

    public function getPaymentActionDataProvider()
    {
        return [
            'test with payment' => [
                [
                    'code' => \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM,
                    'payment_action' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT,
                    'expect' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE
                ]
            ],
            'test without defer' => [
                [
                    'code' => \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM,
                    'payment_action' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_DEFER,
                    'expect' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE
                ]
            ],
            'test default' => [
                [
                    'code' => \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM,
                    'payment_action' => 'authorize_capture',
                    'expect' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE
                ]
            ]
        ];
    }

    public function testGetVendorname()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/vendorname',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('testebizmarts');

        $this->assertEquals(
            'testebizmarts',
            $this->configModel->getVendorname()
        );
    }

    public function testGetLicense()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/license',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('f678dfs786fds786dfs876dfs');

        $this->assertEquals(
            'f678dfs786fds786dfs876dfs',
            $this->configModel->getLicense()
        );
    }

    public function testGetStoreDomain()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL,
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('http://example.com');

        $this->assertEquals(
            'http://example.com',
            $this->configModel->getStoreDomain()
        );
    }

    public function testGetMode()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/mode',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('live');

        $this->assertEquals(
            'live',
            $this->configModel->getMode()
        );
    }

    public function testIsTokenEnabled()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/token',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->isTokenEnabled()
        );
    }

    public function testIsSagePaySuiteMethod()
    {
        $this->assertEquals(
            true,
            $this->configModel->isSagePaySuiteMethod(Config::METHOD_SERVER)
        );

        $this->assertEquals(
            true,
            $this->configModel->isSagePaySuiteMethod(Config::METHOD_PI)
        );

        $this->assertEquals(
            true,
            $this->configModel->isSagePaySuiteMethod(Config::METHOD_FORM)
        );

        $this->assertEquals(
            true,
            $this->configModel->isSagePaySuiteMethod(Config::METHOD_PAYPAL)
        );
    }

    public function testGetFormEncryptedPassword()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM . '/encrypted_password',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('345jh345hj45');

        $this->assertEquals(
            '345jh345hj45',
            $this->configModel->getFormEncryptedPassword()
        );
    }

    public function testGetReportingApiUser()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/reporting_user',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('ebizmarts');

        $this->assertEquals(
            'ebizmarts',
            $this->configModel->getReportingApiUser()
        );
    }

    public function testGetReportingApiPassword()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/reporting_password',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('fds678dsf68ds');

        $this->assertEquals(
            'fds678dsf68ds',
            $this->configModel->getReportingApiPassword()
        );
    }

    public function testGetPIPassword()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PI);

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PI . '/password',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('fd67sf8ds6f78ds6f78ds');

        $this->assertEquals(
            'fd67sf8ds6f78ds6f78ds',
            $this->configModel->getPIPassword()
        );
    }

    public function testGetPIKey()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PI);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PI . '/key',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn('fd7s6f87ds6f78ds6f78dsf8ds76f7ds8f687dsf8');

        $this->assertEquals(
            'fd7s6f87ds6f78ds6f78dsf8ds76f7ds8f687dsf8',
            $this->configModel->getPIKey()
        );
    }

    public function testGet3DsecurePI()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PI);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_3D_DISABLE);

        $this->assertEquals(
            Config::MODE_3D_DISABLE,
            $this->configModel->get3Dsecure()
        );
    }

    public function testGet3DsecureForcedDisable()
    {
        $this->configModel->setMethodCode('sagepaysuiteserver');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('UseMSPSetting');

        $this->assertEquals(
            2,
            $this->configModel->get3Dsecure(true)
        );
    }

    public function testGet3DsecureSERVER()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_3D_DEFAULT);

        $this->assertEquals(
            '0',
            $this->configModel->get3Dsecure()
        );
    }

    public function testGet3DsecurServer1()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_3D_FORCE);

        $this->assertEquals(
            '1',
            $this->configModel->get3Dsecure()
        );
    }

    public function testGet3DsecurServer2()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_3D_DISABLE);

        $this->assertEquals(
            '2',
            $this->configModel->get3Dsecure()
        );
    }

    public function testGet3DsecurServer3()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/threedsecure',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_3D_IGNORE);

        $this->assertEquals(
            '3',
            $this->configModel->get3Dsecure()
        );
    }

    public function testGetAvsCvcPI()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PI);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/avscvc',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_AVSCVC_DISABLE);

        $this->assertEquals(
            Config::MODE_AVSCVC_DISABLE,
            $this->configModel->getAvsCvc()
        );
    }

    public function testGetAvsCvcSERVER()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/avscvc',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_AVSCVC_DEFAULT);

        $this->assertEquals(
            '0',
            $this->configModel->getAvsCvc()
        );
    }

    public function testGetAvsCvcSERVER1()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/avscvc',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_AVSCVC_FORCE);

        $this->assertEquals(
            '1',
            $this->configModel->getAvsCvc()
        );
    }

    public function testGetAvsCvcSERVER2()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/avscvc',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_AVSCVC_DISABLE);

        $this->assertEquals(
            '2',
            $this->configModel->getAvsCvc()
        );
    }

    public function testGetAvsCvcSERVER3()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/avscvc',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(Config::MODE_AVSCVC_IGNORE);

        $this->assertEquals(
            '3',
            $this->configModel->getAvsCvc()
        );
    }

    public function testGetBasketFormat()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/basket_format',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('Sage50');

        $this->assertEquals(
            'Sage50',
            $this->configModel->getBasketFormat()
        );
    }

    public function testGetPaypalBillingAgreement()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL . '/billing_agreement',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(false);

        $this->assertEquals(
            false,
            $this->configModel->getPaypalBillingAgreement()
        );
    }

    public function testGetAutoInvoiceFraudPassed()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/fraud_autoinvoice',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->getAutoInvoiceFraudPassed()
        );
    }

    public function testGetNotifyFraudResult()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/fraud_notify',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('medium_risk');

        $this->assertEquals(
            'medium_risk',
            $this->configModel->getNotifyFraudResult()
        );
    }

    public function testGetAllowedCcTypes()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL . '/cctypes',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn("VI,MC");

        $this->assertEquals(
            "VI,MC",
            $this->configModel->getAllowedCcTypes()
        );
    }

    public function testGetAreSpecificCountriesAllowed()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL . '/allowspecific',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(0);

        $this->assertEquals(
            0,
            $this->configModel->getAreSpecificCountriesAllowed()
        );
    }

    public function testGetSpecificCountries()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/' . \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL . '/specificcountry',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn("UY,US");

        $this->assertEquals(
            "UY,US",
            $this->configModel->getSpecificCountries()
        );
    }

    /**
     * @dataProvider getCurrencyCodeDataProvider
     */
    public function testGetCurrencyCode($data)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/currency',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($data["currency_setting"]);

        $quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            $data["expects"],
            $this->configModel->getQuoteCurrencyCode($quoteMock)
        );
    }

    public function getCurrencyCodeDataProvider()
    {
        return [
            "test base" => [
                [
                    "currency_setting" => Config::CURRENCY_BASE,
                    "expects" => "USD"
                ]
            ],
            "test display" => [
                [
                    "currency_setting" => Config::CURRENCY_STORE,
                    "expects" => "EUR"
                ]
            ],
            "test switcher" => [
                [
                    "currency_setting" => Config::CURRENCY_SWITCHER,
                    "expects" => "GBP"
                ]
            ]
        ];
    }

    public function testGetCurrencyConfig()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/global/currency',
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(Config::CURRENCY_BASE);

        $this->assertEquals(
            Config::CURRENCY_BASE,
            $this->configModel->getCurrencyConfig()
        );
    }

    public function testIsGiftAidEnabled()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/giftaid',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->isGiftAidEnabled()
        );
    }

    public function testGetMethodCode()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL);

        $this->assertEquals(
            \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL,
            $this->configModel->getMethodCode()
        );
    }

    public function testIsServerLowProfileEnabled()
    {
        $this->configModel->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER);

        $this->assertEquals(
            false,
            $this->configModel->isServerLowProfileEnabled()
        );
    }

    /**
     * @param $mockMode
     * @param $mockAction
     * @param $mockUrl
     * @dataProvider urlsProvider
     */
    public function testGetServiceUrl($mockMode, $mockAction, $mockUrl)
    {
        $configMock = $this
            ->getMockBuilder(Config::class)
            ->setMethods(['getMode'])
            ->disableOriginalConstructor()
            ->getMock();

        $configMock->method('getMode')->willReturn($mockMode);

        $url = $configMock->getServiceUrl($mockAction);

        $this->assertEquals($mockUrl, $url);
    }

    public function urlsProvider()
    {
        return [
            'live void' => ['live', 'VOID', 'https://live.sagepay.com/gateway/service/void.vsp'],
            'live refund' => ['live', 'REFUND', 'https://live.sagepay.com/gateway/service/refund.vsp'],
            'live release' => ['live', 'RELEASE', 'https://live.sagepay.com/gateway/service/release.vsp'],
            'live authorise' => ['live', 'AUTHORISE', 'https://live.sagepay.com/gateway/service/authorise.vsp'],
            'live repeat' => ['live', 'REPEAT', 'https://live.sagepay.com/gateway/service/repeat.vsp'],
            'live repeat deferred' => ['live', 'REPEATDEFERRED', 'https://live.sagepay.com/gateway/service/repeat.vsp'],
            'test void' => ['test', 'VOID', 'https://test.sagepay.com/gateway/service/void.vsp'],
            'test refund' => ['test', 'REFUND', 'https://test.sagepay.com/gateway/service/refund.vsp'],
            'test release' => ['test', 'RELEASE', 'https://test.sagepay.com/gateway/service/release.vsp'],
            'test authorise' => ['test', 'AUTHORISE', 'https://test.sagepay.com/gateway/service/authorise.vsp'],
            'test repeat' => ['test', 'REPEAT', 'https://test.sagepay.com/gateway/service/repeat.vsp'],
            'test repeat deferred' => ['test', 'REPEATDEFERRED', 'https://test.sagepay.com/gateway/service/repeat.vsp']
        ];
    }

    public function testIsInvoiceConfirmationNotificationEnabled()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/invoice_confirmation_notification',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->getInvoiceConfirmationNotification()
        );
    }

    public function testGetMaxTokenPerCustomer()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'sagepaysuite/advanced/max_token',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(3);

        $this->assertEquals(
            3,
            $this->configModel->getMaxTokenPerCustomer()
        );
    }

    public function testGet3dNewWindow()
    {
        $this->configModel->setMethodCode('sagepaysuitepi');
        $this->configModel->setConfigurationScope(ScopeInterface::SCOPE_WEBSITE);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                'payment/sagepaysuitepi/threed_new_window',
                ScopeInterface::SCOPE_WEBSITE,
                1
            )
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->configModel->get3dNewWindow()
        );
    }
}
