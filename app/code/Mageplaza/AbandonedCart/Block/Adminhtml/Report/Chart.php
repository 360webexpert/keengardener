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

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Report;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\ResourceModel\Logs;

/**
 * Class Toolbar
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Report
 */
class Chart extends Template
{
    const NAME = 'abandonedcartReport';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AbandonedCart::report/chart.phtml';

    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Logs
     */
    protected $logs;

    /**
     * Toolbar constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param Data $helperData
     * @param Logs $logs
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Data $helperData,
        Logs $logs,
        array $data = []
    ) {
        $this->date       = $date;
        $this->helperData = $helperData;
        $this->logs       = $logs;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getChartData()
    {
        $date = $this->getDateRange();
        $days = $this->helperData->getRangeNumbers($date[0], $date[1]);

        return [
            'data'     => $this->logs->loadChartData($date[0], $date[1]),
            'stepSize' => round($days / 6),
            'name'     => $this->getName(),
        ];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return __('Abandoned Cart Email Report');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return array|null
     */
    public function getDateRange()
    {
        if ($dateRange = $this->_request->getParam('dateRange')) {
            $fromDate = $this->date->date('m/d/Y', $dateRange[0]);
            $toDate   = $this->date->date('m/d/Y', $dateRange[1]);
        } else {
            $toDate   = $this->date->date('m/d/Y');
            $fromDate = $this->date->date('m/d/Y', $toDate . '-1 month');
        }

        return [$fromDate, $toDate];
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        return $this->getUrl('abandonedcart/index/report');
    }

    /**
     * @return string
     */
    public function getContentHtml()
    {
        return $this->toHtml();
    }

    /**
     * @return bool
     */
    public function getTotal()
    {
        return '';
    }
}
