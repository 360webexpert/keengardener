<?php
declare(strict_types=1);
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Form;

use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FailureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseStatusDataProvider
     */
    public function testExecute($responseData, $expectedErrorMessage)
    {
        $responseMock = $this->makeResponseMock();

        $requestMock = $this->makeRequestMock();

        $redirectMock = $this->makeRedirectMock();

        $messageManagerMock = $this->makeMessageManagerMock();

        $contextMock = $this->makeContextMock($requestMock, $responseMock, $redirectMock, $messageManagerMock);

        $formModelMock = $this->makeFormModelMock();
        $formModelMock->expects($this->any())
            ->method('decodeSagePayResponse')
            ->will($this->returnValue($responseData));

        $recoverCartMock = $this
            ->getMockBuilder(RecoverCart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(true)
            ->willReturnSelf();
        $recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $formFailureController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Form\Failure',
            [
                'context'     => $contextMock,
                'formModel'   => $formModelMock,
                'recoverCart' => $recoverCartMock
            ]
        );

        $messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($expectedErrorMessage);

        $redirectMock
            ->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), "checkout/cart", []);

        $formFailureController->execute();
    }

    public function responseStatusDataProvider()
    {
        return [
            [["Status" => "REJECTED", "StatusDetail" => "2000 : Invalid Card"], 'REJECTED: Invalid Card'],
            [
                [
                    "VendorTxCode" => "2000000220-2018-11-12",
                    "VPSTxId" => "{6B8UXLCZ-32C7-99DB-671F-90E4B13282EB}",
                    "Status" => "REJECTED",
                    "StatusDetail" => "The number of authorisation attempts exceeds the limit.",
                    "GiftAid" => "0",
                    "Amount" => "20.18"
                ],
                'REJECTED: The number of authorisation attempts exceeds the limit.'
            ],
        ];
    }

    public function testExecuteException()
    {
        $responseMock = $this->makeResponseMock();

        $requestMock = $this->makeRequestMock();

        $redirectMock = $this->makeRedirectMock();

        $messageManagerMock = $this->makeMessageManagerMock();

        $contextMock = $this->makeContextMock($requestMock, $responseMock, $redirectMock, $messageManagerMock);

        $formModelMock = $this->makeFormModelMock();
        $formModelMock->expects($this->any())
            ->method('decodeSagePayResponse')
            ->willReturn([]);

        $loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->once())->method('critical')->with(
            new \Magento\Framework\Exception\LocalizedException(__('Invalid response from Opayo'))
        );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $formFailureController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Form\Failure',
            [
                'context'                   => $contextMock,
                'formModel'                 => $formModelMock,
                'logger'                    => $loggerMock,
            ]
        );

        $messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with(__('Invalid response from Opayo'));

        $formFailureController->execute();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeResponseMock(): \PHPUnit_Framework_MockObject_MockObject
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
    private function makeRequestMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $requestMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')->disableOriginalConstructor()->getMock();

        return $requestMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMessageManagerMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')->disableOriginalConstructor()->getMock();

        return $messageManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRedirectMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        return $redirectMock;
    }

    /**
     * @param $requestMock
     * @param $responseMock
     * @param $redirectMock
     * @param $messageManagerMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock(
        $requestMock,
        $responseMock,
        $redirectMock,
        $messageManagerMock
    ): \PHPUnit_Framework_MockObject_MockObject {
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($responseMock));
        $contextMock->expects($this->any())->method('getRedirect')->will($this->returnValue($redirectMock));
        $contextMock->expects($this->any())->method('getMessageManager')->will($this->returnValue($messageManagerMock));

        return $contextMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeFormModelMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $formModelMock = $this->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')->disableOriginalConstructor()->getMock();

        return $formModelMock;
    }
}
