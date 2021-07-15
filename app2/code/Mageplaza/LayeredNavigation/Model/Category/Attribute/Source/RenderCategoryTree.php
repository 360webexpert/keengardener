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
 * Class RenderCategoryTree
 * @package Mageplaza\LayeredNavigation\Model\Category\Attribute\Source
 */
class RenderCategoryTree implements OptionSourceInterface
{
    const NO            = '0';
    const FULL_CATEGORY = '1';
    const CUSTOM        = '2';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO,
                'label' => __('No')
            ],
            [
                'value' => self::FULL_CATEGORY,
                'label' => __('Full Category Tree')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom Category Tree')
            ]
        ];
    }
}
