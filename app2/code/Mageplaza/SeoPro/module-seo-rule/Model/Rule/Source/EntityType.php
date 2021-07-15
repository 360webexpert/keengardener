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
use Mageplaza\SeoRule\Helper\Data;

/**
 * Class EntityType
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class EntityType implements ArrayInterface
{
    const PRODUCTS                = 'product';
    const CATEGORIES              = 'category';
    const PAGES                   = 'page';
    const LAYERED_NAVIGATION      = 'layered_navigation';
    const POSTS_BY_MAGEPLAZA_BLOG = 'mageplaza_blog_posts';

    /***
     * @var Data
     */
    protected $data;

    /**
     * EntityType constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::PRODUCTS,
                'label' => __('Product')
            ],
            [
                'value' => self::CATEGORIES,
                'label' => __('Category')
            ],
            [
                'value' => self::PAGES,
                'label' => __('Page')
            ],
            [
                'value' => self::LAYERED_NAVIGATION,
                'label' => __('Layered Navigation')
            ],
        ];

        return $options;
    }

    /**
     * @return array
     */
    public function listEntity()
    {
        $options = [
            [
                'value' => self::PRODUCTS,
                'label' => __('Products')
            ],
            [
                'value' => self::CATEGORIES,
                'label' => __('Categories')
            ],
            [
                'value' => self::PAGES,
                'label' => __('Pages')
            ],
            [
                'value' => self::LAYERED_NAVIGATION,
                'label' => __('Layered Navigation')
            ],
        ];

        return $options;
    }
}
