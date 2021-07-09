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
 * Class SliderType
 * @package Mageplaza\LayeredNavigationUltimate\Model\Config\Source
 */
class SliderType implements ArrayInterface
{
    const TYPE_FLAT    = '1';
    const TYPE_DEFAULT = '2';
    const TYPE_HTML    = '3';
    const TYPE_MODERN  = '4';
    const TYPE_NICE    = '5';
    const TYPE_SIMPLE  = '6';

    /**
     * Css file name for ion range slider
     *
     * @param $type
     *
     * @return string
     */
    public static function getFileName($type)
    {
        $fileName = 'ion.rangeSlider.skin';
        switch ($type) {
            case self::TYPE_FLAT:
                $fileName .= 'Flat';
                break;
            case self::TYPE_HTML:
                $fileName .= 'HTML5';
                break;
            case self::TYPE_MODERN:
                $fileName .= 'Modern';
                break;
            case self::TYPE_NICE:
                $fileName .= 'Nice';
                break;
            default:
                $fileName .= 'Simple';
        }

        return $fileName . '.css';
    }

    /**
     * @return array
     */
    public function getOptionForForm()
    {
        $options = $this->toOptionArray();
        $options[] = [
            ['value' => self::TYPE_DEFAULT, 'label' => __('Use Config Settings')]
        ];

        return $options;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getOptionHash() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::TYPE_DEFAULT => __('Default'),
            self::TYPE_FLAT    => __('Flat UI'),
            self::TYPE_HTML    => __('Html5'),
            self::TYPE_MODERN  => __('Modern'),
            self::TYPE_NICE    => __('Nice white'),
            self::TYPE_SIMPLE  => __('Simple dark')
        ];
    }
}
