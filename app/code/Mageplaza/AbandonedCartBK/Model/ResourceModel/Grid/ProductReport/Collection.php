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

namespace Mageplaza\AbandonedCart\Model\ResourceModel\Grid\ProductReport;

use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\AbstractCollection;
use Zend_Db_Expr;

/**
 * Class Collection
 * @method string getPeriod()
 * @method string getAbandonedTime()
 * @package Mageplaza\AbandonedCart\Model\ResourceModel\Grid\AbandonedCarts
 */
class Collection extends AbstractCollection
{
    /**
     * @var boolean
     */
    protected $isGroupByPeriod;

    /**
     * @var int
     */
    protected $productId;

    /**
     * @param $isGroup
     *
     * @return $this
     */
    public function setGroupByPeriod($isGroup)
    {
        $this->isGroupByPeriod = $isGroup;

        return $this;
    }

    /**
     * @param null $productId
     *
     * @return $this
     */
    public function setProductId($productId = null)
    {
        $this->productId = $productId;

        return $this;
    }

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
                'period_time'       => sprintf('%s', $connection->getDateFormatSql('main_table.period', '%Y-%m-%d')),
                'product_name'      => 'main_table.product_name',
                'product_id'        => new Zend_Db_Expr('MAX(main_table.product_id)'),
                'sku'               => new Zend_Db_Expr('MAX(main_table.sku)'),
                'qty'               => new Zend_Db_Expr('SUM(main_table.qty)'),
                'price'             => new Zend_Db_Expr('MAX(main_table.price)'),
                'abandoned_revenue' => new Zend_Db_Expr('SUM(abandoned_revenue)'),
                'abandoned_time'    => new Zend_Db_Expr('SUM(abandoned_time)'),
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

        if ($this->isGroupByPeriod) {
            $select->group(['product_id', 'period_time']);
        } else {
            $select->group(['product_id']);
        }
        if ($this->productId) {
            $select->where('product_id IN (?)', $this->productId);
        }

        return $this;
    }

    /**
     * Redeclare parent method for applying filters after parent method
     * but before adding unions and calculating totals
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->setOrder('main_table.product_id', 'asc');

        return $this;
    }
}
