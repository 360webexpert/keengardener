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

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Chart;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\ProductReport\Collection;
use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\ProductReport\CollectionFactory;

/**
 * Class AbandonedCarts
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Chart
 */
class Products extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $productReportsCollection;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AbandonedCart::chart/product_reports.phtml';

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productReportsCollection
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productReportsCollection,
        Data $helperData,
        array $data = []
    ) {
        $this->request                  = $context->getRequest();
        $this->productReportsCollection = $productReportsCollection;
        $this->_helperData              = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param Collection $collection
     * @param array $filters
     *
     */
    protected function applyFilters($collection, $filters)
    {
        foreach ($filters as $field => $condition) {
            if ($field === 'placeholder') {
                continue;
            }

            if (is_array($condition)) {
                if (isset($condition['from'])) {
                    $collection->addFieldToFilter($field, ['gteq' => $condition['from']]);
                }
                if (isset($condition['to'])) {
                    $collection->addFieldToFilter($field, ['lteq' => $condition['to']]);
                }
            } else {
                $collection->addFieldToFilter($field, ['like' => '%' . $condition . '%']);
            }
        }
    }

    /**
     * @param null $pageSize
     *
     * @return array
     * @throws Exception
     */
    public function getCollectionData($pageSize = null)
    {
        if (!$pageSize) {
            $pageSize = 5;
        }

        $params    = $this->request->getParams();
        $dateRange = $this->_helperData->getDateRange();
        $from      = $this->getParam($params, 'startDate') ?: $dateRange[0];
        $to        = $this->getParam($params, 'endDate') ?: $dateRange[1];
        $period    = $this->getParam($params, 'period') ?: 'day';

        $collection = $this->productReportsCollection->create()
            ->setOrder('abandoned_time', 'desc')
            ->setPageSize($pageSize);

        if ($filters = $this->getParam($params, 'filters')) {
            $this->applyFilters($collection, $filters);
        }

        $productsData = [];
        $chartData    = [];
        foreach ($collection->getItems() as $item) {
            $productCollection = $this->productReportsCollection->create()->setGroupByPeriod(1)
                ->setProductId($item->getProductId())->load();

            $data = $this->_helperData->getIntervals($from, $to, $period);

            foreach ($productCollection->getItems() as $productItem) {
                $data[$productItem->getPeriodTime()]->setData([
                    'period'         => $productItem->getPeriodTime(),
                    'abandoned_time' => $productItem->getAbandonedTime()
                ]);
            }

            $periods       = [];
            $abandonedTime = [];

            foreach ($data as $key => $value) {
                $periods[]       = $key;
                $abandonedTime[] = $value->getAbandonedTime() ? (int) $value->getAbandonedTime() : 0;
            }
            $chartData['labels'] = $periods;
            $productsData[]      = [
                'label'       => $item->getProductName(),
                'data'        => $abandonedTime,
                'borderWidth' => 1,
                'fill'        => false
            ];
        }
        if (isset($productsData[0])) {
            $productsData[0]['borderColor']     = '#20a8d8';
            $productsData[0]['backgroundColor'] = '#20a8d8';
        }
        if (isset($productsData[1])) {
            $productsData[1]['borderColor']     = '#f86c6b';
            $productsData[1]['backgroundColor'] = '#f86c6b';
        }
        if (isset($productsData[2])) {
            $productsData[2]['borderColor']     = '#ffc107';
            $productsData[2]['backgroundColor'] = '#ffc107';
        }
        if (isset($productsData[3])) {
            $productsData[3]['borderColor']     = '#4dbd74';
            $productsData[3]['backgroundColor'] = '#4dbd74';
        }
        if (isset($productsData[4])) {
            $productsData[4]['borderColor']     = '#f8cb00';
            $productsData[4]['backgroundColor'] = '#f8cb00';
        }
        $chartData['datasets'] = $productsData;

        return $chartData;
    }

    /**
     * Retrieve param by key
     *
     * @param $params
     * @param string $key
     *
     * @return mixed
     */
    public function getParam($params, $key)
    {
        if (isset($params[$key])) {
            return $params[$key];
        }

        return null;
    }
}
