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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Model\Term\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class LinkTarget
 * @package Mageplaza\SeoCrosslinks\Model\Term\Source
 */
class LinkTarget implements ArrayInterface
{
    const _BLANK_NEW_TAB               = 1;
    const _SELF_CURRENT_TAB            = 2;
    const _TOP_FULL_BODY_OF_THE_WINDOW = 3;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::_BLANK_NEW_TAB,
                'label' => __('_blank (New tab)')
            ],
            [
                'value' => self::_SELF_CURRENT_TAB,
                'label' => __('_self (current tab)')
            ],
            [
                'value' => self::_TOP_FULL_BODY_OF_THE_WINDOW,
                'label' => __('_top (full body of the window)')
            ],
        ];

        return $options;
    }
}
