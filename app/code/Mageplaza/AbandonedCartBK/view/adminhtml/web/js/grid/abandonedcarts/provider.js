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
    'moment',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'twix',
    'chartBundle',
], function ($, Element, moment,priceUtils,$t) {
    'use strict';

    return Element.extend({
        defaults: {
            currentChart: 'cart_abandon_rate',
        },

        initialize: function () {
            this._super();
            this.showChart();
            return this;
        },

        processData: function (data) {
            var self = this;
            if (!this.firstLoad) {
                var period = $('.period select').val(),
                    dateRangeData = $('#daterange').data();

                var startDate = dateRangeData.startDate;
                var endDate = dateRangeData.endDate;
                var itr = startDate.twix(endDate).iterate(period);
                var range = {};
                while (itr.hasNext()) {
                    switch (period) {
                        case 'day':
                            range[itr.next().format("YYYY-MM-DD")] = {};
                            break;
                        case 'week':
                            range[itr.next().format("YYYY-WW")] = {};
                            break;
                        case 'month':
                            range[itr.next().format("YYYY-MM")] = {};
                            break;
                        case 'year':
                            range[itr.next().format("YYYY")] = {};
                            break;
                    }
                }
            }

            var items = data.items;
            var cartAbandonRateData = [],
                cartAbandonRate = 0,
                successFullCartRateData = [],
                successFullCartRate = 0,

                totalAbandonedCartsData = [],
                totalAbandonedCarts = 0,
                totalSuccessfulCartsData = [],
                totalSuccessfulCarts= 0,

                abandonedRevenueData = [],
                abandonedRevenue = 0,
                successfulCartsRevenueData = [],
                successfulCartsRevenue = 0,

                actionableAbandonedCartsData = [],
                actionableAbandonedCarts= 0,

                totalEmailAbandonedSentData = [],
                totalEmailAbandonedSent = 0,

                recapturableRevenueData = [],
                recapturableRevenue = 0,

                recapturedRevenueData = [],
                recapturedRevenue = 0,

                recapturedRateData = [],
                recapturedRate = 0,

                totalCartCheckoutSent = 0,

                periods = [];

            moment.twix();
            _.each(items, function (record, index) {
                record._rowIndex = index;
                cartAbandonRate += parseFloat(record['cart_abandon_rate']);
                successFullCartRate += parseFloat(record['successful_cart_rate']);
                totalAbandonedCarts += parseFloat(record['total_abandoned_carts']);
                totalSuccessfulCarts += parseFloat(record['number_of_successful_carts']);
                abandonedRevenue += parseFloat(record['total_abandoned_revenue'].replace(/[^0-9\.-]+/g, ""));
                successfulCartsRevenue += parseFloat(record['successful_carts_revenue'].replace(/[^0-9\.-]+/g, ""));
                actionableAbandonedCarts += parseFloat(record['actionable_abandoned_carts']);
                totalEmailAbandonedSent += parseFloat(record['total_email_abandoned_sent']);
                recapturableRevenue += parseFloat(record['recapturable_revenue'].replace(/[^0-9\.-]+/g, ""));
                recapturedRevenue += parseFloat(record['recaptured_revenue'].replace(/[^0-9\.-]+/g, ""));
                recapturedRate += parseFloat(record['recaptured_rate']);
                totalCartCheckoutSent += parseFloat(record['total_cart_checkout_sent']);


                if (typeof range !== "undefined") {
                    if (typeof range[record['period_time']] === "undefined") {
                        range[record['period_time']] = {};
                    }
                    range[record['period_time']].cart_abandon_rate = record['cart_abandon_rate'];
                    range[record['period_time']].successful_cart_rate = record['successful_cart_rate'];
                    range[record['period_time']].total_abandoned_carts = record['total_abandoned_carts'];
                    range[record['period_time']].number_of_successful_carts = record['number_of_successful_carts'];
                    range[record['period_time']].total_abandoned_revenue = record['total_abandoned_revenue'];
                    range[record['period_time']].successful_carts_revenue = record['successful_carts_revenue'];
                    range[record['period_time']].actionable_abandoned_carts = record['actionable_abandoned_carts'];
                    range[record['period_time']].total_email_abandoned_sent = record['total_email_abandoned_sent'];
                    range[record['period_time']].recapturable_revenue = record['recapturable_revenue'];
                    range[record['period_time']].recaptured_revenue = record['recaptured_revenue'];
                    range[record['period_time']].recaptured_rate = record['recaptured_rate'];

                }
            });
            if (typeof range !== "undefined") {
                _.each(range, function (value, key) {
                    periods.push(key);
                    cartAbandonRateData.push(self.convertValue(value['cart_abandon_rate'],"%"));
                    successFullCartRateData.push(self.convertValue(value['successful_cart_rate'],"%"));
                    totalAbandonedCartsData.push(self.convertValue(value['total_abandoned_carts']));
                    totalSuccessfulCartsData.push(self.convertValue(value['number_of_successful_carts']));
                    abandonedRevenueData.push(self.convertValue(value['total_abandoned_revenue'],'currency'));
                    successfulCartsRevenueData.push(self.convertValue(value['successful_carts_revenue'],'currency'));
                    actionableAbandonedCartsData.push(self.convertValue(value['actionable_abandoned_carts']));
                    totalEmailAbandonedSentData.push(self.convertValue(value['total_email_abandoned_sent']));
                    recapturableRevenueData.push(self.convertValue(value['recapturable_revenue'],'currency'));
                    recapturedRevenueData.push(self.convertValue(value['recaptured_revenue'],'currency'));
                    recapturedRateData.push(self.convertValue(value['recaptured_rate'],'%'));

                });


                var chartContainerAll = $('.chart-container-all');
                chartContainerAll.hide();

                self.destroyChart(window.cartRateReport);
                self.destroyChart(window.numberOfCartReport);
                self.destroyChart(window.revenueReport);
                self.destroyChart(window.actionableAbandonedCarts);
                self.destroyChart(window.totalEmailAbandonedSent);
                self.destroyChart(window.recapturableRevenue);
                self.destroyChart(window.recapturedRevenue);
                self.destroyChart(window.recapturedRate);

                var totalAbandonedCartRate = (totalAbandonedCarts === 0) ? 0 : totalAbandonedCarts/(totalAbandonedCarts+totalSuccessfulCarts);
                var totalRecapturedRate = (totalCartCheckoutSent === 0) ? 0 : totalCartCheckoutSent/totalEmailAbandonedSent;

                $('.mp-select-block.chart-toggle .value.cart_abandon_rate').text(parseFloat(totalAbandonedCartRate*100).toFixed(2)+'%');
                $('.mp-select-block.chart-toggle .value.number_of_card_report').text(totalAbandonedCarts);
                $('.mp-select-block.chart-toggle .value.revenue_report').text(self.convertCurrency(data)+abandonedRevenue);
                $('.mp-select-block.chart-toggle .value.actionable_abandoned_carts').text(actionableAbandonedCarts);
                $('.mp-select-block.chart-toggle .value.total_email_abandoned_sent').text(totalEmailAbandonedSent);
                $('.mp-select-block.chart-toggle .value.recapturable_revenue').text(self.convertCurrency(data)+recapturableRevenue);
                $('.mp-select-block.chart-toggle .value.recaptured_revenue').text(self.convertCurrency(data)+recapturedRevenue);
                $('.mp-select-block.chart-toggle .value.recaptured_rate').text(parseFloat(totalRecapturedRate*100).toFixed(2)+'%');

                if (items.length) {
                    var ctx1 = document.getElementById("cart_abandon_rate");
                    var ctx2 = document.getElementById("number_of_card_report");
                    var ctx3 = document.getElementById("revenue_report");
                    var ctx4 = document.getElementById("actionable_abandoned_carts");
                    var ctx5 = document.getElementById("total_email_abandoned_sent");
                    var ctx6 = document.getElementById("recapturable_revenue");
                    var ctx7 = document.getElementById("recaptured_revenue");
                    var ctx8 = document.getElementById("recaptured_rate");

                    window.cartRateReport = new Chart(ctx1, {
                        type: 'line',
                        data: self.getDataToDrawChart(periods,[$t('Successful Cart Rate'),$t('Abandoned Cart Rate')],
                            [successFullCartRateData,cartAbandonRateData],['#20a8d8','#6610f2']),
                        options: self.getOptionsToDraw(data,'%',true,'%')
                    });

                    window.numberOfCartReport = new Chart(ctx2,{
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[$t('Total Successful Carts'),$t('Total Abandoned Carts')],
                            [totalSuccessfulCartsData,totalAbandonedCartsData],['#20a8d8','#f8cb00']),
                        options: self.getOptionsToDraw(data,'cart(s)',true)
                    });

                    window.revenueReport = new Chart(ctx3,{
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[$t('Successful Carts Revenue'),$t('Abandoned Revenue')],
                            [successfulCartsRevenueData,abandonedRevenueData],['#20a8d8','#4dbd74']),
                        options: self.getOptionsToDraw(data,'currency',true,'currency')
                    });

                    window.actionableAbandonedCarts  = new Chart(ctx4,{
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[''],
                            [actionableAbandonedCartsData],['#63c2de']),
                        options: self.getOptionsToDraw(data, 'cart(s)',false,'email(s)')
                    });

                    window.totalEmailAbandonedSent = new Chart(ctx5,{
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[''],
                            [totalEmailAbandonedSentData],['#f8cb00']),
                        options: self.getOptionsToDraw(data, 'email(s)',false,'email(s)')
                    });

                    window.recapturableRevenue  = new Chart(ctx6,{
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[''],
                            [recapturableRevenueData],['#20a8d8']),
                        options: self.getOptionsToDraw(data, 'currency',false,'currency')
                    });


                    window.recapturedRevenue = new Chart(ctx7, {
                        type:'bar',
                        data: self.getDataToDrawChart(periods,[''],
                            [recapturedRevenueData],['#4dbd74']),
                        options: self.getOptionsToDraw(data, 'currency',false,'currency')
                    });

                    window.recapturedRate = new Chart(ctx8, {
                        type:'line',
                        data: self.getDataToDrawChart(periods,[''],
                            [recapturedRateData],['#6610f2']),
                        options: self.getOptionsToDraw(data, '%',false,'%')
                    });

                    $('.chart-container-all.' + self.currentChart).show();
                }
            }

            return data;
        },

        showChart: function () {
            var self = this;

            $('body').on('click', '.select-block .mp-select-block.chart-toggle', function () {
                var name = $(this).data('name');
                $(this).parents('.abandoned-menu-select-chart').siblings('.chart-container-all').hide();
                $('.chart-container-all.' + name).show();
                self.currentChart = name;
            });
        },

        convertCurrency: function(data){
            return data.formatPrice ? data.formatPrice.pattern.replace('%s','') : data.formatPrice;
        },

        destroyChart: function (chartName){
            if (typeof chartName !== 'undefined' && typeof chartName.destroy === 'function') {
                chartName.destroy();
            }
        },

        convertValue: function(value, type){
            if(type === 'currency'){
                return parseFloat(typeof value !== 'undefined' ? value.replace(/[^0-9\.-]+/g, "") : '0');
            }
            if(type === '%'){
                var  valueConverted = value || '0';
                return parseFloat( valueConverted !== '' ? valueConverted : '0');

            }
            return parseFloat(typeof value !== 'undefined' ? value : '0')
        },

        getDataToDrawChart(dataLabels ,datasetsLabel, datasetsData,colors){
             var datasets = {
                    labels: dataLabels,
                    datasets: [
                        {
                            label:'',
                            data: '',
                            fill: false,
                            borderColor: '',
                            backgroundColor: ''
                        }
                    ]

            };

            for(var i = 0;i<datasetsData.length; i++){
                datasets.datasets[i]={
                    label: datasetsLabel[i],
                    data: datasetsData[i],
                    fill: false,
                    borderColor: colors[i],
                    backgroundColor: colors[i]
                };

            }

            return datasets;
        },

        getOptionsToDraw: function (data,titleText, displayLegend , tooltipText) {
            var self  = this;
            var text = titleText;
            if(titleText === "currency"){
                text = self.convertCurrency(data);
            }

            return  {
                tooltips:self.getTooltips(data,tooltipText),
                title: {
                    display: true,
                    fontSize: 18,
                    position: 'left',
                    text: text
                },
                legend:{
                    display:displayLegend
                },
                barRoundness: 1,
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
            };
        },

        getTooltips(data, tooltipText){
            var self = this;

            return {
                callbacks: {
                    label: function (tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var index = tooltipItem.index;
                        if(tooltipText === '%') {
                            return data.labels[index] + ': ' + dataset.data[index] + '%';
                        }
                        if(tooltipText === 'currency'){
                            return  data.labels[index] + ': ' + (self.data.formatPrice ? priceUtils.formatPrice(dataset.data[index], self.data.formatPrice) : dataset.data[index]);
                        }
                        return data.labels[index] + ': ' + dataset.data[index];
                    },
                }
            }
        },
    });
});
