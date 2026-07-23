<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Login.aspx.cs" Inherits="RetalineProAgent.UI.Login.LoginNew" %>

<%@ Register Src="~/Controls/PopupAlert.ascx" TagPrefix="uc1" TagName="PopupAlert" %>

<%@ Register Src="~/Controls/SignupControl/SignupGST.ascx" TagPrefix="uc1" TagName="SignupGST" %>
<%@ Register Src="~/Controls/SignupControl/SignupCreateStore.ascx" TagPrefix="uc1" TagName="SignupCreateStore" %>
<%@ Register Src="~/Controls/SignupControl/ctrlSignupResetPassword.ascx" TagPrefix="uc1" TagName="ctrlSignupResetPassword" %>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partner | Log in</title>
    <link href="/Content/images/favicon.ico" rel="shortcut icon"
        type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link href="/Content/css/custom/css_login.css" rel="stylesheet">

    <!-- Font Family -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="/Content/css/custom/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="/Content/css/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="/Content/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Content/css/intlTelInput.css">
    <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">

    <link rel="stylesheet" href="/Content/css/custom/custom.css">

    <link rel="stylesheet" href="/Content/css/toastify.css" />


    <script src="/Content/lib/jquery/js/jquery.js"></script>
    <script src="/Content/lib/popper.js/js/popper.js"></script>
    <script src="/Content/lib/bootstrap/js/bootstrap.js"></script>
    <script src="/Content/js/slim.js"></script>

    <script src="/content/js/toastify.js"></script>
    <script src="/Content/js/custom/intlTelInput-jquery.min.js"></script>
    <script src="/Content/js/bootstrap-multiselect.min.js"></script>
    <script src="/Content/js/custom/master.js"></script>
    <script src="/Content/js/custom/auth.js"></script>


    <script src="<%= String.Format("https://www.google.com/recaptcha/api.js?badge=bottomleft&render={0}", ConfigurationManager.AppSettings.Get("Recaptcha.Key")) %>"></script>
    <% int maxbusinessTypeRestricted =  0; 
        try { maxbusinessTypeRestricted= Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted")??"0"); } catch { maxbusinessTypeRestricted = 0; } 
    %>


    <script type="text/javascript">
        $(document).ready(function () {
            $('#lstBusinessTypes').multiselect({
                nonSelectedText: 'Select Retail Category',
                onChange: function (option, checked) {
                    // Get selected options.
                    var selectedOptions = jQuery('#lstBusinessTypes option:selected');
                    var maxBusinessTypes = <%= maxbusinessTypeRestricted %>;

                    if (maxBusinessTypes > 0 && selectedOptions.length >= maxBusinessTypes) {
                        // Disable all other checkboxes.
                        var nonSelectedOptions = jQuery('#lstBusinessTypes option').filter(function () {
                            return !jQuery(this).is(':selected');
                        });

                        nonSelectedOptions.each(function () {
                            var input = jQuery('#lstBusinessTypes + .btn-group input[value="' + jQuery(this).val() + '"]');
                            input.prop('disabled', true);
                            input.parent('li').addClass('disabled');
                        });
                    }
                    else {
                        // Enable all checkboxes.
                        jQuery('#lstBusinessTypes option').each(function () {
                            var input = jQuery('#lstBusinessTypes + .btn-group  input[value="' + jQuery(this).val() + '"]');
                            input.prop('disabled', false);
                            input.parent('li').addClass('disabled');
                        });
                    }
                }
            });


            $("#<%= form1.ClientID%>").on('submit', function () {
                var obj = $(this).find('input[type=submit][clicked=true]');
                if (!obj && $(this).attr('childobj') != '')
                    obj = $('#' + $(this).attr('childobj'));

                if (obj) {
                    $(obj).closest('div').addClass('processing_loader');
                }
            });
            $("#<%= form1.ClientID%>").find("input[type=submit]").click(function () {
                $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
                $(this).attr("clicked", "true");
            });

            // skip 0 at the begining of phone number
            document.querySelectorAll(".restrictmobile").forEach(element => {
                // Add event listener to each element
                element.addEventListener("input", function (event) {
                    const inputValue = event.target.value;
                    // Check if the first character is "0"
                    if (inputValue.charAt(0) === "0") {
                        // Trim the leading "0" from the input value
                        event.target.value = inputValue.substring(1);
                    }
                });
            });


        });

        function businessTypeValidation(source, arguments) {
            var selectedOptions = jQuery('#lstBusinessTypes option:selected');
            arguments.IsValid = selectedOptions.length > 0;
        }

        function validateCreateStore() {
            if (typeof (Page_ClientValidate) == 'function') {
                Page_ClientValidate('CreateStore');
            }
            if (Page_IsValid) {
                return true;
            }
            return false;
        }

        authentication.properties.countryPhoneCode = '<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>';
        authentication.properties.mobilepatern = <%= (ConfigurationManager.AppSettings.Get("IsDemo") == "1"? "null" : (ConfigurationManager.AppSettings.Get("CountryCode") != "IN" ? @"/^[0-9]{9,13}$/" : @"/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/") ) %>


    </script>

    <asp:PlaceHolder ID="plsHeaderPostcoder" Visible="false" runat="server">
        <script>
        (function (w, t, c, p, s, e) {
            p = new Promise(function (r) {
                w[c] = {
                    client: function () {
                        if (!s) {
                            s = document.createElement(t); s.src = 'https://js.cobrowse.io/CobrowseIO.js'; s.async = 1;
                            e = document.getElementsByTagName(t)[0]; e.parentNode.insertBefore(s, e); s.onload = function () { r(w[c]); };
                        } return p;
                    }
                };
            });
        })(window, 'script', 'CobrowseIO');
        CobrowseIO.license = "<%= ConfigurationManager.AppSettings.Get("CoBrowserkey") %>";
        CobrowseIO.client().then(function () {
            CobrowseIO.start();
        });
        </script>
    </asp:PlaceHolder>

    <link id="headerSkin" rel="stylesheet" href="">
</head>

<body class="hold-transition login-page">

    <div class="login_sec_wrp d-flex">


        <div class="login_img col-12 col-lg-6 p-4 d-none d-lg-flex flex-wrap align-item-center justify-content-center position-relative">
            <div class="infogrp d-flex justify-content-center align-item-center p-3">
                <img src="/Content/images/login/getsetgo.svg">
            </div>
            <div class="copyright">
                © <span id="currentYear"></span>
                <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.
            </div>
        </div>

        <div class="login-box col-12 col-lg-6 d-flex flex-nowrap <%= (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.StoreSignup ? "create_store_page" : "") %>">

            <%--<div class="page_header d-flex align-item-center justify-content-between w-100 mx-auto">
        <div class="grozeologo">
          <a class="d-lg-none" href="<%= String.Format(ConfigurationManager.AppSettings.Get("grozeo.url")) %>">
            <img src="/Content/images/logo/grozeo_logo.svg">
          </a>
        </div>
        <h4>Not a Grozeo Partner? <a href="/signup">Sign up</a></h4>
      </div>--%>

            <div class="card">
                <div class="card-body login-card-body">
                    <form id="form1" runat="server">
                        <asp:PlaceHolder ID="plcLoginWithPsw" runat="server">
                            <div class="login_head">
                                <h2>Welcome to Grozeo,</h2>
                                <p class="login-box-msg">Signup or Login to manage your Grozeo Online Store.</p>
                            </div>
                            <!--login_head-->
                            <div class="loginform_wrap">

                                <% if (CurViewType != RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.LoginWithPassword)
                                    { %>
                                <div class="form-row">
                                    <div class="input-group mb-0 col-12">
                                        <label>Enter your Email</label>
                                        <asp:TextBox ID="UserName" runat="server" TextMode="Email" autofocus="autofocus" autocomplete="txtuser" aria-autocomplete="none" CssClass="form-control" required></asp:TextBox>
                                    </div>
                                </div>
                                <%} %>

                                <asp:PlaceHolder ID="plcPassword" runat="server">
                                    <div class="form-row">
                                        <div class="input-group mb-2 col-12">
                                            <label>Your <%= (CurrentLoginType == RetalineProAgent.UI.Login.LoginType.Mobile ? "Mobile" : "Email") %></label>
                                            <label class="form-control"><%= (CurrentLoginType == RetalineProAgent.UI.Login.LoginType.Mobile ? CurMobile : CurEmail) %></label>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="input-group mb-3 col-12">
                                            <label>Enter your Password</label>
                                            <asp:TextBox ID="Password" runat="server" TextMode="Password" CssClass="form-control" required></asp:TextBox>
                                        </div>

                                    </div>
                                    <!--form-row-->

                                    <div class="loginlinks_option d-flex justify-content-between align-item-center mt-2">
                                        <div class="loginlinks_option">
                                            <asp:LinkButton ID="btnshowFogotPassword" CssClass="forgot" OnClick="btnshowFogotPassword_Click" runat="server">Forgot password</asp:LinkButton>
                                        </div>
                                        <asp:LinkButton runat="server" Text="Login with OTP" OnClick="LoginWithOTP_Click"></asp:LinkButton>
                                        <% if (CurrentLoginType == RetalineProAgent.UI.Login.LoginType.Mobile)
                                            {%>
                                        <asp:LinkButton runat="server" Text="Change Mobile" OnClick="lbtnViewMobileLogin_Click"></asp:LinkButton>
                                        <%}
                                            else
                                            { %>
                                        <asp:LinkButton runat="server" Text="Change Email" OnClick="lbtnViewEmailLogin_Click"></asp:LinkButton>
                                        <%} %>
                                    </div>

                                </asp:PlaceHolder>

                                <div class="form-row mb-2 col-12 justify-content-between d-flex flex-wrap">
                                    <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; margin-top: 8px; height: 15px; display: inline-block;">
                                        <asp:Literal ID="ltrLoginError" runat="server"></asp:Literal>
                                    </span>
                                </div>


                                <div class="form-row d-flex justify-content-between align-item-center">
                                    <div class="col">
                                        <div class="sociallogin d-flex align-item-center">
                                            <div class="space-style-line mr-1">or Sign in with</div>
                                            <div class="social-links d-flex justify-content-center">

                                                <asp:LinkButton runat="server" CssClass="facebook" OnClick="FacebookBtnClick">
                        <img src="/content/images/login/facebook_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" OnClick="GoogleBtnClick" CssClass="google">
                        <img src="/content/images/login/google_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click" CssClass="Mobile">
                        <img src="/content/images/login/mobile_icon.svg"></asp:LinkButton>
                                            </div>

                                        </div>
                                        <!-- social-auth-links -->
                                    </div>
                                    <div class="col-auto d-flex justify-content-end">
                                        <% if (CurViewType != RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.LoginWithPassword)
                                            {%>
                                        <asp:Button ID="LoginVerifyEmail" runat="server" CssClass="btn" Text="Continue" ValidationGroup="Login1" OnClick="LoginVerifyEmail_Click" />
                                        <%}
                                            else
                                            { %>
                                        <asp:Button ID="LoginButton" runat="server" CssClass="btn" OnClick="LoginButton_Click" Text="Login" ValidationGroup="Login1" />
                                        <%} %>
                                    </div>
                                </div>
                                <!--form-row-->

                            </div>
                            <!--loginform_wrap-->

                        </asp:PlaceHolder>
                        <asp:PlaceHolder ID="plcLoginWithOTP" runat="server" Visible="false"></asp:PlaceHolder>

                        <asp:PlaceHolder ID="pnlInputMobile" runat="server">
                            <div class="login_head">

                                <h2>Mobile Sign in</h2>
                                <p class="login-box-msg">Enter your mobile number and OTP to login</p>
                            </div>
                            <!--login_head-->

                            <div class="loginform_wrap">
                                <div class="form-row">

                                    <div class="input-group mb-4 col-12">
                                        <label>Enter Mobile Number</label>
                                        <div class="iti iti--allow-dropdown iti--separate-dial-code">

                                            <div class="iti iti--allow-dropdown iti--separate-dial-code">
                                                <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                                                    {  %>
                                                <div class="iti__flag-container">
                                                    <div class="iti__selected-flag" role="combobox" aria-owns="country-listbox" tabindex="0" title="India (भारत): +91">
                                                        <div class="iti__flag iti__in"></div>
                                                        <div class="iti__selected-dial-code">+91</div>
                                                        <div class="iti__arrow"></div>
                                                    </div>
                                                </div>
                                                <% } %>
                                                <input name="txtMobile" type="tel" id="txtMobile" runat="server" class="form-control txtMobile restrictmobile" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off" required="" maxlength="10" style="padding-left: 95px;">
                                                <input type="hidden" name="full_number">
                                            </div>
                                            <input type="hidden" name="full_number">
                                        </div>
                                    </div>

                                </div>
                                <!--form-row-->

                                <div class="form-row d-flex justify-content-between align-item-center mt-2 pt-1">
                                    <div class="col">
                                        <div class="sociallogin d-flex align-item-center">
                                            <div class="space-style-line mr-1">or Sign in with</div>
                                            <div class="social-links d-flex justify-content-center">
                                                <asp:LinkButton runat="server" OnClick="lbtnViewEmailLogin_Click" CssClass="emaiid_icon">
                        <img src="/content/images/login/email_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" CssClass="facebook" OnClick="FacebookBtnClick">
                        <img src="/content/images/login/facebook_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" OnClick="GoogleBtnClick" CssClass="google">
                        <img src="/content/images/login/google_icons.svg"></asp:LinkButton>
                                            </div>

                                        </div>
                                        <!-- social-auth-links -->
                                    </div>
                                    <div class="col d-flex justify-content-end">
                                        <asp:Button ID="btnSendOTP" OnClick="btnSendOTP_Click" runat="server" CssClass="btn btn-primary btn-block btn-drk-green" Text="Continue" />
                                    </div>
                                </div>
                                <!--form-row-->

                                <div class="form-row">
                                    <div class="col-12">
                                        <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; height: 15px; display: inline-block; margin-top: 5px;">
                                            <asp:Literal ID="ltrInvalidMobile" runat="server"></asp:Literal>
                                        </span>
                                    </div>
                                </div>


                            </div>
                            <!--loginform_wrap-->
                        </asp:PlaceHolder>

                        <asp:Panel ID="pnlInputOTP" runat="server" CssClass="onetime_mobile_otpcod" Visible="false">



                            <div class="login_head d-flex justify-content-between align-items-end">
                                <div class="titlewrap pe-2">
                                    <h2>Verify <%= (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.SignupWithEmailOTP?"Email":"Phone") %></h2>
                                    <p class="login-box-msg">
                                        The OTP has been sent to <%= CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.SignupWithEmailOTP ? CurEmail : CurMobile %>
                                        <asp:Literal Visible="false" runat="server" ID="ltrCurMobileNum"></asp:Literal>
                                    </p>
                                </div>
                                <asp:Label ID="countdown" CssClass="otp_countdown" runat="server" ClientIDMode="Static"></asp:Label>
                            </div>
                            <!--login_head-->


                            <div class="loginform_wrap">


                                <div class="form-row">

                                    <div class="onetime_mobile_otpcod d-flex justify-content-between align-items-start">

                                        <div id="otp" class="inputs d-flex flex-row justify-content-between otpinput">
                                            <div class="input-group mb-0 col-12">
                                                <div class="divOuter">
                                                    <div class="divInner">
                                                        <input type="text" name="txtLoginOTP" runat="server" id="txtLoginOTP" class="otp_input_field" required="" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="formtbtn col d-flex justify-content-end ml-0 ml-sm-3 mr-0">
                                            <% if (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.SignupWithEmailOTP)
                                                {                        %>
                                            <asp:Button ID="btnVerifyEmailOTP" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" CssClass="btn btn-primary m-0" OnClick="btnVerifyEmailOTP_Click" Text="Continue" />
                                            <%}
                                                else
                                                { %>
                                            <asp:Button ID="btnVerifyOTP" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" CssClass="btn btn-primary m-0" OnClick="btnVerifyOTP_Click" Text="Continue" />
                                            <% } %>
                                        </div>

                                    </div>
                                    <!--onetime_mobile_otpcod-->

                                </div>
                                <!--form-row-->


                                <div class="form-row mt-3 mb-4">
                                    <div class="col-12">
                                        <div class="loginlinks_option d-flex justify-content-between align-item-center">
                                            <asp:LinkButton ID="OTP_ChangeNumber" runat="server" OnClick="lbtnViewMobileLogin_Click"><span><%= (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.SignupWithEmailOTP?"Change Email":"Change Phone") %></span></asp:LinkButton>
                                            <span class="reciveotp text-end w-auto m-0 btnresendotp" style="display: none;">
                                                <asp:LinkButton runat="server" OnClick="btnResendOTP_Click">Resend</asp:LinkButton></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!--loginform_wrap-->

                            <div class="form-row d-flex justify-content-between align-item-center mt-2 pt-1">
                                <div class="col">
                                    <div class="sociallogin d-flex align-item-center">
                                        <div class="space-style-line mr-1">or Sign in with</div>
                                        <div class="social-links d-flex justify-content-center">
                                            <% if (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.LoginWithOTP)
                                                {                        %>
                                            <asp:LinkButton runat="server" OnClick="lbtnViewEmailLogin_Click" CssClass="emaiid_icon">
                        <img src="/content/images/login/email_icons.svg"></asp:LinkButton>
                                            <% } %>
                                            <asp:LinkButton runat="server" CssClass="facebook" OnClick="FacebookBtnClick">
                        <img src="/content/images/login/facebook_icons.svg"></asp:LinkButton>
                                            <asp:LinkButton runat="server" OnClick="GoogleBtnClick" CssClass="google">
                        <img src="/content/images/login/google_icons.svg"></asp:LinkButton>
                                            <% if (CurViewType == RetalineProAgent.Core.BussinessModel.Store.StoreSignupViewtype.SignupWithEmailOTP)
                                                {                        %>
                                            <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click" CssClass="Mobile">
                        <img src="/content/images/login/mobile_icon.svg"></asp:LinkButton>
                                            <% } %>
                                        </div>

                                    </div>
                                    <!-- social-auth-links -->
                                </div>
                            </div>
                            <!--form-row-->


                        </asp:Panel>

                        <asp:PlaceHolder ID="plcInvitationCode" runat="server" Visible="false">
                            <div class="login_head">
                                <h2>Invitation Code</h2>
                                <p class="login-box-msg">Grozeo currently operates in selected areas only. If you have received an invitation code from us, please enter it here or request one.</p>
                            </div>
                            <div class="loginform_wrap">
                                <div class="form-row">
                                    <label class="mt-3">Enter Invitation Code</label>
                                    <div class="input-group d-flex flex-nowrap">
                                        <asp:TextBox ID="txtInvitationCode" runat="server" ClientIDMode="Static" CssClass="form-control" autocomplete="nonetxtuser" aria-autocomplete="none" placeholder="Enter invitation code"></asp:TextBox>
                                        <%--<input type="tel" id="txtPhone" style="display: none;" required class="form-control" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10">--%>
                                        <div class="formtbtn">
                                            <input class="btn btn-primary" value="Submit" type="submit" id="btn_Submit_invitationcode">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="loginlinks_option">
                                <span class="reciveotp mt-4">
                                    <asp:LinkButton ID="lbtnRequestInvitationCode" OnClick="lbtnRequestInvitationCode_Click" runat="server">Request an invitation code for early access</asp:LinkButton>
                                </span>
                            </div>



                        </asp:PlaceHolder>

                        <asp:PlaceHolder ID="plcForgotPassword" runat="server">

                            <div class="login_head">

                                <h2>Forgot Password</h2>
                                <p class="login-box-msg">Reset password using email id.</p>
                            </div>
                            <!--login_head-->

                            <div class="loginform_wrap">

                                <div class="form-row">

                                    <div class="input-group mb-0 col-12">
                                        <label>Enter your Email</label>
                                        <asp:TextBox ID="txtForgotPswEmail" runat="server" TextMode="Email" CssClass="form-control mb-2" required></asp:TextBox>
                                    </div>

                                </div>
                                <!--form-row-->


                                <div class="form-row d-flex justify-content-between align-item-center">
                                    <div class="col">
                                        <div class="sociallogin d-flex align-item-center">
                                            <div class="space-style-line mr-1">or Sign in with</div>
                                            <div class="social-links d-flex justify-content-center">
                                                <asp:LinkButton runat="server" OnClick="lbtnViewEmailLogin_Click" CssClass="emaiid_icon">
                        <img src="/content/images/login/email_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" CssClass="facebook" OnClick="FacebookBtnClick">
                        <img src="/content/images/login/facebook_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" OnClick="GoogleBtnClick" CssClass="google">
                        <img src="/content/images/login/google_icons.svg"></asp:LinkButton>
                                                <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click" CssClass="Mobile m-0">
                        <img src="/content/images/login/mobile_icon.svg"></asp:LinkButton>

                                            </div>

                                        </div>
                                        <!-- social-auth-links -->
                                    </div>
                                    <div class="col d-flex justify-content-end">
                                        <asp:LinkButton runat="server" ID="btnFogotPassword" OnClick="btnFogotPassword_Click" CssClass="btn">Submit</asp:LinkButton>
                                    </div>
                                </div>
                                <!--form-row-->



                            </div>
                            <!--loginform_wrap-->


                        </asp:PlaceHolder>

                        <asp:PlaceHolder ID="plcVerificationFailedEmail" runat="server" Visible="false">

                            <div class="login_head">

                                <h2>Verification Failed</h2>
                                <p class="login-box-msg">
                                    <asp:Literal ID="ltrEmailLoginFailure" Text="We do not have your email within our system. Please select your email id used for registration or register your store now.." runat="server"></asp:Literal>
                                </p>
                            </div>
                            <!--login_head-->

                            <div class="loginform_wrap">

                                <div class="form-row d-flex justify-content-between align-item-center mt-2">
                                    <div class="loginlinks_option">
                                        <a href="/signup">Register Now</a>
                                    </div>
                                    <div class="loginlinks_option">
                                        <a href="/login">Retry</a>
                                    </div>
                                </div>

                            </div>
                            <!--loginform_wrap-->

                        </asp:PlaceHolder>

                        <asp:PlaceHolder ID="plcVerificationFailedMobile" runat="server" Visible="false">
                            <div class="login_head">

                                <h2>Verification Failed</h2>
                                <p class="login-box-msg">We do not have your number within our system. Please select your 10 digit mobile number used for registration or register your store now..</p>
                            </div>
                            <!--login_head-->

                            <div class="loginform_wrap">

                                <div class="form-row d-flex justify-content-between align-item-center mt-2">
                                    <div class="loginlinks_option">
                                        <a href="/signup">Register Now</a>
                                    </div>
                                    <div class="loginlinks_option">
                                        <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click">Retry</asp:LinkButton>
                                    </div>
                                </div>

                            </div>
                            <!--loginform_wrap-->

                        </asp:PlaceHolder>

                        <asp:PlaceHolder ID="plcLoginPendingVerification" runat="server" Visible="false">
                            <div class="login_head">

                                <h2>Verification Failed</h2>
                                <p class="login-box-msg">Email Verification is Pending</p>
                            </div>
                            <!--login_head-->

                            <div class="loginform_wrap">
                                <div class="form-row">
                                    <br />

                                    <p class="login-box-msg">Your account is pending verification through email. Please use the activation link sent to your email address to activate your account. If you did not receive the activation email, please click the Resend button below to resend it.</p>
                                    <br />
                                </div>

                                <div class="form-row d-flex justify-content-between align-item-center mt-2">
                                    <div class="loginlinks_option">
                                        <asp:LinkButton ID="lbtnResendEmail" OnClick="lbtnResendEmail_Click" runat="server">Re-send Email</asp:LinkButton>
                                    </div>
                                    <div class="loginlinks_option">
                                        <a href="/login" class="gobackbtn">Go back</a>
                                    </div>
                                </div>

                            </div>
                            <!--loginform_wrap-->


                        </asp:PlaceHolder>

                        <asp:PlaceHolder ID="plcSetPassword" runat="server" Visible="false">
                            <uc1:ctrlSignupResetPassword runat="server" ID="ctrlSignupResetPassword1" />
                        </asp:PlaceHolder>


                        <asp:PlaceHolder ID="plcSignupGSTView" runat="server">
                            <uc1:SignupGST runat="server" ID="SignupGST1" />
                        </asp:PlaceHolder>

                        <uc1:SignupCreateStore runat="server" ID="SignupCreateStore1" />

                        <div class="input-group col-12 p-0 mt-1 ht-20">
                            <asp:Label ID="lblResult" runat="server" Style="color: black; line-height: 100%; font-size: 12px; font-weight: 300; display: inline-block;"></asp:Label>
                            <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; display: inline-block;">
                                <asp:Literal ID="ltrResult" runat="server" EnableViewState="False"></asp:Literal>
                                <asp:Literal ID="ltrSendPasswordMessage" runat="server"></asp:Literal>
                            </span>
                        </div>

                        <%--<asp:HiddenField ID="hidLoginType" runat="server" />    --%>
                        <asp:HiddenField ID="hidRCT" runat="server" />

                        <div id="modalwrongnum" class="modal fade">
                            <div class="modal-dialog modal-lg" role="document">

                                <div class="modal-content tx-size-sm">

                                    <div class="modal-body">
                                        <div class="text-center">
                                            <h5 class="tx-inverse">Login failed with mobile</h5>
                                            <p class="m-0">The mobile number you provided is not yet registered with Grozeo. You can either sign up using your email address or social media account or change the number to try again</p>
                                        </div>
                                    </div>
                                    <!-- modal-body -->
                                    <div class="modal-footer d-flex justify-content-center">

                                        <asp:Button CssClass="btn btn btn-outline-secondary" formnovalidate ID="btnSignup" OnClick="lbtnViewEmailLogin_Click" runat="server" Text="Signup" />
                                        <button type="button" formnovalidate class="btn btn btn-outline-secondary" data-dismiss="modal">Edit number</button>
                                    </div>
                                </div>

                            </div>
                            <!-- modal-dialog -->
                        </div>


                    </form>

                </div>
                <!-- login-card-body -->
            </div>

            <div class="copyright copyright_m_view">© 2023 <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.</div>
        </div>
        <!-- login-box -->

    </div>
    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>
    <script type="text/javascript">

    var autofocusobj = '<asp:Literal ID="ltrAutoFocusObj" runat="server"/>';

    $(document).ready(function () {
        if (autofocusobj != '')
            $('#' + autofocusobj).focus();

        $(function () {
            var code = "<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>";
            $('#<%= txtMobile.ClientID%>').val(code);
            $('#<%= txtMobile.ClientID%>').intlTelInput({
                autoHideDialCode: true,
                autoPlaceholder: "ON",
                dropdownContainer: document.body,
                formatOnDisplay: true,
                hiddenInput: "full_number",
                initialCountry: "<%= ConfigurationManager.AppSettings.Get("CountryCode") %>",
                nationalMode: true,
                placeholderNumberType: "MOBILE",
                preferredCountries: ['<%= ConfigurationManager.AppSettings.Get("CountryCode") %>'],
                separateDialCode: true
            });
        });


    });

    let timerOn = true;
    function timer(remaining) {
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        m = m < 10 ? "0" + m : m;
        s = s < 10 ? "0" + s : s;
        $('#countdown').html(`${m} : ${s}`);
        //document.getElementById("countdown").innerHTML = '${m} : ${s}';
        remaining -= 1;
        if (remaining >= 0 && timerOn) {
            setTimeout(function () {
                timer(remaining);
            }, 1000);

            $(".otp_countdown").show();
            return;
        }
        if (!timerOn) {
            return;
        }
        $(".otp_countdown").hide();
        $('.btnresendotp').show();

        //document.getElementById("resend").innerHTML = `Don't receive the code? <span class="resent" onclick="timer(60)">Resend</span>`;
    }
    timer(60);


    function OTPInput() {
        const inputs = document.querySelectorAll('#otp > div > *[id]');
        for (let i = 0; i < inputs.length; i++) { inputs[i].addEventListener('keydown', function (event) { if (event.key === "Backspace") { inputs[i].value = ''; if (i !== 0) inputs[i - 1].focus(); } else { if (i === inputs.length - 1 && inputs[i].value !== '') { return true; } else if (event.keyCode > 47 && event.keyCode < 58) { inputs[i].value = event.key; if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } else if (event.keyCode > 64 && event.keyCode < 91) { inputs[i].value = String.fromCharCode(event.keyCode); if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } } }); }
    } OTPInput();

    <asp:Literal ID="ltrClientScript" runat="server" />

    </script>


</body>

</html>