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

namespace Mageplaza\LayeredNavigationUltimate\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ProductsPage
 * @package Mageplaza\LayeredNavigationUltimate\Model
 */
class ProductsPage extends AbstractModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Mageplaza\LayeredNavigationUltimate\Model\ResourceModel\ProductsPage');
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getData('enable');
    }
}
