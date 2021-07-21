<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Mode
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class Mode implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_TEST,
                'label' => __('Test'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE,
                'label' => __('Live')
            ]
        ];
    }
}
