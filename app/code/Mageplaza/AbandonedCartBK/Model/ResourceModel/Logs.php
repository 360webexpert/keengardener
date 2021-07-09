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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Mageplaza\AbandonedCart\Helper\Data;

/**
 * Class Logs
 * @package Mageplaza\AbandonedCart\Model\ResourceModel
 */
class Logs extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * constructor
     *
     * @param Context $context
     * @param DateTime $date
     * @param Timezone $timeZone
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Timezone $timeZone,
        Data $helperData
    ) {
        $this->date               = $date;
        $this->timeZone           = $timeZone;
        $this->resourceConnection = $context->getResources();
        $this->helperData         = $helperData;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mageplaza_abandonedcart_logs', 'id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        $object->setUpdatedAt($this->date->date());

        return parent::_beforeSave($object);
    }

    /**
     * @param $quoteId
     *
     * @throws LocalizedException
     */
    public function updateRecovery($quoteId)
    {
        $bind  = ['recovery' => true, 'status' => 2];
        $where = ['quote_id = ?' => $quoteId];

        $this->getConnection()->update($this->getMainTable(), $bind, $where);
    }

    /**
     *
     * @param string $date
     *
     * @return string
     */
    private function convertDate($date)
    {
        return $this->date->date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param null $dimension
     *
     * @return array
     * @throws LocalizedException
     */
    public function loadReportData($fromDate, $toDate, $dimension = null)
    {
        $result = [];

        $numbers = $this->helperData->getRangeNumbers($fromDate, $toDate, $dimension);
        if ($dimension === 'month') {
            $level    = ' month';
            $fromDate = $this->date->date('Y-m-01', $fromDate);
        } else {
            $level = ' days';
        }

        for ($number = 0; $number <= $numbers; $number++) {
            $date       = $this->date->date('Y-m-d', $fromDate . '+' . $number . $level);
            $nextDate   = $this->date->date('Y-m-d', $date . '+1' . $level);
            $dateFormat = $this->date->date('Y-M-d', $date);

            if ($dimension === 'month') {
                $date       = $this->date->date('Y-m-01', $date);
                $dateFormat = $this->date->date('Y-M', $date);
            }

            $result[] = [
                $dateFormat,
                $this->getAbandonedCart($date, $nextDate),
                $this->getLogData($date, $nextDate),
                $this->getLogData($date, $nextDate, 'recovery'),
                $this->getLogData($date, $nextDate, 'error')
            ];
        }

        return $result;
    }

    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     * @throws LocalizedException
     */
    public function loadChartData($fromDate, $toDate)
    {
        $result = [];

        $numbers = $this->helperData->getRangeNumbers($fromDate, $toDate);

        for ($number = 0; $number <= $numbers; $number++) {
            $date       = $this->date->date('Y-m-d', $fromDate . '+' . $number . ' day');
            $nextDate   = $this->date->date('Y-m-d', $date . '+1 day');
            $dateFormat = $this->date->date('Y-M-d', $date);

            $result['date'][]          = $dateFormat;
            $result['abandonedCart'][] = $this->getAbandonedCart($date, $nextDate);
            $result['sent'][]          = $this->getLogData($date, $nextDate);
            $result['recovery'][]      = $this->getLogData($date, $nextDate, 'recovery');
            $result['error'][]         = $this->getLogData($date, $nextDate, 'error');
        }

        return $result;
    }

    /**
     * @param string $date
     * @param string $nextDate
     *
     * @return int
     */
    private function getAbandonedCart($date, $nextDate)
    {
        $adapter  = $this->resourceConnection->getConnection();
        $select   = $adapter->select()
            ->from($this->resourceConnection->getTableName('quote'))
            ->where(
                '(created_at >= ? AND updated_at = "0000-00-00 00:00:00") OR updated_at >= ?',
                $this->convertDate($date)
            )
            ->where(
                '(created_at < ? AND updated_at = "0000-00-00 00:00:00") OR updated_at < ?',
                $this->convertDate($nextDate)
            )
            ->where('is_active = ?', true)
            ->where('items_count > ?', 0)
            ->where('customer_email != ?', null);
        $quoteIds = $adapter->fetchAll($select);

        return count($quoteIds);
    }

    /**
     * @throws LocalizedException
     */
    public function clear()
    {
        $bind = ['display' => false];
        $this->getConnection()->update($this->getMainTable(), $bind);
    }

    /**
     * @param $date
     * @param $nextDate
     * @param null $column
     *
     * @return int
     * @throws LocalizedException
     */
    private function getLogData($date, $nextDate, $column = null)
    {
        $adapter = $this->resourceConnection->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('display = ?', true)
            ->where('updated_at >= ?', $this->convertDate($date))
            ->where('updated_at < ?', $this->convertDate($nextDate));
        if ($column === 'recovery') {
            $select->where('recovery = ?', true);
        }
        if ($column === 'error') {
            $select->where('status = ?', false);
        }
        $collection = $adapter->fetchCol($select);

        return count($collection);
    }
}
