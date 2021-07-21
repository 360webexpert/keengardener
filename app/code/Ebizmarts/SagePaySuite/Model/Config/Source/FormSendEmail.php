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
class FormSendEmail implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_FORM_SEND_EMAIL_BOTH,
                'label' => __('Send customer and vendor emails'),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_FORM_SEND_EMAIL_NONE,
                'label' => __('Do not send either customer or vendor emails')
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_FORM_SEND_EMAIL_ONLY_VENDOR,
                'label' => __('Send vendor email but NOT the customer email')
            ]
        ];
    }
}
