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
 * Class Type
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class Type implements ArrayInterface
{
    const PRODUCTS                = 'product';
    const CATEGORIES              = 'category';
    const PAGES                   = 'page';
    const LAYERED_NAVIGATION      = 'layered_navigation';
    const POSTS_BY_MAGEPLAZA_BLOG = 'mageplaza_blog_posts';
    /**
     * Default product type
     */
    const DEFAULT_TYPE = 'product';

    /**
     * Get rule type
     * @return array
     */
    public function toArray()
    {
        return [
            self::PRODUCTS           => __('Product'),
            self::CATEGORIES         => __('Category'),
            self::PAGES              => __('Pages'),
            self::LAYERED_NAVIGATION => __('Layered Navigation')
        ];
    }

    /**
     * Get default type
     * @return string
     */
    public function getDefaultType()
    {
        return self::DEFAULT_TYPE;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }
}
