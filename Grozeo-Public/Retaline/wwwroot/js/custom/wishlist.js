var wishlist = function () {
    var wishlistMethods = {}, wishlistPrivateMethods = {};

    wishlistPrivateMethods.events = {};

    wishlistMethods.properties = {
        wishListItems: { 'items': [] },
        isProductDetailsPage: false
    };
    wishlistPrivateMethods.properties = {
        isOtpTriggered: false,
        currentInstance: null

    };
    wishlistMethods.url = {
        add: '/addtowishlist',
        delete: '/deletefromwishlist',
        get: '/wishlist'
    };

    wishlistPrivateMethods.controls = {
        homeWishlistIconBlock: '.wishlist-block',
        delete: '.delete-product-from-wishlist',
        navigator: '.open-wishlist',
        moveFromCart: '.move-to-wishlist',
        addToCart: '.addcart-wishlist'
    };

    wishlistMethods.updateWishLitemsInHomePage = function (wishlistItems = []) {
        // Nothing to do if there's no data
        if (!Array.isArray(wishlistItems) || wishlistItems.length === 0) {
            return;
        }

        const isPDP = Boolean(wishlistMethods.properties.isProductDetailsPage);

        wishlistItems.forEach(({ stit_ID: productId, groupId }) => {
            const selector = `.wishlist-${productId}-${groupId}`;
            const $btn = $(selector);

            if (!$btn.length) {
                // element not on this page
                return;
            }

            // Always mark it active
            $btn.addClass('active');

            // On PDP, also update the tooltip/title
            if (isPDP) {
                $btn.attr('title', 'Remove from wishlist');
            }
        });
    };



    wishlistPrivateMethods.deleteFromWishlist = function (productId, groupId, isRequestFromHome = false) {
        const details = { product_id: productId, group_id: groupId };
        const baseSelector = `.wishlist-${productId}-${groupId}`;

        // cleanup both in success & error
        function cleanupProcessing() {
            const inst = wishlistPrivateMethods.properties.currentInstance;
            if (inst) {
                $(inst).removeClass('processing');
                wishlistPrivateMethods.properties.currentInstance = null;
            }
        }

        function onSuccess(data) {
            cleanupProcessing();

            if (data.status !== 'ok') {
                console.warn('Wishlist delete failed:', data);
                return;
            }

            // Always remove the “active” state
            $(baseSelector).removeClass('active');

            if (wishlistMethods.properties.isProductDetailsPage) {
                // On PDP, change the tooltip back
                requestAnimationFrame(() => {
                    $(baseSelector).attr('title', 'Add to wishlist');
                });

            } else if (window.location.pathname === '/wishlist') {
                // On the wishlist page, just reload
                window.location.reload(true);

            } else {
                // Anywhere else (e.g. home), remove the tile and clear active class
                $(`#wishlist-tile-${productId}`).remove();
            }
        }

        function onError(err) {
            console.error('Wishlist delete error:', err);
            cleanupProcessing();
        }

        // Fire it off
        master.ajax.JSONRequest(
            wishlistMethods.url.delete,
            'POST',
            details,
            onSuccess,
            onError
        );
    };


    // Helper: show login modal and focus
    function promptLogin() {
        $('#login-modal').modal('show');
        setTimeout(() => $('#txt-mobile').focus(), 500);
    }

    // Helper: update a wishlist button’s state
    function updateWishlistButton($btn, { active, title }) {
        if (active) {
            $btn.addClass('active');
        } else {
            $btn.removeClass('active');
        }
        if (title) {
            $btn.attr('title', title);
        }
    }

    wishlistPrivateMethods.addToWishList = function ($btn, source = 1) {
        const productId = $btn.data('productid');
        const groupId = $btn.data('groupid');
        const branchId = $btn.data('branchid');
        const branchTypeId = $btn.data('branchtypeid');
        const masterId = $btn.data('id');
        const isAuth = String($btn.data('isauthenticated')).toLowerCase() === 'true';
        const selector = `.wishlist-${productId}-${groupId}`;

        // 1. Authentication guard
        if (!isAuth) {
            return promptLogin();
        }

        // 2. Prepare payload
        const payload = {
            product_id: productId,
            group_id: groupId,
            branch_id: branchId,
            branch_type_id: branchTypeId,
            source
        };

        // 3. AJAX success handler
        const onSuccess = data => {
            if (data.status !== 'ok') {
                console.warn('Add to wishlist failed', data);
                return;
            }

            // 3a. If we're on the cart page, remove from cart
            if (cart.properties.isCartPage) {
                cart.deleteItem(masterId);
            }

            // 3b. Determine UI update
            if (wishlistMethods.properties.isProductDetailsPage) {
                updateWishlistButton($(selector), {
                    active: true,
                    title: 'Remove from wishlist'
                });
            } else {
                // Home/catalog context
                updateWishlistButton($btn, { active: true });
                $btn.closest('.form-qty-block').addClass('disabled');
                $(`.notify-bell-${productId}-${groupId}`).addClass('hide');
                $(`.notify-check-${productId}-${groupId}`).removeClass('hide');
            }
        };

        // 4. AJAX error handler
        const onError = err => {
            console.error('Error adding to wishlist', err);
        };

        // 5. Fire the request
        master.ajax.JSONRequest(
            wishlistMethods.url.add,
            'POST',
            payload,
            onSuccess,
            onError
        );
    };


    wishlistPrivateMethods.toggleWishlistItem = function ($btn) {
        const productId = $btn.data('productid');
        const groupId = $btn.data('groupid');
        const source = $btn.data('source') || 1;
        const isActive = $btn.hasClass('active');
        const isPDP = Boolean(wishlistMethods.properties.isProductDetailsPage);

        if (isActive) {
            // Remove from wishlist
            wishlistPrivateMethods.deleteFromWishlist(
                productId,
                groupId,
      /* isRequestFromHome */ !isPDP
            );
        } else {
            // Add to wishlist
            wishlistPrivateMethods.addToWishList($btn, source);
        }
    };

    wishlistPrivateMethods.moveFromCart = function (self) {
        if (!confirm('Are you sure you want to move this item to wishlist?'))
            return false;
        wishlistPrivateMethods.addToWishList(self,"1");
    }

    wishlistPrivateMethods.addToCart = function (self) {
        $(self).addClass("processing");
        wishlistPrivateMethods.properties.currentInstance = self;
        var details = {
            cart_product_id: self.data('productid'),
            cart_group_id: self.data('groupid'),
            cart_order_qty: 1,
            cart_branch_id: self.data('branchid'),
            branch_type_id: self.data('branchtypeid')
        };
        cart.addToCart(details, wishlistPrivateMethods.deleteFromWishlist);
    }


    wishlistPrivateMethods.events.initialize = function () {
        var controls = wishlistPrivateMethods.controls;

        $(controls.navigator).unbind('click').on('click', function () {
            window.location.href = wishlistMethods.url.get;
        });

        $(controls.homeWishlistIconBlock).unbind('click').on('click', function () {
            wishlistPrivateMethods.toggleWishlistItem($(this));
        });

        $(controls.delete).unbind('click').on('click', function () {
            var productId = $(this).data('productid');
            var groupId = $(this).data('groupid');
            wishlistPrivateMethods.deleteFromWishlist(productId, groupId, false);
        });

        $(controls.moveFromCart).unbind('click').on('click', function () {
            wishlistPrivateMethods.moveFromCart($(this));
        });
        $(controls.addToCart).unbind('click').on('click', function () {
            wishlistPrivateMethods.addToCart($(this));
        });
    };

    wishlistMethods.initializePage = function () {
        wishlistPrivateMethods.events.initialize();
    };

    return wishlistMethods;
}();
$(function () {
    wishlist.initializePage();
});