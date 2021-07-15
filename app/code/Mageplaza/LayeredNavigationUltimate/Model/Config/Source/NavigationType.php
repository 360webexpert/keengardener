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
 * Class NavigationType
 * @package Mageplaza\LayeredNavigationUltimate\Model\Config\Source
 */
class NavigationType implements ArrayInterface
{
    const VERTICAL   = 0;
    const HORIZONTAL = 1;
    const BOTH       = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::VERTICAL,
                'label' => __('Vertical')
            ],
            [
                'value' => self::HORIZONTAL,
                'label' => __('Horizontal')
            ],
            [
                'value' => self::BOTH,
                'label' => __('Both')
            ]
        ];
    }
}
