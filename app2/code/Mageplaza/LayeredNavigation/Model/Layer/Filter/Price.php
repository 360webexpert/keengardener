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

namespace Mageplaza\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer as LayerCatalog;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as CatalogPrice;
use Magento\CatalogSearch\Model\Layer\Filter\Price as AbstractFilter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;

/**
 * Class Price
 * @package Mageplaza\LayeredNavigation\Model\Layer\Filter
 */
class Price extends AbstractFilter
{
    /** @var LayerHelper */
    protected $_moduleHelper;

    /** @var array|null Filter value */
    protected $_filterVal = null;

    /** @var TaxHelper */
    protected $_taxHelper;

    /** @var LayerCatalog\Filter\DataProvider\Price */
    private $dataProvider;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param LayerCatalog $layer
     * @param DataBuilder $itemDataBuilder
     * @param CatalogPrice $resource
     * @param Session $customerSession
     * @param Algorithm $priceAlgorithm
     * @param PriceCurrencyInterface $priceCurrency
     * @param AlgorithmFactory $algorithmFactory
     * @param PriceFactory $dataProviderFactory
     * @param TaxHelper $taxHelper
     * @param LayerHelper $moduleHelper
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        LayerCatalog $layer,
        DataBuilder $itemDataBuilder,
        CatalogPrice $resource,
        Session $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceFactory $dataProviderFactory,
        TaxHelper $taxHelper,
        LayerHelper $moduleHelper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );

        $this->priceCurrency = $priceCurrency;
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->_moduleHelper = $moduleHelper;
        $this->_taxHelper = $taxHelper;
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::apply($request);
        }

        /** Filter must be string: $fromPrice-$toPrice */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }
        $filterParams = explode(',', $filter);
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        [$from, $to] = $this->_filterVal = $filter;

        $this->getLayer()->getProductCollection()->addFieldToFilter('price', [
            'from' => $from / $this->getCurrencyRate(),
            'to' => $to / $this->getCurrencyRate()
        ]);

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLayerLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _renderRangeLayerLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        }

        if ($fromPrice === $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        }

        return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
    }

    /**
     * Price Slider Configuration
     *
     * @return array
     */
    public function getSliderConfig()
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_filterVal) {
            /** @type Collection $productCollectionClone */
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch(['price.from', 'price.to']);
        }

        $min = $productCollection->getMinPrice();
        $max = $productCollection->getMaxPrice();

        [$from, $to] = $this->_filterVal ?: [$min, $max];
        $from = max(min($from, $max), $min);
        $to = min(max($to, $from), $max);

        $item = $this->getItems()[0];

        return [
            'selectedFrom' => $from,
            'selectedTo' => $to,
            'minValue' => $min,
            'maxValue' => $max,
            'priceFormat' => $this->_taxHelper->getPriceFormat(),
            'ajaxUrl' => $item->getUrl()
        ];
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws LocalizedException
     * @throws StateException
     */
    protected function _getItemsData()
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::_getItemsData();
        }

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_filterVal) {
            /** @type Collection $productCollectionClone */
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch(['price.from', 'price.to']);
        }

        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        $data = [];
        if (count($facets) >= 1) { // fix range of price with elasticsearch
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count);
            }
        }

        return $data;
    }

    /**
     * @param string $key
     * @param int $count
     *
     * @return array
     */
    private function prepareData($key, $count)
    {
        [$from, $to] = explode('_', $key);
        if ($from === '*') {
            $from = $this->getFrom($to);
        }
        if ($to === '*') {
            $to = $this->getTo($to);
        }
        $label = $this->_renderRangeLayerLabel(
            empty($from) ? 0 : $from * $this->getCurrencyRate(),
            empty($to) ? $to : $to * $this->getCurrencyRate()
        );
        $value = (float)$from * $this->getCurrencyRate() . '-' . (float)$to * $this->getCurrencyRate();

        return compact('label', 'value', 'count', 'from', 'to');
    }
}
