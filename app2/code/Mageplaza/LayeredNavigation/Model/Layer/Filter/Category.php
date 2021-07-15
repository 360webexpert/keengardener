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

namespace Mageplaza\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Layer as LayerCatalog;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Layer\Filter\Category as AbstractFilter;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\CategoriesLevel;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\RenderCategoryTree;

/**
 * Class Category
 * @package Mageplaza\LayeredNavigation\Model\Layer\Filter
 */
class Category extends AbstractFilter
{
    /** @var LayerHelper */
    protected $_moduleHelper;

    /** @var bool Is Filterable Flag */
    protected $_isFilter = false;

    /** @var Escaper */
    private $escaper;

    /** @var  LayerCatalog\Filter\DataProvider\Category */
    private $dataProvider;
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var CategoryManagementInterface
     */
    protected $categoryManagement;

    /**
     * @var Attribute
     */
    protected $attributeData;

    protected $isFullCategoryTree = false;
    /**
     * @var Item\DataBuilder
     */
    protected $layerDataBuilder;
    /**
     * @var CategoryItemFactory
     */
    protected $categoryItemFactory;

    /**
     * Category constructor.
     *
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param LayerCatalog $layer
     * @param DataBuilder $itemDataBuilder
     * @param Escaper $escaper
     * @param CategoryFactory $dataProviderFactory
     * @param LayerHelper $moduleHelper
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param CategoryManagementInterface $categoryManagement
     * @param Item\DataBuilder $layerDataBuilder
     * @param CategoryItemFactory $categoryItemFactory
     * @param array $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        LayerCatalog $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        CategoryFactory $dataProviderFactory,
        LayerHelper $moduleHelper,
        ProductAttributeRepositoryInterface $attributeRepository,
        CategoryManagementInterface $categoryManagement,
        Item\DataBuilder $layerDataBuilder,
        CategoryItemFactory $categoryItemFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $dataProviderFactory,
            $data
        );

        $this->escaper             = $escaper;
        $this->_moduleHelper       = $moduleHelper;
        $this->dataProvider        = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->attributeRepository = $attributeRepository;
        $this->categoryManagement  = $categoryManagement;
        $this->layerDataBuilder    = $layerDataBuilder;
        $this->categoryItemFactory = $categoryItemFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::apply($request);
        }

        $categoryId = $request->getParam($this->_requestVar);
        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = [];
        foreach (explode(',', $categoryId) as $id) {
            $this->dataProvider->setCategoryId($id);
            if ($this->dataProvider->isValid()) {
                $category = $this->dataProvider->getCategory();
                if ($this->isRenderCategoryTree()) {
                    $categoryIds[] = $id;
                    $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $id));
                } elseif ($request->getParam('id') !== $id) {
                    $categoryIds[] = $id;
                    $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $id));
                }
            }
        }

        if (!empty($categoryIds)) {
            $this->_isFilter = true;
            if ($this->isRenderCategoryTree() && $this->isFullCategoryTree) {
                $this->getLayer()->getProductCollection()->resetCategoryIds($this->getCurrentCategoryId())
                    ->resetCategoryFilter($this->getCurrentCategoryId())->addLayerCategoryFilter($categoryIds);
            } else {
                $this->getLayer()->getProductCollection()->addLayerCategoryFilter($categoryIds);
            }
        }

        if ($parentCategoryId = $request->getParam('id')) {
            $this->dataProvider->setCategoryId($parentCategoryId);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _initItems()
    {
        if ($this->isRenderCategoryTree()) {
            $data = $this->getCategoryAdditionData();
            /** @var CategoryItem $itemCollection */
            $itemCollection = $this->categoryItemFactory->create();
            if ($data && $data['count']) {
                $itemCollection->setStartPath($data['startPath']);
                $itemCollection->setCount($data['count']);
                foreach ($data['items'] as $parentId => $items) {
                    foreach ($items as $item) {
                        $itemCollection->addItem(
                            $item['parent_id'],
                            $this->_createItem($item['label'], $item['value'], $item['count'])
                        );
                    }
                }
            }

            $this->_items = $itemCollection;

            return $this;
        }

        return parent::_initItems();
    }

    /**
     * @return array
     * @throws StateException
     */
    protected function _getItemsData()
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::_getItemsData();
        }

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch('category_ids');
        }

        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category           = $this->dataProvider->getCategory();
        $categories         = $category->getChildrenCategories();

        $collectionSize = $productCollection->getSize();

        if ($category->getIsActive()) {
            foreach ($categories as $childCategory) {
                $count = isset($optionsFacetedData[$childCategory->getId()])
                    ? $optionsFacetedData[$childCategory->getId()]['count'] : 0;
                if ($childCategory->getIsActive()
                    && $this->_moduleHelper->getFilterModel()->isOptionReducesResults($this, $count, $collectionSize)
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($childCategory->getName()),
                        $childCategory->getId(),
                        $count
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCategoryAdditionData()
    {
        $attributeModel     = $this->getCategoryAttributeModel();
        $renderCategoryTree = $attributeModel->getData('render_category_tree');
        $itemsData          = [];
        $startPath          = '';

        try {
            $optionsFacetedData = $this->getFacetedData();
            if (empty($optionsFacetedData)) {
                $categoryId                      = $this->dataProvider->getCategory()->getId();
                $optionsFacetedData[$categoryId] = [
                    'value' => $categoryId,
                    'count' => $this->getLayer()->getProductCollection()->getSize()
                ];
            }
        } catch (StateException $e) {
            return [];
        }

        try {
            $depth            = $renderCategoryTree !== RenderCategoryTree::FULL_CATEGORY
                ? $attributeModel->getData('category_tree_depth')
                : null;
            $categoryTreeList = $this->categoryManagement->getTree($this->getCurrentCategoryId(), $depth);
        } catch (NoSuchEntityException $e) {
            $categoryTreeList = [];
        }

        if ($categoryTreeList) {
            if ($renderCategoryTree === RenderCategoryTree::CUSTOM
                && $attributeModel->getData('categories_level') === CategoriesLevel::CURRENT_CATEGORY
            ) {
                $startPath = $categoryTreeList->getParentId();
                $count     = isset($optionsFacetedData[$categoryTreeList->getId()])
                    ? $optionsFacetedData[$categoryTreeList->getId()]['count'] : 0;

                $this->layerDataBuilder->addItemData(
                    $this->escaper->escapeHtml($categoryTreeList->getName()),
                    $categoryTreeList->getId(),
                    $categoryTreeList->getParentId(),
                    $categoryTreeList->getPosition(),
                    $categoryTreeList->getLevel(),
                    $count
                );
            } else {
                $startPath = $categoryTreeList->getId();
            }
            $this->getCategoryTree($categoryTreeList, $optionsFacetedData);
        }
        $itemsData['startPath'] = $startPath;
        $itemsData['count']     = $this->layerDataBuilder->getCount();
        $itemsData['items']     = $this->layerDataBuilder->build();

        return $itemsData;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isRenderCategoryTree()
    {
        $attributeModel     = $this->getCategoryAttributeModel();
        $renderCategoryTree = $attributeModel->getData('render_category_tree');

        if ($renderCategoryTree === RenderCategoryTree::FULL_CATEGORY) {
            $this->isFullCategoryTree = true;
        } elseif ($renderCategoryTree === RenderCategoryTree::CUSTOM
            && $attributeModel->getData('categories_level') === CategoriesLevel::ROOT_CATEGORY) {
            $this->isFullCategoryTree = true;
        }

        return $renderCategoryTree && $renderCategoryTree !== RenderCategoryTree::NO;
    }

    /**
     * @param CategoryTreeInterface $tree
     * @param array $optionsFacetedData
     */
    public function getCategoryTree($tree, $optionsFacetedData)
    {
        if (!$tree) {
            return;
        }

        foreach ($tree->getChildrenData() as $item) {
            $count = isset($optionsFacetedData[$item->getId()])
                ? $optionsFacetedData[$item->getId()]['count'] : 0;

            if ($item->getIsActive() === '1') {
                $child = $item->getChildrenData();
                if ($item->getLevel() !== '1') {
                    $this->layerDataBuilder->addItemData(
                        $this->escaper->escapeHtml($item->getName()),
                        $item->getId(),
                        $item->getParentId(),
                        $item->getPosition(),
                        $item->getLevel(),
                        $count
                    );
                }

                if ($child) {
                    $this->getCategoryTree($item, $optionsFacetedData);
                }
            }
        }
    }

    /**
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCurrentCategoryId()
    {
        if ($this->isFullCategoryTree) {
            $categoryId = $this->_storeManager->getStore()->getRootCategoryId();
        } else {
            $categoryId = $this->getCategoryAttributeModel()->getData('categories_level') === CategoriesLevel::ROOT_CATEGORY
                ? $this->_storeManager->getStore()->getRootCategoryId()
                : $this->dataProvider->getCategory()->getId();
        }

        return $categoryId;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    protected function getFacetedData()
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch('category_ids');
        } else {
            $productCollection = $productCollection->getCollectionClone()
                ->setCategoryFilter($this->getCurrentCategoryId());
        }

        return $productCollection->getFacetedData('category');
    }

    /**
     * @return Attribute
     * @throws NoSuchEntityException
     */
    public function getCategoryAttributeModel()
    {
        if (!$this->attributeData) {
            /** @var Attribute $attribute */
            $this->attributeData = $this->attributeRepository->get('category_ids');
            if ($this->attributeData->getId() && $data = $this->attributeData->getAdditionalData()) {
                $additionalData = $this->_moduleHelper->unserialize($data);
                if (is_array($additionalData)) {
                    $this->attributeData->addData($additionalData);
                }
            }
        }

        return $this->attributeData;
    }
}
