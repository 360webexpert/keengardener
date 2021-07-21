<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class Currency implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CURRENCY_BASE,
                'label' => __('Base Currency'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CURRENCY_SWITCHER,
                'label' => __('Currency Switcher')
            ]
        ];
    }
}
