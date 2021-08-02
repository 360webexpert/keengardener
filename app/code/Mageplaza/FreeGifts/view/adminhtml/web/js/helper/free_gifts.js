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
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';
    
    return {
        ruleUseConfig: {
            '#rule_use_config_allow_notice' : '#rule_allow_notice',
            '#rule_use_config_notice' : '#rule_notice',
        },
        
        gridEditItem: {
            gift_id : '#mpfreegifts-edit-gift_id',
            discount_type : '#mpfreegifts-edit-discount_type',
            gift_price : '#mpfreegifts-edit-gift_price',
            product_price : '#mpfreegifts-edit-product_price',
            free_shipping : '#mpfreegifts-edit-free_shipping',
        },
        
        alertError: function (title, error) {
            alert({
                title: title,
                content: error,
            });
        },
        
        toggleDiscountType: function(discountType) {
            var range = 'input-text admin__control-text validate-number validate-number-range number-range-0-',
                priceInp = $(this.gridEditItem.gift_price),
                giftPrice = $(this.gridEditItem.product_price).val();
            
            if (discountType === 'free') {
                priceInp.val('0');
                priceInp.attr('disabled', 'disabled');
            } else if (discountType === 'percent') {
                priceInp.removeAttr('disabled');
                priceInp.attr('class', range + '100');
            }else {
                priceInp.removeAttr('disabled');
                priceInp.attr('class', range + giftPrice);
            }
        },
        
        toggleModal: function (element, title) {
            if ($(element).data('mageModal') === undefined) {
                $(element).modal({
                    type: 'slide',
                    title: title,
                    innerScroll: true,
                    buttons: []
                });
            }
            $(element).trigger('openModal');
        }
    };
});