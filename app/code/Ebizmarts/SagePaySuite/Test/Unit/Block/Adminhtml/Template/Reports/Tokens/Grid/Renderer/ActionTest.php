<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Tokens\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Tokens\Grid\Renderer\Action
     */
    private $actionRendererBlock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $columnMock = $this
            ->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->actionRendererBlock = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Tokens\Grid\Renderer\Action',
            []
        );

        $this->actionRendererBlock->setColumn($columnMock);
    }
    // @codingStandardsIgnoreEnd

    public function testRender()
    {
        $this->assertEquals(
            "&nbsp;",
            $this->actionRendererBlock->render(new DataObject)
        );
    }
}
