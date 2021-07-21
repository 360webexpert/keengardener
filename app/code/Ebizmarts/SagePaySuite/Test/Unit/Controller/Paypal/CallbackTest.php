<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Paypal;

use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\Payment;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\OrderRepository;

class CallbackTest extends \PHPUnit\Framework\TestCase
{

    const QUOTE_ID_ENCRYPTED = '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0=';
    const QUOTE_ID = 69;
    const ORDER_ID = 70;
    const ORDER_INCREMENT_ID = '000000001';

    /**
     * @var Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var OrderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var QuoteRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Config\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var RepositoryQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryQueryMock;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    private $checkoutSessionMock;

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Controller\Paypal\Callback
     */
    private $paypalCallbackController;

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
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /** @var \Ebizmarts\SagePaySuite\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $suiteHelperMock;
    private $encryptorMock;

    /** @var RecoverCart */
    private $recoverCartMock;

    /** @var OrderLoader */
    private $orderLoaderMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->setMethods(["getMethodInstance", "getLastTransId", "save"])
            ->disableOriginalConstructor()->getMock();
        $this->paymentMock->method('getMethodInstance')->willReturnSelf();

        $this->quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(['getGrandTotal', 'getPayment', 'getReservedOrderId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->any())
            ->method('getGrandTotal')
            ->will($this->returnValue(100));
        $this->quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));

        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(
                [
                    "getQuote",
                    "clearHelperData",
                    "setLastQuoteId",
                    "setLastSuccessQuoteId",
                    "setLastOrderId",
                    "setLastRealOrderId",
                    "setLastOrderStatus",
                    "setData"
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock->expects($this->any())->method('getParam')->with('quoteid')->willReturn(self::QUOTE_ID_ENCRYPTED);

        $this->redirectMock = $this->getMockBuilder(\Magento\Store\App\Response\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->setMethods(["getRequest","getResponse", "getRedirect", "getMessageManager"])
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
            ->will($this->returnValue($messageManagerMock));

        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods(["getPayment", "place", "getId"])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));

        $this->orderMock->method('place')->willReturnSelf();

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

        $postApiMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\Post')
            ->setMethods(["sendPost"])
            ->disableOriginalConstructor()
            ->getMock();
        $postApiMock->expects($this->any())
            ->method('sendPost')
            ->will($this->returnValue([
                "data" => [
                    "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                    "StatusDetail"   => "OK STATUS",
                    "3DSecureStatus" => "NOTCHECKED",
                    "AVSCV2" => "DATA NOT CHECKED",
                    "AddressResult" => "NOTPROVIDED",
                    "PostCodeResult" => "NOTPROVIDED",
                    "CV2Result" => "NOTPROVIDED"
                ]
            ]));

        $checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->setMethods(["placeOrder"])
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutHelperMock->expects($this->any())
            ->method('placeOrder')
            ->will($this->returnValue($this->orderMock));

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(["getId", 'getReservedOrderId'])
            ->getMock();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $closedForActionFactoryMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $closedForActionMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config\ClosedForAction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $closedForActionFactoryMock->method('create')->willReturn($closedForActionMock);

        $this->suiteHelperMock = $this->getMockBuilder("Ebizmarts\SagePaySuite\Helper\Data")
            ->disableOriginalConstructor()
            ->setMethods(["methodCodeIsSagePay"])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->recoverCartMock = $this
            ->getMockBuilder(RecoverCart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderLoaderMock = $this
            ->getMockBuilder(OrderLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper            = new ObjectManagerHelper($this);
        $this->paypalCallbackController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Paypal\Callback',
            [
                'context'            => $contextMock,
                'config'             => $this->configMock,
                'checkoutSession'    => $this->checkoutSessionMock,
                'checkoutHelper'     => $checkoutHelperMock,
                'postApi'            => $postApiMock,
                'transactionFactory' => $transactionFactoryMock,
                'quoteRepository'    => $this->quoteRepositoryMock,
                'actionFactory'      => $closedForActionFactoryMock,
                'suiteHelper'        => $this->suiteHelperMock,
                'encryptor'          => $this->encryptorMock,
                'recoverCart'        => $this->recoverCartMock,
                'quote'              => $this->quoteMock,
                '_repositoryQuery'   => $this->repositoryQueryMock,
                'orderLoader'        => $this->orderLoaderMock

            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function modeProvider()
    {
        return [
            'test live payment' => ['live', 'PAYMENT'],
            'test live deferred' => ['live', 'AUTHENTICATE'],
            'test deferred' => ['test', 'DEFERRED'],
            'test capture default' => ['test', null]
        ];
    }

    /**
     * @dataProvider modeProvider
     */
    public function testExecuteSUCCESS($mode, $paymentAction)
    {
        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($this->quoteMock)
            ->willReturn($this->orderMock);
        $this->configMock->method('getMode')->willReturn($mode);
        $this->configMock->method('getSagepayPaymentAction')->willReturn($paymentAction);
        $this->paymentMock->method('getLastTransId')->willReturn(self::TEST_VPSTXID);
        $this->orderMock->expects($this->once())->method('getId')->willReturn(self::ORDER_ID);
        $this->quoteMock->expects($this->exactly(3))->method('getId')->willReturn(self::QUOTE_ID);
        $this->checkoutSessionMock->expects($this->once())->method("clearHelperData")->willReturn(null);
        $this->checkoutSessionMock
            ->expects($this->once())->method("setLastQuoteId")->with(self::QUOTE_ID);
        $this->checkoutSessionMock
            ->expects($this->once())->method("setLastSuccessQuoteId")->with(self::QUOTE_ID);
        $this->checkoutSessionMock
            ->expects($this->once())->method("setLastOrderId")->with(self::ORDER_ID);

        $this->encryptorMock->expects($this->once())->method('decrypt')->with(self::QUOTE_ID_ENCRYPTED)
            ->willReturn(self::QUOTE_ID);

        $this->quoteRepositoryMock->expects($this->once())->method('get')
            ->with(self::QUOTE_ID)->willReturn($this->quoteMock);

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "PAYPALOK",
                "StatusDetail" => "OK STATUS SUCCESS",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "3DSecureStatus" => "NOTCHECKED",
                "AVSCV2" => "DATA NOT CHECKED",
                "AddressResult" => "NOTPROVIDED",
                "PostCodeResult" => "NOTPROVIDED",
                "CV2Result" => "NOTPROVIDED"
            ]));

        $this->_expectRedirect("checkout/onepage/success");
        $this->paypalCallbackController->execute();
    }

    public function testExecuteERROR()
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "INVALID",
                "StatusDetail" => "INVALID STATUS"
            ]));

        $this->recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $this->recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $this->_expectRedirect("checkout/cart");
        $this->paypalCallbackController->execute();
    }

    public function testExecuteERRORNoResponse()
    {
        $response = new \stdClass();

        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->willReturn($response);

        $this->recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $this->recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $this->_expectRedirect("checkout/cart");
        $this->paypalCallbackController->execute();
    }

    public function testExecuteERRORInvalidQuote()
    {
        $this->encryptorMock->expects($this->once())->method('decrypt');

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "PAYPALOK",
                "StatusDetail" => "OK STATUS",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}"
            ]));

        $this->recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $this->recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $this->_expectRedirect("checkout/cart");
        $this->paypalCallbackController->execute();
    }

    public function testExecuteERRORInvalidOrder()
    {
        $this->quoteMock->method('getId')->willReturn(self::QUOTE_ID);
        $this->orderMock->method('getId')->willReturn(null);

        $this->encryptorMock->expects($this->once())->method('decrypt');

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "PAYPALOK",
                "StatusDetail" => "OK STATUS",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}"
            ]));

        $this->recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $this->recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $this->_expectRedirect("checkout/cart");
        $this->paypalCallbackController->execute();
    }

    public function testExecuteERRORInvalidTrnId()
    {
        $this->quoteMock->method('getId')->willReturn(self::QUOTE_ID);
        $this->orderMock->method('getId')->willReturn(self::ORDER_ID);
        $this->paymentMock->method('getLastTransId')->willReturn('notequal');
        $this->paymentMock->method('save')->willReturnSelf();

        $this->encryptorMock->expects($this->once())->method('decrypt');

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "PAYPALOK",
                "StatusDetail" => "OK STATUS",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}"
            ]));

        $this->recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $this->recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $this->_expectRedirect("checkout/cart");
        $this->paypalCallbackController->execute();
    }

    /**
     * @param string $path
     */
    private function _expectRedirect($path)
    {
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
    }
}
