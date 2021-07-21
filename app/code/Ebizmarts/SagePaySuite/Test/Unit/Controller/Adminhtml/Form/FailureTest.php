<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Adminhtml\Form;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FailureTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Ebizmarts\SagePaySuite\Controller\Adminhtml\Form\Failure
     */
    private $formFailureController;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http', [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteSessionMock = $this
            ->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $actionFlagMock = $this
            ->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock = $this
            ->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getBackendUrl')
            ->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($quoteSessionMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($helperMock));

        $formModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $formModelMock->expects($this->any())
            ->method('decodeSagePayResponse')
            ->will($this->returnValue([
                "Status" => "REJECTED",
                "StatusDetail" => "2000 : Invalid Card"
            ]));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->formFailureController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Adminhtml\Form\Failure',
            [
                'context' => $contextMock,
                'formModel' => $formModelMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testExecute()
    {
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with("REJECTED: Invalid Card");

        $this->formFailureController->execute();
    }
}
