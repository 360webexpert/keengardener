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
    'jquery',
    'underscore',
    'Mageplaza_LayeredNavigation/js/view/layer',
    'mpChosen'
], function ($, _, layer) {
    "use strict";

    $.widget('mageplaza.layer', layer, {
        options: {
            scroll: false,
            buttonSubmit: false,
            buttonSubmitElement: '#ln_button_submit',
            checkboxEl: 'input[type=checkbox],input[type=radio]',
            sliderRangeElementPrefix: '#ln_slider_range_',
            sliderFromElementPrefix: '#ln_slider_from_',
            sliderToElementPrefix: '#ln_slider_to_'
        },

        initObserve: function () {
            this._super();

            var self = this;

            this.initDropdownObserver();
            this.initSearchObserver();

            if (this.options.buttonSubmit.enable) {
                this.initApplyFilterButton();
            }

            var swatch = this.options.swatchOptionText;
            if (swatch.length) {
                swatch.forEach(function (value, index) {
                    self.element.find('[attribute=' + value + '] .swatch-option').each(function () {
                        var label = $(this).attr('option-label');
                        $(this).parent().addClass('swatch-option-label-layered')
                            .append("<span class='swatch-option-text'>" + label + "</span>");
                    });
                });
            }

            var optionElements = this.element.find('.filter-options a.rating-summary');
            optionElements.each(function (index) {
                $(this).bind('click', function (e) {
                    optionElements.removeClass('selected');
                    $(this).addClass('selected');
                });
            });
        },

        initDropdownObserver: function () {
            var self = this,
                select = this.element.find('.layer_filter_select');

            select.each(function (index) {
                var disableSearch = ($(this).attr('search') === 'false');
                $(this).chosen({
                    width: "100%",
                    disable_search: disableSearch,
                    enable_split_word_search: false,
                    display_selected_options: false,
                    allow_single_deselect: true
                });
                $(this).bind('change', function (e, params) {
                    var url = $(this).attr('url');
                    if (typeof params === 'object') {
                        url = params.hasOwnProperty('deselected') ? params.deselected : params.selected;
                    } else {
                        var valSelected = $(this).children('option:selected').val();
                        if (valSelected.trim() !== '') url = valSelected;
                    }
                    if (self.checkUrl(url)) {
                        self.ajaxSubmit(url);
                    }
                    e.stopPropagation();
                });
            });
        },

        initSearchObserver: function () {
            var self = this;

            this.element.find('.layer-search-box').on('keyup', function () {
                var attributeCode = $(this).attr('code'),
                    searchTerm = $(this).val().toLowerCase();

                self.element.find('.layer-search-list-' + attributeCode + ' li').each(function () {
                    if ($(this).filter('[data-search-term *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        },

        selectSwatchOption: function (el) {
            var multipleAttrs = this.options.multipleAttrs,
                swatchElms = el.parents('.swatch-attribute'),
                attCode = swatchElms.attr('attribute-code');

            if (multipleAttrs && multipleAttrs.indexOf(attCode) === -1) {
                swatchElms.find('.swatch-option.selected').each(function (index) {
                    $(this).removeClass('selected');
                });
            }

            this._super(el);
        },

        ajaxSubmit: function (submitUrl, btnClicked) {
            if (this.options.buttonSubmit.enable && (typeof btnClicked === 'undefined')) {
                if (this.options.buttonSubmit.seoUrlEnable) {
                    this.initButtonSeoParams(submitUrl);
                }
                this.initButtonParams(submitUrl);

                return;
            }

            if (this.options.scroll || this.checkDevice()) {
                $("body,html").animate({scrollTop: $('#layer-product-list').offset().top}, "slow");
            }

            return this._super(submitUrl);
        },

        initButtonSeoParams: function (submitUrl) {
            var paramsInit = this.getSeoUrlParams(submitUrl),
                singleAttrs = this.options.buttonSubmit.singleAttrs;

            var params = (this.baseSeoFilterParams.length > paramsInit.length) ?
                _.difference(this.baseSeoFilterParams, paramsInit) :
                _.difference(paramsInit, this.baseSeoFilterParams);
            if (!params.length) {
                return this;
            }

            var param = _.first(params);
            if (_.indexOf(this.seoFilterParams, param) !== -1) {
                this.seoFilterParams = _.difference(this.seoFilterParams, params);
            } else {
                for (var i in singleAttrs) {
                    if (singleAttrs[i].indexOf(param) !== -1) {
                        this.seoFilterParams = _.difference(this.seoFilterParams, singleAttrs[i]);
                    }
                }
                this.seoFilterParams.push(param);
            }

            return this;
        },

        initButtonParams: function (submitUrl) {
            var params = this.getUrlParams(submitUrl),
                multipleAttrs = this.options.multipleAttrs;

            /**
             * If customer remove selected attribute (default selected only 1 options), param in url will be undefined
             */
            for (var fkey in this.baseFilterParams) {
                if (this.baseFilterParams.hasOwnProperty(fkey) && !params.hasOwnProperty(fkey)) {
                    var baseParamFilter = _.first(this.baseFilterParams[fkey]),
                        baseCurrentFilter = (this.filterParams.hasOwnProperty(fkey)) ? this.filterParams[fkey] : [];

                    if (baseParamFilter) {
                        var baseParamPosition = baseCurrentFilter.indexOf(baseParamFilter);
                        if (baseParamPosition >= 0) {
                            baseCurrentFilter.splice(baseParamPosition, 1);
                        } else {
                            if (multipleAttrs && multipleAttrs.indexOf(fkey) >= 0) {
                                baseCurrentFilter.push(baseParamFilter);
                            } else {
                                baseCurrentFilter = [baseParamFilter];
                            }
                        }

                        if (baseCurrentFilter.length > 0) {
                            this.filterParams[fkey] = baseCurrentFilter;
                        } else {
                            delete this.filterParams[fkey];
                        }
                    }
                }
            }

            for (var key in params) {
                if (!params.hasOwnProperty(key)) {
                    continue;
                }
                var newFilter = params[key],
                    baseFilter = (this.baseFilterParams.hasOwnProperty(key)) ? this.baseFilterParams[key] : [],
                    currentFilter = (this.filterParams.hasOwnProperty(key)) ? this.filterParams[key] : [],
                    paramFilter = params[key];

                //Filter other attribute
                if (_.isEqual(newFilter, baseFilter)) {
                    continue;
                }

                //Remove base filter
                if (newFilter.length < baseFilter.length) {
                    for (var i = 0, len = baseFilter.length; i < len; i++) {
                        if (newFilter.indexOf(baseFilter[i]) < 0) {
                            paramFilter = baseFilter[i];
                        }
                    }
                }

                //Add/remove new filter
                if (newFilter.length > baseFilter.length) {
                    for (var j = 0, lenj = newFilter.length; j < lenj; j++) {
                        if (baseFilter.indexOf(newFilter[j]) < 0) {
                            paramFilter = newFilter[j];
                        }
                    }
                }
                if (paramFilter) {
                    var paramPosition = currentFilter.indexOf(paramFilter);
                    if (paramPosition >= 0) {
                        currentFilter.splice(paramPosition, 1);
                    } else {
                        if (multipleAttrs && multipleAttrs.indexOf(key) >= 0) {
                            currentFilter.push(paramFilter);
                        } else {
                            currentFilter = [paramFilter];
                        }
                    }
                }

                if (currentFilter.length > 0) {
                    var filterLen = currentFilter.length;
                    if (this.options.slider.hasOwnProperty(key)) {
                        this.filterParams[key] = [currentFilter[filterLen - 1]];
                    } else {
                        this.filterParams[key] = currentFilter;
                    }
                } else {
                    delete this.filterParams[key];
                }
            }

            return this;
        },

        initApplyFilterButton: function () {
            var self = this,
                buttonSubmit = this.options.buttonSubmit,
                seoUrlEnable = buttonSubmit.seoUrlEnable,
                urlSuffix = buttonSubmit.urlSuffix;

            if (seoUrlEnable) {
                this.baseSeoFilterParams = this.getSeoUrlParams();
                this.seoFilterParams = this.getSeoUrlParams();
            }

            this.baseFilterParams = this.getUrlParams();
            this.filterParams = this.getUrlParams();

            self.element.find(this.options.buttonSubmitElement).click(function () {
                var baseUrl = buttonSubmit.baseUrl,
                    params = {};

                if (seoUrlEnable && self.seoFilterParams.length) {
                    baseUrl = baseUrl.replace(new RegExp(urlSuffix + '$'), '');
                    baseUrl += '/' + self.seoFilterParams.join('-') + urlSuffix;
                }

                for (var elm in self.filterParams) {
                    if (self.filterParams.hasOwnProperty(elm)) {
                        params[elm] = self.filterParams[elm].join(',');
                    }
                }
                params = $.param(params);

                self.ajaxSubmit(baseUrl + (params.length ? '?' + params : ''), true);
            });
        },

        getSeoUrlParams: function (url) {
            var urlSuffix = this.options.buttonSubmit.urlSuffix,
                baseUrl = this.options.buttonSubmit.baseUrl,
                currentUrl = (typeof url !== 'undefined') ? url.split('?')[0] : window.location.origin + window.location.pathname;

            if (baseUrl.length === currentUrl.length) {
                return [];
            }

            var paramUrl = currentUrl.replace(new RegExp(urlSuffix + '$'), '').split('/').pop();

            return paramUrl.split('-');
        },

        getUrlParams: function (url) {
            var params = {},
                queryString = (typeof url !== 'undefined') ? url.split('?')[1] : window.location.search.slice(1);
            if (queryString) {
                queryString = queryString.split('#')[0];
                queryString = queryString.split('&');

                for (var key in queryString) {
                    if (!queryString.hasOwnProperty(key)) {
                        continue;
                    }

                    var string = queryString[key].split('=');
                    if (string.length === 2) {
                        params[string[0]] = decodeURIComponent(string[1]).split(',');
                    }
                }
            }

            return params;
        },

        displaySliderText: function (code, from, to, format) {
            this._super(code, from, to, format);

            var rangeElement = this.element.find(this.options.sliderRangeElementPrefix + code);
            if (rangeElement.length) {
                this.element.find(this.options.sliderFromElementPrefix + code).val(from);
                this.element.find(this.options.sliderToElementPrefix + code).val(to);
            }
        },

        initSlider: function () {
            this._super();
            this.initRangeSlider();
        },

        initRangeSlider: function () {
            var self = this,
                slider = this.options.slider;

            //change range input
            var fromInput = this.element.find('input.ln_slider_input_from'),
                toInput = this.element.find('input.ln_slider_input_to');
            if (fromInput && toInput) {
                fromInput.each(function () {
                    var code = self.getSliderCode($(this).attr('id')),
                        sliderMax = self.element.find(self.options.sliderToElementPrefix + code).val(),
                        sliderMin = slider[code].minValue,
                        sliderElement = self.element.find(self.options.sliderElementPrefix + code);

                    $(this).change(function () {
                        var value = parseFloat($(this).val());

                        value = (value > sliderMax) ? sliderMax : ((value < sliderMin) ? sliderMin : value);
                        $(this).val(value);

                        if (sliderElement.length) {
                            sliderElement.slider('values', 0, value);
                        } else {
                            self.ajaxSubmit(self.getSliderUrl(slider[code].ajaxUrl, value, sliderMax));
                        }
                    });
                });

                toInput.each(function () {
                    var code = self.getSliderCode($(this).attr('id')),
                        sliderMax = slider[code].maxValue,
                        sliderMin = self.element.find(self.options.sliderFromElementPrefix + code).val(),
                        sliderElement = self.element.find(self.options.sliderElementPrefix + code);

                    $(this).change(function () {
                        var value = parseFloat($(this).val());

                        value = (value > sliderMax) ? sliderMax : ((value < sliderMin) ? sliderMin : value);
                        $(this).val(value);

                        if (sliderElement.length) {
                            sliderElement.slider('values', 1, value);
                        } else {
                            self.ajaxSubmit(self.getSliderUrl(slider[code].ajaxUrl, sliderMin, value));
                        }
                    });
                });
            }

            var sliderEl = this.element.find('.ln_slider_element');
            if (sliderEl.length) {
                var sliderConfig;
                sliderEl.slider({
                    slide: function (event, ui) {
                        var code = self.getSliderCode($(this).attr('id'));
                        sliderConfig = slider[code];
                        var priceFormat = sliderConfig.hasOwnProperty('priceFormat') ? JSON.parse(sliderConfig.priceFormat) : null;
                        var sliderRange = $(self.options.sliderRangeElementPrefix + code);
                        if (sliderRange.length) {
                            self.element.find(self.options.sliderFromElementPrefix + code).val(ui.values[0]);
                            self.element.find(self.options.sliderToElementPrefix + code).val(ui.values[1]);
                        }
                        self.displaySliderText(code, ui.values[0], ui.values[1], priceFormat);
                    },
                    change: function (event, ui) {
                        var code = self.getSliderCode($(this).attr('id'));

                        self.ajaxSubmit(self.getSliderUrl(slider[code].ajaxUrl, ui.values[0], ui.values[1]));
                    }
                });
            }
        },

        getSliderCode: function (id) {
            return id.replace('ln_slider_from_', '').replace('ln_slider_to_', '').replace('ln_slider_', '');
        },

        checkDevice: function () {
            var check = false;
            (function (a) {
                if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
            })(navigator.userAgent || navigator.vendor || window.opera);
            return check;
        }

    });

    return $.mageplaza.layer;
});

