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

namespace Mageplaza\Shopbybrand\Block\Brand;

use Mageplaza\Shopbybrand\Block\Brand;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class BrandList
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class BrandList extends Brand
{
    /**
     * @inheritdoc
     */
    public function getCollection($type = null, $option = null, $char = null)
    {
        $brandCollection = [];

        $collection = parent::getCollection($type, $option, $char);
        foreach ($collection as $brand) {
            $qty = $this->getProductQuantity($brand->getOptionId());
            if ($qty) {
                $brand->setProductQuantity($qty);
                $brandCollection[] = $brand;
            }
        }

        return $brandCollection;
    }

    /**
     * Get Brand List by First Char
     *
     * @param $char
     *
     * @return mixed
     */
    public function getCollectionByChar($char)
    {
        return $this->getCollection(Data::BRAND_FIRST_CHAR, null, $char);
    }

    /**
     * Get Category Filter Class for Mixitup
     *
     * @param $optionId
     *
     * @return string
     */
    public function getCatFilterClass($optionId)
    {
        return $this->helper->getCatFilterClass($optionId);
    }

    /**
     * @param $catName
     *
     * @return mixed
     */
    public function getCatNameFilter($catName)
    {
        return str_replace([' ', '*', '/', '\\'], '_', $catName);
    }

    /**
     * @param $char
     *
     * @return string
     */
    public function getOptionIdsByChar($char)
    {
        $optionIds = [];

        $brandCollection = $this->getCollectionByChar($char);
        foreach ($brandCollection as $brand) {
            $optionIds [] = $brand->getId();
        }
        $result = implode(',', $optionIds);
        unset($optionIds);

        return $result;
    }
}
