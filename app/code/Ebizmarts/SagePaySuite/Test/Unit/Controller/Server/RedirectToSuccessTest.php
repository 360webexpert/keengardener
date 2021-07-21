<?php
/**
 * Copyright © 2020 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Server;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface as Logger;
use Ebizmarts\SagePaySuite\Controller\Server\RedirectToSuccess;

class RedirectToSuccessTest extends \PHPUnit\Framework\TestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $messageManagerMock;

    /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject */
    private $responseMock;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilderMock;

    /** @var RedirectToSuccess */
    private $redirectToSuccessController;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(HttpResponse::class)->disableOriginalConstructor()->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()->getMock();
        $this->messageManagerMock = $this->getMockBuilder(Manager::class)->setMethods(['addError'])->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $storeId = 1;
        $encryptedQuoteId = '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0=';

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getUrl')->willReturn($this->urlBuilderMock);

        $this->requestMock->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['_store'], ['quoteid'])
            ->willReturnOnConsecutiveCalls($storeId, $encryptedQuoteId);

        $this->_expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('*/*/success', ['_secure' => true, '_store' => $storeId]). '?quoteid=' .  urlencode($encryptedQuoteId)
            . '";</script>'
        );

        $this->redirectToSuccessController = new RedirectToSuccess(
            $this->contextMock,
            $this->loggerMock
        );

        $this->redirectToSuccessController->execute();
    }

    /**
     * @param $body
     */
    private function _expectSetBody($body)
    {
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($body);
    }
}
