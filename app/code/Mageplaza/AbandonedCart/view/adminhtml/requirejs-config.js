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

var config = {
    paths: {
        daterangepicker: 'Mageplaza_AbandonedCart/js/lib/daterangepicker.min',
        chartBundle: 'Mageplaza_AbandonedCart/js/lib/Chart.bundle.min',
        twix: 'Mageplaza_AbandonedCart/js/lib/twix.min',
        moment: 'Mageplaza_AbandonedCart/js/lib/moment.min'
    },
    map: {
        daterangepicker: {
            'moment': 'Mageplaza_AbandonedCart/js/lib/moment.min'
        },
        '*': {
            moment: 'Mageplaza_AbandonedCart/js/lib/moment.min'
        }
    }
};
