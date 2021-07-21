<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RecommendationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $data
     * @param $color
     * @param $recommendation
     * @dataProvider dataProvider
     */
    public function testRender($data, $color, $recommendation)
    {
        $columnMock = $this
            ->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $recommendationRendererBlock = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Recommendation',
            [
                'context' => $this->makeContextMock(),
                'information' => $this->makeAdditionalInformation(),
                []
            ]
        );

        $recommendationRendererBlock->setColumn($columnMock);

        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->with('additional_information')
            ->willReturn($data);

        $this->assertEquals(
            "<span style=\"color:{$color};\">{$recommendation}</span>",
            $recommendationRendererBlock->render($rowMock)
        );
    }

    public function dataProvider()
    {
        return [
            [
                'data'  => '{"fraudscreenrecommendation":"REJECT"}',
                'color' => 'red',
                'recommendation' => 'REJECT',
            ],
            [
                'data'  => '{"fraudscreenrecommendation":"DENY"}',
                'color' => 'red',
                'recommendation' => 'DENY',
            ],
            [
                'data'  => '{"fraudscreenrecommendation":"CHALLENGE"}',
                'color' => 'orange',
                'recommendation' => 'CHALLENGE',
            ],
            [
                'data'  => '{"fraudscreenrecommendation":"HOLD"}',
                'color' => 'orange',
                'recommendation' => 'HOLD',
            ]
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock()
    {
        $contextMock = $this->getMockBuilder('\Magento\Backend\Block\Context')->disableOriginalConstructor()->getMock();

        return $contextMock;
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
}
