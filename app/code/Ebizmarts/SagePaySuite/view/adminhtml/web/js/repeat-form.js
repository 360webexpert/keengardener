/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*jshint jquery:true*/

define([
    "jquery",
    'mage/url',
    "jquery/ui"
], function ($, url) {
    "use strict";

    /**
     * Disable card server validation in admin
     */
    if (typeof variable !== 'undefined') {
    order.addExcludedPaymentMethod('sagepaysuiterepeat');
    }

    $.widget('mage.sagepaysuiteRepeatForm', {
        options: {
            code: "sagepaysuiterepeat"
        },

        prepare: function (event, method) {
            if (method === this.options.code) {
                this.preparePayment();
            }
        },
        preparePayment: function () {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
            $('#edit_form').off('changePaymentData').on('changePaymentData', this.changePaymentData.bind(this));
        },
        changePaymentData: function () {
            //console.log("changePaymentData");
        },
        fieldObserver: function () {
            //console.log("fieldObserver");
        },
        submitAdminOrder: function () {

            var self = this;
            self.resetPaymentErrors();

            var serviceUrl = this.options.url.request;

            var payload = {
                vpstxid: $('#' + self.getCode() + '_vpstxid').val(),
                form_key: window.FORM_KEY
            };

            jQuery.ajax({
                url: serviceUrl,
                data: payload,
                type: 'POST'
            }).done(function (response) {
                if (response.success == true) {
                    //redirect to success
                    window.location.href = response.response.data.redirect;
                } else {
                    console.log(response);
                    self.showPaymentError(response.error_message ? response.error_message : "Invalid Opayo response.");
                }
            });
        },
        getCode: function () {
            return this.options.code;
        },
        showPaymentError: function (message) {

            var span = document.getElementById(this.getCode() + '-payment-errors');

            span.innerHTML = message;
            span.style.display = "block";

            $('#edit_form').trigger('processStop');
            $('body').trigger('processStop');
        },
        resetPaymentErrors: function () {
            var span = document.getElementById(this.getCode() + '-payment-errors');
            if (span) {
                span.style.display = "none";
            }
        },
        _create: function () {
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').on('changePaymentData', this.changePaymentData.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
        }
    });

    return $.mage.sagepaysuiteRepeatForm;
});