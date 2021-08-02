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
], function ($, _) {
    'use strict';
    
    $.widget('mpfreegifts.multiShipping', {
        options: {
            itemIds: '',
        },
        
        _create: function () {
            _.each(this.options.itemIds, function(itemId) {
                var inpElement = $('#ship-'+ itemId +'-qty');
                
                inpElement.attr('readonly', 'readonly');
                inpElement.css('pointer-events', 'none');
            });
        },
    });
    
    return $.mpfreegifts.multiShipping;
});
