<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model\Config\Source;

/**
 * Class ApplyEstimationRange
 * @package WeSupply\Toolbox\Model\Config\Source
 */

class ApplyEstimationRange
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'all_shipping_methods',
                'label' => __('All Shipping Methods')
            ],
            [
                'value' => 'specific_shipping_methods',
                'label' => __('Specific Shipping Methods')
            ]
        ];
    }
}