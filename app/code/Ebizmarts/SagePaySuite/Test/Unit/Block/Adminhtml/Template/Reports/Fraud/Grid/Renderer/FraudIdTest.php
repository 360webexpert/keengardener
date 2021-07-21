<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FraudIdTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $objectManagerHelper = new ObjectManager($this);
        $fraudIdRendererBlock = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\FraudId',
            [
                'context' => $this->makeContextMock(),
                'information' => $this->makeAdditionalInformation(),
                []
            ]
        );

        $fraudIdRendererBlock->setColumn($this->makeColumnMock());

        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->willReturn('{"fraudid":"12345"}');

        $this->assertEquals(
            '12345',
            $fraudIdRendererBlock->render($rowMock)
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock()
    {
        $contextMock = $this->getMockBuilder('\Magento\Backend\Block\Context')->disableOriginalConstructor()->getMock();

        return $contextMock;
    }
}
