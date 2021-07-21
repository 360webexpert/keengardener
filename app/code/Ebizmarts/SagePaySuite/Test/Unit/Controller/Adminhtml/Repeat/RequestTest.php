<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\Request;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForAction;
use Magento\Sales\Model\Order\Payment\Transaction;

class RequestTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';

    /**
     * @var \Ebizmarts\SagePaySuite\Controller\Adminhtml\Repeat\Request
     */
    private $repeatRequestController;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var  \Magento\Quote\Model\QuoteManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagementMock;

    /**
     * @var  Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    private $repeatModelMock;

    private $configMock;

    private $paymentMock;

    // @codingStandardsIgnoreStart
    private $actionFactoryMock;

    private $transactionFactoryMock;

    private $suiteHelperMock;

    protected function setUp()
    {
        $this->repeatModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Repeat')->disableOriginalConstructor()->getMock();

        $this->paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')->disableOriginalConstructor()->getMock();
        $this->paymentMock->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->repeatModelMock));

        $addressMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(["getGrandTotal", "getQuoteCurrencyCode", "getPayment", "getBillingAddress", "collectTotals", "reserveOrderId"])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->exactly(1))
            ->method('collectTotals')
            ->willReturnSelf();
        $quoteMock->expects($this->exactly(1))
            ->method('reserveOrderId')
            ->willReturnSelf();
        $quoteMock->expects($this->any())
            ->method('getGrandTotal')
            ->will($this->returnValue(100));
        $quoteMock->expects($this->any())
            ->method('getQuoteCurrencyCode')
            ->will($this->returnValue('USD'));
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($addressMock));

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

        $this->resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJson);

        $requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "vpstxid" => "12345"
            ]));

        $urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($resultFactoryMock));
        $contextMock->expects($this->any())
            ->method('getBackendUrl')
            ->will($this->returnValue($urlBuilderMock));

        $this->configMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')->disableOriginalConstructor()->getMock();

        $this->suiteHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->suiteHelperMock->expects($this->any())
            ->method('generateVendorTxCode')
            ->will($this->returnValue("10000001-2015-12-12-12-12345"));

        $sharedapiMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\Shared')
            ->disableOriginalConstructor()
            ->getMock();
        $sharedapiMock->expects($this->any())
            ->method('repeatTransaction')
            ->will($this->returnValue([
                "data" => [
                    "VPSTxId" => self::TEST_VPSTXID,
                    "StatusDetail" => 'OK Status'
                ]
            ]));

        $this->orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $this->orderMock->expects($this->any())
            ->method('place')
            ->willReturnSelf();

        $this->quoteManagementMock = $this
            ->getMockBuilder('Magento\Quote\Model\QuoteManagement')
            ->disableOriginalConstructor()
            ->getMock();

        $requestHelperMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Helper\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $requestHelperMock->expects($this->any())
            ->method('populatePaymentAmountAndCurrency')
            ->will($this->returnValue([]));
        $requestHelperMock->expects($this->any())
            ->method('populateAddressInformation')
            ->will($this->returnValue([]));
        $requestHelperMock->expects($this->any())
            ->method('getOrderDescription')
            ->will($this->returnValue("description"));

        $this->actionFactoryMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->transactionFactoryMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\TransactionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->repeatRequestController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Repeat\Request',
            [
                'context'            => $contextMock,
                'config'             => $this->configMock,
                'suiteHelper'        => $this->suiteHelperMock,
                'sharedApi'          => $sharedapiMock,
                'quoteSession'       => $quoteSessionMock,
                'quoteManagement'    => $this->quoteManagementMock,
                'requestHelper'      => $requestHelperMock,
                'actionFactory'      => $this->actionFactoryMock,
                'transactionFactory' => $this->transactionFactoryMock,
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param string $paymentAction
     * @param int $initializedCount
     * @param int $transactionClosedCount
     * @dataProvider successProvider
     */
    public function testExecuteSuccess($paymentAction, $initializedCount, $transactionClosedCount)
    {
        $actionTransactionMock = $paymentAction === Config::ACTION_REPEAT_DEFERRED ? 1 : 0;

        $this->actionFactoryMock
            ->expects($this->exactly($actionTransactionMock))
            ->method('create')
            ->willReturn(
                new ClosedForAction($paymentAction)
            );

        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock->expects($this->exactly($actionTransactionMock))->method('setIsClosed')->with(false);
        $transactionMock->expects($this->exactly($actionTransactionMock))->method('setTxnType')->with('authorization');

        $this->transactionFactoryMock
            ->expects($this->exactly($actionTransactionMock))
            ->method('create')
            ->willReturn($transactionMock);

        $this->repeatModelMock->expects($this->exactly($initializedCount))->method('markAsInitialized');

        $this->configMock->expects($this->any())->method('getSagepayPaymentAction')->willReturn($paymentAction);

        $this->paymentMock->expects($this->exactly($transactionClosedCount))->method('setIsTransactionClosed')->with(0);

        $this->accertRemoveCurlyBraces(1);

        $this->quoteManagementMock->expects($this->any())
            ->method('submit')
            ->will($this->returnValue($this->orderMock));

        $this->_expectResultJson([
            "success" => true,
            'response' => [
                "data" => [
                    "VPSTxId" => self::TEST_VPSTXID,
                    "StatusDetail" => "OK Status",
                    "redirect" => null
                ]
            ]
        ]);

        $this->repeatRequestController->execute();
    }

    public function successProvider()
    {
        return [
            [Config::ACTION_REPEAT, 1, 0],
            [Config::ACTION_REPEAT_DEFERRED, 0, 0]
        ];
    }

    public function testExecuteERROR()
    {
        $this->quoteManagementMock->expects($this->once())
            ->method('submit')
            ->willThrowException(new \Exception('Unable to save Sage Pay order.'));

        $this->_expectResultJson([
            "success" => false,
            'error_message' => __("Something went wrong: %1", "Unable to save Sage Pay order.")
        ]);

        $this->accertRemoveCurlyBraces(0);

        $this->repeatRequestController->execute();
    }

    /**
     * @param $result
     */
    private function _expectResultJson($result)
    {
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with($result);
    }

    private function accertRemoveCurlyBraces($count)
    {
        $this->suiteHelperMock
            ->expects($this->exactly($count))
            ->method('removeCurlyBraces')
            ->with(self::TEST_VPSTXID)
            ->willReturn(self::TEST_VPSTXID);
    }
}
