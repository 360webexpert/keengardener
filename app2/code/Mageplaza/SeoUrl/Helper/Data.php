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
 * @package     Mageplaza_SeoUrl
 * @copyright   Copyright (c) 2018 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoUrl\Helper;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Mageplaza\Core\Helper\AbstractData;
use Zend_Serializer_Exception;

/**
 * Class Data
 * @package Mageplaza\SeoUrl\Helper
 */
class Data extends AbstractData
{
    /**#@+
     * Category tree cache id
     */
    const SEO_URL_CACHE_KEY = 'CATALOG_PRODUCT_SEO_URL';
    const CONFIG_MODULE_PATH = 'seourl';

    /**
     * @type
     */
    protected $categoryUrlSuffix;

    /**
     * @type UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var array Url Keys save for all options
     */
    protected $_urlKeys = [];

    /**
     * @type FilterManager
     */
    protected $_filter;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param UrlFinderInterface $urlFinder
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param FilterManager $filter
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        UrlFinderInterface $urlFinder,
        CollectionFactory $attrOptionCollectionFactory,
        FilterManager $filter
    ) {
        $this->urlFinder = $urlFinder;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_filter = $filter;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Encode friendly url
     *
     * @param $originUrl
     *
     * @return bool|mixed|string
     * @throws NoSuchEntityException
     * @throws Zend_Serializer_Exception
     */
    public function encodeFriendlyUrl($originUrl)
    {
        if (!$this->isEnabled() || strpos($originUrl, '?') === false) {
            return $originUrl;
        }

        /** convert escape char to normal and remove hash value */
        $correctUrl = str_replace('&amp;', '&', urldecode($originUrl));
        if (!filter_var($correctUrl, FILTER_VALIDATE_URL)) {
            return $originUrl;
        }

        $posHash = strpos($correctUrl, '#');
        if ($posHash) {
            $correctUrl = substr($correctUrl, 0, $posHash);
        }

        list($url, $params) = explode('?', $correctUrl);
        $params = explode('&', $params);

        foreach ($params as $key => $param) {
            list($attKey, $attValues) = explode('=', $param);
            $params[$attKey] = explode(',', $attValues);
            unset($params[$key]);
        }

        $urlKey = [];
        $optionCollection = $this->getOptionsArray();
        foreach ($params as $key => $param) {
            $options = array_filter($optionCollection, function ($option) use ($key, $param) {
                return ($option['attribute_code'] == $key) && in_array($option['option_id'], $param);
            });

            if (sizeof($options)) {
                $urlKey = array_merge($urlKey, array_column($options, 'url_key'));
                unset($params[$key]);
            }
        }

        if (!sizeof($urlKey)) {
            return $originUrl;
        }

        $url = rtrim($url, '/');
        $urlSuffix = $this->getCategoryUrlSuffix();
        if ($urlSuffix && ($urlSuffix != '/')) {
            $pos = strpos($url, $urlSuffix);
            if ($pos) {
                $url = substr($url, 0, $pos);
            } else {
                return $originUrl;
            }
        }

        $url .= '/' . implode('-', $urlKey) . $urlSuffix;
        if (sizeof($params)) {
            foreach ($params as $key => $param) {
                $url .= (!strpos($url, '?') ? '?' : '&') . $key . '=' . implode(',', $param);
            }
        }

        return htmlspecialchars($url);
    }

    /**
     * Decode friendly url
     *
     * @param $pathInfo
     *
     * @return array|null
     * @throws NoSuchEntityException
     * @throws Zend_Serializer_Exception
     */
    public function decodeFriendlyUrl($pathInfo)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $pathInfo = trim($pathInfo, '/');
        if (!$pathInfo) {
            return null;
        }

        $urlSuffix = $this->getCategoryUrlSuffix();
        if ($urlSuffix && ($urlSuffix != '/')) {
            $pos = strpos($pathInfo, $urlSuffix);
            if ($pos) {
                $pathInfo = substr($pathInfo, 0, $pos);
            } else {
                return null;
            }
        }

        $pathInfo = explode('/', $pathInfo);
        if (sizeof($pathInfo) <= 1) {
            return null;
        }

        $rewrite = $this->getRewrite(implode('/', $pathInfo) . $urlSuffix, $this->storeManager->getStore()->getId());
        if ($rewrite !== null) {
            return null;
        }

        $urlParams = explode('-', array_pop($pathInfo));
        $pathInfo = implode('/', $pathInfo) . $urlSuffix;
        $rewrite = $this->getRewrite($pathInfo, $this->storeManager->getStore()->getId());
        if ($rewrite === null) {
            return null;
        }

        $urlKey = '';
        $params = [];
        $optionCollection = $this->getOptionsArray();
        foreach ($urlParams as $param) {
            $urlKey .= ($urlKey ? '-' : '') . $param;
            $options = array_filter($optionCollection, function ($option) use ($urlKey) {
                return ($option['url_key'] == $urlKey);
            });

            if (sizeof($options)) {
                $urlKey = '';
                $option = array_shift($options);
                $params[$option['attribute_code']] = isset($params[$option['attribute_code']]) ?
                    $params[$option['attribute_code']] . ',' . $option['option_id'] :
                    $option['option_id'];
            }
        }

        if ($urlKey != '') {
            return null;
        }

        return ['pathInfo' => $pathInfo, 'params' => $params];
    }

    /**
     * @param string $requestPath
     * @param int $storeId
     *
     * @return UrlRewrite|null
     */
    protected function getRewrite($requestPath, $storeId)
    {
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => trim($requestPath, '/'),
            UrlRewrite::STORE_ID => $storeId,
        ]);

        if ($rewrite === null) {
            $object = new DataObject([
                'pathInfo' => $requestPath,
                'store_id' => $storeId,
                'rewrite' => $rewrite
            ]);
            $this->_eventManager->dispatch('seo_friendly_url_get_rewrite_path', ['object' => $object]);

            $rewrite = $object->getData('rewrite');
        }

        return $rewrite;
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryUrlSuffix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        if (!isset($this->categoryUrlSuffix)) {
            $this->categoryUrlSuffix = $this->scopeConfig->getValue(
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->categoryUrlSuffix;
    }

    /**
     * Get all option with url_key value
     *
     * @return array|mixed
     * @throws Zend_Serializer_Exception
     */
    public function getOptionsArray()
    {
        $cacheManager = $this->objectManager->get(CacheInterface::class);
        $urlOptions = $cacheManager->load(self::SEO_URL_CACHE_KEY);
        if ($urlOptions) {
            return $this->unserialize($urlOptions);
        }

        /** @var Collection $collection */
        $collection = $this->_attrOptionCollectionFactory->create()
            ->setPositionOrder('asc')
            ->setStoreFilter();

        $collection->getSelect()
            ->joinLeft(
                ['at' => $collection->getTable('eav_attribute')],
                "main_table.attribute_id = at.attribute_id",
                ['attribute_code']
            )
            ->joinLeft(
                ['cea' => $collection->getTable('catalog_eav_attribute')],
                "main_table.attribute_id = cea.attribute_id",
                ['is_filterable']
            )->where("is_filterable='1'");

        $urlOptions = $collection->walk([$this, 'processKey']);

        $cacheManager->save(
            $this->serialize($urlOptions),
            self::SEO_URL_CACHE_KEY,
            [
                Product::CACHE_TAG,
                Category::CACHE_TAG,
                Block::CACHE_TAG
            ]
        );

        return $urlOptions;
    }

    /**
     * * Format URL key from name or defined key
     *
     * @param Option $option
     *
     * @return array
     */
    public function processKey($option)
    {
        $optionData = $option->getData();
        if (!isset($optionData['url_key'])) {
            if (isset($optionData['value'])) {
                $key = $this->_filter->translitUrl($optionData['value']);
            }else{
                $key = $this->_filter->translitUrl($optionData['default_value']);
            }
            $key = str_replace('-', '', $key);

            if (array_key_exists($key, $this->_urlKeys)) {
                $this->_urlKeys[$key]++;
            } else {
                $this->_urlKeys[$key] = 0;
            }

            $optionData['url_key'] = $key . ($this->_urlKeys[$key] ?: '');
        }

        return [
            'url_key' => $optionData['url_key'],
            'option_id' => $optionData['option_id'],
            'attribute_code' => $optionData['attribute_code']
        ];
    }
}
