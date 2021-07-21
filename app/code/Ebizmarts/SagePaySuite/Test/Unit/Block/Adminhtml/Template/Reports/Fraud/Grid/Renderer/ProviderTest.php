<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Provider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProviderTest extends \PHPUnit\Framework\TestCase
{

    public function testRender()
    {
        $blockMock = $this
            ->getMockBuilder(Provider::class)
            ->setMethods(['getViewFileUrl'])
            ->setConstructorArgs(
                [
                    'context' => $this->makeContextMock(),
                    'information' => $this->makeAdditionalInformation(),
                    'data' => []
                ]
            )
            ->getMock();

        $blockMock->setColumn($this->makeColumnMock());

        $blockMock
            ->expects($this->once())
            ->method('getViewFileUrl')
            ->with('Ebizmarts_SagePaySuite::images/red_logo.png')
            ->willReturn('Ebizmarts_SagePaySuite/images/red_logo.png');

        $rowMock = $this->makeRowMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->willReturn('{"fraudprovidername":"ReD"}');

        $this->assertEquals(
            '<img style="height: 20px;" src="Ebizmarts_SagePaySuite/images/red_logo.png">',
            $blockMock->render($rowMock)
        );
    }

    public function testRenderT3M()
    {
        $blockMock = $this
            ->getMockBuilder(Provider::class)
            ->setMethods(['getViewFileUrl'])
            ->setConstructorArgs(
                [
                    'context' => $this->makeContextMock(),
                    'information' => $this->makeAdditionalInformation(),
                    'data' => []
                ]
            )
            ->getMock();

        $blockMock->setColumn($this->makeColumnMock());

        $blockMock
            ->expects($this->once())
            ->method('getViewFileUrl')
            ->with('Ebizmarts_SagePaySuite::images/t3m_logo.png')
            ->willReturn('Ebizmarts_SagePaySuite/images/t3m_logo.png');

        $rowMock = $this->makeRowMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->willReturn('{"fraudprovidername":"T3M"}');

        $this->assertEquals(
            '<span><img style="height: 20px;vertical-align: text-top;" src="Ebizmarts_SagePaySuite/images/t3m_logo.png"> T3M</span>',
            $blockMock->render($rowMock)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeColumnMock()
    {
        $columnMock = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')->disableOriginalConstructor()->getMock();

        return $columnMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRowMock()
    {
        $rowMock = $this->getMockBuilder('Magento\Framework\DataObject')->disableOriginalConstructor()->getMock();

        return $rowMock;
    }

    private function makeAdditionalInformation()
    {
        $objectManagerHelper = new ObjectManager($this);

        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();

        $loggerMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $objectManagerHelper
            ->getObject(
                \Ebizmarts\SagePaySuite\Helper\AdditionalInformation::class,
                [
                    'serializer' => $serializerMock,
                    'logger' => $loggerMock
                ]
            );
    }

    private function makeContextMock()
    {
        return $this->getMockBuilder('\Magento\Backend\Block\Context')->disableOriginalConstructor()->getMock();
    }
}
