<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Methods
 * @package Mageplaza\Osc\Model\System\Config\Source\Shipping
 */
class RealTimeReports implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $timeArray = [
            [
                'label' => __('7 days'),
                'value' => '7',
            ],
            [
                'label' => __('14 days'),
                'value' => '14',
            ],
            [
                'label' => __('28 days'),
                'value' => '28',
            ],
            [
                'label' => __('30 days'),
                'value' => '30',
            ],
        ];

        return $timeArray;
    }
}
