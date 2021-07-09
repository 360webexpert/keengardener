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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Logs
 * @package Mageplaza\AbandonedCart\Block\Adminhtml
 */
class Logs extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('add');
        $this->addButton(
            'clear',
            [
                'label'   => __('Clear Logs'),
                'onclick' => 'confirmSetLocation(\'' . __('Are you sure you want to clear all logs?') . '\', \'' . $this->getUrl('abandonedcart/index/clear') . '\')',
                'class'   => 'clear primary'
            ]
        );
    }
}
