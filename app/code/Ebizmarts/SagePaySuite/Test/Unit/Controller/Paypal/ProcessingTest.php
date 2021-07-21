<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Paypal;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProcessingTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var /Ebizmarts\SagePaySuite\Controller\Paypal\Processing
     */
    private $paypalProcessingController;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

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
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue((object)[
                "Status" => "OK",
                "Var1" => "1",
                "Var2" => "2",
            ]));

        $blockMock = $this
            ->getMockBuilder('Magento\Framework\View\Element\Template')
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn("processing_block");
        $layoutMock = $this
            ->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->will($this->returnValue($blockMock));
        $viewMock = $this
            ->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));


        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->paypalProcessingController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Paypal\Processing',
            [
                'context' => $contextMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testExecute()
    {
        $this->_expectSetBody(
            'processing_block'
        );

        $this->paypalProcessingController->execute();
    }

    /**
     * @param $body
     */
    private function _expectSetBody($body)
    {
        $this->responseMock->expects($this->atLeastOnce())
            ->method('setBody')
            ->with($body);
    }
}
