/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*jshint jquery:true*/

define([
    "jquery",
    'mage/storage',
    'mage/url',
    "jquery/ui",
    'mage/translate'
], function ($, storage, url, $t) {
    "use strict";

    $.widget('mage.sagepaysuiteFormForm', {
        "options": {
            "code": "sagepaysuiteform",
            "serviceUrl": ""
        },

        prepare: function (event, method) {
            if (method === this.options.code) {
                this.preparePayment();
            }
        },
        preparePayment: function () {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
            $('#edit_form').off('changePaymentData').on('changePaymentData', this.changePaymentData.bind(this));

            //this.options.serviceUrl = sagepaysuiteform_config.url.request;
        },
        changePaymentData: function () {
            console.log("changePaymentData");
        },
        fieldObserver: function () {
            console.log("fieldObserver");
        },
        submitAdminOrder: function () {

            var self = this;
            self.resetPaymentErrors();

            var serviceUrl = this.options.serviceUrl;

            jQuery.ajax({
                url: serviceUrl,
                data: {form_key: window.FORM_KEY},
                type: 'POST'
            }).done(function (response) {
                if (response.success) {
                    //set form data and submit
                    var form_form = document.getElementById(self.getCode() + '-form');
                    if (!form_form) {
                        form_form = document.createElement("form");
                        form_form.setAttribute('method',"post");
                        form_form.setAttribute('style',"display:none;");
                        var input_VPSProtocol = document.createElement("input");
                        input_VPSProtocol.setAttribute('type',"hidden");
                        input_VPSProtocol.setAttribute('name',"VPSProtocol");
                        form_form.appendChild(input_VPSProtocol);
                        var input_TxType = document.createElement("input");
                        input_TxType.setAttribute('type',"hidden");
                        input_TxType.setAttribute('name',"TxType");
                        form_form.appendChild(input_TxType);
                        var input_Vendor = document.createElement("input");
                        input_Vendor.setAttribute('type',"hidden");
                        input_Vendor.setAttribute('name',"Vendor");
                        form_form.appendChild(input_Vendor);
                        var input_Crypt = document.createElement("input");
                        input_Crypt.setAttribute('type',"hidden");
                        input_Crypt.setAttribute('name',"Crypt");
                        form_form.appendChild(input_Crypt);
                        document.getElementsByTagName('body')[0].appendChild(form_form);
                    }
                    form_form.setAttribute('action',response.redirect_url);
                    form_form.elements[0].setAttribute('value', response.vps_protocol);
                    form_form.elements[1].setAttribute('value', response.tx_type);
                    form_form.elements[2].setAttribute('value', response.vendor);
                    form_form.elements[3].setAttribute('value', response.crypt);
                    form_form.submit();
                } else {
                    self.showPaymentError(response.error_message);
                }
            });

            return false;
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
            span.style.display = "none";

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

    return $.mage.sagepaysuiteFormForm;
});