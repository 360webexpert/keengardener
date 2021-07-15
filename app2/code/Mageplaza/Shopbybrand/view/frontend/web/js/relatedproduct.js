require([
        'jquery',
        'productListToolbarForm'
    ], function ($) {
        "use strict";

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
                            layerProductList.each(function () {
                                $(this).addClass("edit-item-product");
                            });
                        }
                        $('.ln_overlay').hide();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.error(thrownError);
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

        $("#tab-label-related-brand-product-tab").each(function () {
            $(this).click(function () {
                var title = $(".sku").find('div').text(),
                    url = $(".product-brand-logo").find("a").attr('href') + '?title=' + title;

                $('.open_model').show();
                $('.ln_overlay').show();
                $('#show-product-in-view').attr('display', 'block');
                $.ajax({
                    type: 'POST',
                    url: url,
                    success: function (response) {
                        var layerProductList;

                        if (response.status === 'ok') {
                            $('.related-product-modal-content').html(
                                response.products
                            );
                            $('.ln_overlay').hide();
                            initProductListUrl();
                            initPageUrl();
                            $('body').trigger('contentUpdated');

                            layerProductList = $("#layer-product-list");
                            if (layerProductList.find(".grid li").length > 0) {
                                layerProductList.each(function () {
                                    $(this).addClass("edit-item-product");
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