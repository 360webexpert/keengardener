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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Display
 * @package Mageplaza\Search\Model\Config\Source
 */
class Display implements ArrayInterface
{
    const DISPLAY_PRICE = 'price';
    const DISPLAY_IMAGE = 'image';
    const DISPLAY_DESCRIPTION = 'description';

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
        array_unshift($options, [
            'label' => __('-- Please select --'),
            'value' => ''
        ]);

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISPLAY_PRICE => __('Price'),
            self::DISPLAY_IMAGE => __('Image'),
            self::DISPLAY_DESCRIPTION => __('Short Description')
        ];
    }
}
