/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

require([
        'jquery',
        'productListToolbarForm'
    ], function ($) {
    'use strict';
        function loadAjax(link) {
            $('.ln_overlay').show();
            $.ajax({
                type: 'POST',
                url: link,
                success: function (response) {
                    var layerProductList;

                    if (response.status === 'ok') {
                        $('.related-product-modal-content').html(
                            response.products
                        );
                        initProductListUrl();
                        initPageUrl();
                        $('body').trigger('contentUpdated');
                        layerProductList = $("#layer-product-list");
                        if (layerProductList.find(".grid li").length > 0) {
                            layerProductList.find(".grid li").each(function () {
                                $(this).addClass("quickview_product_img");
                            });
                        }
                        $('.ln_overlay').hide();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);

                }
            });
        }

        function initPageUrl() {
            var pageElement = $('#layer-product-list').find($('.pages').find('a'));

            pageElement.each(function () {
                var el = $(this),
                    link = el.prop('href');

                if (!link) {
                    return;
                }
                el.bind('click', function (e) {
                    loadAjax(link);
                    e.stopPropagation();
                    e.preventDefault();
                });
            });
        }

        function initProductListUrl() {
            var isProcessToolbar = false;

            $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
                var urlPaths = this.options.url.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    link, parameters, i;

                if (isProcessToolbar) {
                    return;
                }
                isProcessToolbar = true;

                for (i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[parameters[0]] = parameters[1] !== undefined
                        ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                paramData[paramName] = paramValue;
                if (paramValue === defaultValue) {
                    delete paramData[paramName];
                }
                paramData = $.param(paramData);
                link = baseUrl + (paramData.length ? '?' + paramData : '');
                loadAjax(link);
            };
        }

        $(".fa-eye").each(function () {
            $(this).click(function () {
                var brand = $(this).attr('id');
                var url = window.quickviewUrl + brand;

                $('.open_model').show();
                $('.ln_overlay').show();

                $.ajax({
                    type: 'POST',
                    url: url,
                    success: function (response) {
                        var layerProductList;

                        if (response.status === 'ok') {
                            $('.brand_title').text(response.brand['value']);
                            if (response.brand['image'] == null) {
                                $('.quickview_img').attr('src', '');
                            } else {
                                $('.quickview_img').attr('src', response.brand['image']);
                            }
                            $('.related-product-modal-content').html(response.products);
                            if (response.brand['short_description']) {
                                $('.brand_description').html($("<p>" + response.brand['short_description'] + "</p>"));
                            }
                            $('.ln_overlay').hide();
                            initProductListUrl();
                            initPageUrl();
                            $('body').trigger('contentUpdated');

                            layerProductList = $("#layer-product-list");
                            if (layerProductList.find(".grid li").length > 0) {
                                layerProductList.find(".grid li").each(function () {
                                    $(this).addClass("quickview_product_img");
                                });
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.status);
                        console.error(thrownError);
                    }
                });
            });
        });
    }
);