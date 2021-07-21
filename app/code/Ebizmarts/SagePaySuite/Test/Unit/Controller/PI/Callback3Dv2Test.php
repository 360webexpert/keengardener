<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\PI;

use Ebizmarts\SagePaySuite\Controller\PI\Callback3Dv2;
use Magento\Checkout\Model\Session;
use Ebizmarts\SagePaySuite\Model\Session as SagePaySession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Quote\Model\QuoteRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Callback3Dv2Test extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    /** Sage Pay Transaction ID*/
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    const ORDER_ID = '50';
    const ENCRYPTED_ORDER_ID = '0:3:slozTfXK0r1J23OPKHZkGsqJqT4wudHXPZJXxE9S';
    const ENCODED_ORDER_ID = 'MDozOiswMXF3V0l1WFRLTDRra0wxUCtYSGgyQVdORUdWaXNPN3N5RUNEbzE,';

    const QUOTE_ID = '51';
    const ENCRYPTED_QUOTE_ID = '0:3:hm2arLCQeFcC1C0kU6CEoy06RnjtBZ1jzMomH3+A';
    const ENCODED_QUOTE_ID = 'MDozOlBxWWxwSHdsUklEa3dLY0Q2TlVJTE9YOEZjYjNCbWY2VUVaT1QrN2U,';

    const CUSTOMER_ID = '231';
    const CRES = "12345678";

    /** @var Callback3Dv2 */
    private $callback3Dv2Controller;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var Http|\PHPUnit_Framework_MockObject_MockObject */
    private $responseMock;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $redirectMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilderMock;

    /** @var CryptAndCodeData */
    private $cryptAndCodeMock;

    /** @var OrderLoader|\PHPUnit_Framework_MockObject_MockObject */
    private $orderLoaderMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }
    // @codingStandardsIgnoreEnd

    public function testExecuteSUCCESS()
    {
        $checkoutSessionMock = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRepositoryMock = $this
            ->getMockBuilder(quoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderLoaderMock = $this
            ->getMockBuilder(OrderLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock
            ->expects($this->once())
            ->method('getQuoteId')
            ->willReturn(self::QUOTE_ID);

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeRequestMock();

        $this->redirectMock = $this
            ->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this
            ->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerMock = $this->makeRequestManagerMock();

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($piRequestManagerMock);

        $resultMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock
            ->expects($this->once())
            ->method('getErrorMessage')
            ->willReturnArgument(null);

        $this->requestMock->expects($this->once())->method('getParam')->with('quoteId')->willReturn(self::ENCODED_QUOTE_ID);

        $this->cryptAndCodeMock->expects($this->once())->method('decodeAndDecrypt')->with(self::ENCODED_QUOTE_ID)->willReturn(self::QUOTE_ID);
        $quoteRepositoryMock->expects($this->once())->method('get')->with(self::QUOTE_ID)->willReturn($quoteMock);
        $orderLoaderMock->expects($this->once())->method('loadOrderFromQuote')->with($quoteMock)->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getId')->willReturn(self::ORDER_ID);

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->callback3Dv2Controller = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3Dv2',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'quoteRepository'             => $quoteRepositoryMock,
                'cryptAndCode'                => $this->cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock, 
                'orderLoader'                 => $orderLoaderMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/onepage/success', ['_secure' => true])
            . '";</script>'
        );

        $this->callback3Dv2Controller->execute();
    }

    public function testExecuteERROR()
    {
        $checkoutSessionMock = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRepositoryMock = $this
            ->getMockBuilder(quoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderLoaderMock = $this
            ->getMockBuilder(OrderLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->setMethods(['getPost', 'setParams', 'getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('cres')
            ->will($this->returnValue(self::CRES));

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $messageManagerMock->expects($this->once())->method('addError')->with('Invalid 3D secure authentication.');

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerMock = $this->makeRequestManagerMock();

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $resultMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock
            ->expects($this->exactly(2))->method('getErrorMessage')->willReturn('Invalid 3D secure authentication.');

        $this->cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->once())->method('getParam')->with('quoteId')->willReturn(self::ENCODED_QUOTE_ID);
        $this->cryptAndCodeMock->expects($this->once())->method('decodeAndDecrypt')->with(self::ENCODED_QUOTE_ID)->willReturn(self::QUOTE_ID);
        $quoteRepositoryMock->expects($this->once())->method('get')->with(self::QUOTE_ID)->willReturn($quoteMock);
        $orderLoaderMock->expects($this->once())->method('loadOrderFromQuote')->with($quoteMock)->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getId')->willReturn(self::ORDER_ID);

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->callback3Dv2Controller = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3Dv2',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'quoteRepository'             => $quoteRepositoryMock,
                'cryptAndCode'                => $this->cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'orderLoader'                 => $orderLoaderMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/cart', ['_secure' => true, 'quoteId' => self::ENCRYPTED_QUOTE_ID])
            . '";</script>'
        );

        $this->callback3Dv2Controller->execute();
    }

    /**
     * @param $body
     */
    private function expectSetBody($body)
    {
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($body);
    }

    /**
     * @param $messageManagerMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock($messageManagerMock)
    {
        $contextMock = $this
            ->getMockBuilder('Magento\Framework\App\Action\Context')->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getRedirect')->will($this->returnValue($this->redirectMock));
        $contextMock->expects($this->any())->method('getMessageManager')->will($this->returnValue($messageManagerMock));
        $contextMock->expects($this->any())->method('getUrl')->will($this->returnValue($this->urlBuilderMock));

        return $contextMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRequestManagerMock()
    {
        $piRequestManagerMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManager')
            ->disableOriginalConstructor()->getMock();
        $piRequestManagerMock->expects($this->once())->method('setTransactionId');
        $piRequestManagerMock->expects($this->once())->method('setCres')->with(self::CRES);
        $piRequestManagerMock->expects($this->once())->method('setVendorName');
        $piRequestManagerMock->expects($this->once())->method('setMode');
        $piRequestManagerMock->expects($this->once())->method('setPaymentAction');

        return $piRequestManagerMock;
    }

    /**
     * @param $resultMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeThreeDCallbackManagementMock($resultMock)
    {
        $threeDCallbackManagementMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement')
            ->disableOriginalConstructor()->getMock();
        $threeDCallbackManagementMock->expects($this->once())->method('setRequestData');
        $threeDCallbackManagementMock->expects($this->once())->method('placeOrder')->willReturn($resultMock);

        return $threeDCallbackManagementMock;
    }

    private function makeRequestMock()
    {
        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->setMethods(['getPost', 'setParams', 'getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('cres')
            ->will($this->returnValue(self::CRES));

        $this->cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cryptAndCodeMock
            ->expects($this->exactly(2))
            ->method('encryptAndEncode')
            ->withConsecutive([self::ORDER_ID], [self::QUOTE_ID])
            ->willReturnOnConsecutiveCalls(self::ENCODED_ORDER_ID, self::ENCODED_QUOTE_ID);

        $this->requestMock
            ->expects($this->once())
            ->method('setParams')
            ->with(['orderId' => self::ENCODED_ORDER_ID, 'quoteId' => self::ENCODED_QUOTE_ID])
            ->willReturnSelf();
    }
}
