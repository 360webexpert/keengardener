<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Helper;

class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Checkout
     */
    private $checkoutHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderSenderMock;

    /** @var \Magento\Checkout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $checkoutHelperMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $customerMock = $this
            ->getMockBuilder('Magento\Customer\Model\Customer')
            ->setMethods(["getDefaultBilling"])
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock->expects($this->any())
            ->method('getDefaultBilling')
            ->will($this->returnValue(0));

        $this->quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->customerSessionMock = $this
            ->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepositoryMock = $this
            ->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($customerMock));

        $quoteManagementMock = $this
            ->getMockBuilder('Magento\Quote\Model\QuoteManagement')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteManagementMock->expects($this->any())
            ->method('submit')
            ->will($this->returnValue($this->orderMock));

        $objectCopyServiceMock = $this
            ->getMockBuilder('Magento\Framework\DataObject\Copy')
            ->disableOriginalConstructor()
            ->getMock();
        $objectCopyServiceMock->expects($this->any())
            ->method('getDataFromFieldset')
            ->will($this->returnValue([]));

        $dataObjectHelperMock = $this
            ->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectHelperMock->expects($this->any())
            ->method('populateWithArray')
            ->will($this->returnValue([]));

        $this->orderSenderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\OrderSender')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutHelperMock = $this->getMockBuilder(\Magento\Checkout\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->checkoutHelper = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Checkout',
            [
                'customerSession'    => $this->customerSessionMock,
                'checkoutSession'    => $checkoutSessionMock,
                'customerRepository' => $customerRepositoryMock,
                'quoteManagement'    => $quoteManagementMock,
                'objectCopyService'  => $objectCopyServiceMock,
                'dataObjectHelper'   => $dataObjectHelperMock,
                'orderSender'        => $this->orderSenderMock,
                'checkoutData'       => $this->checkoutHelperMock,
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testPlaceOrderCUSTOMER()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->checkoutHelper->placeOrder();
    }

    public function testPlaceOrderGUEST()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $addressMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue("test@example.com"));

        $this->quoteMock->expects($this->any())
            ->method('getCheckoutMethod')
            ->will($this->returnValue(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST));
        $this->quoteMock->expects($this->exactly(3))
            ->method('getBillingAddress')
            ->will($this->returnValue($addressMock));

        $this->quoteMock->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(true);

        $this->checkoutHelper->placeOrder();
    }

    public function testPlaceOrderREGISTER()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue(true));

        $addressInterfaceMock = $this
            ->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $addressInterfaceMock->expects($this->once())
            ->method('setIsDefaultShipping');

        $addressMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock->expects($this->once())
            ->method('exportCustomerAddress')
            ->will($this->returnValue($addressInterfaceMock));

        $this->quoteMock->expects($this->any())
            ->method('getCheckoutMethod')
            ->will($this->returnValue(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($addressMock));

        $this->quoteMock->expects($this->once())
            ->method('addCustomerAddress');

        $this->checkoutHelper->placeOrder();
    }

    public function testSendOrderEmail()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send');

        $this->checkoutHelper->sendOrderEmail($this->orderMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Can not save order. Please try another payment option.
     */
    public function testPlaceOrderException()
    {
        $customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()->getMock();
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $quoteManagementMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteManagementMock->expects($this->once())->method('submit')->willReturn(null);

        $customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(
                [
                    'getById',
                    'save',
                    'get',
                    'getList',
                    'delete',
                    'deleteById']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock->expects($this->any())->method('getById')->willReturnSelf();

        $checkoutHelperMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Checkout::class)
            ->setConstructorArgs(
                [
                    'context'            => $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'quoteManagement'    => $quoteManagementMock,
                    'orderSender'        => $this
                        ->getMockBuilder(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'customerSession'    => $customerSessionMock,
                    'checkoutData'       => $this->getMockBuilder(\Magento\Checkout\Helper\Data::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'dataObjectHelper'   => $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'customerRepository' => $customerRepositoryMock,
                    'objectCopyService'  => $this->getMockBuilder(\Magento\Framework\DataObject\Copy::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'checkoutSession'    => $checkoutSessionMock,
                    'suiteLogger'        => $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                ]
            )
            ->setMethods(['_prepareCustomerQuote'])
            ->getMock();

        $checkoutHelperMock->placeOrder();
    }

    public function testPlaceOrderNoMethodRegister()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->exactly(2))
            ->method('getCheckoutMethod')
            ->willReturn(null);

        $this->quoteMock->expects($this->once())
            ->method('setCheckoutMethod')->with('register')->willReturnSelf();

        $this->checkoutHelper->placeOrder();
    }

    public function testPlaceOrderNoMethodGuest()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->exactly(2))
            ->method('getCheckoutMethod')
            ->willReturn(null);

        $this->quoteMock->expects($this->once())
            ->method('setCheckoutMethod')->with('guest')->willReturnSelf();

        $this->checkoutHelperMock->expects($this->once())->method('isAllowedGuestCheckout')->willReturn(true);

        $this->checkoutHelper->placeOrder();
    }
}
