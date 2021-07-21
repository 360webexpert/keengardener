<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ThreeDSecure
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class ThreeDSecure implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_3D_DEFAULT,
                'label' => __('Default: Use default MySagePay settings'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_3D_DISABLE,
                'label' => __('Disable: Disable authentication and rules')
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_3D_FORCE,
                'label' => __('Force: Apply authentication even if turned off')
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_3D_IGNORE,
                'label' => __('Force & Ignore: Apply authentication but ignore rules')
            ]
        ];
    }
}
