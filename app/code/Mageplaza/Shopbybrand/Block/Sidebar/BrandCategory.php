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

namespace Mageplaza\Shopbybrand\Block\Sidebar;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class BrandCategory
 *
 * @package Mageplaza\Shopbybrand\Block\Sidebar
 */
class BrandCategory extends Brand
{
    /**
     * Default category template
     *
     * @type string
     */
    protected $_template = 'Mageplaza_Shopbybrand::sidebar/category.phtml';

    /**
     * Default title sidebar category brand
     */
    const TITLE = 'Brand Category';
    /**
     * Default title sidebar category brand
     */
    const LIMIT = '7';

    /**
     * @param $cat
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCatUrl($cat)
    {
        return $this->helper()->getCatUrl($cat);
    }

    /**
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->helper->getSidebarConfig('category_brand/title') ?: self::TITLE;
    }

    /**
     * @return int|mixed
     */
    public function getLimit()
    {
        $limit = $this->helper->getSidebarConfig('category_brand/limit_categories') ?: self::LIMIT;

        $collectionSize = count($this->getCategories()->getData());
        $result = ($limit < $collectionSize) ? $limit : (string) $collectionSize;

        return $this->toString($result);
    }

    /**
     * @return mixed
     */
    public function showBrandQty()
    {
        return $this->helper->getSidebarConfig('category_brand/show_brand_qty');
    }
}
