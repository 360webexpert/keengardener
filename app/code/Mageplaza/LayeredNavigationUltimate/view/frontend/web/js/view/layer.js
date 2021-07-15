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
 * @package     Mageplaza_LayeredNavigationUltimage
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Mageplaza_LayeredNavigationPro/js/view/layer',
    'Mageplaza_LayeredNavigationUltimate/js/action/submit-filter',
    'mage/translate',
    'mpIonRangeSlider'
], function ($, layer, submitInfiniteAction, $t) {
    "use strict";

    $.widget('mageplaza.layer', layer, {
        options: {
            infiniteScroll: false
        },

        _create: function () {
            this._super();

            this.initDisplayType();

            if (this.options.infiniteScroll) {
                this.initInfiniteScroll();
            }
        },

        /**
         * Prepare for horizontal side bar
         */
        initActiveItems: function () {
            var horiEl = this.element.parents('#layered-horizontal-container');
            if (horiEl.length) {
                this.options.multipleCollapsible = false;
                this.options.active = [];
            } else {
                this._super();
            }
        },

        initDisplayType: function () {
            var self = this,
                types = this.options.displayType;

            for (var code in types) {
                if (types.hasOwnProperty(code) && types[code]) {
                    var type = types[code],
                        element = this.element.find('.ln-items-' + code);

                    if (type.type === 'scroll') {
                        element.css('max-height', type.value + 'px');
                        element.css('overflow-y', 'auto');
                    } else if (element.find('li').length > type.value) {
                        element.after($('<a></a>', {
                            href: '#',
                            class: 'active ln-show-more ln-show-more-' + code
                        }).text($t('Show more')));
                        element.after($('<a></a>', {
                            href: '#',
                            class: 'ln-show-less ln-show-less-' + code
                        }).css('display', 'none').attr('size', type.value).text($t('Show less')));

                        this.displayHiddenItem(element, type.value);
                    }
                }
            }

            self.element.find('.ln-show-more').on('click', function (e) {
                var el = $(this),
                    lessEl = el.siblings('.ln-show-less');
                lessEl.show();
                lessEl.addClass('active');

                el.siblings('.items').find('li').show();
                el.hide();
                el.removeClass('active');

                e.stopPropagation();
                e.preventDefault();
            });

            self.element.find('.ln-show-less').on('click', function (e) {
                var el = $(this),
                    moreEl = el.siblings('.ln-show-more');
                moreEl.show();
                moreEl.addClass('active');

                self.displayHiddenItem(el.siblings('.items'), el.attr('size'));
                el.hide();
                el.removeClass('active');

                e.stopPropagation();
                e.preventDefault();
            });
        },

        initSearchObserver: function () {
            var self = this;

            this.element.find('.layer-search-box').on('keyup', function () {
                var el = $(this),
                    attributeCode = el.attr('code'),
                    searchTerm = el.val().toLowerCase(),
                    types = self.options.displayType;

                self.element.find('.layer-search-list-' + attributeCode + ' li').each(function () {
                    if ($(this).filter('[data-search-term *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                if (types.hasOwnProperty(attributeCode) && (types[attributeCode].type === 'hidden')) {
                    var showMoreElement = el.siblings('.ln-show-more'),
                        showLessElement = el.siblings('.ln-show-less');

                    showMoreElement.hide();
                    showLessElement.hide();
                    if (searchTerm.length < 1) {
                        if (showMoreElement.hasClass('active')) {
                            self.displayHiddenItem(el.siblings('.items'), types[attributeCode].value);
                            showMoreElement.show();
                        } else {
                            el.siblings('.items').find('li').show();
                            showLessElement.show();
                        }
                    }
                }
            });
        },

        displayHiddenItem: function (el, size) {
            var options = el.find('li');

            options.hide();
            options.each(function () {
                $(this).show();
                if (--size === 0) {
                    return false;
                }
            })
        },

        initSlider: function () {
            if (this.options.ratingSlider) {
                this.initRatingSlider();
            }

            if (!this.options.ionRange) {
                return this._super();
            }

            var self = this,
                slider = this.options.slider;

            for (var code in slider) {
                if (slider.hasOwnProperty(code)) {
                    var sliderConfig = slider[code],
                        sliderElement = self.element.find(this.options.sliderElementPrefix + code),
                        rangeElement = self.element.find(this.options.sliderRangeElementPrefix + code),
                        priceFormat = sliderConfig.hasOwnProperty('priceFormat') ? JSON.parse(sliderConfig.priceFormat) : null;

                    sliderElement.ionRangeSlider({
                        type: "double",
                        min: sliderConfig.minValue,
                        max: sliderConfig.maxValue,
                        from: sliderConfig.selectedFrom,
                        to: sliderConfig.selectedTo,
                        priceFormat: priceFormat,
                        ajaxUrl: sliderConfig.hasOwnProperty('ajaxUrl') ? sliderConfig.ajaxUrl : null,
                        firstLoad: true,
                        fromElement: rangeElement.length ? self.element.find(self.options.sliderFromElementPrefix + code) : null,
                        toElement: rangeElement.length ? self.element.find(self.options.sliderToElementPrefix + code) : null,
                        prettify: function (value) {
                            if (this.priceFormat !== null) {
                                value = self.formatPrice(value, this.priceFormat);
                            }
                            self.displaySliderText(code, this.from, this.to, this.priceFormat);

                            return value;
                        },
                        onChange: function (data) {
                            self.displaySliderText(code, data.from, data.to, this.priceFormat);
                        },
                        onFinish: function (data) {
                            if (!this.firstLoad && typeof this.ajaxUrl !== null) {
                                self.ajaxSubmit(self.getSliderUrl(this.ajaxUrl, data.from, data.to));
                            }

                            this.firstLoad = false;

                            return this;
                        }
                    });

                    self.displaySliderText(code, sliderConfig.selectedFrom, sliderConfig.selectedTo, priceFormat);
                }
            }

            //change range input
            var fromInput = this.element.find('input.ln_slider_input_from'),
                toInput = this.element.find('input.ln_slider_input_to');
            if (fromInput && toInput) {
                fromInput.each(function () {
                    var code = self.getSliderCode($(this).attr('id')),
                        sliderMax = self.element.find(self.options.sliderToElementPrefix + code).val(),
                        sliderMin = slider[code].minValue,
                        sliderElement = self.element.find(self.options.sliderElementPrefix + code),
                        sliderInstance = sliderElement.data("ionRangeSlider");

                    $(this).change(function () {
                        var value = parseFloat($(this).val());

                        value = (value > sliderMax) ? sliderMax : ((value < sliderMin) ? sliderMin : value);
                        $(this).val(value);

                        if (sliderElement.length) {
                            sliderInstance.update({from: value});
                        }

                        self.ajaxSubmit(self.getSliderUrl(slider[code].ajaxUrl, value, sliderMax));
                    });
                });

                toInput.each(function () {
                    var code = self.getSliderCode($(this).attr('id')),
                        sliderMax = slider[code].maxValue,
                        sliderMin = self.element.find(self.options.sliderFromElementPrefix + code).val(),
                        sliderElement = self.element.find(self.options.sliderElementPrefix + code),
                        sliderInstance = sliderElement.data("ionRangeSlider");

                    $(this).change(function () {
                        var value = parseFloat($(this).val());

                        value = (value > sliderMax) ? sliderMax : ((value < sliderMin) ? sliderMin : value);
                        $(this).val(value);

                        if (sliderElement.length) {
                            sliderInstance.update({to: value});
                        }

                        self.ajaxSubmit(self.getSliderUrl(slider[code].ajaxUrl, sliderMin, value));
                    });
                });
            }
        },

        initRatingSlider: function () {
            var self = this,
                slider = this.options.ratingSlider,
                code = slider.ratingCode,
                sliderElement = self.element.find(this.options.sliderElementPrefix + code),
                ratingParam = self.getUrlParams(window.location.href);

            if (ratingParam.hasOwnProperty(code)) {
                self.changeRatingStarsColor(ratingParam[code]);
            }

            if (sliderElement.length) {
                sliderElement.slider({
                    range: true,
                    min: slider.minValue,
                    max: slider.maxValue,
                    values: [slider.selectedFrom, slider.selectedTo],
                    orientation: slider.orientation,
                    slide: function (event, ui) {
                        self.changeRatingStarsColor(ui, true);
                    },
                    change: function (event, ui) {
                        self.ajaxSubmit(self.getSliderUrl(slider.ajaxUrl, ui.values[0], ui.values[1]));
                    }
                });
            }
        },

        changeRatingStarsColor: function (uiData, slide) {
            var self = this,
                listStars = this.element.find('.rating-slider-items');
            if (listStars.length) {
                var stars = listStars.find('.ln_rating_stars');
                if (typeof slide !== 'undefined') {
                    self.addRatingClass(stars, uiData.values[0], uiData.values[1]);
                } else {
                    var currentRating = uiData[0].split('-');
                    self.addRatingClass(stars, currentRating[0], currentRating[1]);
                }
            }
        },

        addRatingClass: function (stars, data1, data2) {
            stars.each(function () {
                var countStar = $(this).attr('data-count-star');
                if (countStar > data2 || countStar < data1) {
                    $(this).addClass('rating-star-off');
                } else {
                    $(this).removeClass('rating-star-off');
                }
            });
        },

        initInfiniteScroll: function () {
            var self = this;

            $('#layer-product-list').find('.toolbar.toolbar-products').last().hide();
            // $('#toolbar-amount').hide();

            $(window).bind('scroll', function () {
                self.checkInfiniteScroll();
            });
            self.checkInfiniteScroll();
        },

        checkInfiniteScroll: function () {
            var self = this,
                listEl = $('.items.product-items');

            if (listEl.length && ($(window).scrollTop() >= listEl.offset().top + listEl.outerHeight() - window.innerHeight)) {
                submitInfiniteAction(self, listEl);
            }
        }
    });

    return $.mageplaza.layer;
});
