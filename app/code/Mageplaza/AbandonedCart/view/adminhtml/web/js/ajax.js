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
    "jquery",
    "underscore",
    "mage/translate",
    "Magento_Ui/js/modal/alert",
    "mage/calendar"
], function ($, _, $t, alert) {
    "use strict";
    return function (config, element) {
        $('#mp-ace-report-date_range').dateRange({
            buttonText: $t("Select Date"),
            dateFormat: 'MM/dd/y',
            from: {
                id: "mp_ace_report_date_from"
            },
            to: {
                id: "mp_ace_report_date_to"
            }
        });
        if (config.ajaxUrl) {
            sendAjax(config, element);
            $('#mp-ace-report-apply').click(function () {
                sendAjax(config, element);
            });
        }
        $('.mp_ace_report_day, .mp_ace_report_month').click(function () {
            $('.mp_ace_report_dimension div').removeClass('active');
            if (!$(this).hasClass('active')) {
                $(this).find('input[type=radio]').prop("checked", true);
                $(this).addClass('active');
            }
        });
    };

    function sendAjax(config, element) {
        var from = $('#mp_ace_report_date_from').val(),
            to = $('#mp_ace_report_date_to').val();
        if (!from || !to || new Date(from).getTime() > new Date(to).getTime()) {
            alert({
                title: $t('Error'),
                content: $t('Please enter from < to')
            });
        }
        $.ajax({
            url: config.ajaxUrl,
            data: {
                from: from,
                to: to,
                dimension: $('.report_toolbar input[name=mp_ace_report_dimension]:checked').val()
            },
            dataType: 'json',
            showLoader: true,
            success: function (result) {
                if (!result.status) {
                    alert({
                        title: $t('Error'),
                        content: $t('Please submit again')
                    });
                } else {
                    $(element).empty().append(result.content);
                    $(element).trigger('contentUpdated');
                }
            },
            error: function () {
                alert({
                    title: $t('Error'),
                    content: $t('Please submit again')
                });
            }
        });
    }
});
