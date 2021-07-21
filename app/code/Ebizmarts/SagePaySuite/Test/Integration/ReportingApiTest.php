<?php

namespace Ebizmarts\SagePaySuite\Test\Integration;

use Magento\TestFramework\ObjectManager;

class ReportingApiTest extends \PHPUnit\Framework\TestCase
{
    const WHITELIST_IP_ADDRESS = "192.124.249.056";

    /** @var ObjectManager */
    private $objectManager;

    /** @var  \Ebizmarts\SagePaySuite\Test\Api\Helper */
    private $helper;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\Reporting */
    private $reporting;

    protected function setUp()
    {
        /** @var ObjectManager objectManager */
        $this->objectManager = ObjectManager::getInstance();

        $this->helper = $this->objectManager->create("Ebizmarts\SagePaySuite\Test\Api\Helper");

        $this->reporting = $this->objectManager->create('Ebizmarts\SagePaySuite\Model\Api\Reporting');
    }

    public function testWhitelistIpAddress()
    {
        $this->helper->saveReportingApiPassword();
        $this->helper->saveReportingApiUser();

        $whitelistResult = $this->reporting->whitelistIpAddress(self::WHITELIST_IP_ADDRESS);

        $this->assertInstanceOf("stdClass", $whitelistResult);
        $this->assertEquals("0000", $whitelistResult->errorcode);
    }
}
