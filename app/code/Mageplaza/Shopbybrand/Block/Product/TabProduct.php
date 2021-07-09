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

namespace Mageplaza\Shopbybrand\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Mageplaza\Shopbybrand\Helper\Data as Helper;

/**
 * Class TabProduct
 * @package Mageplaza\Shopbybrand\Block\Product
 */
class TabProduct extends ListProduct
{
    /**
     * Default related product page title
     */
    const TITLE = 'Products from the same brand';
    /**
     * Default limit related products
     */
    const LIMIT = 5;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Visibility
     */
    protected $visibleProducts;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * RelatedProduct constructor.
     *
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param CollectionFactory $productCollectionFactory
     * @param Helper $helper
     * @param Visibility $visibleProducts
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        CollectionFactory $productCollectionFactory,
        Helper $helper,
        Visibility $visibleProducts,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->visibleProducts = $visibleProducts;
        $this->_productCollectionFactory = $productCollectionFactory;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->setTabTitle();
    }

    /**
     * set Tab Name
     */
    public function setTabTitle()
    {
        $products = $this->_getProductCollection()->getPageSize();
        $title = __('More from this Brand (%1)', $products);
        $this->setTitle($title);
    }

    /**
     * @return mixed
     * get ProductCollection in same brand ( filter by Attribute Option_Id )
     */
    public function _getProductCollection()
    {
        $product = $this->_helper->getCurrentProduct();
        if (($product instanceof Product) && $product->getId()) {
            $attCode = $this->_helper->getAttributeCode();
            $optionId = $product->getData($attCode);
            $collection = $this->_productCollectionFactory->create()
                ->setVisibility($this->visibleProducts->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')->addAttributeToFilter($attCode, ['eq' => $optionId])
                ->addFieldToFilter('entity_id', ['neq' => $product->getId()]);

            $limit = ($this->getLimitProductConfig() > $collection->getSize())
                ? $collection->getSize()
                : $this->getLimitProductConfig();

            $collection->setPageSize($limit);

            return $collection;
        }

        return null;
    }

    /**
     * @return mixed|string
     */
    public function getRelatedTitle()
    {
        $title = $this->_helper->getBrandConfig('related_products/title');

        return $title ? $title : self::TITLE;
    }

    /**
     * @return mixed|string
     */
    public function getLimitProductConfig()
    {
        return (int) $this->_helper->getBrandConfig('related_products/limit_product') ?: self::LIMIT;
    }

    /**
     * @return null
     */
    public function getToolbarHtml()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getAdditionalHtml()
    {
        return null;
    }
}
