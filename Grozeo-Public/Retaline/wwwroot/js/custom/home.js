var home = function () {
    var homeMethods = {}, homePrivateMethods = {};

    homePrivateMethods.events = {};
    homeMethods.url = {
        cartAndWishlist: '/loadcartandwishlist'
    };
    homePrivateMethods.url = {
        footerContentMini: '/about-us-mini'
    };

    homePrivateMethods.controls = {
        businessType: '#drp-business-type',
        businessTypeTile: '.business-type-tile',
        offer: '.offer-slide',
        footerAboutUs: '#aboutus-footer'
    }

    homePrivateMethods.loadCartAndWishlist = function () {
        master.properties.isLoadingCartAndWishlist = true;
        onSuccess = function (result) {
            master.properties.isLoadingCartAndWishlist = false;
            master.properties.hasLoadedCartAndWishlist = true;
            if (result) {
                if (result.catitems)
                    cart.properties.cartItems.items = result.catitems;
                if (result.catitems.length > 0)
                    cart.updateCartButton();
                if (result.wishlistitems.length > 0)
                    wishlist.updateWishLitemsInHomePage(result.wishlistitems);
            }
        };
        onError = function (data) {
            master.properties.isLoadingCartAndWishlist = false;
            master.properties.hasLoadedCartAndWishlist = true;
        };
        master.ajax.JSONRequest(homeMethods.url.cartAndWishlist, 'GET', {}, onSuccess, onError);
    }

    homePrivateMethods.events.initialize = function () {
        $(homePrivateMethods.controls.businessType).unbind('change').on('change', function () {
            window.location.href = '/bt/' + $(this).val() + '/' + $(this).children("option").filter(":selected").text();
        });

    }


    homeMethods.initializePage = function () {
        homePrivateMethods.events.initialize();
        homePrivateMethods.loadCartAndWishlist();
        //homePrivateMethods.loadFooterAboutUs();
    };

    return homeMethods;
}();
$(function () {
    home.initializePage();
});