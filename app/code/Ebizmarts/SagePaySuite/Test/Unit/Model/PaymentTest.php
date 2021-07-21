<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Data as SagePayHelper;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Pi;
use Ebizmarts\SagePaySuite\Model\Api\Shared;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ebizmarts\SagePaySuite\Model\Payment;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment as OrderPayment;

class PaymentTest extends \PHPUnit\Framework\TestCase
{

    public function testSetOrderStateAndStatusPayment()
    {
        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(Payment::class);

        $paymentMock = $this->getMockBuilder(OrderPayment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->never())->method('getLastTransId');

        $stateObjectMock = $this->makeStateObjectAssertStateStatus('pending_payment', 'pending_payment');

        $sut->setOrderStateAndStatus($paymentMock, 'PAYMENT', $stateObjectMock);
    }

    /**
     * @dataProvider deferredAuthenticateOrderStatusDataProvider
     */
    public function testSetOrderStateAndStatusDeferredAuthenticateNoTransactionId($paymentAction)
    {
        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(Payment::class);

        $paymentMock = $this->getMockBuilder(OrderPayment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn(null);

        $stateObjectMock = $this->makeStateObjectAssertStateStatus('pending_payment', 'pending_payment');

        $sut->setOrderStateAndStatus($paymentMock, $paymentAction, $stateObjectMock);
    }

    /**
     * @dataProvider deferredAuthenticateOrderStatusDataProvider
     */
    public function testSetOrderStateAndStatusDeferredAuthenticate($paymentAction)
    {
        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(Payment::class);

        $paymentMock = $this->getMockBuilder(OrderPayment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn(9812);

        $stateObjectMock = $this->makeStateObjectAssertStateStatus('new', 'pending');

        $sut->setOrderStateAndStatus($paymentMock, $paymentAction, $stateObjectMock);
    }

    public function deferredAuthenticateOrderStatusDataProvider()
    {
        return [
            ['DEFERRED'], ['AUTHENTICATE']
        ];
    }

    private function makeStateObjectAssertStateStatus($expectedState, $expectedStatus)
    {
        $stateObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setState', 'setStatus'])
            ->getMock();
        $stateObjectMock->expects($this->once())->method('setState')->with($expectedState);
        $stateObjectMock->expects($this->once())->method('setStatus')->with($expectedStatus);

        return $stateObjectMock;
    }

    public function testCaptureDeferredPiTransaction()
    {
        $testAmount  = 377.68;
        $testVpsTxId = 'ABCD-1234';

        $resultMock = $this->getMockBuilder(PiInstructionResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $piApiMock = $this->makePiApiMock($testVpsTxId, $testAmount, $resultMock);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config' => $this->makeConfigMockPiDeferredAction()
            ]
        );

        $sut->setApi($piApiMock);

        $orderMock = $this->makeOrderMockPendingState();

        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setParentTransactionId');
        $paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setTransactionId');

        $sut->capture($paymentMock, $testAmount);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot capture deferred transaction, transaction state is invalid.
     */
    public function testCaptureDeferredPiTransactionTxStateIdNull()
    {
        $testAmount  = 377.68;
        $testVpsTxId = 'ABCD-1234';

        $piApiMock = $this
            ->getMockBuilder(Pi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $piApiMock
            ->expects($this->once())
            ->method('captureDeferredTransaction')
            ->with($testVpsTxId, $testAmount)
            ->willThrowException(new ApiException(__('Cannot capture deferred transaction, transaction state is invalid.')));

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config' => $this->makeConfigMockPiDeferredAction()
            ]
        );

        $sut->setApi($piApiMock);

        $orderMock = $this->makeOrderMockPendingState();

        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setParentTransactionId');
        $paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setTransactionId');

        $sut->capture($paymentMock, $testAmount);
    }

    public function testCaptureDeferredPiMultiCurrencyTransaction()
    {
        $testAmount  = 377.68;
        $testVpsTxId = 'ABCD-1234';

        $configMock = $this->makeConfigMockPiDeferredAction();
        $configMock
            ->expects($this->once())
            ->method('getCurrencyConfig')
            ->willReturn(CONFIG::CURRENCY_SWITCHER);

        $orderMock = $this->makeOrderMockPendingState();

        $orderMock
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn('EUR');
        $orderMock
            ->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn('GBP');
        $orderMock
            ->expects($this->once())
            ->method('getBaseToOrderRate')
            ->willReturn(1.3);

        $resultMock = $this->getMockBuilder(PiInstructionResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $piApiMock = $this->makePiApiMock($testVpsTxId, $testAmount * 1.3, $resultMock);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config' => $configMock
            ]
        );

        $sut->setApi($piApiMock);

        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setParentTransactionId');
        $paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->never())->method('setTransactionId');

        $sut->capture($paymentMock, $testAmount);
    }

    public function testCaptureDeferredReleasedPiTransaction()
    {
        $testAmount  = 377.68;
        $testVpsTxId = 'ABCD-1234';

        $resultMock = $this->getMockBuilder(PiTransactionResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock->expects($this->once())->method('getTransactionId')->willReturn('EFAB-0987');

        $piApiMock = $this->makePiApiMock($testVpsTxId, $testAmount, $resultMock);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config' => $this->makeConfigMockPiDeferredAction(),
                'suiteHelper' => $this->makeSagePayHelperDataMock()
            ]
        );

        $sut->setApi($piApiMock);

        $orderMock = $this->makeOrderMockPendingState();

        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setParentTransactionId')->with($testVpsTxId);
        $paymentMock->expects($this->exactly(2))->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setTransactionId')->with('EFAB-0987');

        $sut->capture($paymentMock, $testAmount);
    }

    public function testCaptureAuthenticateForm()
    {
        $testAmount  = 963.80;
        $testVpsTxId = 'D55E2CC0-168C-F770-6862-C28D0CAD0755';

        $sharedApiMock = $this->getMockBuilder(Shared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedApiMock->expects($this->once())->method('authorizeTransaction')
            ->with($testVpsTxId, $testAmount)
            ->willReturn($this->makeAuthoriseResponseMock());

        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config'      => $configMock,
                'suiteHelper' => $this->makeSagePayHelperDataMock()
            ]
        );

        $sut->setApi($sharedApiMock);

        $orderMock = $this->makeOrderMockPendingState();

        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock
            ->expects($this->exactly(2))
            ->method('getAdditionalInformation')
            ->with('paymentAction')
            ->willReturn('AUTHENTICATE');
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setParentTransactionId')->with($testVpsTxId);
        $paymentMock->expects($this->exactly(2))->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setTransactionId')->with('D1C98A42-E2F2-F7BB-631C-B439303A5EC5');
        $this->checkSetTransactionAdditionalCorrect($paymentMock);

        $sut->capture($paymentMock, $testAmount);
    }

    public function testCaptureMultiCurrencyAuthenticateForm()
    {
        $testAmount  = 963.80;
        $rate = 1.09;
        $testInvioceTransaction = $testAmount * $rate;
        $testVpsTxId = 'D55E2CC0-168C-F770-6862-C28D0CAD0755';

        $sharedApiMock = $this
            ->getMockBuilder(Shared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedApiMock
            ->expects($this->once())->method('authorizeTransaction')
            ->with($testVpsTxId, $testInvioceTransaction)
            ->willReturn($this->makeAuthoriseResponseMock());

        $configMock = $this
            ->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock
            ->expects($this->once())
            ->method('getCurrencyConfig')
            ->willReturn(Config::CURRENCY_SWITCHER);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config'      => $configMock,
                'suiteHelper' => $this->makeSagePayHelperDataMock()
            ]
        );

        $sut->setApi($sharedApiMock);

        $orderMock = $this->makeOrderMockPendingState();
        $orderMock
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn('EUR');
        $orderMock
            ->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn('GBP');
        $orderMock
            ->expects($this->once())
            ->method('getBaseToOrderRate')
            ->willReturn($rate);


        $paymentMock = $this->makePaymentMock($orderMock);
        $paymentMock
            ->expects($this->exactly(2))
            ->method('getAdditionalInformation')
            ->with('paymentAction')
            ->willReturn('AUTHENTICATE');
        $paymentMock->expects($this->once())->method('getLastTransId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setParentTransactionId')->with($testVpsTxId);
        $paymentMock->expects($this->exactly(2))->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setTransactionId')->with('D1C98A42-E2F2-F7BB-631C-B439303A5EC5');
        $this->checkSetTransactionAdditionalCorrect($paymentMock);

        $sut->capture($paymentMock, $testAmount);
    }

    public function testRefundTransaction()
    {
        $testAmount  = 963.80;
        $testVpsTxId = 'D55E2CC0-168C-F770-6862-C28D0CAD0755';

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sharedApiMock = $this
            ->getMockBuilder(Shared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedApiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->with($testVpsTxId, $testAmount, $orderMock)
            ->willReturn($this->makeRefundResponseMock());

        $configMock = $this
            ->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock
            ->expects($this->once())
            ->method('getCurrencyConfig')
            ->willReturn(Config::CURRENCY_BASE);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config'      => $configMock,
                'suiteHelper' => $this->makeSagePayHelperDataMock()
            ]
        );

        $sut->setApi($sharedApiMock);

        $paymentMock = $this->getMockBuilder(OrderPayment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setIsTransactionClosed')->with(1);
        $paymentMock->expects($this->once())->method('setShouldCloseParentTransaction')->with(1);
        $paymentMock->expects($this->exactly(2))->method('getOrder')->willReturn($orderMock);
        $this->checkSetTransactionAdditionalCorrectRefund($paymentMock);

        $sut->refund($paymentMock, $testAmount);
    }

    public function testRefundMultiCurrencyTransaction()
    {
        $testAmount  = 963.80;
        $rate = 1.09;
        $testRefundTransaction = $testAmount * $rate;
        $testVpsTxId = 'D55E2CC0-168C-F770-6862-C28D0CAD0755';

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn('EUR');
        $orderMock
            ->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn('GBP');
        $orderMock
            ->expects($this->once())
            ->method('getBaseToOrderRate')
            ->willReturn($rate);

        $sharedApiMock = $this
            ->getMockBuilder(Shared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedApiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->with($testVpsTxId, $testRefundTransaction, $orderMock)
            ->willReturn($this->makeRefundResponseMock());

        $configMock = $this
            ->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock
            ->expects($this->once())
            ->method('getCurrencyConfig')
            ->willReturn(Config::CURRENCY_SWITCHER);

        /** @var Payment $sut */
        $sut = $this->makeObjectManager()->getObject(
            Payment::class,
            [
                'config'      => $configMock,
                'suiteHelper' => $this->makeSagePayHelperDataMock()
            ]
        );

        $sut->setApi($sharedApiMock);

        $paymentMock = $this->getMockBuilder(OrderPayment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn($testVpsTxId);
        $paymentMock->expects($this->once())->method('setIsTransactionClosed')->with(1);
        $paymentMock->expects($this->once())->method('setShouldCloseParentTransaction')->with(1);
        $paymentMock->expects($this->exactly(2))->method('getOrder')->willReturn($orderMock);
        $this->checkSetTransactionAdditionalCorrectRefund($paymentMock);

        $sut->refund($paymentMock, $testAmount);
    }

    /**
     * @return ObjectManager
     */
    private function makeObjectManager(): ObjectManager
    {
        return new ObjectManager($this);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeConfigMockFormAuthenticateAction()
    {
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getSagepayPaymentAction')->willReturn('AUTHENTICATE');

        return $configMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeConfigMockPiDeferredAction()
    {
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('getSagepayPaymentAction')->willReturn('Deferred');

        return $configMock;
    }

    /**
     * @param $testVpsTxId
     * @param $testAmount
     * @param $resultMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makePiApiMock($testVpsTxId, $testAmount, $resultMock)
    {
        $piApiMock = $this
            ->getMockBuilder(Pi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $piApiMock
            ->expects($this->once())
            ->method('captureDeferredTransaction')
            ->with($testVpsTxId, $testAmount)->willReturn($resultMock);

        return $piApiMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeOrderMockPendingState()
    {
        $orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $orderMock->expects($this->once())->method('getState')->willReturn('pending');

        return $orderMock;
    }

    /**
     * @param $orderMock
     * @param $testVpsTxId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makePaymentMock($orderMock)
    {
        $paymentMock = $this->getMockBuilder(OrderPayment::class)->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        return $paymentMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeSagePayHelperDataMock()
    {
        return $this->getMockBuilder(SagePayHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSagePayConfig'])
            ->getMock();
    }

    /**
     * @return array
     */
    private function makeRefundResponseMock(): array
    {
        $authoriseResult = [
            'status' => 200,
            'data'   => [
                'VPSProtocol'    => '3.00',
                'Status'         => 'OK',
                'StatusDetail'   => '0000 : The Authorisation was Successful.',
                'SecurityKey'    => 'IP0Q6DNQMV',
                'TxAuthNo'       => '17759858',
                'VPSTxId'        => '{87ACE673-6821-B9BD-41A6-8E8EEFEE7C25}'
            ]
        ];

        return $authoriseResult;
    }

    /**
     * @return array
     */
    private function makeAuthoriseResponseMock(): array
    {
        $authoriseResult = [
            'status' => 200,
            'data'   => [
                'VPSProtocol'    => '3.00',
                'Status'         => 'OK',
                'StatusDetail'   => '0000 : The Authorisation was Successful.',
                'VPSTxId'        => '{D1C98A42-E2F2-F7BB-631C-B439303A5EC5}',
                'SecurityKey'    => 'RX4FZ3C4JE',
                'TxAuthNo'       => '17759116',
                'AVSCV2'         => 'SECURITY CODE MATCH ONLY',
                'AddressResult'  => 'NOTMATCHED',
                'PostCodeResult' => 'NOTMATCHED',
                'CV2Result'      => 'MATCHED',
                '3DSecureStatus' => 'OK',
                'CAVV'           => 'AAABARR5kwAAAAAAAAAAAAAAAAA=',
                'DeclineCode'    => '00',
                'BankAuthCode'   => '999777'
            ]
        ];

        return $authoriseResult;
    }

    /**
     * @param $paymentMock
     */
    private function checkSetTransactionAdditionalCorrectRefund($paymentMock)
    {
        $paymentMock
            ->expects($this->exactly(6))
            ->method('setTransactionAdditionalInfo')
            ->withConsecutive(
                ['VPSProtocol', '3.00'],
                ['Status', 'OK'],
                ['StatusDetail', '0000 : The Authorisation was Successful.'],
                ['SecurityKey', 'IP0Q6DNQMV'],
                ['TxAuthNo', '17759858'],
                ['VPSTxId', '{87ACE673-6821-B9BD-41A6-8E8EEFEE7C25}']
            );
    }

    /**
     * @param $paymentMock
     */
    private function checkSetTransactionAdditionalCorrect($paymentMock)
    {
        $paymentMock
            ->expects($this->exactly(14))
            ->method('setTransactionAdditionalInfo')
            ->withConsecutive(
                ['VPSProtocol', '3.00'],
                ['Status', 'OK'],
                ['StatusDetail', '0000 : The Authorisation was Successful.'],
                ['VPSTxId', '{D1C98A42-E2F2-F7BB-631C-B439303A5EC5}'],
                ['SecurityKey', 'RX4FZ3C4JE'],
                ['TxAuthNo', '17759116'],
                ['AVSCV2', 'SECURITY CODE MATCH ONLY'],
                ['AddressResult', 'NOTMATCHED'],
                ['PostCodeResult', 'NOTMATCHED'],
                ['CV2Result', 'MATCHED'],
                ['3DSecureStatus', 'OK'],
                ['CAVV', 'AAABARR5kwAAAAAAAAAAAAAAAAA='],
                ['DeclineCode', '00'],
                ['BankAuthCode', '999777']
            );
    }
}
