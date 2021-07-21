<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManager;
use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForAction;
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PI;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\MotoManagement;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;

class MotoManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    const TEST_ORDER_NUMBER = 7832;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
    }

    public function testIsMotoTransaction()
    {
        /** @var MotoManagement $sut */
        $sut = $this->objectManagerHelper->getObject(MotoManagement::class);

        $this->assertTrue($sut->getIsMotoTransaction());
    }

    /**
     * @param string $paymentAction
     * @see \Ebizmarts\SagePaySuite\Model\Config
     * @param integer $expectsMarkInitialized
     * @param integer $expectsTransactionClosed
     * @dataProvider placeOrder
     */
    public function testPlaceOrder($paymentAction, $expectsMarkInitialized, $expectsTransactionClosed)
    {
        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);
        $quoteMock->expects($this->exactly(2))->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->exactly(3))->method('getReservedOrderId');
        $quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);
        $requestDataMock->expects($this->exactly(4))->method('getPaymentAction')->willReturn($paymentAction);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->exactly(2))->method('getStatusCode')->willReturn(Config::SUCCESS_STATUS);

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);
        $sageCardTypeMock->expects($this->once())->method('convert');

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->once())->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('getRequestData')->willReturn([]);

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(true);
        $piResultMock->expects($this->once())->method('setResponse');

        $methodInstanceMock = $this->makeMockDisabledConstructor(PI::class);
        $methodInstanceMock->expects($this->exactly($expectsMarkInitialized))->method('markAsInitialized');

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(
                [
                    'setMethod',
                    'setTransactionId',
                    'setAdditionalInformation',
                    'setCcLast4',
                    'setCcExpMonth',
                    'setCcExpYear',
                    'setCcType',
                    'setIsTransactionClosed',
                    'save',
                    'getMethodInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('setMethod')->willReturnSelf();
        $paymentMock->expects($this->exactly(2))->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->exactly(9))->method('setAdditionalInformation')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcLast4')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpMonth')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpYear')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcType')->willReturnSelf();
        $paymentMock->expects($this->exactly($expectsTransactionClosed))->method('setIsTransactionClosed')->willReturnSelf();
        $paymentMock->expects($this->once())->method('save')->willReturnSelf();
        $paymentMock->expects(
            ($paymentAction === Config::ACTION_PAYMENT_PI ? $this->once() : $this->never())
        )->method('getMethodInstance')->willReturn($methodInstanceMock);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->exactly(16))->method('getPayment')->willReturn($paymentMock);
        $orderMock->expects($this->once())->method('place')->willReturnSelf();
        $orderMock->expects($this->once())->method('getId')->willReturn(self::TEST_ORDER_NUMBER);

        $motoOrderCreateModelMock = $this->getMockBuilder(Create::class)
            ->setMethods(
                [
                    'setIsValidate',
                    'importPostData',
                    'setSendConfirmation',
                    'createOrder',
                    'getQuote'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $motoOrderCreateModelMock->expects($this->once())->method('setIsValidate')->with(true)->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('importPostData')->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('setSendConfirmation')->with(0)->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('createOrder')->willReturn($orderMock);
        $motoOrderCreateModelMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $objectManagerMock = $this->makeMockDisabledConstructor(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->exactly(2))->method('get')->with('Magento\Sales\Model\AdminOrder\Create')
            ->willReturn($motoOrderCreateModelMock);

        $requestMock = $this->makeMockDisabledConstructor(Http::class)  ;
        $requestMock->expects($this->exactly(2))->method('getPost')
            ->withConsecutive(['order'], ['payment'])
            ->willReturnOnConsecutiveCalls([], []);

        $urlMock = $this->makeMockDisabledConstructor(UrlInterface::class);
        $urlMock->expects($this->once())->method('getUrl')->with('sales/order/view', ['order_id' => self::TEST_ORDER_NUMBER]);

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);

        $emailSenderMock = $this->makeMockDisabledConstructor(EmailSender::class);
        $emailSenderMock->expects($this->once())->method('send');

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock
            ->expects(
                ($paymentAction === Config::ACTION_PAYMENT_PI ? $this->never() : $this->once())
            )
            ->method('create')->willReturn(new ClosedForAction($paymentAction));

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock
            ->expects(
                ($paymentAction === Config::ACTION_PAYMENT_PI ? $this->never() : $this->once())
            )
            ->method('create')
            ->willReturn($this->makeMockDisabledConstructor(Transaction::class));

        /** @var MotoManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            MotoManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'objectManager'      => $objectManagerMock,
                'httpRequest'        => $requestMock,
                'backendUrl'         => $urlMock,
                'suiteLogger'        => $loggerMock,
                'emailSender'        => $emailSenderMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $sut->placeOrder();
    }

    public function placeOrder()
    {
        return [
            'Payment payment action' => [Config::ACTION_PAYMENT_PI, 1, 0],
            'Deferred payment action' => [Config::ACTION_DEFER_PI, 0, 1,]
        ];
    }

    /**
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMockDisabledConstructor($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPlaceOrderReservedOrderIdAlreadySet()
    {
        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);
        $quoteMock->expects($this->exactly(2))->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->exactly(3))->method('getReservedOrderId')->willReturn('000000083');
        $quoteMock->expects($this->never())->method('reserveOrderId');

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);
        $requestDataMock->expects($this->exactly(4))->method('getPaymentAction')->willReturn(Config::ACTION_PAYMENT_PI);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->exactly(2))->method('getStatusCode')->willReturn(Config::SUCCESS_STATUS);

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);
        $sageCardTypeMock->expects($this->once())->method('convert');

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->once())->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('getRequestData')->willReturn([]);

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(true);
        $piResultMock->expects($this->once())->method('setResponse');

        $methodInstanceMock = $this->makeMockDisabledConstructor(PI::class);
        $methodInstanceMock->expects($this->once())->method('markAsInitialized');

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(
                [
                    'setMethod',
                    'setTransactionId',
                    'setAdditionalInformation',
                    'setCcLast4',
                    'setCcExpMonth',
                    'setCcExpYear',
                    'setCcType',
                    'setIsTransactionClosed',
                    'save',
                    'getMethodInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('setMethod')->willReturnSelf();
        $paymentMock->expects($this->exactly(2))->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->exactly(9))->method('setAdditionalInformation')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcLast4')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpMonth')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpYear')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcType')->willReturnSelf();
        $paymentMock->expects($this->never())->method('setIsTransactionClosed')->willReturnSelf();
        $paymentMock->expects($this->once())->method('save')->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstanceMock);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->exactly(16))->method('getPayment')->willReturn($paymentMock);
        $orderMock->expects($this->once())->method('place')->willReturnSelf();
        $orderMock->expects($this->once())->method('getId')->willReturn(self::TEST_ORDER_NUMBER);

        $motoOrderCreateModelMock = $this->getMockBuilder(Create::class)
            ->setMethods(
                [
                    'setIsValidate',
                    'importPostData',
                    'setSendConfirmation',
                    'createOrder',
                    'getQuote'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $motoOrderCreateModelMock->expects($this->once())->method('setIsValidate')->with(true)->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('importPostData')->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('setSendConfirmation')->with(0)->willReturnSelf();
        $motoOrderCreateModelMock->expects($this->once())->method('createOrder')->willReturn($orderMock);
        $motoOrderCreateModelMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $objectManagerMock = $this->makeMockDisabledConstructor(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->exactly(2))->method('get')->with('Magento\Sales\Model\AdminOrder\Create')
            ->willReturn($motoOrderCreateModelMock);

        $requestMock = $this->makeMockDisabledConstructor(Http::class)  ;
        $requestMock->expects($this->exactly(2))->method('getPost')
            ->withConsecutive(['order'], ['payment'])
            ->willReturnOnConsecutiveCalls([], []);

        $urlMock = $this->makeMockDisabledConstructor(UrlInterface::class);
        $urlMock->expects($this->once())->method('getUrl')->with('sales/order/view', ['order_id' => self::TEST_ORDER_NUMBER]);

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);

        $emailSenderMock = $this->makeMockDisabledConstructor(EmailSender::class);
        $emailSenderMock->expects($this->once())->method('send');

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock->expects($this->never())->method('create');

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock
            ->expects($this->never())
            ->method('create');

        /** @var MotoManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            MotoManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'objectManager'      => $objectManagerMock,
                'httpRequest'        => $requestMock,
                'backendUrl'         => $urlMock,
                'suiteLogger'        => $loggerMock,
                'emailSender'        => $emailSenderMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $sut->placeOrder();
    }
}
