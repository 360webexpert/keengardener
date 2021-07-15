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
use Mageplaza\Shopbybrand\Helper\Data;

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
     * ListProduct constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $listProduct
     * @param callable $proceed
     * @param Product $product
     *
     * @return string
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        callable $proceed,
        Product $product
    ) {
        if (!$this->helper->isEnabled() || empty($this->helper->getAttributeCode())) {
            return $proceed($product);
        }

        return $this->helper->getConfigGeneral('show_brand_name')
            ? $product->getAttributeText($this->helper->getAttributeCode()) . $proceed($product)
            : $proceed($product);
    }
}
