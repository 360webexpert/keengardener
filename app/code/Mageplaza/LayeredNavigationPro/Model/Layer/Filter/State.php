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

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Class State
 * @package Mageplaza\LayeredNavigationPro\Model\Layer\Filter
 */
class State extends AbstractFilter
{
    const OPTION_NEW   = 'new';
    const OPTION_SALE  = 'onsales';
    const OPTION_STOCK = 'stock';

    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $_moduleHelper;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $_localeDate;

    /** @var \Magento\CatalogInventory\Helper\Stock */
    protected $stockHelper;

    /** Filter Value */
    protected $filterValue;

    /**
     * State constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->_moduleHelper = $moduleHelper;
        $this->_localeDate = $localeDate;
        $this->stockHelper = $stockHelper;
        $this->_requestVar = 'state';
        $this->setData('search_enable', false);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = $this->filterValue = explode(',', $attributeValue);

        /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
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
                $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

                /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
                $collection
                    ->addAttributeToFilter('news_from_date', [
                        'or' => [
                            0 => ['date' => true, 'to' => $todayEndOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ], 'left')
                    ->addAttributeToFilter('news_to_date', [
                        'or' => [
                            0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ], 'left')
                    ->addAttributeToFilter([
                        ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                        ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                    ]);

                break;
            case self::OPTION_SALE:
                /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
                $collection->getSelect()->where('price_index.final_price < price_index.price');

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
            self::OPTION_NEW   => $this->_moduleHelper->getFilterConfig('state/new_label'),
            self::OPTION_SALE  => $this->_moduleHelper->getFilterConfig('state/onsales_label'),
            self::OPTION_STOCK => $this->_moduleHelper->getFilterConfig('state/stock_label'),
        ];

        if (array_key_exists($optionId, $options)) {
            return $options[$optionId];
        }

        return '';
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
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
        /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $stateConfig = $this->_moduleHelper->getFilterConfig('state');
        $checkCount = false;
        $itemData = [];
        $options = [self::OPTION_NEW, self::OPTION_SALE, self::OPTION_STOCK];
        foreach ($options as $option) {
            if (!$stateConfig[$option . '_enable']) {
                continue;
            }

            if ($this->filterValue && in_array($option, $this->filterValue)) {
                $count = $productCollection->getSize();
            } else {
                $productCollectionClone = clone $productCollection;
                $this->addFilterToCollection($option, $productCollectionClone);

                $count = $productCollectionClone->resetTotalRecords()->getSize();
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
