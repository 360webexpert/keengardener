<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid;

/**
 * Sage Pay tokens reports grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Container extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Prepare grid container, add additional buttons
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_blockGroup = 'Ebizmarts_SagePaySuite';
        $this->_controller = 'adminhtml_reports_fraud';
        $this->_headerText = __('Opayo Fraud Report');
        parent::_construct();
        $this->buttonList->remove('add');
    }
    // @codingStandardsIgnoreEnd
}
