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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Ui/js/grid/export'
], function ($, Export) {
    'use strict';

    return Export.extend({
        defaults: {
            imports: {
                params: '${ $.provider }:params'
            }
        },

        /**
         * Retrieve params
         *
         * @returns {Object}
         */
        getParams: function () {
            return this.params;
        }
    });
});
