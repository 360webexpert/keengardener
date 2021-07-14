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

namespace Mageplaza\Shopbybrand\Block\Link;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class CategoryMenu
 *
 * @package Mageplaza\Shopbybrand\Block\Link
 */
class CategoryMenu extends Brand
{

    /**
     * @return array
     */
    public function getBrands()
    {
        $brands = $this->getCollection();
        $limit  = $this->getLimit();
        $result = [];
        $i      = 0;
        $count  = 0;
        foreach ($brands as $brand) {
            $count++;
            $result[$i][] = $brand;
            if ($count === $limit) {
                $count = 0;
                $i++;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function showBrandMenu()
    {
        return $this->helper->getConfigGeneral('show_dropdown');
    }

    /**
     * @return mixed
     */
    public function showBrandInfo()
    {
        return $this->helper->getConfigGeneral('show_brand_menu');
    }

    /**
     * @return string
     */
    public function getBrandTitle()
    {
        return $this->helper->getBrandTitle();
    }

    /**
     * @param \Mageplaza\Shopbybrand\Model\Brand $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandUrl($brand = null)
    {
        return $this->helper->getBrandUrl($brand);
    }

    /**
     * @param \Mageplaza\Shopbybrand\Model\Brand $brand
     *
     * @return string
     */
    public function getBrandImageUrl($brand)
    {
        return $this->helper->getBrandImageUrl($brand);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->helper->getConfigGeneral('limit_brands');
    }

    /**
     * @return array|mixed
     */
    public function isShowBrandsWithoutProducts()
    {
        return $this->helper->getConfigGeneral('show_brands_without_products');
    }

    /**
     * @return array|mixed
     */
    public function getGridColumns()
    {
        return $this->helper->getConfigGeneral('grid_columns');
    }
}
