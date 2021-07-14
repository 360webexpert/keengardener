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

namespace Mageplaza\Shopbybrand\Helper;

use Exception;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Model\Brand;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\Category;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category\Collection as BrandCategoryCollection;

/**
 * Class Data
 * @package Mageplaza\Osc\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'shopbybrand';
    /**
     * Image size default
     */
    const IMAGE_SIZE = '135x135';
    /**
     * General configuration path
     */
    const GENERAL_CONFIGURATION = 'shopbybrand/general';
    /**
     * Brand sidebar configuration path
     */
    const BRAND_SIDEBAR_CONFIGURATION = 'sidebar';
    /**
     * Brand page configuration path
     */
    const BRAND_CONFIGURATION = 'brandpage';
    /**
     * Search brand configuration path
     */
    const BRAND_DETAIL_CONFIGURATION = 'brandview';
    /**
     * Brand media path
     */
    const BRAND_MEDIA_PATH = 'mageplaza/brand';
    /**
     * Default route name
     */
    const DEFAULT_ROUTE = 'brand';
    /**
     * Get Brand by
     */
    const CATEGORY = 'category';
    /**
     * Get Brand by
     */
    const BRAND_FIRST_CHAR = 'char';

    /**
     * @var FilterManager
     */
    protected $_filter;

    /**
     * @var TranslitUrl
     */
    protected $translitUrl;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @type string
     */
    protected $_char = '';

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type
     */
    protected $_brandCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Attribute
     */
    protected $_attribute;

    protected $brandCollectionCache;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param TranslitUrl $translitUrl
     * @param FilterManager $filter
     * @param CategoryFactory $categoryFactory
     * @param BrandFactory $brandFactory
     * @param Registry $registry
     * @param Attribute $attribute
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        TranslitUrl $translitUrl,
        FilterManager $filter,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        Registry $registry,
        Attribute $attribute,
        FilterProvider $filterProvider
    ) {
        $this->_filter = $filter;
        $this->translitUrl = $translitUrl;
        $this->categoryFactory = $categoryFactory;
        $this->_brandFactory = $brandFactory;
        $this->registry = $registry;
        $this->_attribute = $attribute;
        $this->filterProvider = $filterProvider;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $position
     *
     * @return bool
     */
    public function canShowBrandLink($position)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $positionConfig = explode(',', $this->getConfigGeneral('show_position'));

        return in_array($position, $positionConfig, true);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isShowBrandsWithoutProducts($store = null)
    {
        return $this->getBrandConfig('show_brands_without_products', $store);
    }

    /**
     * @param null $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandUrl($brand = null)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $key = ($brand === null) ? '' : '/' . $this->processKey($brand);

        return $baseUrl . $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @param Brand $brand
     *
     * @return string
     */
    public function processKey($brand)
    {
        if (!$brand) {
            return '';
        }
        $str = $brand->getUrlKey() ?: $brand->getDefaultValue();

        return $this->formatUrlKey($str);
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     *
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * @param Brand $brand
     *
     * @return string
     */
    public function getBrandImageUrl($brand)
    {
        if ($brand->getImage()) {
            $image = $brand->getImage();
        } elseif ($brand->getSwatchType() == Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $image = Media::SWATCH_MEDIA_PATH . $brand->getSwatchValue();
        } elseif ($this->getBrandDetailConfig('default_image')) {
            $image = self::BRAND_MEDIA_PATH . '/' . $this->getBrandDetailConfig('default_image');
        } else {
            return ObjectManager::getInstance()
                ->get(Image::class)
                ->getDefaultPlaceholderUrl('small_image');
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $image;
    }

    /**
     * Get Brand Title
     *
     * @return string
     */
    public function getBrandTitle()
    {
        return $this->getConfigGeneral('link_title') ?: __('Brands');
    }

    /**
     * Retrieve Brand description
     *
     * @param Brand $brand
     * @param bool|false $short
     *
     * @return mixed
     */
    public function getBrandDescription($brand, $short = false)
    {
        if ($short) {
            $content = $brand->getShortDescription() ?: '';
        } else {
            $content = $brand->getDescription() ?: '';
        }

        try {
            return $this->filterProvider->getBlockFilter()->filter($content);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Retrieve Attribute code for Brand
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getAttributeCode($store = null)
    {
        return $this->getConfigGeneral('attribute', $store);
    }

    /**
     * Retrieve route name for brand.
     * If empty, default 'brands' will be used
     *
     * @return string
     */
    public function getRoute()
    {
        $route = $this->getConfigGeneral('route', $this->getStoreId()) ?: self::DEFAULT_ROUTE;

        return $this->formatUrlKey($route);
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_CONFIGURATION . '/' . $code : self::BRAND_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param string $group
     * @param null $store
     *
     * @return array
     */
    public function getImageSize($group = '', $store = null)
    {
        $imageSize = $this->getBrandConfig($group . 'image_size', $store) ?: self::IMAGE_SIZE;

        return explode('x', $imageSize);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getFeatureConfig($code = '', $store = null)
    {
        $code = $code ? 'feature' . '/' . $code : 'feature';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableFeature($store = null)
    {
        return $this->getFeatureConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSearchConfig($code = '', $store = null)
    {
        $code = $code ? 'search' . '/' . $code : 'search';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableSearch($store = null)
    {
        return $this->getSearchConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandDetailConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_DETAIL_CONFIGURATION . '/' . $code : self::BRAND_DETAIL_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSidebarConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_SIDEBAR_CONFIGURATION . '/' . $code : self::BRAND_SIDEBAR_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @return array
     */
    public function getAllBrandsAttributeCode()
    {
        $stores = $this->storeManager->getStores();
        $attributeCodes = [];
        $attributeCodes[] = $this->getAttributeCode();
        foreach ($stores as $store) {
            $attributeCodes[] = $this->getAttributeCode($store->getId());
        }
        $attributeCodes = array_unique($attributeCodes);

        return $attributeCodes;
    }

    /**
     * generate url_key for brand category
     *
     * @param $name
     * @param $count
     *
     * @return string
     */
    public function generateUrlKey($name, $count)
    {
        $name = $this->removeUnicode($name);
        $text = $this->translitUrl->filter($name);
        if ($count == 0) {
            $count = '';
        }
        if (empty($text)) {
            return 'n-a' . $count;
        }

        return $text . $count;
    }

    /**
     * replace vietnamese characters to english characters
     *
     * @param $str
     *
     * @return mixed|string
     */
    public function removeUnicode($str)
    {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/([àáạảãâầấậẩẫăằắặẳẵ])/u', 'a', $str);
        $str = preg_replace('/([èéẹẻẽêềếệểễ])/u', 'e', $str);
        $str = preg_replace('/([ìíịỉĩ])/u', 'i', $str);
        $str = preg_replace('/([òóọỏõôồốộổỗơờớợởỡ])/u', 'o', $str);
        $str = preg_replace('/([ùúụủũưừứựửữ])/u', 'u', $str);
        $str = preg_replace('/([ỳýỵỷỹ])/u', 'y', $str);
        $str = preg_replace('/(đ)/u', 'd', $str);

        return $str;
    }

    /**
     * @param null $cat
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCatUrl($cat = null)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->getRoute();
        $key = ($cat === null) ? '' : '/' . $this->processKey($cat);

        return $baseUrl . $brandRoute . '/' . self::CATEGORY . $key . $this->getUrlSuffix();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = 0;
        }

        return $storeId;
    }

    /**
     * @param $routePath
     * @param $routeSize
     *
     * @return bool
     */
    public function isBrandRoute($routePath, $routeSize)
    {
        if ($routeSize > 3) {
            return false;
        }

        $urlSuffix = $this->getUrlSuffix();
        $brandRoute = $this->getRoute();
        if ($urlSuffix) {
            $brandSuffix = strpos($brandRoute, $urlSuffix);
            if ($brandSuffix) {
                $brandRoute = substr($brandRoute, 0, $brandSuffix);
            }
        }

        return ($routePath[0] === $brandRoute);
    }

    /**
     * @param $urlKey
     *
     * @return mixed|null
     */
    public function getCategoryByUrlKey($urlKey)
    {
        /** @var Category $cat */
        $cat = $this->categoryFactory->create()->load($urlKey, 'url_key');
        if ($cat) {
            return $cat->getId();
        }

        return null;
    }

    /**
     * @param null $type
     * @param null $ids
     * @param null $storeId
     *
     * @return Attribute\Option\Collection
     */
    public function getBrandList($type = null, $ids = null, $storeId = null)
    {
        /** @var Brand $brands */
        $brands = $this->_brandFactory->create();
        switch ($type) {
            //Get Brand List by Category
            case self::CATEGORY:
                $list = $brands->getBrandCollection(null, ['main_table.option_id' => ['in' => $ids]]);
                break;
            //Get Brand List Filtered by Brand First Char
            case self::BRAND_FIRST_CHAR:
                $list = $brands->getBrandCollection($storeId);
                break;
            default:
                //Get Brand List
                if (!$this->brandCollectionCache) {
                    $this->brandCollectionCache = $brands->getBrandCollection($storeId);
                }
                $list = $this->brandCollectionCache;
        }

        return $list;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     *
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }

        return array_key_exists($key, $this->_data);
    }

    /**
     * Get condition for sql
     *
     * @param $char
     *
     * @return string
     */
    public function checkCharacter($char)
    {
        $specialChar = [
            '!',
            '"',
            '#',
            '$',
            '&',
            '(',
            ')',
            '*',
            '+',
            ',',
            '-',
            '.',
            '/',
            ':',
            ';',
            '<',
            '=',
            '>',
            '?',
            '@',
            '[',
            ']',
            '^',
            '_',
            '`',
            '{',
            '|',
            '}',
            '~'
        ];
        if (in_array($char, $specialChar, true)) {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . "'" . $char . "%'";
        } elseif ($char === "'") {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . '"' . $char . '%"';
        } else {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) REGEXP BINARY ' . "'^" . $char . "'";
        }

        return $sqlCond;
    }

    /**
     * @return BrandCategoryCollection
     */
    public function getCategoryList()
    {
        $collection = $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', '1')
            ->addFieldToFilter(['store_ids', 'store_ids'], [
                ['finset' => $this->getStoreId()],
                ['finset' => 0]
            ]);

        return $collection;
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function getFilterClass($brand)
    {
        //vietnamese unikey format
        if ($this->getBrandConfig('brand_filter/encode_key')) {
            $firstChar = mb_substr($brand->getValue(), 0, 1, $this->getBrandConfig('brand_filter/encode_key'));
        } else {
            $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
        }

        return is_numeric($firstChar) ? 'num' . $firstChar : ucfirst($firstChar);
    }

    /**
     * @param $optionId
     *
     * @return string
     */
    public function getCatFilterClass($optionId)
    {
        $catName = [];
        $sql = 'brand_cat_tbl.option_id IN (' . $optionId . ')';
        $group = 'main_table.cat_id';

        $collection = $this->categoryFactory->create()->getCategoryCollection($sql, $group);
        foreach ($collection as $item) {
            $str = str_replace([' ', '*', '/', '\\'], '_', $item->getName());
            $catName[] = 'cat' . $str;
        }

        return implode(' ', $catName);
    }

    /**
     * Is show quick view near Brand name
     *
     * @return mixed
     */
    public function showQuickView()
    {
        return $this->getBrandConfig('show_quick_view');
    }

    /**
     * @return string
     */
    public function getQuickViewUrl()
    {
        return $this->_getUrl('');
    }

    /**
     * @param null $brand
     *
     * @return string
     */
    public function getQuickview($brand = null)
    {
        $key = ($brand === null) ? '' : '/' . $this->processKey($brand);

        return $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @param Product $product
     *
     * @return array|string|null
     */
    public function getBrandTextFromProduct(Product $product = null)
    {
        $currentProduct = $product ?: $this->getCurrentProduct();
        if (!$currentProduct) {
            return null;
        }

        $attCode = $this->getAttributeCode();

        return $currentProduct->getAttributeText($attCode);
    }

    /**
     * @return mixed|null
     */
    public function getBrandObject()
    {
        $currentProduct = $this->getCurrentProduct();
        if (!$currentProduct) {
            return null;
        }

        $optionId = $currentProduct->getData($this->getAttributeCode());
        if (!$optionId) {
            return null;
        }

        return $this->_brandFactory->create()->loadByOption($optionId);
    }

    /**
     * Get current product
     * @return mixed
     */
    public function getCurrentProduct()
    {
        if (!$this->registry->registry('current_product')) {
            return $this->registry->registry('product');
        }

        return $this->registry->registry('current_product');
    }

    /**
     * Get current brand
     * @return mixed
     */
    public function getBrand()
    {
        return $this->registry->registry('current_brand');
    }

    /**
     * convert strings in an array to uppercase
     *
     * @param $array
     *
     * @return array|null
     */
    public function convertUppercase($array)
    {
        $input = array_flip($array);
        $input = array_change_key_case($input, CASE_UPPER);
        $input = array_flip($input);

        return $input;
    }

    /**
     * Get attribute id
     *
     * @param $code
     *
     * @return int
     */
    public function getAttributeId($code)
    {
        return $this->_attribute->getIdByCode(Product::ENTITY, $code);
    }
}
