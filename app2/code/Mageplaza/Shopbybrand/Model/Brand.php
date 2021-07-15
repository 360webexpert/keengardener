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

use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Api\Data\BrandInterface;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Zend_Db_Expr;

/**
 * Class Brand
 * @package Mageplaza\Shopbybrand\Model
 */
class Brand extends AbstractModel implements BrandInterface
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
     * @var EavAttribute
     */
    protected $eavAttribute;

    /**
     * Brand constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EavAttribute $eavAttribute
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
        EavAttribute $eavAttribute,
        Helper $helper,
        CollectionFactory $attrOptionCollectionFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
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
     * @param array|string $conditions
     * @param null $sqlString
     *
     * @return Collection
     */
    public function getBrandCollection($storeId = null, $conditions = [], $sqlString = null)
    {
        $storeId = ($storeId === null) ? $this->helper->getStoreId() : $storeId;

        $attributeId = $this->eavAttribute->getIdByCode('catalog_product', $this->helper->getAttributeCode($storeId));
        $collection = $this->_attrOptionCollectionFactory->create()
            ->setPositionOrder('asc')
            ->setAttributeFilter($attributeId)
            ->setStoreFilter();

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
                'main_table.option_id = br.option_id
                AND br.store_id = (' . $storeIdCondition . ')' . (is_string($conditions) ? $conditions : ''),
                [
                    'brand_id' => new Zend_Db_Expr($connection->getCheckSql(
                        'br.store_id = ' . $storeId,
                        'br.brand_id',
                        'NULL'
                    )),
                    'brand_id',
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
            ->group('main_table.option_id')->order('tdv.value');

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
     *
     * @return mixed
     */
    public function loadByOption($optionId, $store = null)
    {
        $collection = $this->getBrandCollection($store, ['main_table.option_id' => $optionId]);

        return $collection->setPageSize(1)->setCurPage(1)->getFirstItem();
    }

    /**
     * @return int
     */
    public function getOptionId()
    {
        return $this->_getData(self::OPTION_ID);
    }

    /**
     * @return mixed|string|null
     */
    public function getPageTitle()
    {
        return $this->_getData(self::PAGE_TITLE);
    }

    /**
     * @return mixed|string|null
     */
    public function getUrlKey()
    {
        return $this->_getData(self::URL_KEY);
    }

    /**
     * @return mixed|string|null
     */
    public function getImage()
    {
        return $this->_getData(self::IMAGE);
    }

    /**
     * @return mixed|string|null
     */
    public function getShortDescription()
    {
        return $this->_getData(self::SHORT_DESCRIPTION);
    }

    /**
     * @return mixed|string|null
     */
    public function getDescription()
    {
        return $this->_getData(self::DESCRIPTION);
    }

    /**
     * @return int|mixed|null
     */
    public function getIsFeatured()
    {
        return $this->_getData(self::IS_FEATURED);
    }

    /**
     * @return mixed|string|null
     */
    public function getStaticBlock()
    {
        return $this->_getData(self::STATIC_BLOCK);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaTitle()
    {
        return $this->_getData(self::META_TITLE);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaKeywords()
    {
        return $this->_getData(self::META_KEYWORDS);
    }

    /**
     * @return mixed|string|null
     */
    public function getMetaDescription()
    {
        return $this->_getData(self::META_DESCRIPTION);
    }

    /**
     *
     * @param int $id
     *
     * @return $this
     */
    public function setOptionId($id)
    {
        return $this->setData(self::OPTION_ID, $id);
    }

    /**
     * @param string $title
     *
     * @return BrandInterface|Brand
     */
    public function setPageTitle($title)
    {
        return $this->setData(self::PAGE_TITLE, $title);
    }

    /**
     * @param string $image
     *
     * @return BrandInterface|Brand
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setDescription($value)
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setShortDescription($value)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $value);
    }

    /**
     * @param int $value
     *
     * @return BrandInterface|Brand
     */
    public function setIsFeatured($value)
    {
        return $this->setData(self::IS_FEATURED, $value);
    }

    /**
     * @param string $url
     *
     * @return BrandInterface|Brand
     */
    public function setUrlKey($url)
    {
        return $this->setData(self::URL_KEY, $url);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setStaticBlock($value)
    {
        return $this->setData(self::STATIC_BLOCK, $value);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setMetaTitle($value)
    {
        return $this->setData(self::META_TITLE, $value);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setMetaDescription($value)
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    /**
     * @param string $value
     *
     * @return BrandInterface|Brand
     */
    public function setMetaKeywords($value)
    {
        return $this->setData(self::META_KEYWORDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData(self::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->getData(self::STORE_LABELS);
    }

    /**
     * Set option label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param string $value
     *
     * @return string
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Set option order
     *
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * set is default
     *
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
    }

    /**
     * Set option label for store scopes
     *
     * @param AttributeOptionLabelInterface[] $storeLabels
     *
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null)
    {
        return $this->setData(self::STORE_LABELS, $storeLabels);
    }

    /**
     * @inheritdoc
     */
    public function getProductQuantity()
    {
        return $this->getData(self::PRODUCT_QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setProductQuantity($quantity)
    {
        return $this->setData(self::PRODUCT_QUANTITY, $quantity);
    }
}
