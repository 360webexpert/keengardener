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

namespace Mageplaza\AbandonedCart\Helper;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as StdlibDateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon as CouponResource;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Zend_Serializer_Exception;

/**
 * Class Data
 * @package Mageplaza\AbandonedCart\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'abandonedcart';

    /**
     * @var string Url Suffix analytics
     */
    protected $urlSuffix = [];

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var
     */
    protected $storeFilter;

    /**
     * @var CouponResource
     */
    protected $couponResource;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param StdlibDateTime $date
     * @param Timezone $timeZone
     * @param CouponFactory $couponFactory
     * @param CouponResource $couponResource
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StdlibDateTime $date,
        Timezone $timeZone,
        CouponFactory $couponFactory,
        CouponResource $couponResource
    ) {
        $this->date           = $date;
        $this->timeZone       = $timeZone;
        $this->couponResource = $couponResource;
        $this->couponFactory  = $couponFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getEmailConfig($storeId = null)
    {
        $emailConfig = $this->getConfigGeneral('email', $storeId);
        if ($emailConfig) {
            $configs = $this->unserialize($emailConfig);
            if (!empty($configs)) {
                $day = 86400;
                foreach ($configs as $configId => $config) {
                    if (isset($config['send']) && $config['send']) {
                        $configSeconds = 0;
                        $configTimes   = explode(' ', $config['send']);
                        foreach ($configTimes as $configTime) {
                            if (strpos($configTime, 'd') !== false) {
                                $configSeconds += (int) str_replace('d', '', $configTime) * $day;
                            } elseif (strpos($configTime, 'h') !== false) {
                                $configSeconds += (int) str_replace('h', '', $configTime) * 60 * 60;
                            } elseif (strpos($configTime, 'm') !== false) {
                                $configSeconds += (int) str_replace('m', '', $configTime) * 60;
                            }
                        }
                        $configs[$configId]['send'] = $configSeconds;
                        $send[$configId]            = $configSeconds;
                    }
                }
                array_multisort($send, SORT_ASC, $configs);

                return $configs;
            }
        }

        return [];
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAnalyticsConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/analytics' . $code, $storeId);
    }

    /**
     * @param Store $store
     *
     * @return mixed
     */
    public function getUrlSuffix($store)
    {
        $storeId = $store->getId();
        if (!isset($this->urlSuffix[$storeId])) {
            $suffix = ['___store' => $store->getCode()];
            if ($this->getAnalyticsConfig('enabled', $storeId)) {
                if ($source = $this->getAnalyticsConfig('source', $storeId)) {
                    $suffix['utm_source'] = $source;
                }
                if ($medium = $this->getAnalyticsConfig('medium', $storeId)) {
                    $suffix['utm_medium'] = $medium;
                }
                if ($name = $this->getAnalyticsConfig('name', $storeId)) {
                    $suffix['utm_campaign'] = $name;
                }
                if ($term = $this->getAnalyticsConfig('term', $storeId)) {
                    $suffix['utm_term'] = $term;
                }
                if ($content = $this->getAnalyticsConfig('content', $storeId)) {
                    $suffix['utm_content'] = $content;
                }
            }

            $this->urlSuffix[$storeId] = $suffix;
        }

        return $this->urlSuffix[$storeId];
    }

    /**
     * Get Coupon Config
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCouponConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/coupon' . $code, $storeId);
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function onlySendToSubscribed($storeId = null)
    {
        return $this->getConfigGeneral('send_subscribed_only', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getRealtimeConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/report' . $code, $storeId);
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param null $dimension
     *
     * @return false|int|string
     */
    public function getRangeNumbers($fromDate, $toDate, $dimension = null)
    {
        $timeZoneFrom = $this->timeZone->date($fromDate);
        $timeZoneTo   = $this->timeZone->date($toDate);
        $timeDiff     = $timeZoneFrom->diff($timeZoneTo);
        if ($dimension === 'month') {
            $numbers = $timeDiff->m;
            if ($numbers == 0 && $this->date->date('m', $fromDate) != $this->date->date('m', $toDate)) {
                $numbers = 1;
            }
        } else {
            $numbers = $timeDiff->days;
        }

        return $numbers;
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    public function getDateRange()
    {
        if ($dateRange = $this->_request->getParam('dateRange')) {
            $startDate = $dateRange[0];
            $endDate   = $dateRange[1];
        } else {
            if ($this->_request->getActionName() === 'cartboard') {
                $dateRange = '-' . $this->getRealtimeConfig('date_range') . ' day';
            } else {
                $dateRange = '-1 month';
            }
            [$startDate, $endDate] = $this->getDateTimeRangeFormat($dateRange, 'now');
        }

        return [$startDate, $endDate];
    }

    /**
     * @param null $from
     * @param null $to
     *
     * @return array
     * @throws Exception
     */
    public function getDateRangeFilter($from = null, $to = null)
    {
        if ($from === null) {
            if (isset($this->_request->getParam('mpFilter')['startDate'])) {
                $from = $this->_request->getParam('mpFilter')['startDate'];
            } else {
                $from = $this->_request->getParam('startDate');
            }
        }
        if ($to === null) {
            if (isset($this->_request->getParam('mpFilter')['endDate'])) {
                $to = $this->_request->getParam('mpFilter')['endDate'];
            } else {
                $to = $this->_request->getParam('endDate');
            }
            $to = (new DateTime($to))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        }
        if ($to === null || $from === null) {
            [$from, $to] = $this->getDateRange();
        }

        return [$from, $to];
    }

    /**
     * @return int|mixed|null
     */
    public function getStoreFilter()
    {
        if ($this->storeFilter === null) {
            $storeParam       = $this->_request->getParam('store');
            $storeFilterParam = isset($this->_request->getParam('mpFilter')['store'])
                ? $this->_request->getParam('mpFilter')['store'] : null;
            if ($storeFilterParam !== null && $storeFilterParam !== '') {
                $this->storeFilter = $storeFilterParam;
            } else {
                $this->storeFilter = ($storeParam !== null && $storeParam !== '') ? $storeParam : 0;
            }
        }

        return $this->storeFilter;
    }

    /**
     * @param $startDate
     * @param null $endDate
     * @param null $isConvertToLocalTime
     *
     * @return array
     * @throws Exception
     */
    public function getDateTimeRangeFormat($startDate, $endDate = null, $isConvertToLocalTime = null)
    {
        if (!$endDate) {
            $endDate = $startDate;
        }
        $startDate = (new DateTime($startDate, new DateTimeZone($this->getTimezone())))->setTime(0, 0, 0);
        $endDate   = (new DateTime($endDate, new DateTimeZone($this->getTimezone())))->setTime(23, 59, 59);

        if ($isConvertToLocalTime) {
            $startDate->setTimezone(new DateTimeZone('UTC'));
            $endDate->setTimezone(new DateTimeZone('UTC'));
        }

        return [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')];
    }

    /**
     * @return array|mixed
     */
    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }

    /**
     * @param $from
     * @param $to
     * @param string $period
     *
     * @return array
     * @throws Exception
     */
    public function getIntervals($from, $to, $period = 'day')
    {
        $intervals = [];
        if (!$from && !$to) {
            return $intervals;
        }

        $dateStart = new DateTime($from);
        $dateEnd   = new DateTime($to);

        switch ($period) {
            case 'week':
                $intervals[$dateStart->format('Y-W')] = new DataObject();
                $dateStart->modify('Monday this week');
                break;
            case 'month':
                $intervals[$dateStart->format('Y-m')] = new DataObject();
                $dateStart->modify('first day of this month');
                break;
            case 'year':
                $intervals[$dateStart->format('Y')] = new DataObject();
                $dateStart->modify('first day of this year');
                break;
        }

        while ($dateStart->diff($dateEnd)->invert == 0) {
            switch ($period) {
                case 'day':
                    $intervals[$dateStart->format('Y-m-d')] = new DataObject();
                    $dateStart->add(new DateInterval('P1D'));
                    break;
                case 'week':
                    $intervals[$dateStart->format('Y-W')] = new DataObject();
                    $dateStart->add(new DateInterval('P1W'));
                    break;
                case 'month':
                    $intervals[$dateStart->format('Y-m')] = new DataObject();
                    $dateStart->add(new DateInterval('P1M'));
                    break;
                case 'year':
                    $intervals[$dateStart->format('Y')] = new DataObject();
                    $dateStart->add(new DateInterval('P1Y'));
                    break;
            }
        }

        return $intervals;
    }

    /**
     * @return int|mixed
     */
    public function getCustomerGroup()
    {
        if (isset($this->_request->getParam('mpFilter')['customer_group_id'])) {
            $customerGroup = $this->_request->getParam('mpFilter')['customer_group_id'];
        } else {
            $customerGroup = ($this->_request->getParam('customer_group_id') !== null)
                ? $this->_request->getParam('customer_group_id') : 32000;
        }

        return $customerGroup;
    }

    /**
     * @param string $from
     *
     * @return string
     * @throws Exception
     */
    public function getStartDateUTC($from)
    {
        $configTimezone = new DateTimeZone($this->getTimezone());
        $utcTimezone    = new DateTimeZone('UTC');
        $startDate      = new DateTime($from, $configTimezone);

        return $startDate->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
    }

    /**
     * @param string $toD
     *
     * @return string
     * @throws Exception
     */
    public function getEndDateUTC($toD)
    {
        $configTimezone = new DateTimeZone($this->getTimezone());
        $utcTimezone    = new DateTimeZone('UTC');
        $endDate        = new DateTime($toD, $configTimezone);

        return $endDate->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
    }

    /**
     * @param string $coupon
     *
     * @return bool
     */
    public function isExpiredCoupon($coupon)
    {
        $salesRuleCoupon = $this->couponFactory->create();
        $this->couponResource->load($salesRuleCoupon, $coupon, 'code');
        $timeNow = $this->timeZone->date()->getTimestamp();

        return $salesRuleCoupon->getData('mp_ace_expires_at') &&
            strtotime($salesRuleCoupon->getData('mp_ace_expires_at')) < $timeNow;
    }
}
