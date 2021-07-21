<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class FraudNotify
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class FraudNotify implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => "disabled",
                'label' => __('Disabled'),
            ],
            [
                'value' => "medium_risk",
                'label' => __('Medium and high risk transactions')
            ],
            [
                'value' => "high_risk",
                'label' => __('High risk transactions only')
            ]
        ];
    }
}
