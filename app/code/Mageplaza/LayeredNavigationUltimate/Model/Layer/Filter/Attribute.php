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

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Helper\Data;
use Mageplaza\LayeredNavigation\Model\Layer\Filter\Attribute as ParentAttribute;

/**
 * Class Attribute
 * @package Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter
 */
class Attribute extends ParentAttribute
{
    /** @var Registry */
    protected $_coreRegistry;

    /**
     * Attribute constructor.
     *
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param StripTags $tagFilter
     * @param Data $moduleHelper
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        Data $moduleHelper,
        Registry $coreRegistry,
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
    public function apply(RequestInterface $request)
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
        $productListSort = $request->getParam(Toolbar::ORDER_PARAM_NAME);
        if ($sortConfig == 'position' || $productListSort == 'position') {
            $productsListDir = $request->getParam(Toolbar::DIRECTION_PARAM_NAME)
                ?: 'ASC';
            $this->getLayer()->getProductCollection()->setOrder('cat_index_position', $productsListDir);
        }

        return $this;
    }
}
