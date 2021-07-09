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

use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Zend_Db_Expr;

/**
 * Class Brand
 * @package Mageplaza\Shopbybrand\Model
 */
class Brand extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_shopbybrand_brand';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_shopbybrand_brand';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_shopbybrand_brand';

    /**
     * @type Config
     */
    protected $eavConfig;

    /**
     * @type StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @type Helper
     */
    protected $helper;

    /**
     * @type CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Brand constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Config $eavConfig
     * @param Helper $helper
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $eavConfig,
        Helper $helper,
        CollectionFactory $attrOptionCollectionFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->registry = $registry;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Brand::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param null $storeId
     * @param array $conditions
     * @param null $sqlString
     * @param null $attributeId
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function getBrandCollection($storeId = null, $conditions = [], $sqlString = null, $attributeId = null)
    {
        $storeId = ($storeId === null) ? $this->_storeManager->getStore()->getId() : $storeId;

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->helper->getAttributeCode($attributeId));
        $collection = $this->_attrOptionCollectionFactory->create()
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($storeId);

        $connection = $collection->getConnection();
        $storeIdCondition = 0;
        if ($storeId) {
            $storeIdCondition = $connection->select()
                ->from(['ab' => $collection->getTable('mageplaza_brand')], 'MAX(ab.store_id)')
                ->where('ab.option_id = br.option_id AND ab.store_id IN (0, ' . $storeId . ')');
        }

        $collection->getSelect()
            ->joinLeft(
                ['br' => $collection->getTable('mageplaza_brand')],
                'main_table.option_id = br.option_id AND br.store_id = (' . $storeIdCondition . ')' . (is_string($conditions) ? $conditions : ''),
                [
                    'brand_id' => new Zend_Db_Expr($connection->getCheckSql(
                        'br.store_id = ' . $storeId,
                        'br.brand_id',
                        'NULL'
                    )),
                    'store_id' => new Zend_Db_Expr($storeId),
                    'page_title',
                    'url_key',
                    'short_description',
                    'description',
                    'is_featured',
                    'static_block',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'image'
                ]
            )
            ->joinLeft(
                ['sw' => $collection->getTable('eav_attribute_option_swatch')],
                'main_table.option_id = sw.option_id',
                ['swatch_type' => 'type', 'swatch_value' => 'value']
            )
            ->group('option_id');

        if (is_array($conditions)) {
            foreach ($conditions as $field => $condition) {
                $collection->addFieldToFilter($field, $condition);
            }
        }
        if ($sqlString) {
            $collection->getSelect()->where($sqlString);
        }

        return $collection;
    }

    /**
     * @param $optionId
     * @param null $store
     * @param null $attributeId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadByOption($optionId, $store = null, $attributeId = null)
    {
        $collection = $this->getBrandCollection($store, ['main_table.option_id' => $optionId], null, $attributeId);

        return $collection->getFirstItem();
    }
}
