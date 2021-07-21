<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

/**
 * grid block action item renderer
 */
class TransactionId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{

    /**
     * Render grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $trnId = parent::render($row);

        $link = $this->getUrl('sales/transactions/view/', ['txn_id'=>$trnId]);

        return '<a href="' . $link . '">' . $trnId . '</a>';
    }
}
