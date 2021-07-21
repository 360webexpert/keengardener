<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Form;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Ebizmarts\SagePaySuite\Model\Api\Http;
use Ebizmarts\SagePaySuite\Model\Form;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\QuoteRepository;

class SuccessTest extends \PHPUnit\Framework\TestCase
{

    /** @var Form|\PHPUnit_Framework_MockObject_MockObject */
    private $formModelMock;

    /** @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $quoteRepositoryMock;

    /**  @var RepositoryQuery|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryQueryMock;

    /** @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $encryptorMock;

    /**
     * @var OrderUpdateOnCallback| \PHPUnit\Framework\MockObject\MockObject
     */
    private $updateOrderCallbackMock;

    /** @var \Ebizmarts\SagePaySuite\Controller\Form\Success */
    private $formSuccessController;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var Http|\PHPUnit_Framework_MockObject_MockObject */
    private $responseMock;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $redirectMock;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    private $orderMock;

    /** @var \Ebizmarts\SagePaySuite\Helper\Checkout|\PHPUnit_Framework_MockObject_MockObject */
    private $checkoutHelperMock;

    private $contextMock;

    /** @var \Ebizmarts\SagePaySuite\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $suiteHelperMock;

    /** @var OrderLoader|\PHPUnit_Framework_MockObject_MockObject */
    private $orderLoaderMock;


    const quoteIDFromParams = 69;
    const encryptedQuoteId = '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0=';
    const crypt = '@77a9f5fb9cbfc11c6f3d5d6b424c7e848ca93f36832d3c9e3cda25fbab68c133362f58aafb1c867df84a8a017daf'
    . '7221da9f22aa9dfe876485d4d86e78f8eef8c027f5bdc7c500fe5adb2671e5ee6c02528604a09dc767c74c2a35bf'
    . 'f83c02fe78ddace8e77beb397099eb8f3465eb9d0daa26da1b5b6282f39f90c85e66598baad4acd6161de42d4052'
    . 'afae1b2f1eecbff6caa6eb831b9c7cfac02ffa036de8e0097a84ea70437e89afcceacf30605091d209237122ed8c'
    . '2417a33a4eb1260da5d7c7278df5738e01eefafaddf93e82988b714573871287d993ad2f9b9bfed2b207955fdcb7'
    . '1f12cd01fb97306680d0b7c82dcf96eb0b336920af3ab6ef69218e24a8f81f53dbd0aa26002192a7469fe5d297ff'
    . '4723c1bc5745429701a9b1a3';
    const reservedOrderId = 79;

    /**
    * Sage Pay Transaction ID
    */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    public function setUp()
    {
        $this->makeQuoteRepositoryMock();
        $this->makeResponseMock();
        $this->makeRequestMock();
        $this->makeRedirectMock();
        $checkoutSessionMock = $this->makeCheckoutSessionMock();
        $messageManagerMock = $this->makeMessageManagerMock();

        $this->orderLoaderMock = $this
            ->getMockBuilder(OrderLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->suiteHelperMock = $this->getMockBuilder(Data::class)
            ->setMethods(['verify'])
            ->disableOriginalConstructor()->getMock();

        $this->encryptorMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateOrderCallbackMock = $this->getMockBuilder(OrderUpdateOnCallback::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->makeContextMock($messageManagerMock);
        $this->formModelMock = $this->makeFormModelMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->formSuccessController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Form\Success',
            [
                'context'             => $this->contextMock,
                'checkoutSession'    => $checkoutSessionMock,
                'checkoutHelper'      => $this->checkoutHelperMock,
                'formModel'          => $this->formModelMock,
                'quoteRepository'    => $this->quoteRepositoryMock,
                'suiteHelper'         => $this->suiteHelperMock,
                'updateOrderCallback' => $this->updateOrderCallbackMock,
                'encryptor'           => $this->encryptorMock,
                'repositoryQuery'    => $this->repositoryQueryMock,
                'orderLoader'         => $this->orderLoaderMock,
            ]
        );
    }

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
    public function testExecuteSuccess($mode, $paymentAction)
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock1 = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['crypt'], ['quoteid'])
            ->willReturnOnConsecutiveCalls(self::crypt, self::encryptedQuoteId);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "3DSecureStatus" => "OK",
                "Status"         => "OK",
                "ExpiryDate"     => "0419",
            ]);

        $this->encryptorMock->expects($this->once())->method('decrypt')
            ->with(self::encryptedQuoteId)
            ->willReturn(self::quoteIDFromParams);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::quoteIDFromParams)
            ->willReturn($quoteMock1);

        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($quoteMock1)
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())->method('getId')->willReturn(1);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $paymentMock->expects($this->exactly(2))->method('getAdditionalInformation')
            ->withConsecutive(['vendorTxCode'], ['Status'])
            ->willReturnOnConsecutiveCalls(
                "100000001-2016-12-12-12346789", 
                \Ebizmarts\SagePaySuite\Model\Config::OK_STATUS
            );

        $this->formSuccessController->execute();
    }

    /**
     * @dataProvider modeProvider
     */
    public function testExecuteSuccessRetry($mode, $paymentAction)
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock1 = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['crypt'], ['quoteid'])
            ->willReturnOnConsecutiveCalls(self::crypt, self::encryptedQuoteId);

        $this->encryptorMock->expects($this->once())->method('decrypt')
            ->with(self::encryptedQuoteId)
            ->willReturn(self::quoteIDFromParams);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "3DSecureStatus" => "OK",
                "Status"         => "PENDING",
                "ExpiryDate"     => "0419",
            ]);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::quoteIDFromParams)
            ->willReturn($quoteMock1);

        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($quoteMock1)
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once(1))->method('getId')->willReturn(1);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $paymentMock->expects($this->exactly(2))->method('getAdditionalInformation')
            ->withConsecutive(['vendorTxCode'], ['Status'])
            ->willReturnOnConsecutiveCalls(
                "100000001-2016-12-12-12346789", 
                \Ebizmarts\SagePaySuite\Model\Config::OK_STATUS
            );

        $this->formSuccessController->execute();
    }

    /**
     * @dataProvider modeProvider
     */
    public function testExecuteSuccessPendingStatus($mode, $paymentAction)
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock1 = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['crypt'], ['quoteid'])
            ->willReturnOnConsecutiveCalls(self::crypt, self::encryptedQuoteId);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "3DSecureStatus" => "OK",
                "Status"         => "PENDING",
                "ExpiryDate"     => "0419",
            ]);

        $this->encryptorMock->expects($this->once())->method('decrypt')
            ->with(self::encryptedQuoteId)
            ->willReturn(self::quoteIDFromParams);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::quoteIDFromParams)
            ->willReturn($quoteMock1);

        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($quoteMock1)
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())->method('getId')->willReturn(1);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $paymentMock->expects($this->exactly(2))->method('getAdditionalInformation')
            ->withConsecutive(['vendorTxCode'], ['Status'])
            ->willReturnOnConsecutiveCalls(
                "100000001-2016-12-12-12346789", 
                \Ebizmarts\SagePaySuite\Model\Config::PENDING_STATUS
            );

        $this->formSuccessController->execute();
    }

    public function testExecuteError()
    {
        $quoteMock1 = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['crypt'], ['quoteid'])
            ->willReturnOnConsecutiveCalls(self::crypt, self::encryptedQuoteId);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "3DSecureStatus" => "OK",
                "Status"         => "OK",
                "ExpiryDate"     => "0419",
            ]);

        $this->encryptorMock->expects($this->once())->method('decrypt')
            ->with(self::encryptedQuoteId)
            ->willReturn(self::quoteIDFromParams);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::quoteIDFromParams)
            ->willReturn($quoteMock1);

            $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($quoteMock1)
            ->willThrowException(new LocalizedException(__("Invalid order.")));

        $this->formSuccessController->execute();
    }

    public function testCryptDoesNotContainVpsTxId()
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->once())->method('getParam')
            ->with('crypt')
            ->willReturn(self::crypt);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2016-12-12-12346789",
                "3DSecureStatus" => "OK",
                "Status"         => "OK",
                "ExpiryDate"     => "0419",
            ]);

        $this->formSuccessController->execute();
    }

    public function testVpsTxIdDontMatch()
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->makeOrderMock($paymentMock);

        $this->checkoutHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock1 = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['crypt'], ['quoteid'])
            ->willReturnOnConsecutiveCalls(self::crypt, self::encryptedQuoteId);

        $this->formModelMock->expects($this->once())
            ->method('decodeSagePayResponse')
            ->with(self::crypt)
            ->willReturn([
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "StatusDetail"   => "OK_STATUS_DETAIL",
                "VendorTxCode"   => "100000001-2000-12-12-12346789", //don't match
                "3DSecureStatus" => "OK",
                "Status"         => "OK",
                "ExpiryDate"     => "0419",
            ]);

        $this->encryptorMock->expects($this->once())->method('decrypt')
            ->with(self::encryptedQuoteId)
            ->willReturn(self::quoteIDFromParams);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::quoteIDFromParams)
            ->willReturn($quoteMock1);

        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($quoteMock1)
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        
        $paymentMock->expects($this->exactly(2))->method('getAdditionalInformation')
            ->withConsecutive(['vendorTxCode'], ['Status'])
            ->willReturnOnConsecutiveCalls(
                "100000001-2016-12-12-12346789", 
                \Ebizmarts\SagePaySuite\Model\Config::OK_STATUS
            );

        $this->formSuccessController->execute();
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeFormModelMock()
    {
        $formModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();

        return $formModelMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeCheckoutSessionMock()
    {
        $checkoutSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        return $checkoutSessionMock;
    }

    private function makeResponseMock()
    {
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()->getMock();
    }

    private function makeRequestMock()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function makeRedirectMock()
    {
        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');
    }

    /**
     * @param $messageManagerMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock($messageManagerMock)
    {
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getRedirect')->will($this->returnValue($this->redirectMock));
        $contextMock->expects($this->any())->method('getMessageManager')->will($this->returnValue($messageManagerMock));

        return $contextMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMessageManagerMock()
    {
        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $messageManagerMock;
    }

    /**
     * @param $paymentMock
     */
    private function makeOrderMock($paymentMock)
    {
        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods(['getId', 'getPayment'])
            ->disableOriginalConstructor()->getMock();
    }

    private function makeQuoteRepositoryMock()
    {
        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(["get"])
            ->getMock();
    }
}
