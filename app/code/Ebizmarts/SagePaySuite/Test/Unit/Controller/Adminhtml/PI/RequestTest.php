<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\PI;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    private $contextMock;
    private $configMock;
    private $suiteHelperMock;
    private $pirestapiMock;
    private $quoteSessionMock;
    private $requestHelperMock;
    private $piRequestMock;
    private $requestMock;
    private $paymentMock;

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Controller\Adminhtml\PI\Request
     */
    private $piRequestController;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var  \Magento\Quote\Model\QuoteManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagementMock;

    /**
     * @var  \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    private $adminOrder;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $piModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\PI')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')->disableOriginalConstructor()->getMock();
        $this->paymentMock->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($piModelMock));

        $addressMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(["getGrandTotal", "getQuoteCurrencyCode", "getPayment", "getBillingAddress"])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getGrandTotal')
            ->will($this->returnValue(100));
        $quoteMock->expects($this->any())
            ->method('getQuoteCurrencyCode')
            ->will($this->returnValue('USD'));
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($addressMock));

        $quoteSessionMock = $this
            ->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http', [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($resultFactoryMock));
        $contextMock->expects($this->any())
            ->method('getBackendUrl')
            ->will($this->returnValue($urlBuilderMock));

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $suiteHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $suiteHelperMock->expects($this->any())
            ->method('generateVendorTxCode')
            ->will($this->returnValue("10000001-2015-12-12-12-12345"));

        $pirestapiMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\PIRest')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $this->orderMock->expects($this->any())
            ->method('place')
            ->willReturnSelf();

        $this->quoteManagementMock = $this
            ->getMockBuilder('Magento\Quote\Model\QuoteManagement')
            ->setConstructorArgs(['context' => $contextMock])
            ->disableOriginalConstructor()
            ->getMock();

        $requestHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Request')
            ->setMethods(["populatePaymentAmount", "getOrderDescription"])
            ->disableOriginalConstructor()
            ->getMock();
        $requestHelperMock->expects($this->any())
            ->method('populatePaymentAmount')
            ->will($this->returnValue([]));
        $requestHelperMock->expects($this->any())
            ->method('getOrderDescription')
            ->will($this->returnValue("description"));

        $this->adminOrder = $this->getMockBuilder('Magento\Sales\Model\AdminOrder\Create', [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adminOrder->method('setIsValidate')->willReturnSelf();
        $this->adminOrder->method('importPostData')->willReturnSelf();
        $objManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager', [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();
        $objManager->method('get')->willReturn($this->adminOrder);
        $contextMock->method('getObjectManager')
            ->willReturn($objManager);

        $piRequestMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\PiRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequestData'])
            ->getMock();

        $this->contextMock         = $contextMock;
        $this->configMock          = $configMock;
        $this->suiteHelperMock     = $suiteHelperMock;
        $this->pirestapiMock       = $pirestapiMock;
        $this->quoteSessionMock    = $quoteSessionMock;
        $this->requestHelperMock   = $requestHelperMock;
        $this->piRequestMock       = $piRequestMock;
    }
    // @codingStandardsIgnoreEnd

    public function postProvider()
    {
        $customFormPostData                       = new \stdClass();
        $customFormPostData->merchant_session_key = '12345';
        $customFormPostData->card_identifier      = '123456';
        $customFormPostData->card_last4           = '0006';
        $customFormPostData->card_exp_month       = '02';
        $customFormPostData->card_exp_year        = '22';
        $customFormPostData->card_type            = 'VISA';

        $threeDStatus = new \stdClass();
        $threeDStatus->status = "Authenticated";

        $customFormCaptureData                = new \stdClass();
        $customFormCaptureData->statusCode    = \Ebizmarts\SagePaySuite\Model\Config::SUCCESS_STATUS;
        $customFormCaptureData->transactionId = self::TEST_VPSTXID;
        $customFormCaptureData->statusDetail  = 'The Authorisation was Successful.';
        $customFormCaptureData->{"3DSecure"}  = $threeDStatus;

        $dropinPostData                       = new \stdClass();
        $dropinPostData->merchant_session_key = '12345';
        $dropinPostData->card_identifier      = 'FE646772-6C9F-478B-BF11-9087105FD372';
        $dropinPostData->card_last4           = '';
        $dropinPostData->card_exp_month       = '';
        $dropinPostData->card_exp_year        = '';
        $dropinPostData->card_type            = '';

        $dropinCaptureData = '{
        "statusCode": "0000",
        "statusDetail": "The Authorisation was Successful.",
        "transactionId": "' . self::TEST_VPSTXID . '",
        "transactionType": "Payment",
        "retrievalReference": 13748340,
        "bankResponseCode": "00",
        "bankAuthorisationCode": "99972",
        "paymentMethod": {
            "card": {
                "cardType": "AmericanExpress",
                "lastFourDigits": "0004",
                "expiryDate": "0419",
                "cardIdentifier": "FE646772-6C9F-478B-BF11-9087105FD372",
                "reusable": false
            }
        },
        "status": "Ok",
        "3DSecure": {
            "status": "NotChecked"
        }
        }';

        return [
            'custom form' => [
                'postData'    => $customFormPostData,
                'captureData' => $customFormCaptureData,
                'expectedResponse' => [
                    "success" => true,
                    'response' => (object)[
                        "statusCode" => '0000',
                        "transactionId" => self::TEST_VPSTXID,
                        "statusDetail" => "The Authorisation was Successful.",
                        "redirect" => null,
                        "3DSecure" => $threeDStatus
                    ]
                ]
            ],
            'dropin' => [
                'postData'    => $dropinPostData,
                'captureData' => json_decode($dropinCaptureData),
                'expectedResponse' => [
                    "success" => true,
                    'response' => (object)[
                        "statusCode"    => '0000',
                        "transactionId" => self::TEST_VPSTXID,
                        "statusDetail"  => "The Authorisation was Successful.",
                        "redirect"      => null,
                        "transactionType" => "Payment",
                        "retrievalReference" => 13748340,
                        "bankResponseCode" => "00",
                        "bankAuthorisationCode" => "99972",
                        "status" => "Ok",
                        "3DSecure" => (object)[
                            'status' => 'NotChecked'
                        ],
                        "paymentMethod" => (object)[
                            "card" => (object)[
                                "cardType" => "AmericanExpress",
                                "lastFourDigits" => "0004",
                                "expiryDate" => "0419",
                                "cardIdentifier" => "FE646772-6C9F-478B-BF11-9087105FD372",
                                "reusable" => false
                            ]
                        ]
                    ]
                ],
            ]];
    }

    public function testExecuteSUCCESS()
    {
        $this->configMock->expects($this->once())->method('getMode')->willReturn("test");
        $this->configMock->expects($this->once())->method('getVendorname')->willReturn("testvendorname");
        $this->configMock->expects($this->once())->method('getSagepayPaymentAction')->willReturn("Payment");

        $piRequestManagerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiRequestManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerMock->expects($this->once())->method('setMode')->with("test");
        $piRequestManagerMock->expects($this->once())->method('setVendorName')->with("testvendorname");
        $piRequestManagerMock->expects($this->once())->method('setPaymentAction')->with("Payment");
        $piRequestManagerMock->expects($this->once())->method('setMerchantSessionKey');
        $piRequestManagerMock->expects($this->once())->method('setCardIdentifier');
        $piRequestManagerMock->expects($this->once())->method('setCcExpMonth');
        $piRequestManagerMock->expects($this->once())->method('setCcExpYear');
        $piRequestManagerMock->expects($this->once())->method('setCcLastFour');
        $piRequestManagerMock->expects($this->once())->method('setCcType');
        $piRequestManagerMock->expects($this->once())->method('setJavascriptEnabled');
        $piRequestManagerMock->expects($this->once())->method('setAcceptHeaders');
        $piRequestManagerMock->expects($this->once())->method('setLanguage');
        $piRequestManagerMock->expects($this->once())->method('setUserAgent');
        $piRequestManagerMock->expects($this->once())->method('setJavaEnabled');
        $piRequestManagerMock->expects($this->once())->method('setColorDepth');
        $piRequestManagerMock->expects($this->once())->method('setScreenWidth');
        $piRequestManagerMock->expects($this->once())->method('setScreenHeight');
        $piRequestManagerMock->expects($this->once())->method('setTimezone');

        $piRequestManagerFactoryMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $piResultInterfaceMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $piResultInterfaceMock->expects($this->once())->method('__toArray')->willReturn(
            [
                "success"  => true,
                "response" => "https://example.com/admin/sales/order/view/order_id/888"
            ]
        );

        $this->setRequestAsserts();

        $requesterMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\PiRequestManagement\MotoManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requesterMock->expects($this->once())->method('setRequestdata');
        $requesterMock->expects($this->once())->method('setQuote');
        $requesterMock->expects($this->once())->method('placeOrder')->willReturn($piResultInterfaceMock);

        $objectManagerHelper       = new ObjectManagerHelper($this);
        $this->piRequestController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\PI\Request',
            [
                "context"                     => $this->contextMock,
                "config"                      => $this->configMock,
                "requester"                   => $requesterMock,
                "quoteSession"                => $this->quoteSessionMock,
                "piRequestManagerDataFactory" => $piRequestManagerFactoryMock
            ]
        );

        $this->piRequestController->execute();
    }

    /**
     * @expectedException \Ebizmarts\SagePaySuite\Model\Api\ApiException
     * @expectedExceptionMessage Invalid transaction.
     */
    public function testExecuteERROR()
    {
        $piRequestManagerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiRequestManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiException = new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
            new \Magento\Framework\Phrase("Invalid transaction."),
            new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase("INVALID"))
        );

        $this->setRequestAsserts();

        $requesterMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\PiRequestManagement\MotoManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requesterMock->expects($this->once())->method('setRequestdata');
        $requesterMock->expects($this->once())->method('setQuote');
        $requesterMock->expects($this->once())->method('placeOrder')->willThrowException($apiException);

        $piRequestManagerFactoryMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $objectManagerHelper       = new ObjectManagerHelper($this);
        $this->piRequestController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\PI\Request',
            [
                "context"                     => $this->contextMock,
                "config"                      => $this->configMock,
                "requester"                   => $requesterMock,
                "quoteSession"                => $this->quoteSessionMock,
                "piRequestManagerDataFactory" => $piRequestManagerFactoryMock
            ]
        );

        $this->piRequestController->execute();
    }

    /**
     * @param $result
     */
    private function _expectResultJson($result)
    {
        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with($result);
    }

    private function setRequestAsserts()
    {
        $this->requestMock->expects($this->exactly(15))->method('getPost')->withConsecutive(['merchant_session_key'],
                ['card_identifier'], ['card_exp_month'], ['card_exp_year'], ['card_last4'], ['card_type'],
                ['javascript_enabled'], ['accept_headers'], ['language'], ['user_agent'], ['java_enabled'],
                ['color_depth'], ['screen_width'], ['screen_height'],
                ['timezone'])->willReturnOnConsecutiveCalls('1B436F04-E6B5-4785-A5B9-B28E9DDC1B92',
                '74D926FD-5E87-451A-A089-7F6B4D1A76C9', '', '', '', '', 1, '*/*', 'en-US', 'Mozilla\/5.0', 0, 24, 1024,
                768, 180);
    }
}
