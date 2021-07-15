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

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Zend_Db_Expr;

/**
 * Class OnSale
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class OnSale implements DataMapperInterface
{
    const FIELD_NAME = 'mp_on_sale';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var CustomerGroupCollectionFactory
     */
    protected $customerGroupCollection;

    protected $onSaleProductIds = [];
    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * OnSale constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerGroupCollectionFactory $customerGroupCollection
     * @param Configurable $configurable
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CustomerGroupCollectionFactory $customerGroupCollection,
        Configurable $configurable
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager             = $storeManager;
        $this->scopeConfig              = $scopeConfig;
        $this->customerGroupCollection  = $customerGroupCollection;
        $this->configurable             = $configurable;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $customerGroupCollection = $this->getCustomerGroup();
        $mapperData              = [];
        $websiteId               = $this->storeManager->getStore($storeId)->getWebsiteId();
        foreach ($customerGroupCollection as $customerGroup) {
            $mapperData[self::FIELD_NAME . '_' . $customerGroup->getId() . '_' . $websiteId] = (int) $this->isProductOnSale($entityId,
                $storeId, $customerGroup->getId());;
        }

        return $mapperData;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag(
            'layered_navigation/filter/state/onsales_enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @param int $groupId
     *
     * @return bool
     */
    private function isProductOnSale($entityId, $storeId, $groupId)
    {
        $onSaleProducts = $this->getOnSaleProductIds($storeId);
        if (isset($onSaleProducts[$entityId])) {
            $customerGroupIds = $onSaleProducts[$entityId];

            return empty($customerGroupIds) || in_array($groupId, array_values($customerGroupIds));
        }

        return false;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    protected function getOnSaleProductIds($storeId)
    {
        if (!isset($this->onSaleProductIds[$storeId])) {
            $this->onSaleProductIds[$storeId] = [];
            $customerGroupCollection          = $this->getCustomerGroup();
            foreach ($customerGroupCollection as $item) {
                $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);

                $collection->addPriceData($item->getId());
                $select = $collection->getSelect();
                $select->where('price_index.final_price < price_index.price');
                $select->group('e.entity_id');
                $select->columns(
                    [
                        'customer_group_ids' =>
                            new Zend_Db_Expr('GROUP_CONCAT(price_index.customer_group_id SEPARATOR ",")')
                    ]
                );

                foreach ($collection as $product) {
                    $customerGroupIds = $product->getCustomerGroupIds() === null ?
                        '' : array_unique(explode(',', $product->getCustomerGroupIds()));
                    $productId        = $product->getId();
                    if ($parentIds = $this->configurable->getParentIdsByChild($productId)) {
                        $productId = $parentIds[0];
                    }
                    // @codingStandardsIgnoreStart
                    $this->onSaleProductIds[$storeId][$productId] =
                        isset($this->onSaleProductIds[$storeId][$productId])
                            ? array_merge($this->onSaleProductIds[$storeId][$productId], $customerGroupIds)
                            : $customerGroupIds;
                    // @codingStandardsIgnoreEnd
                }
            }
        }

        return $this->onSaleProductIds[$storeId];
    }

    /**
     * @return Collection
     */
    protected function getCustomerGroup()
    {
        return $this->customerGroupCollection->create();
    }
}
