<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Tokens\Grid\Renderer;

use Magento\Framework\DataObject;

/**
 * grid block action item renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Render grid column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions = [];

        $actions[] = [
            'url' => $this->getUrl('*/*/delete', ['id' => $row->getId()]),
            'caption' => __('Delete'),
        ];

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
