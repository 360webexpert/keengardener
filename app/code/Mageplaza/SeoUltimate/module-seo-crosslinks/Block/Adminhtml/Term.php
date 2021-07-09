<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Term
 * @package Mageplaza\SeoCrosslinks\Block\Adminhtml
 */
class Term extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'adminhtml_term';
        $this->_blockGroup     = 'Mageplaza_SeoCrosslinks';
        $this->_headerText     = __('Terms');
        $this->_addButtonLabel = __('Create New Term');
        parent::_construct();
    }
}
