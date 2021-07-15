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

namespace Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Search\FilterMapper;

use DomainException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ResouceProduct;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomExclusionStrategy
 * @package Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Search\FilterMapper
 */
class CustomExclusionStrategy
{
    private $validFields = ['mp_is_new', 'mp_on_sale'];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var TimezoneInterface
     */
    private $localeDate;
    /**
     * @var EavConfig
     */
    private $eavConfig;
    /**
     * @var ResouceProduct
     */
    private $productResource;

    /**
     * @var string
     */
    private $productLinks;

    /**
     * CustomExclusionStrategy constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param TimezoneInterface $localeDate
     * @param EavConfig $eavConfig
     * @param ResouceProduct $productResource
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        TimezoneInterface $localeDate,
        EavConfig $eavConfig,
        ResouceProduct $productResource
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->localeDate = $localeDate;
        $this->eavConfig = $eavConfig;
        $this->productLinks = $productResource->getLinkField();
    }

    /**
     * @param FilterInterface $filter
     * @param Select $select
     *
     * @return bool
     * @throws LocalizedException
     */
    public function apply(
        FilterInterface $filter,
        Select $select
    ) {
        $isApplied = false;
        if (!in_array($filter->getField(), $this->validFields, true)) {
            return $isApplied;
        }
        switch ($filter->getField()) {
            case 'mp_on_sale':
                $isApplied = $this->applyOnSaleFilter($select);
                break;
            case 'mp_is_new':
                $isApplied = $this->applyIsNewFilter($select);
                break;
            default:
                $isApplied = false;
        }

        return $isApplied;
    }

    /**
     * Applies on_sale filter
     *
     * @param Select $select
     * @return bool
     * @throws DomainException
     * @throws LocalizedException
     */
    private function applyOnSaleFilter(Select $select)
    {
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $conditions = [
            "catalog_rule.product_id = {$mainTableAlias}.entity_id",
            '(catalog_rule.latest_start_date < NOW() OR catalog_rule.latest_start_date IS NULL)',
            '(catalog_rule.earliest_end_date > NOW() OR catalog_rule.earliest_end_date IS NULL)',
            "catalog_rule.website_id = '{$websiteId}'",
            "catalog_rule.customer_group_id = '{$customerGroupId}'"
        ];
        $select->joinLeft(
            ['catalog_rule' => $this->resourceConnection->getTableName('catalogrule_product_price')],
            implode(' AND ', $conditions),
            null
        );

        $select->joinLeft(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            "relation.child_id = {$mainTableAlias}.entity_id",
            ['parent_id' => 'relation.parent_id']
        );

        $priceIndexConditions = [
            "{$mainTableAlias}.entity_id = on_sale_price_index.entity_id",
            "on_sale_price_index.website_id = {$this->storeManager->getWebsite()->getId()}",
            "on_sale_price_index.customer_group_id = {$customerGroupId}"
        ];
        $select->joinInner(['on_sale_price_index' => $tableName], implode(" AND ", $priceIndexConditions), []);
        $select->where('ifnull(catalog_rule.rule_price, on_sale_price_index.final_price) < on_sale_price_index.price');

        return true;
    }

    /**
     * Applies is_new filter
     *
     * @param Select $select
     *
     * @return bool
     * @throws DomainException
     * @throws LocalizedException
     */
    private function applyIsNewFilter(Select $select)
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $mainTable = $this->resourceConnection->getTableName('catalog_product_entity_datetime');
        $storeId = $this->storeManager->getStore()->getId();

        $this->getJoinCondition('from', '', $mainTableAlias, $select, $mainTable, $storeId);
        $this->getJoinCondition('from', '_default', $mainTableAlias, $select, $mainTable, Store::DEFAULT_STORE_ID);
        $this->getJoinCondition('to', '', $mainTableAlias, $select, $mainTable, $storeId);
        $this->getJoinCondition('to', '_default', $mainTableAlias, $select, $mainTable, Store::DEFAULT_STORE_ID);

        $this->addWhere($select);

        return true;
    }

    /**
     * @param string $fromTo
     * @param string $valueOrDefault
     * @param string $mainTableAlias
     * @param $select
     * @param $mainTable
     * @param $storeId
     */
    private function getJoinCondition($fromTo, $valueOrDefault, $mainTableAlias, $select, $mainTable, $storeId)
    {
        $joinConditions = [
            "news_{$fromTo}_date_attribute{$valueOrDefault}.attribute_id =
            {$this->getAttributeId("news_{$fromTo}_date")}",
            sprintf(
                '%s.entity_id = news_%s_date_attribute%s.%s',
                $mainTableAlias,
                $fromTo,
                $valueOrDefault,
                $this->productLinks
            ),
            "news_{$fromTo}_date_attribute{$valueOrDefault}.store_id = " . $storeId
        ];

        $select->joinLeft(
            ["news_{$fromTo}_date_attribute{$valueOrDefault}" => $mainTable],
            implode(' AND ', $joinConditions),
            []
        );
    }

    /**
     * @param $select
     */
    private function addWhere($select)
    {
        $fromValueOrDefault = 'IF(news_from_date_attribute.value_id > 0, news_from_date_attribute.value,
                              news_from_date_attribute_default.value)';
        $toValueOrDefault = 'IF(news_to_date_attribute.value_id > 0, news_to_date_attribute.value,
                            news_to_date_attribute_default.value)';

        $whereConditions = [
            $fromValueOrDefault . ' IS NOT NULL',
            $toValueOrDefault . ' IS NOT NULL'
        ];
        $select->where(implode(' OR ', $whereConditions));

        $todayStartOfDayDate = $this->localeDate->date()
            ->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->localeDate->date()
            ->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $select->where("({$fromValueOrDefault} <= '{$todayEndOfDayDate}' OR {$fromValueOrDefault} IS NULL)");
        $select->where("({$toValueOrDefault} >= '{$todayStartOfDayDate}' OR {$toValueOrDefault} IS NULL)");
    }

    /**
     * Returns visibility attribute id
     *
     * @param string $attributeCode
     *
     * @return int
     * @throws LocalizedException
     */
    private function getAttributeId(string $attributeCode)
    {
        $attr = $this->eavConfig->getAttribute(
            Product::ENTITY,
            $attributeCode
        );

        return (int) $attr->getId();
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     *
     * @return string|null
     */
    private function extractTableAliasFromSelect(Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
