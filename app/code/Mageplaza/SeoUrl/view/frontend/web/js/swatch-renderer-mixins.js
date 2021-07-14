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
 * @package     Mageplaza_SeoUrl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            /**
             * @inheritDoc
             */
            _RenderControls: function () {
                this._super();

                if (this.options.jsonConfig.seoUrl) {
                    var optionCollection = this.options.jsonConfig.seoUrl.optionCollection;

                    if (optionCollection) {
                        var paramStr = '',
                            seoParams = window.location.href
                                .split('?')[0]
                                .replace(new RegExp(this.options.jsonConfig.seoUrl.urlSuffix + '$'), '')
                                .split('/')
                                .pop()
                                .split('-');

                        for (var param in seoParams) {
                            if (seoParams.hasOwnProperty(param)) {
                                for (var key in optionCollection) {
                                    if (optionCollection.hasOwnProperty(key) && (optionCollection[key]['url_key'] === seoParams[param])) {
                                        if (paramStr !== '') {
                                            paramStr += '&';
                                        }
                                        paramStr += optionCollection[key]['attribute_code'] + '=' + optionCollection[key]['option_id'];
                                    }
                                }
                            }
                        }

                        if (paramStr !== '') {
                            this._EmulateSelected($.parseQuery(paramStr));
                        }
                    }
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});