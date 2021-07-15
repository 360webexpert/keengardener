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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class Category
 *
 * @package Mageplaza\Shopbybrand\Model
 */
class Category extends AbstractModel implements BrandCategoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var string
     */
    protected $tableBrandCategory;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $categoryCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceConnection $resourceConnection,
        CollectionFactory $categoryCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->tableBrandCategory = $resourceConnection->getTableName('mageplaza_shopbybrand_brand_category');
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Category::class);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getData('enable');
    }

    /**
     * @param null $whereCond
     * @param null $groupCond
     *
     * @return ResourceModel\Category\Collection
     */
    public function getCategoryCollection($whereCond = null, $groupCond = null)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->getSelect()->joinInner(
            ['brand_cat_tbl' => $this->tableBrandCategory],
            'main_table.cat_id = brand_cat_tbl.cat_id'
        );
        if ($whereCond !== null) {
            $collection->getSelect()->where($whereCond);
        }
        if ($groupCond !== null) {
            $collection->getSelect()->group($groupCond);
        }

        return $collection;
    }

    /**
     * @return string|null
     */
    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * @param string $store
     *
     * @return $this
     */
    public function setStoreIds($store)
    {
        return $this->setData(self::STORE_IDS, $store);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrlKey($url)
    {
        return $this->setData(self::URL_KEY, $url);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string $value
     *
     * @return BrandCategoryInterface|$this
     */
    public function setMetaTitle($value)
    {
        return $this->setData(self::META_TITLE, $value);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return BrandCategoryInterface|$this
     */
    public function setMetaDescription($value)
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    /**
     * @param string $value
     *
     * @return BrandCategoryInterface|$this
     */
    public function setMetaKeywords($value)
    {
        return $this->setData(self::META_KEYWORDS, $value);
    }

    /**
     * @return int|null
     */
    public function getMetaRobots()
    {
        return $this->getData(self::META_ROBOTS);
    }

    /**
     * @param int $value
     *
     * @return BrandCategoryInterface|$this
     */
    public function setMetaRobots($value)
    {
        return $this->setData(self::META_ROBOTS, $value);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set product created date
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set product updated date
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
