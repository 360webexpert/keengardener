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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'rjsResolver',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Mageplaza_FreeGifts/js/helper/free_gifts',
    'mageplaza/core/owl.carousel',
], function($, ko, _, Component, resolver, $t, quote, helper) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mageplaza_FreeGifts/cart/free_gift'
        },
        rules: ko.observableArray(),
        cartBtn: '',
        
        initialize: function () {
            this._super();
            this.rules = ko.observableArray(this.rule_list);
            resolver(this.afterResolveDocument.bind(this));
        },
        
        afterResolveDocument: function () {
            var self = this,
                itemCartLeft = $('#mpfreegifts-available-item-cart');
            
            this.cartBtn = $('#mpfreegifts-cart-btn');

            if (this.item_id === 'cart') {
                quote.totals.subscribe(function (totals) {
                    var cartRules = totals.extension_attributes.mp_free_gifts;
                    
                    if (cartRules !== undefined) {
                        self.rules([]);
                        self.rule_list = [];
                        _.each(cartRules, function(cartRule) {
                            self.rules.push(JSON.parse(cartRule));
                            self.rule_list.push(JSON.parse(cartRule));
                        });
                    }
                    
                    _.isEmpty(self.rule_list) ? self.cartBtn.hide() : self.cartBtn.show();
                    itemCartLeft.text(self.calculateItemLeft());
                    $('.mpfreegifts-rule-slider').owlCarousel(helper.sliderConfig);
                });
            }
            $('.mpfreegifts-rule-slider').owlCarousel(helper.sliderConfig);
        },

        autoPopup: function () {
            if (this.auto_popup === '1') {
                if (this.has_cart) {
                    if (this.item_id === 'cart' && !this.rule_list[0].auto_add) {
                        this.showModal();
                    }
                } else if (this.rules().length > 0
                    && !$('.mpfreegifts_modal').hasClass('_show')
                    && !this.rule_list[0].auto_add
                ) {
                    this.showModal();
                }
            }
        },

        showModal: function() {
            helper.toggleGiftModal('#mpfreegifts-modal-item-' + this.item_id, $t('Select Free Gifts'));
        },
    
        initAddOptionBtn: function(giftId) {
            helper.createOptionBtn(this, giftId, true);
        },
        
        calculateItemLeft: function() {
            return helper.getItemLeft(this.rule_list);
        },
        
        giftAction: function(self, ruleId, url, itemId, gift) {
            var element = '#mpfreegifts-modal-item-' + itemId,
                optionModal = helper.option.modal + gift.id;
    
            if ($(optionModal).length === 1 && $(optionModal).data().mageModal) {
                helper.toggleGiftModal(optionModal, helper.option.title);
            } else {
                helper.giftAction(self, url, optionModal, {rule_id: ruleId, gift_id: gift.id,}, element);
            }
        },
    
        addGiftOption: function(giftId, itemId) {
            var self = this,
                element = '#mpfreegifts-modal-item-' + itemId,
                formData = new FormData($(helper.option.form + giftId)[0]);
        
            $.ajax({
                url: self.add_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                showLoader: true,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.error) {
                        helper.alertError($t('Error'), response.message);
                    }
                    if (response.success) {
                        $(element).modal('closeModal');
                        helper.closeAndReload(helper.option.modal + giftId);
                    }
                },
                error: function (error) {
                    helper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        }
    });
});