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
        'jquery',
        'underscore',
        'uiComponent',
        'mage/url',
        "Magento_Ui/js/modal/alert",
        "mage/translate"
    ],
    function ($, _, Component, url, alert, $t) {
        'use strict';

        createModalElement('shipping', 'Shipping Address Fields');
        createModalElement('billing', 'Billing Address Fields');
        createModalElement('more', 'More Fields');

        function createModalElement (element, modalTitle) {
            var popupTmpl = $('[data-role=mp_show_popup-tmpl-' + element + ']');

            if (popupTmpl.length) {
                var $dialog = $(popupTmpl.html().trim());
                $dialog.modal({
                    'type': 'popup',
                    title: $.mage.__(modalTitle),
                    buttons: [{
                        text: $.mage.__('Apply'),
                        class: 'action apply',
                        click: function () {
                            this.closeModal();
                            $.each($('#popup-fields .field input'), function () {
                                var className = $(this).attr('id');
                                if ($(this).is(':checked')) {
                                    $('.progress-title.' + className).show();
                                    $('.progress.' + className).show();
                                } else {
                                    $('.progress-title.' + className).hide();
                                    $('.progress.' + className).hide();
                                }
                            });
                        }
                    }],
                    'modalClass': 'mp-fields-select-popup',
                    'responsive': true,
                    'innerScroll': true,

                    /** @inheritdoc */
                    opened: function () {
                        $dialog.trigger('open');
                        // $(".modal-footer").hide();
                        $.each($('.progress-title'), function () {
                            var name = $(this).attr('name');
                            if ($(this).is(':visible')) {
                                $('#popup-fields .field input#' + name).prop("checked", true);
                            }
                        });

                    },

                    /** @inheritdoc */
                    closed: function () {
                        $dialog.trigger('close');
                    }

                });
                $('.' + element + '-fields').on('click', function () {
                    $dialog.modal("openModal");
                });

            }
        }

        return Component.extend({
            params: {},

            initialize: function () {
                this.initParams();
                this.initChart();
                return this;
            },

            setData: function (params) {
                this.params = params;
            },

            initChart: function () {
                var self          = this;
                var ajaxUrl       = url.build('index/shoppingbehavior'),
                    dateRangeData = $('#daterange').data();
                var params        = {};
                if (typeof this.params.mpFilter !== 'undefined') {
                    params = this.params.mpFilter;
                } else {
                    params.mpFilter                      = {};
                    params.mpFilter['customer_group_id'] = $('.customer-group select').val();
                    params.mpFilter.startDate            = dateRangeData.startDate.format('Y-MM-DD');
                    params.mpFilter.endDate              = dateRangeData.endDate.format('Y-MM-DD');
                }
                if (typeof this.params.filters !== 'undefined') {
                    params.filters = this.params.filters;
                }
                $('.loading-mask').show();
                $.ajax({
                    url: ajaxUrl,
                    data: this.params,
                    method: 'POST',
                    success: function (res) {
                        $('.email-summary .number').text(res.data.emails);
                        $('.email-summary .percent').text('(' + Number((res.data.emails / res.data.carts) * 100).toFixed(2) + "%)");
                        $('.container .cart-summary').text(res.data.carts);

                        //Shipping Address Fields
                        self.fillDataToChart('shipping', res.data.shipping_fields, res.data.carts);

                        // Billing Address Fields
                        self.fillDataToChart('billing', res.data.billing_fields, res.data.carts);

                        //More Fields
                        self.fillDataToChart('more', res.data.more_fields, res.data.carts)

                    },
                    error: function () {
                        alert({
                            title: $t('Error'),
                            content: $t('Please submit again')
                        });
                    },
                    complete: function () {
                        $('.loading-mask').hide();
                    }
                });
            },

            fillDataToChart: function (type, data, carts) {
                _.each(data, function (value, key) {
                    var field         = '.' + type + '-' + key,
                        percent       = (value === 0 ? 0 : parseFloat(Number((value / carts) * 100).toFixed(2))),
                        progressField = $('.progress' + field);

                    $('.progress-title' + field + ' span').text('(' + value + '/' + carts + ")");
                    progressField.removeClass('red green yellow');
                    $(field + " .progress-bar").removeClass('progress-bar-danger progress-bar-success progress-bar-warning');

                    if (percent > 40 && percent <= 70) {
                        progressField.addClass('yellow');
                        $(field + " .progress-bar").addClass('progress-bar-warning');
                    } else if (percent > 70) {
                        progressField.addClass('green');
                        $(field + " .progress-bar").addClass('progress-bar-success');
                    } else if (percent > 0 && percent < 40) {
                        progressField.addClass('red');
                        $(field + " .progress-bar").addClass('progress-bar-danger');
                    }
                    $('.progress-value' + field).text(percent + '%');
                    $(field + " .progress-bar").width(percent + '%');
                    if (value === 0) {
                        $(field + " .progress-bar").width('0%');
                    }
                });
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
