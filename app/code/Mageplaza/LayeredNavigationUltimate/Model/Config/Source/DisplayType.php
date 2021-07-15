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
 * Class ShowType
 * @package Mageplaza\LayeredNavigationUltimate\Model\Config\Source
 */
class DisplayType implements ArrayInterface
{
    const TYPE_NORMAL  = '0';
    const TYPE_HIDDEN  = '1';
    const TYPE_DEFAULT = '2';
    const TYPE_SCROLL  = '3';

    /**
     * @return array
     */
    public function getOptionForForm()
    {
        $options = $this->toOptionArray();
        $options[] = ['value' => self::TYPE_DEFAULT, 'label' => __('Use Config Settings')];

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_NORMAL,
                'label' => __('Normal')
            ],
            [
                'value' => self::TYPE_HIDDEN,
                'label' => __('Hidden')
            ],
            [
                'value' => self::TYPE_SCROLL,
                'label' => __('Scroll')
            ]
        ];
    }
}
