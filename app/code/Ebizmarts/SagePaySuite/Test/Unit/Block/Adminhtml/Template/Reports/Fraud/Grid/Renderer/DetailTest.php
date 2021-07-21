<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DetailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Detail
     */
    private $detailRendererBlock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $columnMock = $this
            ->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->detailRendererBlock = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Detail',
            [
                'context' => $this->makeContextMock(),
                'information' => $this->makeAdditionalInformation(),
                []
            ]
        );

        $this->detailRendererBlock->setColumn($columnMock);
    }
    // @codingStandardsIgnoreEnd

    public function testRender()
    {
        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->willReturn('{"fraudcodedetail":"detail"}');

        $this->assertEquals(
            'detail',
            $this->detailRendererBlock->render($rowMock)
        );
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
