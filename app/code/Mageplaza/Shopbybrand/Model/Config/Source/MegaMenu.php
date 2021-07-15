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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class MegaMenu
 *
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class MegaMenu implements ArrayInterface
{
    /**
     * Display listing
     */
    const MENU_LISTING = 0;
    /**
     * Display mega listing
     */
    const MENU_MEGA_LISTING = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Listing'),
                'value' => self::MENU_LISTING
            ],
            [
                'label' => __('Mega Listing'),
                'value' => self::MENU_MEGA_LISTING
            ]
        ];
    }
}
