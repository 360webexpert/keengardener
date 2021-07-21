<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Helper;

class FraudTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Fraud
     */
    private $fraudHelperModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportingApiMock;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportingApiMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Api\Reporting')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fraudHelperModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Fraud',
            [
                "config" => $this->configMock,
                "reportingApi" => $this->reportingApiMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testProcessFraudInformationNoResult()
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudResponse */
        $fraudResponse = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse::class)
            ->setMethods(['setFraudScreenRecommendation'])
            ->disableOriginalConstructor()
            ->getMock();

        $fraudResponse->setErrorCode("0000");
        $fraudResponse->setFraudId("someid");
        $fraudResponse->setFraudCode("somecode");
        $fraudResponse->setFraudCodeDetail("somedetail");
        $fraudResponse->setFraudProviderName("T3M");
        $fraudResponse->setThirdmanAction("NORESULT");
        $fraudResponse->setThirdmanRules([]);

        $this->reportingApiMock->expects($this->once())->method('getFraudScreenDetail')->willReturn($fraudResponse);

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())->method('setAdditionalInformation')
            ->with("fraudscreenrecommendation", 'NORESULT')
            ->willReturnSelf();

        $return = $this->fraudHelperModel->processFraudInformation($transactionMock, $paymentMock);

        $this->assertCount(2, $return);
        $this->assertArrayHasKey('fraudscreenrecommendation', $return);
        $this->assertEquals('NORESULT', $return['fraudscreenrecommendation']);
    }

    public function testProcessFraudInformationResponse()
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudResponse */
        $fraudResponse = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse::class)
            ->setMethods(['setFraudScreenRecommendation'])
            ->disableOriginalConstructor()
            ->getMock();
        $fraudResponse->setErrorCode("0010");

        $this->reportingApiMock->expects($this->once())->method('getFraudScreenDetail')->willReturn($fraudResponse);

        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $return = $this->fraudHelperModel->processFraudInformation($transactionMock, $paymentMock);

        $this->assertArrayHasKey('ERROR', $return);
        $this->assertEquals('Invalid Response: 0010', $return['ERROR']);
    }

    /**
     * @dataProvider processFraudInformationDataProvider
     */
    public function testProcessFraudInformation($data)
    {
        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturn($data['payment_mode']);

        $fraudScreenRuleMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenRule::class)
            ->setMethods(['getScore', 'getDescription', '__toArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceServiceFactoryMock = $this
            ->getMockBuilder('Magento\Sales\Model\Service\InvoiceServiceFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $transactionFactoryMock = $this
            ->getMockBuilder('\Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudResponseMock */
        $fraudResponseMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['setTimestamp']) //This is so all other methods are not mocked.
            ->getMock();
        $fraudResponseMock->setErrorCode('0000');

        if ($data['payment_mode'] == \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE) {
            if (isset($data['expects']['fraudid'])) {
                $fraudResponseMock->setThirdmanId($data['expects']['fraudid']);
            }
            if (isset($data['expects']['fraudid'])) {
                $fraudResponseMock->setFraudId($data['expects']['fraudid']);
            }
            if (isset($data['expects']['fraudprovidername'])) {
                $fraudResponseMock->setFraudProviderName($data['expects']['fraudprovidername']);
            }
            if (isset($data['expects']['fraudscreenrecommendation'])) {
                $fraudResponseMock->setThirdmanAction($data['expects']['fraudscreenrecommendation']);
            }
            if (isset($data['fraudscreenrecommendation'])) {
                $fraudResponseMock->setFraudScreenRecommendation($data['fraudscreenrecommendation']);
            }
            if (isset($data['expects']['fraudcode'])) {
                $fraudResponseMock->setThirdmanScore($data['expects']['fraudcode']);
            }
            if (isset($data['expects']['fraudcode'])) {
                $fraudResponseMock->setFraudCode($data['expects']['fraudcode']);
            }
            if (isset($data['expects']['fraudcodedetail'])) {
                $fraudResponseMock->setFraudCodeDetail($data['expects']['fraudcodedetail']);
            }
            if (isset($data['expects']['fraudrules']) && !empty($data['expects']['fraudrules'])) {
                $fraudScreenRuleMock->method('getScore')->willReturn(34);
                $fraudScreenRuleMock->method('getDescription')->willReturn('no phone ok');
                $fraudScreenRuleMock->method('__toArray')->willReturn([
                    'score'       => 34,
                    'description' => 'no phone ok'
                ]);
                $rules = [$fraudScreenRuleMock];
                $fraudResponseMock->setThirdmanRules($rules);
            }

            $this->reportingApiMock->expects($this->once())
                ->method('getFraudScreenDetail')
                ->willReturn($fraudResponseMock);

            $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
            $this->fraudHelperModel = $objectManagerHelper->getObject(
                'Ebizmarts\SagePaySuite\Helper\Fraud',
                [
                    "config" => $this->configMock,
                    "reportingApi" => $this->reportingApiMock,
                    "invoiceService" => $invoiceServiceFactoryMock,
                    "transactionFactory" => $transactionFactoryMock
                ]
            );
            $invoiceMock = $this
                ->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
                ->disableOriginalConstructor()
                ->setMethods(['setRequestedCaptureCase', 'register', 'save', 'getTotalQty', 'getOrder'])
                ->getMock();
            $invoiceMock
                ->expects($this->exactly($data['expectedregister']))
                ->method('register')
                ->willReturnSelf();
            $invoiceMock
                ->expects($this->exactly($data['expectedcapture']))
                ->method('setRequestedCaptureCase')
                ->with(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE)
                ->willReturnSelf();
            $invoiceMock
                ->expects($this->exactly($data['expectedqty']))
                ->method('getTotalQty')
                ->willReturn(1);

            $invoiceServiceMock = $this
                ->getMockBuilder('Magento\Sales\Model\Service\InvoiceService')
                ->disableOriginalConstructor()
                ->getMock();
            $invoiceServiceMock
                ->expects($this->exactly($data['expectedinvoice']))
                ->method('prepareInvoice')
                ->willReturn($invoiceMock);

            $invoiceServiceFactoryMock
                ->expects($this->exactly($data['expectedcreate']))
                ->method('create')
                ->willReturn($invoiceServiceMock);

            $orderMock = $this
                ->getMockBuilder('Magento\Sales\Model\Order')
                ->disableOriginalConstructor()
                ->getMock();

            $invoiceMock
                ->expects($this->exactly($data['expectedgetorder']))
                ->method('getOrder')
                ->willReturn($orderMock);

            $transactionSaveMock = $this
                ->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
                ->disableOriginalConstructor()
                ->setMethods(['addObject', 'save'])
                ->getMock();
            $transactionSaveMock
                ->expects($this->exactly($data['expectedaddobject']))
                ->method('addObject')
                ->withConsecutive([$invoiceMock], [$orderMock])
                ->willReturnSelf();
            $transactionSaveMock
                ->expects($this->exactly($data['expectedsave']))
                ->method('save')
                ->willReturnSelf();

            $transactionFactoryMock
                ->expects($this->exactly($data['expectedcreate']))
                ->method('create')
                ->willReturn($transactionSaveMock);

            $paymentMock
                ->expects($this->exactly($data['expectedorder']))
                ->method('getOrder')
                ->willReturn($orderMock);

            $orderMock
                ->expects($this->once())
                ->method('getState')
                ->willReturn($data['getState']);

            $this->configMock->expects($this->any())
                ->method('getAutoInvoiceFraudPassed')
                ->willReturn($data['getAutoInvoiceFraudPassed']);
        }

        $transactionMock->expects($this->any())
            ->method('getTxnType')
            ->willReturn(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

        $this->assertEquals(
            $data['expects'],
            $this->fraudHelperModel->processFraudInformation($transactionMock, $paymentMock)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testNoInvoiceException()
    {
        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturn(\Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE);

        $invoiceServiceFactoryMock = $this
            ->getMockBuilder('Magento\Sales\Model\Service\InvoiceServiceFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $transactionFactoryMock = $this
            ->getMockBuilder('\Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudResponseMock */
        $fraudResponseMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['setTimestamp'])
            ->getMock();
        $fraudResponseMock->setErrorCode('0000');
        $fraudResponseMock->setFraudScreenRecommendation('ACCEPT');
        $fraudResponseMock->setFraudId('12345');
        $fraudResponseMock->setFraudCode('765');
        $fraudResponseMock->setFraudCodeDetail('Fraud card');
        $fraudResponseMock->setFraudProviderName('ReD');


        $this->reportingApiMock->expects($this->once())
            ->method('getFraudScreenDetail')
            ->willReturn($fraudResponseMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fraudHelperModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Fraud',
            [
                "config" => $this->configMock,
                "reportingApi" => $this->reportingApiMock,
                "invoiceService" => $invoiceServiceFactoryMock,
                "transactionFactory" => $transactionFactoryMock
            ]
        );

        $invoiceInterfaceMock = $this
            ->getMockBuilder('\Magento\Sales\Api\Data\InvoiceInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceServiceMock = $this
            ->getMockBuilder('Magento\Sales\Model\Service\InvoiceService')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceServiceMock
            ->expects($this->exactly(1))
            ->method('prepareInvoice')
            ->willReturn($invoiceInterfaceMock);

        $invoiceServiceFactoryMock
            ->expects($this->exactly(1))
            ->method('create')
            ->willReturn($invoiceServiceMock);

        $orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);

        $this->configMock->expects($this->any())
            ->method('getAutoInvoiceFraudPassed')
            ->willReturn(true);

        $transactionMock->expects($this->any())
            ->method('getTxnType')
            ->willReturn(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

        $this->fraudHelperModel->processFraudInformation($transactionMock, $paymentMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testNoProductException()
    {
        $transactionMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturn(\Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE);

        $invoiceServiceFactoryMock = $this
            ->getMockBuilder('Magento\Sales\Model\Service\InvoiceServiceFactory')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudResponseMock */
        $fraudResponseMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['setTimestamp'])
            ->getMock();
        $fraudResponseMock->setErrorCode('0000');
        $fraudResponseMock->setFraudScreenRecommendation('ACCEPT');
        $fraudResponseMock->setFraudId('12345');
        $fraudResponseMock->setFraudCode('765');
        $fraudResponseMock->setFraudCodeDetail('Fraud card');
        $fraudResponseMock->setFraudProviderName('ReD');

        $this->reportingApiMock->expects($this->once())
            ->method('getFraudScreenDetail')
            ->willReturn($fraudResponseMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fraudHelperModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Helper\Fraud',
            [
                "config" => $this->configMock,
                "reportingApi" => $this->reportingApiMock,
                "invoiceService" => $invoiceServiceFactoryMock
            ]
        );

        $invoiceMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTotalQty'])
            ->getMock();
        $invoiceMock
            ->expects($this->exactly(1))
            ->method('getTotalQty')
            ->willReturn(0);

        $invoiceServiceMock = $this
            ->getMockBuilder('Magento\Sales\Model\Service\InvoiceService')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceServiceMock
            ->expects($this->exactly(1))
            ->method('prepareInvoice')
            ->willReturn($invoiceMock);

        $invoiceServiceFactoryMock
            ->expects($this->exactly(1))
            ->method('create')
            ->willReturn($invoiceServiceMock);

        $orderMock = $this
            ->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock
            ->expects($this->exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);

        $this->configMock->expects($this->any())
            ->method('getAutoInvoiceFraudPassed')
            ->willReturn(true);

        $transactionMock->expects($this->any())
            ->method('getTxnType')
            ->willReturn(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

        $this->fraudHelperModel->processFraudInformation($transactionMock, $paymentMock);
    }

    public function processFraudInformationDataProvider()
    {
        return [
            'test live thirdman' => [
                [
                    'payment_mode' => \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE,
                    'fraudscreenrecommendation' => \Ebizmarts\SagePaySuite\Model\Config::T3STATUS_REJECT,
                    'getAutoInvoiceFraudPassed' => false,
                    'getState' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    'expectedregister' => 0,
                    'expectedcapture' => 0,
                    'expectedsave' => 0,
                    'expectedorder' => 1,
                    'expectedinvoice' => 0,
                    'expectedrelatedobject' => 0,
                    'expectedqty' => 0,
                    'expectedcreate' => 0,
                    'expectedgetorder' => 0,
                    'expectedaddobject' => 0,
                    'expects' => [
                        'VPSTxId'     => null,
                        'fraudprovidername' => 'T3M',
                        'fraudscreenrecommendation' => 'HOLD',
                        'fraudid' => '4985075328',
                        'fraudcode' => '37',
                        'fraudcodedetail' => 'HOLD',
                        'fraudrules' => [
                            [
                                'score'       => 34,
                                'description' => 'no phone ok'
                            ]
                        ]
                    ]
                ]
            ],
            'test test' => [
                [
                    'payment_mode' => \Ebizmarts\SagePaySuite\Model\Config::MODE_TEST,
                    'expects' => [
                        'VPSTxId' => null,
                        'Action' => 'Marked as TEST',
                    ]
                ]
            ],
            'test live reject' => [
                [
                    'payment_mode' => \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE,
                    'fraudscreenrecommendation' => \Ebizmarts\SagePaySuite\Model\Config::T3STATUS_REJECT,
                    'getAutoInvoiceFraudPassed' => false,
                    'getState' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    'expectedregister' => 0,
                    'expectedcapture' => 0,
                    'expectedsave' => 0,
                    'expectedorder' => 2,
                    'expectedinvoice' => 0,
                    'expectedrelatedobject' => 0,
                    'expectedqty' => 0,
                    'expectedcreate' => 0,
                    'expectedgetorder' => 0,
                    'expectedaddobject' => 0,
                    'expects' => [
                        'VPSTxId'     => null,
                        'fraudrules' => [],
                        'fraudscreenrecommendation' => 'REJECT',
                        'fraudid' => '4985075328',
                        'fraudcode' => null,
                        'fraudcodedetail' => 'REJECT',
                        'fraudprovidername' => 'T3M',
                        'Action' => 'Marked as FRAUD.'
                    ]
                ]
            ],
            'test live ok' => [
                [
                    'payment_mode' => \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE,
                    'fraudscreenrecommendation' => \Ebizmarts\SagePaySuite\Model\Config::REDSTATUS_ACCEPT,
                    'getAutoInvoiceFraudPassed' => true,
                    'getState' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    'expectedregister' => 1,
                    'expectedcapture' => 1,
                    'expectedsave' => 1,
                    'expectedorder' => 2,
                    'expectedinvoice' => 1,
                    'expectedrelatedobject' => 1,
                    'expectedqty' => 1,
                    'expectedcreate' => 1,
                    'expectedgetorder' => 3,
                    'expectedaddobject' => 2,
                    'expects' => [
                        'VPSTxId' => null,
                        'fraudscreenrecommendation' => 'ACCEPT',
                        'fraudid' => '12345',
                        'fraudcode' => '765',
                        'fraudcodedetail' => 'Fraud card',
                        'fraudprovidername' => 'ReD',
                        'Action' => 'Captured online, invoice # generated.'
                    ]
                ]
            ],
            'test live no result' => [
                [
                    'payment_mode' => 'test',
                    'fraudscreenrecommendation' => 'NORESULT',
                    'getAutoInvoiceFraudPassed' => false,
                    'expects' => [
                        'VPSTxId' => null,
                        'Action'  => 'Marked as TEST',
                    ]
                ]
            ],
            'test on hold' => [
                [
                    'payment_mode' => \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE,
                    'fraudscreenrecommendation' => \Ebizmarts\SagePaySuite\Model\Config::REDSTATUS_ACCEPT,
                    'getAutoInvoiceFraudPassed' => true,
                    'getState' => \Magento\Sales\Model\Order::STATE_HOLDED,
                    'expectedregister' => 0,
                    'expectedcapture' => 0,
                    'expectedsave' => 0,
                    'expectedorder' => 1,
                    'expectedinvoice' => 0,
                    'expectedrelatedobject' => 0,
                    'expectedqty' => 0,
                    'expectedcreate' => 0,
                    'expectedgetorder' => 0,
                    'expectedaddobject' => 0,
                    'expects' => [
                        'VPSTxId' => null,
                        'fraudscreenrecommendation' => 'ACCEPT',
                        'fraudid' => '12345',
                        'fraudcode' => '765',
                        'fraudcodedetail' => 'Fraud card',
                        'fraudprovidername' => 'ReD',
                    ]
                ]
            ]
        ];
    }
}
