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
            var serverConfig = window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver;
            if (serverConfig) {
                if (!serverConfig.licensed) {
                    $("#payment .step-title").after('<div class="message error" style="margin-top: 5px;border: 1px solid red;">WARNING: Your Opayo Suite license is invalid.</div>');
                }
            }
        });

        return Component.extend({
            defaults: {
                template: 'Ebizmarts_SagePaySuite/payment/server-form',
                use_token: false,
                save_token: false,
                used_token_slots: 0
            },
            getCode: function () {
                return 'sagepaysuiteserver';
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

                if (this.isTokenServiceEnabled()) {
                    if (document.getElementById('remembertoken') &&
                        document.getElementById('remembertoken').checked == true) {
                        use_token : false;
                        save_token : true;
                    } else {
                        self.use_token = true;
                        self.save_token =  false;
                    }
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

                                var serviceUrl = null;
                                if (customer.isLoggedIn()) {
                                    serviceUrl = urlBuilder.createUrl('/sagepay/server', {});
                                } else {
                                    serviceUrl = urlBuilder.createUrl('/sagepay-guest/server', {});
                                }
                                var save_token = self.save_token && !self.use_token;
                                var token = "%token%";

                                if (self.use_token) {
                                    var tokens = window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver.tokens;
                                    for (var i = 0; i < tokens.length; i++) {
                                        if ($('#' + self.getCode() + '-token-' + tokens[i].id).prop("checked") == true) {
                                            token = tokens[i].token;
                                            break;
                                        }
                                    }
                                    if (token == null) {
                                        self.showPaymentError("Please select the card to be used form the list");
                                        return;
                                    }
                                }

                                //send server post request
                                return storage.post(
                                    serviceUrl,
                                    JSON.stringify({
                                        "cartId": quote.getQuoteId(),
                                        "save_token": save_token,
                                        "token": token
                                    })
                                ).done(
                                    function (response) {

                                        if (response.success) {

                                            customerData.invalidate(['cart']);
                                            customerData.invalidate(['checkout-data']);

                                            var sagePayResponse = $.parseJSON(response.response[1]);
                                            self.openSERVERModal(sagePayResponse.NextURL);

                                            fullScreenLoader.stopLoader();
                                        } else {
                                            self.showPaymentError(response.error_message);
                                        }
                                    }
                                ).fail(
                                    function (response) {
                                        self.showPaymentError("Unable to submit to Opayo. Please try another payment option.");
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
            checkMaxTokensPerCustomer: function () {
                if (this.used_token_slots > 0 && this.used_token_slots >= window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver.max_tokens) {
                    $('#' + this.getCode() + '-tokens .token-list .message-max-tokens').show();
                    $('#' + this.getCode() + '-tokens .add-new-card-link').hide();
                } else {
                    $('#' + this.getCode() + '-tokens .token-list .message-max-tokens').hide();
                    $('#' + this.getCode() + '-tokens .add-new-card-link').show();
                }
            },
            /**
             * Create SERVER modal
             */
            openSERVERModal: function (nextURL) {

                var winProfile  = parseInt(window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver.low_profile) > 0 ? 'low' : 'normal';

                if (this.sagePayIsMobile()) {
                    location.href = nextURL;
                } else {
                    this.modal = $("<div class='sagepaysuiteserver-scroll-wrapper'><iframe class='sagepaysuiteserver_embed_" + winProfile + "' src='" + nextURL + "'></iframe></div>").modal({
                        modalClass: 'sagepaysuiteserver-modal',
                        title: "Opayo Secure Gateway",
                        type: (winProfile === 'normal') ? 'slide' : 'popup',
                        responsive: true,
                        clickableOverlay: false,
                        closeOnEscape: false,
                        buttons: []
                    });
                    this.modal.modal('openModal');
                }
            },
            sagePayIsMobile: function () {
                return (navigator.userAgent.match(/BlackBerry/i) ||
                navigator.userAgent.match(/webOS/i) ||
                navigator.userAgent.match(/Android/i) ||
                navigator.userAgent.match(/iPhone/i) ||
                navigator.userAgent.match(/iPod/i) ||
                navigator.userAgent.match(/iPad/i));
            },
            showPaymentError: function (message) {

                var span = document.getElementById(this.getCode() + '-payment-errors');

                span.innerHTML = message;
                span.style.display = "block";

                $('#sagepaysuiteserver-actions-toolbar').css('display', 'block');
                $('#payment_form_sagepaysuiteserver .payment-method-note').css('display', 'block');

                fullScreenLoader.stopLoader();
            },
            resetPaymentErrors: function () {
                $('#sagepaysuiteserver-actions-toolbar').css('display', 'block');
                $('#payment_form_sagepaysuiteserver .payment-method-note').css('display', 'block');

                var span = document.getElementById(this.getCode() + '-payment-errors');
                span.style.display = "none";
            },
            addNewCard: function () {
                this.use_token = false;
                document.getElementById('remembertoken').checked = 1;
                $('#' + this.getCode() + '-tokens .token-list').hide();
                $('#' + this.getCode() + '-tokens .add-new-card-link').hide();
                $('#' + this.getCode() + '-tokens .using-new-card-message').show();
                $('#' + this.getCode() + '-tokens .use-saved-card-link').show();
            },
            useSavedTokens: function () {
                this.use_token = true;
                document.getElementById('remembertoken').checked = 0;
                $('#sagepay-server-remembertoken-container').hide();

                $('#' + this.getCode() + '-tokens .token-list').show();
                $('#' + this.getCode() + '-tokens .use-saved-card-link').hide();
                $('#' + this.getCode() + '-tokens .using-new-card-message').hide();
            },
            deleteToken: function (id) {
                var self = this;
                if (confirm("Are you sure you wish to delete this saved credit card token?")) {
                    var serviceUrl = url.build('sagepaysuite/token/delete');

                    //send token delete post
                    return storage.get(serviceUrl + "/token_id/" + id + "/checkout/1").done(
                        function (response) {

                            if (response.success && response.success == true) {
                                //check warning message
                                self.used_token_slots = self.used_token_slots - 1;
                                self.checkMaxTokensPerCustomer();

                                //hide token row
                                $('#' + self.getCode() + '-token-' + id).prop("checked", false);
                                $('#' + self.getCode() + '-tokenrow-' + id).hide()

                                //delete from token list
                                var tokens = this.getCustomerTokens();
                                for (var i = 0; i < tokens.length; i++) {
                                    if (id == tokens[i].id) {
                                        tokens.splice(i, 1);
                                    }
                                }
                                if (tokens.length == 0) {
                                    $('#' + self.getCode() + '-tokens').hide();
                                    self.use_token = false;
                                }
                            } else {
                                self.showPaymentError(response.error_message);
                            }
                        }
                    ).fail(
                        function (response) {
                            self.showPaymentError("Unable to delete credit card token.");
                        }
                    );
                }
            },
            isTokenServiceEnabled: function () {
              return (window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver &&
                  (window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver.token_enabled == true));
            },
            getTokenizationState: function () {
                return this.isTokenServiceEnabled();
            },
            customerHasTokens: function () {
                this.save_token = false;
                this.use_token = false;

                if (this.isTokenServiceEnabled()) {
                    this.save_token = true;
                    if (this.getCustomerTokens() && this.getCustomerTokens().length > 0) {
                        this.used_token_slots = this.getCustomerTokens().length;
                        this.checkMaxTokensPerCustomer();
                        this.use_token = true;
                    } else {
                        this.use_token = false;
                    }
                }

                return this.use_token;
            },
            getCustomerTokens: function () {
                var sagePayTokens = window.checkoutConfig.payment.ebizmarts_sagepaysuiteserver.tokens;
                if (sagePayTokens.length > 0) {
                    this.useSavedTokens();
                }
                return sagePayTokens;
            },
            getIcons: function (type) {
                switch (type) {
                    case 'VISA':
                    case 'DELTA':
                    case 'UKE':
                        return window.checkoutConfig.payment.ccform.icons["VI"].url;
                        break;
                    case 'MC':
                    case 'MCDEBIT':
                        return window.checkoutConfig.payment.ccform.icons["MC"].url;
                        break;
                    case 'MAESTRO':
                        return window.checkoutConfig.payment.ccform.icons["MD"].url;
                        break;
                    case 'AMEX':
                        return window.checkoutConfig.payment.ccform.icons["AE"].url;
                        break;
                    case 'DC':
                        return window.checkoutConfig.payment.ccform.icons["DC"].url;
                        break;
                    case 'JCB':
                        return window.checkoutConfig.payment.ccform.icons["JCB"].url;
                        break;
                    default:
                        return "";
                        break;
                }
            }
        });
    }
);
