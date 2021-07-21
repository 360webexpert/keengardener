<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Magento\Framework\Encryption\EncryptorInterface;

class ServerRequestManagementTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }
    // @codingStandardsIgnoreEnd

    public function testSavePaymentInformationAndPlaceOrderNoToken()
    {
        $configMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->setMethods(
                [
                    'getBasketFormat',
                    'getSagepayPaymentAction',
                    'getVendorname',
                    'get3Dsecure',
                    'getAvsCvc',
                    'isGiftAidEnabled',
                    'getPaypalBillingAgreement',
                    'isServerLowProfileEnabled',
                    'getMode'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->once())->method('getBasketFormat')->willReturn("Disabled");
        $configMock->expects($this->exactly(2))->method('getSagepayPaymentAction')->willReturn("PAYMENT");
        $configMock->expects($this->exactly(2))->method('getMode')->willReturn("test");
        $configMock->expects($this->exactly(3))->method('getVendorname')->willReturn("testebizmarts");
        $configMock->expects($this->once())->method('get3Dsecure')->willReturn(0);
        $configMock->expects($this->once())->method('getAvsCvc')->willReturn(0);
        $configMock->expects($this->once())->method('isGiftAidEnabled')->willReturn(0);
        $configMock->expects($this->once())->method('getPaypalBillingAgreement')->willReturn(0);
        $configMock->expects($this->once())->method('isServerLowProfileEnabled')->willReturn(0);

        $helperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $postApiMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Api\Post::class)
            ->disableOriginalConstructor()
            ->getMock();

        $vpsTxId = "{D4EE4DC0-55D2-6250-2884-2343D0610119}";
        $nextUrl = "https://testcheckout.sagepay.com/gateway/service/cardselection?vpstxid=".$vpsTxId;
        $postApiMock->method('sendPost')->willReturn(
            [
                'data' => [
                    'VPSProtocol'  => "3.00",
                    'Status'       => "OK",
                    'StatusDetail' => "2014 : The Transaction was Registered Successfully.",
                    'VPSTxId'      => $vpsTxId,
                    "SecurityKey"  => "H8IBLZTYAC",
                    "NextURL"      => $nextUrl
                ]
            ]
        );

        $suiteLoggerMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->setMethods(['setTransactionId', 'setLastTransId', 'setAdditionalInformation', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('setTransactionId')->with("D4EE4DC0-55D2-6250-2884-2343D0610119");
        $paymentMock->expects($this->once())->method('setLastTransId')->with("D4EE4DC0-55D2-6250-2884-2343D0610119");
        $paymentMock->expects($this->exactly(5))->method('setAdditionalInformation');
        $paymentMock->expects($this->once())->method('save')->willReturnSelf();
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $orderMock->method('getId')->willReturn(456);

        $checkoutHelperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Checkout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutHelperMock->expects($this->once())->method('placeOrder')->willReturn($orderMock);

        $requestHelperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Request::class)
            ->setMethods(['populatePaymentAmountAndCurrency', 'populateAddressInformation'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestHelperMock->expects($this->once())->method('populatePaymentAmountAndCurrency')->willReturn(
            [
                'Amount' => 56.98,
                'Currency' => 'GBP',
            ]
        );
        $requestHelperMock->expects($this->once())->method('populateAddressInformation')->willReturn(
            [
                'CustomerEMail' => 'testcustomer@ebizmarts.com',
                'BillingSurname' => 'Surname',
                'BillingFirstnames' => 'BFirst Names',
                'BillingAddress1' => 'Alfa 1234',
                'BillingAddress2' => '',
                'BillingCity' => 'London',
                'BillingPostCode' => 'ABC 1234',
                'BillingCountry' => 'GB',
                'BillingPhone' => '0707089865857',
                'Deliveryurname' => 'Surname',
                'DeliveryFirstnames' => 'BFirst Names',
                'DeliveryAddress1' => 'Alfa 1234',
                'DeliveryAddress2' => '',
                'DeliveryCity' => 'London',
                'DeliveryPostCode' => 'ABC 1234',
                'DeliveryCountry' => 'GB',
                'DeliveryPhone' => '87415487'
            ]
        );

        $tokenModelMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Token::class)
            ->setMethods(['isCustomerUsingMaxTokenSlots'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenModelMock->expects($this->once())->method('isCustomerUsingMaxTokenSlots')->willReturn(false);

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['getQuote', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)->getMock();
        $customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock->expects($this->once())->method('getCustomerDataObject')->willReturn($customerMock);

        $quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreUrl = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactory = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getReservedOrderId')->willReturn(123);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn(456);

        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $checkoutSessionMock->expects($this->exactly(2))->method('setData')->withConsecutive(
            [
                $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY),
                $this->equalTo(456)
            ],
            [
                $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER),
                $this->equalTo(1)
            ]
        );

        $resultObject = $this->objectManagerHelper->getObject('\Ebizmarts\SagePaySuite\Api\Data\FormResult');

        $encryptorMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encryptorMock->expects($this->once())->method('encrypt')->with(456)->willReturn(
            '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0='
        );

        /** @var \Ebizmarts\SagePaySuite\Model\ServerRequestManagement $requestManager */
        $requestManager = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\ServerRequestManagement::class)
            ->setMethods(['getQuoteById'])
            ->setConstructorArgs(
                [
                    'config'             => $configMock,
                    'suiteHelper'        => $helperMock,
                    'postApi'            => $postApiMock,
                    'suiteLogger'        => $suiteLoggerMock,
                    'checkoutHelper'     => $checkoutHelperMock,
                    'requestHelper'      => $requestHelperMock,
                    'tokenModel'         => $tokenModelMock,
                    'checkoutSession'    => $checkoutSessionMock,
                    'customerSession'    => $customerSessionMock,
                    'result'             => $resultObject,
                    'quoteRepository'    => $quoteRepositoryMock,
                    'coreUrl'            => $coreUrl,
                    'quoteIdMaskFactory' => $quoteIdMaskFactory,
                    'encryptor'          => $encryptorMock
                ]
            )
            ->getMock();
        $requestManager->expects($this->once())->method('getQuoteById')->willReturn($quoteMock);

        $response = $requestManager->savePaymentInformationAndPlaceOrder(456, false, '%token');

        $this->assertTrue($response->getSuccess());
        $this->assertArrayHasKey('data', $response->getResponse());

        $responseData = $response->getResponse()['data'];
        $this->assertEquals("{D4EE4DC0-55D2-6250-2884-2343D0610119}", $responseData['VPSTxId']);
        $this->assertEquals('H8IBLZTYAC', $responseData['SecurityKey']);
        $this->assertEquals('3.00', $responseData['VPSProtocol']);
        $this->assertEquals('OK', $responseData['Status']);
        $this->assertEquals('2014 : The Transaction was Registered Successfully.', $responseData['StatusDetail']);
        $this->assertEquals(
            'https://testcheckout.sagepay.com/gateway/service/cardselection?vpstxid=' . $vpsTxId,
            $responseData['NextURL']
        );
    }

    public function testOrderFailingToSave()
    {
        $configMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->setMethods(
                [
                    'getBasketFormat',
                    'getSagepayPaymentAction',
                    'getVendorname',
                    'get3Dsecure',
                    'getAvsCvc',
                    'isGiftAidEnabled',
                    'getPaypalBillingAgreement',
                    'isServerLowProfileEnabled',
                    'getMode'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->once())->method('getBasketFormat')->willReturn("Disabled");
        $configMock->expects($this->once())->method('getSagepayPaymentAction')->willReturn("PAYMENT");
        $configMock->expects($this->once())->method('getMode')->willReturn("live");
        $configMock->expects($this->exactly(2))->method('getVendorname')->willReturn("liveebizmarts");
        $configMock->expects($this->once())->method('get3Dsecure')->willReturn(0);
        $configMock->expects($this->once())->method('getAvsCvc')->willReturn(0);
        $configMock->expects($this->once())->method('isGiftAidEnabled')->willReturn(0);
        $configMock->expects($this->once())->method('getPaypalBillingAgreement')->willReturn(0);
        $configMock->expects($this->once())->method('isServerLowProfileEnabled')->willReturn(1);

        $helperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $postApiMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Api\Post::class)
            ->disableOriginalConstructor()
            ->getMock();

        $error     = new \Magento\Framework\Phrase("Sage Pay is not available.");
        $exception = new \Ebizmarts\SagePaySuite\Model\Api\ApiException($error);
        $postApiMock->method('sendPost')->willThrowException($exception);

        $suiteLoggerMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->setMethods(['setTransactionId', 'setLastTransId', 'setAdditionalInformation', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutHelperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Checkout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestHelperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Request::class)
            ->setMethods(['populatePaymentAmountAndCurrency', 'populateAddressInformation'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestHelperMock->expects($this->once())->method('populatePaymentAmountAndCurrency')->willReturn(
            [
                'Amount' => 56.98,
                'Currency' => 'GBP',
            ]
        );
        $requestHelperMock->expects($this->once())->method('populateAddressInformation')->willReturn(
            [
                'CustomerEMail' => 'testcustomer@ebizmarts.com',
                'BillingSurname' => 'Surname',
                'BillingFirstnames' => 'BFirst Names',
                'BillingAddress1' => 'Alfa 1234',
                'BillingAddress2' => '',
                'BillingCity' => 'London',
                'BillingPostCode' => 'ABC 1234',
                'BillingCountry' => 'GB',
                'BillingPhone' => '0707089865857',
                'Deliveryurname' => 'Surname',
                'DeliveryFirstnames' => 'BFirst Names',
                'DeliveryAddress1' => 'Alfa 1234',
                'DeliveryAddress2' => '',
                'DeliveryCity' => 'London',
                'DeliveryPostCode' => 'ABC 1234',
                'DeliveryCountry' => 'GB',
                'DeliveryPhone' => '87415487'
            ]
        );

        $tokenModelMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Token::class)
            ->setMethods(['isCustomerUsingMaxTokenSlots'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenModelMock->expects($this->once())->method('isCustomerUsingMaxTokenSlots')->willReturn(false);

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['getQuote', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)->getMock();
        $customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock->expects($this->once())->method('getCustomerDataObject')->willReturn($customerMock);

        $quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreUrl = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactory = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getReservedOrderId')->willReturn(123);
        $quoteMock->expects($this->never())->method('getPayment')->willReturn($paymentMock);

        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);

        $encryptorMock = $this
            ->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encryptorMock->expects($this->once())->method('encrypt');

        $resultObject = $this->objectManagerHelper->getObject('\Ebizmarts\SagePaySuite\Api\Data\FormResult');

        /** @var \Ebizmarts\SagePaySuite\Model\ServerRequestManagement $requestManager */
        $requestManager = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\ServerRequestManagement::class)
            ->setMethods(['getQuoteById'])
            ->setConstructorArgs(
                [
                    'config'             => $configMock,
                    'suiteHelper'        => $helperMock,
                    'postApi'            => $postApiMock,
                    'suiteLogger'        => $suiteLoggerMock,
                    'checkoutHelper'     => $checkoutHelperMock,
                    'requestHelper'      => $requestHelperMock,
                    'tokenModel'         => $tokenModelMock,
                    'checkoutSession'    => $checkoutSessionMock,
                    'customerSession'    => $customerSessionMock,
                    'result'             => $resultObject,
                    'quoteRepository'    => $quoteRepositoryMock,
                    'coreUrl'            => $coreUrl,
                    'quoteIdMaskFactory' => $quoteIdMaskFactory,
                    'encryptor'          => $encryptorMock
                ]
            )
            ->getMock();
        $requestManager->expects($this->once())->method('getQuoteById')->willReturn($quoteMock);

        $response = $requestManager->savePaymentInformationAndPlaceOrder(456, false, '%token');

        $this->assertFalse($response->getSuccess());
        $this->assertEquals(
            "Something went wrong while generating the Sage Pay request: Sage Pay is not available.",
            $response->getErrorMessage()
        );
    }

    public function testGetQuoteById()
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $cartMock */
        $cartMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cartMock->expects($this->once())->method('getId')->willReturn(9876);

        $quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(9876)
            ->willReturn($cartMock);

        /** @var \Ebizmarts\SagePaySuite\Model\ServerRequestManagement $requestManager */
        $requestManager = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\ServerRequestManagement',
            [
                'quoteRepository'    => $quoteRepositoryMock
            ]
        );

        $return = $requestManager->getQuoteById(9876);

        $this->assertEquals(9876, $return->getId());
        $this->assertSame($cartMock, $return);
    }

    public function testGetQuoteRepository()
    {
        $quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Ebizmarts\SagePaySuite\Model\ServerRequestManagement $requestManager */
        $requestManager = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\ServerRequestManagement',
            [
                'quoteRepository'    => $quoteRepositoryMock
            ]
        );

        $return = $requestManager->getQuoteRepository();

        $this->assertSame($quoteRepositoryMock, $return);
    }

    public function testGetQuoteIdMaskFactory()
    {
        $quoteIdMaskFactoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Ebizmarts\SagePaySuite\Model\ServerRequestManagement $requestManager */
        $requestManager = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\ServerRequestManagement',
            [
                'quoteIdMaskFactory' => $quoteIdMaskFactoryMock
            ]
        );

        $return = $requestManager->getQuoteIdMaskFactory();

        $this->assertSame($quoteIdMaskFactoryMock, $return);
    }
}
