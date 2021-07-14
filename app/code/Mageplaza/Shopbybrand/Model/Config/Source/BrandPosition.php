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
 * Class BrandPosition
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class BrandPosition implements OptionSourceInterface
{
    /**
     * Show on Toplink
     */
    const TOPLINK = '0';
    /**
     * Show on Footer link
     */
    const FOOTERLINK = '1';
    /**
     * Show on Menubar
     */
    const CATEGORY = '2';

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
                'label' => __('Toplink'),
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
