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
    'mage/translate',
    'Mageplaza_FreeGifts/js/helper/free_gifts'
], function ($, _, $t, helper) {
    'use strict';
    var giftListing = window.mpfreegifts_gift_gridJsObject,
        productModal = '#mpfreegifts-product-grid-wrapper',
        editModal = '#mpfreegifts-gift-item-edit-wrapper',
        gridMassAction = '#mpfreegifts_gift_grid_massaction-select';
    
    function reloadGiftListing() {
        giftListing.reload();
        giftListing.massaction.unselectAll();
        $(productModal).trigger('closeModal');
        $(editModal).trigger('closeModal');
    }
    window.reloadGiftListing = reloadGiftListing;
    
    $.widget('mpfreegifts.giftListing', {
        addGiftBtn: '#mpfreegifts-add-gift',
        noticeDiv: 'div.field-notice',
        allowNotice: '#rule_allow_notice',
        gridAction: '.mpfreegifts-grid-item-edit-action',
        gridUpdateBtn: '#mpfreegifts-gift-item-edit-btn',
        gridEditForm: '#mpfreegifts-gift-item-edit-form',
        grid: '#mpfreegifts_gift_grid',
        
        _create: function () {
            var self = this,
                gridObject = window.mpfreegifts_gift_gridJsObject,
                useConfig = helper.ruleUseConfig;
            
            _.each(this.options.systemConfig, function(value, config) {
                $(config).val(value);
            });
            _.each(useConfig, function(field, config) {
                if (parseInt($(config).val(), 10)) {
                    $(config).attr('checked', true);
                    $(field).attr('disabled', 'disabled');
                }
                
                $(config).on('change', function() {
                    parseInt(this.value, 10) ? $(field).attr('disabled', 'disabled') : $(field).removeAttr('disabled');
                });
            });
            
            this.toggleElement($(this.allowNotice).val(), $(this.noticeDiv));
            $(this.allowNotice).on('change', function() {
                self.toggleElement($(self.allowNotice).val(), $(self.noticeDiv));
            });
            
            $(helper.gridEditItem.discount_type).on('change', function() {
                helper.toggleDiscountType(this.value);
            });
            $(this.addGiftBtn).on('click', function () {
                self.renderProductGrid(gridObject.massaction.gridIds);
            });
            
            $(this.gridUpdateBtn).on('click', function() {
                if ($(self.gridEditForm).valid()) {
                    self.updateGridItem($(self.gridEditForm).serialize());
                }
            });
            $(this.grid).on('contentUpdated', function() {
                self.initEditAction();
            });
            
            this.initEditAction();
            $(gridMassAction).removeClass('required-entry');
        },
        
        initEditAction: function () {
            var self = this;
            
            $(this.gridAction).each(function(index, item) {
                $(item).on('click', function () {
                    self.openEditModal(item);
                });
            });
            $(gridMassAction).removeClass('required-entry');
        },
        
        renderProductGrid: function (gridIds) {
            var self = this;
            
            $.ajax({
                url: self.options.productGridUrl,
                type: 'POST',
                data: {
                    giftIds : gridIds,
                    ruleId : self.options.ruleId,
                },
                showLoader: true,
                success: function (response) {
                    $(productModal).html(response);
                    $(productModal).trigger('contentUpdated');
                    self.openProductModal();
                },
                error: function (error) {
                    helper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                },
            });
        },
        
        updateGridItem: function(formData) {
            var self = this;
            
            $.ajax({
                url: self.options.updateItemUrl,
                type: 'POST',
                data: formData + '&rule_id=' + self.options.ruleId,
                showLoader: true,
                success: function () {
                    window.reloadGiftListing();
                },
                error: function (error) {
                    helper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                },
            });
        },
        
        openEditModal: function (item) {
            _.each(helper.gridEditItem, function (element, key) {
                $(element).val($(item).data(key));
            });
            helper.toggleDiscountType($(item).data('discount_type'));
            helper.toggleModal(editModal, $t('Update Gift Item'));
        },
        
        openProductModal: function () {
            helper.toggleModal(productModal, $t('Select Gift List Product'));
        },
        
        toggleElement: function (value, element) {
            parseInt(value, 10) ? element.show() : element.hide();
        },
    });
    
    return $.mpfreegifts.giftListing;
});
