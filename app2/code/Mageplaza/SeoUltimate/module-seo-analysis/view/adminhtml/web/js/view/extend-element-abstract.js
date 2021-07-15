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
 * @package     Mageplaza_SeoAnalysis
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Mageplaza_SeoAnalysis/js/model/seo-analysis'
], function (ko, $, Component, SeoAnalysisModel) {
    'use strict';

    return Component.extend({
        seoAnalysis: SeoAnalysisModel.seoAnalysis(),
        metaTitle: SeoAnalysisModel.metaTitle,
        metaDescription: SeoAnalysisModel.metaDescription,
        productUrl: SeoAnalysisModel.productUrl,
        insightsMessages: SeoAnalysisModel.insightsMessages,
        flagClass: ko.observable(true),

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            var self = this;
            this.value.subscribe(function (newValue) {
                if (self.index === 'url_key') {
                    SeoAnalysisModel.urlKey(newValue);
                } else if (self.index === 'mp_main_keyword') {
                    SeoAnalysisModel.mpMainKeyword(newValue);
                }
            });

            return this;
        }
    });
});
