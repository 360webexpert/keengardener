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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MegaMenu
 *
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class MegaMenu implements OptionSourceInterface
{
    const NO       = '0';
    const DROPDOWN = '1';
    const GRID     = '2';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('No'),
                'value' => self::NO
            ],
            [
                'label' => __('Drop-down type'),
                'value' => self::DROPDOWN
            ],
            [
                'label' => __('Grid type'),
                'value' => self::GRID
            ]
        ];
    }
}
