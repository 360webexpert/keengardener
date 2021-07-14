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

require([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ], function ($, modal) {
    'use strict';
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            modalClass: 'brand-quick-view',
            title: 'Brand Informations',
            buttons: [{
                text: $.mage.__('Close'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        modal(options, $('#quick-view'));

        $(".open_model").on("click", function () {
            $('#quick-view').modal('openModal');
            $('.modals-overlay').addClass('z-index:');
        });
        $(document).click(function (event) {
            if (event.target.className.indexOf('_show') >= 0) {
                $('#quick-view').modal('closeModal');
            }
        });
    }
);