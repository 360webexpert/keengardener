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

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class Search
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class Search extends Brand
{
    /**
     * @return false|string
     * @throws NoSuchEntityException
     */
    public function getSearchData()
    {
        $searchData = [];
        foreach ($this->getCollection() as $brand) {
            $searchData[] = [
                'value'    => $brand->getValue(),
                'desc'     => $this->helper->getBrandDescription($brand, true),
                'image'    => $this->helper->getBrandImageUrl($brand),
                'brandUrl' => $this->helper->getBrandUrl($brand)
            ];
        }

        return json_encode($searchData);
    }

    /**
     * @return mixed
     */
    public function getMaxQueryResult()
    {
        return $this->helper->getSearchConfig('max_query_results') ?: 10;
    }

    /**
     * @return mixed
     */
    public function getMinSearchChar()
    {
        return $this->helper->getSearchConfig('min_search_chars') ?: 1;
    }

    /**
     * @return mixed
     */
    public function isVisibleImage()
    {
        return $this->helper->getSearchConfig('visible_images');
    }
}
