<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Api;

use Ebizmarts\SagePaySuite\Api\Data\HttpResponse;
use Ebizmarts\SagePaySuite\Model\Api\HttpText;
use Ebizmarts\SagePaySuite\Model\Api\PaymentOperations;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ebizmarts\SagePaySuite\Model\Config;

class SharedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Shared
     */
    private $sharedApiModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiExceptionFactoryMock;

    /** @var  HttpText|PHPUnit_Framework_MockObject_MockObject */
    private $httpTextMock;

    // @codingStandardsIgnoreStart
    private $transactionDetailsResponse;

    const HTTP_TEXT_FACTORY = '\Ebizmarts\SagePaySuite\Model\Api\HttpTextFactory';

    const MODEL_API_SHARED = 'Ebizmarts\SagePaySuite\Model\Api\Shared';

    const STORE_MANAGER_INTERFACE = 'Magento\Store\Model\StoreManagerInterface';

    const API_EXCEPTION_FACTORY = 'Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory';

    protected function setUp()
    {
        $this->apiExceptionFactoryMock = $this
            ->getMockBuilder(self::API_EXCEPTION_FACTORY)
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionDetailsResponse = new \stdClass();
        $this->transactionDetailsResponse->vpstxid      = "12345";
        $this->transactionDetailsResponse->securitykey  = "fds87";
        $this->transactionDetailsResponse->vpsauthcode  = "879243978234";
        $this->transactionDetailsResponse->currency     = "USD";
        $this->transactionDetailsResponse->vendortxcode = "1000000001-2016-12-12-12345678";

        $reportingApiMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\Reporting')
            ->disableOriginalConstructor()
            ->getMock();
        $reportingApiMock->expects($this->any())
            ->method('getTransactionDetailsByVpstxid')
            ->willReturn($this->transactionDetailsResponse);

        $suiteHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Data')
            ->setMethods(['generateVendorTxCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $suiteHelperMock->expects($this->any())
            ->method('generateVendorTxCode')
            ->will($this->returnValue('1000000001-2016-12-12-12345'));

        $suiteRequestHelperMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['populateAddressInformation'])
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

        $storeManagerMock = $this
            ->getMockBuilder(self::STORE_MANAGER_INTERFACE)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->any())
            ->method("getStore")
            ->willReturn($storerMock);

        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock = $this
            ->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getMode','getVendorname'])
            ->setConstructorArgs(
                ['scopeConfig' => $scopeConfigMock, 'storeManager' => $storeManagerMock, 'logger' => $loggerMock]
            )
            ->getMock();
        $configMock->method('getMode')->willReturn('test');
        $configMock->method('getVendorname')->willReturn('testvendorname');

        $this->httpTextMock = $this
            ->getMockBuilder(HttpText::class)
            ->setMethods(['executePost', 'getResponseData', 'arrayToQueryParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $httpTextFactoryMock = $this
            ->getMockBuilder(self::HTTP_TEXT_FACTORY)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $httpTextFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->httpTextMock);


        $objectManagerHelper = new ObjectManager($this);
        $this->sharedApiModel = $objectManagerHelper->getObject(
            self::MODEL_API_SHARED,
            [
                'reportingApi'        => $reportingApiMock,
                'suiteHelper'         => $suiteHelperMock,
                'apiExceptionFactory' => $this->apiExceptionFactoryMock,
                'config'              => $configMock,
                'requestHelper'  => $suiteRequestHelperMock,
                'httpTextFactory'     => $httpTextFactoryMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testVoidTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'  => '3.00',
                    'TxType'       => 'VOID',
                    'Vendor'       => "testvendorname",
                    'VendorTxCode' => "1000000001-2016-12-12-12345",
                    'SecurityKey'  => "fds87",
                    'TxAuthNo'     => "879243978234",
                    "VPSTxId"      => "12345"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'VPSProtocol'  => '3.00',
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.'
                ]
            ],
            $this->sharedApiModel->voidTransaction($this->transactionDetailsResponse)
        );
    }

    public function testRefundTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";
        $stringResponse .= "VPSTxId=123456\n";
        $stringResponse .= "TxAuthNo=8792439782345\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'         => '3.00',
                    'TxType'              => 'REFUND',
                    'Vendor'              => "testvendorname",
                    'VendorTxCode'        => "1000000001-2016-12-12-12345",
                    'Amount'              => "100.00",
                    'Currency'            => "USD",
                    'Description'         => "Refund issued from magento.",
                    'RelatedVPSTxId'      => "12345",
                    'RelatedVendorTxCode' => "1000000001-2016-12-12-12345678",
                    "RelatedSecurityKey"  => "fds87",
                    "RelatedTxAuthNo"     => "879243978234"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn('1000000001');

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'VPSProtocol'  => '3.00',
                    'VPSTxId'      => '123456',
                    'TxAuthNo'     => '8792439782345',
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.'
                ]
            ],
            $this->sharedApiModel->refundTransaction("12345", 100, $orderMock)
        );
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage The Transaction has already been Refunded.
     */
    public function testRefundTransactionERROR()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=INVALID\n";
        $stringResponse .= "StatusDetail=INVALID : The Transaction has already been Refunded.\n";
        $stringResponse .= "VPSTxId=123456\n";
        $stringResponse .= "TxAuthNo=8792439782345\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'         => '3.00',
                    'TxType'              => 'REFUND',
                    'Vendor'              => "testvendorname",
                    'VendorTxCode'        => "1000000001-2016-12-12-12345",
                    'Amount'              => "100.00",
                    'Currency'            => "USD",
                    'Description'         => "Refund issued from magento.",
                    'RelatedVPSTxId'      => "12345",
                    'RelatedVendorTxCode' => "1000000001-2016-12-12-12345678",
                    "RelatedSecurityKey"  => "fds87",
                    "RelatedTxAuthNo"     => "879243978234"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $apiException = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new \Magento\Framework\Phrase("The Transaction has already been Refunded."),
            new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase("INVALID"))
        );
        $this->apiExceptionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($apiException));

        $orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn('1000000001');

        $this->sharedApiModel->refundTransaction("12345", 100, $orderMock);
    }

    public function testReleaseTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'   => '3.00',
                    'TxType'        => 'RELEASE',
                    'Vendor'        => "testvendorname",
                    'VendorTxCode'  => "1000000001-2016-12-12-12345678",
                    'VPSTxId'       => "12345",
                    "SecurityKey"   => "fds87",
                    "TxAuthNo"      => "879243978234",
                    'ReleaseAmount' => "100.00",
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $orderMock = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.',
                    'VPSProtocol'  => '3.00'
                ]
            ],
            $this->sharedApiModel->releaseTransaction("12345", 100, $orderMock)
        );
    }

    public function testAbortDeferredTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=2006 : The Abort was Successful.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'         => '3.00',
                    'TxType'              => 'ABORT',
                    'ReferrerID'          => '01bf51f9-0dcd-49dd-a07a-3b1f918c77d7',
                    'Vendor'              => "testvendorname",
                    'VendorTxCode'        => "1000000001-2016-12-12-12345678",
                    'VPSTxId'             => "12345",
                    'SecurityKey'         => 'fds87',
                    'TxAuthNo'            => "879243978234"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'VPSProtocol'  => '3.00',
                    'Status'       => 'OK',
                    'StatusDetail' => '2006 : The Abort was Successful.'
                ]
            ],
            $this->sharedApiModel->abortDeferredTransaction($this->transactionDetailsResponse)
        );
    }

    public function testCancelAuthenticatedTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'  => '3.00',
                    'TxType'       => 'CANCEL',
                    'Vendor'       => "testvendorname",
                    'VendorTxCode' => "1000000001-2016-12-12-12345678",
                    'SecurityKey'  => "fds87",
                    "VPSTxId"      => "12345"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'VPSProtocol'  => '3.00',
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.'
                ]
            ],
            $this->sharedApiModel->cancelAuthenticatedTransaction($this->transactionDetailsResponse)
        );

    }

    public function testCaptureDeferredTransactionAwaitingRelease()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'   => '3.00',
                    'TxType'        => 'RELEASE',
                    'Vendor'        => 'testvendorname',
                    'VendorTxCode'  => '1000000001-2016-12-12-12345678',
                    'VPSTxId'       => '12345',
                    'SecurityKey'   => 'fds87',
                    'TxAuthNo'      => '879243978234',
                    'ReleaseAmount' => '100.00',
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $this->transactionDetailsResponse->txstateid = PaymentOperations::DEFERRED_AWAITING_RELEASE;

        $orderMock = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertEquals(
            [
                'status' => 200,
                'data'   => [
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.',
                    'VPSProtocol'  => '3.00'
                ]
            ],
            $this->sharedApiModel->captureDeferredTransaction('12345', 100, $orderMock)
        );
    }

    public function testCaptureDeferredTransactionAlreadyAuthorised()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'   => '3.00',
                    'TxType'        => 'REPEAT',
                    'Vendor'        => 'testvendorname',
                    'VendorTxCode'  => '1000000001-2016-12-12-12345',
                    'Amount' => 100,
                    'Currency' => 'USD',
                    'Description'  => 'Repeat transaction from Magento',
                    'RelatedVPSTxId' => '12345',
                    'RelatedVendorTxCode' => '1000000001-2016-12-12-12345678',
                    'RelatedSecurityKey' => 'fds87',
                    'RelatedTxAuthNo' => '879243978234',
                    'ReferrerID' => '01bf51f9-0dcd-49dd-a07a-3b1f918c77d7'
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $this->transactionDetailsResponse->txstateid = PaymentOperations::SUCCESSFULLY_AUTHORISED;

        $orderMock = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertEquals(
            [
                'status' => 200,
                'data'   => [
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.',
                    'VPSProtocol'  => '3.00'
                ]
            ],
            $this->sharedApiModel->captureDeferredTransaction('12345', 100, $orderMock)
        );
    }

    public function testAuthorizeTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";
        $stringResponse .= "VPSTxId=123456\n";
        $stringResponse .= "TxAuthNo=2439782345\n";
        $stringResponse .= "SecurityKey=8759623519\n";
        $stringResponse .= "BankAuthCode=T99777\n";
        $stringResponse .= "DeclineCode=00\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'         => '3.00',
                    'TxType'              => 'AUTHORISE',
                    'Vendor'              => "testvendorname",
                    'VendorTxCode'        => "1000000001-2016-12-12-12345",
                    'Amount'              => "100.00",
                    'Description'         => "Authorise transaction from Magento",
                    'RelatedVPSTxId'      => "12345",
                    'RelatedVendorTxCode' => "1000000001-2016-12-12-12345678",
                    "RelatedSecurityKey"  => "fds87",
                    "RelatedTxAuthNo"     => "879243978234"
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn('1000000001');

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.',
                    'VPSTxId'      => '123456',
                    'VPSProtocol'  => '3.00',
                    'TxAuthNo'     => '2439782345',
                    'SecurityKey'  => '8759623519',
                    'BankAuthCode' => 'T99777',
                    'DeclineCode'  => '00'
                ]
            ],
            $this->sharedApiModel->authorizeTransaction("12345", 100, $orderMock)
        );
    }

    public function testRepeatTransaction()
    {
        $stringResponse = 'HTTP/1.1 200 OK';
        $stringResponse .= "\n\n";
        $stringResponse .= "VPSProtocol=3.00\n";
        $stringResponse .= "Status=OK\n";
        $stringResponse .= "StatusDetail=Success.\n";
        $stringResponse .= "VPSTxId=123456\n";
        $stringResponse .= "TxAuthNo=2439782345\n";
        $stringResponse .= "SecurityKey=8759623519\n";
        $stringResponse .= "BankAuthCode=T99777\n";
        $stringResponse .= "DeclineCode=00\n";

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(200);

        $this->httpTextMock
            ->method('getResponseData')
            ->willReturn($stringResponse);
        $this->httpTextMock
            ->expects($this->once())
            ->method('arrayToQueryParams')
            ->with(
                [
                    'VPSProtocol'   => '3.00',
                    'TxType'        => 'REPEAT',
                    'Vendor'        => "testvendorname",
                    //'VendorTxCode'  => "1000000001-2016-12-12-123456",
                    //'Amount'  => "100",
                    //'Currency'  => "USD",
                    'Description'  => "Repeat transaction from Magento",
                    'RelatedVPSTxId'       => "12345",
                    "RelatedVendorTxCode"   => "1000000001-2016-12-12-12345678",
                    "RelatedSecurityKey"      => "fds87",
                    'RelatedTxAuthNo' => "879243978234",
                ]
            );
        $this->httpTextMock
            ->expects($this->once())
            ->method('executePost')
            ->willReturn($responseMock);

        $orderMock = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertEquals(
            [
                "status" => 200,
                "data" => [
                    'Status'       => 'OK',
                    'StatusDetail' => 'Success.',
                    'VPSTxId'      => '123456',
                    'VPSProtocol'  => '3.00',
                    'TxAuthNo'     => '2439782345',
                    'SecurityKey'  => '8759623519',
                    'BankAuthCode' => 'T99777',
                    'DeclineCode'  => '00'
                ]
            ],
            $this->sharedApiModel->repeatTransaction("12345", [], $orderMock)
        );
    }
}
