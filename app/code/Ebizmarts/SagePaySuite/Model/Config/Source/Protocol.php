<?php

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Protocol
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class Protocol implements ArrayInterface
{

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::VPS_PROTOCOL,
                'label' => __(\Ebizmarts\SagePaySuite\Model\Config::VPS_PROTOCOL),
            ],
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::VPS_PROTOCOL_FOUR,
                'label' => __('4.00 (3Dv2)')
            ]
        ];
    }
}
