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
 * @package     Mageplaza_
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Zend_Db_Expr;

/**
 * Class IsNew
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class IsNew implements DataMapperInterface
{
    const FIELD_NAME = 'mp_is_new';
    const DOCUMENT_FIELD_NAME = 'news_from_date';
    const INDEX_DOCUMENT = 'document';
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $newProductIds = [];
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * IsNew constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $localeDate
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     *
     * @return array|int[]
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $value = isset($context[self::INDEX_DOCUMENT][self::DOCUMENT_FIELD_NAME])
            ? $context[self::INDEX_DOCUMENT][self::DOCUMENT_FIELD_NAME] : $this->isProductNew($entityId, $storeId);
        return [self::FIELD_NAME => (int)$value];
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag('layered_navigation/filter/state/new_enable', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $entityId
     * @param $storeId
     * @return bool
     */
    private function isProductNew($entityId, $storeId)
    {
        return isset($this->getNewProductIds($storeId)[$entityId]);
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    private function getNewProductIds($storeId)
    {
        if (!isset($this->newProductIds[$storeId])) {
            $this->newProductIds[$storeId] = [];
            $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);
            $this->addNewFilter($collection);
            foreach ($collection as $item) {
                $this->newProductIds[$storeId][$item->getId()] = $item->getId();
            }
        }
        return $this->newProductIds[$storeId];
    }

    /**
     * @param $collection
     */
    protected function addNewFilter($collection)
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $collection->addAttributeToFilter('news_from_date', [
            'or' => [
                0 => ['date' => true, 'to' => $todayEndOfDayDate],
                1 => ['is' => new Zend_Db_Expr('null')],
            ]
        ], 'left')
            ->addAttributeToFilter('news_to_date', [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new Zend_Db_Expr('null')],
                ]
            ], 'left')
            ->addAttributeToFilter([
                ['attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null')],
            ]);
    }
}
