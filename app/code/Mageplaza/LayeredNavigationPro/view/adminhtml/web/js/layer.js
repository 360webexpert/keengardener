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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery'
], function ($) {
    "use strict";

    return function (optionConfig) {
        var frontendInput = $('#frontend_input'),
            frontendClass = $('#frontend_class'),
            layerProductAttributes = {
                isFilterable: $('#is_filterable'),
                isFilterableInSearch: $('#is_filterable_in_search'),
                optionDisplay: $(optionConfig['optionDisplayEl']),
                optionDisplayValue: null,
                allowMultipleInput: $(optionConfig['allowMultipleInputEL']),
                searchEnableInput: $(optionConfig['searchEnableInputEl']),
                attrTabsLayer: $('#product_attribute_tabs_layer'),

                /**
                 * @returns {*|jQuery|HTMLElement}
                 */
                get tabsLayer() {
                    return this.attrTabsLayer.length ? this.attrTabsLayer.closest('li') : $('#layer_fieldset-wrapper');
                },
                selectFields: ['text', 'select', 'multiselect', 'price', 'swatch_text', 'swatch_visual'],
                decimalFields: ['validate-number', 'validate-digits'],
                displayOption: optionConfig['displayOption'],
                displayRule: optionConfig['displayRule'],

                bindAttributeInputType: function () {
                    var type = frontendInput.val();

                    if (!~$.inArray(type, this.selectFields) || ((type === 'text') && !~$.inArray(frontendClass.val(), this.decimalFields))) {
                        this.tabsLayer.hide();

                        return this;
                    }

                    this._enable();
                    this.tabsLayer.show();

                    this.allowMultipleInput.removeClass('no-display');
                    this.searchEnableInput.removeClass('no-display');
                    switch (type) {
                        case 'text':
                        case 'price':
                            this.allowMultipleInput.addClass('no-display');
                            this.searchEnableInput.addClass('no-display');
                            this.appendDisplayOptions('price');
                            break;
                        case 'select':
                        case 'multiselect':
                            this.appendDisplayOptions('select');
                            break;
                        case 'swatch_text':
                        case 'swatch_visual':
                            this.appendDisplayOptions('swatch');
                            break;
                    }

                    return this;
                },
                appendDisplayOptions: function (type) {
                    var self = this,
                        options = {};

                    if (this.displayRule.hasOwnProperty(type)) {
                        options = this.displayRule[type];
                    }

                    if (this.optionDisplayValue === null) {
                        this.optionDisplayValue = this.optionDisplay.val();
                    }

                    this.optionDisplay.empty();
                    $.each(options, function (key, value) {
                        if (self.displayOption.hasOwnProperty(value)) {
                            var optionTmp = $("<option></option>")
                                .attr("value", value)
                                .text(self.displayOption[value]);

                            if (self.optionDisplayValue && (value === self.optionDisplayValue)) {
                                optionTmp.attr("selected", true);
                            }

                            self.optionDisplay.append(optionTmp);
                        }
                    });

                    return this;
                },

                _enable: function () {
                    if (!this.isFilterable.attr('readonly')) {
                        this.isFilterable.removeAttr('disabled');
                    }

                    if (!this.isFilterableInSearch.attr('readonly')) {
                        this.isFilterableInSearch.removeAttr('disabled');
                    }
                }
            };

        $(function () {
            setTimeout(function () {
                frontendInput.bind('change', function () {
                    layerProductAttributes.bindAttributeInputType();
                });

                frontendClass.bind('change', function () {
                    layerProductAttributes.bindAttributeInputType();
                });

                layerProductAttributes.bindAttributeInputType();
            }, 500);
        });
    };
});

