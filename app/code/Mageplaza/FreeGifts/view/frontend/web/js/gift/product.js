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
    'underscore',
    'uiComponent',
    'rjsResolver',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Mageplaza_FreeGifts/js/helper/free_gifts',
    'mageplaza/core/owl.carousel',
], function($, _, Component, resolver, $t, customerData, helper) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mageplaza_FreeGifts/product/free_gift'
        },
        selectedGifts: {},
        queueGifts: {},
        inputGifts: {},
        
        initialize: function () {
            this._super();
            resolver(this.afterResolveDocument.bind(this));
        },
        
        afterResolveDocument: function () {
            var self = this,
                cart = customerData.get('cart');
            
            $('.mpfreegifts-rule-slider').owlCarousel(helper.sliderConfig);
            cart.subscribe(function () {
                _.each(self.queueGifts, function(giftForm, giftId) {
                    var id = parseInt(giftId, 10),
                        ruleId = self.selectedGifts[id].rule_id,
                        giftItem = _.find(cart().items, function(item){
                            return item.product_id === giftId
                                && parseInt(item.mpfreegifts_ruleId, 10) === ruleId;
                        });
                    
                    if (giftItem === undefined) {
                        $.ajax({
                            url: self.add_url,
                            type: 'POST',
                            data: giftForm,
                            dataType: 'json',
                            showLoader: true,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                if (response.error) {
                                    helper.alertError($t('Error'), response.message);
                                }
                                if (response.success) {
                                    customerData.reload(['cart'], false);
                                }
                            },
                            error: function (error) {
                                helper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                            }
                        });
                    }
                });
            });
        },
    
        initAddOptionBtn: function(giftId) {
            helper.createOptionBtn(this, giftId, false);
        },
        
        showModal: function() {
            helper.toggleGiftModal('#mpfreegifts-modal-item-' + this.item_id, $t('Select Free Gifts'));
            $('.mpfreegifts_modal').css('z-index', '1044');
        },
        
        closeModal: function() {
            $('#mpfreegifts-modal-item-' + this.item_id).modal('closeModal');
        },
        
        calculateItemLeft: function () {
            return helper.getItemLeft(this.rules);
        },
        
        toggleGiftItem: function (giftItem) {
            $(giftItem).toggleClass('mpfreegifts-selected');
            $(giftItem + ' .mpfreegifts-block-icon').toggleClass('mpfreegifts-selected-icon');
        },
        
        toggleOptionModal: function (optionUrl, ruleId, giftId) {
            var modal = helper.option.modal + giftId;
    
            if ($(modal).length === 1 && $(modal).data().mageModal) {
                helper.toggleGiftModal(modal, helper.option.title);
            } else {
                helper.giftAction(this, optionUrl, modal, {rule_id: ruleId, gift_id: giftId}, '');
            }
        },
        
        selectOption: function(self, form, giftId) {
            var id = parseInt(giftId, 10),
                ruleId = parseInt(form.find('input[name="rule_id"]').val(), 10),
                giftName = form.find('input[name="gift_name"]').val(),
                giftItem = '#gift-' + ruleId +'-'+ giftId,
                hasFile = form.find('input[type="file"]').length >= 1,
                currentRule = _.find(self.rules, function(rule){return parseInt(rule.rule_id, 10) === ruleId;});
            
            
            hasFile ? self.queueGifts[id] = new FormData(form[0])
                : self.inputGifts[id] = helper.getGiftAttributes(ruleId, giftName, form.serialize());
            self.selectedGifts[id] = {name: giftName, rule_id: ruleId};
            currentRule.max_gift--;
            self.toggleGiftItem(giftItem);
            $('#mpfreegifts-available-item').text(self.calculateItemLeft());
            self.finalizeInput(self.selectedGifts, self.inputGifts);
            $(helper.option.modal + giftId).modal('closeModal');
        },
        
        finalizeInput: function(selectedGifts, inputGifts) {
            var inpContainer = $('#mpfreegifts-inputs');
            
            inpContainer.html(helper.prepareInput(inputGifts));
            helper.updateSelectedGifts(selectedGifts);
        },
        
        selectGift: function(rule, self, gift) {
            var giftItem = '#gift-' + rule.rule_id + '-' + gift.id,
                hasGift = _.has(self.selectedGifts, gift.id),
                giftFlag = true,
                ruleId = parseInt(rule.rule_id, 10),
                optionFlag = false;
            
            if (hasGift && self.selectedGifts[gift.id].rule_id !== ruleId) {
                
                helper.alertError($t('Error'), $t('This gift is already selected.'));
            }
            if (hasGift && self.selectedGifts[gift.id].rule_id === ruleId) {
                delete self.selectedGifts[gift.id];
                if (_.has(self.inputGifts, gift.id)) {
                    delete self.inputGifts[gift.id];
                }
                if (_.has(self.queueGifts, gift.id)) {
                    delete self.queueGifts[gift.id];
                }
                rule.max_gift++;
                self.toggleGiftItem(giftItem);
                giftFlag = false;
            }
            if (rule.max_gift <= 0 || rule.max_gift - rule.total_added <= 0) {
                helper.alertError($t('Error'), $t('Maximum number of gifts added.'));
            }
            if (rule.max_gift > 0 && rule.max_gift - rule.total_added > 0 && !hasGift && giftFlag) {
                if (gift.configurable || gift.required_option) {
                    self.toggleOptionModal(self.option_url, ruleId, gift.id);
                    optionFlag = true;
                }else {
                    self.inputGifts[gift.id] = helper.getGiftAttributes(ruleId, gift.name, '');
                    self.selectedGifts[gift.id] = {name: gift.name, rule_id: ruleId};
                    rule.max_gift--;
                    self.toggleGiftItem(giftItem);
                }
            }
            if (!optionFlag) {
                $('#mpfreegifts-available-item').text(self.calculateItemLeft());
            }
            self.finalizeInput(self.selectedGifts, self.inputGifts);
        },
    });
});