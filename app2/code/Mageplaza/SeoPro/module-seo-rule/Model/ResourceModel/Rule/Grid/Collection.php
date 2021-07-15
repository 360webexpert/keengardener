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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\ResourceModel\Rule\Grid;

use Mageplaza\SeoRule\Model\ResourceModel\Rule;

/**
 * Class Collection
 * @package Mageplaza\SeoRule\Model\ResourceModel\Rule\Grid
 */
class Collection extends Rule\Collection
{
    /**
     * @return $this|Rule\Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        return $this;
    }
}
