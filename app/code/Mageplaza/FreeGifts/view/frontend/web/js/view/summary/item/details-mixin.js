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

define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
    ],
    function ($, quote) {
        'use strict';

        var mixin = {

            checkGiftsItem: function (item) {
                var quoteItem = this.getQuoteItem(item.item_id, quote.getItems());

                if (quoteItem.mpfreegifts_rule_id) {
                    var inputElm = $('.item_qty[id="' + quoteItem.item_id + '"]'),
                        button = inputElm.parent().parent().find('.button-action');

                    inputElm.attr('readonly', 'readonly');
                    inputElm.css('pointer-events', 'none');
                    button.css('display', 'none');
                }
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
