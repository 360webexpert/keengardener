<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentAction
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class PaymentActionRepeat implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_REPEAT,
                'label' => __('Payment - Authorize and Capture'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_REPEAT_DEFERRED,
                'label' => __('Defer - Authorize Only'),
            ]
        ];
    }
}
