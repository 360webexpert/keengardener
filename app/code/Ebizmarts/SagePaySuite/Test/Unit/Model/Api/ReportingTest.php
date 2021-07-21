<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Api;

use Ebizmarts\SagePaySuite\Api\Data\HttpResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRule;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\HttpText;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use stdClass;

class ReportingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Reporting
     */
    private $reportingApiModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiExceptionFactoryMock;

    private $objectManagerMock;

    private $fraudScreenRuleInterfaceFactoryMock;

    private $fraudScreenResponseFactoryMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->apiExceptionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory')->setMethods(["create"])->disableOriginalConstructor()->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->fraudScreenResponseFactoryMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterfaceFactory')->setMethods(["create"])->disableOriginalConstructor()->getMock();

        $this->fraudScreenRuleInterfaceFactoryMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRuleInterfaceFactory')->setMethods(["create"])->disableOriginalConstructor()->getMock();

    }

    // @codingStandardsIgnoreEnd

    public function testGetTransactionDetails()
    {
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getTransactionDetailsResponse());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')->with($this->getTransactionDetailsRequestXML())->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getTransactionDetailsResponseXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $result = $reportingApiModel->getTransactionDetailsByVpstxid("12345");

        $expected               = new stdClass;
        $expected->errorcode    = '0000';
        $expected->timestamp    = '04/11/2013 11:45:32';
        $expected->vpstxid      = 'EE6025C6-7D24-4873-FB92-CD7A66B9494E';
        $expected->vendortxcode = 'REF20131029-1-838';

        $this->assertEquals($expected, $result);
    }

    public function testGetTransactionDetailsByVendorTxCode()
    {
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getTransactionDetailsByVendorTxCodeResponse());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')->with($this->getTransactionDetailsByVendorTxCodeRequestXML())->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getTransactionDetailsByVendorTxCodeResponseXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $result = $reportingApiModel->getTransactionDetailsByVendorTxCode("REF20131029-1-838");

        $expected               = new stdClass;
        $expected->errorcode    = '0000';
        $expected->timestamp    = '04/11/2013 11:45:32';
        $expected->vpstxid      = 'EE6025C6-7D24-4873-FB92-CD7A66B9494E';
        $expected->vendortxcode = 'REF20131029-1-838';

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage INVALID STATUS
     */
    public function testGetTransactionDetailsError()
    {
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getTransactionDetailsResponseFailed());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')->with($this->getTransactionDetailsRequestXML())->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getTransactionDetailsResponseFailedXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $apiException = new ApiException(
            new Phrase("INVALID STATUS"),
            new LocalizedException(new Phrase("INVALID STATUS"))
        );

        $this->apiExceptionFactoryMock->expects($this->once())->method('create')->with([
                'phrase' => new Phrase("INVALID STATUS"),
                'code'   => 2015
            ])->willReturn($apiException);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $result = $reportingApiModel->getTransactionDetailsByVpstxid("12345");
    }

    public function testGetTokenCount()
    {
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getGetTokenCountResponse());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')->with($this->getGetTokenCountRequestXml())->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getGetTokenCountResponseXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $result = $reportingApiModel->getTokenCount();

        $expected              = new stdClass;
        $expected->errorcode   = '0000';
        $expected->timestamp   = '04/11/2013 11:45:32';
        $expected->totalnumber = '255';

        $this->assertEquals($expected, $result);
    }

    public function testGetFraudScreenDetailRed()
    {
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getTransactionDetailsResponse());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')
            ->with($this->getGetFraudScreenDetailRedXml())
            ->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getGetFraudScreenDetailRedResponseXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $objectManagerHelper = new ObjectManager($this);
        $fraudResponse = $objectManagerHelper->getObject(FraudScreenResponse::class);

        $this->fraudScreenResponseFactoryMock->expects($this->once())->method('create')->willReturn($fraudResponse);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $response = $reportingApiModel->getFraudScreenDetail("12345");

        $this->assertEquals('0000', $response->getErrorCode());
        $this->assertEquals('', $response->getTimestamp());
        $this->assertEquals('ReD', $response->getFraudProviderName());
        $this->assertEquals('ACCEPT', $response->getFraudScreenRecommendation());
        $this->assertEquals('', $response->getFraudId());
        $this->assertEquals('0100', $response->getFraudCode());
        $this->assertEquals('Accept', $response->getFraudCodeDetail());
    }

    public function testGetFraudScreenDetailThirdman()
    {
        $objectManagerHelper = new ObjectManager($this);

        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->exactly(2))->method('getResponseData')->willReturn($this->getFraudScreenDetailThirdmanResponse());

        $httpTextMock = $this->createMock(HttpText::class);
        $httpTextMock->expects($this->once())->method('setUrl')->with(Config::URL_REPORTING_API_TEST);
        $httpTextMock->expects($this->once())->method('executePost')
            ->with($this->getGetFraudScreenDetailThirdmanRequextXml())
            ->willReturn($responseMock);

        $simpleInstance = new \SimpleXMLElement($this->getFraudScreenDetailThirdmanResponseXml());
        $this->objectManagerMock->method('create')->willReturn($simpleInstance);

        $httpTextFactory = $this->createMock('\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory');
        $httpTextFactory->expects($this->once())->method('create')->willReturn($httpTextMock);

        $fraudResponse = $objectManagerHelper->getObject(FraudScreenResponse::class);
        $this->fraudScreenResponseFactoryMock->expects($this->once())->method('create')->willReturn($fraudResponse);

        $fraudResponseRule = $objectManagerHelper->getObject(FraudScreenRule::class);
        $this->fraudScreenRuleInterfaceFactoryMock->expects($this->exactly(2))->method('create')
            ->willReturn($fraudResponseRule);

        $reportingApiModel = $this->makeReportingModelObjectManager($httpTextFactory);

        $response = $reportingApiModel->getFraudScreenDetail("12345");

        $this->assertEquals('0000', $response->getErrorCode());
        $this->assertEquals('30/11/2016 09:55:01', $response->getTimestamp());
        $this->assertEquals('T3M', $response->getFraudProviderName());
        $this->assertEquals('4985075328', $response->getThirdmanId());
        $this->assertEquals('37', $response->getThirdmanScore());
        $this->assertEquals('HOLD', $response->getThirdmanAction());
        $this->assertCount(2, $response->getThirdmanRules());

        $firstRule = current($response->getThirdmanRules());
        $this->assertEquals('10', $firstRule->getScore());
        $this->assertEquals(
            'No Match on Electoral Roll, or Electoral Roll not available at billing address',
            $firstRule->getDescription()
        );
    }

    /**
     * @param $httpTextFactory
     * @return Reporting
     */
    private function makeReportingModelObjectManager($httpTextFactory): Reporting
    {
        $objectManagerHelper = new ObjectManager($this);
        $reportingApiModel   = $objectManagerHelper->getObject(Reporting::class, [
            'httpTextFactory'     => $httpTextFactory,
            'apiExceptionFactory' => $this->apiExceptionFactoryMock,
            'config'              => $this->createMock(Config::class),
            'suiteLogger'         => $this->createMock(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class),
            'objectManager'       => $this->objectManagerMock,
            'fraudResponse'       => $this->fraudScreenResponseFactoryMock,
            'fraudScreenRule'     => $this->fraudScreenRuleInterfaceFactoryMock,
        ]);

        return $reportingApiModel;
    }

    private function getGetFraudScreenDetailThirdmanRequextXml() : string
    {
        $xml = 'XML=<vspaccess><command>getFraudScreenDetail</command><vendor></vendor><user></user>';
        $xml .= '<vpstxid>12345</vpstxid><signature>85bd7f80aad73ecd5740bd6b58142071</signature></vspaccess>';

        return $xml;
    }

    private function getGetFraudScreenDetailRedXml() : string
    {
        $xmlWrite = 'XML=<vspaccess><command>getFraudScreenDetail</command><vendor></vendor><user></user>';
        $xmlWrite .= '<vpstxid>12345</vpstxid><signature>85bd7f80aad73ecd5740bd6b58142071</signature></vspaccess>';

        return $xmlWrite;
    }

    private function getFraudScreenDetailThirdmanResponse() : string
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getFraudScreenDetailThirdmanResponseXml();
    }

    private function getFraudScreenDetailThirdmanResponseXml() : string
    {
        return '<vspaccess>
                        <errorcode>0000</errorcode>
                        <timestamp>30/11/2016 09:55:01</timestamp>
                        <fraudprovidername>T3M</fraudprovidername>
                        <t3mid>4985075328</t3mid>
                        <t3mscore>37</t3mscore>
                        <t3maction>HOLD</t3maction>
                        <t3mresults>
                            <rule>
                                <description>Telephone number is a mobile number</description>
                                <score>4</score>
                            </rule>
                            <rule>
                                <description>No Match on Electoral Roll, or Electoral Roll not available at billing address</description>
                                <score>10</score>
                            </rule>
                        </t3mresults>
                </vspaccess>';
    }

    /**
     * @return string
     */
    private function getTransactionDetailsRequestXML()
    {
        return 'XML=<vspaccess><command>getTransactionDetail</command><vendor></vendor><user></user><vpstxid>12345</vpstxid><signature>4a0787ba97d65455d24be4d1768133ac</signature></vspaccess>';
    }

    /**
     * @return string
     */
    private function getTransactionDetailsByVendorTxCodeRequestXML()
    {
        return 'XML=<vspaccess><command>getTransactionDetail</command><vendor></vendor><user></user><vendorTxCode>REF20131029-1-838</vendorTxCode><signature>6a4a665ca8a1785db650aaf6a2f86fd7</signature></vspaccess>';
    }

    /**
     * @return string
     */
    private function getGetTokenCountRequestXml()
    {
        return 'XML=<vspaccess><command>getTokenCount</command><vendor></vendor><user></user><signature>eca0a57c18e960a6cba53f685597b6c2</signature></vspaccess>';
    }

    /**
     * @return string
     */
    private function getGetTokenCountResponse()
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getGetTokenCountResponseXml();
    }

    private function getGetTokenCountResponseXml()
    {
        return '<vspaccess><errorcode>0000</errorcode><timestamp>04/11/2013 11:45:32</timestamp>
                <totalnumber>255</totalnumber>
                </vspaccess>';
    }

    private function getTransactionDetailsResponse() : string
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getTransactionDetailsResponseXml();
    }

    private function getTransactionDetailsByVendorTxCodeResponse() : string
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getTransactionDetailsByVendorTxCodeResponseXML();
    }

    private function getFraudRedResponse() : string
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getGetFraudScreenDetailRedResponseXml();
    }

    private function getGetFraudScreenDetailRedResponseXml() : string
    {
        return '<vspaccess>
                    <errorcode>0000</errorcode>
                    <timestamp/>
                    <fraudprovidername>ReD</fraudprovidername>
                    <fraudscreenrecommendation>ACCEPT</fraudscreenrecommendation>
                    <fraudid/>
                    <fraudcode>0100</fraudcode>
                    <fraudcodedetail>Accept</fraudcodedetail>
                </vspaccess>';
    }

    /**
     * @return string
     */
    private function getTransactionDetailsResponseFailed()
    {
        return 'Content-Language: en-GB'.PHP_EOL.PHP_EOL . $this->getTransactionDetailsResponseFailedXml();
    }

    private function getTransactionDetailsResponseXml()
    {
        return '<vspaccess><errorcode>0000</errorcode><timestamp>04/11/2013 11:45:32</timestamp>
                <vpstxid>EE6025C6-7D24-4873-FB92-CD7A66B9494E</vpstxid><vendortxcode>REF20131029-1-838</vendortxcode>
                </vspaccess>';
    }

    private function getTransactionDetailsByVendorTxCodeResponseXml()
    {
        return '<vspaccess><errorcode>0000</errorcode><timestamp>04/11/2013 11:45:32</timestamp>
                <vpstxid>EE6025C6-7D24-4873-FB92-CD7A66B9494E</vpstxid><vendortxcode>REF20131029-1-838</vendortxcode>
                </vspaccess>';
    }

    private function getTransactionDetailsResponseFailedXml()
    {
        return '<vspaccess><errorcode>2015</errorcode><error>INVALID STATUS</error>
                <timestamp>04/11/2013 11:45:32</timestamp><vpstxid>EE6025C6-7D24-4873-FB92-CD7A66B9494E</vpstxid>
                <vendortxcode>REF20131029-1-838</vendortxcode></vspaccess>';
    }
}
