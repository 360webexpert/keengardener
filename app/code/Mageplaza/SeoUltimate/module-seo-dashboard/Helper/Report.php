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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Helper;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SeoDashboard\Model\Issue;
use Mageplaza\SeoDashboard\Model\IssueFactory;
use Mageplaza\SeoDashboard\Model\Low;
use Mageplaza\SeoDashboard\Model\LowFactory;
use Mageplaza\SeoDashboard\Model\Mediate;
use Mageplaza\SeoDashboard\Model\MediateFactory;
use Mageplaza\SeoDashboard\Model\NoRoute;
use Mageplaza\SeoDashboard\Model\NoRouteFactory;

/**
 * Class Report
 * @package Mageplaza\SeoDashboard\Helper
 */
class Report extends Data
{
    /**
     * @type ProductFactory
     */
    protected $_productFactory;

    /**
     * @type ProductRepository
     */
    protected $_productRepository;

    /**
     * @type CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @type CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @type PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Issue
     */
    protected $_issueModel;

    /**
     * @var Low
     */
    protected $_lowModel;

    /**
     * @var Mediate
     */
    protected $_mediateModel;

    /**
     * @var NoRoute
     */
    protected $_noRouteModel;

    /**
     * @type array
     */
    protected $storeIds;

    /**
     * @var string
     */
    protected $_entityType;

    /**
     * @var array
     */
    protected $_duplicateFields = [self::FRONTEND_IDENTIFY, self::META_DESCRIPTION, self::META_TITLE];

    /**
     * @var array
     */
    protected $_missingFields = [self::META_DESCRIPTION, self::META_TITLE];

    /**
     * @var array
     */
    protected $enableReport = [];

    /**
     * @var array
     */
    protected $applyFor = [];

    /**
     * Report constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepository $categoryRepository
     * @param PageFactory $pageFactory
     * @param MediateFactory $mediateFactory
     * @param IssueFactory $issueFactory
     * @param LowFactory $lowFactory
     * @param NoRouteFactory $noRouteFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        PageFactory $pageFactory,
        MediateFactory $mediateFactory,
        IssueFactory $issueFactory,
        LowFactory $lowFactory,
        NoRouteFactory $noRouteFactory
    ) {
        $this->_productFactory     = $productFactory;
        $this->_productRepository  = $productRepository;
        $this->_categoryFactory    = $categoryFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_pageFactory        = $pageFactory;
        $this->_mediateModel       = $mediateFactory->create();
        $this->_lowModel           = $lowFactory->create();
        $this->_issueModel         = $issueFactory->create();
        $this->_noRouteModel       = $noRouteFactory->create();

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Mapping Meta data to Seo dashboard report table
     *
     * @throws LocalizedException
     */
    public function mappingFieldsData()
    {
        $this->_mediateModel->deleteData();
        $this->_lowModel->deleteData();
        $this->_issueModel->deleteData();
        $this->_noRouteModel->deleteData();

        $this->mappingProduct();
        $this->mappingCategory();
        $this->mappingPage();

        foreach ([self::PRODUCT_ENTITY, self::CATEGORY_ENTITY, self::PAGE_ENTITY] as $entity) {
            $this->_entityType = $entity;
            $this->determineDuplicate();
            $this->determineMissing();
            $this->determineLowCount();
        }
    }

    /**
     * @param $entity
     * @param $type
     *
     * @return $this
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function reloadMediateTable($entity, $type)
    {
        $data = [];
        $this->_mediateModel->deleteData(['entity_id = ?' => $entity->getId(), 'entity = ?' => $type]);
        $this->_entityType = $type;

        foreach ($this->getAllStoreId() as $storeId) {
            if (!$this->isEnableReport($storeId)) {
                continue;
            }
            if ($type == self::CATEGORY_ENTITY) {
                $object = $this->_categoryRepository->get($entity->getId(), $storeId);
                if ($object->getIsActive()) {
                    $data[] = $this->getRecord($object, $storeId);
                }
            } elseif ($type == self::PRODUCT_ENTITY) {
                $object = $this->_productRepository->getById($entity->getId(), false, $storeId);
                if ($object->getStatus() && $object->isVisibleInSiteVisibility()) {
                    $data[] = $this->getRecord($object, $storeId);
                }
            } else {
                $entityStoreId = $entity->getStoreId();
                if ($entity->getIsActive() && (in_array(0, $entityStoreId) || in_array($storeId, $entityStoreId))) {
                    $this->_mediateModel->addData($this->getRecord($entity, $storeId))->save();
                }
            }
        }
        $this->_mediateModel->insertMultipleData($data);

        $this->determineDuplicate();
        $this->determineMissing($entity->getId());
        $this->determineLowCount($entity->getId());

        return $this;
    }

    /**
     * Determine missing meta data
     *
     * @param null $entityId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function determineMissing($entityId = null)
    {
        $arrCondition = [
            'issue_type = ?' => self::ISSUE_TYPE_MISSING,
            'entity = ?'     => $this->_entityType
        ];
        if ($entityId) {
            $arrCondition['entity_ids = ?'] = $entityId;
        }
        $this->_issueModel->deleteData($arrCondition);

        $data = [];
        foreach ($this->_missingFields as $field) {
            $collection = $this->_mediateModel->getCollection()
                ->addFieldToFilter('entity', $this->_entityType)
                ->addFieldToFilter($this->getFieldName($field), '');
            if ($entityId) {
                $collection->addFieldToFilter('entity_id', $entityId);
            }

            foreach ($collection as $component) {
                $data[] = [
                    'issue_type' => self::ISSUE_TYPE_MISSING,
                    'entity_ids' => $component['entity_id'],
                    'fields'     => $field,
                    'entity'     => $this->_entityType,
                    'store'      => $component['store_id']
                ];
            }

            if (count($data) > 1000) {
                $this->_issueModel->insertMultipleData($data);
                $data = [];
            }
        }

        if (count($data) > 0) {
            $this->_issueModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function determineDuplicate()
    {
        $this->_issueModel->deleteData([
            'issue_type = ?' => self::ISSUE_TYPE_DUPLICATE,
            'entity = ?'     => $this->_entityType
        ]);

        $data = [];
        foreach ($this->_duplicateFields as $field) {
            $collection = $this->_mediateModel
                ->getDuplicateCollection($this->getFieldName($field), $this->_entityType);
            foreach ($collection as $component) {
                if (!in_array($this->_entityType, explode(',', $this->getApplyFor($component['store_id'])))) {
                    continue;
                }

                $data[] = [
                    'issue_type' => self::ISSUE_TYPE_DUPLICATE,
                    'entity_ids' => $component['entity_ids'],
                    'fields'     => $field,
                    'entity'     => $this->_entityType,
                    'store'      => $component['store_id']
                ];
            }

            if (sizeof($data) > 1000) {
                $this->_issueModel->insertMultipleData($data);
                $data = [];
            }
        }

        if (sizeof($data)) {
            $this->_issueModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * @param null $entityId
     *
     * @throws LocalizedException
     */
    public function determineLowCount($entityId = null)
    {
        $this->_lowModel->deleteData($entityId ? ['entity_ids = ?' => $entityId] : []);

        switch ($this->_entityType) {
            case self::PRODUCT_ENTITY:
                $this->checkLowCount(self::DESCRIPTION, self::DESCRIPTION_WORD_COUNT_MINIMUM, $entityId);
                $this->checkLowCount(self::SHORT_DESCRIPTION, self::SHORT_DESCRIPTION_WORD_COUNT_MINIMUM, $entityId);
                break;
            case self::CATEGORY_ENTITY:
                $this->checkLowCount(self::DESCRIPTION, self::DESCRIPTION_WORD_COUNT_MINIMUM, $entityId);
                break;
            case self::PAGE_ENTITY:
                $this->checkLowCount(self::CONTENT, self::CONTENT_WORD_COUNT_MINIMUM, $entityId);
        }
    }

    /**
     * Check low count for entity
     *
     * @param $field
     * @param $condition
     * @param null $entityId
     *
     * @return $this
     */
    public function checkLowCount($field, $condition, $entityId = null)
    {
        $collection = $this->_mediateModel->getCollection()
            ->addFieldToFilter('entity', $this->_entityType);
        if ($entityId) {
            $collection->addFieldToFilter('entity_id', $entityId);
        }

        $data = [];

        foreach ($collection as $item) {
            $content = trim(str_replace('&nbsp;', ' ', strip_tags($item[$this->getFieldName($field)])));
            $count   = ($this->_entityType == self::PAGE_ENTITY) ? str_word_count($content) : strlen($content);
            if ($count < $condition) {
                $data[] = [
                    'entity_ids' => $item['entity_id'],
                    'fields'     => $field,
                    'count'      => $count,
                    'entity'     => $this->_entityType,
                    'store'      => $item['store_id']
                ];
            }
        }

        if (sizeof($data)) {
            $this->_lowModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * Mapping category
     * @return mixed
     */
    public function mappingPage()
    {
        $data           = [];
        $pageCollection = $this->_pageFactory->create()
            ->getCollection()
            ->addFieldToFilter('is_active', 1);
        $storeIds       = $this->getAllStoreId();

        /** @type Page $page */
        foreach ($pageCollection as $page) {
            foreach ($storeIds as $storeId) {
                if (!$this->isEnableReport($storeId)) {
                    continue;
                }
                $entityStoreId = $page->getStoreId();
                if ($page->getIsActive() && (in_array(0, $entityStoreId) || in_array($storeId, $entityStoreId))) {
                    $data[] = $this->getRecord($page, $storeId, self::PAGE_ENTITY);
                }
            }

            if (sizeof($data) > 1000) {
                $this->_mediateModel->insertMultipleData($data);
                $data = [];
            }
        }

        if (sizeof($data)) {
            $this->_mediateModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function mappingCategory()
    {
        $data               = [];
        $categoryCollection = $this->_categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToSelect('*');
        $storeIds           = $this->getAllStoreId();

        /** @type Category $category */
        foreach ($categoryCollection as $category) {
            foreach ($storeIds as $storeId) {
                if (!$this->isEnableReport($storeId)) {
                    continue;
                }
                $object = $this->_categoryRepository->get($category->getId(), $storeId);
                $data[] = $this->getRecord($object, $storeId, self::CATEGORY_ENTITY);
            }

            if (sizeof($data) > 1000) {
                $this->_mediateModel->insertMultipleData($data);
                $data = [];
            }
        }

        if (sizeof($data)) {
            $this->_mediateModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function mappingProduct()
    {
        $data              = [];
        $productCollection = $this->_productFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                'in' => [
                    Visibility::VISIBILITY_IN_SEARCH,
                    Visibility::VISIBILITY_BOTH
                ]
            ])
            ->addAttributeToSelect('*');

        /** @type Product $product */
        foreach ($productCollection as $product) {
            $storeIds = $product->getStoreIds();
            foreach ($storeIds as $storeId) {
                if (!$this->isEnableReport($storeId)) {
                    continue;
                }
                $object = $this->_productRepository->getById($product->getId(), false, $storeId);
                $data[] = $this->getRecord($object, $storeId, self::PRODUCT_ENTITY);
            }

            if (sizeof($data) > 1000) {
                $this->_mediateModel->insertMultipleData($data);
                $data = [];
            }
        }

        if (sizeof($data)) {
            $this->_mediateModel->insertMultipleData($data);
        }

        return $this;
    }

    /**
     * @param $object
     * @param $storeId
     * @param null $entityType
     *
     * @return array
     */
    public function getRecord($object, $storeId, $entityType = null)
    {
        $entityType = $entityType ?: $this->_entityType;

        $frontendIdentify = ($entityType == self::PAGE_ENTITY) ? $object->getTitle() : $object->getName();
        $description      = ($entityType == self::PAGE_ENTITY) ? $object->getContent() : $object->getDescription();
        $shortDescription = ($entityType == self::PRODUCT_ENTITY) ? $object->getShortDescription() : '';

        return [
            'entity_id'         => $object->getId(),
            'frontend_identity' => str_replace('"', '\"', $frontendIdentify),
            'meta_title'        => str_replace('"', '\"', $object->getMetaTitle()),
            'meta_description'  => str_replace('"', '\"', $object->getMetaDescription()),
            'description'       => str_replace('"', '\"', $description),
            'short_description' => str_replace('"', '\"', $shortDescription),
            'entity'            => $entityType,
            'store_id'          => $storeId
        ];
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    protected function isEnableReport($storeId)
    {
        if (!isset($this->enableReport[$storeId])) {
            $this->enableReport[$storeId] = $this->getDbReportConfig('enable', $storeId);
        }

        return $this->enableReport[$storeId];
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    protected function getApplyFor($storeId)
    {
        if (!isset($this->applyFor[$storeId])) {
            $this->applyFor[$storeId] = $this->getDbReportConfig('apply_for', $storeId);
        }

        return $this->applyFor[$storeId];
    }

    /**
     * Check table has data
     *
     * @return bool
     */
    public function isEmptyMediateData()
    {
        return !$this->_mediateModel->getCollection()->getSize();
    }

    /**
     * Get all store id
     *
     * @return array
     */
    public function getAllStoreId()
    {
        if (!$this->storeIds) {
            $this->storeIds = [];
            foreach ($this->storeManager->getStores() as $store) {
                $this->storeIds[] = $store->getId();
            }
        }

        return $this->storeIds;
    }
}
