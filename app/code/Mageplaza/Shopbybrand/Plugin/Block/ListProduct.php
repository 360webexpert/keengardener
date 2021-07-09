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

namespace Mageplaza\Shopbybrand\Plugin\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class ListProduct
 * @package Mageplaza\Shopbybrand\Plugin\Block
 */
class ListProduct
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var BrandFactory
     */
    protected $_brandFactory;

    /**
     * ListProduct constructor.
     *
     * @param Data $helper
     * @param BrandFactory $brandFactory
     */
    public function __construct(
        Data $helper,
        BrandFactory $brandFactory
    ) {
        $this->helper = $helper;
        $this->_brandFactory = $brandFactory;
    }

    /**
     * @param $product
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductBrand($product)
    {
        $attCode = $this->helper->getAttributeCode();
        $objectManager = ObjectManager::getInstance();
        $product = $objectManager->create(Product::class)->load($product->getId());
        $optionId = $product->getData($attCode);

        return $this->_brandFactory->create()->loadByOption($optionId)->getValue();
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $listProduct
     * @param callable $proceed
     * @param Product $product
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        callable $proceed,
        Product $product
    ) {
        return $this->helper->getModuleConfig('brandpage/show_brandname')
            ? $this->getProductBrand($product) . $proceed($product)
            : $proceed($product);
    }
}
