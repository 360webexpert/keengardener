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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter;

/**
 * Class Rating
 * @package Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter
 */
class Rating extends \Mageplaza\LayeredNavigationPro\Model\Layer\Filter\Rating
{
    /**
     * @var null
     */
    protected $_filterVal = null;

    /**
     * Rating constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $moduleHelper, $data);

        if ($this->_moduleHelper->getFilterConfig('rating/show_as_slider')) {
            $this->setData('range_mode', true);
        }
    }

    /**
     * @inheritdoc
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $showRatingSlider = $this->_moduleHelper->getFilterConfig('rating/show_as_slider');
        if ($showRatingSlider) {
            $productCollection = $this->getLayer()->getProductCollection();
            $productCollection->getSelect()
                ->joinLeft(
                    ['rt' => $productCollection->getTable('review_entity_summary')],
                    "e.entity_id = rt.entity_pk_value AND rt.store_id = " . $this->_storeManager->getStore()->getId(),
                    ['rating_summary']
                );

            $attributeValue = $request->getParam($this->_requestVar);
            if (empty($attributeValue)) {
                return $this;
            }

            $filtered = $request->getParam($this->_requestVar);
            if ($filtered && !is_array($filtered)) {
                $filterParams = explode(',', $filtered);
                $filter = $this->validateFilter($filterParams[0]);
                if ($filter) {
                    $this->_filterVal = $filter;
                }
            }

            $attributeValue = explode('-', $attributeValue);
            if (count($attributeValue) != 2 || $attributeValue[0] > $attributeValue[1]
                || ($attributeValue[0] < 1 || $attributeValue[1] > 5)
            ) {
                return $this;
            }

            $ratingDown = min($attributeValue);
            $ratingUp = max($attributeValue);
            $productCollection->getSelect()->where('rt.rating_summary >= "'
                                                   . $ratingDown * 20 . '" AND rt.rating_summary <=  "' . $ratingUp * 20 . '"');

            $this->getLayer()->getState()->addFilter($this->_createItem(
                $this->getRatingOptionText($attributeValue),
                $ratingDown
            ));

            return $this;
        }

        return parent::apply($request);
    }

    /**
     * Rating Slider Configuration
     *
     * @return array
     */
    public function getSliderConfig()
    {
        $ratingsSlider = [5, 4, 3, 2, 1];

        $min = min($ratingsSlider);
        $max = max($ratingsSlider);
        list($from, $to) = $this->_filterVal ?: [$min, $max];
        $from = ($from < $min) ? $min : $from;
        $to = ($to > $max) ? $max : $to;

        $item = $this->getItems()[0];

        return [
            "selectedFrom" => $from,
            "selectedTo"   => $to,
            "minValue"     => $min,
            "maxValue"     => $max,
            "orientation"  => "vertical",
            "ajaxUrl"      => $item->getUrl(),
            "ratingCode"   => \Mageplaza\LayeredNavigationPro\Helper\Data::FILTER_TYPE_RATING
        ];
    }

    /**
     * Validate and parse filter request param
     *
     * @param string $filter
     *
     * @return array|bool
     */
    public function validateFilter($filter)
    {
        $filter = explode('-', $filter);
        if (count($filter) != 2) {
            return false;
        }
        foreach ($filter as $v) {
            if ($v !== '' && $v !== '0' && (double) $v <= 0 || is_infinite((double) $v)) {
                return false;
            }
        }

        return $filter;
    }

    /**
     * get rating option text
     *
     * @param array $value
     *
     * @return \Magento\Framework\Phrase
     */
    public function getRatingOptionText(array $value)
    {
        if ($value[0] == $value[1]) {
            return $value[0] == 1 ? __('%1 star', $value[0]) : __('%1 stars', $value[0]);
        }

        return $value[0] == 1 ? __('%1 star to %2 stars', $value[0], $value[1])
            : __('%1 stars to %2 stars', $value[0], $value[1]);
    }
}
