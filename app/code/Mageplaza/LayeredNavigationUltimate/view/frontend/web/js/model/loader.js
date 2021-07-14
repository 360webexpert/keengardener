/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(['jquery'], function ($) {
        'use strict';

        return {
            /**
             * Start full page loader action
             */
            startLoader: function () {
                if (!$('#ln_product_overlay').length) {
                    var lnOverlay = $('#ln_overlay').html(),
                        lnProductOverlay = $('<div></div>', {id: 'ln_product_overlay'}).css('display', 'none').css('text-align', 'center').html(lnOverlay);

                    $('#layer-product-list').append(lnProductOverlay);
                }

                $('#ln_product_overlay').show();
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                $('#ln_product_overlay').hide();
            }
        };
    }
);
