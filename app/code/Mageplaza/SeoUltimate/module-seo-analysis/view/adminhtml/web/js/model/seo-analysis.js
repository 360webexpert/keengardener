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
    'uiRegistry',
    'Mageplaza_SeoAnalysis/js/model/seo-insights-message'
], function (ko, $, Registry, SeoInsightsMessage) {
    'use strict';

    var dataSource = Registry.get('product_form.product_form_data_source'),
        product = dataSource.data.product,
        urlKey = ko.observable(product.url_key || ''),
        mpMainKeyword = ko.observable(product.mp_main_keyword || ''),
        metaTitle = ko.observable(product.meta_title || ''),
        metaDescription = ko.observable(product.meta_description || '');

    return {
        metaTitle: metaTitle,
        metaDescription: metaDescription,
        mpMainKeyword: mpMainKeyword,
        urlKey: urlKey,
        seoAnalysis: ko.observable(product.seo_analysis),
        product: ko.observable(product),
        productUrl: ko.computed(function () {
            return product.seo_analysis.base_url + urlKey() + '/';
        }),

        insightsMessages: ko.computed(function () {
            var mainKeyword = mpMainKeyword(),
                metaTitleVal = metaTitle(),
                metaDescriptionVal = metaDescription(),
                description = $('input[name="product[description]"]').val() || product.description || '',
                urlKeyVal = urlKey();

            if (mainKeyword) {
                SeoInsightsMessage.inMetaTitle(mainKeyword, metaTitleVal)
                    .inFirstParagraph(mainKeyword, description)
                    .inMetaDescription(mainKeyword, metaDescriptionVal)
                    .calculateDensity(mainKeyword, description)
                    .inUrlKey(mainKeyword.replace(/ /g, ""), urlKeyVal.replace(/-/g, ""))
                    .inImageAlt(mainKeyword, description, product.seo_analysis.images);
            }

            SeoInsightsMessage.evaluateMetaTitle(metaTitleVal)
                .evaluateMetaDescription(metaDescriptionVal)
                .evaluateDescriptionContent(description)
                .determineOutLink(description, product.seo_analysis.base_url);

            return SeoInsightsMessage.getResult();
        })
    };
});