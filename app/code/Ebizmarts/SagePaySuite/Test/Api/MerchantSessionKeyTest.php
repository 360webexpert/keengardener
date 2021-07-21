<?php

namespace Ebizmarts\SagePaySuite\Test\Api;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class MerchantSessionKeyTest extends WebapiAbstract
{
    const VALID_MERCHANT_SESSION_KEY = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var  \Ebizmarts\SagePaySuite\Test\Api\Helper */
    private $helper;

    /** @var \Magento\Config\Model\ResourceModel\Config */
    private $config;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->config = $this->objectManager->create(
            \Magento\Config\Model\ResourceModel\Config::class
        );

        $this->helper = $this->objectManager->create("Ebizmarts\SagePaySuite\Test\Api\Helper");
    }

    public function testMskCall()
    {
        $this->helper->savePiKey();
        $this->helper->savePiPassword();

        $this->config->saveConfig("sagepaysuite/global/mode", Config::MODE_DEVELOPMENT);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/sagepay/pi-msk',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, []);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('response', $response);
        $this->assertRegExp(self::VALID_MERCHANT_SESSION_KEY, $response['response']);
    }
}
