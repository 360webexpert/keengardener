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

namespace Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Adapter\Mysql\Aggregation;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class DataProvider
 * @package Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Adapter\Mysql\Aggregation
 */
class DataProvider
{

    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * DataProvider constructor.
     *
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $localeDate
    ){

        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param \Closure $proceed
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return \Magento\Framework\DB\Select|mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        if ($bucket->getField() === 'mp_on_sale') {
            $isOnSaleEnabled = $this->scopeConfig->isSetFlag(
                'layered_navigation/filter/state/onsales_enable',
                ScopeInterface::SCOPE_STORE
            );
            if ($isOnSaleEnabled) {
                return $this->addOnSaleAggregation($entityIdsTable, $dimensions);
            }
        }
        if ($bucket->getField() === 'mp_is_new') {
            $isNewEnabled = $this->scopeConfig->isSetFlag(
                'layered_navigation/filter/state/new_enable',
                ScopeInterface::SCOPE_STORE
            );
            if ($isNewEnabled) {
                return $this->addIsNewAggregation($entityIdsTable, $dimensions);
            }
        }

        return $proceed($bucket, $dimensions, $entityIdsTable);
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addOnSaleAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection->addStoreFilter($currentScopeId);
        $collection->addPriceData();
        $select = $collection->getSelect();
        if ($collection->getLimitationFilters()->isUsingPriceIndex()) {
            $select->where('price_index.final_price < price_index.price');
        }

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('e.entity_id');

        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $derivedTable->joinLeft(
            ['mp_on_sale' => $collection->getSelect()],
            'mp_on_sale.entity_id  = entities.entity_id',
            [
                'value' => new \Zend_Db_Expr("if(mp_on_sale.entity_id is null, 0, 1)")
            ]
        );

        $derivedTable->group('entities.entity_id');

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);

        return $select;
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addIsNewAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection->addStoreFilter($currentScopeId);
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate   = $this->localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $collection->addAttributeToFilter('news_from_date', [
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

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('e.entity_id');

        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $derivedTable->joinLeft(
            ['mp_is_new' => $collection->getSelect()],
            'mp_is_new.entity_id  = entities.entity_id',
            [
                'value' => new \Zend_Db_Expr("if(mp_is_new.entity_id is null, 0, 1)")
            ]
        );

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);

        return $select;
    }
}
