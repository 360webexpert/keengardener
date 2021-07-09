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
    'Magento_Ui/js/grid/provider',
    'chartBundle'
], function ($, Element) {
    'use strict';

    return Element.extend({
        reload: function (options) {
            $('.chart-container-all').hide();
            if (typeof this.params.mpFilter === "undefined") {
                this.params.mpFilter = {};
            }
            if (typeof this.params.mpFilter.startDate === "undefined") {
                this.params.mpFilter.startDate = $('#daterange').data().startDate.format('Y-MM-DD');
            }
            if (typeof this.params.mpFilter.endDate === "undefined") {
                this.params.mpFilter.endDate = $('#daterange').data().endDate.format('Y-MM-DD');
            }
            if (typeof this.params.mpFilter.period === "undefined") {
                this.params.mpFilter.period = $('.period select').val();
            }
            if (typeof this.params.mpFilter.store === "undefined") {
                this.params.mpFilter.store = $('#store_switcher').val();
            }
            if (typeof this.params.mpFilter.customer_group_id === "undefined") {
                this.params.mpFilter.customer_group_id = $('.customer-group select').val();
            }
            this._super();
        }
    });
});