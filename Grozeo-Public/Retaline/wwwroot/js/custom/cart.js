var cart = function () {
    var cartMethods = {}, cartPrivateMethods = {};

    cartPrivateMethods.events = {};

    cartMethods.properties = {
        isCartPage: false,
        multiCourier: false,
        cartItems: { 'items': [] },
        paymentGatewayType: 1, // 1: popup, 2: inline
        currencySymbol: '',
        notesLength: 200
    };
    cartPrivateMethods.properties = {
        isOtpTriggered: false,
        template: ''

    };
    cartMethods.url = {
        addToCart: '/Cart/AddToCart',
        updateCart: '',
        deleteCart: '/Cart/DeleteCartItem',
        clearCart: '/Cart/ClearCart',
        cartCount: '/cartcount',
        updateQuantity: '/Cart/UpdateCart',
        cartSummary: '/Cart/CartSummary',
        addCoupon: '/Checkout/SubmitCoupon',
        removeCoupon:'/Checkout/RemoveCoupon',
        useWallet: '/Checkout/UseWallet',
        changeDelivery: '/cart/replaceitem',
        setSlot: '/checkout/setslot',
        removeOrderGroup: '/checkout/removeorderGroup',
        addOrdernote: '/checkout/addOrderNote'
    };

    cartPrivateMethods.controls = {
        deleteProduct: ".btn-cart-remove",
        cartcount: ".cartcount",
        addProduct: '.btn-add-product-cart',
        cartContainer: '#cart-container',
        qtyminus: '.minus',
        qtyplus: '.plus',
        clear: '#btnemptycart',
        applycoupon: '.btnapplycoupon',
        groupButton: '.btn-group',
        headerCartSection: '.minicart_menu',
        deleteOutOfStock: '.btn-cart-out-of-stock-remove',
        courierLabelBlock: '#courier-title-block',
        outOfSockTile: '.out-of-stock-tile',
        cartHeaderTemplate: 'cart-details-model',
        walletHolder: '.wallet-holder',
        walletAmountUsed: '#wallet-amount-used',
        removeOrder: '.remove_order'
    };

    cartMethods.addToCart = function (details, callback) {
        cartPrivateMethods.addToCart(details, callback)
    };

    cartPrivateMethods.cartCount = function (minify) {
        onSuccess = function (cart) {
            $(cartPrivateMethods.controls.headerCartSection).find('.cartcount').html(cart.totalItems);

            // Adding and updating count for floating cart view
                if (cart.totalItems > 0) {
                    var itemLabel = cart.totalItems > 1 ? "Items" : "Item";
                    //$('.header-action-list').addClass('hide');
                    $('.cart-notification-sticky').removeClass('hide');
                    $('#cart-notification-sticky-btn').html(`<span class="itemscount">${cart.totalItems} ${itemLabel}</span>
            <span>in cart</span>`)
                    if (cart?.cartItems[0]?.productImage && cart?.cartItems[0]?.productImage !== "")
                        $('#cart-notification-sticky-img').attr('src', cart.cartItems[0].productImage)
                    // cart.totalItems + itmes in cart
                } else {
                    $('.header-action-list').removeClass('hide');
                    $('.cart-notification-sticky').addClass('hide');
                }

            if (minify === 0) {
                cartPrivateMethods.properties.template = cartPrivateMethods.properties.template === '' ? document.getElementById(cartPrivateMethods.controls.cartHeaderTemplate).innerHTML : cartPrivateMethods.properties.template;
                var template = Handlebars.compile(cartPrivateMethods.properties.template);
                var compiledTemplate = template(cart);
                $(cartPrivateMethods.controls.headerCartSection).find('.Carttoggelwrap').html(compiledTemplate);

            }
            cartPrivateMethods.events.initialize();
        };

        onError = function (data) {

        };
        master.ajax.JSONRequest(cartMethods.url.cartCount + (minify === 0 ? '/0' : ''), 'GET', {}, onSuccess, onError);

    };

    cartPrivateMethods.registerHandlebarHelpers = function (data) {

        Handlebars.registerHelper("showRemainingItems", function (remainingItems, options) {
            if (remainingItems == 0) {
                return options.inverse(this);
            } else {
                return options.fn(this);
            }
        });

        Handlebars.registerHelper("isThereAnyItemsInCart", function (cartCount, options) {
            if (cartCount == 0) {
                return options.inverse(this);
            } else {
                return options.fn(this);
            }
        });
    }

    cartMethods.updateCartCount = function (count) {
        var ccount = '';
        if (count >= 0) {
            ccount = '' + count;
        };
        $(cartPrivateMethods.controls.cartcount).html(ccount);

    };

    cartMethods.updateCartSummary = function () {
        onSuccess = function (data) {
            if (data) {
                // data.priceLabels[0].label
                if (data.cart.length < 1 && data.priceLabels.length < 1) {
                    window.location.reload(true);
                    return;
                }
                else if (data.priceLabels) {
                    $.each(data.priceLabels, function (i, val) {
                        $('ul.order-summery_list li').each(function () {
                            if ($(this).find('div').eq(0).html() == val.label)
                                $(this).find('div').eq(1).html(val.value);

                        });
                        if ($('div.orderSummoryTotal') && val.label == 'Total')
                            $('div.orderSummoryTotal').find('div').eq(1).html(val.value);
                    });
                }
                // 
                if (data.cart) {
                    $.each(data.cart, function (i, val) {
                        var objitemprice = $('#cartitemsellingprice_' + val.id);
                        if (objitemprice) {
                            var totalitemprice = val.cart_sales_price * val.cart_order_qty;
                            var pricehtml = cart.properties.currencySymbol + ' ' + val.cart_sales_price + ' x ' + val.cart_order_qty + ' = ' + cart.properties.currencySymbol + ' ' + totalitemprice.toFixed(2) + '';
                            $(objitemprice).find('span.product-price_discount').html(pricehtml);
                        }
                    });
                }
            }
        };

        onError = function (data) {
            //console.log(data);
        };
        master.ajax.JSONRequest(cartMethods.url.cartSummary, 'GET', {}, onSuccess, onError);
    };

    cartPrivateMethods.changeDelivery = function (self) {
        var details = {
            cart_product_id: self.data('productid'),
            cart_group_id: self.data('groupid'),
            cart_order_qty: parseInt($("#product-qty-" + self.data('productid') + "-" + self.data('groupid')).val()),
            cart_branch_id: self.data('brid'),
            branch_type_id: self.data('brtypeid'),
            cart_id: self.data('id')
        };

        onSuccess = function (result) {
            window.location.reload(true);
        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(cartMethods.url.changeDelivery, 'POST', details, onSuccess, onError);

    }

    cartPrivateMethods.prepareModelAndAddToCart = function ($btn) {
        // 1. UI feedback
        $btn.addClass('processing');
        const $item = $btn.closest('.catitems');
        $item.find('.right-column').children().hide();
        $item.find('.left-column').children().show();
        $item.find('.tocart').hide();
        $item.find('.incart').show();

        // 2. Resolve quantity
        const productId = $btn.data('productid');
        const groupId = $btn.data('groupid');
        let $qtyInput = $btn.closest('.form-qty-block').find('.form-qty');
        if (!$qtyInput.length) {
            $qtyInput = $(`#product-qty-${productId}-${groupId}`);
        }
        let qty = parseInt($qtyInput.val(), 10) || 1;
        $qtyInput.val(qty);

        // 3. Build details and delegate to addToCart
        const details = {
            cart_product_id: productId,
            cart_group_id: groupId,
            cart_order_qty: qty,
            cart_branch_id: $btn.data('brid'),
            branch_type_id: $btn.data('brtypeid')
        };

        cartPrivateMethods.addToCart(details);
    };


    cartPrivateMethods.addToCart = function (details, callback) {
        // Success handler
        const onSuccess = result => {
            // Always clear any “processing” state
            $('.processing').removeClass('processing');

            // If result is invalid or indicates failure, log and stop
            if (!result || result === -1) {
                console.warn('Add to cart failed or returned invalid result:', result);
                return;
            }

            // 1. Update internal cart model
            cartMethods.properties.cartItems.items.push({
                id: result.itemid,
                groupId: result.grp,
                quantity: 1
            });

            // 2. Ensure quantity >= 1
            const item = cartMethods.properties.cartItems.items
                .find(i => i.id === result.itemid && i.groupId === result.grp);
            if (item && item.quantity <= 0) {
                item.quantity = 1;
            }

            // 3. Refresh UI
            cartMethods.updateCartButton();
            cartPrivateMethods.cartCount();
            if (cartMethods.properties.isCartPage) {
                cartMethods.updateCartSummary();
            }

            // 4. Invoke optional callback (e.g., remove from wishlist)
            if (typeof callback === 'function') {
                callback(details.cart_product_id, details.cart_group_id, false);
            }

            // 5. Show mini-cart feedback
            cartPrivateMethods.events.showCart();
            setTimeout(cartPrivateMethods.events.hideCart, 3000);
        };

        // Error handler
        const onError = err => {
            console.error('Error adding to cart:', err);
            // Cleanup and UI rollback
            $('.processing').removeClass('processing');
            cartMethods.updateCartButton();
        };

        // Fire the AJAX request
        master.ajax.JSONRequest(
            cartMethods.url.addToCart,
            'POST',
            details,
            onSuccess,
            onError
        );
    };


    cartPrivateMethods.updateQuantity = function ($btn) {
        // 1. Mark processing state
        $btn.addClass('processing');

        // 2. Resolve IDs and quantity input
        const productId = $btn.data('productid');
        const groupId = $btn.data('groupid');
        let $input = $btn.closest('.form-qty-block').find('.form-qty');
        if (!$input.length) {
            $input = $(`#product-qty-${productId}-${groupId}`);
        }

        // 3. Parse and clamp quantity
        let qty = parseInt($input.val(), 10);
        if (isNaN(qty) || qty < 0) {
            qty = 0;
        }
        const max = parseInt($input.attr('max'), 10) || Infinity;

        // 4. Disable/enable controls based on max
        if (qty >= max) {
            if ($btn.hasClass('plus')) $btn.prop('disabled', true);
            $('#pr_limit_reached_err').show()//$btn.siblings('.Error').show();
        } else {
            $btn.prop('disabled', false)
                .siblings('.plus').prop('disabled', false)
            //.siblings('.Error').hide();
            $('#pr_limit_reached_err').hide()
        }

        // 5. Reflect clamped value back to input(s)
        const displayQty = Math.max(qty, 1);
        $input.val(displayQty);
        $(`.product-tile-qty-${productId}-${groupId}`).val(displayQty);

        // 6. Prepare payload
        const details = {
            cart_product_id: productId,
            cart_order_qty: qty
        };

        // 7. Success & error handlers
        const onSuccess = result => {
            $btn.removeClass('processing');

            if (!result) {
                console.warn('Quantity update failed');
                return;
            }

            // If qty zero, remove item
            if (qty <= 0) {
                $btn.closest('div.product-card.cartItem').remove();

                //fixing an issue that caused some UI issues while removing and adding products randomly in product card
            cartMethods.properties.cartItems.items = cartMethods.properties.cartItems.items.filter(function (item) {
                return item.id !== result.itemid;
            });
            }

            // Update counts and UI
            cartPrivateMethods.cartCount();
            cartMethods.updateCartCount(result.cartCount);
            if (cartMethods.properties.isCartPage) {
                cartMethods.updateCartSummary();
            }
        };

        const onError = err => {
            $btn.removeClass('processing');
            console.error('Error updating quantity:', err);
        };

        // 8. Fire AJAX
        master.ajax.JSONRequest(
            cartMethods.url.updateQuantity,
            'POST',
            details,
            onSuccess,
            onError
        );
    };


    cartPrivateMethods.updateCart = function () {

        var mobile = $(cartPrivateMethods.controls.mobile).val();
        onSuccess = function (data) {
            cartPrivateMethods.cartCount();
            if (cartMethods.properties.isCartPage)
                cartMethods.updateCartSummary();

        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(cartMethods.url.updateCart, 'POST', { Mobile: mobile }, onSuccess, onError);

    };
    cartPrivateMethods.deleteOrder = function (orderId) {
        var details = { OrderId: orderId }
        onSuccess = function (data) {
            $('.order-' + orderId).remove();
            if ($('.cartcheckoutlisting li').length <= 0) {
                window.location.href = '/';
                $('#btncontinuePay').addClass('disabled');
                return
            }
        };

        onError = function (data) {
            console.log(data);
        };

        master.ajax.JSONRequest(cartMethods.url.removeOrderGroup, 'POST', details, onSuccess, onError);
    }
    cartPrivateMethods.deleteCart = function (productId, isOutOfStock = false) {
        const details = { cart_product_id: productId };

        // Success handler
        const onSuccess = response => {
            if (response?.status !== 'ok') {
                console.warn('deleteCart did not succeed:', response);
                return;
            }

            // 1. Remove the table row
            const removedId = response.data?.product_id || productId;
            $(`#tr-product-${removedId}`).remove();

            // 2. Update counts and summary
            cartPrivateMethods.cartCount();
            if (cartMethods.properties.isCartPage) {
                cartMethods.updateCartSummary();
            }

            // 3. Handle out-of-stock UI
            if (isOutOfStock) {
                $(`.out-of-stock-block-${productId}`).remove();

                // If no more out-of-stock tiles, reload to re-render
                if (!$(`${cartPrivateMethods.controls.outOfStockTile}`).length) {
                    return window.location.reload(true);
                }
            }

            // 4. Conditionally hide empty sections
            const sections = [
                { selector: '.courier-items li', wrap: '.courier_Delivery_wrap' },
                { selector: '.quik-items li', wrap: '.Quick_Delivery_wrap' },
                { selector: '.multi-listing-items li', wrap: '.multi-listing-items-wrap' }
            ];

            sections.forEach(({ selector, wrap }) => {
                if (!$(selector).length) {
                    $(wrap).addClass('hide');
                }
            });
            //hide the Remove unavailable buttons and show the review to checkout button if the last
            //unavailable item is removed manually
            if ($('.cart-listing > .product_outofstock').length === 0) {
                $('.checkout_btn').removeClass('hide')//this should always be first
                $('.auto_remove_items').addClass('hide')//this should always be second as both buttons has checkout_btn class in it
            }
          
        };

        // Error handler
        const onError = err => {
            console.error('Error deleting cart item:', err);
        };

        // Fire the AJAX request
        master.ajax.JSONRequest(
            cartMethods.url.deleteCart,
            'POST',
            details,
            onSuccess,
            onError
        );
    };


    cartPrivateMethods.clearCart = function () {
        // 1. Confirm action
        if (!window.confirm('Are you sure you want to clear all items in your cart?')) {
            return;
        }

        // 2. Success & error handlers, scoped as constants
        const onSuccess = response => {
            // Update counts & UI
            cartPrivateMethods.cartCount();
            if (cartMethods.properties.isCartPage) {
                cartMethods.updateCartSummary();
            }
            // Reload to show empty cart
            window.location.reload(true);
        };

        const onError = error => {
            console.error('Failed to clear cart:', error);
            // Optional: show user-facing error message here
        };

        // 3. Fire AJAX request
        master.ajax.JSONRequest(
            cartMethods.url.clearCart,
            'POST',
            {},
            onSuccess,
            onError
        );
    };

    //cartPrivateMethods.applyCoupon = function ($btn) {
    //    // 1. Visual feedback
    //    $btn.addClass('processing');

    //    // 2. Determine action & source input
    //    const isInputClick = $btn.is('input');
    //    const currentText = $btn.text().trim();
    //    //const isRemoving = !isInputClick && currentText === 'Remove';
    //    const rawCouponInput = isInputClick
    //        ? $('#CouponCode')
    //        : $('#txtCoupon');
    //    const couponCode = rawCouponInput.val().trim();

    //    // 3. Validation
    //    if (!isInputClick && !couponCode) {
    //        alert('Invalid coupon code. Please enter a valid coupon.');
    //        $btn.removeClass('processing');
    //        return;
    //    }
    //    // Mirror code between inputs
    //    if (!isInputClick) {
    //        $('#CouponCode').val(couponCode);
    //    } else {
    //        $('#txtCoupon').val(couponCode);
    //    }

    //    // 4. Prepare payload
    //    const useWallet = $('#frmSubmitCheckout')
    //        .find('.walletrow input:checked')
    //        .length > 0;
    //    const details = {
    //        OrderId: $('#OrderId').val(),
    //        CouponCode: couponCode,
    //        UseWallet: useWallet
    //    };

    //    // 5. Handlers
    //    const onSuccess = response => {
    //        const labels = response?.data?.labels;
    //        if (Array.isArray(labels) && labels.length) {
    //            // Clear old summary
    //            $('.order-summery_wrap ul').empty();

    //            let netPayment = 0;
    //            let totalDiscount = 0;

    //            labels.forEach(({ label, value }) => {
    //                const formatted = isNaN(value) ? value : Number(value).toFixed(2);

    //                if (label === 'Net Amount Payable') {
    //                    netPayment = formatted;
    //                    $('.grand-total').html(
    //                        ` <div class="order-summery_label">Grand Total</div><div class="order-summery_value">${formatted}</div>`
    //                    );
    //                } else {
    //                    $('.order-summery_wrap ul').prepend(
    //                        ` <li class="cost-prod"> <div class="order-summery_label">${label}</div>` +
    //                        ` <div class="order-summery_value">${formatted}</div></li>`
    //                    );
    //                }
    //                if (label === 'Discount') totalDiscount = formatted;
    //            });

    //            $('#frmSubmitCheckout input#NetAmount').val(netPayment);
    //            $('.paymentoptionsrow').attr('total', netPayment);

    //            if (!isInputClick) {
    //                const $couponRow = $('#frmSubmitCheckout .have-acoupon-wrap');
    //                $couponRow.find('label').text(
    //                    isRemoving ? 'Have Coupon? ' : 'Coupon applied'
    //                );
    //                $couponRow.find('#txtCoupon')
    //                    .prop('disabled', !isRemoving);
    //                $btn.text(isRemoving ? 'Apply' : 'Remove');

    //                rawCouponInput.val(isRemoving ? '' : couponCode);
    //                master.showResult(
    //                    `Coupon ${isRemoving ? 'removed' : 'applied'} successfully!` +
    //                    (totalDiscount > 0
    //                        ? `<br/>Total Discount: ${master.properties.currency} ${totalDiscount}`
    //                        : ''),
    //                    `Coupon ${isRemoving ? 'Removed' : 'Applied'}`,
    //                    true
    //                );
    //            }
    //        } else {
    //            const errMsg = response?.result?.error?.msg || 'An error occurred.';
    //            master.showResult(errMsg, 'Failure', false);
    //        }

    //        // 6. Cleanup
    //        $btn.removeClass('processing');
    //    };

    //    const onError = err => {
    //        console.error('applyCoupon error', err);
    //        $btn.removeClass('processing');
    //    };

    //    // 7. Send request
    //    master.ajax.JSONRequest(
    //        cartMethods.url.addCoupon,
    //        'POST',
    //        details,
    //        onSuccess,
    //        onError
    //    );
    //};


    cartPrivateMethods.applyCoupon = function ($btn) {
        $btn.addClass('processing');

        const isInputClick = $btn.is('input');
        const rawCouponInput = isInputClick ? $('#CouponCode') : $('#txtCoupon');
        const couponCode = rawCouponInput.val().trim();

        if (!couponCode && !isInputClick) {
            alert('Invalid coupon code. Please enter a valid coupon.');
            $btn.removeClass('processing');
            return;
        }

        $('#CouponCode, #txtCoupon').val(couponCode); // Mirror inputs

        const useWallet = $('#frmSubmitCheckout').find('.walletrow input:checked').length > 0;
        const details = {
            OrderId: $('#OrderId').val(),
            CouponCode: couponCode,
            UseWallet: useWallet
        };

        const onSuccess = response => {
            const labels = response?.data?.labels;
            if (Array.isArray(labels) && labels.length) {
                $('.order-summery_wrap ul').empty();
                let netPayment = 0, totalDiscount = 0;

                labels.forEach(({ label, value }) => {
                    if (value) {
                        const formatted = isNaN(value) ? value : Number(value).toFixed(2);
                        if (label === 'Net Amount Payable') {
                            netPayment = formatted;
                            $('.grand-total').html(
                                `<div class="order-summery_label">Grand Total</div><div class="order-summery_value">${formatted}</div>`
                            );
                            $('.contunuPay_btn ').html(`Pay Now ${formatted}`)
                        } else {
                            $('.order-summery_wrap ul').prepend(
                                `<li class="cost-prod"><div class="order-summery_label">${label}</div>` +
                                `<div class="order-summery_value">${formatted}</div></li>`
                            );
                        }
                        if (label === 'Discount') totalDiscount = formatted;
                    }
                 
                });

                $('#frmSubmitCheckout input#NetAmount').val(netPayment);
                $('.paymentoptionsrow').attr('total', netPayment);
               var attr = netPayment.replace(/[^0-9.]/g, '')
                    
                if (attr == "0") {
                    $('#btncontinuePay').removeClass("hide")
                    $('.contunuPay_btn').addClass("hide")
                }
                else {
                    $('#btncontinuePay').addClass("hide")
                    $('.contunuPay_btn').removeClass("hide") 

                }
                

                if (!isInputClick) {
                    $('#frmSubmitCheckout .have-acoupon-wrap label').text('Coupon applied');
                    $('#txtCoupon').prop('disabled', true);
                    $btn.text('Remove');

                    master.showResult(
                        `Coupon applied successfully!` +
                        (totalDiscount > 0
                            ? `<br/>Total Discount: ${master.properties.currency} ${totalDiscount}`
                            : ''),
                        'Coupon Applied',
                        true
                    );
                }
            } else {
                const errMsg = response?.result?.error?.msg || 'An error occurred.';
                master.showResult(errMsg, 'Failure', false);
                if ($('#chkcheckoutusewallet').is(':checked')) {
                    $('#chkcheckoutusewallet').prop('checked', false);
                }
            }

            $btn.removeClass('processing');
        };

        const onError = err => {
            console.error('applyCoupon error', err);
            $btn.removeClass('processing');
            if ($('#chkcheckoutusewallet').is(':checked')) {
                $('#chkcheckoutusewallet').prop('checked', false);
            }
        };

        master.ajax.JSONRequest(
            cartMethods.url.addCoupon,
            'POST',
            details,
            onSuccess,
            onError
        );
    };

    cartPrivateMethods.removeCoupon = function ($btn) {
        $btn.addClass('processing');
        const rawCouponInput =  $('#txtCoupon');
        const couponCode = rawCouponInput.val().trim();
        const details = {
            OrderId: $('#OrderId').val(),
            CouponCode: couponCode,
            UseWallet: $('#frmSubmitCheckout').find('.walletrow input:checked').length > 0
        };

        const onSuccess = response => {
            const labels = response?.data?.labels;

            $('.order-summery_wrap ul').empty();
            $('#frmSubmitCheckout input#NetAmount').val('');
            $('.paymentoptionsrow').removeAttr('total');
            $('.grand-total').empty();
            labels.forEach(({ label, value }) => {
                const formatted = isNaN(value) ? value : Number(value).toFixed(2);
                if (label === 'Net Amount Payable') {
                    netPayment = formatted;
                    $('.grand-total').html(
                        `<div class="order-summery_label">Grand Total</div><div class="order-summery_value">${formatted}</div>`
                    );
                } else {
                    $('.order-summery_wrap ul').prepend(
                        `<li class="cost-prod"><div class="order-summery_label">${label}</div>` +
                        `<div class="order-summery_value">${formatted}</div></li>`
                    );
                }
                if (label === 'Discount') totalDiscount = formatted;
            });
            $('#txtCoupon, #CouponCode').val('').prop('disabled', false);
            $('#frmSubmitCheckout .have-acoupon-wrap label').text('Have Coupon?');
            $btn.text('Apply');

            master.showResult('Coupon removed successfully!', 'Coupon Removed', true);
            $btn.removeClass('processing');
        };

        const onError = err => {
            console.error('removeCoupon error', err);
            $btn.removeClass('processing');

        };

        master.ajax.JSONRequest(
            cartMethods.url.removeCoupon,
            'POST',
            details,
            onSuccess,
            onError
        );
    };

    cartPrivateMethods.validateCartSubmit = function (obj, e) {
        if ($('.cart-listing').find('.outofstock').length > 0) {
            master.showResult("Your cart contains out of stock items. Please remove these items from cart to proceed.", "Failure!", false);
            e.preventDefault();
            $(this).find('button[type=submit]').prop('disabled', false);
            return false;
        }
        if ($('#prescriptionupload')) {
            if ($('#prescriptionupload').val() == '') {
                e.preventDefault();
                alert('Please upload prescription');
                return false;
            }
        }
        return true;
    };
    cartPrivateMethods.loadPayment = function (obj, e) {
        var attr = ''; // $('.paymentoptionsrow').attr('total').replace(/[^0-9.]/g, '');
        $('#btncontinuePay').addClass('processing')
        if ($('.paymentoptionsrow').attr('total'))
            attr = $('.paymentoptionsrow').attr('total').replace(/[^0-9.]/g, '');

        if (typeof attr !== 'undefined' && attr !== false && attr === "0") {
            //e.preventDefault();
            $('#frmSubmitCheckout').submit();
            return false;
        }

        $('#dvpaymentmodes').show(); $('#dvcheckoutinfo').hide();
        $('.titlewrap title_group').html('<h2>Payment</h2>')

        $('#payment-progress').addClass('odr_processing')

        $('.have-acoupon-wrap').hide();
        $('.couponrow').hide();
        $('.haveAcouponWrap').hide();
        $('#btncontinuePay').hide();
        if ($("#payment-request").length > 0) {
            var checkoutvals = cartPrivateMethods.getCheckoutVal($('#frmSubmitCheckout'));
            master.getPaymentPage(checkoutvals, 0, true);
        }

        return false;
    }

    cartPrivateMethods.loadOnlinePayment = function (obj, e) {
        if ($('#frmSubmitCheckout').attr('cancheckout') != 'True') {
            e.preventDefault();
            $(".submitonlinepaymentWrap").removeClass("processing");
            alert('Sorry, we are not started accepting orders. Please check back soon.');
            return false;
        }
        if (e)
            e.preventDefault();
        var checkoutvals = cartPrivateMethods.getCheckoutVal($('#frmSubmitCheckout'));
        $('#frmSubmitCheckout').addClass('processing') 
        master.getPaymentPage(checkoutvals);
        return false;

    }
    cartPrivateMethods.continuePOD = function (obj, e) {
        if ($('#frmSubmitCheckout').attr('cancheckout') != 'True') {
            e.preventDefault();
            $(".submitonlinepaymentWrap").removeClass("processing");
            alert('Sorry, we are not started accepting orders. Please check back soon.');
            return false;
        }
        $('#rdPaymentMethod_0').prop('checked', true)
        return true;

    }
    cartPrivateMethods.getCheckoutVal = function (obj) {
        var Checkout = {
            PaymentMethod: 2,//$(frm).find('#PaymentMethod').val(),
            TimeSlote: $('#TimeSlote').val(),
            OrderId: $('#OrderId').val(),
            CustomerId: $('#CustomerId').val(),
            OrderNum: $('#OrderNum').val(),
            NetAmount: $('#NetAmount').val(),
            UseWallet: $('#chkcheckoutusewallet').is(":checked"),
            CouponCode: $('#CouponCode').val(),
            OrderGroupId: $('#OrderGroupId').val()
        };
        return Checkout;
    }

    cartPrivateMethods.validateCheckoutSubmit = function (obj, e) {
        if ($(obj).attr('cancheckout') != 'True') {
            e.preventDefault();
            alert('Sorry, we are not started accepting orders. Please check back soon.');
            return false;
        }

        //if ($(obj).find('.paymentoptionsrow').is(":visible")) {
        if ($("#rdPaymentMethod_0").is(":checked")) {
            return confirm("You have selected to Pay On Delivery. Are you sure to proceed?");
        } // 
        else if ($("#rdPaymentMethod_1").is(":checked")) {
            //$('#frmSubmitCheckout').attr("action", "/checkout")
            e.preventDefault();
            var checkoutvals = cartPrivateMethods.getCheckoutVal(this);
            master.getPaymentPage(checkoutvals);
            return false;
        }
        else if ($('.paymentoptionsrow').attr('total') && $('.paymentoptionsrow').attr('total').replace(/[^0-9.]/g, '') === "0") {
            return true;
        }
        else {
            e.preventDefault();
            //alert("Please select a Payment Method to proceed");
            return false;
        }
        //}
        //else {
        //    $('#frmSubmitCheckout').attr("action", "/Checkout/SubmitOrder");
        //    return true;
        //}

    };

    cartMethods.updateCartButton = function () {
        if (!master.properties.hasLoadedCartAndWishlist && !master.properties.isLoadingCartAndWishlist) {
            setTimeout(cartMethods.updateCartButton, 15000);
            return;
        }
        $.each(cartMethods.properties.cartItems.items, function (index, item) {
            $(".addunitWrap-" + item.id + "-" + item.groupId).addClass('form-qty-full')
            $(".product-tile-qty-" + item.id + "-" + item.groupId).val(item.quantity);
            $(".product-tile-plus-" + item.id + "-" + item.groupId).removeClass('btn-add').html('+');
            $(".product-tile-minus-" + item.id + "-" + item.groupId).removeClass('hide');
            $(".product-tile-qty-" + item.id + "-" + item.groupId).removeClass('hide');
        });
    };

    cartPrivateMethods.events.onAdd = function (self) {
        if (self.hasClass('open-login-modal')) { //check if the user is logged in or not.

            $("#login-modal").modal("show");
            setTimeout(function () {
                $("#txt-mobile").focus();
            }, 500);
            return; //If not logged in break the execution and show login modal.

        }
        //If logged in, Continue execution.

        // Get current quantity values
        var qtyblock = self.closest('.form-qty-block');
        var qty = qtyblock.find('.form-qty');
        var val = parseInt(qty.val()) || 0;
        var max = parseInt(qty.attr('max')) || 0;
        var min = parseInt(qty.attr('min')) || 0;
        var step = parseInt(qty.attr('step')) || 0;

        var minusbtn = qtyblock.find('.btn-minus');
        var plusbtn = qtyblock.find('.btn-plus');
        var formqty = qtyblock.find('.form-qty');

        var cart_product_id = self.data('productid');
        var cart_group_id = self.data('groupid');

        // Change the value if plus or minus
        if (self.is('.btn-plus')) {
            if (max && (max <= val)) {
                qty.val(max);
            } else {
                qty.val(val + step);
            }
            if (minusbtn.is(':hidden')) {
                $('input.product-tile-qty-' + cart_product_id + '-' + cart_group_id).val(min + step);
                $('.product-tile-plus-' + cart_product_id + '-' + cart_group_id).removeClass('btn-add').html('+');
                $('.product-tile-minus-' + cart_product_id + '-' + cart_group_id).removeClass('hide');
                $('input.product-tile-qty-' + cart_product_id + '-' + cart_group_id).removeClass('hide');
                $("div.form-qty-block").find("[data-productid='" + cart_product_id + "']").addClass('form-qty-full');

                //qty.val(min + step);
                cartPrivateMethods.prepareModelAndAddToCart(self);
                //plusbtn.removeClass('btn-add').html('+');
                //minusbtn.removeClass('hide');
                //formqty.removeClass('hide');
                //qtyblock.addClass('form-qty-full');
            } else {
                cartPrivateMethods.updateQuantity(self, true);
                cartMethods.properties.cartItems.items.forEach(item => {
                    if (item.id === cart_product_id && item.groupId === cart_group_id) {
                        item.quantity += 1; // Increment quantity
                    }
                });
            }
        } else {
            if (val == (min + 1)) {
                $("div.form-qty-block").find("[data-productid='" + cart_product_id + "']").removeClass('form-qty-full');
                $('.product-tile-plus-' + cart_product_id + '-' + cart_group_id).addClass('btn-add').html('<span>Add</span>+');
                $('.product-tile-minus-' + cart_product_id + '-' + cart_group_id).addClass('hide');
                $('input.product-tile-qty-' + cart_product_id + '-' + cart_group_id).addClass('hide');

                //qtyblock.removeClass('form-qty-full');
                //plusbtn.addClass('btn-add').html('<span>Add</span>+');
                //minusbtn.addClass('hide');
                //formqty.addClass('hide');
                qty.val(0);
            } else {
                qty.val(val - step);
                cartMethods.properties.cartItems.items.forEach(item => {
                    if (item.id === cart_product_id && item.groupId === cart_group_id) {
                        item.quantity -= 1; // decrement quantity
                    }
                });
            }
            cartPrivateMethods.updateQuantity(self, false);
        }
    };


    cartPrivateMethods.events.navigateToGroup = function (self) {
        var groupId = self.data('groupid');
        var groupName = self.data('groupname');
        window.location.href = '/groupitem/' + groupId + '/' + groupName;
    }

    cartMethods.deleteItem = function (productId, wishListCallBack) {
        cartPrivateMethods.deleteCart(productId, false, wishListCallBack);
    }

    cartPrivateMethods.events.showCart = function (self) {
        //Function shows the mini cart dropdown
        cartMethods.updateCartHtml();
        cartPrivateMethods.cartCount(0);
        //self.addClass('active');
        $('.Carttoggelwrap').slideDown();
        $('.Carttoggelwrap').addClass('active');
        $('#site-wrapper').addClass('Cart-visible');
        $('.TopMenuCategoriesTogel, .TopMenuCategories, .delivery_adress_togle, .delivery_adress_triger, .trigerprofile, .Profiletogle, .ShareTriger').removeClass('active');
        $(".delivery_adress_togle, .Profiletogle, .BookaSlotToggle, .Sharetogle .TopMenuCategories").hide();
        $('#site-wrapper').removeClass('menu-visible delivery_adress_visible profile-visible BookaSlot-visible');

    };

    cartPrivateMethods.events.hideCart = function (self) {
        $('.minicart').removeClass('open-minicart');
        $('.Carttoggelwrap').removeClass('active')
    };
    $('.close-cart').on("click", function (event) {
        cartPrivateMethods.events.hideCart()
    });
    cartMethods.updateCartHtml = function () {
        $(cartPrivateMethods.controls.headerCartSection).find('.Carttoggelwrap').html(`
        <div class="minicart-card">
			<div class="minicart-card_head">
				<h5>My cart</h5>
				<div class="close-minicart_icon">
					<a href="javascript:void(0)" class="close-minicart" onclick="CloseMiniCart()" role="button" aria-label="Close Cart">
						<i class="fa-regular fa-xmark" aria-hidden="true"></i>
					</a>
				</div>
			</div>
			<div class="minicart-card_body" style="overflow:hidden;">
				<div class="preloder">
                <span class="preloder_content">Loading...</span>
                <span class="loader_anim"></span>
                </div>
			</div>
		</div>
        `);
    }


    cartMethods.setTimeSlot = function (self, isDeselected) {
        var ordid = self.attr('ordid');
        var tslot = self.attr('tslot');
        var slotid = isDeselected ? -1 : self.attr('slotid');
        var slotdate = self.attr('slotdate');
        if (!isDeselected) {
            $('.BookaSlotTriger').html(tslot + "<i></i>");
            $('#delivery-label').removeClass("active");
            $('.BookaSlotTriger').css("background", "#fff2d9");

        }
        else {
            $('.BookaSlotTriger').html("Book a Slot <i></i>");
            $('#delivery-label').addClass("active");
            $('.BookaSlotTriger').css("background", "#ffffff");

        }

        var obj = self;
        var useWallet = $('#chkcheckoutusewallet').prop('checked') ? 1 : 0;
        var details = { OrderId: ordid, SlotId: slotid, SlotDate: slotdate, UseWallet: useWallet }
        onSuccess = function (data) {
            if (data && data.result.status == 'ok') {
                $('#TimeSlote').val(tslot); $(obj).parent('ul').find('li').removeClass('active'); $(obj).addClass('active');
                $(obj).closest('.BookaSlotToggle').hide();
                $('#site-wrapper').removeClass('BookaSlot-visible');
                //for (var i = 1; i < data.result.data.orders.length; i++) {
                $.each(data.result.data.orders, function (key, value) {
                    if (value.order_id == ordid) {
                        $(obj).closest('div.ItemdeliveryInfoWrap').find('ul.orderingitems_itemInfo li').each(function () {
                            if ($(this).find('span').length > 1 && $(this).children('span').eq(0).html() == 'Delivery Charge :')
                                $(this).children('span').eq(1).html(value.style.find(o => o.label == 'Delivery charge').value);
                            else if ($(this).find('span').length > 1 && $(this).children('span').eq(0).html() == 'Order Amount :')
                                $(this).children('span').eq(1).html(value.style.find(o => o.label == 'Total').value);
                        });
                    }
                });


                cartMethods.updateActiveSlots(slotid)
                $('.orderSummoryTableItems ul li').remove();
                var totaldiscount = 0;
                var netpayment = 0;
                $.each(data.result.data.summary.style, function (key, value) {
                    if (value.label == 'Net Amount Payable') {
                        //$('#tblordersummary').prepend('<tr  class="order-total baboth cartval" style="border-top: 1px solid #ded7d7"><th>' + value.label + '</th><td>' + value.value + '</td></tr>');
                        $('.orderSummoryTotal span').remove();
                        netpayment = value.value;
                        $('.orderSummoryTotal').append('<span>Amount Payable</span><span>' + (isNaN(netpayment) ? netpayment : Number(netpayment).toFixed(2)) + '</span>');
                    }
                    else {
                        $('.orderSummoryTableItems ul').append('<li><span>' + value.label + '</span><span>' + (isNaN(value.value) ? value.value : Number(value.value).toFixed(2)) + '</span></li>');
                    }

                    //else if (value.order == 1)
                    //    $('#tblordersummary').prepend('<tr class="cartval"><th><b>' + value.label + '</b></th><td><b>' + value.value + '</b></td></tr>');
                    //else
                    //    $('#tblordersummary').prepend('<tr class="cartval"><th>' + value.label + '</th><td>' + value.value + '</td></tr>');
                    if (value.label == 'Discount')
                        totaldiscount = value.value;
                });
                $('#frmSubmitCheckout').find('input[id="NetAmount"]').val(netpayment);

            }


        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(cartMethods.url.setSlot, 'POST', details, onSuccess, onError);

    }
    cartMethods.updateActiveSlots = function (targetSlotId) {
        $('ul.time-slot li.time-slot-item.active').each(function () {
            var slotId = $(this).attr('slotid');
            console.log(slotId, targetSlotId)
            // Check if the slotid does not match the targetSlotId
            if (slotId != targetSlotId) {
                // Remove the 'active' class
                $(this).removeClass('active');
            }
        });
    }
    cartMethods.OpenMiniCart = function () {
        $(".mycart_dropdown").addClass("active");
        $(".minicart").addClass("openminicart");
    }
    cartMethods.CloseMiniCart = function () {
        $(".minicart").removeClass("openminicart");
        $(".mycart_dropdown").removeClass("active");
    }
    cartMethods.initializePage = function () {
        cartPrivateMethods.events.initialize();
        cartPrivateMethods.registerHandlebarHelpers();
        cartPrivateMethods.cartCount();
    };

    cartPrivateMethods.events.initialize = function () {
        var controls = cartPrivateMethods.controls;

        $('input.changedelivery').unbind('click').on('click', function (e) {
            if ($(self).is(':checked')) { return false; }
            if (!confirm("Are you sure to change the delivery type?")) return false;

            cartPrivateMethods.changeDelivery($(this));
        });
        $(controls.applycoupon).unbind('click').on('click', function (e) {
            e.preventDefault();
            isRemove = $('.btnapplycoupon').text().toLowerCase() == 'remove';
            if (isRemove) {
                cartPrivateMethods.removeCoupon($(this));

            } else {
                cartPrivateMethods.applyCoupon($(this));

            }
            return false;
        });

        $('#chkcheckoutusewallet').unbind('click').on('click', function () {
            cartPrivateMethods.applyCoupon($(this));
        });

        $(controls.clear).unbind('click').on('click', function () {
            cartPrivateMethods.clearCart();
        });

        $(controls.deleteProduct).unbind('click').on('click', function () {
            $(this).addClass('processing');
            if ($(this).data('id'))
                cartPrivateMethods.deleteCart($(this).data('id'), false);
            else
                cartPrivateMethods.deleteOrder($(this).data('orderid'));
        });

        $('#frmSubmitCheckout').unbind('submit').on('submit', function (e) {
            //$(this).find('button.contunuPay_btn').prop('disabled', true);

            var result = cartPrivateMethods.validateCheckoutSubmit(this, e);
            //if (result)
            //    $(".contunuPay_btn").addClass('processing');
            //if (!($("#rdPaymentMethod_1").is(":checked"))) {
            //    $(this).find('button.contunuPay_btn img').remove();
            //    $(this).find('button.contunuPay_btn').prop('disabled', (result == true ? true : false));
            //}
            return result;
        });
        $("#btncontinuePay").unbind('click').on('click', function (e) {
            $(this).parent().removeClass('m-fixed-button'); // Removes from immediate parent
            if ($('.checkout-listing div.not-deliverable-order').length > 0) {
                master.showResult('Some items in your order are currently non-deliverable. Please remove these items or update their quantities, then try again.', 'Failure', false);
                return
            }
            window.scrollTo(0, 0);
            return cartPrivateMethods.loadPayment();
        });
        // 
        $("#submitonlinepayment").unbind('click').on('click', function (e) {
            $(".submitonlinepaymentWrap").addClass("processing");
            return cartPrivateMethods.loadOnlinePayment(this, e);
        });
        $("#submitonlinepayment-rzp-mob").unbind('click').on('click', function (e) {
            $("#submitonlinepayment-rzp-mob").addClass("processing");
            return cartPrivateMethods.loadOnlinePayment(this, e);
        });
        $('#btnsubmitpod').unbind('click').on('click', function (e) {
            return cartPrivateMethods.continuePOD();
        });
        $('#frmsubmitcart').on('submit', function (e) {
            $(this).find('button[type=submit]').addClass('processing');
            var _isvalid = cartPrivateMethods.validateCartSubmit(this, e);
            $(this).find('button[type=submit] img').remove();
            if (_isvalid == false)
                $(this).find('button[type=submit]').removeClass('processing');
        });
        $('#reviewBtn').unbind('click').on('click', function (e) {
            master.showResult('Sorry, we are not started accepting orders. Please check back soon.', 'Failure', false);

        });

        $('#deliveryTypeModal buttton.submitdeliverymethod').unbind('click').on('click', function (e) {
            $('#frmsubmitcart').submit();
        });

        $('#TimeSlotMode_2').unbind('click').on('click', function (e) {
            $("#datebox").show();
            var color = $("#datebox").is(':hidden') ? '#FF0' : 'white';
            $("#changedate").css('color', color);
        });
        $("#TimeSlotMode_1").click(function () {
            $("#datebox").hide();
        });
        $("#TimeSlotMode_1").click(function () {
            $(".hidicon").hide();
        });
        $("#hidedate").click(function () {
            $(".hidicon").show();
        });
        $('ul.time-slot li.time-slot-item').on('click', function (e) {
            var self = $(this)
            // the class `active` is added as soon as the button is clicked
            if ($(this).hasClass('active'))
                cartMethods.setTimeSlot(self, false)
            else
                cartMethods.setTimeSlot(self, true)
        });

        $('.form-qty-block').unbind('click').on('click', '.btn-plus, .btn-minus', function (e) {
            if ($(this).hasClass('skip-addcart'))
                return true;

            e.preventDefault();
            // Checks if the selected item requires age verification
            var needageverification = $(this).data("needageverification");

            // needageverification will be either 0 or 1
            if (needageverification) {
                master.showAgeVerificationModal(true) //shows age verification modal if 1
            } else
                cartPrivateMethods.events.onAdd($(this)); //else continue add to cart

        });

        $(controls.groupButton).unbind('click').on('click', function () {
            cartPrivateMethods.events.navigateToGroup($(this));
        });

        $('.trigerCartMenu').unbind('click').on('click', function () {
            event.stopPropagation();

            // Check if the dropdown is currently visible
            const isDropdownVisible = $('.Carttoggelwrap').hasClass('active');
            if (isDropdownVisible) {
                cartPrivateMethods.events.hideCart($(this));
            } else {
                cartPrivateMethods.events.showCart($(this));
                cartPrivateMethods.events.initialize();
            }

        });


        $(controls.deleteOutOfStock).unbind('click').on('click', function () {
            cartPrivateMethods.deleteCart($(this).data('id'), true);
        });

        $(controls.removeOrder).unbind('click').on('click', function () {
            cartPrivateMethods.deleteOrder($(this).data('orderid'));

        })

        $('.trigger-notes-modal').unbind('click').on('click', function () {
            //fetching orderid from the add button
            const orderId = $(this).data('orderid');

            // Set the order ID in the modals textarea as a data attribute and show the modal.
            $('#order-notes-modal .confirm-add-note').attr('data-orderid', orderId);
            $('#order-notes-modal').modal("show")
        })
        $('.confirm-add-note').unbind('click').on('click', function () {
            let Note = $('#noteToSeller').val().trim().substring(0, cartMethods.properties.notesLength);
            let OrderId = $(this).data('orderid')
            if (Note == '')
                return $('#noteToSeller').addClass('error')
            if (!OrderId || OrderId === '')
                return console.error('order_id was ' + OrderId)

            let apiData = {
                OrderId,
                Note
            }
            onSuccess = function (res) {
                $('#order-notes-modal').modal("hide")
            };

            onError = function (err) {
                console.error(err.result.error)
            };

            master.ajax.JSONRequest(cartMethods.url.addOrdernote, 'POST', apiData, onSuccess, onError);
        })
        $('.search_in_other_store').unbind('click').on('click', function () {
            var stit_ID = $(this).data('stitid');
            onSuccess = function (res) {
                //console.log(res)
                if (res.status == 1) { //1 for success and 0 for failure.
                    let product = res.data;
                    //console.log(product)
                    var imageUrl = (product.additional_image && product.additional_image.length > 0)
                        ? product.additional_image[0].image_url
                        : '/images/p-no-image.png';
                    var html = `<div class="productItemWrap" >
                          <div class="offer_wishlist">
                            <div
                              class="addtowishicon wishlist-block wishlist-${product.stit_ID}-${product.branch_id}"
                              data-productid="${product.stit_ID}"
                              data-groupid="${product.stit_ID}" 
                              data-branchid="${product.branch_id}" 
                              data-branchtypeid="${product.branch_type_id}" 
                              data-source="1"
                            >
                              <i class="fa-regular fa-heart"></i>
                            </div>
                          </div>
                          <div class="productImgWrap">
                            <div class="productImg">
                              <a href="/pd/${product.stit_ID}/${product.branch_id}/${product.branch_type_id}/${encodeURIComponent(product.stit_itemName)}">
                                <img
                                  src="${imageUrl}"
                                    onerror="${this.src = '/images/p-no-image.png'}"
                                  alt="${product.stit_SKU}"
                                />
                              </a>
                            </div>
                          </div>
                          <div class="productItemInfo">
                            <div class="productTitle">
                              <a href="/pd/${product.stit_ID}/${product.branch_id}/${product.branch_type_id}/${encodeURIComponent(product.stit_itemName)}">
                                ${product.stit_itemName}
                              </a>
                            </div>
                            <div class="productPrice">
                              <span class="actulPrice">
                               <span class="badge"><img src="/images/sponsored-01.svg"></span></span>
                              <span class="DisPrice_unitprice">
                                <span class="productDisPrice">₹${product.selling_price}</span>
                                <i class="expres_delivery" title="Courier Delivery">
                                  <img src="/images/icons/normal_delivery.svg" />
                                </i>
                              </span>
                            </div>
                          </div>
                          <div class="addunit_infowrap">
                            <div class="form-qty-block addunitWrap addunitWrap-${product.stit_ID}-${product.branch_id}" data-productid="${product.stit_ID}"
                              data-groupid="${product.stit_ID}">
                              <button
                                type="button"
                                id="product-minus-${product.stit_ID}-${product.branch_id}"
                                class="btn btn-sm btn-minus hide product-tile-minus-${product.stit_ID}-${product.branch_id}"
                                data-productid="${product.stit_ID}"
                                data-groupid="${product.stit_ID}"
                              >
                                -
                              </button>
                              <input
                                type="number"
                                class="form-control form-control-sm form-qty hide product-tile-qty-${product.stit_ID}-${product.branch_id}"
                                step="1"
                                min="0"
                                max="${product.stock_available}"
                                value="0"
                                id="product-qty-${product.stit_ID}-${product.branch_id}"
                                placeholder="Add"
                              />
                              <button
                                type="button"
                                id="product-plus-${product.stit_ID}-${product.branch_id}"
                                class="btn btn-sm btn-plus btn-add product-tile-plus-${product.stit_ID}-${product.branch_id}"
                                data-productid="${product.stit_ID}"
                                data-groupid="${product.stit_ID}"
                                data-brid="${product.branch_id}"
                                data-brtypeid="${product.branch_type_id}"
                                data-needageverification="0"
                              >
                                <span>Add</span>+
                              </button>
                            </div>
                            <div class="veg_novgsymple"></div>
                          </div>
                        </div >`;
                    $('li').has('a.search_in_other_store[data-stitid="' + stit_ID + '"]').html(html); //injecting the html to the selected view
                    cart.initializePage();//initializing the cart after injecting the html so that add to cart and other functionalities work as expected.
                } else {
                    $('.search_in_other_store[data-stitid="' + stit_ID + '"]').addClass('hide')
                    alert(res.message);
                }

            };

            onError = function (err) {
                console.log(err)
                $('.search_in_other_store[data-stitid="' + stit_ID + '"]').addClass('hide')
                alert('Sorry, No other stores are selling this product!');
            };

            master.ajax.JSONRequest('/searchInOtherStore', 'POST', stit_ID, onSuccess, onError);
        });
        $('.auto_remove_items').unbind('click').on('click', async function () {
            $(this).addClass('processing');

            let promises = [];

            $('.product-card.product_outofstock').each(function () {
                var $removeBtn = $(this).find('.btn-cart-remove');
                var productId = $removeBtn.data('id');

                if ($removeBtn.length > 0 && productId) {
                    var details = { cart_product_id: productId };

                    const promise = master.ajax.JSONRequestPromise(cartMethods.url.deleteCart, 'POST', details);

                    promises.push(promise);
                }
            });
            

            // Wait for all deleteCart API calls to finish
            Promise.all(promises).then(() => {
                window.location.reload();
            }).catch(err => {
                console.error("Some deleteCart requests failed:", err);
            });
        });
    };



    return cartMethods;
}();
$(function () {
    cart.initializePage();
});