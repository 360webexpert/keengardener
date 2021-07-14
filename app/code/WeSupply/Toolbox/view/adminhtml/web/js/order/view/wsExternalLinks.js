define([
    'jquery'
], function ($) {
    'use strict';
    var wsExternalLinks = wsExternalLinks || {};

    wsExternalLinks.init = function()
    {
        this.externalLinksBtn = $('#ws_external_links_btn');
        this.wrapperId = 'ws_external_links_wrapper';

        this.prepareLinkOptions(this.externalLinksBtn.data('ws-links') ?? {});

        this.createBtnWrapper();
        if (wsExternalLinks.links.length) {
            this.appendLinks();

            this.optionsWrapper = $('#' + this.wrapperId);
            this.EventObserver();
        }
    };

    wsExternalLinks.prepareLinkOptions = function(options)
    {
        wsExternalLinks.links = [];
        $.each(options, function(linkId, data) {
            wsExternalLinks.links.push('<a href="' + data.url + '" target="_blank" title="' + data.title + '">' + data.title + '</a>');
        });
    };

    wsExternalLinks.createBtnWrapper = function()
    {
        wsExternalLinks.externalLinksBtn
            .wrapAll('<div id="' + wsExternalLinks.wrapperId + '" class="ws-external-links-wrapper"></div>');
    };

    wsExternalLinks.appendLinks = function()
    {
        var html =  '<ul class="dropdown-menu">\n';
        $.each(wsExternalLinks.links, function(index, link) {
            html += '<li><span>' + link + '</span></li>\n';
        });
        html += '</ul>';

        $('#'  +wsExternalLinks.wrapperId).addClass('has-children').append(html);
    };

    wsExternalLinks.EventObserver = function()
    {
        wsExternalLinks.optionsWrapper.on('mouseenter', function(ev) {
            $(ev.currentTarget).addClass('active')
                .find('.dropdown-menu').show();
        });
        wsExternalLinks.optionsWrapper.on('mouseleave', function(ev) {
            $(ev.currentTarget).removeClass('active')
                .find('.dropdown-menu').hide();
        });

        $(window).scroll(function () {
            var scroll = $(window).scrollTop();
            if ($(window).scrollTop()) {
                $('.page-actions-inner').addClass('moved');
            } else {
                $('.page-actions-inner').removeClass('moved');
            }
        });
    };

    return wsExternalLinks;
});
