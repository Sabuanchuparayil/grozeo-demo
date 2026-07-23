<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Login_Old.aspx.cs" Inherits="RetalineProAgent.UI.Login.Login_Old" %>


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
  <link rel="stylesheet" href="/Content/css/custom/custom.css">


  <script src="/Content/lib/jquery/js/jquery.js"></script>
  <script src="/Content/lib/popper.js/js/popper.js"></script>
  <script src="/Content/lib/bootstrap/js/bootstrap.js"></script>
  <script src="/Content/js/slim.js"></script>
  <script src="/Content/js/custom/intlTelInput-jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {

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
      <div class="copyright">© 2023 <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.</div>
    </div>

    <div class="login-box col-12 col-lg-6 d-flex flex-nowrap">
      
      <div class="page_header d-flex align-item-center justify-content-between w-100 mx-auto">
        <div class="grozeologo">
          <a class="d-lg-none" href="<%= String.Format(ConfigurationManager.AppSettings.Get("grozeo.url")) %>">
            <img src="/Content/images/logo/grozeo_logo.svg">
          </a>
        </div>
        <h4>Not a Grozeo Partner? <a href="/signup">Sign up</a></h4>
      </div>

      <div class="card">
        <div class="card-body login-card-body">
          <form id="form1" runat="server">


    <asp:PlaceHolder ID="plcLoginWithPsw" runat="server">
            <div class="login_head">

              <h2>Welcome Partner,</h2>
              <p class="login-box-msg">Login to access your account and resources.</p>
            </div><!--login_head-->

            <div class="loginform_wrap">


              <div class="form-row">

                <div class="input-group mb-4 col-12">
                  <label>Enter your Email</label>
                  <asp:TextBox ID="UserName" runat="server" TextMode="Email" autofocus="autofocus" autocomplete="txtuser" aria-autocomplete="none" CssClass="form-control" required></asp:TextBox>
                </div>

                <div class="input-group mb-3 col-12">
                  <label>Enter your Password</label>
                  <asp:TextBox ID="Password" runat="server" TextMode="Password" CssClass="form-control" required ></asp:TextBox>
                </div>

              </div> <!--form-row-->



              <div class="form-row mb-4 col-12 justify-content-between d-flex flex-wrap">
                <div class="loginlinks_option">
                  <asp:LinkButton ID="btnshowFogotPassword" CssClass="forgot" OnClick="btnshowFogotPassword_Click" runat="server">I forgot my password</asp:LinkButton>
                </div>
                <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; margin-top:8px; height: 15px; display: inline-block;">
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
      
                  </div> <!-- social-auth-links -->
                </div>
                <div class="col d-flex justify-content-end">
                  <asp:Button ID="LoginButton" runat="server" CssClass="btn" OnClick="LoginButton_Click" Text="Login" ValidationGroup="Login1" />
                </div>
              </div><!--form-row-->

            </div><!--loginform_wrap-->
            
</asp:PlaceHolder>
<asp:PlaceHolder ID="plcLoginWithOTP" runat="server" Visible="false">
    <asp:PlaceHolder ID="pnlInputMobile" runat="server">
            <div class="login_head">

              <h2>Mobile Sign in</h2>
              <p class="login-box-msg">Enter your mobile number and OTP to login</p>
            </div><!--login_head-->

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

              </div><!--form-row-->              

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
      
                  </div> <!-- social-auth-links -->
                </div>
                <div class="col d-flex justify-content-end">
                    <asp:Button ID="btnSendOTP" OnClick="btnSendOTP_Click" runat="server" CssClass="btn btn-primary btn-block btn-drk-green" Text="Continue" />
                </div>
              </div><!--form-row-->

              <div class="form-row">
                <div class="col-12">                  
                  <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; height: 15px; display: inline-block; margin-top: 5px;">
                     <asp:Literal ID="ltrInvalidMobile" runat="server"></asp:Literal>
                  </span>
                </div>
              </div>


            </div><!--loginform_wrap-->
        </asp:PlaceHolder>

    <asp:Panel ID="pnlInputOTP" runat="server" CssClass="onetime_mobile_otpcod" Visible="false">
            
              <asp:Label ID="countdown" CssClass="otp_countdown" runat="server" ClientIDMode="Static"></asp:Label>
            
              <div class="login_head">
            
                <h2>Verifiy Phone</h2>
                <p class="login-box-msg">The OTP has been sent to <asp:Literal runat="server" ID="ltrCurMobileNum"></asp:Literal></p>
              </div><!--login_head-->
            
            
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
                      <asp:Button ID="btnVerifyOTP" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" CssClass="btn btn-primary m-0" OnClick="btnVerifyOTP_Click" Text="Continue" />
                    </div>
            
                  </div> <!--onetime_mobile_otpcod-->
            
                </div> <!--form-row-->

            
                <div class="form-row mt-3 mb-4">
                  <div class="col-12">
                    <div class="loginlinks_option d-flex justify-content-between align-item-center">
                      <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click">Edit Mobile Number</asp:LinkButton>
                      <span class="reciveotp text-end w-auto m-0 btnresendotp" style="display: none;"><asp:LinkButton runat="server" OnClick="btnSendOTP_Click">Resend</asp:LinkButton></span>
                    </div>
                  </div>
                </div>
            
              </div><!--loginform_wrap-->

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
      
                  </div> <!-- social-auth-links -->
                </div>
              </div><!--form-row-->


    </asp:Panel>
</asp:PlaceHolder>

<asp:PlaceHolder ID="plcForgotPassword" runat="server">

            <div class="login_head">

              <h2>Forgot Password</h2>
              <p class="login-box-msg">Reset password using email id.</p>
            </div><!--login_head-->

            <div class="loginform_wrap">

              <div class="form-row">

                <div class="input-group mb-4 col-12">
                  <label>Enter your Email</label>
                  <asp:TextBox ID="txtForgotPswEmail" runat="server" TextMode="Email" CssClass="form-control mb-2" required></asp:TextBox>
                </div>

              </div> <!--form-row-->


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
      
                  </div> <!-- social-auth-links -->
                </div>
                <div class="col d-flex justify-content-end">
                  <asp:LinkButton runat="server" ID="btnFogotPassword" OnClick="btnFogotPassword_Click" CssClass="btn">Submit</asp:LinkButton>
                </div>
              </div><!--form-row-->



            </div><!--loginform_wrap-->


</asp:PlaceHolder>

<asp:PlaceHolder ID="plcVerificationFailedEmail" runat="server" Visible="false">

            <div class="login_head">

              <h2>Verification Failed</h2>
              <p class="login-box-msg"><asp:Literal ID="ltrEmailLoginFailure" Text="We do not have your email within our system. Please select your email id used for registration or register your store now.." runat="server"></asp:Literal></p>
            </div><!--login_head-->

            <div class="loginform_wrap">

            <div class="form-row d-flex justify-content-between align-item-center mt-2">
                <div class="loginlinks_option">
                    <a href="/signup">Register Now</a>
                </div>
                <div class="loginlinks_option">
                    <a href="/login">Retry</a>
                </div>
              </div>

            </div><!--loginform_wrap-->

</asp:PlaceHolder>

<asp:PlaceHolder ID="plcVerificationFailedMobile" runat="server" Visible="false">
            <div class="login_head">

              <h2>Verification Failed</h2>
              <p class="login-box-msg">We do not have your number within our system. Please select your 10 digit mobile number used for registration or register your store now..</p>
            </div><!--login_head-->

            <div class="loginform_wrap">

            <div class="form-row d-flex justify-content-between align-item-center mt-2">
                <div class="loginlinks_option">
                    <a href="/signup">Register Now</a>
                </div>
                <div class="loginlinks_option">
                    <asp:LinkButton runat="server" OnClick="lbtnViewMobileLogin_Click">Retry</asp:LinkButton>
                </div>
              </div>

            </div><!--loginform_wrap-->

</asp:PlaceHolder>

<asp:PlaceHolder ID="plcLoginPendingVerification" runat="server" Visible="false">
            <div class="login_head">

              <h2>Verification Failed</h2>
              <p class="login-box-msg">Email Verification is Pending</p>
            </div><!--login_head-->

            <div class="loginform_wrap">
              <div class="form-row"><br />

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

            </div><!--loginform_wrap-->


</asp:PlaceHolder>

            <div class="input-group col-12 p-0 mt-1 ht-20">
              <asp:Label ID="lblResult" runat="server" style="color: black; line-height: 100%; font-size: 12px; font-weight: 300; display: inline-block;"></asp:Label>
              <span style="color: red; line-height: 100%; font-size: 12px; font-weight: 300; display: inline-block;">
                  <asp:Literal ID="ltrResult" runat="server" EnableViewState="False"></asp:Literal>
      <asp:Literal ID="ltrSendPasswordMessage" runat="server"></asp:Literal>
              </span>
            </div>

            


      <asp:HiddenField ID="hidLoginType" runat="server" />    
            
          </form>
          
        </div>
        <!-- login-card-body -->
      </div>

      <div class="copyright copyright_m_view">© 2023 <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.</div>
    </div>
    <!-- login-box -->

  </div>

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




</script>

</body>

</html>