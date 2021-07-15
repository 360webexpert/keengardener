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

namespace Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Model\Search\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zend_Db_Exception;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /** @var Collection|null Clone collection */
    public $collectionClone = null;

    /** @var string */
    private $queryText;

    /** @var string|null */
    private $order = null;

    /** @var string */
    private $searchRequestName;

    /** @var TemporaryStorageFactory */
    private $temporaryStorageFactory;

    /** @var SearchInterface */
    private $search;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var SearchResultInterface */
    private $searchResult;

    /** @var FilterBuilder */
    private $filterBuilder;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var array
     */
    private $searchOrders;
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;
    /**
     * @var TotalRecordsResolverFactory
     */
    protected $totalRecordsResolverFactory;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EavEntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $productFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param TemporaryStorageFactory $tempStorageFactory
     * @param Http $request
     * @param AdapterInterface|null $connection
     * @param string $searchRequestName
     * @param SearchResultFactory|null $searchResultFactory
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $productFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        TemporaryStorageFactory $tempStorageFactory,
        Http $request,
        TotalRecordsResolverFactory $totalRecordsResolver,
        AdapterInterface $connection = null,
        $searchRequestName = 'catalog_view_container',
        SearchResultFactory $searchResultFactory = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $productFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );

        $this->temporaryStorageFactory = $tempStorageFactory;
        $this->searchRequestName = $searchRequestName;
        $this->request = $request;
        $this->searchResultFactory = $searchResultFactory ?? ObjectManager::getInstance()
                ->get(SearchResultFactory::class);
        $this->totalRecordsResolverFactory = $totalRecordsResolver;
    }

    /**
     * MP LayerNavigation Clone collection
     *
     * @return Collection
     */
    public function getCollectionClone()
    {
        if ($this->collectionClone === null) {
            $this->collectionClone = clone $this;
            $this->collectionClone->setSearchCriteriaBuilder($this->searchCriteriaBuilder->cloneObject());
        }

        $searchCriterialBuilder = $this->collectionClone->getSearchCriteriaBuilder()->cloneObject();

        /** @var Collection $collectionClone */
        $collectionClone = clone $this->collectionClone;
        $collectionClone->setSearchCriteriaBuilder($searchCriterialBuilder);

        return $collectionClone;
    }

    /**
     * MP LayerNavigation Add multi-filter categories
     *
     * @param $categories
     *
     * @return $this
     */
    public function addLayerCategoryFilter($categories)
    {
        if ((strpos($this->getSearchEngine(), 'elasticsearch') !== false)
            || $this->getSearchEngine() === 'amasty_elastic'
        ) {
            $this->addFieldToFilter('category_ids', ['in' => $categories]);
        } else {
            $this->addFieldToFilter('category_ids', implode(',', $categories));
        }

        return $this;
    }

    /**
     * MP LayerNavigation remove filter to load option item data
     *
     * @param $attributeCode
     *
     * @return $this
     */
    public function removeAttributeSearch($attributeCode)
    {
        if (is_array($attributeCode)) {
            foreach ($attributeCode as $attCode) {
                $this->searchCriteriaBuilder->removeFilter($attCode);
            }
        } else {
            $this->searchCriteriaBuilder->removeFilter($attributeCode);
        }

        $this->_isFiltersRendered = false;

        return $this->loadWithFilter();
    }

    /**
     * @param $categoryIds
     *
     * @return $this
     */
    public function resetCategoryFilter($categoryIds)
    {
        $this->searchCriteriaBuilder->setCategoryFilter($categoryIds);

        return $this;
    }

    /**
     * @param $categoryIds
     *
     * @return Collection
     */
    public function setCategoryFilter($categoryIds)
    {
        $this->searchCriteriaBuilder->setCategoryFilter($categoryIds);
        $this->_isFiltersRendered = false;

        return $this->loadWithFilter();
    }

    /**
     * MP LayerNavigation Get attribute condition sql
     *
     * @param $attribute
     * @param $condition
     * @param string $joinType
     *
     * @return string
     */
    public function getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        return $this->_getAttributeConditionSql($attribute, $condition, $joinType);
    }

    /**
     * MP LayerNavigation Reset Total records
     *
     * @return $this
     */
    public function resetTotalRecords()
    {
        $this->_totalRecords = null;

        return $this;
    }

    /**
     * @return SearchInterface
     * @deprecated
     */
    private function getSearch()
    {
        if ($this->search === null) {
            $this->search = ObjectManager::getInstance()->get(SearchInterface::class);
        }

        return $this->search;
    }

    /**
     * @param SearchInterface $object
     *
     * @return void
     * @deprecated
     *
     */
    public function setSearch(SearchInterface $object)
    {
        $this->search = $object;
    }

    /**
     * @return SearchCriteriaBuilder
     * @deprecated
     */
    public function getSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
        }

        return $this->searchCriteriaBuilder;
    }

    /**
     * @param SearchCriteriaBuilder $object
     */
    public function setSearchCriteriaBuilder(SearchCriteriaBuilder $object)
    {
        $this->searchCriteriaBuilder = $object;
    }

    /**
     * @return FilterBuilder
     * @deprecated
     */
    private function getFilterBuilder()
    {
        if ($this->filterBuilder === null) {
            $this->filterBuilder = ObjectManager::getInstance()->get(FilterBuilder::class);
        }

        return $this->filterBuilder;
    }

    /**
     * @param FilterBuilder $object
     *
     * @return void
     * @deprecated
     *
     */
    public function setFilterBuilder(FilterBuilder $object)
    {
        $this->filterBuilder = $object;
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->searchResult !== null) {
            throw new RuntimeException('Illegal state');
        }

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();

        if (isset($condition['in'])
            && (strpos($this->getSearchEngine(), 'elasticsearch') !== false
                || $this->getSearchEngine() === 'amasty_elastic')
        ) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition['in']);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } elseif (!is_array($condition) || !in_array(key($condition), ['from', 'to'])) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } else {
            if (!empty($condition['from'])) {
                $this->filterBuilder->setField("{$field}.from");
                $this->filterBuilder->setValue($condition['from']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
            if (!empty($condition['to'])) {
                $this->filterBuilder->setField("{$field}.to");
                $this->filterBuilder->setValue($condition['to']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
        }

        return $this;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     *
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        $this->setSearchOrder($attribute, $dir);
        if ($attribute === 'relevance') {
            $this->order[$attribute] = $dir;
        }

        return $this;
    }

    /**
     * Add attribute to sort order.
     *
     * @param string $attribute
     * @param string $dir
     *
     * @return $this
     * @since 101.0.2
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $this->setOrder($attribute, $dir);

        return $this;
    }

    /**
     * Set sort order for search query.
     *
     * @param string $field
     * @param string $direction
     *
     * @return void
     */
    private function setSearchOrder($field, $direction)
    {
        $field = (string)$this->_getMappedField($field);
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;

        $this->searchOrders[$field] = $direction;
    }

    /**
     * @throws LocalizedException
     * @throws Zend_Db_Exception
     */
    protected function _renderFiltersBefore()
    {
        $this->getCollectionClone();

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();
        $this->getSearch();

        if ($this->searchRequestName !== 'quick_search_container'
            || trim($this->queryText) !== ''
        ) {
            if ($this->queryText) {
                $this->filterBuilder->setField('search_term');
                $this->filterBuilder->setValue($this->queryText);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }

            $priceRangeCalculation = $this->_scopeConfig->getValue(
                AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
                ScopeInterface::SCOPE_STORE
            );
            if ($priceRangeCalculation) {
                $this->filterBuilder->setField('price_dynamic_algorithm');
                $this->filterBuilder->setValue('auto');
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
            if ($this->request->getFullActionName() === 'catalogsearch_result_index') {
                $this->searchRequestName = 'quick_search_container';
                $this->filterBuilder->setField('visibility');
                $this->filterBuilder->setValue([3, 4]);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchCriteria->setRequestName($this->searchRequestName);
            $searchCriteria->setSortOrders($this->searchOrders);
            $searchCriteria->setCurrentPage((int)$this->_curPage);

            try {
                $this->searchResult = $this->getSearch()->search($searchCriteria);
                $this->_totalRecords = $this->searchResult->getTotalCount();
            } catch (EmptyRequestDataException $e) {
                $this->searchResult = $this->createEmptyResult();
            } catch (Exception $e) {
                throw new LocalizedException(__('Sorry, something went wrong. You can find out more in the error log.'));
            }
        } else {
            $this->searchResult = $this->createEmptyResult();
        }

        if (strpos($this->getSearchEngine(), 'elasticsearch') !== false) {
            $this->ElasticSearchApply();
        } else {
            $this->CatalogSearchApply();
        }

        parent::_renderFiltersBefore();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _renderFilters()
    {
        $this->_filters = [];

        return parent::_renderFilters();
    }

    /**
     * sort product before load
     */
    protected function _beforeLoad()
    {
        $this->setOrder('entity_id');

        return parent::_beforeLoad();
    }

    /**
     * Create empty search result
     *
     * @return SearchResultInterface
     */
    private function createEmptyResult()
    {
        return $this->searchResultFactory->create()->setItems([]);
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    protected function CatalogSearchApply()
    {
        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table = $temporaryStorage->storeDocuments($this->searchResult->getItems());

        $this->getSelect()->joinInner(
            [
                'search_result' => $table->getName(),
            ],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
            []
        );
        if ($this->order && isset($this->order['relevance'])) {
            $this->getSelect()->order(
                'search_result.' . TemporaryStorage::FIELD_SCORE . ' ' . $this->order['relevance']
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function ElasticSearchApply()
    {
        if (empty($this->searchResult->getItems())) {
            $this->getSelect()->where('NULL');

            return;
        }

        $ids = [];
        foreach ($this->searchResult->getItems() as $item) {
            $ids[] = (int)$item->getId();
        }
        $this->getSelect()
            ->where('e.entity_id IN (?)', $ids);
        $sortOrder = $this->searchResult->getSearchCriteria()
            ->getSortOrders();
        if (!empty($sortOrder['price']) && $this->getLimitationFilters()->isUsingPriceIndex()) {
            $sortDirection = $sortOrder['price'];
            $this->getSelect()
                ->order(
                    new \Zend_Db_Expr("price_index.min_price = 0, price_index.min_price {$sortDirection}")
                );
        } else {
            $orderList = implode(',', $ids);
            $this->getSelect()
                ->order(new \Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
        }
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     *
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $result = [];

        $aggregations = $this->searchResult->getAggregations();
        // This behavior is for case with empty object when we got EmptyRequestDataException
        if (null !== $aggregations) {
            $bucket = $aggregations->getBucket($field . RequestGenerator::BUCKET_SUFFIX);
            if ($bucket) {
                foreach ($bucket->getValues() as $value) {
                    $metrics = $value->getMetrics();
                    $result[$metrics['value']] = $metrics;
                }
            } else {
                throw new StateException(__('Bucket does not exist'));
            }
        }

        return $result;
    }

    /**
     * Specify category filter for product collection
     *
     * @param Category $category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function addCategoryFilter(Category $category)
    {
        $this->addFieldToFilter('category_ids', $category->getId());

        return parent::addCategoryFilter($category);
    }

    /**
     * @param string $categoryIds
     *
     * @return $this
     */
    public function resetCategoryIds($categoryIds)
    {
        $this->_productLimitationFilters['category_id'] = $categoryIds;
        if ($this->getStoreId() == Store::DEFAULT_STORE_ID) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            $this->_applyProductLimitations();
        }

        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);

        return parent::setVisibility($visibility);
    }

    /**
     * Get Search Engine Config
     *
     * @return string
     */
    public function getSearchEngine()
    {
        return $this->_scopeConfig->getValue(Custom::XML_PATH_CATALOG_SEARCH_ENGINE, ScopeInterface::SCOPE_STORE);
    }
}
