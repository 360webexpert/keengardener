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

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Mageplaza\Shopbybrand\Block\Brand;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class BrandList
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class BrandList extends Brand
{
    protected $optionIds = [];

    /**
     * @var Collection
     */
    protected $brandCollection;

    /**
     * Get Brand List by First Character
     *
     * @param $char
     *
     * @return Collection|mixed
     */
    public function getCollectionByChar($char)
    {
        if (!$this->brandCollection) {
            $this->brandCollection = $this->getCollection(Data::BRAND_FIRST_CHAR);
        }
        $collection = clone $this->brandCollection;
        $sqlString = $this->helper->checkCharacter($char);
        $collection->getSelect()->where($sqlString);
        $this->optionIds[$char] = $this->getOptionIdsToFilter($collection);

        return $collection;
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
     * @param string $char
     *
     * @return mixed
     */
    public function getOptionIdByChar($char)
    {
        return $this->optionIds[$char];
    }

    /**
     * @param Collection $collection
     *
     * @return string
     */
    public function getOptionIdsToFilter($collection)
    {
        $optionIds = [];

        foreach ($collection as $brand) {
            $optionIds [] = $brand->getId();
        }
        $result = implode(',', $optionIds);
        unset($optionIds);

        return $result;
    }
}
