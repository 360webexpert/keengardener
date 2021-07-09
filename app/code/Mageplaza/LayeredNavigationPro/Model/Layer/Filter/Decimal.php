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

namespace Mageplaza\LayeredNavigationPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\Decimal as AbstractFilter;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;

/**
 * Layer decimal filter
 */
class Decimal extends AbstractFilter
{
    /** @var \Mageplaza\LayeredNavigation\Helper\Data */
    protected $_moduleHelper;

    /** @var array|null Filter value */
    protected $_filterVal = null;

    /** @var  float Min value */
    protected $minValue;

    /** @var  float Max value */
    protected $maxValue;

    /** @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal */
    private $dataProvider;

    /**
     * Decimal constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory $dataProviderFactory
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory $dataProviderFactory,
        LayerHelper $moduleHelper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $priceCurrency,
            $dataProviderFactory,
            $data
        );

        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * @inheritdoc
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $this->setMinMaxValues();

        /**
         * Filter must be string: $index, $range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        list($from, $to) = $this->_filterVal = explode('-', $filter);

        $this->getLayer()
            ->getProductCollection()
            ->addFieldToFilter(
                $this->getAttributeModel()->getAttributeCode(),
                ['from' => $from, 'to' => $to]
            );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->renderRangeLabel($from, $to), $filter)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMinMaxValues()
    {
        $this->minValue = round($this->dataProvider->getMinValue($this), 2, PHP_ROUND_HALF_DOWN);
        $this->maxValue = round($this->dataProvider->getMaxValue($this), 2);
    }

    /**
     * @inheritdoc
     */
    protected function renderRangeLabel($from, $to)
    {
        if ($to === '') {
            return __('%1 and above', $from);
        } else {
            return __('%1 - %2', $from, $to);
        }
    }

    /**
     * Slider Configuration
     *
     * @return array
     */
    public function getSliderConfig()
    {
        list($from, $to) = $this->_filterVal ?: [$this->minValue, $this->maxValue];
        $from = ($from < $this->minValue) ? $this->minValue : $from;
        $to = ($to > $this->maxValue) ? $this->maxValue : $to;

        $item = $this->getItems()[0];

        return [
            "selectedFrom" => $from,
            "selectedTo"   => $to,
            "minValue"     => $this->minValue,
            "maxValue"     => $this->maxValue,
            "ajaxUrl"      => $item->getUrl()
        ];
    }

    /**
     * Retrieve data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $range = 10;
        $dbRanges = $this->getRangeItemCounts($range);

        foreach ($dbRanges as $index => $count) {
            $from = $range * ($index - 1);
            $to = $range * $index;

            $this->itemDataBuilder->addItemData(
                $this->renderRangeLabel($from, $to),
                $from . '-' . $to,
                $count
            );
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * @param $range
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getRangeItemCounts($range)
    {
        /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_filterVal) {
            /** @type \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollectionClone */
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch([
                    $this->getAttributeModel()->getAttributeCode() . '.from',
                    $this->getAttributeModel()->getAttributeCode() . '.to'
                ]);
        }

        // clone select from collection with filters
        $select = clone $productCollection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $attributeId = $this->getAttributeModel()->getId();
        $storeId = $productCollection->getStoreId();

        $select->join(
            ['decimal_index' => $productCollection->getTable('catalog_product_index_eav_decimal')],
            'e.entity_id = decimal_index.entity_id' . ' AND ' . $productCollection->getConnection()->quoteInto(
                'decimal_index.attribute_id = ?',
                $attributeId
            ) . ' AND ' . $productCollection->getConnection()->quoteInto(
                'decimal_index.store_id = ?',
                $storeId
            ),
            []
        );

        $countExpr = new \Zend_Db_Expr("COUNT(*)");
        $rangeExpr = new \Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(['decimal_range' => $rangeExpr, 'count' => $countExpr]);
        $select->group($rangeExpr);

        return $productCollection->getConnection()->fetchPairs($select);
    }
}
