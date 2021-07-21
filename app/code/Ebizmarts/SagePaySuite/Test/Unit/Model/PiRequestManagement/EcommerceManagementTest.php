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
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForAction;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteValidator;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\PaymentFailuresInterface;

class EcommerceManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    /** @var InvoiceSender|\PHPUnit_Framework_MockObject_MockObject */
    private $invoiceEmailSenderMock;

    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    private $configMock;

    const TEST_ORDER_NUMBER = 7832;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->invoiceEmailSenderMock = $this->getMockBuilder(InvoiceSender::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
    }

    public function testIsMotoTransaction()
    {
        $objectManagerHelper = new ObjectManager($this);

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(EcommerceManagement::class);

        $this->assertFalse($sut->getIsMotoTransaction());
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
        $quoteMock->expects($this->exactly(2))->method('reserveOrderId')->willReturnSelf();

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);
        $requestDataMock->expects($this->any())->method('getPaymentAction')->willReturn($paymentAction);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->any())->method('getStatusCode')->willReturn(Config::SUCCESS_STATUS);

        $payResultMock->expects($this->once())->method('getAvsCvcCheck');

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);
        $sageCardTypeMock->expects($this->once())->method('convert');

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->exactly(2))->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('getRequestData')->willReturn(
            ['transactionType' => $paymentAction]
        );

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(true);
        $piResultMock->expects($this->once())->method('getSuccess')->willReturn(true);

        $methodInstanceMock = $this->makeMockDisabledConstructor(\Ebizmarts\SagePaySuite\Model\PI::class);
        $methodInstanceMock->expects($this->exactly($expectsMarkInitialized))->method('markAsInitialized');

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
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
        $paymentMock->expects($this->any())->method('setMethod')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setAdditionalInformation')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcLast4')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcExpMonth')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcExpYear')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcType')->willReturnSelf();
        $paymentMock->expects($this->any())->method('save')->willReturnSelf();
        $paymentMock->expects($this->any())->method('getMethodInstance')->willReturn($methodInstanceMock);

        $quoteMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);
        $orderMock->expects($this->any())->method('place')->willReturnSelf();
        $orderMock->expects($this->any())->method('getId')->willReturn(self::TEST_ORDER_NUMBER);

        $checkoutHelperMock->expects($this->once())->method('placeOrder')->willReturn($orderMock);

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock->expects($this->any())->method('create')->willReturn(
            new ClosedForAction($paymentAction)
        );

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->makeMockDisabledConstructor(\Magento\Sales\Model\Order\Payment\Transaction::class)
        );

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
        ->disableOriginalConstructor()
        ->setMethods(
            [
                'setData',
                'clearHelperData',
                'setLastQuoteId',
                'setLastSuccessQuoteId',
                'setLastOrderId',
                'setLastRealOrderId',
                'setLastOrderStatus',
            ]
        )
        ->getMock();
        $checkoutSessionMock->expects($this->exactly(3))->method('setData')
            ->withConsecutive(
                [
                    $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY),
                    $this->equalTo(self::TEST_ORDER_NUMBER)
                ],
                [
                    $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER),
                    $this->equalTo(1)
                ],
                [
                    $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER),
                    $this->equalTo(0)
                ]
            );
        $checkoutSessionMock->expects($this->once())->method('clearHelperData');
        $checkoutSessionMock->expects($this->once())->method('setLastQuoteId');
        $checkoutSessionMock->expects($this->once())->method('setLastSuccessQuoteId');
        $checkoutSessionMock->expects($this->once())->method('setLastOrderId');
        $checkoutSessionMock->expects($this->once())->method('setLastRealOrderId');
        $checkoutSessionMock->expects($this->once())->method('setLastOrderStatus');

        $quoteValidatorMock = $this->getMockBuilder(QuoteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteValidatorMock->expects($this->once())->method('validateBeforeSubmit')->with($quoteMock)->willReturnSelf();

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            EcommerceManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'sagePaySuiteLogger' => $loggerMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
                'checkoutSession'    => $checkoutSessionMock,
                'quoteValidator'     => $quoteValidatorMock,
                'invoiceEmailSender' => $this->invoiceEmailSenderMock,
                'config'             => $this->configMock
            ]
        );


        $this->configMock
            ->expects($this->once())
            ->method('getInvoiceConfirmationNotification')
            ->willReturn("1");
        $this->configMock
            ->expects($this->once())
            ->method('getSagepayPaymentAction')
            ->willReturn(Config::ACTION_PAYMENT_PI);

        $invoiceMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceCollectionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->setMethods(['getFirstItem', 'count'])
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceCollectionMock
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $invoiceCollectionMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($invoiceMock);

        $orderMock
            ->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($invoiceCollectionMock);

        $this->invoiceEmailSenderMock
            ->expects($this->once())
            ->method('send')
            ->with($invoiceMock)
            ->willReturn(true);

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $result = $sut->placeOrder();
        $this->assertTrue($result->getSuccess());
    }

    public function testPlaceOrderReservedOrderIdAlreadySet()
    {
        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);
        $quoteMock->expects($this->exactly(2))->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->exactly(5))->method('getReservedOrderId')->willReturn('000000083');
        $quoteMock->expects($this->never())->method('reserveOrderId');

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);
        $requestDataMock->expects($this->any())->method('getPaymentAction')->willReturn(Config::ACTION_PAYMENT_PI);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->any())->method('getStatusCode')->willReturn(Config::SUCCESS_STATUS);

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);
        $sageCardTypeMock->expects($this->once())->method('convert');

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->exactly(2))->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->exactly(2))->method('getRequestData')->willReturn(
            ['transactionType' => Config::ACTION_PAYMENT_PI]
        );

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(true);
        $piResultMock->expects($this->once())->method('getSuccess')->willReturn(true);

        $methodInstanceMock = $this->makeMockDisabledConstructor(\Ebizmarts\SagePaySuite\Model\PI::class);
        $methodInstanceMock->expects($this->once())->method('markAsInitialized');

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
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
        $paymentMock->expects($this->any())->method('setMethod')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setAdditionalInformation')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcLast4')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcExpMonth')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcExpYear')->willReturnSelf();
        $paymentMock->expects($this->any())->method('setCcType')->willReturnSelf();
        $paymentMock->expects($this->any())->method('save')->willReturnSelf();
        $paymentMock->expects($this->any())->method('getMethodInstance')->willReturn($methodInstanceMock);

        $quoteMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);
        $orderMock->expects($this->any())->method('place')->willReturnSelf();
        $orderMock->expects($this->any())->method('getId')->willReturn(self::TEST_ORDER_NUMBER);

        $checkoutHelperMock->expects($this->once())->method('placeOrder')->willReturn($orderMock);

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock->expects($this->any())->method('create')->willReturn(
            new ClosedForAction(Config::ACTION_PAYMENT_PI)
        );

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->makeMockDisabledConstructor(\Magento\Sales\Model\Order\Payment\Transaction::class)
        );

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
        ->disableOriginalConstructor()
        ->setMethods(
            [
                'setData',
                'clearHelperData',
                'setLastQuoteId',
                'setLastSuccessQuoteId',
                'setLastOrderId',
                'setLastRealOrderId',
                'setLastOrderStatus',
            ]
        )
        ->getMock();
        $checkoutSessionMock->expects($this->exactly(3))->method('setData')
            ->withConsecutive(
                [
                    $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY),
                    $this->equalTo(self::TEST_ORDER_NUMBER)
                ],
                [
                    $this->equalTo(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER),
                    $this->equalTo(1)
                ]
            );
        $checkoutSessionMock->expects($this->once())->method('clearHelperData');
        $checkoutSessionMock->expects($this->once())->method('setLastQuoteId');
        $checkoutSessionMock->expects($this->once())->method('setLastSuccessQuoteId');
        $checkoutSessionMock->expects($this->once())->method('setLastOrderId');
        $checkoutSessionMock->expects($this->once())->method('setLastRealOrderId');
        $checkoutSessionMock->expects($this->once())->method('setLastOrderStatus');

        $quoteValidatorMock = $this->getMockBuilder(QuoteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteValidatorMock->expects($this->once())->method('validateBeforeSubmit')->with($quoteMock)->willReturnSelf();

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            EcommerceManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'sagePaySuiteLogger' => $loggerMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
                'checkoutSession'    => $checkoutSessionMock,
                'quoteValidator'     => $quoteValidatorMock
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $result = $sut->placeOrder();
        $this->assertTrue($result->getSuccess());
    }

    public function testPlaceOrderFailedMagentoOkSagePay()
    {
        $paymentAction = Config::ACTION_DEFER_PI;

        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);
        $requestDataMock->expects($this->once())->method('getPaymentAction')->willReturn($paymentAction);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->exactly(3))->method('getStatusCode')->willReturn(Config::SUCCESS_STATUS);

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);
        $piRestApiMock->expects($this->once())->method('void')
        ->willThrowException(
            new \Ebizmarts\SagePaySuite\Model\Api\ApiException(
                new \Magento\Framework\Phrase('Transaction status not applicable')
            )
        );

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);
        $sageCardTypeMock->expects($this->once())->method('convert');

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->once())->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('getRequestData')->willReturn(
            ['transactionType' => $paymentAction]
        );

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(false);
        $piResultMock->expects($this->once())->method('getSuccess')->willReturn(false);

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
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
        $paymentMock->expects($this->once())->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->exactly(9))->method('setAdditionalInformation')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcLast4')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpMonth')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcExpYear')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setCcType')->willReturnSelf();
        $paymentMock->expects($this->never())->method('save');
        $paymentMock->expects($this->never())->method('getMethodInstance');

        $quoteMock->expects($this->exactly(15))->method('getPayment')->willReturn($paymentMock);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->never())->method('getPayment');
        $orderMock->expects($this->never())->method('place')->willReturnSelf();
        $orderMock->expects($this->never())->method('getId');

        $checkoutHelperMock->expects($this->once())->method('placeOrder')->willThrowException(
            new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Can not save order. Please try another payment option.')
            )
        );

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);
        $loggerMock->expects($this->exactly(2))->method('logException');

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock->expects($this->any())->method('create')->willReturn(
            new ClosedForAction($paymentAction)
        );

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->makeMockDisabledConstructor(\Magento\Sales\Model\Order\Payment\Transaction::class)
        );

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
        ->disableOriginalConstructor()
        ->setMethods(
            [
                'setData',
                'clearHelperData',
                'setLastQuoteId',
                'setLastSuccessQuoteId',
                'setLastOrderId',
                'setLastRealOrderId',
                'setLastOrderStatus',
            ]
        )
        ->getMock();
        $checkoutSessionMock->expects($this->never())->method('setData');
        $checkoutSessionMock->expects($this->never())->method('clearHelperData');
        $checkoutSessionMock->expects($this->never())->method('setLastQuoteId');
        $checkoutSessionMock->expects($this->never())->method('setLastSuccessQuoteId');
        $checkoutSessionMock->expects($this->never())->method('setLastOrderId');
        $checkoutSessionMock->expects($this->never())->method('setLastRealOrderId');
        $checkoutSessionMock->expects($this->never())->method('setLastOrderStatus');

        $quoteValidatorMock = $this->getMockBuilder(QuoteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteValidatorMock->expects($this->once())->method('validateBeforeSubmit')->with($quoteMock)->willReturnSelf();

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            EcommerceManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'sagePaySuiteLogger' => $loggerMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
                'checkoutSession'    => $checkoutSessionMock,
                'quoteValidator'     => $quoteValidatorMock
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $result = $sut->placeOrder();
        $this->assertFalse($result->getSuccess());
    }

    public function testPlaceOrderInvalidQuote()
    {
        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->never())->method('void');

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(false);
        $piResultMock->expects($this->once())->method('getSuccess')->willReturn(false);
        $piResultMock
            ->expects($this->once())
            ->method('setErrorMessage')
            ->with(
                new \Magento\Framework\Phrase('Something went wrong: %1', ['Please specify a shipping method.'])
            );

        $checkoutHelperMock->expects($this->never())->method('placeOrder');

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);
        $loggerMock->expects($this->once())->method('logException');

        $quoteValidatorMock = $this->getMockBuilder(QuoteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteValidatorMock
            ->expects($this->once())
            ->method('validateBeforeSubmit')
            ->willThrowException($this->makeNoShippingMethodException());

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            EcommerceManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'sagePaySuiteLogger' => $loggerMock,
                'quoteValidator'     => $quoteValidatorMock
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        /** @var \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface $result */
        $result = $sut->placeOrder();
        $this->assertFalse($result->getSuccess());
    }

    public function placeOrder()
    {
        return [
            'Payment payment action'  => [Config::ACTION_PAYMENT_PI, 1, 0],
            'Deferred payment action' => [Config::ACTION_DEFER_PI, 0, 1]
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

    /**
     * @return \Magento\Framework\Exception\LocalizedException
     */
    private function makeNoShippingMethodException(): \Magento\Framework\Exception\LocalizedException
    {
        return new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('Please specify a shipping method.')
        );
    }

    public function testSendPaymentFailedEmail()
    {
        $quoteId = 40;

        $paymentAction = Config::ACTION_DEFER_PI;

        $checkoutHelperMock = $this->makeMockDisabledConstructor(Checkout::class);

        $quoteMock = $this->makeMockDisabledConstructor(Quote::class);
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $requestDataMock = $this->makeMockDisabledConstructor(PiRequestManager::class);

        $payResultMock = $this->makeMockDisabledConstructor(PiTransactionResultInterface::class);
        $payResultMock->expects($this->exactly(3))->method('getStatusCode')->willReturn(0004);
        $payResultMock->expects($this->exactly(2))->method('getStatusDetail')->willReturn('Test error');

        $piRestApiMock = $this->makeMockDisabledConstructor(PIRest::class);
        $piRestApiMock->expects($this->once())->method('capture')->willReturn($payResultMock);

        $sageCardTypeMock = $this->makeMockDisabledConstructor(SagePayCardType::class);

        $piRequestMock = $this->makeMockDisabledConstructor(PiRequest::class);
        $piRequestMock->expects($this->once())->method('setCart')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setMerchantSessionKey')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setCardIdentifier')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setVendorTxCode')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setIsMoto')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('setRequest')->willReturnSelf();
        $piRequestMock->expects($this->once())->method('getRequestData')->willReturn(
            ['transactionType' => $paymentAction]
        );

        $suiteHelperMock = $this->makeMockDisabledConstructor(Data::class);

        $piResultMock = $this->makeMockDisabledConstructor(PiResultInterface::class);
        $piResultMock->expects($this->once())->method('setSuccess')->with(false);
        $piResultMock->expects($this->once())->method('getSuccess')->willReturn(false);

        $orderMock = $this->makeMockDisabledConstructor(Order::class);
        $orderMock->expects($this->never())->method('getPayment');
        $orderMock->expects($this->never())->method('place')->willReturnSelf();
        $orderMock->expects($this->never())->method('getId');

        $loggerMock = $this->makeMockDisabledConstructor(Logger::class);

        $actionFactoryMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $actionFactoryMock->expects($this->any())->method('create')->willReturn(
            new ClosedForAction($paymentAction)
        );

        $transactionFactoryMock = $this->makeMockDisabledConstructor('Magento\Sales\Model\Order\Payment\TransactionFactory');
        $transactionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->makeMockDisabledConstructor(\Magento\Sales\Model\Order\Payment\Transaction::class)
        );

        $checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setData',
                    'clearHelperData',
                    'setLastQuoteId',
                    'setLastSuccessQuoteId',
                    'setLastOrderId',
                    'setLastRealOrderId',
                    'setLastOrderStatus',
                ]
            )
            ->getMock();
        $checkoutSessionMock->expects($this->never())->method('setData');
        $checkoutSessionMock->expects($this->never())->method('clearHelperData');
        $checkoutSessionMock->expects($this->never())->method('setLastQuoteId');
        $checkoutSessionMock->expects($this->never())->method('setLastSuccessQuoteId');
        $checkoutSessionMock->expects($this->never())->method('setLastOrderId');
        $checkoutSessionMock->expects($this->never())->method('setLastRealOrderId');
        $checkoutSessionMock->expects($this->never())->method('setLastOrderStatus');

        $quoteValidatorMock = $this->getMockBuilder(QuoteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteValidatorMock->expects($this->once())->method('validateBeforeSubmit')->with($quoteMock)->willReturnSelf();

        $paymentFailuresMock = $this
            ->getMockBuilder(PaymentFailuresInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentFailuresMock
            ->expects($this->once())
            ->method('handle')
            ->with($quoteId, 'Test error')
            ->willReturnSelf();

        /** @var EcommerceManagement $sut */
        $sut = $this->objectManagerHelper->getObject(
            EcommerceManagement::class,
            [
                'checkoutHelper'     => $checkoutHelperMock,
                'piRestApi'          => $piRestApiMock,
                'ccConvert'          => $sageCardTypeMock,
                'piRequest'          => $piRequestMock,
                'suiteHelper'        => $suiteHelperMock,
                'result'             => $piResultMock,
                'sagePaySuiteLogger' => $loggerMock,
                'actionFactory'      => $actionFactoryMock,
                'transactionFactory' => $transactionFactoryMock,
                'checkoutSession'    => $checkoutSessionMock,
                'quoteValidator'     => $quoteValidatorMock,
                'paymentFailures'    => $paymentFailuresMock
            ]
        );

        $sut->setQuote($quoteMock);
        $sut->setRequestData($requestDataMock);

        $result = $sut->placeOrder();
        $this->assertFalse($result->getSuccess());
    }
}
