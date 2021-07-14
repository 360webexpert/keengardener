<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\Rule\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ApplyFor
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class ApplyFor implements ArrayInterface
{
    const ALL                   = 'all';
    const PRODUCT_ATTRIBUTE_SET = 'attribute_set';
    const SPECIFIC_PRODUCTS     = 'specific_products';
    const SPECIFIC_PAGE         = 'specific_page';
    const SPECIFIC_CATEGORIES   = 'specific_categories';
    const SPECIFIC_POSTS        = 'specific_posts';
    const ATTRIBUTE             = 'attribute';

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ALL,
                'label' => __('All')
            ],
            [
                'value' => self::PRODUCT_ATTRIBUTE_SET,
                'label' => __('Product Attribute Set')
            ],
            [
                'value' => self::SPECIFIC_PRODUCTS,
                'label' => __('Specific Products')
            ],
            [
                'value' => self::SPECIFIC_PAGE,
                'label' => __('Specific Page')
            ],
            [
                'value' => self::SPECIFIC_CATEGORIES,
                'label' => __('Specific Categories')
            ],
            [
                'value' => self::ATTRIBUTE,
                'label' => __('Attribute')
            ],
            [
                'value' => self::SPECIFIC_POSTS,
                'label' => __('Specific Posts')
            ]

        ];

        return $options;
    }
}
