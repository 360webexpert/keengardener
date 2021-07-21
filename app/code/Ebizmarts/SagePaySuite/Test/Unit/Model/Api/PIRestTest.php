<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Api;

use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequest;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponseFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiRepeatRequest;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureRequest;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResult;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmount;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCard;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethod;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeD;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDFactory;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class PIRestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PIRest
     */
    private $pirestApiModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiExceptionFactoryMock;

    private $objectManager;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpRest|\PHPUnit_Framework_MockObject_MockObject */
    private $httpRestMock;

    private $httpRestFactoryMock;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\HttpResponse|\PHPUnit_Framework_MockObject_MockObject */
    private $httpResponseMock;

    /** @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $configMock;

    const PI_KEY      = "hJYxsw7HLbj40cB8udES8CDRFLhuJ8G54O6rDpUXvE6hYDrria";
    const PI_PASSWORD = "o2iHSrFybYMZpmWOQMuhsXP52V4fBtpuSDshrKDSWsBY1OiN6hwd9Kb12z4j5Us5u";

    // @codingStandardsIgnoreStart
    const TRANSACTION_RESULT_CARD_FACTORY = '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCardFactory';

    const REPEAT_REQUEST_FACTORY = '\Ebizmarts\SagePaySuite\Api\SagePayData\PiRepeatRequestFactory';

    const TRANSACTION_RESULT_FACTORY = '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultFactory';

    const TRANSACTION_RESULT_PAYMENT_METHOD_FACTORY = '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethodFactory';

    const PI_REST = 'Ebizmarts\SagePaySuite\Model\Api\PIRest';

    const TRANSACTION_RESULT_AMOUNT_FACTORY = '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmountFactory';

    const TEST_VPS_TX_ID = "2B97808F-9A36-6E71-F87F-6714667E8AF4";

    const ABORT_INSTRUCTION_TYPE = "abort";

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->apiExceptionFactoryMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\ApiExceptionFactory')
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock
            ->expects($this->once())
            ->method('getPIKey')
            ->willReturn(self::PI_KEY);
        $this->configMock
            ->expects($this->once())
            ->method('getPIPassword')
            ->willReturn(self::PI_PASSWORD);

        $this->httpRestMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Api\HttpRest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpRestMock
            ->expects($this->once())
            ->method('setBasicAuth')
            ->with(self::PI_KEY, self::PI_PASSWORD);

        $this->httpRestFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Api\HttpRestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->httpResponseMock = $this->
            getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\HttpResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    // @codingStandardsIgnoreEnd

    public function testGenerateMerchantKey()
    {
        $mskRequestMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequest::class)
            ->setMethods(['__toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $mskRequestMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn(['vendorName' => 'testvendorname']);
        $mskRequestMockFactory = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $mskRequestMockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($mskRequestMock);
        $this->httpRestMock
            ->expects($this->once())
            ->method('setUrl')
            ->with("https://pi-test.sagepay.com/api/v1/merchant-session-keys");

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '{"merchantSessionKey":"M1E996F5-A9BC-41FE-B088-E5B73DB94277","expiry":"2025-08-11T11:45:16.285+01:00"}'
                )
            );

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"vendorName":"testvendorname"}')
            ->willReturn($this->httpResponseMock);

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $mskResponseMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mskResponseFactory = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponseFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $mskResponseFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($mskResponseMock);
        $mskResponseMock
            ->expects($this->once())
            ->method('setExpiry')
            ->with("2025-08-11T11:45:16.285+01:00");
        $mskResponseMock
            ->expects($this->once())
            ->method('setMerchantSessionKey')
            ->with("M1E996F5-A9BC-41FE-B088-E5B73DB94277");

        $this->configMock->expects($this->once())->method("setConfigurationScopeId")
            ->with(1);
        $this->configMock->expects($this->once())->method("setConfigurationScope")
            ->with(\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "mskRequest"      => $mskRequestMockFactory,
                "httpRestFactory" => $this->httpRestFactoryMock,
                "mskResponse"     => $mskResponseFactory,
                "config"          => $this->configMock
            ]
        );

        $this->assertInstanceOf(
            '\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponse',
            $this->pirestApiModel->generateMerchantKey($this->makeQuoteMock())
        );
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Missing mandatory field: vendorName
     */
    public function testGenerateMerchantKeyERROR()
    {
        $mskRequestMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequest::class)
            ->setMethods(['__toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $mskRequestMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn(['vendorName' => '']);
        $mskRequestMockFactory = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $mskRequestMockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($mskRequestMock);
        $this->httpRestMock
            ->expects($this->once())
            ->method('setUrl')
            ->with("https://pi-test.sagepay.com/api/v1/merchant-session-keys");

        $this->httpResponseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(422);
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '{"errors": [{"description": "Missing mandatory field","property": "vendorName","code": 1003}]}'
                )
            );

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"vendorName":""}')
            ->willReturn($this->httpResponseMock);
        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->exactly(3))
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->exactly(3))
            ->method('getLogger')
            ->willReturn($loggerMock);

        $apiException = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new Phrase("Missing mandatory field: vendorName"),
            new LocalizedException(new Phrase("Missing mandatory field: vendorName"))
        );

        $phrase = new Phrase("Missing mandatory field: vendorName");

        $this->apiExceptionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(["phrase" => $phrase, "code" => "1003"])
            ->willReturn($apiException);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "mskRequest"          => $mskRequestMockFactory,
                "httpRestFactory"     => $this->httpRestFactoryMock,
                "config"              => $this->configMock,
                "apiExceptionFactory" => $this->apiExceptionFactoryMock
            ]
        );

        $this->pirestApiModel->generateMerchantKey($this->makeQuoteMock());
    }

    /**
     * @param int $responseCode
     */
    public function testCapture()
    {
        $threedResultMock = $this
            ->getMockBuilder(PiTransactionResultThreeD::class)
            ->disableOriginalConstructor()
            ->getMock();
        $threedResultMock->expects($this->once())->method('setStatus')->with("NotChecked");

        $avsCvcCheckResultMock = $this->
            getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheck::class)
            ->disableOriginalConstructor()
            ->getMock();
        $avsCvcCheckResultMock->expects($this->once())->method('setStatus')->with('SecurityCodeMatchOnly');
        $avsCvcCheckResultMock->expects($this->once())->method('setAddress')->with('NotMatched');
        $avsCvcCheckResultMock->expects($this->once())->method('setPostalCode')->with('NotMatched');
        $avsCvcCheckResultMock->expects($this->once())->method('setSecurityCode')->with('Matched');

        $avsCvcCheckResultFactoryMock = $this->
            getMockBuilder(PiTransactionResultAvsCvcCheckFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $avsCvcCheckResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($avsCvcCheckResultMock);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $threedResultFactoryMock = $this
            ->getMockBuilder(PiTransactionResultThreeDFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $threedResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threedResultMock);

        $payResult = $this
            ->getMockBuilder(PiTransactionResultPaymentMethod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodResultFactory = $this->makePaymentMethodResultFactoryMock($payResult);

        $cardResult = $this
            ->getMockBuilder(PiTransactionResultCard::class)
            ->disableOriginalConstructor()
        ->getMock();
        $cardResult->expects($this->never())->method('setCardIdentifier');
        $cardResult->expects($this->never())->method('setIsReusable');
        $cardResult->expects($this->once())->method('setCardType')->with("Visa");
        $cardResult->expects($this->once())->method('setLastFourDigits')->with("0006");
        $cardResult->expects($this->once())->method('setExpiryDate')->with("0317");

        $cardResultFactory = $this->makeCardResultFactoryMock($cardResult);

        $payResult->expects($this->once())->method('setCard')->with($cardResult);

        $piTransactionResult = $this
            ->getMockBuilder(PiTransactionResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $piTransactionResult->expects($this->once())->method('setStatusCode')->with("0000");
        $piTransactionResult->expects($this->once())->method('setStatusDetail')
            ->with("The Authorisation was Successful.");
        $piTransactionResult->expects($this->once())->method('setTransactionId')
            ->with("T6569400-1516-0A3F-E3FA-7F222CC79221");
        $piTransactionResult->expects($this->once())->method('setStatus')->with("Ok");
        $piTransactionResult->expects($this->once())->method('setTransactionType')->with("Payment");
        $piTransactionResult->expects($this->once())->method('setRetrievalReference')->with("8636128");
        $piTransactionResult->expects($this->once())->method('setBankAuthCode')->with("999777");
        $piTransactionResult->expects($this->once())->method('setBankResponseCode')->with("00");
        $piTransactionResult->expects($this->once())->method('setPaymentMethod')->with($payResult);
        $piTransactionResult->expects($this->once())->method('setThreeDSecure')->with($threedResultMock);
        $piTransactionResult->expects($this->once())->method('setAvsCvcCheck')->with($avsCvcCheckResultMock);

        $piResultFactory = $this->makeTransactionResultFactoryMock($piTransactionResult);

        $this->configMock
            ->method('getMode')
            ->willReturn('live');

        $this->httpRestMock
            ->expects($this->once())
            ->method('setUrl')
            ->with("https://pi-live.sagepay.com/api/v1/transactions");

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '
                        {
                            "transactionId": "T6569400-1516-0A3F-E3FA-7F222CC79221",
                            "transactionType": "Payment",
                            "status": "Ok",
                            "statusCode": "0000",
                            "statusDetail": "The Authorisation was Successful.",
                            "retrievalReference": 8636128,
                            "bankResponseCode": "00",
                            "bankAuthorisationCode": "999777",
                            "paymentMethod": {
                                "card": {
                                    "cardType": "Visa",
                                    "lastFourDigits": "0006",
                                    "expiryDate": "0317"
                                }
                            },
                            "3DSecure": {
                                "status": "NotChecked"
                            },
                            "avsCvcCheck": {
                                "status": "SecurityCodeMatchOnly",
                                "address": "NotMatched",
                                "postalCode": "NotMatched",
                                "securityCode": "Matched"
                            }
                        }
                    '
                )
            );

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $requestArray = [
            "transactionType" => "Payment",
            "paymentMethod" => [
                "card" => [
                    "merchantSessionKey" => "M1E996F5-A9BC-41FE-B088-E5B73DB94277",
                    "cardIdentifier" => "1234564766758",
                ]
            ],
            "vendorTxCode"      => "demotransaction-100092813",
            "amount"            => 10000,
            "currency"          => "GBP",
            "description"       => "Demotransaction",
            "apply3DSecure"     => "UseMSPSetting",
            "customerFirstName" => "Sam",
            "customerLastName"  => "Jones",
            "billingAddress" => [
                "address1"   => "407St.JohnStreet",
                "city"       => "London",
                "postalCode" => "EC1V4AB",
                "country"    => "GB",
            ],
            "entryMethod" => "Ecommerce"
        ];

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"transactionType":"Payment","paymentMethod":{"card":{"merchantSessionKey":"M1E996F5-A9BC-41FE-B088-E5B73DB94277","cardIdentifier":"1234564766758"}},"vendorTxCode":"demotransaction-100092813","amount":10000,"currency":"GBP","description":"Demotransaction","apply3DSecure":"UseMSPSetting","customerFirstName":"Sam","customerLastName":"Jones","billingAddress":{"address1":"407St.JohnStreet","city":"London","postalCode":"EC1V4AB","country":"GB"},"entryMethod":"Ecommerce"}')
            ->willReturn($this->httpResponseMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "piCaptureResultFactory"     => $piResultFactory,
                "cardResultFactory"          => $cardResultFactory,
                "paymentMethodResultFactory" => $paymentMethodResultFactory,
                "threedResultFactory"        => $threedResultFactoryMock,
                "avsCvcCheckResultFactory"   => $avsCvcCheckResultFactoryMock
            ]
        );

        $resultObject = $this->pirestApiModel->capture($requestArray);

        $this->assertInstanceOf('\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResult', $resultObject);
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Contains invalid value: paymentMethod.card.merchantSessionKey
     */
    public function testCaptureERROR()
    {
        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"Amount":"100.00"}')
            ->willReturn($this->httpResponseMock);

        $this->httpResponseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(422);
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '
                        {
                        "errors":
                            [
                                {"description":"Contains invalid value","property":"paymentMethod.card.merchantSessionKey","code":1009},
                                {"description":"Contains invalid value","property":"paymentMethod.card.cardIdentifier","code":1009}
                            ]
                        }
                    '
                )
            );

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->exactly(3))
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->exactly(3))
            ->method('getLogger')
            ->willReturn($loggerMock);

        $apiExceptionObj = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new Phrase("Contains invalid value: paymentMethod.card.merchantSessionKey"),
            new LocalizedException(
                new Phrase("Contains invalid value: paymentMethod.card.merchantSessionKey")
            )
        );

        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->apiExceptionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'phrase' => __("Contains invalid value: paymentMethod.card.merchantSessionKey"),
                'code' => 1009
            ])
            ->willReturn($apiExceptionObj);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock
            ]
        );

        $this->pirestApiModel->capture(["Amount" => "100.00"]);
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage No card provided.
     */
    public function testCaptureError1()
    {
        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"Amount":"100.00"}')
            ->willReturn($this->httpResponseMock);

        $this->httpResponseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(422);
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '
                    {
                    "errors":
                    [
                      {"statusDetail": "No card provided.", "description":"Contains invalid value","property":"paymentMethod.card.cardIdentifier","code":1009}
                    ]
                    }
                    '
                )
            );

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->exactly(3))
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->exactly(3))
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $apiExceptionObj = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new Phrase("No card provided."),
            new LocalizedException(
                new Phrase("No card provided.")
            )
        );

        $this->apiExceptionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'phrase' => __("No card provided."),
                'code' => 1009
            ])
            ->willReturn($apiExceptionObj);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock
            ]
        );

        $this->pirestApiModel->capture(["Amount" => "100.00"]);
    }

    public function testSubmit3D()
    {
        $pi3dRequestMock = $this
            ->getMockBuilder(PiThreeDSecureRequest::class)
            ->setMethods(['__toArray', 'setParEs'])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestMock->expects($this->once())->method('setParEs')->with("fsd678dfs786dfs786fds678fds");
        $pi3dRequestMock->expects($this->once())->method('__toArray')->willReturn(["paRes" => "fsd678dfs786dfs786fds678fds"]);

        $pi3dRequestFactoryMock = $this
            ->getMockBuilder(PiThreeDSecureRequestFactory::class)
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestFactoryMock->expects($this->once())->method('create')->willReturn($pi3dRequestMock);

        $piTransactionResult3DMock = $this
            ->getMockBuilder(PiTransactionResultThreeD::class)
            ->disableOriginalConstructor()
            ->getMock();
        $piTransactionResult3DMock->expects($this->once())->method('setStatus')->with("OK");

        $piTransactionResult3DFactoryMock = $this
        ->getMockBuilder(PiTransactionResultThreeDFactory::class)
        ->setMethods(["create"])
        ->disableOriginalConstructor()
        ->getMock();
        $piTransactionResult3DFactoryMock->expects($this->once())->method('create')->willReturn($piTransactionResult3DMock);

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"paRes":"fsd678dfs786dfs786fds678fds"}')
            ->willReturn($this->httpResponseMock);

        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{"status": "OK"}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->exactly(3))
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->exactly(3))
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "threedRequest"              => $pi3dRequestFactoryMock,
                "threedStatusResultFactory"  => $piTransactionResult3DFactoryMock
            ]
        );

        $this->assertInstanceOf(
            '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeD',
            $this->pirestApiModel->submit3D("fsd678dfs786dfs786fds678fds", 12345)
        );
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Invalid 3D secure response.
     */
    public function testSubmit3DEmptyResponse()
    {
        $pi3dRequestMock = $this
            ->getMockBuilder(PiThreeDSecureRequest::class)
            ->setMethods(['__toArray', 'setParEs'])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestMock->expects($this->once())->method('setParEs')->with("fsd678dfs786dfs786fds678fds");
        $pi3dRequestMock->expects($this->once())->method('__toArray')->willReturn(["paRes" => "fsd678dfs786dfs786fds678fds"]);

        $pi3dRequestFactoryMock = $this
            ->getMockBuilder(PiThreeDSecureRequestFactory::class)
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestFactoryMock->expects($this->once())->method('create')->willReturn($pi3dRequestMock);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $piTransactionResult3DFactoryMock = $this
        ->getMockBuilder(PiTransactionResultThreeDFactory::class)
        ->setMethods(["create"])
        ->disableOriginalConstructor()
        ->getMock();
        $piTransactionResult3DFactoryMock->expects($this->never())->method('create');

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"paRes":"fsd678dfs786dfs786fds678fds"}')
            ->willReturn($this->httpResponseMock);

        $this->verifyResponseCalledOnceReturns201();

        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "threedRequest"              => $pi3dRequestFactoryMock,
                "threedStatusResultFactory"  => $piTransactionResult3DFactoryMock
            ]
        );

        $this->assertInstanceOf(
            '\Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeD',
            $this->pirestApiModel->submit3D("fsd678dfs786dfs786fds678fds", 12345)
        );
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Contains invalid characters: paRes
     */
    public function testSubmitThreedError()
    {
        $pi3dRequestMock = $this
            ->getMockBuilder(PiThreeDSecureRequest::class)
            ->setMethods(['__toArray', 'setParEs'])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestMock->expects($this->once())->method('setParEs')->with("fsd678dfs786dfs786fds678fds");
        $pi3dRequestMock->expects($this->once())->method('__toArray')->willReturn(["paRes" => "fsd678dfs786dfs786fds678fds"]);

        $pi3dRequestFactoryMock = $this
            ->getMockBuilder(PiThreeDSecureRequestFactory::class)
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();
        $pi3dRequestFactoryMock->expects($this->once())->method('create')->willReturn($pi3dRequestMock);

        $piTransactionResult3DFactoryMock = $this
            ->getMockBuilder(PiTransactionResultThreeDFactory::class)
            ->setMethods(["create"])
            ->disableOriginalConstructor()
            ->getMock();
        $piTransactionResult3DFactoryMock->expects($this->never())->method('create');

        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"paRes":"fsd678dfs786dfs786fds678fds"}')
            ->willReturn($this->httpResponseMock);

        $this->httpResponseMock
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(422);
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{"errors": [{"description": "Contains invalid characters","property": "paRes","code": 1005}]}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->exactly(3))
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->exactly(3))
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->httpRestFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->httpRestMock);

        $apiException = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new Phrase("Contains invalid characters: paRes"),
            new LocalizedException(new Phrase("Contains invalid characters: paRes"))
        );

        $this->apiExceptionFactoryMock->expects($this->any())
            ->method('create')
            ->with([
                'phrase' => __("Contains invalid characters: paRes"),
                'code'   => 1005
            ])
            ->willReturn($apiException);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "threedRequest"              => $pi3dRequestFactoryMock,
                "threedStatusResultFactory"  => $piTransactionResult3DFactoryMock
            ]
        );
        $this->pirestApiModel->submit3D("fsd678dfs786dfs786fds678fds", 12345);
    }

    public function testTransactionDetailsOk()
    {
        $this->httpRestMock
            ->expects($this->once())
            ->method('executeGet')
            ->willReturn($this->httpResponseMock);

        $this->httpResponseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);

        $transactionResponse = new \stdClass();
        $transactionResponse->statusCode = '2007';
        $transactionResponse->statusDetail = 'Please redirect your customer to the ACSURL to complete the 3DS Transaction';
        $transactionResponse->transactionId = '12345';
        $transactionResponse->acsUrl = 'https://test.sagepay.com/mpitools/accesscontroler?action=pareq';
        $transactionResponse->paReq = 'VUstuwjAQvPcronxAbMcJJGgxgtK';
        $transactionResponse->status = '3DAuth';

        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn($transactionResponse);

        $resultFactory = $this->getMockBuilder(self::TRANSACTION_RESULT_FACTORY)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $transactionResult = $this->objectManager->getObject(PiTransactionResult::class);
        $resultFactory->expects($this->once())->method('create')->willReturn($transactionResult);

        $this->pirestApiModel  = $this->objectManager->getObject(
            PIRest::class,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "piCaptureResultFactory"     => $resultFactory
            ]
        );

        $result = $this->pirestApiModel->transactionDetails(12345);

        $this->assertEquals('3DAuth', $result->getStatus());
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Invalid Transaction Id
     */
    public function testTransactionDetailsError()
    {
        $this->httpRestMock
            ->expects($this->once())
            ->method('executeGet')
            ->willReturn($this->httpResponseMock);

        $this->httpResponseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(400);
        $this->httpResponseMock
            ->expects($this->exactly(2))
            ->method('getResponseData')
            ->willReturn(json_decode(
                '{"description": "Contains invalid characters","property": "paRes","code": 1005}'
            ));

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $apiException = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new Phrase("Invalid Transaction Id"),
            new LocalizedException(new Phrase("Invalid Transaction Id"))
        );
        $this->apiExceptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($apiException);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
            ]
        );

        $this->pirestApiModel->transactionDetails(12345);
    }

    public function testVoidSucess()
    {
        $piInstructionRequest = $this
            ->getMockBuilder(PiInstructionRequest::class)
        ->disableOriginalConstructor()
            ->setMethods(['setInstructionType', '__toArray'])
            ->getMock();
        $piInstructionRequest->expects($this->once())->method('setInstructionType')->with("void");
        $piInstructionRequest->expects($this->once())->method('__toArray')->willReturn(["instructionType" => "void"]);
        $piInstructionRequestFactory = $this
            ->getMockBuilder(PiInstructionRequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $piInstructionRequestFactory->expects($this->once())->method('create')->willReturn($piInstructionRequest);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $instructionResponse = $this
            ->getMockBuilder(PiInstructionResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toArray'])
            ->getMock();

        $instructionResponseFactory = $this
        ->getMockBuilder(PiInstructionResponseFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $instructionResponseFactory->expects($this->once())->method('create')->willReturn($instructionResponse);

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"instructionType":"void"}')
            ->willReturn($this->httpResponseMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{"instructionType": "void","date": "2015-08-11T11:45:16.285+01:00"}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "instructionRequest"         => $piInstructionRequestFactory,
                "instructionResponse"        => $instructionResponseFactory,
            ]
        );
        $result = $this->pirestApiModel->void(self::TEST_VPS_TX_ID);
        $this->assertEquals($result->getInstructionType(), "void");
        $this->assertEquals($result->getDate(), "2015-08-11T11:45:16.285+01:00");
    }

    public function testAbortSuccess()
    {
        $piInstructionRequest = $this
            ->getMockBuilder(PiInstructionRequest::class)
        ->disableOriginalConstructor()
            ->setMethods(['setInstructionType', '__toArray'])
            ->getMock();
        $piInstructionRequest->expects($this->once())->method('setInstructionType')->with(self::ABORT_INSTRUCTION_TYPE);
        $piInstructionRequest->expects($this->once())->method('__toArray')->willReturn(
            ["instructionType" => self::ABORT_INSTRUCTION_TYPE]
        );
        $piInstructionRequestFactory = $this
            ->getMockBuilder(PiInstructionRequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $piInstructionRequestFactory->expects($this->once())->method('create')->willReturn($piInstructionRequest);

        $instructionResponse = $this
            ->getMockBuilder(PiInstructionResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toArray'])
            ->getMock();

        $instructionResponseFactory = $this
        ->getMockBuilder(PiInstructionResponseFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $instructionResponseFactory->expects($this->once())->method('create')->willReturn($instructionResponse);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"instructionType":"abort"}')
            ->willReturn($this->httpResponseMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{"instructionType": "abort","date": "2015-08-11T11:45:16.285+01:00"}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "instructionRequest"         => $piInstructionRequestFactory,
                "instructionResponse"        => $instructionResponseFactory,
            ]
        );
        $result = $this->pirestApiModel->abort(self::TEST_VPS_TX_ID);
        $this->assertEquals($result->getInstructionType(), self::ABORT_INSTRUCTION_TYPE);
        $this->assertEquals($result->getDate(), "2015-08-11T11:45:16.285+01:00");
    }

    public function testReleaseSuccess()
    {
        $piInstructionRequest = $this
            ->getMockBuilder(PiInstructionRequest::class)
        ->disableOriginalConstructor()
            ->setMethods(['setInstructionType', '__toArray', 'setAmount'])
            ->getMock();
        $piInstructionRequest->expects($this->once())->method('setInstructionType')->with("release");
        $piInstructionRequest->expects($this->once())->method('setAmount')->with(9738);
        $piInstructionRequest->expects($this->once())->method('__toArray')
            ->willReturn(["instructionType" => "release", "amount" => 9738]);

        $piInstructionRequestFactory = $this
            ->getMockBuilder(PiInstructionRequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $piInstructionRequestFactory->expects($this->once())->method('create')->willReturn($piInstructionRequest);

        $instructionResponse = $this
            ->getMockBuilder(PiInstructionResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toArray'])
            ->getMock();

        $instructionResponseFactory = $this
        ->getMockBuilder(PiInstructionResponseFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $instructionResponseFactory->expects($this->once())->method('create')->willReturn($instructionResponse);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"instructionType":"release","amount":9738}')
            ->willReturn($this->httpResponseMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(json_decode('{"instructionType": "release","date": "2015-08-11T11:45:16.285+01:00"}'));

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "config"                     => $this->configMock,
                "apiExceptionFactory"        => $this->apiExceptionFactoryMock,
                "instructionRequest"         => $piInstructionRequestFactory,
                "instructionResponse"        => $instructionResponseFactory,
            ]
        );
        $result = $this->pirestApiModel->release(self::TEST_VPS_TX_ID, 97.38);
        $this->assertEquals($result->getInstructionType(), "release");
        $this->assertEquals($result->getDate(), "2015-08-11T11:45:16.285+01:00");
    }

    public function testRepeatSuccess()
    {
        $repeatRequestFactoryMock = $this
            ->getMockBuilder(self::REPEAT_REQUEST_FACTORY)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $repeatRequestFactoryMock->expects($this->once())->method('create')->willReturn($this->makeRepeatRequestMock());

        $this->verifyRestApiSetUrlCalledOnce();

        $this->verifyResponseCalledOnceReturns201();

        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn($this->getRepeatOkResponseJson());

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with($this->getRepeatRequestPostJsonString())
            ->willReturn($this->httpResponseMock);

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $piTransactionResult = $this->makeTransactionResultMockOk();
        $piTransactionResult->expects($this->once())->method('setTransactionId')
            ->with("1F910D3F-65EB-9B79-4ABA-D46C182DC78C");
        $piTransactionResult->expects($this->once())->method('setTransactionType')->with("Repeat");

        $amountResult = $this->getMockBuilder(PiTransactionResultAmount::class)
            ->disableOriginalConstructor()
            ->getMock();
        $amountResult->expects($this->once())->method('setSaleAmount')->with(3840);
        $amountResult->expects($this->once())->method('setTotalAmount')->with(3840);
        $amountResult->expects($this->once())->method('setSurchargeAmount')->with(0);

        $piTransactionResult->expects($this->once())->method('setAmount')->with($amountResult);

        $cardResult = $this->makeCardResultMockMastercard();
        $cardResult->expects($this->once())->method('setExpiryDate')->with("0219");

        $cardResultFactory = $this->makeCardResultFactoryMock($cardResult);

        $piResultFactory = $this->makeTransactionResultFactoryMock($piTransactionResult);

        $payResult = $this
            ->getMockBuilder(PiTransactionResultPaymentMethod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodResultFactory = $this->makePaymentMethodResultFactoryMock($payResult);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                'config'                     => $this->configMock,
                'repeatRequest'              => $repeatRequestFactoryMock,
                'piCaptureResultFactory'     => $piResultFactory,
                'httpRestFactory'            => $this->httpRestFactoryMock,
                'cardResultFactory'          => $cardResultFactory,
                'paymentMethodResultFactory' => $paymentMethodResultFactory,
                'amountResultFactory'        => $this->makeAmountResultFactory($amountResult)
            ]
        );

        $this->pirestApiModel->repeat(
            'RT-2018-04-10-1731151523381475',
            'ABA09B63-6ABA-F04F-45EE-5FCF8935915D',
            'GBP',
            3840,
            'REPEAT deferred transaction from Magento.'
        );
    }

    public function testRefundSucess()
    {
        $refundRequestMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\PiRefundRequest::class)
            ->setMethods(['__toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $refundRequestMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn(['vendorName' => 'testvendorname']);
        $refundRequestFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\SagePayData\PiRefundRequestFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $refundRequestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($refundRequestMock);
        $this->verifyRestApiSetUrlCalledOnce();

        $this->configMock
            ->expects($this->once())
            ->method('setConfigurationScopeId')
            ->with(1)
            ->willReturnSelf();

        $this->httpRestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpRestMock);

        $this->verifyResponseCalledOnceReturns201();
        $this->httpResponseMock
            ->expects($this->once())
            ->method('getResponseData')
            ->willReturn(
                json_decode(
                    '
                    {
                    "statusCode": "0000",
                    "statusDetail": "The Authorisation was Successful.",
                    "transactionId": "043D6711-E722-ACC6-2C2E-B03E00BF7603",
                    "transactionType": "Refund",
                    "retrievalReference": 13551640,
                    "bankAuthorisationCode": "999778",
                    "paymentMethod": {
                        "card": {
                            "cardType": "MasterCard",
                            "lastFourDigits": "0001",
                            "expiryDate": "0520"
                        }
                    },
                    "status": "Ok"
                    }
                    '
                )
            );

        $this->httpRestMock
            ->expects($this->once())
            ->method('executePost')
            ->with('{"vendorName":"testvendorname"}')
            ->willReturn($this->httpResponseMock);

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('sageLog');

        $this->httpRestMock
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($loggerMock);

        $piTransactionResult = $this
            ->getMockBuilder(PiTransactionResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $piTransactionResult->expects($this->once())->method('setStatusCode')->with("0000");
        $piTransactionResult->expects($this->once())->method('setStatusDetail')->with("The Authorisation was Successful.");
        $piTransactionResult->expects($this->once())->method('setTransactionId')->with("043D6711-E722-ACC6-2C2E-B03E00BF7603");
        $piTransactionResult->expects($this->once())->method('setStatus')->with("Ok");
        $piTransactionResult->expects($this->once())->method('setTransactionType')->with("Refund");
        $piTransactionResult->expects($this->once())->method('setRetrievalReference')->with("13551640");
        $piTransactionResult->expects($this->once())->method('setBankAuthCode')->with("999778");
        $piTransactionResult->expects($this->never())->method('setBankResponseCode');

        $cardResult = $this->makeCardResultMockMastercard();
        $cardResult->expects($this->once())->method('setExpiryDate')->with("0520");

        $cardResultFactory = $this->makeCardResultFactoryMock($cardResult);

        $piResultFactory = $this->makeTransactionResultFactoryMock($piTransactionResult);

        $payResult = $this
            ->getMockBuilder(PiTransactionResultPaymentMethod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodResultFactory = $this->makePaymentMethodResultFactoryMock($payResult);

        $this->pirestApiModel  = $this->objectManager->getObject(
            self::PI_REST,
            [
                "config"                     => $this->configMock,
                "refundRequest"              => $refundRequestFactoryMock,
                "piCaptureResultFactory"     => $piResultFactory,
                "httpRestFactory"            => $this->httpRestFactoryMock,
                "cardResultFactory"          => $cardResultFactory,
                "paymentMethodResultFactory" => $paymentMethodResultFactory
            ]
        );

        $this->pirestApiModel->refund(
            "R000000122-2016-12-22-1423481482416628",
            self::TEST_VPS_TX_ID,
            10800,
            "GBP",
            1
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeQuoteMock()
    {
        $quoteMock = $this->getMockBuilder("\Magento\Quote\Model\Quote")->disableOriginalConstructor()->getMock();
        $quoteMock->expects($this->once())->method("getStoreId")->willReturn(1);

        return $quoteMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeCardResultMockMastercard()
    {
        $cardResult = $this->getMockBuilder(PiTransactionResultCard::class)->disableOriginalConstructor()->getMock();
        $cardResult->expects($this->never())->method('setCardIdentifier');
        $cardResult->expects($this->never())->method('setIsReusable');
        $cardResult->expects($this->once())->method('setCardType')->with("MasterCard");
        $cardResult->expects($this->once())->method('setLastFourDigits')->with("0001");

        return $cardResult;
    }

    /**
     * @return mixed
     */
    private function getRepeatOkResponseJson(): \stdClass
    {
        return json_decode('
                    {
                        "statusCode": "0000",
                        "statusDetail": "The Authorisation was Successful.",
                        "transactionId": "1F910D3F-65EB-9B79-4ABA-D46C182DC78C",
                        "transactionType": "Repeat",
                        "retrievalReference": 17731745,
                        "bankAuthorisationCode": "999778",
                        "paymentMethod": {
                            "card": {
                                "cardType": "MasterCard",
                                "lastFourDigits": "0001",
                                "expiryDate": "0219"
                            }
                        },
                        "amount": {
                            "totalAmount": 3840,
                            "saleAmount": 3840,
                            "surchargeAmount": 0
                        },
                        "currency": "GBP",
                        "fiRecipient": {},
                        "status": "Ok"
                    }
                    ');
    }

    /**
     * @return string
     */
    private function getRepeatRequestPostJsonString(): string
    {
        return '{"transactionType":"Repeat","referenceTransactionId":"ABA09B63-6ABA-F04F-45EE-5FCF8935915D","vendorTxCode":"RT-2018-04-10-1731151523381475","amount":3840,"currency":"GBP","description":"REPEAT deferred transaction from Magento."}';
    }

    /**
     * @return array
     */
    private function getRepeatRequestAsArray(): array
    {
        return [
            'transactionType'        => 'Repeat',
            'referenceTransactionId' => 'ABA09B63-6ABA-F04F-45EE-5FCF8935915D',
            'vendorTxCode'           => 'RT-2018-04-10-1731151523381475',
            'amount'                 => 3840,
            'currency'               => 'GBP',
            'description'            => 'REPEAT deferred transaction from Magento.',
        ];
    }

    /**
     * @param $amountResult
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeAmountResultFactory($amountResult): \PHPUnit_Framework_MockObject_MockObject
    {
        $amountResultFactory = $this->getMockBuilder(self::TRANSACTION_RESULT_AMOUNT_FACTORY)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $amountResultFactory->expects($this->once())->method('create')->willReturn($amountResult);

        return $amountResultFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeTransactionResultMockOk()
    {
        $piTransactionResult = $this->getMockBuilder(PiTransactionResult::class)
            ->disableOriginalConstructor()->getMock();
        $piTransactionResult->expects($this->once())->method('setStatusCode')->with("0000");
        $piTransactionResult->expects($this->once())->method('setStatusDetail')
            ->with("The Authorisation was Successful.");
        $piTransactionResult->expects($this->once())->method('setRetrievalReference')->with("17731745");
        $piTransactionResult->expects($this->once())->method('setStatus')->with("Ok");
        $piTransactionResult->expects($this->once())->method('setBankAuthCode')->with("999778");
        $piTransactionResult->expects($this->never())->method('setBankResponseCode');

        return $piTransactionResult;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRepeatRequestMock()
    {
        $repeatRequestMock = $this->getMockBuilder(PiRepeatRequest::class)->disableOriginalConstructor()->getMock();
        $repeatRequestMock->expects($this->once())->method('setTransactionType')->with('Repeat');
        $repeatRequestMock->expects($this->once())->method('setVendorTxCode')->with('RT-2018-04-10-1731151523381475');
        $repeatRequestMock->expects($this->once())->method('setAmount')->with(3840);
        $repeatRequestMock->expects($this->once())->method('setCurrency')->with('GBP');
        $repeatRequestMock->expects($this->once())->method('setDescription')->with('REPEAT deferred transaction from Magento.');
        $repeatRequestMock->expects($this->once())->method('setReferenceTransactionId')->with('ABA09B63-6ABA-F04F-45EE-5FCF8935915D');
        $repeatRequestMock->expects($this->once())->method('__toArray')->willReturn($this->getRepeatRequestAsArray());

        return $repeatRequestMock;
    }

    /**
     * @param $cardResult
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeCardResultFactoryMock($cardResult)
    {
        $cardResultFactory = $this->getMockBuilder(self::TRANSACTION_RESULT_CARD_FACTORY)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $cardResultFactory->expects($this->once())->method('create')->willReturn($cardResult);

        return $cardResultFactory;
    }

    /**
     * @param $piTransactionResult
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeTransactionResultFactoryMock($piTransactionResult)
    {
        $piResultFactory = $this->getMockBuilder(self::TRANSACTION_RESULT_FACTORY)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $piResultFactory->expects($this->once())->method('create')->willReturn($piTransactionResult);

        return $piResultFactory;
    }

    /**
     * @param $payResult
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makePaymentMethodResultFactoryMock($payResult)
    {
        $paymentMethodResultFactory = $this->getMockBuilder(self::TRANSACTION_RESULT_PAYMENT_METHOD_FACTORY)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $paymentMethodResultFactory->expects($this->once())->method('create')->willReturn($payResult);

        return $paymentMethodResultFactory;
    }

    private function verifyResponseCalledOnceReturns201()
    {
        $this->httpResponseMock->expects($this->once())->method('getStatus')->willReturn(201);
    }

    private function verifyRestApiSetUrlCalledOnce()
    {
        $this->httpRestMock->expects($this->once())->method('setUrl');
    }
}
