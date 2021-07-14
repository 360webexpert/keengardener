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

namespace Mageplaza\LayeredNavigationPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;
use Mageplaza\LayeredNavigationPro\Helper\Data;
use Zend_Db_Expr;

/**
 * Class State
 * @package Mageplaza\LayeredNavigationPro\Model\Layer\Filter
 */
class State extends AbstractFilter
{
    const OPTION_NEW   = 'new';
    const OPTION_SALE  = 'onsales';
    const OPTION_STOCK = 'stock';

    /** @var Data */
    protected $_moduleHelper;

    /** @var TimezoneInterface */
    protected $_localeDate;

    /** @var Stock */
    protected $stockHelper;

    /** Filter Value */
    protected $filterValue;

    /**
     * State constructor.
     *
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param Data $moduleHelper
     * @param TimezoneInterface $localeDate
     * @param Stock $stockHelper
     * @param array $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Data $moduleHelper,
        TimezoneInterface $localeDate,
        Stock $stockHelper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->_moduleHelper = $moduleHelper;
        $this->_localeDate   = $localeDate;
        $this->stockHelper   = $stockHelper;
        $this->_requestVar   = 'state';
        $this->setData('search_enable', false);
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = $this->filterValue = explode(',', $attributeValue);

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        foreach ($attributeValue as $value) {
            $this->addFilterToCollection($value, $productCollection);
        }

        $state = $this->getLayer()->getState();
        foreach ($attributeValue as $value) {
            $label = $this->getOptionText($value);
            if (!$label) {
                continue;
            }

            $state->addFilter($this->_createItem($label, $value));
        }

        return $this;
    }

    /**
     * @param $type
     * @param $collection
     *
     * @return mixed
     */
    protected function addFilterToCollection($type, $collection)
    {
        switch ($type) {
            case self::OPTION_NEW:
                /** @var Collection $collection */
                $collection->addFieldToFilter('mp_is_new', 1);

                break;
            case self::OPTION_SALE:
                /** @var Collection $collection */
                $collection->addFieldToFilter('mp_on_sale', 1);

                break;
            case self::OPTION_STOCK:
                $this->stockHelper->addInStockFilterToCollection($collection);

                break;
        }

        return $collection;
    }

    /**
     * @param int $optionId
     *
     * @return string
     */
    protected function getOptionText($optionId)
    {
        $options = [
            self::OPTION_NEW   => $this->_moduleHelper->getFilterConfig('state/new_label') ?: 'New',
            self::OPTION_SALE  => $this->_moduleHelper->getFilterConfig('state/onsales_label') ?: 'On Sales',
            self::OPTION_STOCK => $this->_moduleHelper->getFilterConfig('state/stock_label') ?: 'In Stock',
        ];

        if (array_key_exists($optionId, $options)) {
            return $options[$optionId];
        }

        return '';
    }

    /**
     * Get filter name
     *
     * @return Phrase
     */
    public function getName()
    {
        return $this->_moduleHelper->getFilterConfig('state/label') ?: __('Product State');
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $stateConfig = $this->_moduleHelper->getFilterConfig('state');
        $checkCount  = false;
        $itemData    = [];
        $options     = [self::OPTION_NEW, self::OPTION_SALE, self::OPTION_STOCK];
        foreach ($options as $option) {
            if (!$stateConfig[$option . '_enable']) {
                continue;
            }

            if ($this->filterValue && in_array($option, $this->filterValue)) {
                $count = $productCollection->getSize();
            } else {
                $productCollectionClone = clone $productCollection;
                if ($option === self::OPTION_SALE) {
                    $optionsFacetedData = $productCollection->getFacetedData('mp_on_sale');
                    $count = isset($optionsFacetedData[1]) ? $optionsFacetedData[1]['count'] : 0;
                } elseif ($option === self::OPTION_NEW) {
                    $optionsFacetedData = $productCollection->getFacetedData('mp_is_new');
                    $count = isset($optionsFacetedData[1]) ? $optionsFacetedData[1]['count'] : 0;
                } else {
                    $this->addFilterToCollection($option, $productCollectionClone);
                    $count = $productCollectionClone->resetTotalRecords()->getSize();
                }
            }

            if ($count == 0 && !$this->_moduleHelper->getFilterModel()->isShowZero($this)) {
                continue;
            }

            if ($count > 0) {
                $checkCount = true;
            }

            $itemData[] = [
                'label' => $this->getOptionText($option),
                'value' => $option,
                'count' => $count
            ];
        }

        if ($checkCount) {
            foreach ($itemData as $item) {
                $this->itemDataBuilder->addItemData($item['label'], $item['value'], $item['count']);
            }
        }

        return $this->itemDataBuilder->build();
    }
}
