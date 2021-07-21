<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentAction
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class PaymentAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::ACTION_PAYMENT,
                'label' => __('Payment - Authorize and Capture'),
            ],
            [
                'value' => Config::ACTION_DEFER,
                'label' => __('Defer - Authorize Only'),
            ],
            [
                'value' => Config::ACTION_AUTHENTICATE,
                'label' => __('Authenticate - Authenticate Only'),
            ]
        ];
    }
}
