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
 * @package     Mageplaza_ProductLabels
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mageplaza.productlabels', {

        /**
         * @inheritDoc
         */
        _create: function () {
            this.initLabel();
        },

        initLabel: function () {
            var labelEl          = $('#mpfreegifts-gift-icon'),
                imgLabelEl       = $('#mpfreegifts-gift-icon-img'),
                labelTopPercent  = 0,
                labelLeftPercent = 100,
                labelWidth       = parseInt(this.options.iconWidth, 0),
                labelHeight      = parseInt(this.options.iconHeight, 0),
                galleryWidth     = parseInt(this.options.galleryWidth, 0),
                galleryHeight    = parseInt(this.options.galleryHeight, 0);

            $("[data-gallery-role=gallery-placeholder]").on('gallery:loaded', function () {
                var productImgEl  = $('.fotorama__stage'),
                    fotoramaStage = $('.fotorama__stage__shaft'),
                    top, left, width, height;

                if ($(imgLabelEl).attr('src')) {
                    imgLabelEl.css({
                        'width': '100%',
                        'height': '100%'
                    });
                }

                top    = (galleryHeight - labelHeight) * labelTopPercent / 100 / galleryHeight * 100;
                left   = (galleryWidth - labelWidth) * labelLeftPercent / 100 / galleryWidth * 100;
                width  = labelWidth * 100 / galleryWidth;
                height = labelHeight * 100 / galleryHeight;

                labelEl.css({
                    'width': width + '%',
                    'height': height + '%',
                    'top': top + '%',
                    'left': left + '%'
                });
                productImgEl.prepend(labelEl);
                labelEl.show();
                labelEl.after('<div id="mpfotorama-gift-icon"></div>');

                $('#mpfotorama-gift-icon').css({
                    'width': productImgEl.width(),
                    'height': productImgEl.height()
                });

                fotoramaStage.appendTo("#mpfotorama-gift-icon");
            });
        }

    });

    return $.mageplaza.productlabels;
});

