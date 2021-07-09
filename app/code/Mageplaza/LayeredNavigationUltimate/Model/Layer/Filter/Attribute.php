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

use Mageplaza\LayeredNavigation\Model\Layer\Filter\Attribute as ParentAttribute;

/**
 * Class Attribute
 * @package Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter
 */
class Attribute extends ParentAttribute
{
    /** @var \Magento\Framework\Registry */
    protected $_coreRegistry;

    /**
     * Attribute constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $moduleHelper,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        parent::apply($request);

        if (!$this->_moduleHelper->isEnabled()) {
            return $this;
        }

        $defaultParams = $this->_coreRegistry->registry('current_product_page_params');
        if (is_array($defaultParams) && sizeof($defaultParams)) {
            if (array_key_exists($this->getRequestVar(), $defaultParams)) {
                $this->setItems([]);

                $state = $this->getLayer()->getState();
                $filters = $state->getFilters();
                foreach ($filters as $key => $item) {
                    $filter = $item->getFilter();
                    if ($filter->getRequestVar() == $this->getRequestVar()) {
                        unset($filters[$key]);
                    }
                }
                $state->setFilters($filters);
            }
        }

        return $this;
    }

    /**
     * set order for products collection
     *
     * @param $request
     *
     * @return $this;
     */
    public function setProductCollectionOrder($request)
    {
        $sortConfig = $this->_moduleHelper->getConfigValue('catalog/frontend/default_sort_by');
        $productListSort = $request->getParam(\Magento\Catalog\Model\Product\ProductList\Toolbar::ORDER_PARAM_NAME);
        if ($sortConfig == 'position' || $productListSort == 'position') {
            $productsListDir = $request->getParam(\Magento\Catalog\Model\Product\ProductList\Toolbar::DIRECTION_PARAM_NAME)
                ?: 'ASC';
            $this->getLayer()->getProductCollection()->setOrder('cat_index_position', $productsListDir);
        }

        return $this;
    }
}
