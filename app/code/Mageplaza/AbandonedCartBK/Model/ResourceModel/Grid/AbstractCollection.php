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

namespace Mageplaza\AbandonedCart\Model\ResourceModel\Grid;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\AbandonedCart\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class AbstractCollection
 * @package Mageplaza\AbandonedCart\Model\ResourceModel\Grid
 */
abstract class AbstractCollection extends SearchResult
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var array
     */
    protected $_dateRange = ['from' => null, 'to' => null];

    /**
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @var
     */
    protected $_store;

    /**
     * AbstractCollection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     * @param string $mainTable
     * @param null|string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        Data $helperData,
        $mainTable,
        $resourceModel
    ) {
        $this->_request    = $request;
        $this->_helperData = $helperData;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()], $this->_getSelectedColumns());

        return $this;
    }

    /**
     * @return $this
     */
    protected function _getSelectedColumns()
    {
        return $this;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->_dateRange['from'] = $from;
        $this->_dateRange['to']   = $to;

        return $this;
    }

    /**
     * @param null $from
     * @param null $to
     *
     * @return array
     */
    protected function getDateRange($from = null, $to = null)
    {
        if ($from === null) {
            if (isset($this->_request->getParam('mpFilter')['startDate'])) {
                $from = $this->_request->getParam('mpFilter')['startDate'];
            } else {
                $from = ($this->_request->getParam('startDate') !== null)
                    ? $this->_request->getParam('startDate') : null;
            }
        }
        if ($to === null) {
            if (isset($this->_request->getParam('mpFilter')['endDate'])) {
                $to = $this->_request->getParam('mpFilter')['endDate'];
            } else {
                $to = ($this->_request->getParam('endDate') !== null) ? $this->_request->getParam('endDate') : null;
            }
        }
        if ($to === null || $from === null) {
            try {
                $dates = $this->_helperData->getDateRange();
                $from  = $dates[0];
                $to    = $dates[1];
            } catch (Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return [$from, $to];
    }

    /**
     * @param null $from
     * @param null $to
     *
     * @return $this
     */
    protected function _applyDateRangeFilter($from = null, $to = null)
    {
        $dateRange = $this->getDateRange($from, $to);
        $from      = $dateRange[0];
        $to        = $dateRange[1];

        // Remember that field PERIOD is a DATE(YYYY-MM-DD) in all databases
        if ($from !== null) {
            $this->getSelect()->where('main_table.period >= ?', $from);
        }
        if ($to !== null) {
            $this->getSelect()->where('main_table.period <= ?', $to);
        }

        return $this;
    }

    /**
     * @return int|mixed|null
     */
    protected function getStore()
    {
        if ($this->_store === null) {
            $storeParam       = $this->_request->getParam('store');
            $storeFilterParam = isset($this->_request->getParam('mpFilter')['store'])
                ? $this->_request->getParam('mpFilter')['store'] : null;
            if ($storeFilterParam !== null && $storeFilterParam !== '') {
                $this->_store = $storeFilterParam;
            } else {
                $this->_store = ($storeParam !== null && $storeParam !== '') ? $storeParam : 0;
            }
        }

        return $this->_store;
    }

    /**
     * @return $this
     */
    protected function _applyStoreFilter()
    {
        $store = $this->getStore();
        $this->getSelect()->where('main_table.store_id =?', $store);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function applyCustomerGroupFilter()
    {
        if (isset($this->_request->getParam('mpFilter')['customer_group_id'])) {
            $customerGroup = $this->_request->getParam('mpFilter')['customer_group_id'];
        } else {
            $customerGroup = ($this->_request->getParam('customer_group_id') !== null)
                ? $this->_request->getParam('customer_group_id') : 32000;
        }
        if ((int) $customerGroup !== 32000) {
            $this->getSelect()->where('customer_group_id=' . $customerGroup);
        }

        return $this;
    }

    /**
     * @return $this|SearchResult
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->_applyAggregatedTable();
        $this->_applyDateRangeFilter($this->_dateRange['from'], $this->_dateRange['to']);
        $this->_applyStoreFilter();
        $this->applyCustomerGroupFilter();

        return $this;
    }

    /**
     * @return mixed|string
     */
    protected function getPeriod()
    {
        if (isset($this->_request->getParam('mpFilter')['period'])) {
            $period = $this->_request->getParam('mpFilter')['period'];
        } else {
            $period = ($this->_request->getParam('period') !== null) ? $this->_request->getParam('period') : 'day';
        }

        return $period;
    }

    /**
     * @return Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(Select::ORDER);

        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (isset($condition['like'])) {
            $this->getSelect()->having("{$field} like '{$condition['like']}'");
        }
        if (isset($condition['gteq'])) {
            $this->getSelect()->having("{$field} >= {$condition['gteq']}");
        }
        if (isset($condition['lteq'])) {
            $this->getSelect()->having("{$field} <= {$condition['lteq']}");
        }

        return $this;
    }
}
