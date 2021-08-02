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
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache',
], function ($, defaultTotal, cartCache) {
    'use strict';
    
    $.widget('mpfreegifts.notification', {
        options: {
            quoteId: '',
            add_url: '',
        },
        
        _create: function () {
            var self = this,
                notification = $('#mpfreegifts-notification'),
                notifyButton = $('#mpfreegifts-notify-hide');
            
            if (sessionStorage.getItem('mpfreegifts_quoteId') !== this.options.quoteId) {
                notification.show();
            }
            
            cartCache.set('totals',null);
            defaultTotal.estimateTotals();
            
            notifyButton.on('click', function(event) {
                event.preventDefault();
                sessionStorage.setItem('mpfreegifts_quoteId', self.options.quoteId);
                notification.hide();
            });
        },
    });
    
    return $.mpfreegifts.notification;
});
