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

use DateTime;
use Exception;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;
use Magento\Store\Model\Store;
use Zend_Db_Expr;

/**
 * Class AbandonedCarts
 * @package Mageplaza\AbandonedCart\Model\ResourceModel
 */
class AbandonedCarts extends AbstractReport
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_abandonedcart_reports_index', 'id');
    }

    /**
     * @param DateTime|null $from
     * @param null $to
     *
     * @return $this
     * @throws Exception
     */
    public function aggregate($from = null, $to = null)
    {
        $connection = $this->getConnection();
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $connection->select()->from(
                    ['rt' => $this->getTable('quote')],
                    $connection->getDatePartSql(
                        $this->getStoreTZOffsetQuery(
                            ['rt' => 'quote'],
                            'rt.created_at',
                            $from,
                            $to
                        )
                    )
                )->distinct(
                    true
                )->joinLeft(
                    ['logs' => $this->getTable('mageplaza_abandonedcart_logs')],
                    'rt.entity_id = logs.quote_id',
                    []
                )->where(
                    'IF(logs.quote_id IS NULL , rt.updated_at, GREATEST(logs.updated_at,  rt.updated_at))
                     >= \'' . $from->format('Y-m-d H:i:s') . '\''
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

            $select->group([$periodExpr, 'source_table.store_id', 'source_table.customer_group_id']);

            $columns = [
                'period'                     => $periodExpr,
                'store_id'                   => 'source_table.store_id',
                'number_of_successful_carts' => new Zend_Db_Expr(
                    'COUNT(CASE WHEN source_table.is_active = 0 THEN source_table.is_active END)'
                ),
                'total_abandoned_carts'      => new Zend_Db_Expr(
                    'COUNT(CASE WHEN source_table.is_active = 1 THEN source_table.is_active END)'
                ),
                'successful_carts_revenue'   => new Zend_Db_Expr(
                    'SUM(CASE WHEN source_table.is_active = 0 THEN source_table.grand_total END)'
                ),
                'actionable_abandoned_carts' => new Zend_Db_Expr(
                    'COUNT(CASE WHEN source_table.is_active = 1 AND source_table.customer_email IS NOT NULL
                     THEN source_table.is_active END)'
                ),
                'total_abandoned_revenue'    => new Zend_Db_Expr(
                    'SUM(CASE WHEN source_table.is_active = 1 THEN source_table.grand_total END)'
                ),
                'total_email_abandoned_sent' => new Zend_Db_Expr(
                    'COUNT(CASE WHEN source_table.entity_id = lt.quote_id AND lt.status != 0 
                    THEN source_table.grand_total END)'
                ),
                'recapturable_revenue'       => new Zend_Db_Expr(
                    'SUM(CASE WHEN source_table.is_active = 1 AND source_table.entity_id = lt.quote_id
                     AND lt.status != 0 THEN source_table.grand_total END)'
                ),
                'recaptured_revenue'         => new Zend_Db_Expr(
                    'SUM(CASE WHEN source_table.is_active = 0 AND source_table.entity_id = lt.quote_id 
                    AND lt.recovery = 1 THEN source_table.grand_total END)'
                ),
                'total_cart_checkout_sent'   => new Zend_Db_Expr(
                    'COUNT(CASE WHEN source_table.entity_id = lt.quote_id AND source_table.is_active = 0 
                    AND lt.status != 0 THEN source_table.grand_total END)'
                ),

                'customer_group_id' => 'MAX(source_table.customer_group_id)',
            ];

            $select->from(
                ['source_table' => $this->getTable('quote')],
                $columns
            )->joinLeft(
                ['lt' => $this->getTable('mageplaza_abandonedcart_logs')],
                'source_table.entity_id = lt.quote_id',
                []
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->useStraightJoin();
            // important!
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
            $columns = [
                'period'                     => 'period',
                'store_id'                   => new Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                'number_of_successful_carts' => new Zend_Db_Expr('SUM(number_of_successful_carts)'),
                'total_abandoned_carts'      => new Zend_Db_Expr('SUM(total_abandoned_carts)'),
                'successful_carts_revenue'   => new Zend_Db_Expr('SUM(successful_carts_revenue)'),
                'actionable_abandoned_carts' => new Zend_Db_Expr('SUM(actionable_abandoned_carts)'),
                'total_abandoned_revenue'    => new Zend_Db_Expr('SUM(total_abandoned_revenue)'),
                'total_email_abandoned_sent' => new Zend_Db_Expr('SUM(total_email_abandoned_sent)'),
                'recapturable_revenue'       => new Zend_Db_Expr('SUM(recapturable_revenue)'),
                'recaptured_revenue'         => new Zend_Db_Expr('SUM(recaptured_revenue)'),
                'total_cart_checkout_sent'   => new Zend_Db_Expr('SUM(total_cart_checkout_sent)'),
                'customer_group_id'          => 'MAX(customer_group_id)'
            ];

            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where('store_id <> ?', Store::DEFAULT_STORE_ID);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'customer_group_id']);
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }
}
