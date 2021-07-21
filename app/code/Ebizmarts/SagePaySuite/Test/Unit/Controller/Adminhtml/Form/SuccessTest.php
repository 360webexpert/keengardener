<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\Form;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SuccessTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Controller\Adminhtml\Form\Success
     */
    private $formSuccessController;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var  \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var \Magento\Quote\Model\QuoteManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_quoteManagementMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_messageManagerMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $formModelMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formModelMock->method('markAsInitialized')->willReturnSelf();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock
            ->expects($this->once())
            ->method('setCcExpMonth')
            ->with('04');
        $paymentMock
            ->expects($this->once())
            ->method('setCcExpYear')
            ->with('19');

        $paymentMock->method('getMethodInstance')->willReturn($formModelMock);

        $quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

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

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $actionFlagMock = $this
            ->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock = $this
            ->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->_messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getBackendUrl')
            ->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($quoteSessionMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($helperMock));

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock
            ->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

        $closedActionMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config\ClosedForAction::class)
            ->setMethods(['getActionClosedForPaymentAction'])
            ->disableOriginalConstructor()
            ->getMock();
        $closedActionMock
            ->expects($this->any())
            ->method('getActionClosedForPaymentAction')
            ->willReturn(['capture', true]);
        $actionFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $actionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($closedActionMock);

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $transactionFactoryMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $transactionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $formModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $formModelMock->expects($this->any())
            ->method('decodeSagePayResponse')
            ->will($this->returnValue([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "ExpiryDate"     => "0419",
                "3DSecureStatus" => "OK"
            ]));

        $this->_quoteManagementMock = $this
            ->getMockBuilder('Magento\Quote\Model\QuoteManagement')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->formSuccessController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Form\Success',
            [
                'context'            => $contextMock,
                'config'             => $configMock,
                'quoteSession'       => $quoteSessionMock,
                'checkoutHelper'     => $this->checkoutHelperMock,
                'transactionFactory' => $transactionFactoryMock,
                'formModel'          => $formModelMock,
                'quoteManagement'    => $this->_quoteManagementMock,
                'actionFactory'      => $actionFactoryMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testExecuteSUCCESS()
    {
        $this->orderMock
            ->expects($this->once())
            ->method('place')
            ->willReturnSelf();

        $invoiceCollectionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceCollectionMock->expects($this->once())->method('setDataToAll')->willReturnSelf();
        $this->orderMock
            ->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($invoiceCollectionMock);

        $this->_quoteManagementMock->expects($this->once())
            ->method("submit")
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId');

        $this->formSuccessController->execute();
    }

    public function testExecuteERROR()
    {
        $this->_quoteManagementMock->expects($this->once())
            ->method("submit")
            ->willReturn(null);

        $this->_messageManagerMock->expects($this->once())
            ->method("addError")
            ->with('Your payment was successful but the order was NOT created: Can not create order');

        $this->formSuccessController->execute();
    }
}
