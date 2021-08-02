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
], function ($) {
    'use strict';
    
    $.widget('mpfreegifts.notice', {
        options: {
            itemId: '',
        },
        
        _create: function () {
            var self = this,
                itemQty = $('#cart-'+ this.options.itemId +'-qty');
    
            itemQty.attr('readonly', 'readonly');
            itemQty.css('pointer-events', 'none');
    
            $('a.action-edit').each(function() {
                if ($(this).attr('href').includes('/configure/id/' + self.options.itemId)) {
                    $(this).hide();
                }
            });
        },
    });
    
    return $.mpfreegifts.notice;
});
