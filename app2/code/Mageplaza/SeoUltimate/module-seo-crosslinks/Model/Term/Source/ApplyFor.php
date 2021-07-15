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
 * Class ApplyFor
 * @package Mageplaza\SeoCrosslinks\Model\Term\Source
 */
class ApplyFor implements ArrayInterface
{
    const PRODUCT  = 0;
    const CATEGORY = 1;
    const PAGE     = 2;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::PRODUCT,
                'label' => __('Product Description')
            ],
            [
                'value' => self::CATEGORY,
                'label' => __('Category Description')
            ],
            [
                'value' => self::PAGE,
                'label' => __('Page Content')
            ],
        ];

        return $options;
    }
}
