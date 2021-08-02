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
    'uiRegistry',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function ($, _, registry, customerData, quote, totalsDefaultProvider) {
    'use strict';
    
    var mixins = {
        setShippingInformation: function () {
            this._super();
    
            customerData.reload(['cart'], false).done(function () {
                var items = customerData.get('cart')()['items'],
                    refresh = false;
                
                _.each(items, function(item) {
                    var itemId = item.item_id,
                        imageData = registry.get('checkout.sidebar.summary.cart_items.details.thumbnail').imageData;
                    
                    if (item.mpfreegifts_ruleId) {
                        imageData[itemId] = item.product_image;
                        refresh = true;
                    }
                });
                
                if (refresh) {
                    totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                }
            });
        },
    };
    
    return function (Shipping) {
        return Shipping.extend(mixins);
    };
});
