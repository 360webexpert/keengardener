<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Paypal;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use stdClass;

class PaypalTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
    }

    public function testBlockExists()
    {
        $paypalProcessingBlock = $this->objectManagerHelper->getObject(
            \Ebizmarts\SagePaySuite\Block\Paypal\Processing::class
        );

        $this->assertInstanceOf("\Ebizmarts\SagePaySuite\Block\Paypal\Processing", $paypalProcessingBlock);
    }

    public function testHtmlOk()
    {
        /** @var $paypalProcessingBlock \Ebizmarts\SagePaySuite\Block\Paypal\Processing $paypalProcessingBlock|\PHPUnit_Framework_MockObject_MockObject */
        $paypalProcessingBlock = $this->getMockBuilder(
            \Ebizmarts\SagePaySuite\Block\Paypal\Processing::class
        )
            ->setMethods(['getViewFileUrl', 'getUrl', 'getRequest', 'getData'])
        ->disableOriginalConstructor()
        ->getMock();

        $postData = new stdClass;
        $postData->Status = 'PPREDIRECT';

        $paypalProcessingBlock->expects($this->once())->method('getData')->with('paypal_post')
        ->willReturn($postData);

        $paypalProcessingBlock->expects($this->exactly(2))->method('getViewFileUrl')
            ->withConsecutive(
                ['Ebizmarts_SagePaySuite::images/paypal_checkout.png'],
                ['Ebizmarts_SagePaySuite::images/ajax-loader.gif']
            );

        $paypalProcessingBlock->expects($this->once())->method('getUrl')
            ->with('sagepaysuite/paypal/callback', ['_secure' => true]);

        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->once())->method('getParam')->with('quoteid')->willReturn(
            '0:2:Dwn8kCUk6nZU5B7b0Xn26uYQDeLUKBrD:S72utt9n585GrslZpDp+DRpW+8dpqiu/EiCHXwfEhS0='
        );

        $paypalProcessingBlock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $htmlResult = $paypalProcessingBlock->paypalHtml();

        $this->assertStringStartsWith('<html', $htmlResult);
    }

    public function testHtmlError()
    {
        /** @var $paypalProcessingBlock \Ebizmarts\SagePaySuite\Block\Paypal\Processing $paypalProcessingBlock|\PHPUnit_Framework_MockObject_MockObject */
        $paypalProcessingBlock = $this->getMockBuilder(
            \Ebizmarts\SagePaySuite\Block\Paypal\Processing::class
        )
            ->setMethods(['getViewFileUrl', 'getUrl', 'getRequest', 'getData'])
        ->disableOriginalConstructor()
        ->getMock();

        $paypalProcessingBlock->expects($this->once())->method('getData')->with('paypal_post')
        ->willReturn([]);

        $paypalProcessingBlock->expects($this->once())->method('getViewFileUrl')
            ->with('Ebizmarts_SagePaySuite::images/paypal_checkout.png');

        $paypalProcessingBlock->expects($this->once())->method('getUrl')
            ->with('sagepaysuite/paypal/callback', ['_secure' => true]);

        $htmlResult = $paypalProcessingBlock->paypalHtml();

        $this->assertContains('ERROR: Invalid response from PayPal', $htmlResult);
    }
}
