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
    'Mageplaza_AbandonedCart/js/grid/abstract-provider',
    'mage/url',
    "Magento_Ui/js/modal/alert",
    'mage/translate',
    'chartBundle'
], function ($, Element, url, alert ,$t) {
    'use strict';

    return Element.extend({
        reload: function (options) {
            var ajaxUrl = url.build('index/productreport'),
                dateRangeData = $('#daterange').data();
            var params = {};
            if (typeof this.params.mpFilter !== 'undefined') {
                params = this.params.mpFilter;
            } else {
                params.mpFilter = {};
                params.mpFilter['customer_group_id'] = $('.customer-group select').val();
                params.mpFilter.store = $('#store_switcher').val();
                params.mpFilter.period = $('.period select').val();
                params.mpFilter.startDate = dateRangeData.startDate.format('Y-MM-DD');
                params.mpFilter.endDate = dateRangeData.endDate.format('Y-MM-DD');
            }
            if (typeof this.params.filters !== 'undefined') {
                params.filters = this.params.filters;
            }
            params.isChartDetail = 1;
            $('.chart-container-all').hide();
            if (typeof window.myChart !== 'undefined' && typeof window.myChart.destroy === 'function') {
                window.myChart.destroy();
            }
            $.ajax({
                url: ajaxUrl,
                data: params,
                method: 'POST',
                success: function (res) {
                    var ctx = document.getElementById("myChart").getContext('2d');
                    $('.chart-container-all').show();
                    window.myChart = new Chart(ctx, {
                        type: 'bar',
                        data: res.chart,
                        options: {
                            barRoundness: 1,
                            title: {
                                display: true,
                                fontSize: 18,
                                position: 'left',
                                text: $t('Abandoned Time(s)')
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }],
                                xAxes: [{
                                    display: true,
                                    labelString: 'Time'
                                }]
                            }
                        }
                    });
                },
                error: function () {
                    alert({
                        title: $t('Error'),
                        content: $t('Please submit again')
                    });
                },
            });
            this._super();
        }
    });
});