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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductPosition
 * @package Mageplaza\LayeredNavigationUltimate\Model\Config\Source
 */
class ProductPosition implements ArrayInterface
{
    const TOPLINK    = 1;
    const FOOTERLINK = 2;
    const CATEGORY   = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('-- Please select --'),
                'value' => '',
            ],
            [
                'label' => __('Top link'),
                'value' => self::TOPLINK,
            ],
            [
                'label' => __('Footer link'),
                'value' => self::FOOTERLINK,
            ],
            [
                'label' => __('Category'),
                'value' => self::CATEGORY,
            ],
        ];
    }
}
