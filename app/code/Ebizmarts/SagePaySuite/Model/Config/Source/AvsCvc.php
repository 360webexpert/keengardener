<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class ThreeDSecure
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class AvsCvc implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::MODE_AVSCVC_DEFAULT,
                'label' => __('Default: Use default MySagePay settings'),
            ],
            [
                'value' => Config::MODE_AVSCVC_DISABLE,
                'label' => __('Disable: Disable authentication and rules')
            ],
            [
                'value' => Config::MODE_AVSCVC_FORCE,
                'label' => __('Force: Apply authentication even if turned off')
            ],
            [
                'value' => Config::MODE_AVSCVC_IGNORE,
                'label' => __('Force & Ignore: Apply authentication but ignore rules')
            ]
        ];
    }
}
