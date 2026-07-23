var retMaster = function () {
    var retMasterMethods = {}, retMasterPrivateMethods = {};

    retMasterMethods.events = {};

    retMasterMethods.properties = {
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
        captchaKey: '',
        paymentGateway: '',
        countrycode:''
    };
    retMasterPrivateMethods.properties = {
        rzp: null,
        retailerTemplate: ''
    };
    retMasterMethods.url = {
        contactUs: '/contactus-submit',
        loadMore: '/loadmore',
        successPage: '',
        upgradeOtpUrl: '/api/Upgrade/UpgradeStore',
        upgradeUrl: '/api/Upgrade/UpgradeStore',
        upgradepayment: '/api/Upgrade/UpgradeStoreByPaymentId'
    };

    retMasterPrivateMethods.controls = {
        errorModal: 'error-modal',
        errorMessage: 'error-message',
        contactForm: '#frmContactSubmit',
        resultsModal: '#resultsModal',
        retailer: '.retailer-type-link',
        retailerTemplateContainer: 'retailer-link-template',
        retailerContainer: "#retailer-type-",
        upgradebtn: "#upgradebtn_submit",
        upgradeverifybtn: "#btnUpgradeVerifyOTP",
        upgradeotp: ".upgrade_otp",
        modalupgrade: "#modalupgrade",
        Supportview: ".btnsupportview",
    };

    retMasterMethods.clearForm = function (obj) {
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
        var frm = $(retMasterPrivateMethods.controls.contactForm);
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


    retMasterPrivateMethods.contactUs = function (self) {
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
            $(self).closest('#contactSubmit').hide();
            if (result && result.message && result.message != '')
                $(self).find('#contactResultText').html(result.message);
            else
                $(self).find('#contactResultText').html("Thank you for contacting us. We will get in touch with you soonest possible.");
            $(self).closest('#contactSubmit').siblings('#contactResult').show();
        };

        onError = function (data) {
            $(self).closest('#contactSubmit').hide();
            $(self).find('#contactResultText').html("Error on submitting contact form. Please contact support using phone number provided.");
            $(self).closest('#contactSubmit').siblings('#contactResult').show();
            console.log(data);
        };

        retMaster.ajax.JSONRequest(retMasterMethods.url.contactUs, 'POST', details, onSuccess, onError);

    }

    retMasterPrivateMethods.addToCart = function (self) {

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
        retMaster.ajax.JSONRequest(catalogMethods.url.addTocatalog, 'POST', details, onSuccess, onError);

    }

    retMasterMethods.showErrorMessage = function (message) {
        if (message !== null) {
            $(retMasterPrivateMethods.controls.errorMessage).text(message);
        }
        $(retMasterPrivateMethods.controls.errorModal).modal('show');
    };

    retMasterMethods.showResult = function (message, title, success) {
        if (message !== null) {
            if (success)
                $(retMasterPrivateMethods.controls.resultsModal).find('.resulthead').css('color', 'green');
            else
                $(retMasterPrivateMethods.controls.resultsModal).find('.resulthead').css('color', 'red');
            $(retMasterPrivateMethods.controls.resultsModal).find('.resulthead').html('<i class="fa ' + (success ? 'fa-check-circle' : 'fa-exclamation') + '" aria-hidden="true"></i>&nbsp;' + title);
            $(retMasterPrivateMethods.controls.resultsModal).find('.resultcontent').html(message);
            $(retMasterPrivateMethods.controls.resultsModal).modal('show');
        }
        //$(retMasterPrivateMethods.controls.errorModal).modal('show');
    };

    retMasterMethods.getGeoLocation = function () {
        getLocation = function (position) {
            retMasterMethods.properties.latitude = position.coords.latitude;
            retMasterMethods.properties.longitude = position.coords.longitude;
        };

        handleError = function (error) {
            retMasterMethods.properties.latitude = 0;
            retMasterMethods.properties.longitude = 0;
        }

        //if (navigator.geolocation) {
        //    navigator.geolocation.getCurrentPosition(getLocation, handleError);
        //}
    };

    retMasterMethods.showInstallPromotion = function () {
        if ($(window).width() < 769 && !retMasterMethods.properties.invokedPwa) {
            retMasterMethods.properties.invokedPwa = true;
            $('#PWAModal').modal('show');
        }

    };

    retMasterMethods.hideInstallPromotion = function () {
        $('#PWAModal').modal('hide');
    };

    retMasterPrivateMethods.getRetailerType = function (self) {
        var businessTypeId = self.data("id");
        onSuccess = function (result) {
            for (var i = 0; i < result.length; i++) {
                result[i].id = businessTypeId;
            }
            retMasterPrivateMethods.properties.retailerTemplate = retMasterPrivateMethods.properties.retailerTemplate === '' ? document.getElementById(retMasterPrivateMethods.controls.retailerTemplateContainer).innerHTML : retMasterPrivateMethods.properties.retailerTemplate;
            var template = Handlebars.compile(retMasterPrivateMethods.properties.retailerTemplate);
            var compiledTemplate = template(result);
            $(retMasterPrivateMethods.controls.retailerContainer + businessTypeId).html(compiledTemplate);
        };
        retMaster.ajax.JSONRequest('/retailerCategory/' + businessTypeId, 'GET', {}, onSuccess);
    }

    retMasterPrivateMethods.upgradeGetOtp = function (self, token) {
        //$(self).addClass('processing_loader');
        $(self).closest('div').addClass('processing_loader');
        var upgradetype = $(self).attr('upgradetype');
        onSuccess = function (data) {
            $(self).closest('div').removeClass('processing_loader');
            if (data && data.status === 'Success') {
                $(self).closest('div').removeClass('processing_loader');
                $(retMasterPrivateMethods.controls.upgradeotp).slideDown();
                $(self).addClass("disabled");

                $(retMasterPrivateMethods.controls.modalupgrade).find('div.modal-footer').hide();
                $(retMasterPrivateMethods.controls.modalupgrade).find('.upgrade_otp').show();
                $('#txtUpgradeTOP').focus();
                //$(".otpsent").show();

                //authenticationPrivateMethods.setResendTimer();
            } else if (data && data.addPaymentMethod === 1 && data.token != '' && retMasterMethods.properties.paymentGateway == 'revolut') {
                retMasterPrivateMethods.revolutPayment(data.token, data.orderId, data.id);
            } else if (data && data.addPaymentMethod === 1 && data.phishablekey != '' && retMasterMethods.properties.paymentGateway == 'Stripe') {
                $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                $('#modalpayment').modal({ backdrop: 'static' });
                retMasterPrivateMethods.stripepayment(data.phishablekey);
            } else {
                $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                //$('.error_msg_wrap').html(data.message);

                $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                showModal('Failure', data.message, false);
            }
        };

        onError = function (data) {
            console.log(data);
            $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
            showModal('Failure', 'There is a technical error. Please contact administrator for more details', false);
            //$(authenticationPrivateMethods.controls.mobile).addClass('error');
            $('.processing_loader').removeClass('processing_loader');
            //$(self).closest('div').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(retMasterMethods.url.upgradeOtpUrl, 'POST', { mobile: '', token: token, type: upgradetype }, onSuccess, onError);

    }
    retMasterPrivateMethods.loadPrice = function (data, token, subscId, priceId) {
        $('#row_subscription_price').html(''); $('#PlanModalPopup').find("#select_payment_title").html('');
        $('#PlanModalPopup').find('#selectPrice').unbind('click');
        var template = '<label class="rdiobox price_scheme">[BTNSELECT]<span class="scheme_amout_sect">[DISCOUNT_TOP]<span '
            +'class="scheme_amout_wrap"><span class="currency_symbols">[CURRENCYSYMBOL]</span><span class="scheme_amout">[AMOUNT]</span><span class="scheme_info">[TITLE]</span></span>[DISCOUNT_BOTTOM]</span></label>';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                //if (i == 0) {
                //    $('#PlanModalPopup').find("#select_payment_title").html(data[i].Name);
                //    var selectedPrice = retMasterMethods.properties.currency + '' + data[i].PricePerCycle + '/ ' + data[i].BillingCycle;
                //    $('#PlanModalPopup').find('.you_llb_paying').text(selectedPrice);
                //}
                var templatecontent = template.replace('[TITLE]', data[i].BillingCycle).replace('[AMOUNT]', data[i].PricePerCycle).replace('[PRICEID]', data[i].PriceId)
                    .replace('[CURRENCYSYMBOL]', retMasterMethods.properties.currency);

                if (data[i].PriceId == data[i].CurPriceId)
                    templatecontent = templatecontent.replace('[DISCOUNT_TOP]', '<span class="scheme_offer"><span class="cont_mnt">Active</span></span>')
                        .replace('[BTNSELECT]', '').replace('[DISCOUNT_BOTTOM]', '<span class="scheme_note">Your <strong>Current Plan</strong></span>');
                else
                    templatecontent = templatecontent.replace('[DISCOUNT_TOP]', (data[i].Discount > 0 ? '<span class="scheme_offer"><span class="cout">2</span><span class="cont_mnt">Save Months</span></span>' : ''))
                        .replace('[DISCOUNT_BOTTOM]', (data[i].Discount > 0 ? '<span class="scheme_note">Pay for an year and Get <strong>2 months FREE</strong></span>' : ''))
                        .replace('[BTNSELECT]', '<input type="radio" name="rdselprice" priceid="' + data[i].PriceId +'" class="rdselprice">');

                $('#row_subscription_price').append(templatecontent);

            }
            $('#PlanModalPopup').find('#selectPrice').unbind('click').on('click', function () {
                var priceid = $('#row_subscription_price').find('input[type=radio]:checked').attr('priceid');
                if (!priceid || priceid == '') {
                    alert("Error: invalid selection. Please select the price plan");
                    return;
                }
                var refcode = $('#PlanModalPopup').find('#txtSubscriptionRefCode').val();
                $('#modalpayment').modal({ backdrop: 'static' });
                retMasterPrivateMethods.stripepayment(token, subscId, priceid, refcode);
                $('#PlanModalPopup').modal('hide');
            });
            $('#row_subscription_price').find('label.rdiobox').unbind('click').on('click', function () {
                if ($(this).find('input:radio[name=rdselprice]').length > 0) {
                    var selectedPrice = $(this).find('.currency_symbols').text() + $(this).find('.scheme_amout').text() + '/ ' + $(this).find('.scheme_info').text();
                    $('#PlanModalPopup').find('.you_llb_paying').text(selectedPrice);
                }
            });
            $('#row_subscription_price').find('label.rdiobox input:radio[name=rdselprice]:first').prop("checked", true).trigger("click");
        }
        else {
            $('#row_subscription_price').html(template.replace('[TITLE]', 'No data available').replace('[AMOUNT]', ''));
        }

        $('#PlanModalPopup').modal({ backdrop: 'static' });
    }
    retMasterPrivateMethods.subscribePack = function (subscId, token, priceId) {
        if (!subscId || subscId == '') { 
            showModal('Failure', 'The subscription is not available at the moment. Please contact support for more details', false);
            $('.processing_loader').removeClass('processing_loader');
            return;
        }

        onSuccess = function (data) {
            $('.processing_loader').removeClass('processing_loader');
            if (data && data.token != '' && data.pg == 'revolut') {
                retMasterPrivateMethods.revolutPayment(data.token, data.orderId, data.id);
            } else if (data && data.phishablekey != '' && data.pg == 'Stripe' && data.subcriptionPrices != null) {
                retMasterPrivateMethods.loadPrice(data.subcriptionPrices, data.phishablekey, subscId, priceId);
            } else if (data && data.subcriptionPrices != null) {
                retMasterPrivateMethods.loadPrice(data.subcriptionPrices, data.phishablekey, subscId, priceId);
            } else {
                $('.processing_loader').removeClass('processing_loader');
                showModal('Failure', data.message, false);
            }
        };

        onError = function (data) {
            $('.processing_loader').removeClass('processing_loader');
            showModal('Failure', 'There is a technical error. Please contact administrator for more details', false);
        };
        retMaster.ajax.JSONRequest('/api/Upgrade/EnableSubscription', 'POST', { priceId: priceId, subscId: subscId, token: token }, onSuccess, onError);

    }

    retMasterPrivateMethods.upgradeVerifyOtp = function (self, token) {
        //$(self).addClass('processing_loader');
        $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').next('label').remove();
        if ($(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').val() == '') {
            $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').addClass('error');
            $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').next('label').remove();
            $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').after('<label class="tx-danger">Invalid OTP</label>');
            $(self).removeClass('processing_loader');
            return false;
        }
        $(self).closest('div').addClass('processing_loader');

        onSuccess = function (data) {
            if (data && data.status === 'Success') {
                $(self).closest('div').removeClass('processing_loader');
                $(retMasterPrivateMethods.controls.upgradeotp).slideDown();
                $(self).addClass("disabled");
                $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                showModal('Thank you.', 'You have upgraded your Grozeo account from Starter to Growth plan with effect from today. Please go through the benefits of upgraded account in detail here.<br/><br/>You will be charged a subscription fee on a monthly basis from today. You would get the monthly summary of business through Grozeo which will also include the details of subscription charges.<br/><br/>Happy Selling!', true, '/Tenant/?upgrade');
            } else {
                $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                //$('.error_msg_wrap').html(data.message);
                //$(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                //showModal('Failure', data.message??'Execution failed.', false, '/');
                $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').addClass('error');
                $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').next('label').remove();
                $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').after('<label class="tx-danger">' + data.message ?? 'Execution failed.' + '</label>');
            }
        };

        onError = function (data) {
            console.log(data);
            $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
            showModal('Failure', 'There is a technical error. Please contact administrator for more details', false);
            //$(authenticationPrivateMethods.controls.mobile).addClass('error');
            $('.processing_loader').removeClass('processing_loader');
            //$(self).closest('div').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(retMasterMethods.url.upgradeOtpUrl, 'POST', { otp: $(retMasterPrivateMethods.controls.modalupgrade).find('#txtUpgradeTOP').val(), token: token }, onSuccess, onError);

    }
    retMasterPrivateMethods.supportviewbutton = function (event) {
        //event.preventDefault();
        $(".dynamical_support").css("display", "none");
        $(".showclass").css("display", "block");
        $('.input_search_box').hide();
        $('.createsupprot').hide();
    }

    retMasterMethods.ajax = {
        postJSON: function (url, form, success, error) {
            var customError = function (er) {
                console.log(er);
            }
            if (error !== 'undefined' && error !== null)
                customError = error;
            $.ajax({
                type: 'POST',
                url: url,
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
                url: url,
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
                url: url,
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
                url: url,
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
                url: url,
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
                url: url,
                type: type,
                cache: false,
                data: data,
                success: success,
                error: customError
            });
        }
    };

    retMasterPrivateMethods.onUpgradePayment = function (obj) {
        onRSuccess = function (data) {
            if (data && data.status === 'Success') {
                Toastify({ text: 'Account upgraded successfully!!', duration: 5000, stopOnFocus: true, className: 'success', }).showToast();
                window.location.reload(true);
            } else {
                $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                showModal('Failure', data.message, false);
            }
        };

        onRError = function (data) {
            console.log(data);
            $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
            showModal('Failure', 'There is a technical error. Please contact administrator for more details', false);
            $('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(retMasterMethods.url.upgradepayment, 'POST', obj, onRSuccess, onRError);
    };

    retMasterPrivateMethods.revolutPayment = function (token, orderId, id) {
        RevolutCheckout(token).then(function (instance) {
            instance.payWithPopup({
                onSuccess() {
                    //alert("Your payment was completed.");
                    retMasterPrivateMethods.onUpgradePayment({ id: id, orderId: orderId });
                },
                onError(message) {
                    $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                    $(retMasterPrivateMethods.controls.modalupgrade).modal('hide');
                    showModal('Failure', 'Payment method failed', false);

                },
            });
        });
    }

    retMasterPrivateMethods.stripepayment = function (phishablekey, subscId, price_Id, refcode) {
        var stripe = Stripe(phishablekey);
        var elements = stripe.elements();
        //var style = {
        //    base: {
        //        color: "#32325d",
        //        fontFamily: 'Arial, sans-serif',
        //        fontSmoothing: "antialiased",
        //        fontSize: "16px",
        //        "::placeholder": {
        //            color: "#32325d"
        //        }
        //    },
        //    invalid: {
        //        fontFamily: 'Arial, sans-serif',
        //        color: "#fa755a",
        //        iconColor: "#fa755a"
        //    }
        //};
        var card = elements.create("card", {
            hidePostalCode: true,
            //style: style
        });
        card.mount("#upgrademodelcontent");
        card.on("change", function (event) {
            // Disable the Pay button if there are no card details in the Element
            //document.querySelector("button").disabled = event.empty;
            document.querySelector("#lblPaymentResult").textContent = event.error ? event.error.message : "";
        });

        var payWithCard = function (stripe, card, subscId, price_Id, refcode) {
            stripe.createPaymentMethod({
                    type: 'card',
                    card: card
            }).then(function (result) {
                if (result.error) {
                    // Show error to your customer
                    alert("Error - " + result.error.message);
                } else {
                    // The payment succeeded!
                    retMasterPrivateMethods.onUpgradePayment({ id: result.paymentMethod, orderId: -1, priceId: price_Id, subscriptionId: subscId, refCode: refcode });
                }

            });
        };

        $('#upgradebtn_savecard').unbind('click').on('click', function () {
            payWithCard(stripe, card, subscId, price_Id, refcode);
            return;
        });

    }



    retMasterMethods.events.initialize = function () {
        var controls = retMasterPrivateMethods.controls;

        $(controls.contactForm).unbind('submit').on('submit', function (event) {
            event.preventDefault();
            retMasterPrivateMethods.contactUs($(this));
        });
        $('.btnloadmore').unbind('click').on('click', function (event) {
            if (!retMasterMethods.properties.isLoading && $(this).isInViewport()) {
                retMasterMethods.properties.isLoading = true;
                retMasterMethods.loadMore(this);
            }
        });
        $(retMasterPrivateMethods.controls.Supportview).unbind('click').on('click', function (event) {
            retMasterPrivateMethods.supportviewbutton($(this))
        });
        if (!retMasterMethods.properties.isHomePage) {
            $(controls.retailer).mouseenter(function () {
                retMasterPrivateMethods.getRetailerType($(this));
            });
        }

        $('.subscribe_btn_action').unbind('click').on('click', function (event) {
            event.preventDefault();
            $(this).closest('div').addClass('processing_loader');
            var priceId = $(this).attr('priceid');
            var subscId = $(this).attr('sbr_id');
            
            grecaptcha.ready(function () {
                grecaptcha.execute(retMasterMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    retMasterPrivateMethods.subscribePack(subscId, token, priceId);
                    return false;
                });
            });
            return false;
        });


    };

    retMasterMethods.initializePage = function () {
        // document.body.innerHTML = document.body.innerHTML.replace(/₹/g, retMasterMethods.properties.currency);
        retMasterMethods.getGeoLocation();
        retMasterMethods.events.initialize();
        var obj = $('.btnautoloadmore');
        if (obj && obj.length > 0 && !retMaster.properties.isLoading && $(obj[0]).isInViewport()) {
            retMaster.properties.isLoading = true;
            retMaster.loadMore(obj[0]);
        }
        var pathElements = window.location.pathname.split('/');
        if (pathElements.length >= 2 && pathElements[1] == "bt") {
            $(".searchform").attr("action", "/bt/search/" + pathElements[2]);
        } else if (pathElements.length >= 2 && pathElements[1] == "st") {
            $(".searchform").attr("action", "/st/search/" + pathElements[2]);
        } else if (pathElements.length >= 2 && pathElements[1] == "rc") {
            $(".searchform").attr("action", "/rc/search/" + pathElements[2]);
        }

        $(retMasterPrivateMethods.controls.upgradebtn).unbind('click').on('click', function (event) {
            event.preventDefault();
            var self = $(this);
            grecaptcha.ready(function () {
                grecaptcha.execute(retMasterMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    retMasterPrivateMethods.upgradeGetOtp(self, token);
                });
            });
        });

        // 
        $(retMasterPrivateMethods.controls.upgradeverifybtn).unbind('click').on('click', function (event) {
            event.preventDefault();
            var self = $(this);
            grecaptcha.ready(function () {
                grecaptcha.execute(retMasterMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    retMasterPrivateMethods.upgradeVerifyOtp(self, token);
                });
            });
        });



        $(retMasterPrivateMethods.controls.modalupgrade).on('hidden.bs.modal', function (e) {
            $(retMasterPrivateMethods.controls.modalupgrade).find('.upgrade_otp').hide();
            $(retMasterPrivateMethods.controls.modalupgrade).find('div.modal-footer').show();
            $(retMasterPrivateMethods.controls.modalupgrade).find("div.modal-footer .disabled").removeClass("disabled");
        });
        $(retMasterPrivateMethods.controls.modalupgrade).on('shown.bs.modal', function (e) {
            $(retMasterPrivateMethods.controls.modalupgrade).find('.upgrade_otp').hide();
            $(retMasterPrivateMethods.controls.modalupgrade).find('div.modal-footer').show();
            $(retMasterPrivateMethods.controls.modalupgrade).find("div.modal-footer .disabled").removeClass("disabled");
        });


        
        const input = $(".PhoneNumbercode");
        console.log(input)
        console.log($('#countrycode').val())
        let countryCode = retMaster.properties.countrycode === "UK"
            ? "gb"
            : retMaster.properties.countrycode;
        var allowDropdown = retMaster.properties.countrycode === "IN" ? false : true;
        input.intlTelInput({
            autoHideDialCode: true,
            autoPlaceholder: "aggressive",
            dropdownContainer: document.body,
            formatOnDisplay: true,
            hiddenInput: "full_number",
            initialCountry: countryCode,  // Pre-select India
            nationalMode: true,
            placeholderNumberType: "MOBILE",
            preferredCountries: [countryCode],
            separateDialCode: true,
            showSelectedDialCode: true,
            showFlags: true,
            allowDropdown: allowDropdown,
        });
        document.querySelectorAll(".restrictmobile").forEach(element => {
            element.addEventListener("input", function (event) {
                const inputValue = event.target.value;
                // Check if the first character is "0"
                if (inputValue.charAt(0) === "0") {
                    // Trim the leading "0" from the input value
                    event.target.value = inputValue.substring(1);
                }
            });
        });
    };

    return retMasterMethods;
}();
$(function () {
    retMaster.initializePage();
    $.fn.isInViewport = function () {
        var elementTop = $(this).offset().top;
        var elementBottom = elementTop + $(this).outerHeight();

        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    };
    $(window).scroll(function () {
        $('.btnautoloadmore').each(function (i, el) {

            if (!retMaster.properties.isLoading && $(this).isInViewport()) {
                retMaster.properties.isLoading = true;
                //$(this).hide();
                //alert('load more');
                retMaster.loadMore(this);
                //$(this).addClass('test');
                //console.log('content block is in viewport.', $(this))
            }
        })
        var obj = $('.loadhomecategorylist');
        if (obj && obj.length > 0 && $(obj).isInViewport()) {
            retMaster.properties.isLoading = true;
            retMaster.loadHomeCategories(obj);
        }
    });

    // Initialize popover
    $('[data-toggle="popover"]').popover({
        trigger: 'focus'
    });


});