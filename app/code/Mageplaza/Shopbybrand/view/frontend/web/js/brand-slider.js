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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'mageplaza/core/owl.carousel'
], function ($) {
    return function (config, element) {
        $(element).owlCarousel({
            center: true,
            loop: true,
            margin: 10,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplayHoverPause: true,
            lazyLoad: true,
            dots: false,
            responsiveClass: true,
            responsiveBaseElement: '#' + $(element).attr('id'),
            responsive: {
                0: {items: 1},
                360: {items: 2},
                540: {items: 3},
                720: {items: 4},
                900: {items: 5}
            }
        });
    };
});