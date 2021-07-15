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
use Mageplaza\Shopbybrand\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class Category
 *
 * @package Mageplaza\Shopbybrand\Model
 */
class Category extends AbstractModel
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
}
