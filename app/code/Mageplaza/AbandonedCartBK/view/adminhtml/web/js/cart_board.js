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

/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'jquery',
        'underscore',
        'uiComponent',
        'mage/url',
        "Magento_Ui/js/modal/alert",
        "mage/translate"
    ],
    function (ko, $, _, Component, url,alert,$t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_AbandonedCart/cart_board'
            },
            params: {},
            realtime: ko.observable(),
            abandoned: ko.observable(),
            recoverable: ko.observable(),
            converted: ko.observable(),

            initialize: function () {
                this._super();
                this.initParams();
                this.initData();
                return this;
            },

            initData: function () {
                var self          = this;
                var ajaxUrl       = url.build('index/cartboard'),
                    dateRangeData = $('#daterange').data();
                var params        = {};
                if (typeof this.params.mpFilter !== 'undefined') {
                    params = this.params.mpFilter;
                } else {
                    params.mpFilter                      = {};
                    params.mpFilter.startDate            = dateRangeData.startDate.format('Y-MM-DD');
                    params.mpFilter.endDate              = dateRangeData.endDate.format('Y-MM-DD');
                }
                if (typeof this.params.filters !== 'undefined') {
                    params.filters = this.params.filters;
                }

                $.ajax({
                    url: ajaxUrl,
                    data: this.params,
                    method: 'POST',
                    success: function (res) {
                        self.realtime(res.data.realtime);
                        self.abandoned(res.data.abandoned);
                        self.recoverable(res.data.recoverable);
                        self.converted(res.data.converted);
                    },
                    error: function () {
                        alert({
                            title: $t('Error'),
                            content: $t('Please submit again')
                        });
                    }
                });
            },

            getRealtimeData: function (){
                return this.realtime();
            },

            getAbandonedData: function () {
                return this.abandoned();
            },

            getRecoverableData: function () {
                return this.recoverable();
            },

            getConvertedData: function () {
                return this.converted();
            },

            initParams: function () {
                if (typeof this.params.mpFilter === "undefined") {
                    this.params.mpFilter = {};
                }
                if (typeof this.params.mpFilter.startDate === "undefined") {
                    this.params.mpFilter.startDate = $('#daterange').data().startDate.format('Y-MM-DD');
                }
                if (typeof this.params.mpFilter.endDate === "undefined") {
                    this.params.mpFilter.endDate = $('#daterange').data().endDate.format('Y-MM-DD');
                }
                if (typeof this.params.mpFilter.store === "undefined") {
                    this.params.mpFilter.store = $('#store_switcher').val();
                }
                if (typeof this.params.mpFilter.customer_group_id === "undefined") {
                    this.params.mpFilter.customer_group_id = $('.customer-group select').val();
                }
            }
        });
    }
);
