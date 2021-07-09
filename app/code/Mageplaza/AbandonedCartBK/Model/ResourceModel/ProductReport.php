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

namespace Mageplaza\AbandonedCart\Model\ResourceModel;

use Exception;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;
use Magento\Store\Model\Store;
use Zend_Db_Expr;

/**
 * Class AbandonedCarts
 * @package Mageplaza\AbandonedCart\Model\ResourceModel
 */
class ProductReport extends AbstractReport
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_abandonedcart_product_reports_index', 'id');
    }

    /**
     * @param null $from
     * @param null $to
     *
     * @return $this
     * @throws Exception
     */
    public function aggregate($from = null, $to = null)
    {
        $connection = $this->getConnection();
        //$this->getConnection()->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('quote'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['source_table' => $this->getTable('quote')],
                    'source_table.created_at',
                    $from,
                    $to
                )
            );
            $select     = $connection->select();

            $select->group([$periodExpr, 'source_table.store_id', 'quote_item.product_id']);

            $columns = [
                'period'            => $periodExpr,
                'store_id'          => 'source_table.store_id',
                'product_id'        => 'quote_item.product_id',
                'sku'               => 'quote_item.sku',
                'product_name'      => new Zend_Db_Expr('MIN(quote_item.name)'),
                'price'             => new Zend_Db_Expr(
                    'MIN(IF(quote_item_parent.base_price,quote_item_parent.base_price, quote_item.base_price))' .
                    '* MIN(source_table.base_to_global_rate)'
                ),
                'abandoned_time'    => new Zend_Db_Expr(
                    'COUNT(quote_item.product_id)'
                ),
                'qty'               => new Zend_Db_Expr('SUM(IF(quote_item.parent_item_id IS NULL,' .
                    'quote_item.qty,quote_item_parent.qty))'),
                'abandoned_revenue' => new Zend_Db_Expr(
                    'SUM(IF(quote_item.parent_item_id IS NULL,quote_item.row_total,quote_item_parent.row_total))'
                ),

                'customer_group_id' => 'source_table.customer_group_id',
            ];

            $select->from(
                ['source_table' => $this->getTable('quote')],
                $columns
            )->where(
                'source_table.is_active = 1'
            )->joinInner(
                ['quote_item' => $this->getTable('quote_item')],
                'quote_item.quote_id = source_table.entity_id',
                []
            )->joinLeft(
                ['quote_item_parent' => $this->getTable('quote_item')],
                'quote_item.parent_item_id = quote_item_parent.item_id',
                []
            )->where(
                ' quote_item.product_type NOT IN(?)',
                [
                    Type::TYPE_BUNDLE       => Type::TYPE_BUNDLE,
                    Grouped::TYPE_CODE      => Grouped::TYPE_CODE,
                    Configurable::TYPE_CODE => Configurable::TYPE_CODE
                ]
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->useStraightJoin();

            // important!
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
            $columns = [
                'period'            => 'period',
                'store_id'          => new Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                'product_id'        => 'product_id',
                'sku'               => 'sku',
                'product_name'      => new Zend_Db_Expr('MAX(product_name)'),
                'price'             => new Zend_Db_Expr('MAX(price)'),
                'abandoned_time'    => new Zend_Db_Expr('SUM(abandoned_time)'),
                'qty'               => new Zend_Db_Expr('SUM(qty)'),
                'abandoned_revenue' => new Zend_Db_Expr('SUM(abandoned_revenue)'),
                'customer_group_id' => 'customer_group_id'
            ];

            $select->reset();
            $select->from(
                $this->getMainTable(),
                $columns
            )->where(
                'store_id <> ?',
                Store::DEFAULT_STORE_ID
            );

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'customer_group_id', 'product_id']);
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }
}
