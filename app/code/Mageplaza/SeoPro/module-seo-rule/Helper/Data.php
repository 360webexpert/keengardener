<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Helper;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Seo\Helper\Data as AbstractHelper;
use Mageplaza\SeoRule\Model\Meta;
use Mageplaza\SeoRule\Model\MetaFactory;
use Mageplaza\SeoRule\Model\Rule\Source\EntityType;
use Mageplaza\SeoRule\Model\Rule\Source\Type;
use Mageplaza\SeoRule\Model\Rule\Source\Type as SourceType;
use Mageplaza\SeoRule\Model\RuleFactory;

/**
 * Class Data
 * @package Mageplaza\SeoRule\Helper
 */
class Data extends AbstractHelper
{
    const LIMIT = 5;
    /**
     * Match options in {{ }}
     */
    const PATTERN_OPTIONS = '/{{([a-zA-Z_]{0,50})(.*?)}}/si';
    /**
     * Match options in []
     */
    const PATTERN_RANDOM_OPTIONS = '/\[([^\]]*)\]/';
    /**
     * Match value must start equal {{ and end equal }}
     */
    const PATTERN_MATCH = '/^\{\{(.*?)\}\}$/';
    /** Seo rule configuration path */
    const SEO_RULE_CONFIGUARATION = 'seo_rule';

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var PricingHelper
     */
    protected $priceHelper;

    /**
     * @var RuleFactory
     */
    protected $seoRuleFactory;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param PageFactory $pageFactory
     * @param PricingHelper $priceHelper
     * @param RuleFactory $ruleFactory
     * @param MetaFactory $metaFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        PageFactory $pageFactory,
        PricingHelper $priceHelper,
        RuleFactory $ruleFactory,
        MetaFactory $metaFactory,
        Registry $registry
    ) {
        $this->productFactory  = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->pageFactory     = $pageFactory;
        $this->priceHelper     = $priceHelper;
        $this->seoRuleFactory  = $ruleFactory;
        $this->metaFactory     = $metaFactory;
        $this->registry        = $registry;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Is enable Seo rule
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnableSeoRule($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getSeoRuleConfig('enabled', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSeoRuleConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(
            static::CONFIG_MODULE_PATH . '/' . self::SEO_RULE_CONFIGUARATION . $code,
            $storeId
        );
    }

    /** Is use seo name for product pages
     *
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function isUseSeoNameProduct($storeId = null)
    {
        if (!$this->isEnableSeoRule()) {
            return false;
        }

        return (bool) $this->getSeoRuleConfig('seo_name_product', $storeId);
    }

    /**
     * Is use seo name for category pages
     *
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function isUseSeoNameCategory($storeId = null)
    {
        if (!$this->isEnableSeoRule()) {
            return false;
        }

        return (bool) $this->getSeoRuleConfig('seo_name_category', $storeId);
    }

    /**
     * Iss enable automate alt image
     *
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function isEnableAutomateAltImg($storeId = null)
    {
        if (!$this->isEnableSeoRule()) {
            return false;
        }

        return $this->getSeoRuleConfig('enable_automate_alt_image', $storeId);
    }

    /**
     * Preview action
     *
     * @param $templateTitle
     * @param $templateDescription
     * @param $templateKeywords
     * @param string $type
     *
     * @return array
     */
    public function preview($templateTitle, $templateDescription, $templateKeywords, $type = 'product')
    {
        $data = [];
        try {
            if ($type == Type::CATEGORIES || $type == Type::LAYERED_NAVIGATION) {
                $collection = $this->randomCategories(self::LIMIT);
            } elseif ($type == Type::PAGES) {
                $collection = $this->randomPages(self::LIMIT);
            } else {
                $collection = $this->randomProducts(self::LIMIT);
            }

            foreach ($collection as $item) {
                $id                           = $item->getId();
                $object                       = $this->getObject($type)->load($id);
                $data[$id]['name']            = ($type == Type::PAGES) ? $object->getTitle() : $object->getName();
                $data[$id]['metaTitle']       = $this->generateMetaTemplate($templateTitle, $id, false, $type);
                $data[$id]['metaDescription'] = $this->generateMetaTemplate($templateDescription, $id, false, $type);
                $data[$id]['metaKeyword']     = $this->generateMetaTemplate($templateKeywords, $id, true, $type);
                $data[$id]['id']              = $object->getId();
                $data[$id]['type']            = $type;
            }
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $data;
    }

    /**
     * @param $template
     * @param $id
     * @param bool $isKeyWord
     * @param string $type
     * @param bool $isApply
     *
     * @return string
     */
    public function generateMetaTemplate($template, $id, $isKeyWord = false, $type = 'product', $isApply = false)
    {
        if ($type == Type::LAYERED_NAVIGATION) {
            return $this->formatSpace($template);
        }
        $object              = $this->getObject($type)->load($id);
        $productData         = $this->processOptions($template, $object, $type);
        $attributeRandomData = $this->processRandomOptions($template, $productData);

        $template     = $this->replaceDataTemplate($template, $attributeRandomData, $isKeyWord, $isApply);
        $metaTemplate = $this->replaceDataTemplate($template, $productData, $isKeyWord, $isApply);

        preg_match_all(self::PATTERN_OPTIONS, $metaTemplate, $match);
        foreach ($match[0] as $value) {
            $metaTemplate = str_replace($value, '', $metaTemplate);
        }
        $metaTemplate = $this->sliceRight(trim($metaTemplate), $isKeyWord);

        return $this->formatSpace($metaTemplate);
    }

    /**
     * @param $template
     * @param $product
     * @param string $type
     *
     * @return array
     */
    public function processOptions($template, $product, $type = 'product')
    {
        preg_match_all(self::PATTERN_OPTIONS, $template, $match);
        $productData = [];
        foreach ($match[1] as $key => $attribute) {
            if ($type == Type::PAGES) {
                if ($product->getData($attribute)) {
                    $productData[$match[0][$key]] = $product->getData($attribute);
                }
            } else {
                if ($attribute == 'category_name' && is_array($product->getCategoryIds())) {
                    $randomCategory               = array_rand($product->getCategoryIds());
                    $productData[$match[0][$key]] = $this->categoryFactory->create()->load($product->getCategoryIds()[$randomCategory])->getName();
                } elseif ($product->getResource()->getAttribute($attribute) && $product->getResource()->getAttribute($attribute)->usesSource()) {
                    $idAttribute = $product->getData($attribute);
                    if ($product->getData($attribute)) {
                        $productAttribute = $product->getResource()->getAttribute((string) $attribute);
                        if ($productAttribute->getFrontendInput() == 'multiselect') {
                            $productData[$match[0][$key]] = $productAttribute->getFrontend()->getValue($product);
                        } else {
                            $productData[$match[0][$key]] = $productAttribute->getSource()->getOptionText($idAttribute);
                        }
                    }
                } elseif (in_array($attribute, ['price', 'special_price'])) {
                    if (!empty($product->getData($attribute))) {
                        $productData[$match[0][$key]] = $this->priceHelper->currency(
                            $product->getData($attribute),
                            true,
                            false
                        );
                    }
                } else {
                    if ($product->getData($attribute)) {
                        $productData[$match[0][$key]] = $product->getData($attribute);
                    }
                }
            }
        }

        return $productData;
    }

    /**
     * Process random options
     *
     * @param $template
     * @param $productData
     *
     * @return array
     */
    public function processRandomOptions($template, $productData)
    {
        preg_match_all(self::PATTERN_RANDOM_OPTIONS, $template, $matches);
        $attributeRandomData = [];
        foreach ($matches[1] as $key => $attributeRandom) {
            $tmpData                = [];
            $isRandom               = false;
            $explodeAttributeRandom = explode('||', $attributeRandom);
            if (is_array($explodeAttributeRandom)) {
                foreach ($explodeAttributeRandom as $keyExplode => $randomValue) {
                    if (preg_match(self::PATTERN_MATCH, $randomValue, $match)) {
                        if (isset($productData[$randomValue])) {
                            $tmpData[] = $productData[$randomValue];
                        }
                    } else {
                        $attributeRandomData[$matches[0][$key]] = $this->randomValue($explodeAttributeRandom);
                        $isRandom                               = true;
                    }
                }
            }

            if (!$isRandom) {
                $attributeRandomData[$matches[0][$key]] = $this->randomValue($this->deleteValueEmpty($tmpData));
            }
        }

        return $attributeRandomData;
    }

    /**
     * Get Object by entity
     *
     * @param string $entity
     *
     * @return Category|Product|Page
     */
    public function getObject($entity)
    {
        /**
         * Product entity
         */
        if ($entity == SourceType::PRODUCTS) {
            return $this->productFactory->create();
        }

        /**
         * Category entity
         */
        if ($entity == SourceType::CATEGORIES || $entity == SourceType::CATEGORIES) {
            return $this->categoryFactory->create();
        }

        /**
         * Page entity
         */
        if ($entity == SourceType::PAGES) {
            return $this->pageFactory->create();
        }

        if ($entity == EntityType::POSTS_BY_MAGEPLAZA_BLOG) {
            return ObjectManager::getInstance()->create('\Mageplaza\Blog\Model\Post');
        }

        return $this->productFactory->create();
    }

    /**
     * Random products
     *
     * @param int $limit
     *
     * @return AbstractCollection
     */
    public function randomProducts($limit)
    {
        $products = $this->productFactory->create()->getCollection()
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->setPageSize($limit);
        $products->getSelect()->orderRand();

        return $products;
    }

    /**
     * Random blogs
     *
     * @param int $limit
     *
     * @return mixed
     */
    public function randomBlogs($limit)
    {
        $blogs = ObjectManager::getInstance()->create('\Mageplaza\Blog\Model\Post')->getCollection()->setPageSize($limit);
        $blogs->getSelect()->orderRand();

        return $blogs;
    }

    /**
     * Random categories
     *
     * @param int $limit
     *
     * @return AbstractCollection
     */
    public function randomCategories($limit)
    {
        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setPageSize($limit);
        $categories->getSelect()->orderRand();

        return $categories;
    }

    /**
     * Get category ids
     * @return array
     */
    public function getCategoryIds()
    {
        $categoryCollection = $this->categoryFactory->create()
            ->getCollection();
        $ids                = [];
        foreach ($categoryCollection as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }

    /**
     * Random pages
     *
     * @param int $limit
     *
     * @return Collection
     */
    public function randomPages($limit)
    {
        $pages = $this->pageFactory->create()->getCollection()->setPageSize($limit);
        $pages->getSelect()->orderRand();

        return $pages;
    }

    /**
     * Get page ids
     * @return array
     */
    public function getPageIds()
    {
        $pageCollection = $this->pageFactory->create()
            ->getCollection();
        $ids            = [];
        foreach ($pageCollection as $page) {
            $ids[] = $page->getId();
        }

        return $ids;
    }

    /**
     * Replace data template
     *
     * @param $template
     * @param $arrayReplace
     * @param bool $isKeyword
     * @param bool $isApply
     *
     * @return mixed
     */
    public function replaceDataTemplate($template, $arrayReplace, $isKeyword = false, $isApply = false)
    {
        foreach ($arrayReplace as $replaceKey => $replaceValue) {
            $isKeyword = $isKeyword ? ', ' : '';
            if ($replaceKey == '{{category_name}}' && $isApply) {
                $template = str_replace($replaceKey, '((category_name))' . $isKeyword, $template);
            } else {
                $template = str_replace($replaceKey, $replaceValue . $isKeyword, $template);
            }
        }

        return $template;
    }

    /**
     * Delete all value empty on array
     *
     * @param $arr
     *
     * @return array
     */
    public function deleteValueEmpty($arr)
    {
        $data = [];
        foreach ($arr as $value) {
            if (!empty($value)) {
                $data[] = $value;
            }
        }

        return $data;
    }

    /**
     * Random value from array
     *
     * @param $arr
     *
     * @return mixed
     */
    public function randomValue($arr)
    {
        if (is_array($arr) && count($arr) > 0) {
            $ranIndex = array_rand($arr);

            return $arr[$ranIndex];
        }
    }

    /**
     * Format space
     *
     * @param $str
     *
     * @return string
     */
    public function formatSpace($str)
    {
        return implode(' ', $this->deleteValueEmpty(explode(' ', $str)));
    }

    /**
     * @param $object
     * @param $metaData
     *
     * @return mixed
     */
    public function forceUpdateTemplate($object, $metaData)
    {
        $object->setMetaDescription($metaData->getMetaDescription());
        $object->setMetaTitle($metaData->getMetaTitle());

        if ($metaData->getEntityType() == Type::PRODUCTS) {
            $object->setMetaKeyword($metaData->getMetaKeywords());
        } else {
            $object->setMetaKeywords($metaData->getMetaKeywords());
        }

        $this->registry->register('seo_rule_robots', $metaData->getMetaRobots());

        return $object;
    }

    /**
     * Skip update template
     *
     * @param $object
     * @param $metaData
     *
     * @return mixed
     */
    public function skipUpdateTemplate($object, $metaData)
    {
        if (!isset($object['meta_description']) || empty($object->getMetaDescription())) {
            $object->setMetaDescription($metaData->getMetaDescription());
        }

        if (empty($object->getMetaTitle())) {
            $object->setMetaTitle($metaData->getMetaTitle());
        }

        if ($metaData->getEntityType() == EntityType::PRODUCTS) {
            if (empty($object->getMetaKeyword())) {
                $object->setMetaKeyword($metaData->getMetaKeywords());
            }
        } else {
            if (empty($object->getMetaKeywords())) {
                $object->setMetaKeywords($metaData->getMetaKeywords());
            }
        }

        if (empty($object->getMetaRobots())) {
            $this->registry->register('seo_rule_robots', $metaData->getMetaRobots());
        }

        return $object;
    }

    /**
     * Generate Meta Template For Layer Navigation
     *
     * @param $metaTemplate
     * @param $dataAttributeFilter
     * @param bool $isKeyWord
     *
     * @return string
     */
    public function generateMetaTemplateForLayer($metaTemplate, $dataAttributeFilter, $isKeyWord = false)
    {
        $meta = $this->replaceDataTemplate($metaTemplate, $dataAttributeFilter, $isKeyWord);
        preg_match_all(self::PATTERN_OPTIONS, $metaTemplate, $match);
        $newMeta = $meta;
        foreach ($match[0] as $value) {
            $newMeta = str_replace($value, '', $newMeta);
        }
        $newMeta = $this->sliceRight(trim($newMeta), $isKeyWord);

        return $this->formatSpace($newMeta);
    }

    /**
     * slice char end is comma
     *
     * @param $newMeta
     * @param $isKeyWord
     *
     * @return bool|string
     */
    public function sliceRight($newMeta, $isKeyWord)
    {
        if ($isKeyWord) {
            if (preg_match('/,$/', $newMeta)) {
                $newMeta = substr($newMeta, 0, strlen($newMeta) - 1);
            }
        }

        return $newMeta;
    }

    /**
     * Apply all rule
     */
    public function applyRules()
    {
        $result     = [];
        $collection = $this->seoRuleFactory->create()->getCollection()
            ->addFieldToFilter('entity_type', ['neq' => Type::LAYERED_NAVIGATION])
            ->setOrder('sort_order', 'ASC');

        /** @var Meta $metaModel */
        $metaModel = $this->metaFactory->create();
        $metaModel->truncateData();

        foreach ($collection as $rule) {
            if ($rule->getStatus()) {
                $this->applyRuleId($rule, $metaModel);
                $result[] = $rule->getRuleId();
            }
        }

        return $result;
    }

    /**
     * @param $model
     * @param Meta $metaModel
     *
     * @return $this
     */
    public function applyRuleId($model, $metaModel)
    {
        $listId   = [];
        $data     = $model->getData();
        $typeName = 'product_id';
        switch ($model->getEntityType()) {
            case Type::PRODUCTS:
                $listId = $model->getMatchingProductIds();
                break;
            case Type::CATEGORIES:
                if (!empty($model->getCategorys())) {
                    $listId = explode(',', $model->getCategorys());
                } else {
                    $listId = $this->getCategoryIds();
                }
                $typeName = 'category_id';
                break;
            case Type::PAGES:
                if (!empty($model->getPages())) {
                    try {
                        $pages  = $this->unserialize($model->getPages());
                        $listId = array_keys($pages);
                    } catch (Exception $e) {
                        $listId = $this->getPageIds();
                    }
                } else {
                    $listId = $this->getPageIds();
                }
                $typeName = 'page_id';
                break;
        }

        if (is_array($listId)) {
            $metaData = [];
            foreach ($listId as $id) {
                $data['meta_title']       = $this->generateMetaTemplate(
                    $model->getMetaTitle(),
                    $id,
                    false,
                    $model->getEntityType()
                );
                $data['meta_description'] = $this->generateMetaTemplate(
                    $model->getMetaDescription(),
                    $id,
                    false,
                    $model->getEntityType()
                );
                $data['meta_keywords']    = $this->generateMetaTemplate(
                    $model->getMetaKeywords(),
                    $id,
                    true,
                    $model->getEntityType()
                );
                $data['rule_id']          = $model->getRuleId();
                $data[$typeName]          = trim($id);
                $metaData[]               = $data;

                if (sizeof($metaData) > 1000) {
                    $metaModel->applyRule($metaData);
                    $metaData = [];
                }
            }

            if (sizeof($metaData)) {
                $metaModel->applyRule($metaData);
            }
        }

        return $this;
    }

    /**
     * @param $object
     * @param $meta
     * @param $type
     *
     * @return mixed
     */
    public function replaceCategoryName($object, $meta, $type)
    {
        if ($type != Type::PRODUCTS) {
            return $meta;
        }

        $category = $this->registry->registry('current_category');
        if ($category) {
            $categoryName = $category->getName();
        } elseif (count($object->getCategoryIds())) {
            $categoryName = $this->categoryFactory->create()
                ->load($object->getCategoryIds()[0])->getName();
        } else {
            $categoryName = '';
        }

        return str_replace('((category_name))', $categoryName, $meta);
    }
}
