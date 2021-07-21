/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'sagepaysuitepi',
                component: 'Ebizmarts_SagePaySuite/js/view/payment/method-renderer/pi-method'
            },
            {
                type: 'sagepaysuiteform',
                component: 'Ebizmarts_SagePaySuite/js/view/payment/method-renderer/form-method'
            },
            {
                type: 'sagepaysuiteserver',
                component: 'Ebizmarts_SagePaySuite/js/view/payment/method-renderer/server-method'
            },
            {
                type: 'sagepaysuitepaypal',
                component: 'Ebizmarts_SagePaySuite/js/view/payment/method-renderer/paypal-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
