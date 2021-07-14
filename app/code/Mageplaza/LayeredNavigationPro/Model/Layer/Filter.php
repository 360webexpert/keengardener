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

namespace Mageplaza\LayeredNavigationPro\Model\Layer;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Mageplaza\LayeredNavigation\Model\Layer\Filter as FilterModel;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;

/**
 * Class Filter
 * @package Mageplaza\LayeredNavigationPro\Model\Layer
 */
class Filter extends FilterModel
{
    /** @var LayerHelper */
    protected $helper;

    /** @var SwatchHelper */
    protected $swatchHelper;

    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /** @var array Slider Types */
    protected $sliderTypes = [
        LayerHelper::FILTER_TYPE_SLIDER,
        LayerHelper::FILTER_TYPE_SLIDERRANGE,
        LayerHelper::FILTER_TYPE_RANGE
    ];

    /**
     * Filter constructor.
     *
     * @param RequestInterface $request
     * @param LayerHelper $layerHelper
     * @param SwatchHelper $swatchHelper
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        LayerHelper $layerHelper,
        SwatchHelper $swatchHelper,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->helper         = $layerHelper;
        $this->swatchHelper   = $swatchHelper;
        $this->_objectManager = $objectManager;
        $this->storeManager   = $storeManager;

        parent::__construct($request);
    }

    /**
     * @inheritdoc
     */
    public function getLayerConfiguration($filters, $config)
    {
        parent::getLayerConfiguration($filters, $config);

        $active            = $config->getActive();
        $swatchOptionText  = [];
        $multipleAttribute = [];
        foreach ($filters as $filter) {
            $requestVar = $filter->getRequestVar();
            if (!in_array($requestVar, $active, true) && $this->isExpand($filter)) {
                $active[] = $requestVar;
            }
            if ($this->isMultiple($filter)) {
                $multipleAttribute[] = $filter->getRequestVar();
            }
            if ($this->getFilterType($filter, LayerHelper::FILTER_TYPE_SWATCHTEXT)) {
                $swatchOptionText[] = $filter->getRequestVar();
            }
        }

        $config->addData([
            'scroll'           => (bool) $this->helper->getConfigGeneral('scroll_top'),
            'active'           => $active,
            'buttonSubmit'     => $this->initButtonSubmit($multipleAttribute),
            'multipleAttrs'    => $multipleAttribute,
            'swatchOptionText' => $swatchOptionText
        ]);

        return $this;
    }

    /**
     * @param $multipleAttribute
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function initButtonSubmit($multipleAttribute)
    {
        $enable       = (bool) $this->helper->getConfigGeneral('apply_filter');
        $seoUrlEnable = (bool) $this->helper->isModuleOutputEnabled('Mageplaza_SeoUrl');
        $baseUrl      = trim($this->storeManager->getStore()->getBaseUrl(), '/') . '/'
            . trim($this->request->getOriginalPathInfo(), '/');
        $urlSuffix    = strpos($baseUrl, 'catalogsearch') ? '' : $this->helper->getUrlSuffix();
        $submitResult = [
            'enable'       => $enable,
            'seoUrlEnable' => $seoUrlEnable,
            'baseUrl'      => $baseUrl,
            'urlSuffix'    => $urlSuffix
        ];

        if ($enable && $seoUrlEnable) {
            $seoMultipleAttrs = [];
            $seoHelper        = $this->_objectManager->get('Mageplaza\SeoUrl\Helper\Data');
            $optionCollection = $seoHelper->getOptionsArray();
            foreach ($optionCollection as $options) {
                if (!in_array($options['attribute_code'], $multipleAttribute, true)) {
                    $seoMultipleAttrs[$options['attribute_code']][] = $options['url_key'];
                }
            }

            $submitResult['singleAttrs'] = $seoMultipleAttrs;
        }

        return $submitResult;
    }

    /**
     * Is attribute expand by default
     *
     * @param $filter
     *
     * @return mixed
     */
    public function isExpand($filter)
    {
        $code   = $filter->getRequestVar();
        $config = $this->helper->getFilterConfig($code);
        if (isset($config['is_expand']) && ($config['is_expand'] != 2)) {
            return $config['is_expand'];
        }

        return $this->getLayerProperty($filter, LayerHelper::FIELD_IS_EXPAND);
    }

    /**
     * @param $filter
     * @param $field
     *
     * @return mixed
     */
    protected function getLayerProperty($filter, $field)
    {
        if ($filter->hasAttributeModel()) {
            $attribute = $filter->getAttributeModel();
            $this->prepareAttributeData($attribute);

            $fieldValue = $attribute->getData($field);
            if ($fieldValue !== null && $fieldValue != 2) {
                return $fieldValue;
            }
        }

        return $this->helper->getConfigGeneral($field);
    }

    /**
     * Prepare layer data from attribute additional data
     *
     * @param $attribute
     */
    public function prepareAttributeData($attribute)
    {
        if ($data = $attribute->getAdditionalData()) {
            $additionalData = $this->helper->unserialize($data);
            if (is_array($additionalData)) {
                $attribute->addData($additionalData);
            }
        }

        if ($attribute->getData(LayerHelper::FIELD_ALLOW_MULTIPLE) === null) {
            $attribute->setData(LayerHelper::FIELD_ALLOW_MULTIPLE, 2);
        }

        if ($attribute->getData(LayerHelper::FIELD_SEARCH_ENABLE) === null) {
            $attribute->setData(LayerHelper::FIELD_SEARCH_ENABLE, 2);
        }

        if ($attribute->getData(LayerHelper::FIELD_IS_EXPAND) === null) {
            $attribute->setData(LayerHelper::FIELD_IS_EXPAND, 2);
        }
    }

    /**
     * @inheritdoc
     */
    public function isMultiple($filter)
    {
        if ($filter->hasMultipleMode()) {
            return $filter->getMultipleMode();
        }

        if ($filter->hasAttributeModel()) {
            $attribute = $filter->getAttributeModel();
            if ($attribute->getFrontendInput() === 'price' || $attribute->getBackendType() === 'decimal') {
                return false;
            }
        }

        return $this->getLayerProperty($filter, LayerHelper::FIELD_ALLOW_MULTIPLE);
    }

    /**
     * @param AbstractFilter $filter
     * @param null $compareType
     *
     * @return bool|mixed|string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFilterType($filter, $compareType = null)
    {
        $type = LayerHelper::FILTER_TYPE_LIST;
        if ($filter->hasFilterType()) {
            $type = $filter->getFilterType();
        } elseif ($filter->hasAttributeModel()) {
            $attribute = $filter->getAttributeModel();
            $this->prepareAttributeData($attribute);

            $filterType = $attribute->getData(LayerHelper::FIELD_FILTER_TYPE);
            if (!$filterType) {
                switch ($attribute->getData('frontend_input')) {
                    case 'text':
                    case 'price':
                        $filterType = LayerHelper::FILTER_TYPE_SLIDER;
                        break;
                    case 'select':
                        if ($this->swatchHelper->isVisualSwatch($attribute)
                            || $this->swatchHelper->isTextSwatch($attribute)
                        ) {
                            $filterType = LayerHelper::FILTER_TYPE_SWATCH;
                        }
                        break;
                    case 'multiselect':
                        $filterType = LayerHelper::FILTER_TYPE_LIST;
                        break;
                }
            } elseif ($filter->getRequestVar() === 'cat'
                && $filter instanceof \Mageplaza\LayeredNavigation\Model\Layer\Filter\Category
                && $filter->isRenderCategoryTree()) {
                $type = LayerHelper::FILTER_TYPE_TREE;
            }

            $type = $filterType ?: $type;
        } elseif ($filter->getRequestVar() === 'cat'
            && $filter instanceof \Mageplaza\LayeredNavigation\Model\Layer\Filter\Category
            && $filter->isRenderCategoryTree()) {
            $type = LayerHelper::FILTER_TYPE_TREE;
        }

        return $compareType ? ($type == $compareType) : $type;
    }

    /**
     * Is search enable on filter
     *
     * @param $filter
     *
     * @return bool|mixed
     */
    public function isSearchEnable($filter)
    {
        if ($filter->hasSearchEnable()) {
            return $filter->getSearchEnable();
        }

        if ($filter->hasAttributeModel()) {
            $attribute = $filter->getAttributeModel();
            if (($attribute->getFrontendInput() === 'price') ||
                ($attribute->getBackendType() === 'decimal')
            ) {
                return false;
            }
        }

        return $this->getLayerProperty($filter, LayerHelper::FIELD_SEARCH_ENABLE);
    }

    /**
     * @inheritdoc
     */
    public function isShowCounter($filter)
    {
        return $this->helper->getConfigGeneral('show_counter');
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
     * @inheritdoc
     */
    public function isShowZero($filter)
    {
        return $this->helper->getConfigGeneral('show_zero');
    }
}
