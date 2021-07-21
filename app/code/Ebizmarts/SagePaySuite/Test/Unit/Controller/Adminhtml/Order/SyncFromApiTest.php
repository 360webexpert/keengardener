<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\Order;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\OrderRepository;

class SyncFromApiTest extends \PHPUnit\Framework\TestCase
{
    const TEST_STORE_ID = 1;
    const TEST_VPS_TX_ID = '463B3DE6-443F-585B-E75C-C727476DE98F';

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }
    // @codingStandardsIgnoreEnd

    public function testExecute()
    {
        $redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $responseMock = $this->makeResponseMock();

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with(__('Successfully synced from Sage Pay\'s API'));

        $requestMock = $this->makeRequestMock();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue(1));

        $urlBuilderMock = $this->makeUrlBuilderMock();

        $actionFlagMock = $this->makeActionFlagMock();

        $sessionMock = $this->makeSessionMock();

        $helperMock = $this->makeHelperMock();

        $contextMock = $this->makeContextMock(
            $responseMock,
            $redirectMock,
            $messageManagerMock,
            $requestMock,
            $urlBuilderMock,
            $actionFlagMock,
            $sessionMock,
            $helperMock
        );

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->setMethods(['getLastTransId', 'setAdditionalInformation', 'save', 'setLastTransId'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->exactly(3))
            ->method('getLastTransId')
            ->willReturn('{' . self::TEST_VPS_TX_ID .'}');

        $paymentMock
            ->expects($this->exactly(4))
            ->method('setAdditionalInformation')
            ->willReturnSelf();

        $paymentMock
            ->expects($this->once())
            ->method('setLastTransId')
            ->willReturnSelf();

        $orderMock = $this->makeOrderMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock->expects($this->once())
            ->method('get')->with(1)
            ->willReturn($orderMock);

        $orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

        $orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(self::TEST_STORE_ID);

        $reportingApiMock = $this->makeReportingApiMock();

        $reportingApiMock->expects($this->once())
            ->method('getTransactionDetailsByVpstxid')
            ->with(self::TEST_VPS_TX_ID, self::TEST_STORE_ID)
            ->will($this->returnValue((object)[
                "vendortxcode" => "100000001-2016-12-12-123456",
                "vpstxid" => "513C0BA4-E135-469B-DF3E-DF936FF69291",
                "status" => "OK STATUS",
                "securitykey" => "CDBE617TI9",
                "threedresult" => "CHECKED"
            ]));

        $trnRepoMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class)
            ->setMethods(['getSagepaysuiteFraudCheck', 'getByTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $trnRepoMock
            ->expects($this->once())
            ->method('getSagepaysuiteFraudCheck')
            ->willReturn(false);

        $trnRepoMock
            ->expects($this->once())
            ->method('getByTransactionId')
            ->willReturnSelf();

        $fraudHelperMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Fraud::class)
            ->setMethods(['processFraudInformation'])
            ->disableOriginalConstructor()
            ->getMock();

        $fraudHelperMock->expects($this->once())->method('processFraudInformation');

        $suiteHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['clearTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $suiteHelperMock
            ->expects($this->once())
            ->method('clearTransactionId')
            ->with('{' . self::TEST_VPS_TX_ID .'}')
            ->willReturn(self::TEST_VPS_TX_ID);

        $syncFromApiController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Order\SyncFromApi',
            [
                'context'               => $contextMock,
                'orderRepository'       => $orderRepositoryMock,
                'reportingApi'          => $reportingApiMock,
                'transactionRepository' => $trnRepoMock,
                'fraudHelper'           => $fraudHelperMock,
                'suiteHelper'           => $suiteHelperMock
            ]
        );

        $syncFromApiController->execute();
    }

    public function testExecuteNoTransactionFound()
    {
        $redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $responseMock = $this->makeResponseMock();

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with(__('Successfully synced from Sage Pay\'s API'));

        $requestMock = $this->makeRequestMock();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue(1));

        $urlBuilderMock = $this->makeUrlBuilderMock();

        $actionFlagMock = $this->makeActionFlagMock();

        $sessionMock = $this->makeSessionMock();

        $helperMock = $this->makeHelperMock();

        $contextMock = $this->makeContextMock(
            $responseMock,
            $redirectMock,
            $messageManagerMock,
            $requestMock,
            $urlBuilderMock,
            $actionFlagMock,
            $sessionMock,
            $helperMock
        );

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->setMethods(['getLastTransId', 'setAdditionalInformation', 'save', 'getAdditionalInformation', 'setLastTransId'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->exactly(3))
            ->method('getLastTransId')
            ->willReturn('NOT_TO_BE_FOUND-463B3DE6-443F-585B-E75C-C727476DE98F');

        $paymentMock
            ->expects($this->atLeastOnce())
            ->method('setAdditionalInformation')
            ->willReturnSelf();

        $orderMock = $this->makeOrderMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock->expects($this->once())
            ->method('get')->with(1)
            ->willReturn($orderMock);

        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(self::TEST_STORE_ID);

        $reportingApiMock = $this->makeReportingApiMock();

        $paymentMock
            ->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('vendorTxCode')
            ->willReturn('000000010-2019-12-23-1854421577127282');

        $reportingApiMock
            ->expects($this->once())
            ->method('getTransactionDetailsByVendorTxCode')
            ->with('000000010-2019-12-23-1854421577127282', 1)
            ->will($this->returnValue((object)[
                'vendortxcode' => '000000010-2019-12-23-1854421577127282',
                'vpstxid' => '513C0BA4-E135-469B-DF3E-DF936FF69291',
                'status' => 'OK STATUS',
                'threedresult' => 'CHECKED'
            ]));

        $paymentMock
            ->expects($this->once())
            ->method('setLastTransId')
            ->with('513C0BA4-E135-469B-DF3E-DF936FF69291');

        $paymentMock
            ->expects($this->once())
            ->method('save');

        $trnRepoMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class)
            ->setMethods(['getSagepaysuiteFraudCheck', 'getByTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $trnRepoMock->expects($this->never())->method('getSagepaysuiteFraudCheck');
        $trnRepoMock
            ->expects($this->once())
            ->method('getByTransactionId')
            ->willReturn(false);

        $fraudHelperMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Fraud::class)
            ->setMethods(['processFraudInformation'])
            ->disableOriginalConstructor()
            ->getMock();

        $fraudHelperMock->expects($this->never())->method('processFraudInformation');

        $syncFromApiController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Order\SyncFromApi',
            [
                'context'               => $contextMock,
                'orderRepository'       => $orderRepositoryMock,
                'reportingApi'          => $reportingApiMock,
                'transactionRepository' => $trnRepoMock,
                'fraudHelper'           => $fraudHelperMock,
                //'suiteHelper'           => $suiteHelperMock
            ]
        );

        $syncFromApiController->execute();
    }

    public function testExecuteNoParam()
    {
        $requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn(null);

        $actionFlagMock = $this->makeActionFlagMock();

        $sessionMock = $this->makeSessionMock();

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Something went wrong: Unable to sync from API: Invalid order id.');

        $urlBuilderMock = $this->makeUrlBuilderMock();
        $urlBuilderMock->expects($this->once())->method('getUrl')->with('sales/order/index/', []);

        $helperMock = $this->makeHelperMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($requestMock);
        $contextMock->expects($this->any())
            ->method('getBackendUrl')
            ->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($sessionMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this
                ->getMockBuilder('Magento\Framework\App\Response\Http', [], [], '', false)
                ->disableOriginalConstructor()
                ->getMock()));
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($helperMock));

        $controller = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Order\SyncFromApi',
            [
                'context' => $contextMock,
            ]
        );

        $controller->execute();
    }

    /**
     * @dataProvider apiExceptionProvider
     */
    public function testExecuteApiException($data)
    {
        $redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $responseMock = $this->makeResponseMock();

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__($data["cleanedException"]));

        $requestMock = $this->makeRequestMock();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn(5899);

        $urlBuilderMock = $this->makeUrlBuilderMock();
        $urlBuilderMock
            ->expects($this->once())
            ->method('getUrl')
            ->with('sales/order/view/', ['order_id' => 5899]);

        $actionFlagMock = $this->makeActionFlagMock();

        $sessionMock = $this->makeSessionMock();

        $helperMock = $this->makeHelperMock();

        $contextMock = $this->makeContextMock(
            $responseMock,
            $redirectMock,
            $messageManagerMock,
            $requestMock,
            $urlBuilderMock,
            $actionFlagMock,
            $sessionMock,
            $helperMock
        );

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $suiteHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['clearTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->setMethods(['getLastTransId', 'setAdditionalInformation', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->never())
            ->method('setAdditionalInformation')
            ->willReturnSelf();

        $orderMock = $this->makeOrderMock();

        $orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(5899);

        $orderRepositoryMock->expects($this->once())
            ->method('get')->with(5899)
            ->willReturn($orderMock);

        $reportingApiMock = $this->makeReportingApiMock();

        $error     = new \Magento\Framework\Phrase($data["exception"]);
        $exception = new \Ebizmarts\SagePaySuite\Model\Api\ApiException($error);

        $paymentMock
            ->expects($this->once())
            ->method('getLastTransId')
            ->willReturn('{' . self::TEST_VPS_TX_ID . '}');

        $suiteHelperMock
            ->expects($this->once())
            ->method('clearTransactionId')
            ->with('{' . self::TEST_VPS_TX_ID . '}')
            ->willReturn(self::TEST_VPS_TX_ID);

        $reportingApiMock->expects($this->once())
            ->method('getTransactionDetailsByVpstxid')
            ->willThrowException($exception);

        $trnRepoMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class)
            ->setMethods(['getSagepaysuiteFraudCheck', 'getByTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $trnRepoMock
            ->expects($this->never())
            ->method('getSagepaysuiteFraudCheck');
        $trnRepoMock
            ->expects($this->never())
            ->method('getByTransactionId')
            ->willReturnSelf();

        $fraudHelperMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Fraud::class)
            ->setMethods(['processFraudInformation'])
            ->disableOriginalConstructor()
            ->getMock();
        $fraudHelperMock->expects($this->never())->method('processFraudInformation');

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->setMethods(['sageLog'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->once())->method('sageLog')
            ->with(Logger::LOG_EXCEPTION, $exception->getTraceAsString());

        $syncFromApiController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Order\SyncFromApi',
            [
                'context'                => $contextMock,
                'orderRepository'       => $orderRepositoryMock,
                'reportingApi'          => $reportingApiMock,
                'transactionRepository' => $trnRepoMock,
                'fraudHelper'           => $fraudHelperMock,
                'suiteLogger'           => $loggerMock,
                'suiteHelper'           => $suiteHelperMock
            ]
        );

        $syncFromApiController->execute();
    }

    public function testExecuteSecurityKeyNotSet()
    {
        $redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $responseMock = $this->makeResponseMock();

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with(__('Successfully synced from Sage Pay\'s API'));

        $requestMock = $this->makeRequestMock();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue(1));

        $urlBuilderMock = $this->makeUrlBuilderMock();

        $actionFlagMock = $this->makeActionFlagMock();

        $sessionMock = $this->makeSessionMock();

        $helperMock = $this->makeHelperMock();

        $contextMock = $this->makeContextMock(
            $responseMock,
            $redirectMock,
            $messageManagerMock,
            $requestMock,
            $urlBuilderMock,
            $actionFlagMock,
            $sessionMock,
            $helperMock
        );

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->setMethods(['getLastTransId', 'setAdditionalInformation', 'save', 'setLastTransId'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->exactly(3))
            ->method('getLastTransId')
            ->willReturn('{' . self::TEST_VPS_TX_ID .'}');

        $paymentMock
            ->expects($this->exactly(3))
            ->method('setAdditionalInformation')
            ->willReturnSelf();

        $paymentMock
            ->expects($this->once())
            ->method('setLastTransId')
            ->willReturnSelf();

        $orderMock = $this->makeOrderMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock->expects($this->once())
            ->method('get')->with(1)
            ->willReturn($orderMock);

        $orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

        $orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(self::TEST_STORE_ID);

        $reportingApiMock = $this->makeReportingApiMock();

        $reportingApiMock->expects($this->once())
            ->method('getTransactionDetailsByVpstxid')
            ->with(self::TEST_VPS_TX_ID, self::TEST_STORE_ID)
            ->will($this->returnValue((object)[
                "vendortxcode" => "100000001-2016-12-12-123456",
                "vpstxid" => "513C0BA4-E135-469B-DF3E-DF936FF69291",
                "status" => "OK STATUS",
                "threedresult" => "CHECKED"
            ]));

        $trnRepoMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class)
            ->setMethods(['getSagepaysuiteFraudCheck', 'getByTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $trnRepoMock
            ->expects($this->once())
            ->method('getSagepaysuiteFraudCheck')
            ->willReturn(false);

        $trnRepoMock
            ->expects($this->once())
            ->method('getByTransactionId')
            ->willReturnSelf();

        $fraudHelperMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Helper\Fraud::class)
            ->setMethods(['processFraudInformation'])
            ->disableOriginalConstructor()
            ->getMock();

        $fraudHelperMock->expects($this->once())->method('processFraudInformation');

        $suiteHelperMock = $this
            ->getMockBuilder(Data::class)
            ->setMethods(['clearTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $suiteHelperMock
            ->expects($this->once())
            ->method('clearTransactionId')
            ->with('{' . self::TEST_VPS_TX_ID .'}')
            ->willReturn(self::TEST_VPS_TX_ID);

        $syncFromApiController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Order\SyncFromApi',
            [
                'context'               => $contextMock,
                'orderRepository'          => $orderRepositoryMock,
                'reportingApi'          => $reportingApiMock,
                'transactionRepository' => $trnRepoMock,
                'fraudHelper'           => $fraudHelperMock,
                'suiteHelper'           => $suiteHelperMock
            ]
        );

        $syncFromApiController->execute();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeResponseMock()
    {
        $responseMock = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false
        )->disableOriginalConstructor()->getMock();

        return $responseMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMessageManagerMock()
    {
        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')->disableOriginalConstructor()->getMock();

        return $messageManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeUrlBuilderMock()
    {
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')->disableOriginalConstructor()->getMock();

        return $urlBuilderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRequestMock()
    {
        $requestMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')->disableOriginalConstructor()->getMock();

        return $requestMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeActionFlagMock()
    {
        $actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')->disableOriginalConstructor()->getMock();

        return $actionFlagMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeHelperMock()
    {
        $helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')->disableOriginalConstructor()->getMock();

        return $helperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeSessionMock()
    {
        $sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')->disableOriginalConstructor()->getMock();

        return $sessionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeOrderMock()
    {
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods(['getPayment', 'getStoreId', 'getId'])
            ->disableOriginalConstructor()->getMock();
        return $orderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeReportingApiMock()
    {
        $reportingApiMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\Reporting')->disableOriginalConstructor()->getMock();

        return $reportingApiMock;
    }

    /**
     * @param $responseMock
     * @param $redirectMock
     * @param $messageManagerMock
     * @param $requestMock
     * @param $urlBuilderMock
     * @param $actionFlagMock
     * @param $sessionMock
     * @param $helperMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock(
        $responseMock,
        $redirectMock,
        $messageManagerMock,
        $requestMock,
        $urlBuilderMock,
        $actionFlagMock,
        $sessionMock,
        $helperMock
    ) {
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($responseMock));
        $contextMock->expects($this->any())->method('getRedirect')->will($this->returnValue($redirectMock));
        $contextMock->expects($this->any())->method('getMessageManager')->will($this->returnValue($messageManagerMock));
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($requestMock));
        $contextMock->expects($this->any())->method('getBackendUrl')->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($actionFlagMock));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($helperMock));

        return $contextMock;
    }

    public function apiExceptionProvider()
    {
        return [
            "testExecuteApiException" => [
                [
                    "exception" => "Unable to find the transaction for the <vendortxcode> or <vpstxid> supplied.",
                    "cleanedException" => "Unable to find the transaction for the vendortxcode or vpstxid supplied."
                ],
            "testExecuteApiException" =>
                [
                    "exception" => "The user does not have permission to view this transaction.",
                    "cleanedException" => "The user does not have permission to view this transaction."
                ]
            ]
        ];
    }
}
