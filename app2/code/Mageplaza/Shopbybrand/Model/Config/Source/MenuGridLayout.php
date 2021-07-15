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
 * Class MenuGridLayout
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class MenuGridLayout implements OptionSourceInterface
{
    const COLUMNS_2 = '2';
    const COLUMNS_3 = '3';
    const COLUMNS_4 = '4';

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('2 Columns'),
                'value' => self::COLUMNS_2
            ],
            [
                'label' => __('3 Columns'),
                'value' => self::COLUMNS_3
            ],
            [
                'label' => __('4 Columns'),
                'value' => self::COLUMNS_4
            ]
        ];
    }
}
