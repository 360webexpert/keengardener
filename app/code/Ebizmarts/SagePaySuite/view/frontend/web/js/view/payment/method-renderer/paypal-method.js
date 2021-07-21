/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, Component, storage, url, urlBuilder, customer, quote, fullScreenLoader, additionalValidators, customerData, setPaymentInformation) {
        'use strict';

        $(document).ready(function () {
            var paypalConfig = window.checkoutConfig.payment.ebizmarts_sagepaysuitepaypal;
            if (paypalConfig && !paypalConfig.licensed) {
                $("#payment .step-title").after('<div class="message error" style="margin-top: 5px;border: 1px solid red;">WARNING: Your Opayo Suite license is invalid.</div>');
            }
        });

        return Component.extend({
            defaults: {
                template: 'Ebizmarts_SagePaySuite/payment/paypal-form'
            },
            getCode: function () {
                return 'sagepaysuitepaypal';
            },
            /** Returns payment information data */
            getData: function () {
                return $.extend(true, this._super(), {'additional_data': null});
            },
            preparePayment: function () {

                var self = this;
                self.resetPaymentErrors();

                //validations
                if (!this.validate() || !additionalValidators.validate()) {
                    return false;
                }

                fullScreenLoader.startLoader();

                /**
                 * Save billing address
                 * Checkout for guest and registered customer.
                 */
                var serviceUrl,
                    payload;
                if (!customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/billing-address', {
                        cartId: quote.getQuoteId()
                    });
                    payload = {
                        cartId: quote.getQuoteId(),
                        address: quote.billingAddress()
                    };
                } else {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/billing-address', {});
                    payload = {
                        cartId: quote.getQuoteId(),
                        address: quote.billingAddress()
                    };
                }

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function () {
                        var paymentData = {method: self.getCode()};

                        $.when(
                            setPaymentInformation(this.messageContainer, paymentData)
                        ).done(
                            function () {

                                var paypalServiceUrl = null;
                                if (customer.isLoggedIn()) {
                                    paypalServiceUrl = urlBuilder.createUrl(
                                        '/sagepay/paypal/:cartId',
                                        {cartId: quote.getQuoteId()}
                                    );
                                } else {
                                    paypalServiceUrl = urlBuilder.createUrl(
                                        '/sagepay/paypal-guest/:cartId',
                                        {cartId: quote.getQuoteId()}
                                    );
                                }

                                //generate crypt and form data
                                storage.get(paypalServiceUrl)
                                    .done(
                                        function (response) {
                                            if (response.success) {
                                                var sagePayResponse = $.parseJSON(response.response[1]);
                                                var redirectUrl = sagePayResponse.PayPalRedirectURL;
                                                if (redirectUrl) {
                                                    customerData.invalidate(['cart']);
                                                    customerData.invalidate(['checkout-data']);

                                                    window.location.href = redirectUrl;
                                                } else {
                                                    self.showPaymentError("Invalid response from PayPal, please try again later.");
                                                }
                                            } else {
                                                self.showPaymentError(response.error_message);
                                            }
                                        }
                                    ).fail(
                                    function (response) {
                                        self.showPaymentError("Unable to submit form to PayPal.");
                                    }
                                );
                            }
                        ).fail(
                            function (response) {
                                if (response.responseJSON) {
                                    self.showPaymentError(response.responseJSON.message);
                                } else {
                                    self.showPaymentError("Unable to save payment info.");
                                }
                            }
                        );
                    }
                ).fail(
                    function (response) {
                        self.showPaymentError("Unable to save billing address.");
                    }
                );
            },
            showPaymentError: function (message) {

                var span = document.getElementById(this.getCode() + '-payment-errors');

                span.innerHTML = message;
                span.style.display = "block";

                fullScreenLoader.stopLoader();
            },
            resetPaymentErrors: function () {
                var span = document.getElementById(this.getCode() + '-payment-errors');
                span.style.display = "none";

            }
        });
    }
);
