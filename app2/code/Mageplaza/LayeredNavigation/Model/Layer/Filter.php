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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\Layer;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigation\Model\Layer\Filter\Category;

/**
 * Class Filter
 * @package Mageplaza\LayeredNavigation\Model\Layer
 */
class Filter
{
    /** @var RequestInterface */
    protected $request;

    /** @var array Slider types */
    protected $sliderTypes = [LayerHelper::FILTER_TYPE_SLIDER];

    /**
     * Filter constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Layered configuration for js widget
     *
     * @param AbstractFilter $filters
     * @param $config
     *
     * @return mixed
     */
    public function getLayerConfiguration($filters, $config)
    {
        $slider = [];
        foreach ($filters as $filter) {
            if ($this->isSliderTypes($filter) && $filter->getItemsCount()) {
                $slider[$filter->getRequestVar()] = $filter->getSliderConfig();
            }
        }
        $config->setData('slider', $slider);

        return $this;
    }

    /**
     * @param $filter
     * @param null $types
     *
     * @return bool
     */
    public function isSliderTypes($filter, $types = null)
    {
        $filterType = $this->getFilterType($filter);
        $types      = $types ?: $this->sliderTypes;

        return in_array($filterType, $types, true);
    }

    /**
     * @param AbstractFilter $filter
     * @param null $compareType
     *
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getFilterType($filter, $compareType = null)
    {
        $type = LayerHelper::FILTER_TYPE_LIST;
        if ($filter->getRequestVar() === 'price') {
            $type = LayerHelper::FILTER_TYPE_SLIDER;
        } elseif ($filter->getRequestVar() === 'cat'
            && $filter instanceof Category
            && $filter->isRenderCategoryTree()) {
            $type = LayerHelper::FILTER_TYPE_TREE;
        }

        return $compareType ? ($type === $compareType) : $type;
    }

    /**
     * Get option url. If it has been filtered, return removed url. Else return filter url
     *
     * @param Item $item
     *
     * @return mixed
     */
    public function getItemUrl($item)
    {
        if ($this->isSelected($item)) {
            return $item->getRemoveUrl();
        }

        return $item->getUrl();
    }

    /**
     * Check if option is selected or not
     *
     * @param Item $item
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isSelected(Item $item)
    {
        $filterValue = $this->getFilterValue($item->getFilter());

        return !empty($filterValue) && in_array((string)$item->getValue(), $filterValue, true);
    }

    /**
     * @param AbstractFilter $filter
     * @param bool|true $explode
     *
     * @return array|mixed
     */
    public function getFilterValue($filter, $explode = true)
    {
        $filterValue = $this->request->getParam($filter->getRequestVar());
        if (empty($filterValue)) {
            return [];
        }

        return $explode ? explode(',', $filterValue) : [$filterValue];
    }

    /**
     * Allow to show counter after options
     *
     * @param AbstractFilter $filter
     *
     * @return bool
     */
    public function isShowCounter($filter)
    {
        return true;
    }

    /**
     * Allow multiple filter
     *
     * @param AbstractFilter $filter
     *
     * @return bool
     */
    public function isMultiple($filter)
    {
        return !($this->isSliderTypes($filter) || $filter->getRequestVar() === 'price');
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * @param AbstractFilter $filter
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     *
     * @return bool
     */
    public function isOptionReducesResults($filter, $optionCount, $totalSize)
    {
        $result = $optionCount <= $totalSize;

        if ($this->isShowZero($filter)) {
            return $result;
        }

        return $optionCount && $result;
    }

    /**
     * @param AbstractFilter $filter
     *
     * @return bool
     */
    public function isShowZero($filter)
    {
        return false;
    }
}
