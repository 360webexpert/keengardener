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
 * Class Status
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class Status implements ArrayInterface
{
    const ENABLE  = 1;
    const DISABLE = 0;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ENABLE,
                'label' => __('Enable')
            ],
            [
                'value' => self::DISABLE,
                'label' => __('Disable')
            ],
        ];

        return $options;
    }
}
