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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    "use strict";

    $.widget('mageplaza.redirect', {
        options: {
            url: ''
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initObserve();
            this.initPopup();
        },

        /**
         * Init observe
         */
        initObserve: function () {
            this.initSaveRedirect();
            this.initCancelRedirect();
        },

        /**
         * Init popup
         * Popup will automatic open
         */
        initPopup: function () {
            var options = {
                responsive: true,
                innerScroll: true,
                modalClass: 'mageplaza_seo_redirects',
                title: $t('SEO Redirects')
            };
            var popup = modal(options, $('#mageplaza-redirect'));
            $('#mageplaza-redirect').modal('openModal');
        },

        /**
         * Save redirect
         */
        initSaveRedirect: function () {
            var self = this;
            $(".seo-redirect").click(function () {
                var redirectElement = "#base_" + $(this).attr('id'),
                    targetPath = $(redirectElement + " .target_path").val();
                if (targetPath == undefined || targetPath.length == 0) {
                    $('<p class="redirect-required-message">' + $t('This is a required field') + '.</p>').insertAfter(redirectElement + " .target_path");
                } else {
                    $(redirectElement + " .target_path").next().hide();
                    var params = {
                        form_key: window.FORM_KEY,
                        store_id: $(redirectElement + " .store_id").val(),
                        target_path: $(redirectElement + " .target_path").val(),
                        request_path: $(redirectElement + " .request_path").val(),
                        redirect_type: $(redirectElement + " .redirect_type").val(),
                        type_process: $("#mageplaza-redirect #type_process").val(),
                        description: $(redirectElement + " .description").val(),
                    };
                    self.sendAjax(params, redirectElement);
                }
            });
        },

        /**
         * Cancel redirect
         */
        initCancelRedirect: function () {
            var self = this;
            $(".cancel-redirect").click(function () {
                var redirectElement = "#base_" + $(this).attr('id');
                var params = {
                    form_key: window.FORM_KEY,
                    request_path: $(redirectElement + " .request_path").val(),
                    type_process: $("#mageplaza-redirect #type_process").val(),
                    cancel: 1
                };
                self.sendAjax(params, redirectElement);
            });
        },

        /**
         * Send ajax
         * @param params
         * @param redirectElement
         */
        sendAjax: function (params, redirectElement) {
            $.ajax({
                method: 'POST',
                url: this.options.url,
                data: params,
                showLoader: true
            }).done(function (response) {
                if (response.success) {
                    $('<div class="message message-success"><p>' + response.message + '</p></div>').insertBefore(redirectElement);
                    $(redirectElement).remove();
                } else {
                    $('<div class="message message-error"><p>' + response.message + '</p></div>').insertBefore(redirectElement);
                }
            });
        }
    });

    return $.mageplaza.redirect;
});
