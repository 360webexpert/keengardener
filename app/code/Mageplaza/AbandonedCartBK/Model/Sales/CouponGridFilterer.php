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

namespace Mageplaza\AbandonedCart\Model\Sales;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class CouponGridFilterer
 * @package Mageplaza\AbandonedCart\Model\Sales
 */
class CouponGridFilterer
{
    /**
     * Callback action for cart price rule coupon grid.
     *
     * @param AbstractCollection $collection
     * @param Column $column
     *
     * @return void
     */
    public function filterByGeneratedByAbandonedCart($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex()
            : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value === 'null') {
            $collection->addFieldToFilter($field, ['null' => true]);
        } else {
            $collection->addFieldToFilter($field, ['notnull' => true]);
        }
    }
}
