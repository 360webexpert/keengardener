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
 * Class Direction
 * @package Mageplaza\SeoCrosslinks\Model\Term\Source
 */
class Direction implements ArrayInterface
{
    const TOP_DOWN  = 0;
    const BOTTOM_UP = 1;
    const RANDOM    = 2;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::TOP_DOWN,
                'label' => __('Top Down')
            ],
            [
                'value' => self::BOTTOM_UP,
                'label' => __('Bottom Up')
            ],
            [
                'value' => self::RANDOM,
                'label' => __('Random')
            ],
        ];

        return $options;
    }
}
