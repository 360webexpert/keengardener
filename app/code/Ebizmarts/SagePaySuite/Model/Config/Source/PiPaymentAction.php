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
class PiPaymentAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray() : array
    {
        return [
            [
                'value' => Config::ACTION_PAYMENT_PI,
                'label' => __('Payment - Authorize and Capture'),
            ],
            [
                'value' => Config::ACTION_DEFER_PI,
                'label' => __('Defer - Authorize Only'),
            ],
        ];
    }
}
