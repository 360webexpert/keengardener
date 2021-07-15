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

namespace Mageplaza\AbandonedCart\Model\ResourceModel\Grid\AbandonedCarts;

use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\AbstractCollection;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\AbandonedCart\Model\ResourceModel\Grid\AbandonedCarts
 */
class Collection extends AbstractCollection
{
    /**
     * Retrieve selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $period     = $this->getPeriod();
        $connection = $this->getConnection();
        if (!$this->_selectedColumns) {
            $this->_selectedColumns = [
                'period_time'                => sprintf('%s', $connection->getDateFormatSql('period', '%Y-%m-%d')),
                'cart_abandon_rate'          => new Zend_Db_Expr('ROUND(SUM(total_abandoned_carts)*100
                /(SUM(total_abandoned_carts)+SUM(number_of_successful_carts)),2)'),
                'successful_cart_rate'       => new Zend_Db_Expr('ROUND(SUM(number_of_successful_carts)*100
                /(SUM(total_abandoned_carts)+SUM(number_of_successful_carts)),2)'),
                'total_abandoned_carts'      => new Zend_Db_Expr('SUM(total_abandoned_carts)'),
                'total_abandoned_revenue'    => new Zend_Db_Expr('SUM(total_abandoned_revenue)'),
                'number_of_successful_carts' => new Zend_Db_Expr('SUM(number_of_successful_carts)'),
                'successful_carts_revenue'   => new Zend_Db_Expr('SUM(successful_carts_revenue)'),
                'actionable_abandoned_carts' => new Zend_Db_Expr('SUM(actionable_abandoned_carts)'),
                'recapturable_revenue'       => new Zend_Db_Expr('SUM(recapturable_revenue)'),
                'recaptured_revenue'         => new Zend_Db_Expr('SUM(recaptured_revenue)'),
                'recaptured_rate'            => new Zend_Db_Expr('ROUND(SUM(total_cart_checkout_sent)*100
                /SUM(total_email_abandoned_sent),2)'),
                'total_email_abandoned_sent' => new Zend_Db_Expr('SUM(total_email_abandoned_sent)'),
                'total_cart_checkout_sent'   => new Zend_Db_Expr('SUM(total_cart_checkout_sent)'),
            ];
            if ($period === 'year') {
                $this->_selectedColumns['period_time'] = sprintf('%s', $connection->getDateFormatSql('period', '%Y'));
            } elseif ($period === 'month') {
                $this->_selectedColumns['period_time'] = sprintf(
                    '%s',
                    $connection->getDateFormatSql('period', '%Y-%m')
                );
            } elseif ($period === 'week') {
                $this->_selectedColumns['period_time'] = sprintf(
                    '%s',
                    $connection->getDateFormatSql('period', '%Y-%u')
                );
            }
        }

        return $this->_selectedColumns;
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    public function _applyAggregatedTable()
    {
        $select = $this->getSelect();
        $select->group('period_time');

        return $this;
    }
}
