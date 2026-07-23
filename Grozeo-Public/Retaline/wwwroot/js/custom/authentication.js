var authentication = function () {
    var authenticationMethods = {}, authenticationPrivateMethods = {};

    authenticationMethods.events = {};

    authenticationMethods.properties = {
        captchaKey: '',
        isCaptchaEnabled: true,
        fedAuthFLKey: '',
        fedGoogleAuthClientId: '',
        newemail: '',
        newMobile: '',
        signupEmail: '',
        signupName: '',
        signupPhone: '',
        password: ''
    };
    authenticationPrivateMethods.properties = {
        isOtpTriggered: false,
        tmrCountdownId: null,
        verifyPsw: 'Authentication/VerifyPsw',
        updateStatusHandler: '/UpdateVerifiedStatus',
        countryCodeList: {
            "UK": "GB",
            "UAE": "AE"
        }
    };
    authenticationMethods.url = {
        getOtp: '',
        verifyOtp: '',
        signUp: '',
        redirectUrl: '',
        resendOtp: '',
        getEmailOtp: '/login/validateemail',
        fedRedirectUrl: ''
    };
    authenticationMethods.messages = {
        phoneExistsAlert:'This phone number is already registered. Please use a different number.',
        emailExistsAlert: 'This email is already registered. Please use a different email.',
    }

    authenticationPrivateMethods.controls = {
        addressModal: '#address-modal',
        mobile: '#txt-mobile',
        otpsingle: '#txtLoginOTP',
        otpFirst: '#txt-otp-first',
        otpSecond: '#txt-otp-second',
        otpThird: '#txt-otp-third',
        otpFourth: '#txt-otp-fourth',
        frmmobilelogin: '#frmmobilelogin',
        loginClose: '#loginclose',
        mobileControls: '.mobile-control',
        otpControls: '.otp-control',
        loginModal: '#login-modal',
        mobileSignUp: '#txt-signup-mobile',
        nameSignUp: '#txt-signup-name',
        emailSignUp: '#txt-signup-email',
        passwordSignup: '#txt-mobile-password',
        flMobileSignUp: '#txt-mobile-acc-create',
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
        signupError: '#error-signup',
        emailLoginSec: '.emaillogin_sec',
        emailLoginBtn: '#emailLogin-btn',
        mobileLogin: '#mobileLogin',
        instagramLogin: '#lbtninstalogin',
        facebookLogin: '#lbtnFBlogin',
        emaillogin: '#emaillogin',
        emailloginInput: '.emaillogin-ip',
        email: '#txt-email',
        labelEmail: '#lbl-email',
        emailPassword: '.enterpassword_email_container',
        enterpassword_mobile_container: '.enterpassword_mobile_container',
        changeEmailPwd: '#lbl-email-pwd',
        labelPassword: '#lbl-password',
        loginWithOtpLabel: ".loginWithOtpLabel",
        editBtn: '.edit_btn',
        changeEmail: '#lbl-change-email',
        changePhonePwd: '#lbl-change-mobile-pwd',
        frmmobileloginpsw: '#enterpassword_mobile',
        enterpassword_control: ".enterpassword_control",
        verifyValidation: '.verify_validation',
        changeTarget: '.change_target',
        intlTeleInput: '.intl_tele_input'

    };

    authenticationPrivateMethods.isValidMobile = function (newMobile) {
        $(authenticationPrivateMethods.controls.loginModal).find(".Error").remove();

        var mobile = newMobile ? newMobile : $(authenticationPrivateMethods.controls.mobile).val().replace(/\D/g, "");
        let code = master.properties.countryCode;
        var countryCodeList = authenticationPrivateMethods.properties.countryCodeList;
        var countryCode = countryCodeList[code] || code;
        try {
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(mobile, countryCode);
            if (phoneNumber && phoneNumber.isValid()) {
                return true;
            }
        } catch (error) {
            console.error("Phone number parsing error:", error);
        }

        // Show error if validation fails
       // $(authenticationPrivateMethods.controls.mobile).parent().append("<div class='Error'>Please enter a valid mobile number.</div>");
        $('.invalid-feedback.invalid-phoone').show()
        //$('#frmmobilelogin').addClass('was-validated') 

        return false;
    };
    authenticationPrivateMethods.isValidEmail = function (emailProp) {
        var isValid = true;
        $(authenticationPrivateMethods.controls.loginModal).find(".Error").remove();
        var email = emailProp ? emailProp : $(authenticationPrivateMethods.controls.email).val(); // Fetch the value of the email input field
        var pattern = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

        if (pattern.test(email)) { // Use test method on the regex pattern
            return true;
        } else {
            $(authenticationPrivateMethods.controls.email).parent().append("<div class='Error'>Please enter a valid Email.</div>");
            return false;
        }
        return isValid;
    };

    authenticationPrivateMethods.isValidOTP = function (otpProp) {
        var controls = authenticationPrivateMethods.controls;
        var isValid = true;
        $(controls.loginModal).find(".Error").remove();
        var otp = otpProp ? otpProp : $(controls.otpsingle).val();//$(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        var pattern = /^\d{4}$/;

        if (pattern.test(otp)) {
            return true;
        }
        else {
            //$(controls.otpFirst).parent().append("<div class='Error'>Please enter valid OTP.</div>");
            // $(controls.otpsingle).parent().append("<div class='invalid-feedback invalid-otp' style='display:block;' >Please enter valid OTP.</div>");
            $('.invalid-feedback.invalid-otp').show()
            $('.invalid-feedback.invalid-otp').html("<div class='invalid-feedback invalid-otp' style='display:block;'>Please enter valid OTP.</div>")
            return false;
        }
        return isValid;
    };



    authenticationPrivateMethods.getOtp = function (self, token) {
        var url = authenticationMethods.url.getOtp + "?token=" + token;
        if ($(self).find('button.btn.mobilevalidationbtn').hasClass('processing'))
            return false;

        if (!authenticationPrivateMethods.isValidMobile()) {
          // $(self).removeClass('processing');
            $(self).find('button.btn.mobilevalidationbtn').removeClass('processing');
            $(self).find('button.btn.mobilevalidationbtn').attr('disabled', false);

            return false;
        }
        $(self).find('button.btn.mobilevalidationbtn').addClass('processing');
        $(self).find('button.btn.mobilevalidationbtn').attr('disabled', true);

        authenticationPrivateMethods.resetValidations()


        //$('#frmmobilelogin').removeClass('was-validated') 
        var mobile = $(authenticationPrivateMethods.controls.mobile).val().replace(/\D/g, "");;
        var selectedCountryCode = $('.iti__selected-dial-code').html();
        fullPhone = selectedCountryCode + mobile
        var usepsw = ($(self).attr('usepsw') == '1' ? 1 : 0);
        onSuccess = function (data) {
            if (data && data.status === 'ok') {
                //$(self).removeClass('processing');
                $(self).find('button.btn.mobilevalidationbtn').removeClass('processing');
                $(self).find('button.btn.mobilevalidationbtn').attr('disabled', false);

                if (data.msg == "verified successfully for password") {//pwd
                    toggleElements(authenticationPrivateMethods.controls.mobileControls, true);
                    toggleElements(authenticationPrivateMethods.controls.enterpassword_control, false);
                    var html = "Please enter the password for " + $('#txt-mobile').val() + '<a class="edit_btn" ><small id="lbl-change-mobile>change</small></a>'
                    $(authenticationPrivateMethods.controls.labelPassword).html(html);
                    $(authenticationPrivateMethods.controls.loginModalTitle).html("Password");
                    toggleElements('.otpText', true);
                    $('#pswmobile').val(fullPhone);
                    $('#psw-email').val('');
                    $('#psw-mobile').val(fullPhone);
                    $('#psw-type').val('1');

                } else { //otp
                    toggleElements('#lbl-change-mobile', false);
                    toggleElements(authenticationPrivateMethods.controls.mobileControls, true);
                    toggleElements(authenticationPrivateMethods.controls.otpControls, false);
                    authenticationPrivateMethods.properties.isOtpTriggered = true;
                    var html = "Please enter the verification code received in " + $(authenticationPrivateMethods.controls.mobile).val()
                    $(authenticationPrivateMethods.controls.labelMobileNumber).html(html);
                    $(authenticationPrivateMethods.controls.loginModalTitle).html("Verification Code");
                    //$(authenticationPrivateMethods.controls.otpFirst).focus();
                    $("#dynamic-otp0").focus();
                    authenticationPrivateMethods.setResendTimer();
                }

            } else {
                $(authenticationPrivateMethods.controls.loginModal).modal('hide');
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            }
        };

        onError = function (data) {
            console.log(data);
            master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            $(self).removeClass('processing');
        };
        master.ajax.JSONRequest(url, 'POST', { Mobile: fullPhone, usePsw: usepsw }, onSuccess, onError);

    }

    //Send OTP via Email
    authenticationPrivateMethods.getEmailOtp = function (self, token, bypassPwd) {
        try {
            var url = authenticationMethods.url.getEmailOtp + "?token=" + token;
            $(self).addClass('processing');
            if (!authenticationPrivateMethods.isValidEmail(false)) {
                $(self).removeClass('processing');
                $('.emailloginform').addClass('was-validated') 
                $('.invalid-feedback.error-email').show()
                
                return false;
            }

            authenticationPrivateMethods.resetValidations();

            var email = $(authenticationPrivateMethods.controls.email).val();
            onSuccess = function (data) {
                console.log(data)
                if (data && data.status === 'ok') {
                    $(self).removeClass('processing');
                    data.msg = bypassPwd ? "cx" : data.msg
                    if (data.msg == "verified successfully for password") {//pwd


                        toggleElements(authenticationPrivateMethods.controls.emailLoginSec, true);
                        toggleElements(authenticationPrivateMethods.controls.enterpassword_control, false);
                        var html = "Please enter the password for " + $(authenticationPrivateMethods.controls.email).val() + '<a  href="javascript:void(0)" class="edit_btn" id="lbl-change-email"><small class="ms-1">change</small></a>'
                        $(authenticationPrivateMethods.controls.labelPassword).html(html);
                        $(authenticationPrivateMethods.controls.loginModalTitle).html("Password");
                        toggleElements('.otpText', true);
                        var email = $(authenticationPrivateMethods.controls.email).val();
                        $('#psw-email').val(email);
                        $('#psw-mobile').val('');
                        $('#psw-type').val('2');

                        //$(authenticationPrivateMethods.controls.otpsingle).focus();
                        //authenticationPrivateMethods.setResendTimer('-email');
                    } else {//otp
                        toggleElements(authenticationPrivateMethods.controls.emailloginInput, true);
                        toggleElements(authenticationPrivateMethods.controls.emailPassword, true);
                        toggleElements(authenticationPrivateMethods.controls.enterpassword_control, true);
                        toggleElements('#lbl-change-mobile', true);
                        toggleElements('.emaillogin_sec', true);
                        toggleElements('.otp-control', false);
                        authenticationPrivateMethods.properties.isOtpTriggered = true;
                        var html = 'enter the verification code received in <span>' + $(authenticationPrivateMethods.controls.email).val() + '<a id="lbl-change-mobile" class="change-mobile" href="">Change</a></span>'
                        $('#lblMobileNum').html(html);
                        $(authenticationPrivateMethods.controls.loginModalTitle).html("Verification Code");
                        //$(authenticationPrivateMethods.controls.otpFirst).focus();
                        $("#dynamic-otp0").focus();
                        authenticationPrivateMethods.setResendTimer('-email');

                    }


                } else {
                    $(authenticationPrivateMethods.controls.loginModal).modal('hide');
                    master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
                }
            };

            onError = function (data) {
                console.log(data);
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            };
            //console.log(url, 'POST', { Email: email })
            master.ajax.JSONRequest(url, 'POST', { Email: email, usePsw: (bypassPwd ? 0 : 1) }, onSuccess, onError);
        }
        catch (e) {
            console.log(e)
        }

    }
    //Send an OTP for email verification after signup (when the logged in user have a non verified email after signup)

    authenticationPrivateMethods.getEmailOtpForVerificationAfterSignup = function (self, token, email, emailFromApi) {
        try {
            var url = authenticationMethods.url.getEmailOtp + "?token=" + token;
            authenticationMethods.properties.newEmail = email
            $('.newEmail').text('Enter OTP recieved in ' + email)

            $(self).addClass('processing');
            if (!authenticationPrivateMethods.isValidEmail(email)) {
                $(self).removeClass('processing');
                return false;
            }


            onSuccess = function (data) {
                console.log(data)
                $(authenticationPrivateMethods.controls.verifyValidation).removeClass('processing')

                if (data && data.status === 'ok') {
                    if (emailFromApi) {
                        $('.openpopover').addClass('active');
                        $('.emailverification ').removeClass('hide')
                        $('#emailVerifyOtp').focus()
                    } else {
                        $(self).removeClass('processing');

                        $('.openpopover').removeClass('active');
                        $('.emailverification ').removeClass('hide');
                    }


                } else {
                    $(authenticationPrivateMethods.controls.loginModal).modal('hide');
                    master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
                }
            };

            onError = function (data) {
                $(controls.verifyValidation).removeClass('processing')
                console.log(data);
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            };
            //console.log(url, 'POST', { Email: email })
            master.ajax.JSONRequest(url, 'POST', { Email: email, usePsw: 0 }, onSuccess, onError);
        }
        catch (e) {
            console.log(e)
        }

    }
    //Send an OTP for mobile number verification after signup (when the logged in user have a non verified mobile number after signup)

    authenticationPrivateMethods.getMobileOtpForVerificationAfterSignup = function (self, token, mobile, mobileFromApi) {
        try {
            var url = authenticationMethods.url.getOtp + "?token=" + token;
            authenticationMethods.properties.newMobile = mobile
            $('.newEmail').text('Enter OTP recieved in ' + mobile)
            $(self).addClass('processing');
            if (!authenticationPrivateMethods.isValidMobile(mobile)) {
                $(self).removeClass('processing');
                return false;
            }
            onSuccess = function (data) {
                $(authenticationPrivateMethods.controls.verifyValidation).removeClass('processing')
                console.log(data)
                if (data && data.status === 'ok') {
                    if (mobileFromApi) {
                        $('.openpopover').addClass('active');
                        $('.newmobileverification').removeClass('hide')
                        $('#otpHiddenInput').focus()
                    }
                } else {
                    $(authenticationPrivateMethods.controls.loginModal).modal('hide');
                    master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
                }
            };

            onError = function (data) {
                $(authenticationMethods.controls.verifyValidation).removeClass('processing')

                console.log(data);
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            };

            master.ajax.JSONRequest(url, 'POST', { Mobile: mobile, usePsw: 0 }, onSuccess, onError);
        }
        catch (e) {
            console.log(e)
        }

    }

    //Verify the OTP after signup (when the logged in user have a non verified email after signup)
    authenticationPrivateMethods.emailOtpVerificationAfterSignup = function (self, token, newEmail) {
        var controls = authenticationPrivateMethods.controls;
        var otp = $('#otpHiddenInput').val()
        $(self).addClass('processing');
        if (!authenticationPrivateMethods.isValidOTP(otp)) {
            $(self).removeClass('processing');
            return false;
        }

        var details = {};
        details.mobile = "";
        details.email = authenticationMethods.properties.newEmail;
        details.otp = otp; //$(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        $(controls.mobileSignUp).val(details.mobile);
        onSuccess = function (result) {
            console.log(result)
            if (result&&result.status == 'ok') {
                $('.verify_dropdown_popover').addClass('hide')

                $.ajax({
                    url: authenticationPrivateMethods.properties.updateStatusHandler,
                    type: 'POST',
                    data: { verified: 'email', },
                    success: function (response) {
                        // Handle success response if needed
                        location.reload(true); // Reload the page after updating the user
                    },
                });
            } else {
                master.showResult(result?.error?.msg ?? "Couldn't Verify", 'Failure', false);
                $('#emailVerifyOtp').val('')
                $('#otpContainer .otp-input').val('');
                $('.verify_dropdown_popover').removeClass('active')
                $(self).removeClass('processing');
            }


        };
        onError = function (data, polo, marco) {
            console.log(data);
            master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
        };
        master.ajax.JSONRequest(authenticationMethods.url.verifyOtp, 'POST', details, onSuccess, onError);
    }

    authenticationPrivateMethods.mobileOtpVerificationAfterSignup = function (self, token, newMobile) {
        var controls = authenticationPrivateMethods.controls;
        $(self).addClass('processing');
        if (!authenticationPrivateMethods.isValidOTP(false)) {
            $(self).removeClass('processing');
            return false;
        }

        var details = {};
        details.mobile = $('#mobile_to_verify').val().replace('+91 ', '');
        details.email = "";
        details.otp = $('#txtLoginOTP').val(); //$(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        // $(controls.mobileSignUp).val(details.mobile);

        onSuccess = function (result) {

            if (result && result.data && result.data.is_registered) {

                $.ajax({
                    url: authenticationPrivateMethods.properties.updateStatusHandler,
                    type: 'POST',
                    data: { verified: 'mobile' },
                    success: function (response) {
                        location.reload(true); // Reload the page after updating the user
                    },

                });


            }
            else {
                $(self).removeClass('processing');

            }
        };
        onError = function (data, polo, marco) {
            console.log(data);
            master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
        };
        master.ajax.JSONRequest(authenticationMethods.url.verifyOtp, 'POST', details, onSuccess, onError);
    }
    authenticationPrivateMethods.oneClickVerify = function (param, prop, emailFromApi) {
        var self = $(this)
        if (param == 'email') {
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.getEmailOtpForVerificationAfterSignup(self, token, prop, emailFromApi)
                    });
                });
            } else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.getEmailOtpForVerificationAfterSignup(self, 'dummyToken', prop, emailFromApi)

            }
        } else {
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.getMobileOtpForVerificationAfterSignup(self, token, prop, emailFromApi)
                    });
                });
            } else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.getMobileOtpForVerificationAfterSignup(self, 'dummyToken', prop, emailFromApi)

            }
        }

    }
    authenticationPrivateMethods.setResendTimer = function (from) {
        if (from == null)
            from = '';
        $("#countdown" + from).show();
        if (authenticationPrivateMethods.properties.tmrCountdownId)
            clearInterval(authenticationPrivateMethods.properties.tmrCountdownId);
        var btn = $('#resend' + from);
        $(btn).html("");
        var timeout = 60;

        authenticationPrivateMethods.properties.tmrCountdownId = setInterval(showCountDown, 1000);
        function showCountDown() {
            if (timeout == 0) {
                $("#countdown" + from).hide();
                clearInterval(authenticationPrivateMethods.properties.tmrCountdownId);
                $(btn).html("Didn't receive the code? <span class='resent' id='resend-trigger'>Resend</span>");

                $("#resend-trigger").off("click").on("click", function () {
                    //authenticationPrivateMethods.getOtp($(authenticationPrivateMethods.controls.frmmobileotp));
                    var self = $(authenticationPrivateMethods.controls.frmmobileotp); //$(this);
                    $("#countdown" + from).html('');
                    if (authenticationMethods.properties.isCaptchaEnabled) {
                        grecaptcha.ready(function () {
                            grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                                authenticationPrivateMethods.getOtp(self, token);
                            });
                        });
                    }
                    else {

                        console.log('Skipping recaptcha execution');
                        authenticationPrivateMethods.getOtp(self, 'dummyToken');
                    }

                });
            } else {
                timeout--;
                $("#countdown" + from).html('Resend in ' + timeout + ' seconds');
                $('#resend').prop('disabled', true);
            }

        }

    }

    authenticationPrivateMethods.verifyOtp = function (self) {
        var controls = authenticationPrivateMethods.controls;
        var signUpFromElem = $(controls.frmSignup);
       // $(self).addClass('processing');
        $(self).find('button.btn.validate').addClass('processing');
        $(self).find('button.btn.validate').attr('disabled', true);
        if (!authenticationPrivateMethods.isValidOTP(false)) {
            //$(self).removeClass('processing')
            $(self).find('button.btn.validate').removeClass('processing');
            $(self).find('button.btn.validate').attr('disabled', false);
            return false;
        }

        var details = {};
        var mobile = $(controls.mobile).val()
            ? $(controls.mobile).val().replace(/\s+/g, '')
            : "";
        var selectedCountryCode = $('.iti__selected-dial-code').html();

        fullPhone = mobile != "" ? selectedCountryCode + mobile : "";
        details.mobile = fullPhone
        details.email = $(controls.email).val();
        details.otp = $(controls.otpsingle).val(); //$(controls.otpFirst).val() + $(controls.otpSecond).val() + $(controls.otpThird).val() + $(controls.otpFourth).val();
        authenticationMethods.url.redirectUrl = window.location.pathname;

        $(controls.mobileSignUp).val(details.mobile);

        if (details.email) {
            $('#phone-acc-create').removeClass('hide');
            $('#txt-mobile-acc-create').prop('required', true);
            $('#newacount_emailid_field').addClass('hide');
            $(authenticationPrivateMethods.controls.emailSignUp).removeAttr('required');
        } else {
            $('#newacount_emailid_field').removeClass('hide');
            $(authenticationPrivateMethods.controls.emailSignUp).prop('required', true);
            $('#phone-acc-create').addClass('hide');
            $('#txt-mobile-acc-create').removeAttr('required');
        }

        onSuccess = function (result) {


            $(signUpFromElem).find('button').parent('div').removeClass('processing')

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
               // $(self).removeClass('processing');
                $(self).find('.button.btn.validate').removeClass('processing');
                $(self).find('button.btn.validate').attr('disabled', false);
                $(controls.loginModalTitle).html("Create Account");
                $(function () {
                    $(authenticationPrivateMethods.controls.nameSignUp).focus();
                });
                toggleElements(controls.mobileControls, true);
                toggleElements(controls.otpControls, true);
                $(controls.signupControls).removeClass("hide");
                $('.sociallogin').addClass("hide");

                //$(authenticationPrivateMethods.controls.passwordSignup).removeAttr('required');

            }
            else {
                //$(self).removeClass('processing');
                //$('#login-modal').modal('hide');
               // master.showResult(result.error.msg ? result.error.msg : 'There is a technical error. Please contact administrator for more details', 'Failure', false);

                $('.invalid-feedback.invalid-otp').show()
                $('.otp-input').css('border-color', 'red');

                if (result?.error?.msg) {
                    $('.invalid-feedback.invalid-otp').html(result.error.msg)
                }
                $(self).find('button.btn.validate').parent('div').removeClass('processing')

                $(self).find('button.btn.validate').removeClass('processing');
                $(self).find('button.btn.validate').attr('disabled', false);
                $(controls.otp).val('');
                $(controls.invalidOtp).removeClass('hide');
            }
        };
        onError = function (data, polo, marco) {
            console.log(data);
            master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
        };
        master.ajax.JSONRequest(authenticationMethods.url.verifyOtp, 'POST', details, onSuccess, onError);
    }
    authenticationPrivateMethods.initiateSignup = function (self) {
        var details = {
            mobile: authenticationMethods.properties.signupPhone,
            email: authenticationMethods.properties.signupEmail,
            name: authenticationMethods.properties.signupName,
            password: authenticationMethods.properties.password
        }
        onSuccess = function (data) {
            if (data.status == 'error') {
                $(self).find('button').parent('div').removeClass('processing')
                var signpUsingPhone = $('#phone-acc-create').hasClass('hide')

                var errorMessage = 'There is a technical error.Please contact administrator for more details';
                if (data.message[0] == 'Already Registered') {
                    errorMessage = signpUsingPhone ? authentication.messages.emailExistsAlert : authenticationMethods.messages.phoneExistsAlert;
                    $('#error-signup').html(errorMessage);
                }
                $('#resultsModal .modal-content').css('border', '1px solid #ffd6d6');

                master.showResult("Could'nt signup! " + errorMessage, 'Failure', false)
                setTimeout(function () {
                    $('#resultsModal').modal('hide');
                    $('#resultsModal .modal-content').css('border', '1px solid #ffffff');

                }, 5000);
            } else {
                if (window.location.pathname != '/')
                    window.location.href = '/';
                else
                    window.location.reload(true);
            }
           
        };

        onError = function (data) {
            $(self).find('button').parent('div').removeClass('processing');
            console.log(data);
        };
        master.ajax.JSONRequest(
            //authenticationMethods.url.signUp,
            'Authentication/SignUp',
            'POST', details, onSuccess, onError);
    }
    authenticationPrivateMethods.signUp = function (self) {


        authenticationMethods.properties.signupEmail = $(authenticationPrivateMethods.controls.emailSignUp).val() ? $(authenticationPrivateMethods.controls.emailSignUp).val() : $(authenticationPrivateMethods.controls.email).val();
        authenticationMethods.properties.signupName = $(authenticationPrivateMethods.controls.nameSignUp).val();
        var mobilenum = $('#txt-mobile').val() != ""
            ? $('#txt-mobile').closest('.iti').find('.iti__selected-dial-code').html() + $('#txt-mobile').val() 
            : $(authenticationPrivateMethods.controls.flMobileSignUp).closest('.iti').find('.iti__selected-dial-code').html() + $(authenticationPrivateMethods.controls.flMobileSignUp).val();

        authenticationMethods.properties.signupPhone = mobilenum.replace(" ", "").trim();
        authenticationMethods.properties.password = $(authenticationPrivateMethods.controls.passwordSignup).val();

        console.log(address.properties.signupEmail, address.properties.signupName, address.properties.signupPhone)
        if (authenticationMethods.properties.signupEmail.trimEnd() != '' && authenticationMethods.properties.signupName.trimEnd() != '') {
            //address.triggerAddAddress(true);
            authenticationPrivateMethods.initiateSignup(self)
            //$(authenticationPrivateMethods.controls.loginModal).modal('hide');
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

    authenticationPrivateMethods.verifyPsw = function (self, token) {
        var url = authenticationPrivateMethods.properties.verifyPsw + "?token=" + token;
        //var data = $(self).serializeArray().reduce(function (json, { name, value }) {
        //        json[name] = value;
        //        return json;
        //    }, {});
        //var data = $(self).serialize();
        $(self).find(".Error").remove();
        authenticationMethods.url.redirectUrl = window.location.pathname;

        var data = $(self).serializeArray().map(function (x) { this[x.name] = x.value; return this; }.bind({}))[0];

        $(self).addClass('processing');
        onSuccess = function (data) {
            $(self).removeClass('processing');

            if (data && data.status === 'ok') {
                if (authenticationMethods.url.redirectUrl != '')
                    window.location.href = authenticationMethods.url.redirectUrl;
                else
                    window.location.reload(true);
            }
            else if (data && data.status == 'error' && data.error && data.error.msg && data.error.msg != '') {
                $(self).find('div.Error').text(data.error.msg);
                $(self).find('.errclasstobeadded').append("<div class='invalid-feedback invalid-otp' style='display:block;'>" + data.error.msg + "</div>");
            }
            else {
                $(authenticationPrivateMethods.controls.loginModal).modal('hide');
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);

            }
        };

        onError = function (data) {
            master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            $(self).removeClass('processing');
        };
        master.ajax.JSONRequest('/' + url, 'POST', data, onSuccess, onError);

    }


    authenticationPrivateMethods.bringBackMobileModal = function () {
        $(authenticationPrivateMethods.controls.otpControls).addClass("hide");
        $(authenticationPrivateMethods.controls.mobileControls).removeClass("hide");
        $(authenticationPrivateMethods.controls.enterpassword_mobile_container).addClass("hide");
        authenticationPrivateMethods.properties.isOtpTriggered = false;
    }
    authenticationPrivateMethods.bringBackEmailModal = function () {

        toggleElements('.otp-control', true)
        toggleElements(authenticationPrivateMethods.controls.enterpassword_control, true);
        toggleElements(authenticationPrivateMethods.controls.emailPassword, true);
        $(authenticationPrivateMethods.controls.changeEmailPwd).html("");
        $(authenticationPrivateMethods.controls.loginModalTitle).html("Email");
        toggleElements(authenticationPrivateMethods.controls.emailloginInput, false);
    }
    authenticationPrivateMethods.getCodeForInput = function () {
        //The country code used in the configuration files may not be compatible with the intlTelInput,
        //So wee need a function to map the country codes from the configuration files/apis to the country code format that intlTelInput supports.

        let code = master.properties.countryCode;

        //List of country codes that has to be mapped.
        //countryCodeList = {country code from master : country code supported by intlTelInput}

        const countryCodeList = {
            "GB": "UK",
            "UAE": "AE",
            "UK":"gb"
        }
       // return the country code from countryCodeList, else return the country code from master 
       return countryCodeList[code] || code;
    }
    authenticationPrivateMethods.initializeMobileInput = function () {

        let countryCodeForInput = authenticationPrivateMethods.getCodeForInput()

        const inputs = document.querySelectorAll(".intl_tele_input");
        inputs.forEach(input => {
            window.intlTelInput(input, {
                initialCountry: countryCodeForInput,
                preferredCountries: [countryCodeForInput],
                strictMode: true,
                allowDropdown: countryCodeForInput == "IN" ? false : true,
                separateDialCode: true,
                formatOnDisplay: false, // Disables automatic formatting

            });
        });



        //$(authenticationPrivateMethods.controls.intlTeleInput).intlTelInput({
        //    autoHideDialCode: true,
        //    autoPlaceholder: "ON",
        //    dropdownContainer: document.body,
        //    formatOnDisplay: true,
        //    hiddenInput: "full_number",
        //    initialCountry: countryCodeForInput,
        //    nationalMode: true,
        //    placeholderNumberType: "MOBILE",
        //    preferredCountries: [countryCodeForInput],
        //    separateDialCode: true,
        //    showSelectedDialCode: true,
        //    showFlags: true,
        //    allowDropdown: countryCodeForInput == "IN" ? false : true,

        //});
    }
    authenticationPrivateMethods.getAuthCodeIfLoggedIn = function () {
        // Get the URL parameters
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        // Get the value of the 'code' parameter
        const authCode = urlParams.get('code');
        if (authCode) {
            // Use the 'authCode' variable to send to server and validate
            console.log('Auth Code:', authCode);
            onSuccess = function (result) {

                if (result && result.data && result.data.is_registered) {
                    if (authenticationMethods.url.redirectUrl != '')
                        window.location.href = authenticationMethods.url.redirectUrl;
                    else if (window.location.pathname == '/')
                        window.location.reload(true);
                    else
                        window.location.replace("/");
                }

                else if (result && result.data && result.data.is_registered == false) {
                    $(self).removeClass('processing');
                    $(controls.loginModalTitle).html("Create Account");
                    toggleElements(controls.mobileControls, true);
                    toggleElements(controls.otpControls, false);
                    $(controls.signupControls).removeClass("hide");

                }
                else {
                    $(self).removeClass('processing');
                    $(controls.otp).val('');
                    $(controls.invalidOtp).removeClass('hide');
                }
            };
            onError = function (data, polo, marco) {
                console.log(data);
                master.showResult('There is a technical error. Please contact administrator for more details', 'Failure', false);
            };

            //pass in the code to validate it from the server
            //master.ajax.JSONRequest(authenticationMethods.url.verifyCode, 'POST', details, onSuccess, onError);


        }

    }
    authenticationMethods.initializePage = function () {
        authenticationMethods.events.initialize();
        authenticationPrivateMethods.initializeMobileInput();
        authenticationPrivateMethods.getAuthCodeIfLoggedIn();

    };

    authenticationMethods.events.initialize = function () {
        var controls = authenticationPrivateMethods.controls;

        $(controls.frmmobilelogin).off('submit').on('submit', function (event) {
            event.preventDefault();
            var self = $(this);
            $("#countdown").html('');
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.getOtp(self, token);
                    });
                });
            }
            else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.getOtp(self, 'dummyToken');
            }

        });

        $(controls.frmmobileloginpsw).off('submit').on('submit', function (event) {
            event.preventDefault();
            var self = $(this);
            grecaptcha.ready(function () {
                grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    authenticationPrivateMethods.verifyPsw(self, token);
                });
            });
        });

        $(controls.emaillogin).off('submit').on('submit', function (event) {
            event.preventDefault();
            var self = $(this);
            $("#countdown").html('');
            grecaptcha.ready(function () {
                grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    authenticationPrivateMethods.getEmailOtp(self, token, false);
                });
            });
        });
        $(controls.frmmobileotp).off('submit').on('submit', function (event) {
            event.preventDefault();
            authenticationPrivateMethods.verifyOtp($(this));
        });

        $(controls.frmSignup).off('submit').on('submit', function (event) {
            var self = $(controls.frmSignup);
            if ($(self).find('button').parent('div').hasClass('processing'))
                return false;
            $(self).find('button').parent('div').addClass('processing')
            $('#error-signup').html('')
            event.preventDefault();

            authenticationPrivateMethods.signUp(self);
        });

        if (master.properties.countryCode == "IN") {
            $(controls.mobile).inputmask({
                placeholder: '',
                clearMaskOnLostFocus: false,
                colorMask: true,
                mask: [{ "mask": "##########" }],
                greedy: false,
                definitions: { '#': { validator: "[0-9]", cardinality: 1 } }
            });
        }
       

        $(controls.otp).inputmask({
            placeholder: '',
            clearMaskOnLostFocus: false,
            colorMask: true,
            mask: [{ "mask": "####" }],
            greedy: false,
            definitions: { '#': { validator: "[0-9]", cardinality: 1 } }
        });

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

        $(controls.otpBoxes).off("keyup").on("keyup", function () {
            authenticationPrivateMethods.focusOnOtpBox($(this))
        });

        $('#lbl-change-mobile').off("click").on("click", function () {
            authenticationPrivateMethods.bringBackMobileModal();
        });

        //Email click function
        $(controls.emailLoginBtn).off("click").on("click", function () {
            //Elements to be hidden
            toggleElementsFromArray([
                controls.mobileControls,
                controls.emailLoginBtn,
                controls.signupControls,
                controls.emailPassword,
                controls.otpControls
            ], true)

            //Elements to be shown

            toggleElementsFromArray([
                controls.mobileLogin,
                controls.emailLoginSec
            ], false)
            document.getElementById("txt-email").focus();
            clearInputValue(controls.email)
            $(authenticationPrivateMethods.controls.emailloginInput).removeClass('hide')

        });

        //Mobile click function
        $(controls.mobileLogin).off("click").on("click", function () {
            //Elements to be hidden
            toggleElementsFromArray([
                controls.mobileLogin,
                controls.emailLoginSec,
                controls.signupControls,
                controls.emailPassword,
                controls.otpControls,
                controls.enterpassword_control
            ], true)
            //Elements to be shown
            toggleElementsFromArray([
                controls.mobileControls,
                controls.emailLoginBtn
            ], false)
            document.getElementById("txt-mobile").focus();
            clearInputValue(controls.mobile)

        })

        authenticationPrivateMethods.resetValidations = function () {
            //Resetting validation
            $('.invalid-feedback.invalid-phoone').hide()
            $('.invalid-feedback.invalid-otp').hide()
            $('.otp-input').css('border-color', '#ccc');
        }

        //Verify email or phone 
        $('#change_email_form').submit(function (event) {
            // Prevent the default form submission behavior
            event.preventDefault();

            // Get the email address entered by the user
            var newEmail = $('#newemail').val();
            var self = $(this)
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.getEmailOtpForVerificationAfterSignup(self, token, newEmail)
                    });
                });
            } else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.getEmailOtpForVerificationAfterSignup(self, 'dummyToken', authenticationMethods.properties.newEmail)

            }


        });

        $('#verify_email_otp').submit(function (event) {
            // Prevent the default form submission behavior
            event.preventDefault();
            let otp = $('#otpHiddenInput').val();
            if (otp.length < 4) {
                $('.verificationStripError.email').removeClass('hide');
                $('.verificationStripError.email').html('Please enter a valid OTP')
                return false
            }

            // Get the email address entered by the user
            var newEmail = $('#email_to_verify').val();
            var self = $(this)
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.emailOtpVerificationAfterSignup(self, token, newEmail)
                    });
                });
            } else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.emailOtpVerificationAfterSignup(self, 'dummyToken', newEmail)

            }


        });


        $('#verify_mobile_otp').submit(function (event) {
            // Prevent the default form submission behavior
            event.preventDefault();

            // Get the email address entered by the user
            var newMobile = $('#mobile_to_verify').val();
            var self = $(this)
            let otp = $('#txtLoginOTP').val();

            if (otp.length < 4) {
                $('.verificationStripError.mobile').removeClass('hide');
                $('.verificationStripError.mobile').html('Please enter a valid OTP')
                return false
            }
            if (authenticationMethods.properties.isCaptchaEnabled) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                        authenticationPrivateMethods.mobileOtpVerificationAfterSignup(self, token, newMobile)
                    });
                });
            } else {

                console.log('Skipping recaptcha execution');
                authenticationPrivateMethods.mobileOtpVerificationAfterSignup(self, 'dummyToken', newMobile)

            }


        });
        //Instagram Login
        $(controls.instagramLogin).on("click", function () {
            //navigateTo('https://api.instagram.com/oauth/authorize?client_id=' + authenticationMethods.properties.fedAuthFLKey + '&redirect_uri=' + authenticationMethods.url.fedRedirectUrl + '/fl/in&scope=user_profile,user_media&response_type=code');
            navigateTo(authenticationMethods.url.fedRedirectUrl.replace('[source]', 'instagram'))
        });
        //Facebook Login
        $('.facebookLogin').on("click", function () {
            //navigateTo('https://graph.facebook.com/oauth/authorize?client_id=' + authenticationMethods.properties.fedAuthFLKey + '&redirect_uri=' + authenticationMethods.url.fedRedirectUrl + '/fl/fb&scope=email,public_profile')
            navigateTo(authenticationMethods.url.fedRedirectUrl.replace('[source]', 'facebook'))
        });
        //Google Login
        $('.google').on("click", function () {
            //navigateTo('https://accounts.google.com/o/oauth2/auth?response_type=code&redirect_uri=' + authenticationMethods.url.fedRedirectUrl + '/fl/gm&scope=https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile&client_id=' + authenticationMethods.properties.fedGoogleAuthClientId + '&state=https://mafarm.in')
            navigateTo(authenticationMethods.url.fedRedirectUrl.replace('[source]', 'google'))
        })
        $(controls.loginWithOtpLabel).on("click", function ($event) {

            var self = $(this);

            $("#countdown").html('');
            grecaptcha.ready(function () {
                grecaptcha.execute(authenticationMethods.properties.captchaKey, { action: 'submit' }).then(function (token) {
                    authenticationPrivateMethods.getEmailOtp(self, token, true);
                    toggleElements(controls.emailPassword, true)
                    toggleElements(controls.emailOtpControls, false)
                });
            });
        })

        $(document).on("click", controls.changeEmail, function () {
            authenticationPrivateMethods.bringBackEmailModal();
        });
        $(document).on("click", controls.changeMobile, function () {
            authenticationPrivateMethods.bringBackMobileModal();
        });
        $(document).on("click", controls.changePhonePwd, function () {

            authenticationPrivateMethods.bringBackMobileModal();
        });

        $(document).on("click", controls.changeTarget, function () {

            $('.verify_dropdown_popover').toggleClass('hide')
            //$(controls.postCodeLookup).addClass('hide')
            var target = $(this).data('target');
            switch (target) {
                case "email":
                    $('.newemailid').toggleClass('hide')
                    break
                case 'mobile':
                    $('.newmobilenumber').toggleClass('hide')
                    break

            }
        });

        $(document).on("click", controls.verifyValidation, function () {
            var target = $(this).data('target');
            console.log(target)
            $(controls.verifyValidation).addClass('processing')
            switch (target) {
                case "email":
                    var email = $('#email_to_verify').val()
                    authenticationPrivateMethods.oneClickVerify('email', email, true);
                    break
                case 'mobile':
                    var phone = $('#mobile_to_verify').val()
                    authenticationPrivateMethods.oneClickVerify('phone', phone, true);
                    //$('.newmobilenumber').toggleClass('hide')
                    break

            }
        });





        //Provide the array of classes and IDs from the controls for toggling the 'hide' class in bulk
        function toggleElementsFromArray(array, isHide) {
            array.forEach(function (string) {
                toggleElements(string, isHide);
            });
        }

        function clearInputValue(inputElement) {
            $(inputElement).val('');
        }

    };

    //Toggles the class hide on provided element
    function toggleElements(element, isHide) { // true => hide, false => show
        $(element).toggleClass("hide", isHide);
    }
    //Navigates to the provided url
    function navigateTo(url) {
        window.location.href = url;
    }

    return authenticationMethods;
}();
$(function () {
    authentication.initializePage();
});