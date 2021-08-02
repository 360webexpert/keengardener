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
], function ($, _) {
    'use strict';
    
    var mixins = {
        /**
         * Disable Qty Edit In Mini Cart If Product is Gift
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            this._super(updatedCart);
            
            setTimeout(function() {
                _.each(updatedCart.items, function(item) {
                    if (item.mpfreegifts_ruleId) {
                        $('#cart-item-'+ item.item_id + '-qty').attr('readonly', 'readonly');
                    }
                });
            }, 500);
        
        },
    };
    
    return function (MiniCart) {
        return MiniCart.extend(mixins);
    };
});
