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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Model\Term\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TargetType
 * @package Mageplaza\SeoCrosslinks\Model\Term\Source
 */
class TargetType implements ArrayInterface
{
    const CUSTOM_URL  = 1;
    const PRODUCT_SKU = 2;
    const CATEGORY    = 3;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::CUSTOM_URL,
                'label' => __('Custom URL')
            ],
            [
                'value' => self::PRODUCT_SKU,
                'label' => __('Product SKU')
            ],
            [
                'value' => self::CATEGORY,
                'label' => __('Category')
            ],
        ];

        return $options;
    }
}
