<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use \Magento\Framework\Exception\LocalizedException;

class FormTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Form */
    private $formModelObject;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->formModelObject = $this->objectManagerHelper->getObject('\Ebizmarts\SagePaySuite\Model\Form');
    }
    // @codingStandardsIgnoreEnd

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid response from Opayo
     */
    public function testDecodeSagePayResponseEmpty()
    {
        $this->formModelObject->decodeSagePayResponse("");
    }

    public function testDecodeSagePayResponse()
    {
        $crypt = "@77a9f5fb9cbfc11c6f3d5d6b424c7e840ad2573a3dcab681978e33a10202f0483177475ac5a76752c8b10a736d13fe83bb";
        $crypt .= "f34446f55a4276008bdf1cf59d7c3fb2325524cb427779a1143320584e971664954712c5b2ed8f25d638156d2110457862a";
        $crypt .= "24d7ca7e6f0580b6462462548a83ba3636ffebb14dea013a5983894fb0dd21b9cad9f6fdfe57b5b49a4a70c7d5d7a371b16";
        $crypt .= "f9526cf3cebcef863dbffd3f89dadc418e3d032e731e70a77eee20359865ab60b5303d5dd275553968ef5711541ab00df1c";
        $crypt .= "cc2fb44212ea630682c032183f54050ddf5e68f4a768876464f543a4b1719eda6dad8fea96011938b50ff318b804ff7f9e9";
        $crypt .= "7b909d104afc04daa2add570b3f7356f40db80029be49451504fe7b32e1f18b988bf426f98e8b58e925691cef817c7f58af";
        $crypt .= "3fefd0707f7acff1c14e260a7fe7e60cf157f7becde9d6dc23c62cea96f56795d0cd8743cd5398f5a7b05294f6b2b6e32";
        $crypt .= "a178066aa08523319325ceb2e61b830a4ad34c1b65bcffb03d0cf293c1115de933159b1d1a69b220dbbfe9aab49c1366904";
        $crypt .= "7b893893eea229eccebef511fe7bf45f4be3ab6f8d10a5d0a0b81669c60a49eaf79129a57e1b702a0866d150155c77a8a2";
        $crypt .= "49245e78e";

        $configMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock
            ->expects($this->once())
            ->method('getFormEncryptedPassword')
            ->willReturn('4BMxx5kDvDshzS6Q');

        $formCryptObject = new \Ebizmarts\SagePaySuite\Model\FormCrypt();

        /** @var \Ebizmarts\SagePaySuite\Model\Form $formModelMock */
        $formModelMock = $this
            ->objectManagerHelper
            ->getObject(
                '\Ebizmarts\SagePaySuite\Model\Form',
                [
                    "config"    => $configMock,
                    "formCrypt" => $formCryptObject
                ]
            );

        $response = $formModelMock->decodeSagePayResponse($crypt);

        $this->assertEquals('000000034-2016-10-11-1900471476212447', $response['VendorTxCode']);
        $this->assertEquals('{20CBE649-B3A3-9A95-0A57-4CB9E2EDAC19}', $response['VPSTxId']);
        $this->assertEquals('OK', $response['Status']);
        $this->assertEquals('0000 : The Authorisation was Successful.', $response['StatusDetail']);
        $this->assertEquals('12745378', $response['TxAuthNo']);
        $this->assertEquals('SECURITY CODE MATCH ONLY', $response['AVSCV2']);
        $this->assertEquals('NOTMATCHED', $response['AddressResult']);
        $this->assertEquals('NOTMATCHED', $response['PostCodeResult']);
        $this->assertEquals('MATCHED', $response['CV2Result']);
        $this->assertEquals('0', $response['GiftAid']);
        $this->assertEquals('OK', $response['3DSecureStatus']);
        $this->assertEquals('AAABARR5kwAAAAAAAAAAAAAAAAA', $response['CAVV']);
        $this->assertEquals('MC', $response['CardType']);
        $this->assertEquals('0001', $response['Last4Digits']);
        $this->assertEquals('00', $response['DeclineCode']);
        $this->assertEquals('0120', $response['ExpiryDate']);
        $this->assertEquals('214.00', $response['Amount']);
        $this->assertEquals('999778', $response['BankAuthCode']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid encryption string
     */
    public function testDecodeSagePayInvalidResponse()
    {
        $crypt = "77a9f5fb9cbrtyfc11c6f3d5d6b424c7e840ad2573a3dcab681978e33a10202f0483177475ac5a76752c8b10a736d13fe83";

        $configMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock
            ->expects($this->once())
            ->method('getFormEncryptedPassword')
            ->willReturn('4BMxx5kDvDshzS6Q');

        $formCryptObject = new \Ebizmarts\SagePaySuite\Model\FormCrypt();

        /** @var \Ebizmarts\SagePaySuite\Model\Form $formModelMock */
        $formModelMock = $this
            ->objectManagerHelper
            ->getObject(
                '\Ebizmarts\SagePaySuite\Model\Form',
                [
                    "config" => $configMock,
                    "formCrypt" => $formCryptObject
                ]
            );

        $formModelMock->decodeSagePayResponse($crypt);
    }

    public function testGetCode()
    {
        $this->assertEquals('sagepaysuiteform', $this->formModelObject->getCode());
    }

    public function testGetInfoBlockType()
    {
        $this->assertEquals('Ebizmarts\SagePaySuite\Block\Info', $this->formModelObject->getInfoBlockType());
    }

    public function testIsGateway()
    {
        $this->assertTrue($this->formModelObject->isGateway());
    }

    public function testCanOrder()
    {
        $this->assertTrue($this->formModelObject->canOrder());
    }

    public function testCanAuthorize()
    {
        $this->assertTrue($this->formModelObject->canAuthorize());
    }

    public function testCanCapture()
    {
        $this->assertTrue($this->formModelObject->canCapture());
    }

    public function testCanCapturePartial()
    {
        $this->assertTrue($this->formModelObject->canCapturePartial());
    }

    public function testCanRefund()
    {
        $this->assertTrue($this->formModelObject->canRefund());
    }

    public function testCanRefundPartialPerInvoice()
    {
        $this->assertTrue($this->formModelObject->canRefundPartialPerInvoice());
    }

    public function testCanUseCheckout()
    {
        $this->assertTrue($this->formModelObject->canUseCheckout());
    }

    public function testCanFetchTransactionInfo()
    {
        $this->assertTrue($this->formModelObject->canFetchTransactionInfo());
    }

    public function testCanReviewPayment()
    {
        $this->assertTrue($this->formModelObject->canReviewPayment());
    }

    public function testmarkAsInitialized()
    {
        $this->assertTrue($this->formModelObject->isInitializeNeeded());
        $this->formModelObject->markAsInitialized();
        $this->assertFalse($this->formModelObject->isInitializeNeeded());
    }

    public function testIsInitializedNeeded()
    {
        $this->assertTrue($this->formModelObject->isInitializeNeeded());
    }

    public function testCanUseInternal()
    {
        $configMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())->method('setMethodCode')->with('sagepaysuiteform')->willReturnSelf();
        $configMock->expects($this->once())->method('isMethodActiveMoto')->willReturn(1);

        $form = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\Form',
            [
                'config' => $configMock,
            ]
        );

        $this->assertTrue($form->canUseInternal());
    }

    public function testIsActive()
    {
        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->any())->method('getValue')
            ->with('payment/sagepaysuiteform/active')
            ->willReturn(1);

        $appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getAppState')->willReturn($appStateMock);

        $form = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\Form',
            [
                'context'     => $contextMock,
                'scopeConfig' => $scopeConfigMock
            ]
        );

        $this->assertTrue($form->isActive());
    }

    public function testIsActiveMoto()
    {
        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->any())->method('getValue')
            ->with('payment/sagepaysuiteform/active_moto')
            ->willReturn(1);

        $appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('adminhtml');

        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getAppState')->willReturn($appStateMock);

        $form = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\Form',
            [
                'context'     => $contextMock,
                'scopeConfig' => $scopeConfigMock
            ]
        );

        $this->assertTrue($form->isActive());
    }

    /**
     * @dataProvider magentoPaymentActionProvider
     * @param string $paymentAction
     */
    public function testInitialize($paymentAction)
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['setCanSendNewEmailFlag'])
            ->disableOriginalConstructor()->getMock();
        $orderMock->expects($this->once())->method('setCanSendNewEmailFlag')->with(false);

        $infoInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->setMethods(
                [
                    'getOrder',
                    'getLastTransId',
                    'encrypt',
                    'decrypt',
                    'setAdditionalInformation',
                    'getMethodInstance',
                    'hasAdditionalInformation',
                    'getAdditionalInformation',
                    'unsAdditionalInformation'
                ]
            )
            ->disableOriginalConstructor()->getMock();
        $infoInstanceMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $paymentOperationsMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentOperationsMock->expects($this->once())->method('setOrderStateAndStatus');

        $formModel = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\Form',
            ["paymentOps" => $paymentOperationsMock]
        );

        $formModel->setInfoInstance($infoInstanceMock);

        $stateObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['setIsNotified'])
            ->disableOriginalConstructor()
            ->getMock();
        $stateObjectMock->expects($this->once())->method('setIsNotified')->with(false);

        $formModel->initialize($paymentAction, $stateObjectMock);
    }

    public function magentoPaymentActionProvider()
    {
        return [['PAYMENT'], ['DEFERRED'], ['AUTHENTICATE']];
    }

    public function testCanVoid()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getState'])
            ->disableOriginalConstructor()->getMock();
        $orderMock->expects($this->once())->method('getState')->willReturn('pending_payment');

        $infoInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->setMethods(
                [
                    'getOrder',
                    'encrypt',
                    'decrypt',
                    'setAdditionalInformation',
                    'getMethodInstance',
                    'hasAdditionalInformation',
                    'getAdditionalInformation',
                    'unsAdditionalInformation'
                ]
            )
            ->disableOriginalConstructor()->getMock();
        $infoInstanceMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $this->formModelObject->setInfoInstance($infoInstanceMock);

        $this->assertFalse($this->formModelObject->canVoid());
    }

    public function testCanVoidYes()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getState'])
            ->disableOriginalConstructor()->getMock();
        $orderMock->expects($this->once())->method('getState')->willReturn('processing');

        $infoInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->setMethods(
                [
                    'getOrder',
                    'encrypt',
                    'decrypt',
                    'setAdditionalInformation',
                    'getMethodInstance',
                    'hasAdditionalInformation',
                    'getAdditionalInformation',
                    'unsAdditionalInformation'
                ]
            )
            ->disableOriginalConstructor()->getMock();
        $infoInstanceMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $this->formModelObject->setInfoInstance($infoInstanceMock);

        $this->assertTrue($this->formModelObject->canVoid());
    }

    public function testGetConfigPaymentAction()
    {
        $configMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->once())->method('getPaymentAction')->willReturn('authorize_capture');

        $formModelMock = $this->objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Model\Form',
            [
                'config' => $configMock,
            ]
        );

        $this->assertEquals('authorize_capture', $formModelMock->getConfigPaymentAction());
    }

    public function testRefund()
    {
        $formModel = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Form::class)
            ->setConstructorArgs(
                [
                    'context' => $this->createMock(\Magento\Framework\Model\Context::class),
                    'registry' => $this->createMock(\Magento\Framework\Registry::class),
                    'extensionFactory' => $this->createMock('\Magento\Framework\Api\ExtensionAttributesFactory'),
                    'customAttributeFactory' => $this->createMock('\Magento\Framework\Api\AttributeValueFactory'),
                    'formCrypt' => $this->createMock('\Ebizmarts\SagePaySuite\Model\FormCrypt'),
                    'paymentOps' => $this->createMock(\Ebizmarts\SagePaySuite\Model\Payment::class),
                    'paymentData' => $this->createMock(\Magento\Payment\Helper\Data::class),
                    'scopeConfig' => $this->createMock('\Magento\Framework\App\Config\ScopeConfigInterface'),
                    'logger' => $this->createMock(\Magento\Payment\Model\Method\Logger::class),
                    'config' => $this->createMock(\Ebizmarts\SagePaySuite\Model\Config::class),
                    'resource' => null,
                    'resourceCollection' => null,
                    'data' => [],
                ]
            )
            ->setMethodsExcept(['refund'])
            ->getMock();

        $paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);

        $refundResult = $formModel->refund($paymentMock, 48.67);

        $this->assertInstanceOf(\Ebizmarts\SagePaySuite\Model\Form::class, $refundResult);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error refunding Sage Pay transaction
     */
    public function testRefundException()
    {
        $paymentOpsMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Payment::class)
        ->disableOriginalConstructor()
        ->getMock();

        $formModel = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Form::class)
            ->setConstructorArgs(
                [
                    'context' => $this->createMock(\Magento\Framework\Model\Context::class),
                    'registry' => $this->createMock(\Magento\Framework\Registry::class),
                    'extensionFactory' => $this->createMock('\Magento\Framework\Api\ExtensionAttributesFactory'),
                    'customAttributeFactory' => $this->createMock('\Magento\Framework\Api\AttributeValueFactory'),
                    'formCrypt' => $this->createMock('\Ebizmarts\SagePaySuite\Model\FormCrypt'),
                    'paymentOps' => $paymentOpsMock,
                    'paymentData' => $this->createMock(\Magento\Payment\Helper\Data::class),
                    'scopeConfig' => $this->createMock('\Magento\Framework\App\Config\ScopeConfigInterface'),
                    'logger' => $this->createMock(\Magento\Payment\Model\Method\Logger::class),
                    'config' => $this->createMock(\Ebizmarts\SagePaySuite\Model\Config::class),
                    'resource' => null,
                    'resourceCollection' => null,
                    'data' => [],
                ]
            )
            ->setMethodsExcept(['refund'])
            ->getMock();

        $paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);

        $paymentOpsMock->expects($this->once())->method('refund')->with($paymentMock, 48.67)
            ->willThrowException(
                new LocalizedException(__('There was an error refunding Sage Pay transaction '))
            );

        $formModel->refund($paymentMock, 48.67);
    }
}
