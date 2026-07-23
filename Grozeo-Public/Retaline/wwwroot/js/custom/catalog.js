var catalog = function () {
    var catalogMethods = {}, catalogPrivateMethods = {};

    catalogMethods.events = {};

    catalogMethods.properties = {
    };
    catalogPrivateMethods.properties = {
        isOtpTriggered: false
    };
    catalogMethods.url = {
        addTocatalog: '',
        sideBannerSmall: '/sidebannersmall'
    };

    catalogPrivateMethods.controls = {
        sideBannerSmall:'.LeftAddSec'
    };
    catalogPrivateMethods.loadSideBar = function () {
        if ($(catalogPrivateMethods.controls.sideBannerSmall).length > 0) {
            $(catalogPrivateMethods.controls.sideBannerSmall).find('div.addList img').attr('src', '');
            onSuccess = function (result) {
                if (result && result.adinfo) {
                    $(catalogPrivateMethods.controls.sideBannerSmall).removeClass('hide')
                    var banner = result.adinfo;
                    $(catalogPrivateMethods.controls.sideBannerSmall).find('div.addList img').attr('src', banner.adv_imageurl);
                    $(catalogPrivateMethods.controls.sideBannerSmall).find('div.addList a').attr('href', result.url);
                }
            };
            onError = function (data) {
            };
            master.ajax.JSONRequest(catalogMethods.url.sideBannerSmall, 'GET', {}, onSuccess, onError);
        }

    }
    $('.three-grid-container').on('click', function () {
        $(this).addClass('active').siblings('.five-grid-container').removeClass('active');
        $('ul.itemsListing').addClass('three-gridview');
    });
    $('.five-grid-container').on('click', function () {
        $(this).addClass('active').siblings('.three-grid-container').removeClass('active');
        $('ul.itemsListing').removeClass('three-gridview');
    });
    
  

    catalogMethods.events.initialize = function () {
        var controls = catalogPrivateMethods.controls;
    };

    catalogMethods.initializePage = function () {
        catalogMethods.events.initialize();
        catalogPrivateMethods.loadSideBar();
    };

    return catalogMethods;
}();

$(function () {
    catalog.initializePage();
});