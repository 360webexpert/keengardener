<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Server;

use Ebizmarts\SagePaySuite\Controller\Server\Notify;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Ebizmarts\SagePaySuite\Model\Token;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\TransactionFactory;
use function urlencode;

class NotifyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID  = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';
    const QUOTE_ID      = '99999999';
    const ORDER_ID      = '88888888';
    const STORE_ID      = 1;
    const ENC_QUOTE_ID  =  '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0=';

    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /** @var TransactionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $transactionFactory;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Quote|\PHPUnit_Framework_MockObject_MockObject */
    private $quote;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var \Ebizmarts\SagePaySuite\Controller\Server\Notify */
    private $serverNotifyController;

    /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilder;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    private $order;

    /** @var OrderSender|\PHPUnit_Framework_MockObject_MockObject */
    private $orderSender;

    /** @var Token|\PHPUnit_Framework_MockObject_MockObject */
    private $token;

    /** @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $cartRepository;

    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    private $suiteHelper;

    /** @var OrderUpdateOnCallback|\PHPUnit_Framework_MockObject_MockObject */
    private $updateOrderCallback;

    /** @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $encryptor;

    /** @var OrderLoader */
    private $orderLoaderMock;

    // @codingStandardsIgnoreStart
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

//        // Initialize common constructor args
        $this->makeSuiteLogger();
        $this->makeOrderSender();
        $this->makeConfig();
        $this->makeSuiteHelper();
        $this->makeUpdateOrderCallback();
        $this->makeHttpRequest();
        $this->makeHttpResponse();
        $this->makeUrlBuilder();
        $this->makeContext();
        $this->makeToken();
        $this->makeEncryptor();
        $this->makeOrderLoader();
    }
    // @codingStandardsIgnoreEnd

    public function testExecuteOK()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));
        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->encryptor->expects($this->once())->method('decrypt')->willReturn(self::QUOTE_ID);

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => "8E77F29220981737C51C615C3464301F"
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->updateOrderCallback->expects($this->once())->method('setOrder')->with($this->order);
        $this->updateOrderCallback->expects($this->once())->method('confirmPayment')->with(self::TEST_VPSTXID);

        $this->controllerInstantiate();
         $this->serverNotifyController->execute();
    }

    public function testExecuteOkSagePayRetry()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => "8E77F29220981737C51C615C3464301F"
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->updateOrderCallback->expects($this->once())->method('setOrder')->with($this->order);
        $this->updateOrderCallback->expects($this->once())
            ->method('confirmPayment')
            ->with(self::TEST_VPSTXID)
            ->willThrowException(new AlreadyExistsException(__('Transaction already exists.')));

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecutePENDING()
    {
        $serverModelMock = $this->makeServerModel();

        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);
        $paymentMock->expects($this->exactly(9))->method('setAdditionalInformation');

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));
        $invoiceCollectionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceCollectionMock->expects($this->never())->method('setDataToAll')->willReturnSelf();
        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->orderSender->expects($this->once())->method('send')->with($this->order);

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->willReturn((object)[
                "TxType"         => "PAYMENT",
                "Status"         => "PENDING",
                "VPSTxId"        => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail"   => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType"       => "VISA",
                "Last4Digits"    => "0006",
                "ExpiryDate"     => "0222",
                "VendorTxCode"   => "10000000001-2015-12-12-123456",
                "AVSCV2"         => "OK",
                "AddressResult"  => "OK",
                "PostCodeResult" => "OK",
                "CV2Result"      => "OK",
                "GiftAid"        => "0",
                "AddressStatus"  => "OK",
                "PayerStatus"    => "OK",
                "VPSSignature"   => '5E3C9B48732834181EBA17ACDE1E55EF'
            ]);

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteABORT()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $this->order->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "ABORT",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "ABORT Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => 'EA6C59BD4DBEDB8B8B59345E64F9A02C'
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction ABORTED successfully' . "\r\n" .
            'RedirectURL=?quote=' . urlencode(self::ENC_QUOTE_ID) . '&message=Transaction cancelled by customer' . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteStatusError()
    {
        $serverModelMock = $this->makeServerModel();

        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order
            ->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();
        $this->order
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "ERROR",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "ABORT Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => 'F348327D868D37850E361B75A2B1D885'
            ]));

        $errorStatusDetail = 'Payment was not accepted, please try another payment method. Status: ERROR, ABORT Status';

        $this->responseExpectsSetBody(
            'Status=INVALID' . "\r\n" .
            'StatusDetail=' . $errorStatusDetail . "\r\n" .
            'RedirectURL=?message=' . $errorStatusDetail . '&quote=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteInvalidTransactionId()
    {
        $serverModelMock = $this->makeServerModel();

        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1, 'INVALID_TRANSACTION');

        $this->order->expects($this->any())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . "INVALID_TRANSACTION" . "}",
                "StatusDetail" => "ABORT Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => '01C00A6026B02C534200728C4E85DDA3'
            ]));

        $this->responseExpectsSetBody(
            'Status=INVALID' . "\r\n" .
            'StatusDetail=Something went wrong: Invalid transaction id' . "\r\n" .
            'RedirectURL=?message=Something went wrong: Invalid transaction id&quote=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteNoBankAuthCode()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);
        $paymentMock->expects($this->exactly(7))->method('setAdditionalInformation');

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));
        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => "4B27106C4F30903A434176C5807AE4A3"
            ]));
        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->updateOrderCallback->expects($this->once())->method('setOrder')->with($this->order);
        $this->updateOrderCallback->expects($this->once())->method('confirmPayment')->with(self::TEST_VPSTXID);

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteNoTxAuthCode()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);
        $paymentMock->expects($this->exactly(7))->method('setAdditionalInformation');

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();
        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));
        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => "FF89BBB5FE43019620C21F3E763179BB"
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->updateOrderCallback->expects($this->once())->method('setOrder')->with($this->order);
        $this->updateOrderCallback->expects($this->once())->method('confirmPayment')->with(self::TEST_VPSTXID);

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteNoTxAuthCodeOrBankAutCode()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);
        $paymentMock->expects($this->exactly(6))->method('setAdditionalInformation');

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder(TransactionFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));
        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => "301680A8BBDB771C67918A6599703B10"
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->updateOrderCallback->expects($this->once())->method('setOrder')->with($this->order);
        $this->updateOrderCallback->expects($this->once())->method('confirmPayment')->with(self::TEST_VPSTXID);
        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteNoQuote()
    {
        $this->makeCartRepositoryWithException(self::QUOTE_ID, [self::STORE_ID]);
        $this->requestExpectsGetParam();

        $this->response->expects($this->once())
            ->method('setBody')
            ->with('Status=INVALID' . "\r\n" .
                'StatusDetail=Unable to find quote' . "\r\n" .
                'RedirectURL=?message=Unable to find quote&quote=' . "\r\n");

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteNoOrder()
    {
        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrderFailed();
        $this->suiteHelperExpectsRemoveCurlyBraces(0);
        $this->controllerInstantiate();

        $this->responseExpectsSetBody(
            'Status=INVALID' . "\r\n" .
            'StatusDetail=Something went wrong: Invalid order.' . "\r\n" .
            'RedirectURL=?message=Something went wrong: Invalid order.&quote=' . "\r\n"
        );

        $this->serverNotifyController->execute();
    }

    public function testExecuteWithToken()
    {
        $serverModelMock = $this->makeServerModel();

        $paymentMock = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $this->order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(4);
        $this->order->expects($this->any())
            ->method('cancel')
            ->willReturnSelf();

        $transactionMock = $this
            ->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder(TransactionFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $invoiceCollectionMock = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceCollectionMock->expects($this->never())->method('setDataToAll')->willReturnSelf();

        $this->order
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->token->expects($this->once())
            ->method('saveToken')
            ->with(
                4,
                'DB771C67918A659',
                'VISA',
                '0006',
                '02',
                '22',
                'testebizmarts'
            )->willReturnSelf();

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . self::TEST_VPSTXID . "}",
                "StatusDetail" => "OK Status",
                "3DSecureStatus" => "NOTCHECKED",
                "BankAuthCode" => "999777",
                "TxAuthNo" => "17962849",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                'Token' => 'DB771C67918A659',
                "VPSSignature" => '8E77F29220981737C51C615C3464301F'
            ]));

        $this->responseExpectsSetBody(
            'Status=OK' . "\r\n" .
            'StatusDetail=Transaction completed successfully' . "\r\n" .
            'RedirectURL=?quoteid=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    public function testExecuteInvalidSignature()
    {
        $serverModelMock = $this->makeServerModel();
        $paymentMock     = $this->makeOrderPaymentMock($serverModelMock);

        $this->requestExpectsGetParam();
        $this->makeQuote();
        $this->makeCartRepositoryWithFoundQuote(self::QUOTE_ID, [self::STORE_ID]);
        $this->makeOrder($paymentMock, self::ORDER_ID);
        $this->suiteHelperExpectsRemoveCurlyBraces(1);

        $transactionMock = $this
            ->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->transactionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($transactionMock));

        $this->order->expects($this->any())
            ->method('cancel')
            ->willReturnSelf();

        $this->encryptor->expects($this->any())->method('encrypt')
            ->with(self::QUOTE_ID)
            ->willReturn(self::ENC_QUOTE_ID);

        $this->request->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "TxType" => "PAYMENT",
                "Status" => "OK",
                "VPSTxId" => "{" . "INVALID_TRANSACTION" . "}",
                "StatusDetail" => "ABORT Status",
                "3DSecureStatus" => "NOTCHECKED",
                "CardType" => "VISA",
                "Last4Digits" => "0006",
                "ExpiryDate" => "0222",
                "VendorTxCode" => "10000000001-2015-12-12-123456",
                "AVSCV2" => "OK",
                "AddressResult" => "OK",
                "PostCodeResult" => "OK",
                "CV2Result" => "OK",
                "GiftAid" => "0",
                "AddressStatus" => "OK",
                "PayerStatus" => "OK",
                "VPSSignature" => '123123123ads123'
            ]));

        $this->responseExpectsSetBody(
            'Status=INVALID' . "\r\n" .
            'StatusDetail=Something went wrong: Invalid VPS Signature' . "\r\n" .
            'RedirectURL=?message=Something went wrong: Invalid VPS Signature&quote=' . urlencode(self::ENC_QUOTE_ID) . "\r\n"
        );

        $this->controllerInstantiate();
        $this->serverNotifyController->execute();
    }

    /**
     * @param Context $contextMock
     * @param Config $configMock
     * @param TransactionFactory $transactionFactoryMock
     * @param Quote $quoteMock
     * @param \Ebizmarts\SagePaySuite\Helper\Data $helperMock
     * @param OrderSender $orderSender
     */
    private function controllerInstantiate()
    {
        $args = [
            'context'            => $this->context,
            'suiteLogger'        => $this->logger,
            'orderSender'        => $this->orderSender,
            'config'             => $this->config,
            'tokenModel'         => $this->token,
            'updateOrderCallback'=> $this->updateOrderCallback,
            'suiteHelper'        => $this->suiteHelper,
            'cartRepository'     => $this->cartRepository,
            'encryptor'          => $this->encryptor,
            'orderLoader'        => $this->orderLoaderMock
        ];

        $this->serverNotifyController = $this->objectManagerHelper->getObject(Notify::class, $args);
    }

    private function makeSuiteLogger()
    {
        $this->logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
    }

    private function makeSuiteHelper()
    {
        $this->suiteHelper = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
    }

    private function suiteHelperExpectsRemoveCurlyBraces($calls, $return = null)
    {
        if ($calls === 0) {
            $this->suiteHelper->expects($this->never())->method("removeCurlyBraces");
        } else {
            $returnValue = ($return !== null) ? $return : self::TEST_VPSTXID;
            $this->suiteHelper->expects($this->exactly($calls))->method("removeCurlyBraces")->willReturn($returnValue);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeServerModel()
    {
        $serverModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Server')->disableOriginalConstructor()->getMock();

        return $serverModelMock;
    }

    private function makeQuote()
    {
        $this->quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $this->quote->expects($this->any())->method('getId')->willReturn(self::QUOTE_ID);
    }

    private function makeCartRepositoryWithFoundQuote($quoteId, $sharedStoreIds = [])
    {
        $this->cartRepository = $this->getMockBuilder(QuoteRepository::class)->disableOriginalConstructor()->getMock();
        $this->cartRepository->expects($this->once())->method('get')->with($quoteId, $sharedStoreIds)->willReturn($this->quote);
    }

    private function makeCartRepositoryWithException($quoteId, $sharedStoreIds = [])
    {
        $this->cartRepository = $this->getMockBuilder(QuoteRepository::class)->disableOriginalConstructor()->getMock();
        $this->cartRepository->expects($this->once())->method('get')->with($quoteId, $sharedStoreIds)->willThrowException(new NoSuchEntityException(__('Test quote not found.')));
    }

    private function makeConfig()
    {
        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->config->expects($this->once())->method('setMethodCode')->with(Config::METHOD_SERVER);
        $this->config->expects($this->any())->method('getSagepayPaymentAction')->will($this->returnValue("PAYMENT"));
        $this->config->expects($this->any())->method('getVendorname')->will($this->returnValue("testebizmarts"));
    }

    /**
     * @param $serverModelMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeOrderPaymentMock($serverModelMock)
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')->disableOriginalConstructor()->getMock();
        $paymentMock->expects($this->any())->method('getLastTransId')->will($this->returnValue(self::TEST_VPSTXID));
        $paymentMock->expects($this->any())->method('getMethodInstance')->will($this->returnValue($serverModelMock));

        return $paymentMock;
    }

    private function makeHttpResponse()
    {
        $this->response = $this->getMockBuilder(HttpResponse::class)->disableOriginalConstructor()->getMock();
    }

    private function makeHttpRequest()
    {
        $this->request = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->getMock();
    }

    private function makeUrlBuilder()
    {
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()->getMock();
    }

    private function makeContext()
    {
        $this->context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getUrl')->will($this->returnValue($this->urlBuilder));
    }

    /**
     * @param $paymentMock
     */
    private function makeOrder($paymentMock = null, $id = null)
    {
        $this->order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        if ($paymentMock) {
            $this->order->expects($this->any())->method('getPayment')->will($this->returnValue($paymentMock));
        }
        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($this->quote)
            ->willReturn($this->order);
        $this->order->expects($this->any())->method('place')->willReturnSelf();
    }

    public function makeOrderFailed($paymentMock = null, $id = null)
    {
        $this->order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        if ($paymentMock) {
            $this->order->expects($this->any())->method('getPayment')->will($this->returnValue($paymentMock));
        }
        $this->orderLoaderMock
            ->expects($this->once())
            ->method('loadOrderFromQuote')
            ->with($this->quote)
            ->willThrowException(new \Exception('Invalid order.'));
        $this->order->expects($this->any())->method('place')->willReturnSelf();
    }


    private function makeOrderSender()
    {
        $this->orderSender = $this->getMockBuilder(OrderSender::class)->disableOriginalConstructor()->getMock();
    }

    private function makeUpdateOrderCallback()
    {
        $this->updateOrderCallback = $this->getMockBuilder(OrderUpdateOnCallback::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function makeToken()
    {
        $this->token = $this->getMockBuilder(Token::class)->disableOriginalConstructor()->getMock();
    }

    private function makeEncryptor()
    {
        $this->encryptor = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->encryptor->expects($this->once())->method('decrypt')
            ->with(self::ENC_QUOTE_ID)
            ->willReturn(self::QUOTE_ID);
    }

    private function makeOrderLoader()
    {
        $this->orderLoaderMock = $this
            ->getMockBuilder(OrderLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $body
     */
    private function responseExpectsSetBody($body)
    {
        $this->response->expects($this->atLeastOnce())
            ->method('setBody')
            ->with($body);
    }

    private function requestExpectsGetParam()
    {
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnCallback(function ($param) {
                if ($param === '_store') {
                    return self::STORE_ID;
                }

                if ($param === 'quoteid') {
                    return self::ENC_QUOTE_ID;
                }
                return '';
            });
    }
}
