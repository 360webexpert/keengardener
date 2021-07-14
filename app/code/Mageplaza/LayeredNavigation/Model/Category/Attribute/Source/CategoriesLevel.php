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
 * Class CategoriesLevel
 * @package Mageplaza\LayeredNavigation\Model\Category\Attribute\Source
 */
class CategoriesLevel implements OptionSourceInterface
{
    const ROOT_CATEGORY              = '0';
    const CURRENT_CATEGORY           = '1';
    const CURRENT_CATEGORY_CHILDRENS = '2';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ROOT_CATEGORY,
                'label' => __('Root Category')
            ],
            [
                'value' => self::CURRENT_CATEGORY,
                'label' => __('Current Category')
            ],
            [
                'value' => self::CURRENT_CATEGORY_CHILDRENS,
                'label' => __('Current Category Childrens')
            ]
        ];
    }
}
