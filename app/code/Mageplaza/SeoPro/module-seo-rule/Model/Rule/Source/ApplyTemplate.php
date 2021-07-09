<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\Rule\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ApplyTemplate
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class ApplyTemplate implements ArrayInterface
{
    const SKIP_IF_READY_DEFINED = 'skip';
    const FORCE_UPDATE          = 'force';

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::SKIP_IF_READY_DEFINED,
                'label' => __('Skip if ready defined')
            ],
            [
                'value' => self::FORCE_UPDATE,
                'label' => __('Force update')
            ],
        ];

        return $options;
    }
}
