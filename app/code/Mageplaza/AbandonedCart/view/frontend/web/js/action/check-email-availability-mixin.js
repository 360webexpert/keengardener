/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Mageplaza_AbandonedCart/js/model/resource-url-manager'
], function ($, wrapper, quote, storage, resourceUrlManager) {
    'use strict';

    return function (checkEmailAvailabilityAction) {

        return wrapper.wrap(checkEmailAvailabilityAction, function (originalAction, deferred, email) {
            return storage.post(
                resourceUrlManager.getUrlForCheckIsEmailAvailable(quote),
                JSON.stringify({
                    customerEmail: email
                }),
                false
            ).done(
                function (isEmailAvailable) {
                    if (isEmailAvailable) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                }
            ).fail(
                function () {
                    deferred.reject();
                }
            );
        });
    };
});