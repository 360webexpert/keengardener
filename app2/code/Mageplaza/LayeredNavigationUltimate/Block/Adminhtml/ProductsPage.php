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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class ProductsPage
 * @package Mageplaza\LayeredNavigationUltimate\Block\Adminhtml
 */
class ProductsPage extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_productsPage';/*block grid.php directory*/
        $this->_blockGroup = 'Mageplaza_LayeredNavigationUltimate';
        $this->_headerText = __('Custom Products Pages');
        $this->_addButtonLabel = __('Add New Page');

        parent::_construct();
    }
}
