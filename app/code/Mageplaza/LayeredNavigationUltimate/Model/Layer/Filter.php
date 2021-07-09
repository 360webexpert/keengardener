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

namespace Mageplaza\LayeredNavigationUltimate\Model\Layer;

use Mageplaza\LayeredNavigationPro\Model\Layer\Filter as FilterModel;
use Mageplaza\LayeredNavigationUltimate\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\DisplayType;

/**
 * Class Filter
 * @package Mageplaza\LayeredNavigationUltimate\Model\Layer
 */
class Filter extends FilterModel
{
    /**
     * @inheritdoc
     */
    public function getLayerConfiguration($filters, $config)
    {
        parent::getLayerConfiguration($filters, $config);

        $displayType = [];
        $ratingSlider = false;

        foreach ($filters as $filter) {
            $requestVar = $filter->getRequestVar();
            if ($filter->getItemsCount() && $this->getFilterType($filter, LayerHelper::FILTER_TYPE_LIST)) {
                $displayType[$requestVar] = $this->getDisplayType($filter);
            }

            if ($filter->getRequestVar() == LayerHelper::FILTER_TYPE_RATING
                && $this->helper->getFilterConfig('rating/show_as_slider')
            ) {
                $ratingSlider = $filter->getSliderConfig();
            }
        }

        $config->addData([
            'infiniteScroll' => (bool) $this->helper->getConfigGeneral('infinite_scroll'),
            'ionRange'       => (bool) $this->helper->enableIonRangeSlider(),
            'displayType'    => $displayType,
            'ratingSlider'   => $ratingSlider
        ]);

        return $this;
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function getDisplayType($filter)
    {
        if ($filter->hasAttributeModel()) {
            $attribute = $filter->getAttributeModel();
            $this->prepareAttributeData($attribute);

            $displayType = $attribute->getData(LayerHelper::FIELD_DISPLAY_TYPE);
            if ($displayType == DisplayType::TYPE_SCROLL) {
                return [
                    'type'  => 'scroll',
                    'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_HEIGHT, $attribute)
                ];
            } elseif ($displayType == DisplayType::TYPE_HIDDEN) {
                return [
                    'type'  => 'hidden',
                    'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_SIZE, $attribute)
                ];
            } elseif ($displayType == DisplayType::TYPE_DEFAULT) {
                $displayType = $this->helper->getConfigGeneral(LayerHelper::FIELD_DISPLAY_TYPE);
                if ($displayType == DisplayType::TYPE_SCROLL) {
                    return [
                        'type'  => 'scroll',
                        'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_HEIGHT)
                    ];
                } elseif ($displayType == DisplayType::TYPE_HIDDEN) {
                    return [
                        'type'  => 'hidden',
                        'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_SIZE)
                    ];
                }
            }
        } else {
            $displayType = $this->helper->getConfigGeneral(LayerHelper::FIELD_DISPLAY_TYPE);
            if ($displayType == DisplayType::TYPE_SCROLL) {
                return [
                    'type'  => 'scroll',
                    'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_HEIGHT)
                ];
            } elseif ($displayType == DisplayType::TYPE_HIDDEN) {
                return [
                    'type'  => 'hidden',
                    'value' => $this->getDisplayValue(LayerHelper::FIELD_DISPLAY_SIZE)
                ];
            }
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function prepareAttributeData($attribute)
    {
        parent::prepareAttributeData($attribute);

        if ($attribute->getData(LayerHelper::FIELD_DISPLAY_TYPE) === null) {
            $attribute->setData(LayerHelper::FIELD_DISPLAY_TYPE, 2);
        }

        if ($attribute->getData(LayerHelper::FIELD_DISPLAY_SIZE) === null) {
            $attribute->setData(LayerHelper::FIELD_DISPLAY_SIZE, '5');
        }

        if ($attribute->getData(LayerHelper::FIELD_DISPLAY_HEIGHT) === null) {
            $attribute->setData(LayerHelper::FIELD_DISPLAY_HEIGHT, '200');
        }
    }

    /**
     * Get display value include default value
     *
     * @param $type
     * @param null $attribute
     *
     * @return string
     */
    protected function getDisplayValue($type, $attribute = null)
    {
        $defaultValue = ($type == LayerHelper::FIELD_DISPLAY_HEIGHT) ? '200' : '5';
        $minValue = ($type == LayerHelper::FIELD_DISPLAY_HEIGHT) ? '50' : '1';

        if ($attribute !== null) {
            return max($minValue, $attribute->getData($type) ?: $defaultValue);
        }

        return max($minValue, $this->helper->getConfigGeneral($type) ?: $defaultValue);
    }
}
