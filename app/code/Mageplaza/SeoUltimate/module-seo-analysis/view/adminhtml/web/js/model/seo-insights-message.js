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
    'jquery',
    'uiClass',
    'mage/translate'
], function ($, Class, $t) {
    'use strict';

    var SeoInsightsMessage;

    SeoInsightsMessage = Class.extend({
        defaults: {
            mp_score: 0,
            mp_queue: [],
            indexField: 'entity_id',
            data: {}
        },

        /**
         * Check string in content
         *
         * @param {String} str - Specific string
         * @param {String} content - Specific content
         * @returns {Boolean}
         */
        inSpecificContent: function (str, content) {
            return this.matchTextWithWord(content, str).length > 0;
        },

        /**
         * Display result
         * @returns {Array}
         */
        getResult: function () {
            var result = this.mp_queue;
            result.sort(function (a, b) {
                var aState = a.state_class,
                    bState = b.state_class;

                var aOrder = aState.search('error') >= 0 ? 1 : (aState.search('warning') >= 0 ? 2 : 3),
                    bOrder = bState.search('error') >= 0 ? 1 : (bState.search('warning') >= 0 ? 2 : 3);

                if (aOrder < bOrder) {
                    return -1;
                } else if (aOrder > bOrder) {
                    return 1;
                }

                return 0;
            });

            this.mp_queue = [];

            return result;
        },

        /**
         * Check key in meta Title
         *
         * @param {String} key
         * @param {String} metaTitle
         * @returns {SeoInsightsMessage}
         */
        inMetaTitle: function (key, metaTitle) {
            var data = {};
            if (this.inSpecificContent(key.toLowerCase(), metaTitle.toLowerCase())) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Focus Keyword is included in meta title.');
            } else {
                data.state_class = SeoInsightsMessage.ERROR;
                data.message = $t('Focus Keyword is not included in meta title.');
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Check key in description
         * @param key
         * @param description
         * @returns {SeoInsightsMessage}
         */
        inFirstParagraph: function (key, description) {
            var data = {},
                firstParagraph = $(description).filter('p').first();
            if (!firstParagraph.length) {
                firstParagraph = description;
            } else {
                firstParagraph = firstParagraph.text();
            }

            if (this.inSpecificContent(key.toLowerCase(), firstParagraph.toLowerCase())) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Focus Keyword is included in the first paragraph.');
            } else {
                data.state_class = SeoInsightsMessage.ERROR;
                data.message = $t('Focus Keyword is not included in the first paragraph.');
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Check key in Meta Description
         *
         * @param {String} key
         * @param {String} metaDescription
         * @returns {SeoInsightsMessage}
         */
        inMetaDescription: function (key, metaDescription) {
            var data = {};
            if (this.inSpecificContent(key.toLowerCase(), metaDescription.toLowerCase())) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Focus Keyword is included in meta description.');
            } else {
                data.state_class = SeoInsightsMessage.ERROR;
                data.message = $t('Focus Keyword is not included in meta description.');
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Calculate the density of key in Description
         * @param key
         * @param description
         * @returns {SeoInsightsMessage}
         */
        calculateDensity: function (key, description) {
            var self = this,
                data = {},
                formatDes = this.stripTags(description.toLowerCase().replace(/ +(?= )/g, '')),
                foundNumber = this.matchTextWithWord(formatDes, key).length;

            var density = foundNumber / self.calculateWord(formatDes) * 100;
            density = density.toFixed(1);

            if (density > 1) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Keyword Density is %1%, which is great; the focus keyword was found %2 times.').replace('%1', density).replace('%2', foundNumber);
            } else {
                data.state_class = SeoInsightsMessage.ERROR;
                data.message = $t('Keyword Density is %1% (Recommended: 1-3%); the focus keyword was found %2 times.').replace('%1', density).replace('%2', foundNumber);
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Check key in url
         *
         * @param {String} key
         * @param {String} url
         * @returns {SeoInsightsMessage}
         */
        inUrlKey: function (key, url) {
            var data = {};
            if (url.toLowerCase().indexOf(key.toLowerCase()) >= 0) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Focus Keyword is included in the URL of this page.');
            } else {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('Focus Keyword should be contained in the URL of this page.');
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Evaluate Meta Title
         *
         * @param {String} metaTitle
         * @returns {SeoInsightsMessage}
         */
        evaluateMetaTitle: function (metaTitle) {
            var data = {};
            if (metaTitle && (metaTitle.length * 10) > 300) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Meta title length is adequate.');
            } else {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('Meta title length is inadequate, you should add more meta title.');

            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Evaluate Meta Description
         *
         * @param metaDescription
         * @returns {SeoInsightsMessage}
         */
        evaluateMetaDescription: function (metaDescription) {
            var data = {};
            if (metaDescription && (metaDescription.length * 3) > 300) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Meta description length is adequate.');
            } else {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('Meta description is inadequate, you should add more meta description.');
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Evaluate Description content
         *
         * @returns {SeoInsightsMessage}
         */
        evaluateDescriptionContent: function (description) {
            var data = {},
                formatDes = this.stripTags(description.toLowerCase().replace(/ +(?= )/g, '')),
                wordNumber = this.calculateWord(formatDes);

            if (wordNumber > 300) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Description contains %1 words, which is good.').replace('%1', wordNumber);
            } else {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('Description contains %1 words, which is inadequate (Recommended: equal or greater than 300 words).').replace('%1', wordNumber);
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Determine out links
         *
         * @returns {SeoInsightsMessage}
         */
        determineOutLink: function (description, baseUrl) {
            var data = {},
                links = $(description).contents().filter("a"),
                hasOutLink = false,
                number = 0
            ;
            links.each(function (index, link) {
                if ($(link).attr("href") && $(link).attr("href").indexOf(baseUrl) < 0) {
                    hasOutLink = true;
                    number++;
                }
            });

            if (!hasOutLink) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Description has NO outbound link.');
            } else if (number >= 1 && number <= 3) {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Description has %1 outbound links.').replace('%1', number);
            } else {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('Description has %1 outbound links (Recommended: from 1 to 3 outbound links).').replace('%1', number);
            }
            this.mp_queue.push(data);

            return this;
        },

        /**
         * Check key in Image alt
         * @param key
         * @param description
         * @param images
         * @returns {SeoInsightsMessage}
         */
        inImageAlt: function (key, description, images) {
            var self = this,
                data = {},
                failAttemp = 0,
                mainImages = $('textarea[data-role="image-description"]'),
                descriptionImages = $(description).filter("img");

            if (mainImages.length) {
                images = [];
                mainImages.each(function () {
                    images.push($(this).val());
                });
            }
            $.each(images, function (index, value) {
                if (!value || !self.inSpecificContent(key, value)) {
                    failAttemp++;
                }
            });

            if (failAttemp) {
                data.state_class = SeoInsightsMessage.WARNING;
                data.message = $t('%1 product image(s) has(have) NO focus keyword in the description. You should implement it..').replace('%1', failAttemp);
            } else {
                data.state_class = SeoInsightsMessage.SUCCESS;
                data.message = $t('Product image alts has focus keyword.');
            }

            this.mp_queue.push(data);

            if (descriptionImages.length) {
                failAttemp = 0;
                descriptionImages.each(function (index, img) {
                    var alt = $(img).attr("alt");
                    if (!alt || !self.inSpecificContent(key, alt)) {
                        failAttemp++;
                    }
                });

                if (failAttemp) {
                    this.mp_queue.push({
                        state_class: SeoInsightsMessage.WARNING,
                        message: $t('%1 image(s) don\'t contain the main keyword in the product description. You should implement it.').replace('%1', failAttemp)
                    });
                }
            }

            return this;
        },

        /**
         * Strip HTML tag
         *
         * @param {String} input
         * @returns {*}
         */
        stripTags: function (input) {
            var startTag = /<([a-z][a-z0-9]*)\b[^>]*>/gmi;
            var endTag = /<\/([a-z][a-z0-9]*)\b[^>]*>/gmi;

            return input.replace(startTag, '').replace(endTag, '');
        },

        /**
         * @param text
         * @param word
         * @returns {Array}
         */
        matchTextWithWord: function (text, word) {
            text = text.replace(/&nbsp;/g, " ");
            text = text.replace(/\s/g, " ");

            var regex = this.addWordboundary(word);

            return text.match(regex) || [];
        },

        /**
         * @param matchString
         * @returns {*}
         */
        addWordboundary: function (matchString) {
            var wordBoundary, wordBoundaryStart, wordBoundaryEnd;

            wordBoundary = "[ \\u00a0 \\n\\r\\t\.,'\(\)\"\+\-;!?:\/»«‹›<>]";
            wordBoundaryStart = "(^|" + wordBoundary + ")";
            wordBoundaryEnd = "($|" + wordBoundary + ")";

            return new RegExp(wordBoundaryStart + matchString + wordBoundaryEnd, "ig");
        },

        /**
         * Calculate word in string
         * @param string
         */
        calculateWord: function (string) {
            var stringMatch = string.match(/([^\u0000-\u007F]|\w)+/g);

            return !stringMatch ? 0 : stringMatch.length;
        }
    });

    /**
     * Defile state class
     */
    Object.defineProperty(SeoInsightsMessage, "SUCCESS", {
        sortValue: 3,
        value: 'mp_icon mp_success',
        writable: false,
        enumerable: true,
        configurable: true
    });

    Object.defineProperty(SeoInsightsMessage, "WARNING", {
        sortValue: 2,
        value: 'mp_icon mp_warning',
        writable: false,
        enumerable: true,
        configurable: true
    });

    Object.defineProperty(SeoInsightsMessage, "ERROR", {
        sortValue: 1,
        value: 'mp_icon mp_error',
        writable: false,
        enumerable: true,
        configurable: true
    });

    return new SeoInsightsMessage;
});
