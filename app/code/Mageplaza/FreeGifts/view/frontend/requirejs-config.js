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

var config = {
    'config': {
        'mixins': {
            'Magento_Checkout/js/view/minicart': {
                'Mageplaza_FreeGifts/js/cart/mini_cart': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Mageplaza_FreeGifts/js/cart/shipping': true
            },
            'Mageplaza_Osc/js/view/summary/item/details': {
                'Mageplaza_FreeGifts/js/view/summary/item/details-mixin': true
            },
        }
    },

    paths: {
        giftIcon: 'Mageplaza_FreeGifts/js/product/gift_icon'
    }
};