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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Chart;

use Magento\Backend\Block\Template;

/**
 * Class AbandonedCarts
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Chart
 */
class CartCheckout extends Template
{
    /**
     * @return array
     */
    public function getTypeCharts()
    {
        return
            [
                'cart_abandon_rate'          => __('Cart Abandonment Rate'),
                'number_of_card_report'      => __('Number of Carts Report'),
                'revenue_report'             => __('Revenue Report'),
                'actionable_abandoned_carts' => __('Actionable Abandoned Carts'),
                'total_email_abandoned_sent' => __('Total Abandonment Emails Sent'),
                'recapturable_revenue'       => __('Recapturable Revenue'),
                'recaptured_revenue'         => __('Recaptured Revenue'),
                'recaptured_rate'            => __('Recaptured Rate')
            ];
    }

    /**
     * @return array
     */
    public function getSelectCharts()
    {
        return
            [
                'number_of_card_report'      => [
                    'color' => 'mp-bg-orange',
                    'label' => __('Total Abandoned Carts: ')
                ],
                'actionable_abandoned_carts' => [
                    'color' => 'mp-bg-info',
                    'label' => __('Actionable Abandoned Carts: ')
                ],
                'revenue_report'             => [
                    'color' => 'mp-bg-success',
                    'label' => __('Abandoned Revenue: ')
                ],
                'cart_abandon_rate'          => [
                    'color' => 'mp-bg-indigo',
                    'label' => __('Abandoned Cart Rate: ')
                ],
                'total_email_abandoned_sent' => [
                    'color' => 'mp-bg-orange',
                    'label' => __('Total Abandonment Emails Sent: ')
                ],
                'recapturable_revenue'       => [
                    'color' => 'mp-bg-info',
                    'label' => __('Recapturable Revenue: ')
                ],
                'recaptured_revenue'         => [
                    'color' => 'mp-bg-success',
                    'label' => __('Recaptured Revenue: ')
                ],
                'recaptured_rate'            => [
                    'color' => 'mp-bg-indigo',
                    'label' => __('Recaptured Rate: ')
                ],
            ];
    }
}
