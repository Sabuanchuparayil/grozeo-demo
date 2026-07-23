var master = function () {
    var masterMethods = {}, masterPrivateMethods = {};

    masterMethods.events = {};

    masterMethods.properties = {
        latitude: 0,
        longitude: 0,
        isLoading: false,
        hasLoadedCartAndWishlist: false,
        isLoadingCartAndWishlist: false,
        invokedPwa: false,
        popupWindow: null,
        popuptimer: null,
        paymentWindowCounter: null,
        orderId: null,
        currency: '',
        isHomePage: false,
        countryCode: '',
        paymentToken: null,
        pgkey: null,
        pgtotal: 0,
    };
    masterPrivateMethods.properties = {
        rzp: null,
        retailerTemplate: '',
        searchResultTemplate: '',
        isSearchInitiated: false,
        searchQueue: [],
        isPodToOnline: 0,
    };
    masterMethods.url = {
        contactUs: '/contactus-submit',
        loadMore: '/loadmore',
        offerLoadMore: '',
        successPage: '',
        updateAgeVerifiedStatus: '/UpdateAgeVerifiedStatus',
        confirmLegalAge: '/confirmLegalAge'
    };

    masterPrivateMethods.controls = {
        errorModal: 'error-modal',
        errorMessage: 'error-message',
        contactForm: '#frmContactSubmit',
        resultsModal: '#resultsModal',
        retailer: '.retailer-type-link',
        retailerTemplateContainer: 'retailer-link-template',
        retailerContainer: "#retailer-type-",
        searchResultContainer: '#mainsitsearch',
        searchResultTemplate: 'search-results-template',
        searchBox: '#searchkey',
        searchResultTile: '.search_info_items'
    };

    masterMethods.clearForm = function (obj) {
        var orderid = -1;
        var ordernum = '';
        var orderbranch = '';
        var orderdate = '';
        if (obj) {
            orderid = $(obj).attr('orderid');
            ordernum = $(obj).attr('ordernum');
            orderbranch = $(obj).attr('orderbranch');
            orderdate = $(obj).attr('orderdate');

        }
        var frm = $(masterPrivateMethods.controls.contactForm);
        if (frm) {
            //$(frm).find('#txtEmail').val('');
            $(frm).find('#txtMessage').val('');
            $(frm).closest('#contactSubmit').show();
            $(frm).closest('#contactSubmit').siblings('#contactResult').hide();
            $(frm).find('#hidOrderId').val(orderid);
            $(frm).find('#hidOrderNum').val(ordernum);

            $(frm).find('#hidOrderBranch').val(orderbranch);
            $(frm).find('#hidOrderDate').val(orderdate);

        }
    };

    masterMethods.loadHomeCategories = function (obj) {
        var url = masterMethods.url.offerLoadMore != '' ? masterMethods.url.offerLoadMore : '/homecategories';
        onSuccess = function (result) {
            if (result) {
                $(obj).parent('ul').append(result);
                $(obj).remove();
            }
            masterMethods.properties.isLoading = false;
        };
        onError = function (data) {
            masterMethods.properties.isLoading = false;
        };
        master.ajax.JSONRequest(url, 'GET', {}, onSuccess, onError);
    }

    masterMethods.loadMore = function (obj) {
        masterMethods.properties.isLoading = true;
        var pagenum = $(obj).attr('pagenum');
        var subcatId = $(obj).attr('subcatId');
        var brandId = $(obj).attr('brandid');
        var catlevel = $(obj).attr('catlevel');
        var contenttype = $(obj).attr('contenttype');
        var searchkey = $(obj).attr('searchkey');
        var navlink = $(obj).attr('navlink');
        var retailtypeid = $(obj).attr('retailtypeid');
        var is_businesstypes = $(obj).attr('isbusinesstypes');

        var url = masterMethods.url.loadMore + '/' + contenttype + '/' + subcatId + '/' + pagenum + '/' + brandId + '/' + catlevel;
        if (navlink && navlink != '')
            url = navlink;
        else if (contenttype == 4 || contenttype == 5)
            url = masterMethods.url.loadMore + '/' + contenttype + '/' + pagenum + '/' + searchkey;
        else if (is_businesstypes) {
            url = `/stores/${retailtypeid}`;

        }
        //$(obj).insertAfter('<img src="/images/loadingbig.gif" class="imgloadingmore" style="width: 40px;"/>');
        $(obj).append('<div class="processing"></div>');
        //$(obj).remove(); // html('<img src="/images/loadingbig.gif" style="width: 50px;"/>'); //
        onSuccess = function (result) {
            $(obj).find('div.processing').remove();
            if (result) {
                if (result.trim().length > 0) {
                    // Create a temporary jQuery element
                    let $tempElement = $('<div>').html(result.trim());

                    // Unwrap .col-12 and then .row
                    $tempElement.find('.row > .col-12').contents().unwrap();
                    $tempElement.find('.row').contents().unwrap();

                    // Now get cleaned HTML
                    let modifiedResult = $tempElement.html();

                    // Append the result to desired container
                    $(obj).parent('div').append(modifiedResult);

                } else {
                    $(obj).parent('div').append("<p>No nearby stores are currently available.</p>");

                }
                //$(obj).parent('ul').append($(obj));
                $(obj).remove();
                cart.initializePage();
                cart.updateCartButton();
                masterMethods.events.initialize();
                //$(obj).insertBefore(result);
                //alert("Product Added");
                //cart.updateCartCount(result);
                wishlist.initializePage();
                //$(catalogPrivateMethods.controls.cartContainer).html('<img src="~/images/shopping.png" class="avt"><span class="addnumber">' + result + '</span><span class="cont">My Cart</span><br>' + result + ' items');
            }
            else {
                $(obj).remove();
            }
            masterMethods.properties.isLoading = false;
        };

        onError = function (data) {
            $(obj).parent('div').find('div.processing').remove();
            console.log(data);
            masterMethods.properties.isLoading = false;
        };
        master.ajax.JSONRequest(url, 'GET', {}, onSuccess, onError);

    };

    masterPrivateMethods.stripepayment = function (obj, ordno, ordid) {
       

        //var createOrderUrl = "/confirm-order/" + ordno + "/" + ordid;
        // stripe object load
        var stripe = Stripe(obj.keyid); //Stripe('pk_test_51KT2peHhDmQH9IBvD7gClAR6mKK2uAwgvQWd1tjqK7jagNum1mLFG0vOhkIvWh6EqJDgEKDbYiio5dLBmfWtnYwN00vSvPt2Zy');
        var elements = stripe.elements();
        var style = {
            base: {
                color: "#32325d",
                fontFamily: 'Arial, sans-serif',
                fontSmoothing: "antialiased",
                fontSize: "16px",
                "::placeholder": {
                    color: "#32325d"
                }
            },
            invalid: {
                fontFamily: 'Arial, sans-serif',
                color: "#fa755a",
                iconColor: "#fa755a"
            }
        };
        var card = elements.create("card", {
            hidePostalCode: true,
            style: style
        });

        const paymentRequest = stripe.paymentRequest({
            country: obj.countryCode,
            currency: obj.currencySymbol,
            total: {
                label: 'Total',
                amount: obj.paymentInfo.amount,
            },
            requestPayerName: true,
            requestPayerEmail: true,
        });

        const prButton = elements.create('paymentRequestButton', {
            paymentRequest,
            style: {
                paymentRequestButton: {
                    type: 'default',
                    // One of 'default', 'book', 'buy', or 'donate'
                    // Defaults to 'default'

                    theme: 'dark',
                    // One of 'dark', 'light', or 'light-outline'
                    // Defaults to 'dark'

                    height: '64px',
                    // Defaults to '40px'. The width is always '100%'.

                },
            },

        });

        (async () => {
            // Check the availability of the Payment Request API first.
            const result = await paymentRequest.canMakePayment();
            if (result) {
                prButton.mount('#payment-request');
            } else {
                //alert('payment buttons failed');
                //document.getElementById('payment-request-button').style.display = 'none';
            }
        })();

        paymentRequest.on('paymentmethod', async (ev) => {
            // Confirm the PaymentIntent without handling potential next actions (yet).
            const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(
                obj.paymentInfo.client_secret,
                { payment_method: ev.paymentMethod.id },
                { handleActions: false }
            );

            if (confirmError) {
                ev.complete('fail');
            } else {
                ev.complete('success');
                if (paymentIntent.status === "requires_action") {
                    const { error } = await stripe.confirmCardPayment(obj.paymentInfo.client_secret);
                    if (error) {
                        alert("Error - payment failed!");
                        // The payment failed -- ask your customer for a new payment method.
                    } else {
                        // alert("Success");
                        orderComplete(ordid, ordno, 1);
                        // The payment has succeeded -- show a success message to your customer.
                    }
                } else {
                    //alert("success payment process");
                    orderComplete(ordid, ordno, 1);
                    // The payment has succeeded -- show a success message to your customer.
                }
            }
        });



        // Stripe injects an iframe into the DOM
        card.mount("#card-element");
        card
            .on(
                "change",
                function (event) {
                    // Disable the Pay button if there are no card details in
                    // the Element
                    //document.querySelector("button").disabled = event.empty;
                    document.querySelector("#card-error").textContent = event.error ? event.error.message : "";
                });
        $('.contunuPay_btn_wrap.payNowBtnsec').removeClass('hide');
        $('#card-element').removeClass('processing');
        $('#card-element').css('border-width', '1px');

        // end stripe object load
        var payWithCard = function (stripe, card, clientSecret, orderHash) {
            loading(true);
            stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: card
                }
            }).then(function (result) {
                if (result.error) {
                    // Show error to your customer
                    showError(result.error.message);
                } else {
                    // The payment succeeded!
                    orderComplete(result.paymentIntent.id, orderHash);
                }
            });
        };
        /* ------- UI helpers ------- */
        // Shows a success message when the payment is complete
        var orderComplete = function (paymentIntentId, orderHash, delay) {
            loading(false);
            if (!delay)
                delay = 10000;

            $("#submitonlinepayment").prop('disabled', true);
            $(".submitonlinepaymentWrap").addClass('processing');
             
            //$("#submitonlinepayment").append('<img src="/images/loadingbig.gif" class="imgloadingmore" style="width: 60px; height: 60px; position: absolute"/>');
            $("#confirm-checkout-form").attr("action", "/confirm-order/" + ordno + "/" + ordid);
            setTimeout(function () {
                $("#confirm-checkout-form").submit();
                $("#submitonlinepayment").prop('disabled', false); $("#submitonlinepayment").find('img').remove();
            }, delay);

        };
        // Show the customer the error from Stripe if their card fails to
        // charge
        var showError = function (errorMsgText) {
            loading(false);
            //alert("Error - " + errorMsgText);
            var errorMsg = document.querySelector("#card-error");
            errorMsg.textContent = errorMsgText;
            setTimeout(function () {
                errorMsg.textContent = "";
            }, 10000);
        };
        // Show a spinner on payment submission
        var loading = function (isLoading) {
            if (isLoading) {
                // Disable the button and show a spinner
                //document.querySelector("button").disabled = true;
                //document.querySelector("#spinner").classList.remove("hidden");
                //document.querySelector("#button-text").classList.add("hidden");
            } else {
                //document.querySelector("button").disabled = false;
                //document.querySelector("#spinner").classList.add("hidden");
                //document.querySelector("#button-text").classList
                //    .remove("hidden");
            }
        };

        //$('#frmSubmitCheckout button[type = submit]').prop('disabled', false);
        //$('#frmSubmitCheckout button[type = submit] img').remove();
        //$('#frmSubmitCheckout').find('button[type = submit]').html('Send Payment');
        //$('#rdPaymentMethod_0').on('click', function () {
        //    $('#frmSubmitCheckout button[type = submit]').unbind('click');
        //    $('#frmSubmitCheckout button[type = submit]').text('Place Order');
        //    $('#card-element').html('');
        //    $('#card-error').html('');
        //});

        //$('#frmSubmitCheckout button[type = submit]').unbind('click').on('click', function (e) {
        $('#frmSubmitCheckout').find('#submitonlinepayment').unbind('click').on('click', function (e) {

            var secret = obj.paymentInfo.client_secret;
            //$(this).prop('disabled', true);
            //$(this).append('<img src="/images/loadingbig.gif" class="imgloadingmore" style="width: 60px; height: 60px; position: absolute"/>');
            payWithCard(stripe, card, secret, ordno);
            return false;
        });

    }

    masterPrivateMethods.rpayment = function (obj, ordno, ordid, isPodToOnline) {

        var options = obj;
        options.handler = function (response) {
            onSuccess = function (result) {
                if (isPodToOnline || masterPrivateMethods.properties.isPodToOnline == 1) {
                    alert("Your payment was completed.");
                    window.location.reload(true);
                }
                else {
                    $("#confirm-checkout-form").attr("action", "/confirm-order/" + ordno + "/" + ordid);
                    $("#confirm-checkout-form").submit();
                }
            };
            onError = function (data) {
                alert('Operation failed');
                $('.processing').removeClass('processing');
            };
            if (response)
                response.isPodToOnline = masterPrivateMethods.properties.isPodToOnline;
            master.ajax.JSONRequest('/checkout/rsubmit', 'POST', response, onSuccess, onError);

        };
        options.modal = {
            escape: false, ondismiss: function () {
                $('#frmSubmitCheckout').find('button[type=submit]').prop('disabled', false);
                $('#frmSubmitCheckout').find('button[type=submit]').removeClass('processing');
                $('#frmSubmitCheckout button[type=submit] img').remove();
                $('.processing').removeClass('processing');
            }
        };

        options.theme.image_padding = false;
        masterPrivateMethods.properties.rzp = new Razorpay(options);
        if (masterPrivateMethods.properties.rzp) {
            masterPrivateMethods.properties.rzp.open();
        }
    }

    masterPrivateMethods.easebuzzPay = function (obj, ordno, ordid, isPodToOnline) {
        var easebuzzCheckout = new EasebuzzCheckout(obj.key, obj.mode);
        if (!easebuzzCheckout) {
            alert('Invalid operation or technical failure.');
            return;
        }

        var options = {
            access_key: obj.id,
            onResponse: (response) => {
                if (response.status == 'success') {
                    onSuccess = function (result) {
                        if (isPodToOnline || masterPrivateMethods.properties.isPodToOnline == 1) {
                            alert("Your payment was completed.");
                            window.location.reload(true);
                        }
                        else {
                            $("#confirm-checkout-form").attr("action", "/confirm-order/" + ordno + "/" + ordid);
                            $("#confirm-checkout-form").submit();
                        }
                    };
                    onError = function (data) {
                        alert('Operation failed');
                        $('.processing').removeClass('processing');

                    };
                    if (response)
                        response.isPodToOnline = masterPrivateMethods.properties.isPodToOnline;
                    master.ajax.JSONRequest('/checkout/easybuzzsubmit', 'POST', response, onSuccess, onError);

                } else {
                    alert("Your payment failed. Error message: " + response.errorMessage);
                }

            },
            theme: "#123456" // color hex
        }
        easebuzzCheckout.initiatePayment(options);
    }
    masterMethods.revolutPayment = function (token, ordno, ordid, isPodToOnline, isApplepay) {
        if (isApplepay == true) {
            try {
                const target = document.getElementById("payment-request");
                const { paymentRequest } = RevolutCheckout.payments({
                    locale: "en", // Optional, defaults to "auto"
                    publicToken: masterMethods.properties.pgkey, // Merchant public API key 
                }).then((paymentsInstance) => {
                    const paymentRequest = paymentsInstance.paymentRequest

                    const paymentRequestInstance = paymentRequest(target, {
                        currency: "GBP", // 3-letter currency code
                        amount: masterMethods.properties.pgtotal, // In lowest denomination e.g., cents
                        createOrder: () => {
                            if (token)
                                masterMethods.properties.paymentToken = token;
                            var rlToken = masterMethods.properties.paymentToken;

                            return Promise.resolve({
                                publicId: rlToken,
                            });

                            //return { publicId: rlToken, }
                        },
                        onSuccess: () => {
                            $("#confirm-checkout-form").attr("action", "/confirm-order/" + ordno + "/" + ordid);
                            $("#confirm-checkout-form").submit();
                        },
                        onCancel: () => {
                            masterMethods.showResult('Looks like you chose to cancel the action. No worries — you can try again.', 'Cancelled!', false);
                            $('.processing').removeClass('processing');

                        },
                        onError: (error) => {
                            masterMethods.showResult('The selected payment method failed. Please try another method.', 'Payment failed', false);
                            $('.processing').removeClass('processing');

                        }
                    });

                    paymentRequestInstance.canMakePayment().then((method) => {
                        if (method) {
                            paymentRequestInstance.render()
                        } else {
                            paymentRequestInstance.destroy()
                        }
                    })
                })
            } catch (error) {
                //console.error("Error initializing payment buttons:", error);
            }
        }
        else {
            RevolutCheckout(token).then(function (instance) {
                var popup = instance.payWithPopup({
                    onSuccess: () => {

                        if (isPodToOnline || isPodToOnline == 1) {
                            alert("Your payment was completed.");
                            window.location.reload(true);
                        }
                        else {
                            setTimeout(function () {
                                $("#confirm-checkout-form").attr("action", "/confirm-order/" + ordno + "/" + ordid);
                                $("#confirm-checkout-form").submit();
                            }, 5000); // Display for 5 seconds (5000ms)
                        }


                    },
                    onCancel: () => {
                        $('.submitonlinepaymentWrap').removeClass('processing');
                        popup.destroy();
                    },
                    onError: (message) => {
                        $('.submitonlinepaymentWrap').removeClass('processing')
                        masterMethods.showResult('The selected payment method failed. Please try another method.', 'Payment failed', false)

                    },
                });

            });

        }

    };

    masterMethods.getPaymentPage = function (checkoutData, isPodToOnline, mPayment) {
        masterPrivateMethods.properties.isPodToOnline = isPodToOnline;
        if (masterPrivateMethods.properties.rzp) {
            masterPrivateMethods.properties.rzp.open();
            return false;
        }

        // If token was generated already
        if (masterMethods.properties.paymentToken && masterMethods.properties.paymentToken != '')
            return masterMethods.revolutPayment(masterMethods.properties.paymentToken, checkoutData.OrderNum, checkoutData.OrderId, masterPrivateMethods.properties.isPodToOnline, (mPayment && mPayment == true ? true : false));

        if (checkoutData) {
            $('.btnabortonlinepayment').unbind('click').on('click', function (e) {
                e.stopImmediatePropagation(); e.stopPropagation();
                if (!confirm('Are you sure you want to abort payment?'))
                    return false;

                //$(this).append('<img src="/images/odoprogress.gif" class="imgloadingmore" style="width: 30px; height: 30px; position: absolute"/>')
                if (masterMethods.properties.popupWindow && !masterMethods.properties.popupWindow.closed) { masterMethods.properties.popupWindow.close(); }
                masterMethods.properties.orderId = checkoutData.OrderId
                masterMethods.abortPayment(masterMethods.properties.orderId);
            });

            //masterMethods.url.successPage = '/confirm-order/' + Checkout.OrderNum + '/' + Checkout.OrderId;
            masterMethods.url.successPage = '/Checkout/SubmitOrder';
            onSuccess = function (result) {
                //$('.processing').removeClass('processing');

                if (result && result.paymentInfo && result.gateway == 'razorpay')
                    masterPrivateMethods.rpayment(result.paymentInfo, checkoutData.OrderNum, checkoutData.OrderId);
                else if (result && result.paymentInfo && result.gateway == 'stripe')
                    masterPrivateMethods.stripepayment(result, checkoutData.OrderNum, checkoutData.OrderId);
                else if (result && result.paymentInfo && result.gateway == 'easebuzz')
                    masterPrivateMethods.easebuzzPay(result.paymentInfo, checkoutData.OrderNum, checkoutData.OrderId);
                else if (result && result.paymentInfo && result.gateway == 'revolut') {
                    masterMethods.properties.paymentToken = result.paymentInfo.token;
                    if (mPayment)
                        masterMethods.revolutPayment(result.paymentInfo.token, checkoutData.OrderNum, checkoutData.OrderId, masterPrivateMethods.properties.isPodToOnline, true);
                    //return result;
                    else
                        masterMethods.revolutPayment(result.paymentInfo.token, checkoutData.OrderNum, checkoutData.OrderId, masterPrivateMethods.properties.isPodToOnline, false);

                }

                else if (result && result.paymentUrl && result.paymentUrl != '')
                    //window.location.href = result.paymentUrl;
                    masterMethods.loadPaymentPopupPage(result, checkoutData);
                else
                    alert('Failure');
            };
            onError = function (data) {
                alert('Operation failed');
                $('.processing').removeClass('processing');

            };
            master.ajax.JSONRequest('/paymenturl', 'POST', checkoutData, onSuccess, onError);
        }
    };

    masterMethods.loadPaymentPopupPage = function (result, checkoutData) {
        var _url = result.paymentUrl
        if (result.gateway == 'ccavenue')
            $('.btn-field.m-fixed-button').addClass('opacity-0') //popuop overlaps the loading pay now button in mobile screens

        // for ccavenue

        //$('iframe#paymentFrame').load(function () {
        //    window.addEventListener('message', function (e) {
        //        $("#paymentFrame").css("height", e.data['newHeight'] + 'px');
        //    }, false);
        //});
        //$('#modalpaymentframe').find('.bg-white').addClass('processing');
        //$('iframe#paymentFrame').html('<img src="https://dev.grozeo.in/images/processing.gif"/>');
        /*
        $('#btnccavenueclose').unbind('click').on('click', function () {
            $("#confirm-checkout-form").attr("action", "/confirm-order/" + checkoutData.OrderNum + "/" + checkoutData.OrderId);
            $("#confirm-checkout-form").submit();
        });
        */
        $("#modalpaymentframe").on("hidden.bs.modal", function () {
            $("#confirm-checkout-form").attr("action", "/confirm-order/" + checkoutData.OrderNum + "/" + checkoutData.OrderId);
            $("#confirm-checkout-form").submit();
        });

        $('#modalpaymentframe').modal('show');
        $('iframe#paymentFrame').attr("src", _url);

        return; // retrun from here to avoid the unnecessary popup window for the time being.
        // end of ccavenue

        if (masterMethods.properties.popupWindow && !masterMethods.properties.popupWindow.closed) { masterMethods.properties.popupWindow.close(); }
        $('.retrypayment').hide();
        $('button.retrypayment').unbind('click').on('click', function () {
            masterMethods.loadPaymentPopupPage(_url);
        });
        $('#frmSubmitCheckout').find('button[type=submit] img').remove();
        masterMethods.properties.popupWindow = window.open(_url, 'window', 'location = yes,menubar =‌​yes, scrollbars = yes, resizable = yes');
        $('#paymentRedirectedModal').modal({ backdrop: 'static', keyboard: false });
        masterMethods.properties.popupWindow.focus();
        document.onmousedown = masterMethods.focusPopup;
        document.onkeyup = masterMethods.focusPopup;
        document.onmousemove = masterMethods.focusPopup;
        masterMethods.properties.paymentWindowCounter = new Date();
        masterMethods.properties.popuptimer = setInterval(masterMethods.checkChild, 3000);
    }
    masterMethods.focusPopup = function () {
        if (masterMethods.properties.popupWindow && !masterMethods.properties.popupWindow.closed) { masterMethods.properties.popupWindow.focus(); }
    }
    masterMethods.checkChild = function () {
        if (masterMethods.properties.popupWindow && masterMethods.properties.popupWindow.closed && !($('.retrypayment').is(":visible"))) {
            if (masterMethods.properties.popuptimer)
                clearInterval(masterMethods.properties.popuptimer);
            masterMethods.properties.popuptimer = setInterval(masterMethods.checkChild, 60 * 1000); // 1minute.
            $('.retrypayment').show();
            //if (masterMethods.url.successPage && masterMethods.url.successPage != '')
            //    window.location.href = masterMethods.url.successPage;
            //else
            //    window.location.href = '/cart';
        }
        if (masterMethods.properties.paymentWindowCounter) {
            var difference = new Date() - masterMethods.properties.paymentWindowCounter;
            if (difference > 6 * 1000 * 60) {
                if (masterMethods.properties.popupWindow && !masterMethods.properties.popupWindow.closed)
                    masterMethods.properties.popupWindow.close();
                clearInterval(masterMethods.properties.popuptimer);

                if (!masterMethods.properties.orderId || masterMethods.properties.orderId < 1) {
                    //masterMethods.abortPayment(masterMethods.properties.orderId);
                    //alert('Unfortunately we have not received the update about the payment from Bank. If your bank got debited, please contact support and mention the Order Number');
                    window.location.href = masterMethods.url.successPage;
                    return;
                }

                onSuccess = function (result) {
                    //if (result && result.status_id) {                        
                    //    if (result.status_id == 21)
                    //        alert('Unfortunately we have not received any update about the payment from Bank. If your bank got debited, please contact support/help desk and mention the Order Number: ' + masterMethods.properties.orderId);
                    //}
                    window.location.href = masterMethods.url.successPage;
                };
                onError = function (data) {
                    //masterMethods.abortPayment(masterMethods.properties.orderId);
                    window.location.href = masterMethods.url.successPage;
                };
                master.ajax.JSONRequest('/orderstatus/' + masterMethods.properties.orderId, 'GET', {}, onSuccess, onError);

            }
        }
    }
    masterMethods.abortPayment = function (orderId) {
        onSuccess = function (result) {
            window.location.href = '/cart';
        };
        onError = function (data) {
            window.location.href = '/cart';
        };
        master.ajax.JSONRequest('checkout/abortonlinepayment/' + orderId, 'GET', {}, onSuccess, onError);

    }

    masterPrivateMethods.contactUs = function (self) {
        //if ($.trim())
        var details = {
            Email: $(self).find('#txtEmail').val(),
            Message: $(self).find('#txtMessage').val(),
            Phone: $(self).find('#txtPhone').val(),
            OrderNum: $(self).find('#hidOrderNum').val(),
            OrderId: $(self).find('#hidOrderId').val(),
            BranchName: $(self).find('#hidOrderBranch').val(),
            OrderDate: $(self).find('#hidOrderDate').val()
        };

        if ($.trim(details.Email) == '') {
            alert('Please enter your email');
            return false;
        }
        if ($.trim(details.Phone) == '') {
            alert('Please enter your phone');
            return false;
        }
        if ($.trim(details.Message) == '') {
            alert('Please input your message');
            return false;
        }

        onSuccess = function (result) {
            //if (result !== -1) {
            $(window).scrollTop(0);
            $(self).closest('#contactSubmit').hide();
            if (result && result.message && result.message != '')
                $(self).find('#contactResultText').html(result.message);
            else
                $(self).closest('#contactSubmit').siblings('#contactResult').show();
        };

        onError = function (data) {
            $(self).closest('#contactSubmit').hide();
            $(self).find('#contactResultText').html("Error on submitting contact form. Please contact support using phone number provided.");
            $(self).closest('#contactSubmit').siblings('#contactResult').show();
            console.log(data);
        };

        master.ajax.JSONRequest(masterMethods.url.contactUs, 'POST', details, onSuccess, onError);

    }

    masterPrivateMethods.addToCart = function (self) {

        var prodctId = self.data('productid')
        var details = {
            cart_product_id: prodctId,
            cart_group_id: self.data('groupid'),
            cart_order_qty: parseInt($("#product-qty-" + prodctId).val())
        };

        onSuccess = function (result) {
            if (result !== -1) {
                alert("Product Added");
                cart.updateCartCount(result);
                //$(catalogPrivateMethods.controls.cartContainer).html('<img src="~/images/shopping.png" class="avt"><span class="addnumber">' + result + '</span><span class="cont">My Cart</span><br>' + result + ' items');
            }
        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(catalogMethods.url.addTocatalog, 'POST', details, onSuccess, onError);

    }

    masterMethods.showErrorMessage = function (message) {
        if (message !== null) {
            $(masterPrivateMethods.controls.errorMessage).text(message);
        }
        $(masterPrivateMethods.controls.errorModal).modal('show');
    };
    masterMethods.showAgeVerificationModal = function (state) {
        if (state)
            $('#age_verification_modal').modal('show')
        else
            $('#age_verification_modal').modal('hide')

    }
    masterMethods.showResult = function (message, title, success, oncloseredirecturl = '') {
        if (message !== null) {
            if (success)
                $(masterPrivateMethods.controls.resultsModal).find('.resulthead').css('color', 'green');
            else
                $(masterPrivateMethods.controls.resultsModal).find('.resulthead').css('color', 'red');
            $(masterPrivateMethods.controls.resultsModal).find('.resulthead').html('<i class="fa ' + (success ? 'fa-check-circle' : 'fa-exclamation') + '" aria-hidden="true"></i>&nbsp;' + title);
            $(masterPrivateMethods.controls.resultsModal).find('.resultcontent').html(message);
            $(masterPrivateMethods.controls.resultsModal).modal('show');

            $(masterPrivateMethods.controls.resultsModal).unbind('hidden.bs.modal');
            if (oncloseredirecturl != '')
                $(masterPrivateMethods.controls.resultsModal).on('hidden.bs.modal', function (e) { window.location.href = oncloseredirecturl; });

        }
        //$(masterPrivateMethods.controls.errorModal).modal('show');
    };

    masterMethods.getGeoLocation = function () {
        getLocation = function (position) {
            masterMethods.properties.latitude = position.coords.latitude;
            masterMethods.properties.longitude = position.coords.longitude;
        };

        handleError = function (error) {
            masterMethods.properties.latitude = 0;
            masterMethods.properties.longitude = 0;
        }

        // if (navigator.geolocation) {
        //    navigator.geolocation.getCurrentPosition(getLocation, handleError);
        //}
    };

    masterMethods.showInstallPromotion = function () {
        if ($(window).width() < 769 && !masterMethods.properties.invokedPwa) {
            masterMethods.properties.invokedPwa = true;
            $('#PWAModal').modal('show');
        }

    };

    masterMethods.hideInstallPromotion = function () {
        $('#PWAModal').modal('hide');
    };

    //masterPrivateMethods.getRetailerType = function (self) {
    //    var businessTypeId = self.data("id");
    //    onSuccess = function (result) {
    //        for (var i = 0; i < result.length; i++) {
    //            result[i].id = businessTypeId;
    //        }
    //        masterPrivateMethods.properties.retailerTemplate = masterPrivateMethods.properties.retailerTemplate === '' ? document.getElementById(masterPrivateMethods.controls.retailerTemplateContainer).innerHTML : masterPrivateMethods.properties.retailerTemplate;
    //        var template = Handlebars.compile(masterPrivateMethods.properties.retailerTemplate);
    //        var compiledTemplate = template(result);
    //        $(masterPrivateMethods.controls.retailerContainer + businessTypeId).html(compiledTemplate);
    //    };
    //    master.ajax.JSONRequest('/retailerCategory/' + businessTypeId, 'GET', {}, onSuccess);
    //}


    masterPrivateMethods.onSearchResultClick = function (self) {
        var branch = self.data("branch");
        var branchtypeid = self.data("branchtypeid");
        var stitid = self.data("stitid");
        var stitfsiuid = self.data("stitfsiuid");
        var name = self.data("name");
        var url = '/pd/' + stitid + '/' + branch + '/' + branchtypeid + '/' + (name.replace ? name.replace('+', '').replace('/', '') : name);
        window.open(url, "_self");
    }

    masterPrivateMethods.initializeSearchAutoComplete = function (key) {
        if (!masterPrivateMethods.properties.isSearchInitiated) {
            masterPrivateMethods.properties.isSearchInitiated = true;
            var url = "/searchautocomplete?searchkey=" + key;
            var pathElements = window.location.pathname.split('/');
            if (pathElements.length >= 2 && pathElements[1] == "bt") {
                url = "/bt/searchautocomplete/" + pathElements[2] + "?searchkey=" + key;
            } else if (pathElements.length >= 2 && pathElements[1] == "st") {
                url = "/st/searchautocomplete/" + pathElements[2] + "?searchkey=" + key;
            } else if (pathElements.length >= 2 && pathElements[1] == "rc") {
                url = "/rc/searchautocomplete/" + pathElements[2] + "?searchkey=" + key;
                //url = "/st/searchautocomplete/" + pathElements[2] + "?searchkey=" + key;
            }

            onSuccess = function (result) {

                masterPrivateMethods.properties.searchResultTemplate = masterPrivateMethods.properties.searchResultTemplate === '' ? document.getElementById(masterPrivateMethods.controls.searchResultTemplate).innerHTML : masterPrivateMethods.properties.searchResultTemplate;
                var template = Handlebars.compile(masterPrivateMethods.properties.searchResultTemplate);
                var compiledTemplate = template(result);
                $(masterPrivateMethods.controls.searchResultContainer).html(compiledTemplate);
                $(masterPrivateMethods.controls.searchResultContainer).removeClass("hide");
                $('.search-cntrl').removeClass('hide')
                masterPrivateMethods.properties.isSearchInitiated = false;
                $(masterPrivateMethods.controls.searchResultTile).unbind("click").on("click", function () {
                    masterPrivateMethods.onSearchResultClick($(this));
                });
                if (masterPrivateMethods.properties.searchQueue.length > 0) {
                    var index = masterPrivateMethods.properties.searchQueue.length - 1;
                    var searchKey = masterPrivateMethods.properties.searchQueue[index];
                    masterPrivateMethods.properties.searchQueue.splice(index, 1);
                    masterPrivateMethods.initializeSearchAutoComplete(searchKey);
                }
            };

            onError = function (data) {
                console.log(data);
                masterPrivateMethods.properties.isSearchInitiated = false;
            };
            master.ajax.JSONRequest(url, 'GET', {}, onSuccess, onError);

        } else {
            masterPrivateMethods.properties.searchQueue.push(key);
        }
    }
    masterPrivateMethods.ageConfirmation = function (confirmation) {
        onSuccess = function (data) {
            console.log(data);
            master.showAgeVerificationModal(false)
            $.ajax({
                url: master.url.updateAgeVerifiedStatus,
                type: 'POST',
                data: { value: confirmation },
                success: function (response) {
                    location.reload(true); // Reload the page after updating the user
                },
            });
        };
        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(master.url.confirmLegalAge, 'POST', confirmation ? 1 : 0, onSuccess, onError);
    }
    masterPrivateMethods.updateGuest = function (apiData) {
        onSuccess = function (data) {
            console.log(data);
            if (data.success == true)
                location.reload(true)

        };
        onError = function (data) {
            $('.search_address_input_wrap').removeClass('processing')

            console.error(data.responseText);
        };
        master.ajax.JSONRequest('/guest/update', 'POST', apiData, onSuccess, onError);
    }

    masterMethods.ajax = {
        postJSON: function (url, form, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                type: 'POST',
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                async: true,
                cache: false,
                data: $('#' + form).serialize(),
                dataType: "html",
                success: success,
                error: customError
            });
        },
        SyncpostJSON: function (url, form, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                type: 'POST',
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                async: false,
                cache: false,
                data: $('#' + form).serialize(),
                dataType: "html",
                success: success,
                error: customError
            });
        },
        requestServer: function (url, type, datatype, data, success, error) {
            var customError = function (er) {
                console.log(er);
            }

            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                type: type,
                cache: false,
                data: data,
                dataType: datatype,
                success: success,
                error: customError
            });
        },
        fileUpload: function (url, formData, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;

            $.ajax({
                type: "POST",
                url: url,
                cache: false,
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: success,
                error: customError
            });
        },
        JSONRequest: function (url, type, data, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                type: type,
                cache: false,
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                success: success,
                error: customError
            });
        },
        SyncJSONRequest: function (url, type, data, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                type: type,
                async: false,
                cache: false,
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                success: success,
                error: customError
            });
        },
        JSONRequestWithHeader: function (url, type, data, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'key=' + Math.random().toString(),
                type: type,
                cache: false,
                data: data,
                success: success,
                error: customError
            });
        },
        //JSON request that returns a resolved promise.
        JSONRequestPromise: function (url, type, data) {
        return new Promise((resolve, reject) => {
            master.ajax.JSONRequest(
                url,
                type,
                data,
                function success(response) {
                    resolve(response);
                },
                function error(err) {
                    reject(err);
                }
            );
        });
    }
    };

    masterMethods.events.initialize = function () {
        var controls = masterPrivateMethods.controls;

        $(controls.contactForm).unbind('submit').on('submit', function (event) {
            event.preventDefault();
            masterPrivateMethods.contactUs($(this));
        });
        $('.btnloadmore').unbind('click').on('click', function (event) {
            if (!masterMethods.properties.isLoading && $(this).isInViewport()) {
                masterMethods.properties.isLoading = true;
                masterMethods.loadMore(this);
            }
        });

        $(controls.searchBox).unbind("keyup").on("keyup", function () {
            var key = $(this).val();
            $('.search-cntrl').toggleClass('hide', key.length <=0);

            if (key.length >= 3) {
                masterPrivateMethods.initializeSearchAutoComplete(key);
            } else {
                masterPrivateMethods.properties.searchQueue = [];
                $(masterPrivateMethods.controls.searchResultContainer).addClass("hide");
            }
        });
        
        $('.search-cntrl').on("click", function () {
            $(controls.searchBox).val("");
            masterPrivateMethods.properties.searchQueue = [];
            $(masterPrivateMethods.controls.searchResultContainer).addClass("hide");
            $('.search-cntrl').addClass('hide')
        })
        $('.trigger_ageverify_modal').on("click", function () {
            var is_ageVerified = $(this).data('ageverified') == 1;
            $('.confirm-age').each((index, elem) => {
                $(elem).data('ageverified', is_ageVerified)
            })
            master.showAgeVerificationModal(true)
        })
        $('.confirm-age').unbind("click").on("click", function () {
            var confirmation = $(this).data('confirm');
            var ageverified = $(this).data('ageverified');
            if (confirmation != ageverified)
                masterPrivateMethods.ageConfirmation(confirmation)
            else
                master.showAgeVerificationModal(false)

        })
        masterMethods.updateguestuser = function () {
            var locationDetails = address.returnGuestLocation();
            var guestUser = {}
            guestUser.GuestLatitude = locationDetails.latitude.toString();
            guestUser.GuestLongitude = locationDetails.longitude.toString();
            guestUser.GuestLocality = locationDetails.locality;
            console.log(guestUser)
            $('.search_address_input_wrap').addClass('processing')
         masterPrivateMethods.updateGuest(guestUser)
        }

        masterMethods.updateGuestUserByAutoDetect = function () {
            var locationDetails = address.returnGuestLocationAutoDetect();
            var guestUser = {}
            guestUser.GuestLatitude = locationDetails.latitude.toString();
            guestUser.GuestLongitude = locationDetails.longitude.toString();
            guestUser.GuestLocality = locationDetails.locality;
            
            console.log(guestUser)
            $('.search_address_input_wrap').addClass('processing')
            if (guestUser.GuestLocality) {
                $('.search_address_input_text').addClass('hide')
                $('.search_address_input_wrap').removeClass('processing')
                $('.search_address_input_wrap').fadeIn(1000, function () {
                    $(this).html(`<div><strong>Delivering to</strong> <br>${guestUser.GuestLocality}</div>`).fadeIn(200);
                });
            }
            setTimeout(function () {
                masterPrivateMethods.updateGuest(guestUser)
            }, 2000); 
        }
        $('#btn-update-guestAddr').unbind('click').on('click', function () {

            masterMethods.updateguestuser();
            //var locationDetails = address.returnGuestLocation();
            //var guestUser = {}
            //guestUser.GuestLatitude = locationDetails.latitude.toString();
            //guestUser.GuestLongitude = locationDetails.longitude.toString();
            //guestUser.GuestLocality = locationDetails.locality;
            //masterPrivateMethods.updateGuest(guestUser)
        });
        if ($(".rcstoreslider .swiper-slide").length < 5) {
            console.log($(".rcstoreslider .swiper-slide").length)
            $('.rcstoreslider .swiper-wrapper').addClass("justify-content-center");
        }
        //if (!masterMethods.properties.isHomePage) {
        //    $(controls.retailer).mouseenter(function () {
        //        masterPrivateMethods.getRetailerType($(this));
        //    });
        //}
    };

    masterMethods.initializePage = function () {
        // document.body.innerHTML = document.body.innerHTML.replace(/₹/g, masterMethods.properties.currency);
        masterMethods.getGeoLocation();
        masterMethods.events.initialize();
        var obj = $('.btnautoloadmore');
        if (obj && obj.length > 0 && !master.properties.isLoading && $(obj[0]).isInViewport()) {
            master.properties.isLoading = true;
            if (!location.href.includes('rc/more')) {
                master.loadMore(obj[0]);
            } else {
                for (let i = 0; i < 4; i++) {
                    master.loadMore(obj[i])
                }
            }

        }
        var pathElements = window.location.pathname.split('/');
        if (pathElements.length >= 2 && pathElements[1] == "bt") {
            $(".searchform").attr("action", "/bt/search/" + pathElements[2]);
        } else if (pathElements.length >= 2 && pathElements[1] == "st") {
            $(".searchform").attr("action", "/st/search/" + pathElements[2]);
        } else if (pathElements.length >= 2 && pathElements[1] == "rc") {
            $(".searchform").attr("action", "/rc/search/" + pathElements[2]);
        }

    };
    return masterMethods;
}();
$(function () {
    master.initializePage();
    $(window).scroll(function () {
        $('.btnautoloadmore').each(function (i, el) {

            if (!master.properties.isLoading && $(this).isInViewport()) {
                master.properties.isLoading = true;
                master.loadMore(this);
            }
        })
        var obj = $('.loadhomecategorylist');
        if (obj && obj.length > 0 && $(obj).isInViewport()) {
            master.properties.isLoading = true;
            master.loadHomeCategories(obj);
        }
    });
});
$.fn.isInViewport = function () {
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();

    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
};