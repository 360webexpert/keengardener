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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Helper;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\LayeredNavigation\Helper\Data as AbstractData;

/**
 * Class Data
 * @package Mageplaza\LayeredNavigationPro\Helper
 */
class Data extends AbstractData
{
    const FIELD_ALLOW_MULTIPLE = 'allow_multiple';
    const FIELD_FILTER_TYPE = 'filter_type';
    const FIELD_SEARCH_ENABLE = 'search_enable';
    const FIELD_IS_EXPAND = 'is_expand';
    const FILTER_TYPE_DROPDOWN = 'dropdown';
    const FILTER_TYPE_SLIDERRANGE = 'sliderrange';
    const FILTER_TYPE_RANGE = 'range';
    const FILTER_TYPE_SWATCH = 'swatch';
    const FILTER_TYPE_SWATCHTEXT = 'swatchtext';
    const FILTER_TYPE_RATING = 'rating';
    const FIELD_SHOW_TOOLTIP = 'show_tooltip';
    const FIELD_TOOLTIP_THUMBNAIL = 'tooltip_thumbnail';
    const FIELD_TOOLTIP_CONTENT = 'tooltip_content';

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getFilterConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('layered_navigation/filter' . $code, $storeId);
    }

    /**
     * Layer additional field on attribute edit page
     *
     * @return array
     */
    public function getLayerAdditionalFields()
    {
        return [
            self::FIELD_ALLOW_MULTIPLE,
            self::FIELD_FILTER_TYPE,
            self::FIELD_SEARCH_ENABLE,
            self::FIELD_IS_EXPAND,
            self::FIELD_SHOW_TOOLTIP,
            self::FIELD_TOOLTIP_THUMBNAIL,
            self::FIELD_TOOLTIP_CONTENT,
        ];
    }

    /**
     * Option config for Layer navigation tab in Product Attribute Edit page
     *
     * @return array
     */
    public function getLayerAttributeParams()
    {
        return [
            'displayOption' => $this->getDisplayOptions(),
            'displayRule' => $this->getDisplayRule(),
            'optionDisplayEl' => '#' . self::FIELD_FILTER_TYPE,
            'allowMultipleInputEL' => '#attribute-' . self::FIELD_ALLOW_MULTIPLE . '-container',
            'searchEnableInputEl' => '#attribute-' . self::FIELD_SEARCH_ENABLE . '-container'
        ];
    }

    /**
     * @return array
     */
    public function getDisplayOptions()
    {
        $displayTypes = $this->getDisplayTypes();

        $options = [];
        foreach ($displayTypes as $key => $type) {
            if (isset($type['label'])) {
                $options[$key] = $type['label'];
            }
        }

        return $options;
    }

    /**
     * Declare type and block to display type
     *
     * @return array
     */
    public function getDisplayTypes()
    {
        $displayTypes = new DataObject(
            [
                self::FILTER_TYPE_SLIDER => [
                    'label' => __('Slider'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\Slider'
                ],
                self::FILTER_TYPE_SLIDERRANGE => [
                    'label' => __('Slider and range'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\Slider'
                ],
                self::FILTER_TYPE_RANGE => [
                    'label' => __('Range'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\Slider'
                ],
                self::FILTER_TYPE_LIST => [
                    'label' => __('List'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\OptionList'
                ],
                self::FILTER_TYPE_DROPDOWN => [
                    'label' => __('Dropdown'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\Dropdown'
                ],
                self::FILTER_TYPE_RATING => [
                    'label' => __('Rating'),
                    'class' => 'Mageplaza\LayeredNavigationPro\Block\Type\Rating'
                ],
                self::FILTER_TYPE_SWATCH => [
                    'label' => __('Swatch')
                ],
                self::FILTER_TYPE_SWATCHTEXT => [
                    'label' => __('Swatch and text')
                ],
            ]
        );

        $this->_eventManager->dispatch('layer_option_display_type_list', ['type' => $displayTypes]);

        return $displayTypes->getData();
    }

    /**
     * With each 'frontend_input' type, change the option list of Display Type field
     *
     * @return array
     */
    public function getDisplayRule()
    {
        return [
            'price' => [
                self::FILTER_TYPE_SLIDER,
                self::FILTER_TYPE_RANGE,
                self::FILTER_TYPE_SLIDERRANGE,
                self::FILTER_TYPE_LIST
            ],
            'select' => [
                self::FILTER_TYPE_LIST,
                self::FILTER_TYPE_DROPDOWN
            ],
            'swatch' => [
                self::FILTER_TYPE_SWATCH,
                self::FILTER_TYPE_SWATCHTEXT,
                self::FILTER_TYPE_LIST,
                self::FILTER_TYPE_DROPDOWN
            ]
        ];
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $store
     *
     * @return array|mixed
     */
    public function isSubCategorySliderEnabled($store = null)
    {
        return $this->getModuleConfig('subcategory_slider/subcategory_slider_enable', $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getCategory($store = null)
    {
        $categoryIds = $this->getModuleConfig('subcategory_slider/categories', $store);
        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        return $categoryIds;
    }

    /**
     * @param string $field
     * @param null $store
     *
     * @return mixed
     */
    public function getSubCategoryConfig($field = '', $store = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->getModuleConfig('subcategory_slider' . $field, $store);
    }
}
