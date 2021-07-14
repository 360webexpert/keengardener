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

define([
    'jquery',
    'mage/translate',
    'chartBundle'
], function ($, $t) {
    'use strict';
    $.widget('mageplaza.abandonedcartChart', {
        options: {
            chartData: {}
        },
        _create: function () {
            self = this;
            var ctx = $('#' + this.options.chartData.name + '-chart'),
                data = {
                    type: 'bar',
                    data: {
                        index: this.options.chartData.name,
                        labels: this.options.chartData['data']['date'],
                        datasets: [
                            {
                                label: $t('Abandoned Cart'),
                                data: this.options.chartData['data']['abandonedCart'],
                                backgroundColor: 'rgba(54, 162, 235, 1)',
                            },
                            {
                                label: $t('Sent'),
                                data: this.options.chartData['data']['sent'],
                                backgroundColor: 'rgba(255, 206, 86, 1)',
                            },
                            {
                                label: $t('Recovered'),
                                data: this.options.chartData['data']['recovery'],
                                backgroundColor: 'rgba(75, 192, 192, 1)',
                            },
                            {
                                label: $t('Error'),
                                data: this.options.chartData['data']['error'],
                                backgroundColor: 'rgba(255,99,132,1)',
                            }
                        ]
                    },
                    options: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                fontColor: '#333'
                            }
                        }
                    }
                };

            new Chart(ctx, data);
        }
    });

    return $.mageplaza.abandonedcartChart;
});
