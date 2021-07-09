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
    'Magento_Catalog/js/components/import-handler',
    'Mageplaza_SeoAnalysis/js/model/seo-analysis'
], function (ko, Component, SeoAnalysisModel) {
    'use strict';

    return Component.extend({
        previewProgressMetaTite: ko.observable(),
        previewProgressMetaDescription: ko.observable(),

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            var self = this;
            this.value.subscribe(function (newValue) {
                if (self.index === 'meta_title') {
                    SeoAnalysisModel.metaTitle(newValue);
                } else if (self.index === 'meta_description') {
                    SeoAnalysisModel.metaDescription(newValue);
                }
            });

            return this;
        },

        /**
         * Bind value
         * @param value
         * @returns {exports}
         */
        bindValue: function (value) {
            if (this.index === 'meta_title') {
                SeoAnalysisModel.metaTitle(value);
            } else if (this.index === 'meta_description') {
                SeoAnalysisModel.metaDescription(value);
            }

            return this;
        },

        /**
         * Veify meta title
         * @returns {number}
         */
        verifyTitle: function () {
            var metaTitle = SeoAnalysisModel.metaTitle().length * 10;
            this.previewProgressMetaTite(metaTitle > 300);

            return metaTitle;
        },

        /**
         * Verify meta Description
         * @returns {number}
         */
        verifyDescription: function () {
            var metaDescription = SeoAnalysisModel.metaDescription().length * 3;
            this.previewProgressMetaDescription(metaDescription > 300);

            return metaDescription;
        }
    });
});