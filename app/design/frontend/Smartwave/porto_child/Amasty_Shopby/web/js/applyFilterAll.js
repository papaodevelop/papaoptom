define([
    "underscore",
    "jquery",
    "amShopbyFilterAbstract",
    "mage/translate"
], function (_, $) {
    'use strict';

    $.widget('mage.applyFilterAll', {
        isMobile: window.innerWidth < 768,

        _create: function () {
            var self = this;
            $(function () {
                var element = $(self.element[0]),
                    navigation = element.closest(self.options.navigationSelector),
                    isMobile = $.mage.applyFilterAll.prototype.isMobile;
                if (isMobile) {
                    $(element).hide();
                }
                element.on('click', function (e) {
                    var valid = true,
                        cachedValues = $.mage.amShopbyAjax.prototype
                            ? $.mage.amShopbyAjax.prototype.cached[$.mage.amShopbyAjax.prototype.cacheKey]
                            : null,
                        cachedKey = $.mage.amShopbyAjax.prototype.response;

                    navigation.find('form').each(function () {
                        valid = valid && $(this).valid();
                    });

                    var response = cachedValues ? cachedValues : cachedKey;

                    $.mage.amShopbyFilterAbstract.prototype.options.isCategorySingleSelect
                        = self.options.isCategorySingleSelect;

                    if (!response && $.mage.amShopbyAjax.prototype.startAjax) {
                        $("#amasty-shopby-overlay").show();
                    }

                    if (valid && self.options.ajaxEnabled && response) {
                        window.history.pushState({url: response.url}, '', response.url);
                        $(document).trigger('amshopby:reload_html', {response: response});
                        $.mage.amShopbyAjax.prototype.response = false;
                    }

                    window.onpopstate = function () {
                        location.reload();
                    };

                    if (valid && self.options.ajaxEnabled != 1) {
                        var forms = $('form[data-amshopby-filter]'),
                            data = $.mage.amShopbyFilterAbstract.prototype.normalizeData(forms.serializeArray()),
                            baseUrl = self.options.clearUrl;

                        if (typeof data.clearUrl !== 'undefined') {
                            baseUrl = data.clearUrl;
                            delete data.clearUrl;
                        }
                        var params = $.param(data),
                            url = baseUrl +
                                (baseUrl.indexOf('?') === -1 ? '?' : '&') +
                                params;
                        document.location.href = url;
                    }
                    this.blur();
                    return true;
                });

            });
        }
    });
});
