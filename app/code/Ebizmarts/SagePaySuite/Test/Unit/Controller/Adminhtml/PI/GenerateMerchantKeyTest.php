<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\PI;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class GenerateMerchantKeyTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $resultJson = $this->makeResultJsonMock();
        $responseMock = $this->makeResponseMock();
        $quoteMock = $this->makeQuoteMock();
        $backendQuoteMock = $this->makeBackendQuoteMock($quoteMock);
        $objectManagerMock = $this->makeObjectManagerMock($backendQuoteMock);
        $contextMock = $this->makeContextMock($responseMock, $resultJson);
        $contextMock->expects($this->once())
            ->method("getObjectManager")
            ->willReturn($objectManagerMock);

        $mskResultMock = $this->makeMskResultMock();
        $mskResultMock
            ->expects($this->once())
            ->method('getSuccess')
            ->willReturn(true);
        $mskResultMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn(['success' => true, 'response' => '12345']);
        $piServiceMock = $this->makeServiceMock($quoteMock, $mskResultMock);

        $piGenerateMerchantKeyController = $this->makePiGenerateMerchantKeyController($contextMock, $piServiceMock);

        $resultJson
            ->expects($this->once())
            ->method('setData')
            ->with([
                "success"  => true,
                'response' => "12345"
            ]);

        $piGenerateMerchantKeyController->execute();
    }

    private function makeResultFactoryMock($resultJson)
    {
        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        return $resultFactoryMock;
    }

    private function makeResultJsonMock()
    {
        return $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testExecuteApiException()
    {
        $resultJson = $this->makeResultJsonMock();
        $responseMock = $this->makeResponseMock();

        $messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with("Something went wrong: Authentication values are missing");

        $quoteMock = $this->makeQuoteMock();
        $backendQuoteMock = $this->makeBackendQuoteMock($quoteMock);
        $objectManagerMock = $this->makeObjectManagerMock($backendQuoteMock);
        $contextMock = $this->makeContextMock($responseMock, $resultJson);
        $contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($messageManagerMock);
        $contextMock->expects($this->once())
            ->method("getObjectManager")
            ->willReturn($objectManagerMock);

        $mskResultMock = $this->makeMskResultMock();
        $mskResultMock
            ->expects($this->once())
            ->method('getSuccess')
            ->willReturn(false);
        $mskResultMock
            ->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn('Authentication values are missing');
        $mskResultMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn(
                [
                    'success'       => false,
                    'error_message' => 'Authentication values are missing'
                ]
            );

        $piServiceMock = $this->makeServiceMock($quoteMock, $mskResultMock);

        $piGenerateMerchantKeyController = $this->makePiGenerateMerchantKeyController($contextMock, $piServiceMock);

        $resultJson
            ->expects($this->once())
            ->method('setData')
            ->with([
                "success"       => false,
                "error_message" => "Authentication values are missing"
            ]);

        $piGenerateMerchantKeyController->execute();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeResponseMock()
    {
        $responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        return $responseMock;
    }

    /**
     * @param $responseMock
     * @param $resultJson
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock($responseMock, $resultJson)
    {
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($this->makeResultFactoryMock($resultJson));

        return $contextMock;
    }

    /**
     * @param $backendQuoteMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeObjectManagerMock($backendQuoteMock)
    {
        $objectManagerMock = $this->getMockBuilder("Magento\Framework\ObjectManager\ObjectManager")->disableOriginalConstructor()->getMock();
        $objectManagerMock->expects($this->once())->method("get")->with("Magento\Backend\Model\Session\Quote")->willReturn($backendQuoteMock);

        return $objectManagerMock;
    }

    /**
     * @param $quoteMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeBackendQuoteMock($quoteMock)
    {
        $backendQuoteMock = $this->getMockBuilder("Magento\Backend\Model\Session\Quote")->disableOriginalConstructor()->getMock();
        $backendQuoteMock->expects($this->once())->method("getQuote")->willReturn($quoteMock);

        return $backendQuoteMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeQuoteMock()
    {
        $quoteMock = $this->getMockBuilder("\Magento\Quote\Model\Quote")->disableOriginalConstructor()->getMock();

        return $quoteMock;
    }

    /**
     * @param $quoteMock
     * @param $mskResultMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeServiceMock($quoteMock, $mskResultMock)
    {
        $piServiceMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\PiMsk::class)->disableOriginalConstructor()->getMock();
        $piServiceMock->expects($this->once())->method('getSessionKey')->with($quoteMock)->willReturn($mskResultMock);

        return $piServiceMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMskResultMock()
    {
        $mskResultMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\Result::class)->disableOriginalConstructor()->getMock();

        return $mskResultMock;
    }

    /**
     * @param $contextMock
     * @param $piServiceMock
     * @return object
     */
    private function makePiGenerateMerchantKeyController($contextMock, $piServiceMock)
    {
        $objectManagerHelper             = new ObjectManagerHelper($this);
        $piGenerateMerchantKeyController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\PI\GenerateMerchantKey',
            [
                'context' => $contextMock,
                'piMsk'   => $piServiceMock
            ]
        );

        return $piGenerateMerchantKeyController;
    }
}
