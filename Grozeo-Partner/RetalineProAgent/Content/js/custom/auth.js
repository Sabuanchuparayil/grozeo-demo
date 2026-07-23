var authentication = function () {
    var authenticationMethods = {}, authenticationPrivateMethods = {};

    authenticationMethods.events = {};

    authenticationMethods.properties = {
        captchaKey: '',
        mapTriggered: false,
        countryPhoneCode: '+91',
        mobilepatern: null,
        showInvitationCode: false,
    };
    authenticationPrivateMethods.properties = {
        isOtpTriggered: false,
        tmrCountdownId: null
    };
    authenticationMethods.url = {
        getOtp: '',
        verifyOtp: '',
        signUp: '',
        redirectUrl: '',
        resendOtp: '',
        captchaVerification: ''
    };

    authenticationPrivateMethods.controls = {
        mobile: '#txtSignupMobileNumber',
        invitationcode: '#txtInvitationCode',
        otpFirst: '#txtOTP',
        otpSecond: '#txt-otp-second',
        otpThird: '#txt-otp-third',
        otpFourth: '#txt-otp-fourth',
        frmmobilelogin: '#frmmobilelogin',
        mobileloginbtn: '#btn_Submit_mobile',
        invcodebtn: '#btn_Submit_invitationcode',
        dvmobileotp: '.otp_toggle',
        loginClose: '#loginclose',
        mobileControls: '.mobile-control',
        otpControls: '.otp-control',
        loginModal: '#login-modal',
        mobileSignUp: '#txt-signup-mobile',
        nameSignUp: '#txt-signup-name',
        emailSignUp: '#txt-signup-email',
        showSignUp: '.link-show-signup',
        frmSignup: '#frmsignup',
        frmmobileotp: '#frmmobileotp',
        loginModal: '#login-modal',
        loginModalTitle: '#h-loginHead',
        signupControls: '.create_account',
        labelMobileNumber: '#lblMobileNum',
        verifyOtp: "#btn-verify-otp",
        otpBoxes: ".otp-txt-boxes",
        changeMobile: '#lbl-change-mobile',
        invalidOtp: '#invalid-otp',
        signupError: '#error-signup'
    };

    authenticationPrivateMethods.isValidMobile = function () {
        $('.error_msg_wrap').html('');
        var isValid = true;
        var mobile = $(authenticationPrivateMethods.controls.mobile).val();//.replace('+91 ', '');
        if (mobile.startsWith(authenticationMethods.properties.countryPhoneCode))
            mobile = mobile.substring(authenticationMethods.properties.countryPhoneCode.length).replace(/^\s+|\s+$/gm, '');
        //var patern = /^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/;

        if (authenticationMethods.properties.mobilepatern == null || authenticationMethods.properties.mobilepatern.test(mobile)) {//(patern.test(mobile)) {
            return true;
        }
        else {
            $('.error_msg_wrap').html('Invalid number. Please enter a valid mobile number.');
            $('.processing_loader').removeClass('processing_loader');
            //$(authenticationPrivateMethods.controls.mobile).parent().append("<div class='Error'>Please enter a valid mobile number.</div>");
            return false;
        }
        return isValid;
    };

    authenticationPrivateMethods.isValidOTP = function () {
        var controls = authenticationPrivateMethods.controls;
        var isValid = true;
        $(controls.loginModal).find(".Error").remove();
        var otp = $(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        var patern = /^\d{4}$/;

        if (patern.test(otp)) {
            return true;
        }
        else {
            $(controls.otpFirst).parent().append("<div class='Error'>Please enter valid OTP.</div>");
            return false;
        }
        return isValid;
    };

    authenticationPrivateMethods.getOtp = function (self, token) {
        //$(self).addClass('processing_loader');
        $(self).closest('div').addClass('processing_loader');
        if (!authenticationPrivateMethods.isValidMobile()) {
            $(authenticationPrivateMethods.controls.mobile).addClass('error');
            $(self).removeClass('processing_loader');
            return false;
        }

        var mobile = $(authenticationPrivateMethods.controls.mobile).val().replace('+91 ', '');
        var invitationcode = $(authenticationPrivateMethods.controls.invitationcode).val();

        onSuccess = function (data) {
            $(authenticationPrivateMethods.controls.mobile).removeClass('error');
            $(authenticationPrivateMethods.controls.invitationcode).removeClass('error');
            if (data && data.status === 'Success') {
                $('.dvmobilenum').show();
                $('.dvinvcode').hide();

                $(self).closest('div').removeClass('processing_loader');
                $(authenticationPrivateMethods.controls.dvmobileotp).slideDown();

                $(self).addClass("disabled");

                //$(authenticationPrivateMethods.controls.mobile).attr('readonly', true);
                $(authenticationPrivateMethods.controls.mobile).on('click', function () { $(this).blur(); });
                //$('#txtPhone').val($(authenticationPrivateMethods.controls.mobile).val());
                //$('#txtPhone').show();
                //$('#txtPhone').prop('disabled', true);
                //$(authenticationPrivateMethods.controls.mobile).hide();
                //$(authenticationPrivateMethods.controls.mobile).prop('disabled', true);

                $(".otpsent").show();
                //$(authenticationPrivateMethods.controls.mobileControls).addClass("hide");
                //$(authenticationPrivateMethods.controls.otpControls).removeClass("hide");
                authenticationPrivateMethods.properties.isOtpTriggered = true;
                var html = "OTP sent to " + $(authenticationPrivateMethods.controls.mobile).val().replace(/^(\d{2})(\d{5})(\d{3}).*/, "$1XXXXX$3");
                $(authenticationPrivateMethods.controls.labelMobileNumber).html(html);
                //$(authenticationPrivateMethods.controls.loginModalTitle).html("Verification Code");
                $(authenticationPrivateMethods.controls.otpFirst).focus();
                authenticationPrivateMethods.setResendTimer();
            }
            else if (data && data.result === -2) {
                $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                if ($('.dvinvcode').is(":hidden") && authenticationMethods.properties.showInvitationCode) {
                    $('.dvmobilenum').hide();
                    $('.dvinvcode').show();
                    return false;
                }
                else {
                    $(authenticationPrivateMethods.controls.invitationcode).addClass('error');
                    $('.error_msg_wrap').html(data.message);

                }
            }
            else {
                $(authenticationPrivateMethods.controls.mobile).addClass('error');
                //$(self).closest('div').removeClass('processing_loader');
                $('.processing_loader').each(function () { $(this).removeClass('processing_loader'); });
                $('.error_msg_wrap').html(data.message);
                //$(authenticationPrivateMethods.controls.loginModal).modal('hide');
                //retMaster.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            }
        };

        onError = function (data) {
            console.log(data);
            //retMaster.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            $(authenticationPrivateMethods.controls.mobile).addClass('error');
            $('.processing_loader').removeClass('processing_loader');
            //$(self).closest('div').removeClass('processing_loader');
            $('.error_msg_wrap').html('There is a technical error. Please contact administrator for more details');
        };
        retMaster.ajax.JSONRequest(authenticationMethods.url.getOtp, 'POST', { mobile: mobile, token: token, invitationcode: invitationcode }, onSuccess, onError);

    }

    authenticationPrivateMethods.setResendTimer = function () {
        //$("#countdown").show();
        if (authenticationPrivateMethods.properties.tmrCountdownId)
            clearInterval(authenticationPrivateMethods.properties.tmrCountdownId);
        //var btn = $('#resend');
        var timeout = 60;
        $("#resend-trigger").unbind("click");
        //$("#resend-trigger").css('visibility', 'hidden');
        authenticationPrivateMethods.properties.tmrCountdownId = setInterval(showCountDown, 1000);
        function showCountDown() {
            if (timeout == 0) {
                //$("#countdown").hide();
                clearInterval(authenticationPrivateMethods.properties.tmrCountdownId);
                //$(btn).html("Didn't receive the code? <span class='resent' id='resend-trigger'>Resend</span>");
                $("#resend-trigger").html('Resend OTP');
                $("#resend-trigger").unbind("click").on("click", function () {
                    grecaptcha.ready(function () {
                        grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                            //authenticationPrivateMethods.verifyToken(self, token);
                            authenticationPrivateMethods.getOtp($(authenticationPrivateMethods.controls.mobileloginbtn), token);
                        });
                    });
                });
            } else {
                timeout--;
                $("#resend-trigger").html('Resend in ' + timeout + ' seconds');
                $("#resend-trigger").prop('disabled', true);
                //$("#countdown").html('Resend in ' + timeout + ' seconds');
                //$('#resend').prop('disabled', true);
                //$("#resend-trigger").css('visibility', 'hidden');//.prop('disabled', true);
            }

        }

    }

    authenticationPrivateMethods.verifyOtp = function (self) {
        var controls = authenticationPrivateMethods.controls;
        $(self).addClass('processing_loader');
        if (!authenticationPrivateMethods.isValidOTP()) {
            $(self).removeClass('processing_loader');
            return false;
        }

        var details = {};
        details.mobile = $(controls.mobile).val().replace('+91 ', '');
        details.otp = $(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        $(controls.mobileSignUp).val(details.mobile);

        onSuccess = function (result) {

            if (result && result.data && result.data.is_registered) {
                $(controls.invalidOtp).addClass('hide');
                if (authenticationMethods.url.redirectUrl != '')
                    window.location.href = authenticationMethods.url.redirectUrl;
                else if (window.location.pathname == '/')
                    window.location.reload(true);
                else
                    window.location.replace("/");
            }

            else if (result && result.data && result.data.is_registered == false) {
                $(controls.invalidOtp).addClass('hide');
                $(self).removeClass('processing_loader');
                $(controls.loginModalTitle).html("Create Account");
                $(controls.mobileControls).addClass("hide");
                $(controls.otpControls).addClass("hide");
                $(controls.signupControls).removeClass("hide");

            }
            else {
                $(self).removeClass('processing_loader');
                $(controls.otp).val('');
                $(controls.invalidOtp).removeClass('hide');
            }
        };
        onError = function (data, polo, marco) {
            console.log(data);
            retMaster.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
        };

        retMaster.ajax.JSONRequest(authenticationMethods.url.verifyOtp, 'POST', details, onSuccess, onError);
    }

    authenticationPrivateMethods.signUp = function () {

        address.properties.signupEmail = $(authenticationPrivateMethods.controls.emailSignUp).val();
        address.properties.signupName = $(authenticationPrivateMethods.controls.nameSignUp).val();
        address.properties.signupPhone = $(authenticationPrivateMethods.controls.mobile).val().replace('+91 ', '');

        if (address.properties.signupEmail.trimEnd() != '' && address.properties.signupName.trimEnd() != '') {
            address.triggerAddAddress(true);
            $(authenticationPrivateMethods.controls.loginModal).modal('hide');
        } else {
            $(authenticationPrivateMethods.controls.signupError).removeClass("hide");
        }

    }

    authenticationPrivateMethods.focusOnOtpBox = function (self) {
        var position = self.data("next");
        if (position == "submit") {
            $(authenticationPrivateMethods.controls.verifyOtp).focus();
        } else {
            $("#txt-otp-" + position).focus();
        }
    }

    authenticationPrivateMethods.bringBackMobileModal = function () {
        $(authenticationPrivateMethods.controls.otpControls).addClass("hide");
        $(authenticationPrivateMethods.controls.mobileControls).removeClass("hide");
        authenticationPrivateMethods.properties.isOtpTriggered = false;
    }

    authenticationPrivateMethods.verifyToken = function (self, token) {

        var url = authenticationMethods.url.captchaVerification + "?token=" + token;
        onSuccess = function (result) {
            if (result.success) {
                authenticationPrivateMethods.getOtp(self);
            } else {
                alert("Something went wrong. Please try again!!.")
            }
        };
        retMaster.ajax.JSONRequest(url, 'POST', {}, onSuccess, onError);
    };


    authenticationMethods.initializePage = function () {
        authenticationMethods.events.initialize();
    };

    authenticationMethods.events.initialize = function () {
        var controls = authenticationPrivateMethods.controls;

        $(controls.mobileloginbtn).unbind('click').on('click', function (event) {
            event.preventDefault();
            if (authenticationMethods.properties.showInvitationCode)
                $(authenticationPrivateMethods.controls.invitationcode).val('');

            var self = $(this);

            grecaptcha.ready(function () {
                grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    //authenticationPrivateMethods.verifyToken(self, token);
                    authenticationPrivateMethods.getOtp(self, token);
                });
            });
            //authenticationPrivateMethods.getOtp(self);
        });
        $(controls.invcodebtn).unbind('click').on('click', function (event) {
            event.preventDefault();
            var self = $(this);
            grecaptcha.ready(function () {
                grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    //authenticationPrivateMethods.verifyToken(self, token);
                    authenticationPrivateMethods.getOtp(self, token);
                });
            });
            //authenticationPrivateMethods.getOtp(self);
        });



        $(controls.frmmobileotp).unbind('submit').on('submit', function (event) {
            event.preventDefault();
            authenticationPrivateMethods.verifyOtp($(this));
        });

        $(controls.frmSignup).unbind('submit').on('submit', function (event) {
            event.preventDefault();
            authenticationPrivateMethods.signUp();
        });

        //$(controls.mobile).inputmask({
        //    placeholder: '',
        //    clearMaskOnLostFocus: false,
        //    colorMask: true,
        //    mask: [{ "mask": "##########" }],
        //    greedy: false,
        //    definitions: { '#': { validator: "[0-9]", cardinality: 1 } }
        //});

        //$(controls.otp).inputmask({
        //    placeholder: '',
        //    clearMaskOnLostFocus: false,
        //    colorMask: true,
        //    mask: [{ "mask": "####" }],
        //    greedy: false,
        //    definitions: { '#': { validator: "[0-9]", cardinality: 1 } }
        //});

        //$(controls.emailSignUp).inputmask({
        //    mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,6}][.*{1,2}]",
        //    greedy: false,
        //    onBeforePaste: function (pastedValue, opts) {
        //        pastedValue = pastedValue.toLowerCase();
        //        return pastedValue.replace("mailto:", "");
        //    },
        //    definitions: {
        //        '*': {
        //            validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
        //            casing: "lower"
        //        }
        //    }
        //});

        $(controls.otpBoxes).unbind("keyup").on("keyup", function () {
            authenticationPrivateMethods.focusOnOtpBox($(this))
        });

        $(controls.changeMobile).unbind("click").on("click", function () {
            authenticationPrivateMethods.bringBackMobileModal();
        });
    };



    return authenticationMethods;
}();
$(function () {
    authentication.initializePage();
});