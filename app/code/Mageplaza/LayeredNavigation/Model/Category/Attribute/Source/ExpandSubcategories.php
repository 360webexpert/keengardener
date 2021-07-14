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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\Category\Attribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ExpandSubcategories
 * @package Mageplaza\LayeredNavigation\Model\Category\Attribute\Source
 */
class ExpandSubcategories implements OptionSourceInterface
{
    const AUTO  = 'auto';
    const CLICK = 'click';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTO,
                'label' => __('Auto')
            ],
            [
                'value' => self::CLICK,
                'label' => __('Click')
            ]
        ];
    }
}
