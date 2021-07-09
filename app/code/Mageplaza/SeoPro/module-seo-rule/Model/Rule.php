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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SeoRule\Model\Rule\Condition\CombineFactory;

/**
 * Class Rule
 * @package Mageplaza\SeoRule\Model
 */
class Rule extends AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * Filter Layer Navigation
     */
    protected $filter;

    /**
     * Is use rule
     */
    protected $isUseRule;

    /**
     * Store matched product Ids in condition tab
     *
     * @var array
     */
    protected $productConditionsIds;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param ProductFactory $productFactory
     * @param CombineFactory $condCombineFactory
     * @param Iterator $resourceIterator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        Status $productStatus,
        Visibility $productVisibility,
        ProductFactory $productFactory,
        CombineFactory $condCombineFactory,
        Iterator $resourceIterator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->condCombineFactory       = $condCombineFactory;
        $this->registry                 = $registry;
        $this->resourceIterator         = $resourceIterator;
        $this->productFactory           = $productFactory;
        $this->storeManager             = $storeManager;
        $this->productVisibility        = $productVisibility;
        $this->productStatus            = $productStatus;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mageplaza\SeoRule\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get conditions instance
     * @return mixed
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get actions instance
     * @return mixed
     */
    public function getActionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get conditions field set id
     *
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection Collection */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->productFactory->create()
                ]
            );
        }

        return $this->productConditionsIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        if ($this->getConditions()->validate($product)) {
            $this->productConditionsIds[] = $product->getId();
        }
    }

    /**
     * @param $filter
     *
     * @return mixed
     */
    public function getMatchingLayerNavigation($filter)
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection Collection */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $productCollection->setPageSize(1);
            $this->filter = $filter;
            $this->resourceIterator->walk(
                $productCollection->getSelect()->limit(1),
                [[$this, 'callbackValidateLayerNavigation']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->productFactory->create()
                ]
            );
        }

        return $this->isUseRule;
    }

    /**
     * @param $args
     */
    public function callbackValidateLayerNavigation($args)
    {
        $this->isUseRule = false;
        $product         = clone $args['product'];
        unset($this->filter['id']);
        $product->setData($this->filter);
        if ($this->getConditions()->validate($product)) {
            $this->isUseRule = true;
        }
    }
}
