/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, alert, modal, $t) {
    'use strict';
    
    return {
        sliderConfig: {
            loop: false,
            margin: 10,
            nav: true,
            dots: true,
            lazyLoad: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            responsive: {
                1200: {items: 4},
                1000: {items: 3},
                800: {items: 2},
                600: {items: 1},
                100: {items: 1}
            }
        },
        
        option: {
            modal: '#mpfreegifts-option-modal-',
            form: '#mpfreegifts-option-form-',
            add: '#mpfreegifts-option-add-',
            wrapper: '#mpfreegifts-option-wrapper',
            title: $t('Select Gift Options')
        },
        
        alertError: function (title, error) {
            alert({
                title: title,
                content: error
            });
        },
        
        getGiftAttributes: function (ruleId, giftName, options) {
            return {rule_id: ruleId, options: options, name: giftName};
        },
        
        prepareInput: function (gifts) {
            var element = '';
            
            _.each(gifts, function (attribute, giftId) {
                var ruleId = attribute.rule_id;
                
                element += '<input type="hidden" name="mpfreegifts[' + ruleId + '][' + giftId + '][qty]" value="1">';
                if (_.has(attribute, 'options') && attribute.options !== "") {
                    element += '<input type="hidden" name="mpfreegifts[' + ruleId + '][' + giftId + ']';
                    element += '[options]" value="' + attribute.options + '">';
                }
            });
            
            return element;
        },
        
        updateSelectedGifts: function (selectedGifts) {
            var selectedLi        = '',
                selectedContainer = $('#mpfreegifts-selected-container'),
                selectedUl        = $('#mpfreegifts-selected-ul');
            
            if (_.isEmpty(selectedGifts)) {
                selectedContainer.hide();
            } else {
                _.each(selectedGifts, function (selectedGift) {
                    selectedLi += '<li>' + selectedGift.name + '</li>';
                });
                
                selectedUl.html(selectedLi);
                selectedContainer.show();
            }
        },
        
        toggleGiftModal: function (element, title) {
            var options = {
                'type': 'popup',
                'title': title,
                'responsive': true,
                'innerScroll': true,
                'modalClass': 'mpfreegifts_modal',
                'buttons': []
            };
            
            modal(options, $(element));
            $(element).modal('openModal');
        },
        
        closeAndReload: function (element) {
            $(element).modal('closeModal');
            location.reload();
        },
        
        getItemLeft: function (rules) {
            var itemLeft = _.reduce(rules, function (memo, rule) {
                return memo + rule.max_gift - rule.total_added;
            }, 0);
            
            return Math.max(0, itemLeft);
        },
        
        createOptionBtn: function (obj, giftId, cart) {
            var self = this;
            
            $(this.option.add + giftId).on('click', function () {
                if ($(self.option.form + giftId).valid()) {
                    if (cart) {
                        obj.addGiftOption(giftId, obj.item_id);
                    } else {
                        obj.selectOption(obj, $(self.option.form + giftId), giftId);
                    }
                }
            });
        },
        
        giftAction: function (obj, url, optionModal, data, element) {
            var self = this;
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.error) {
                        self.alertError($t('Error'), response.message);
                    }
                    if (response.option) {
                        $(self.option.wrapper).append(response.html);
                        $(self.option.wrapper).trigger('contentUpdated');
                        obj.initAddOptionBtn(data.gift_id);
                        self.toggleGiftModal(optionModal, self.option.title);
                        $('.mpfreegifts_modal').css('z-index', '1044');
                    }
                    if (element !== '' && response.success) {
                        self.closeAndReload(element);
                    }
                },
                error: function (error) {
                    self.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        }
    };
});
