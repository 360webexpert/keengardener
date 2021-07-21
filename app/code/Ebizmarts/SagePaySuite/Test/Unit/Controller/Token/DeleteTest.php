<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Token;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delete
     */
    private $deleteTokenController;

    /**
     * @var Token|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenModelMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /** @var \Magento\Framework\Message\ManagerInterface */
    private $messageManagerMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->tokenModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Token')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));

        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));

        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJson);

        $contextMock->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($resultFactoryMock));

        $this->tokenModelMock->expects($this->any())
            ->method('loadToken')
            ->will($this->returnValue($this->tokenModelMock));

        $this->tokenModelMock->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteTokenController = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\Token\Delete',
            [
                'context'    => $contextMock,
                'tokenModel' => $this->tokenModelMock
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testExecuteCheckout()
    {
        $this->requestMock->expects($this->at(0))->method('getParam')->with('token_id')->will($this->returnValue('5'));
        $this->requestMock->expects($this->at(1))->method('getParam')->with('token_id')->will($this->returnValue('5'));
        $this->requestMock->expects($this->at(2))->method('getParam')->with('checkout')->will($this->returnValue('1'));

        $this->tokenModelMock->expects($this->once())
            ->method('isOwnedByCustomer')
            ->will($this->returnValue(true));

        $this->_expectResultJson([
            "success" => true,
            'response' => true
        ]);

        $this->deleteTokenController->execute();
    }

    public function testExecuteCustomerAccount()
    {
        $this->requestMock->expects($this->at(0))->method('getParam')->with('token_id')->will($this->returnValue('5'));
        $this->requestMock->expects($this->at(1))->method('getParam')->with('token_id')->will($this->returnValue('5'));

        $this->tokenModelMock->expects($this->once())
            ->method('isOwnedByCustomer')
            ->will($this->returnValue(true));

        $this->_expectRedirect("sagepaysuite/customer/tokens");

        $this->assertEquals(
            $this->deleteTokenController->execute(),
            true
        );
    }

    public function testExecuteFail()
    {
        $this->requestMock->expects($this->at(0))->method('getParam')->with('token_id')->will($this->returnValue('5'));
        $this->requestMock->expects($this->at(1))->method('getParam')->with('token_id')->will($this->returnValue('5'));
        $this->requestMock->expects($this->at(2))->method('getParam')->with('checkout')->will($this->returnValue('1'));

        $this->tokenModelMock->expects($this->once())
            ->method('isOwnedByCustomer')
            ->will($this->returnValue(false));

        $this->_expectResultJson([
            "success" => false,
            'error_message' => "Something went wrong: Unable to delete token: Token is not owned by you"
        ]);

        $this->deleteTokenController->execute();
    }

    public function testNoTokenParam()
    {
        $this->requestMock->expects($this->at(0))->method('getParam')->with('token_id')->willReturn(null);

        $this->_expectRedirect('sagepaysuite/customer/tokens');

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with(
                'Something went wrong: Unable to delete token: Invalid token id.'
            );

        $this->assertFalse($this->deleteTokenController->execute());
    }

    /**
     * @param string $path
     */
    private function _expectRedirect($path)
    {
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
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
}
