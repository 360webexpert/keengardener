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
                success: function (reponse) {
                    if (reponse.status === 'ok') {
                        $('.related-product-modal-content').html(
                            reponse.products
                        );
                        initProductListUrl();
                        initPageUrl();
                        $('body').trigger('contentUpdated');
                        if ($("#layer-product-list").find(".grid li").length > 0) {
                            $("#layer-product-list").each(function () {
                                $(this).addClass("edit-item-product")
                            })
                        }
                        $('.ln_overlay').hide();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);
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
                })
            });
        }

        function initProductListUrl() {
            var isProcessToolbar = false;
            $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
                if (isProcessToolbar) {
                    return;
                }
                isProcessToolbar = true;

                var urlPaths = this.options.url.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;
                for (var i = 0; i < urlParams.length; i++) {
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
                var link = baseUrl + (paramData.length ? '?' + paramData : '');
                loadAjax(link);
            }
        }

        $("#tab-label-related-brand-product-tab").each(function () {
            $(this).click(function () {
                $('.open_model').show();
                $('.ln_overlay').show();
                $('#show-product-in-view').attr('display', 'block');
                var title = $(".sku").find('div').text();
                var url = $(".product-brand-logo").find("a").attr('href') + '?title=' + title;
                $.ajax({
                    type: 'POST',
                    url: url,
                    success: function (reponse) {
                        if (reponse.status === 'ok') {
                            $('.related-product-modal-content').html(
                                reponse.products
                            );
                            $('.ln_overlay').hide();
                            initProductListUrl();
                            initPageUrl();
                            $('body').trigger('contentUpdated');
                            if ($("#layer-product-list").find(".grid li").length > 0) {
                                $("#layer-product-list").each(function () {
                                    $(this).addClass("edit-item-product")
                                })
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.status);
                        alert(thrownError);
                    }
                });
            });
        });
    }
);