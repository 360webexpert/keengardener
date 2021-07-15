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
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Effect
 *
 * @package Mageplaza\SocialLogin\Model\System\Config\Source
 */
class Search implements ArrayInterface
{
    const PRODUCT_SEARCH = 'product_search';
    const NEW_PRODUCTS = 'new_products';
    const MOST_VIEWED_PRODUCTS = 'most_viewed_products';
    const BESTSELLERS = 'bestsellers';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NEW_PRODUCTS, 'label' => __('New Products')],
            ['value' => self::MOST_VIEWED_PRODUCTS, 'label' => __('Most Viewed Products')],
            ['value' => self::BESTSELLERS, 'label' => __('Bestsellers')]
        ];
    }
}
