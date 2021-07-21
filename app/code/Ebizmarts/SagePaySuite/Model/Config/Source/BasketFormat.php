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
class BasketFormat implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::BASKETFORMAT_SAGE50,
                'label' => __('Sage50 compatible')
            ],
            [
                'value' => Config::BASKETFORMAT_XML,
                'label' => __('XML')
            ],
            [
                'value' => Config::BASKETFORMAT_DISABLED,
                'label' => __('Disabled')
            ]
        ];
    }
}
