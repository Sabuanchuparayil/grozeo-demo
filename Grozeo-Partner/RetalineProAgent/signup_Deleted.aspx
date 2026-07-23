<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="signup_Deleted.aspx.cs" Inherits="RetalineProAgent.signup_Deleted" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<%@ Register Src="~/Controls/ctrlSignupLeadPopup.ascx" TagPrefix="uc1" TagName="ctrlSignupLeadPopup" %>
<%@ Register Src="~/Controls/PopupAlert.ascx" TagPrefix="uc1" TagName="PopupAlert" %>


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
    <link rel="stylesheet" href="/Content/css/toastify.css" />


  <!-- Font Awesome -->
  <link rel="stylesheet" href="/Content/css/custom/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="/Content/css/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="/Content/css/bootstrap.min.css">
  <!-- Theme style -->
<%--  <link rel="stylesheet" href="/Content/css/adminlte.min.css">--%>
  <link rel="stylesheet" href="/Content/css/intlTelInput.css">
  <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">
  <link rel="stylesheet" href="/Content/css/custom/custom.css">


  <script src="/Content/lib/jquery/js/jquery.js"></script>
  <script src="/Content/lib/popper.js/js/popper.js"></script>
  <script src="/Content/lib/bootstrap/js/bootstrap.js"></script>

  <%--<script src="/Content/js/slim.js"></script>--%>
<script src="/content/js/toastify.js"></script>

  <script src="/Content/js/custom/intlTelInput-jquery.min.js"></script>
  <script src="/Content/js/custom/master.js"></script>
  <script src="/Content/js/custom/auth.js"></script>
  <script src="/Content/js/bootstrap-multiselect.min.js"></script>

  <link id="headerSkin" rel="stylesheet" href="">

    <% int maxbusinessTypeRestricted =  0; 
        try { maxbusinessTypeRestricted= Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted")??"0"); } catch { maxbusinessTypeRestricted = 0; } 
        %>

<script src="<%= String.Format("https://www.google.com/recaptcha/api.js?badge=bottomleft&render={0}", ConfigurationManager.AppSettings.Get("Recaptcha.Key")) %>"></script>


    <script type="text/javascript">
        $(document).ready(function () {
            $('#lstBusinessTypes').multiselect({
                nonSelectedText: 'Select Retail Category *',
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
        authentication.url.captchaVerification = '/api/auth/VerifyCaptchaToken';
        authentication.properties.captchaKey = '<%= ConfigurationManager.AppSettings.Get("Recaptcha.Key") %>';
        authentication.url.getOtp = '/api/auth/GetOTP';
        authentication.properties.countryPhoneCode = '<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>';
        authentication.properties.mobilepatern = <%= (ConfigurationManager.AppSettings.Get("IsDemo") == "1"? "null" : (ConfigurationManager.AppSettings.Get("CountryCode") != "IN" ? @"/^[0-9]{9,13}$/" : @"/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/") ) %>

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
</head>

<body class="hold-transition login-page">
          <form id="form1" runat="server">
  
  <div class="login_sec_wrp d-flex">

    <div class="login_img col-12 col-lg-6 p-4 d-none d-lg-flex flex-wrap align-item-center justify-content-center position-relative">
      <div class="infogrp d-flex justify-content-center align-item-center p-3 w-100">
        <img src="/Content/images/login/getsetgo.svg">
      </div>
      <div class="copyright">© 2023 <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.</div>
    </div>

    <div class="login-box col-12 col-lg-6 d-flex flex-nowrap <%= (plcSignupGSTSuccess.Visible ? "create_store_page" : "") %>">
      
      <div class="page_header d-flex align-item-center justify-content-between w-100 mx-auto">
        <div class="grozeologo">
          <a class="d-lg-none" href="<%= String.Format(ConfigurationManager.AppSettings.Get("grozeo.url")) %>">
            <img src="/Content/images/logo/grozeo_logo.svg">
          </a>
        </div>
        <h4>Already a Grozeo Partner? <a href="/login">Login</a></h4>
      </div>

      <div class="card">
        <div class="card-body login-card-body">

<asp:PlaceHolder ID="plcSignupMobile" runat="server">

    <asp:PlaceHolder ID="plcWithoutInvitationCode" runat="server">
        <uc1:ctrlSignupLeadPopup runat="server" id="ctrlSignupLeadPopup1" />
        <div class="text-center loginlinks_option">
                  <span class="reciveotp mt-3">
                      <asp:LinkButton ID="lbtnHaveInvitationCode" runat="server" OnClick="lbtnHaveInvitationCode_Click">I have invitation code</asp:LinkButton>
                  </span>
                </div>

    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcWithInvitationCode" runat="server">
            <div class="login_head">
              <h2>Verify Mobile with OTP</h2>
                <p class="login-box-msg">In order to keep the communication with you in future, we need a mobile number that you have access. Please provide a valid mobile number and enter the OTP received to validate the same.</p>
            </div><!--login_head-->

            <div class="loginform_wrap">
    
              <div class="form-row m-0 dvmobilenum">
                <label>Your Mobile Number</label>
                  <div class="input-group d-flex flex-nowrap">
                    <div class="iti iti--allow-dropdown iti--separate-dial-code">                    
  <div class="iti iti--allow-dropdown iti--separate-dial-code">
        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
%>
    <div class="iti__flag-container">
      <div class="iti__selected-flag" role="combobox" aria-owns="country-listbox" tabindex="0" title="India (भारत): +91">
        <div class="iti__flag iti__in"></div>
        <div class="iti__selected-dial-code">+91</div>
        <div class="iti__arrow"></div>
      </div>
    </div>
      <%}
          else
          {  %>
          <div class="iti__flag-container"><div class="iti__selected-flag" role="combobox" aria-owns="country-listbox" tabindex="0" title="<%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "United Kingdom" : ConfigurationManager.AppSettings.Get("CountryCode")) %>": <%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>"><div class="iti__flag iti__<%= ConfigurationManager.AppSettings.Get("CountryCode").ToLower() %>"></div><div class="iti__selected-dial-code"><%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %></div><div class="iti__arrow"></div></div></div>
      <% } %>
    <input name="txtSignupMobileNumber" runat="server" type="tel" id="txtSignupMobileNumber" class="form-control txtMobile restrictmobile" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off" required="" maxlength="10" value="">
      <input type="hidden" name="full_number">
  </div>
  <input type="hidden" name="full_number">
</div>
                    <div class="formtbtn col d-flex justify-content-end ml-3 mr-0">
  <input class="btn btn-primary" value="Send OTP" type="submit" id="btn_Submit_mobile">
</div>
                  </div>
              </div>
              <div class="form-row m-0 dvinvcode" style="display:none;">
                  <p class="login-box-msg">Grozeo currently operates in selected areas only. If you have received an invitation code from us, please enter it here or request one.</p>
                <label class="mt-3">Invitation Code</label>
                <div class="input-group d-flex flex-nowrap">
                    <asp:TextBox ID="txtInvitationCode" runat="server" ClientIDMode="Static" CssClass="form-control" autocomplete="nonetxtuser" aria-autocomplete="none" placeholder="Enter invitation code"></asp:TextBox>
                  <%--<input type="tel" id="txtPhone" style="display: none;" required class="form-control" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10">--%>
                  <div class="formtbtn"><input class="btn btn-primary" value="Submit" type="submit" id="btn_Submit_invitationcode"></div>
                </div>
                 <div class="loginlinks_option">
                  <span class="reciveotp mt-4">
                      <asp:LinkButton ID="lbtnRequestInvitationCode" OnClick="lbtnRequestInvitationCode_Click" runat="server">Request an invitation code for early access</asp:LinkButton>
                  </span>
                </div>


              </div>
              <div class="co-12 mt-2">
                <%--<p class="mentioned dark_green" id="lblMobileNum">An OTP is sent to the above mentioned number</p>--%>
                <p class="otpsent dark_green">OTP has been sent to the provided number.</p>
              </div>

              <div class="otp_toggle">
                <p class="mb-2">Enter the OTP received in mobile</p>
<asp:Panel ID="pnlOTP" runat="server" CssClass="d-flex justify-content-between align-items-start" DefaultButton="lbtnSignupOtpVerify">
  
                        <div class="divOuter">
                          <div class="divInner">
                              <input class="otp_input_field" id="txtOTP" runat="server" name="txtOTP" required type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"/>
                          </div>
                      </div>
                    <div class="formtbtn col d-flex justify-content-end ml-3 mr-0">
                        <asp:LinkButton ID="lbtnSignupOtpVerify" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Verify" OnClick="btnVerifyOTP_Click" CssClass="btn btn-primary"></asp:LinkButton>
                      <%--<input type="submit" required name="btnVerifyOTP" value="Verify" id="btnVerifyOTP" class="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left">--%>
                    </div>
                    </asp:Panel>

<div class="form-row mb-4">
                <div class="col-12">

                <div class="loginlinks_option d-flex justify-content-between align-item-center mt-4">
                    <a id="lbEditMobile" href="">Change Mobile Number</a>
                  <span class="reciveotp w-auto">
                    <a id="resend-trigger" href="javascript:void(0)" onclick="">Resend OTP</a>
                  </span>
                </div>
</div></div>

              </div>
              

            </div><!--loginform_wrap-->

    </asp:PlaceHolder>

</asp:PlaceHolder>

<asp:PlaceHolder runat="server" ID="plcSignupGST">

<div class="login_head">
              <h2><%= (CurVATType == 2 ? "Verify "+RetalineProAgent.Service.Store.VATService.VATLabel+" with OTP" : "Verify "+ RetalineProAgent.Service.Store.VATService.VATLabel) %></h2>
              <p class="login-box-msg">
                  <% if (CurVATType == 2)
                      {%>
                  <%= RetalineProAgent.Service.Store.VATService.VATLabel %> Registration is mandatory to operate online store in India. Provide your <%= RetalineProAgent.Service.Store.VATService.VATLabel %> Number to register as Merchant Partner 

                  <%}
                      else
                      { %>
                  <%= RetalineProAgent.Service.Store.VATService.VATLabel %> priority will be given to registered merchants when listing on Grozeo.
                  <% } %>

              </p>
            </div><!--login_head-->

            <div class="loginform_wrap">    
            <div class="form-row">
            <div class="d-flex justify-content-between align-items-end">
              <div class="input-group mb-0">
              <label><%= (CurVATType == 2 ? "Your "+RetalineProAgent.Service.Store.VATService.VATLabel+" Number" : "Your "+RetalineProAgent.Service.Store.VATService.VATLabel+" Number") %></label>
                  <input id="txtGSTNumber" runat="server" autocomplete="off" type="text" class="form-control gstnumber" name="gstnumber" placeholder="Enter GSTIN #" required>
               </div>
                  <div class="formtbtn col d-flex justify-content-end ml-3 mr-0"><asp:Button ID="btnSubmitGSTNumber" OnClick="lbtnGSTSendOtp_Click" runat="server" Text="Continue" CssClass="btn btn-primary " /></div>
              </div>
            </div>
              <div class="co-12 mt-2">
                <p id="pGSTINRequest" runat="server" class="mentioned dark_green mb-3">OTP will be send to the mobile and email linked to the <%= RetalineProAgent.Service.Store.VATService.VATLabel %>.</p>
                <p id="pGSTINOTP" runat="server" class="dark_green">
                    <% if (CurVATType == 2)
                        { %>
                    OTP sent to 
                    <asp:Literal ID="ltrGSTMaskedEmail" runat="server"></asp:Literal>
                     and <asp:Literal ID="ltrGSTMaskedMobile" runat="server"></asp:Literal> &nbsp; &nbsp;<br />
                    <% } %>
                    <asp:LinkButton ID="lbtnChangeGST" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" OnClick="lbtnChangeGST_Click" CssClass="changegstin">Change GSTIN</asp:LinkButton>
                    </p>
              </div>

<asp:PlaceHolder ID="plcSignupGSTOTP" Visible="false" runat="server">
    <asp:PlaceHolder ID="plcSignupGSTShowVerification" Visible="true" runat="server">
              <div style="margin-top: 40px;">
                <p class="mb-2">Enter the OTP received in mobile/Email</p>
                <div class="form-row d-flex justify-content-between align-items-start">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="partitioned" id="gstOTP" runat="server" autocomplete="off" aria-autocomplete="none" required type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                      </div>
                  </div>
                  </div>
                    <div class="formtbtn col d-flex justify-content-end ml-3 mr-0"><asp:Button ID="btnGSTOTPVerify" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 flot-left" Text="Verify" OnClick="btnGSTOTPVerify_Click" /></div>
                </div>

                <div class="loginlinks_option mt-4">
                  <span class="reciveotp ">
                      <a href="" data-toggle="modal" data-target="#modalSkipGST">I don't have access to <%= RetalineProAgent.Service.Store.VATService.VATLabel %> authorised mobile & Email</a>
                  </span>
                </div>

              </div>
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcSignupGSTSkipVerification" Visible="false" runat="server">
              <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal"><%= RetalineProAgent.Service.Store.VATService.VATLabel %> Details</h5>
                  <p class="login-box-msg">Please verify that the <%= RetalineProAgent.Service.Store.VATService.VATLabel %> information displayed is accurate. Your store's tax inputs, etc., will be added to this <%= RetalineProAgent.Service.Store.VATService.VATLabel %> account.</p>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter" style="height: 65px;">
                        <asp:Literal ID="ltrVATResult" runat="server"></asp:Literal>

                  </div>
                  </div>
                    <asp:Button ID="btnSkipVerificationConfim" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 mb-2 flot-left" Text="Continue" OnClick="btnGSTOTPVerify_Click" />
                </div>

                <%--<div class="loginlinks_option">
                  <span class="reciveotp mt-4">
                      <a href="" data-toggle="modal" data-target="#modalSkipGST">I don't have access to GST authorised mobile & Email</a>
                  </span>
                </div>--%>

              </div>
    </asp:PlaceHolder>

</asp:PlaceHolder>


<div class="loginlinks_option">
                    <% if (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
                        { %>
                    <asp:LinkButton ID="lbtnNoGST" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="I don't have GST Number" OnClick="lbtnNoGST_Click" runat="server"></asp:LinkButton>
                <% }
                    else
                    { %>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#modalSkipGST">I don't have <%= RetalineProAgent.Service.Store.VATService.VATLabel %></a>
                <% } %>
                </div>
            </div><!--loginform_wrap-->



</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupNoGST" runat="server" Visible="false">
    <asp:PlaceHolder ID="plcAdharView" runat="server">

        <div class="login_head">
              <h2 class="mb-3">Register without <%= RetalineProAgent.Service.Store.VATService.VATLabel %> Number</h2>
            <% if (CurVATType == 2)
                { %>
              <p class="login-box-msg"><%= RetalineProAgent.Service.Store.VATService.VATLabel %> Registration is required to operate online store in India. Still you can register with your Aadhaar account but only intra state operation is permitted.</p>
            <%}
                  %>
            </div><!--login_head-->


            <% if (CurVATType == 2)
                { %>
            <div class="loginform_wrap">
    
              <div class="form-row">

                <div class="d-flex justify-content-between align-items-end">
                  <div class="input-group mb-0">
                    <label>Your Aadhaar Number</label>
                    <asp:TextBox ID="txtAdharNum" runat="server" CssClass="form-control gstnumber" TextMode="Number" MaxLength="12" autocomplete="off" placeholder="" required="required"></asp:TextBox>
                  </div>

                  <div class="formtbtn col d-flex justify-content-end ml-3 mr-0">
                    <asp:Button ID="btnAdharSubmit" OnClick="btnAdharSubmit_Click" CssClass="btn btn-primary triger_submit" runat="server" Text="Submit" />
                  </div>
                </div>

              </div><!--form-row-->

                <asp:PlaceHolder ID="plcAdharVerify" runat="server" Visible="false">
                    <style>
                        .divOuter .adhar_OTP {    letter-spacing: 33px; margin-left: 0; padding-left: 32px;  }
                    </style>
                    <div class="form-row"><div class="co-12 mt-2">
                
                <p class="otpsent dark_green" style="display: block;">OTP has been sent to the registered number from UIDAI.</p>
              </div></div>
              <div class="form-row mt-4">
                                    <p class="mb-2">Enter the OTP received in mobile</p>
                <div class="form-row d-flex justify-content-between align-items-start">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="form-control adhar_OTP" id="txtAdharOTP" runat="server" required type="text" maxlength="6" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                      </div>
                  </div>
                  </div>
                  <div class="formtbtn col d-flex justify-content-end ml-3 mr-0"><asp:Button ID="btnAdharOTPSubmit" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 flot-left" Text="Continue" OnClick="btnAdharOTPSubmit_Click" /></div>

                </div>
              </div>


                    
                </asp:PlaceHolder>

              <div class="loginlinks_option mt-4">
                      <asp:LinkButton runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" ID="LinkButton1" Text="Continue with GST" OnClick="lbContinueWithGST_Click"></asp:LinkButton>
              </div>

            </div><!--loginform_wrap-->

            <%}
                else
                { %>
            <div class="loginform_wrap">

                    <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal">Affiliate Store</h5>
                  <p class="login-box-msg">Only promotional items will be available in your store and there is no order management since there is no own product for sale.</p>
                <div class="form-row m-0"><div class="input-group"><div style="margin-top: 20px;"></div></div>                  

                    <asp:Button ID="Button3" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left" Text="Continue" OnClick="btnNoPANAffiliate_Click" />
                </div>
              </div>

              <div class="loginlinks_option mt-4">
                      <asp:LinkButton runat="server" ID="LinkButton2" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Continue with VAT" OnClick="lbContinueWithGST_Click"></asp:LinkButton>
              </div>

            </div><!--loginform_wrap-->


            <% } %>


    </asp:PlaceHolder>

    <asp:PlaceHolder ID="plcNotAdharView" Visible="false" runat="server">

<div class="login_head">
              <h2 class="mb-3">Register without <%= RetalineProAgent.Service.Store.VATService.VATLabel %> Number</h2>
            <% if (CurVATType == 2)
                { %>
              <p class="login-box-msg"><%= RetalineProAgent.Service.Store.VATService.VATLabel %> Registration is mandatory to operate online store in India. Still you can register as an affiliate partner  of Grozeo to promote business among your contacts. You need to verify your PAN to register as an Affiliate Partner.</p>
            <%}
                else
                { %>
                <p><%= RetalineProAgent.Service.Store.VATService.VATLabel %> Priority will be given to registered merchants when listing on Grozeo. Set up your affiliate store if you dont have <%= RetalineProAgent.Service.Store.VATService.VATLabel %>.</p>
            <% } %>
            </div><!--login_head-->


            <% if (CurVATType == 2)
                { %>
            <div class="loginform_wrap">
    
              <div class="form-row m-0">
                <label>Your PAN Number</label>
                <div class="input-group d-flex flex-nowrap">                  
                  <input type="text" id="txtPAN" runat="server" required class="form-control gstnumber" autocomplete="off" placeholder="" maxlength="15">
                    <div class="formtbtn"><asp:Button ID="btnPANSubmit" OnClick="btnPANSubmit_Click" CssClass="btn btn-primary triger_submit" runat="server" Text="Submit" /></div>
                  <%--<input class="btn btn-primary triger_submit" value="Submit" type="submit" id="btn_Submit_pan">--%>
                </div>
              </div>
              <%--<div class="co-12 mt-2">
                <p class="mentioned dark_green mb-3">We will send an OTP to the mobile linked with PAN</p>
                <p class="otpsent dark_green">OTP sent to 94XXXX00</p>
              </div>--%>

              <%--<div class="otp_toggle">
                <h5 class="mb-3 font-weight-normal">Enter the OTP received in mobile</h5>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="partitioned" required type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                      </div>
                  </div>
                  </div>                  
                  <input type="submit" required name="RegisterwithPAN" autocomplete="off" value="Register with PAN" id="RegisterwithPAN" class="btn btn-primary btn-block btn-drk-green w-auto mt-3 mr-3 flot-left">
                </div>
              </div>--%>

                <asp:PlaceHolder ID="plcPANConfirm" runat="server" Visible="false">
                    <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal">PAN Details</h5>
                  <p class="login-box-msg">Please verify that the PAN information displayed is accurate. Your store's tax inputs, etc., will be linked with this PAN account.</p>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div style="margin-top: 20px;">
                        <asp:Literal ID="ltrPANResult" runat="server"></asp:Literal>
                  </div>
                  </div>                  

                    <asp:Button ID="btnPANConfirm" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left" Text="Continue" OnClick="btnPANConfirm_Click" />

                </div>
              </div>
                </asp:PlaceHolder>

              <div class="loginlinks_option mt-4">
                      <asp:LinkButton runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" ID="lbContinueWithGST" Text="Continue with GST" OnClick="lbContinueWithGST_Click"></asp:LinkButton>
              </div>

            </div><!--loginform_wrap-->

            <%}
                else
                { %>
            <div class="loginform_wrap">

                    <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal">Affiliate Store</h5>
                  <p class="login-box-msg">Only promotional items will be available in your store and there is no order management since there is no own product for sale.</p>
                <div class="form-row m-0"><div class="input-group"><div style="margin-top: 20px;"></div></div>                  

                    <asp:Button ID="btnNoPANAffiliate" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left" Text="Continue" OnClick="btnNoPANAffiliate_Click" />
                </div>
              </div>

              <div class="loginlinks_option mt-4">
                      <asp:LinkButton runat="server" ID="lbAffiliateContinueWithVAT" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Continue with VAT" OnClick="lbContinueWithGST_Click"></asp:LinkButton>
              </div>

            </div><!--loginform_wrap-->


            <% } %>

</asp:PlaceHolder>



</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupGSTSuccess" runat="server" Visible="false">
            <div class="login_head">
              <h2 class="mb-0">Welcome <asp:Literal ID="ltrGstOrganization" runat="server"></asp:Literal></h2>
              <p class="login-box-msg"><asp:Literal ID="ltrGstAddress" runat="server"></asp:Literal></p>
            </div>

            <div class="loginform_wrap">
                <label>Create your first store here</label>
              <div class="row row-sm ">

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtStoreName" runat="server" autocomplete="off" CssClass="form-control mb-3"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Store Name"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store name is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                    
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <input id="txtContactPerson" runat="server" type="text" class="form-control mb-3" name="ContactPerson " autocomplete="off" placeholder="Contact Name">
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPerson" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact person is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtContactPhone" runat="server" autocomplete="off" CssClass="form-control mb-3 restrictmobile" placeholder="Telephone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPhone" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact phone is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                    
                </div>

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <input id="txtLoginEmail" runat="server" type="email" class="form-control mb-3" autocomplete="off" placeholder="Email (User ID)">
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLoginEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact email is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                  <%--<input name="EmailID" id="EmailID" type="email" class="form-control mb-3" placeholder="Email (User ID)" required="">--%>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="input-group">
                        <asp:DropDownList ID="selBusinessTypes" AutoPostBack="true" data-placeholder="Choose business type" runat="server" AppendDataBoundItems="true" DataSourceID="SDSBusinessCategories" DataTextField="business_category_name" DataValueField="business_category_id"
                          CssClass="form-control" style="width: 100%;"><asp:ListItem Text="Select Business Category" Value=""></asp:ListItem></asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selBusinessTypes" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select Business Category" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="input-group">
                                          <asp:ListBox ID="lstBusinessTypes" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
                        <asp:CustomValidator ControlToValidate="lstBusinessTypes" ClientValidationFunction="businessTypeValidation" ValidateEmptyText="true" runat="server" CssClass="col-12 error_msg_wrap" ErrorMessage="Please select retail category" ValidationGroup="CreateStore" />
                    </div>
                </div>
                  <div class="col-12 mt-2">
                  <label><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Search / " : "") %>Enter Store Address</label>
                </div>
                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                      { %>

                  <div class="col-12 d-flex flex-wrap position-relative">
                  <div class="d-flex flex-wrap w-100" id="postcode_lookup_signup">

                  </div>
                  <div class="input-group w-100">
                        <asp:TextBox ID="txtAddr1UK" runat="server" autocomplete="off" CssClass="form-control mb-3 w-100 mx-wd-100p-force"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Select Your Address"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr1UK" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Address is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                  </div>

                </div>


                      <div class="input-group">
                      </div>
                  <% } %>

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtLocation" onfocus="if(!authentication.properties.mapTriggered){$('#ADDRESS').modal('show'); authentication.properties.mapTriggered=true;}" runat="server" data-toggle="modal" data-backdrop="static" autocomplete="off" data-keyboard="false" data-target="#ADDRESS" required CssClass="form-control mb-3" placeholder="Click to load map"/>
                        <i class="icon_map"></i>
                        <asp:HiddenField ID="hidMapAddr" runat="server" />
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtPinCode" autocomplete="off" runat="server" CssClass="form-control mb-3" placeholder="Postcode"/>
                        <asp:RequiredFieldValidator runat="server" ID="rqdpostcod"  ControlToValidate="txtPinCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Post code is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>

                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") != "UK")
                      { %>
                 <div class="col-12">
                   <div class="input-group">
                        <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control mb-3 w-100 mx-wd-100p-force" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder="Address"/>
                   </div>
                </div>
                  <% } %>


                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:DropDownList ID="selState" OnSelectedIndexChanged="selState_SelectedIndexChanged" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control mb-3" style="width: 100%;" AppendDataBoundItems="true" ></asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selState" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select state" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                        <asp:HiddenField ID="hidState" runat="server" />
                    </div>
                </div>
                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="false" ></asp:DropDownList>
                        <asp:HiddenField ID="hidDistrict" runat="server" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selDistrict" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select district" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                  <div class="col-12 col-md-6">
                    <div class="input-group">
                     <asp:TextBox runat="server" CssClass="form-control" ID="txtRaferralcode" placeholder="Referral Code,If Any"></asp:TextBox>
                     <asp:RequiredFieldValidator runat="server" Enabled="false" ID="rfvreferral" ControlToValidate="txtRaferralcode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Address is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                  </div>
                   <div class="col-12 col-md-6">
                         <div class="col-6 ckbox w-100 mb-2 mb-md-0 ml-2 mt-2">
                    <asp:CheckBox ID="chkAcceptTerms" runat="server" CssClass="tx-12" />
                    <span class="tx-12"> <strong>I accept the <a class="text-secondary" href="javascript:void(0)" data-toggle="modal" data-backdrop="static" autocomplete="off" data-keyboard="false" data-target="#EnrollmentAgreement">Terms Enrollment</a>.</strong></span> 
                        <asp:CustomValidator ID="CustomValidatorChkAcceptTerms" runat="server"
    
    OnServerValidate="CustomValidatorChkAcceptTerms_ServerValidate"
    ClientValidationFunction="CheckBoxRequired_ClientValidate"
    ErrorMessage="Please accept the terms and conditions"
    ForeColor="Red" ValidationGroup="CreateStore"
    Display="Dynamic">
</asp:CustomValidator>
                  </div>
                    <asp:HyperLink ID="hlGoHome" runat="server" NavigateUrl="/signup" Visible="false" Text="Verify" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140"></asp:HyperLink>
                   </div>
                  <div class="col-12 d-flex flex-wrap justify-content-end">
                   <div class="formtbtn"><asp:Button ID="btnSubmitAccount" ValidationGroup="CreateStore" OnClientClick="if(validateCreateStore()){$(this).closest('form').attr('childobj', this.id);}else{return false;}" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140" Text="Create Store" OnClick="btnSubmitAccount_Click" />
                  </div>
                  </div>
              </div>

            </div><!--loginform_wrap-->                    
    <asp:SqlDataSource ID="SDSBusinessCategories" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT * FROM retaline_business_category bc WHERE Store_group_Id=0 AND `status`=1 AND EXISTS(SELECT * FROM finascop_business_type bt WHERE FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
    ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>

                <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt WHERE EXISTS(SELECT * FROM retaline_business_category bc WHERE business_category_id= @catid AND Store_group_Id=0 AND FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
                ProviderName="MySql.Data.MySqlClient"><SelectParameters><asp:ControlParameter ControlID="selBusinessTypes" ConvertEmptyStringToNull="false" Name="catid" /></SelectParameters></asp:SqlDataSource>

<asp:SqlDataSource ID="SDSState" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state ORDER BY name ASC" ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID ORDER BY dst_Name ASC">
        <SelectParameters><asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" /></SelectParameters></asp:SqlDataSource>


        <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
                        <asp:HiddenField ID="hidLat" runat="server" />
                        <asp:HiddenField ID="hidLong" runat="server" />
                        <asp:HiddenField ID="HiddenField1" runat="server" />

        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        { %>

<script src="https://cdn.getaddress.io/scripts/getaddress-find-2.0.0.min.js"></script>
<script>
    getAddress.find(
        'postcode_lookup_signup',
        'kYRwv8Lf0kKFQlypo1P9pw38210',
        {
            input: {
                id: 'getaddress_input',  /* The id of the textbox' */
                name: 'getaddress_input',  /* The name of the textbox' */
                class: 'form-control mb-3 me-0 me-sm-3',  /* The class of the textbox' */
                label: 'Enter Postcode'  /* The label of the textbox' */
            },
            button: {
                id: 'getaddress_button',  /* The id of the botton' */
                class: 'btn btn-primary btn-drk-green mb-3 ms-0 ms-sm-1',  /* The class of the botton' */
                label: 'Find Address',  /* The label of the botton' */
                disabled_message: 'Find Address'  /* The disabled message of the botton' */
            },
            dropdown: {
                id: 'getaddress_dropdown',  /* The id of the dropdown' */
                class: 'form-control min-margin-8',  /* The class of the dropdown' */
                select_message: 'Select your Address',  /* The select message of the dropdown' */
                template: ''  /* The suggestion template of the dropdown' (see Autocomplete API)*/
            },
        }
    );
    document.addEventListener("getaddress-find-address-selected", function (e) {
        const result = [
            e.address.line_1,
            e.address.line_2,
            e.address.line_3,
            e.address.district,
        ]
            .filter((elem) => elem !== "")
            .join(", ");
        $("#<%= txtAddr1UK.ClientID%>").val(result);

        $("#<%= selState.ClientID%> option").filter(function () {
            return $(this).text() == e.address.country;
        }).prop("selected", true);
        $("#<%= hidDistrict.ClientID%>").val(e.address.county);
        $("#<%= hidLat.ClientID%>").val(e.address.latitude);
        $("#<%= hidLong.ClientID%>").val(e.address.longitude);
        $("#<%= txtPinCode.ClientID%>").val(e.address.postcode);
        $('#<%= ctrlAddressMap1.LocationTxtClientId%>').val(result);
        $('#<%= txtLocation.ClientID%>').val(result);

        $('#<%= selState.ClientID %>').change();

    });

</script>


    <%} %>




<script type="text/javascript">
    function CheckBoxRequired_ClientValidate(sender, e)
{
    e.IsValid = $("#<%= chkAcceptTerms.ClientID %>").is(':checked');
}
</script>


</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSetPassword" runat="server" Visible="false">
    <asp:Panel ID="pnlResetPswView" runat="server" CssClass="loginform_wrap">

          <div class="login_head">
           
              <h2 class="mb-3">You have successfully verified your Email ID</h2>
              <p class="login-box-msg">Please set password to access your account</p>
          </div><!--login_head-->

                    
          <div class="loginform_wrap">

                <div class="form-row">
                <div class="input-group mb-3 col-12">
                    <asp:TextBox ID="txtSetPassword1" runat="server" TextMode="Password" CssClass="form-control" placeholder="Password" ValidationGroup="SetPassword"></asp:TextBox>
                  </div>
                <div class="input-group mb-3 col-12">
                    <asp:TextBox ID="txtSetPassword2" runat="server" TextMode="Password" CssClass="form-control" placeholder="Password" ValidationGroup="SetPassword" ></asp:TextBox>                    
                  </div>
                </div>
                <div class="form-row">

            <div class="input-group mb-3 col-12">
                <asp:RequiredFieldValidator runat="server" ValidationGroup="SetPassword" Display="Dynamic" ErrorMessage="Password 1 is required" ControlToValidate="txtSetPassword1"></asp:RequiredFieldValidator>
                <asp:RequiredFieldValidator runat="server" ValidationGroup="SetPassword" Display="Dynamic" ErrorMessage="Password 2 is required" ControlToValidate="txtSetPassword2"></asp:RequiredFieldValidator>
                <asp:CompareValidator runat="server" ValidationGroup="SetPassword" ControlToValidate="txtSetPassword2" ControlToCompare="txtSetPassword1" ErrorMessage="Password 1 and Password 2 does not match"></asp:CompareValidator>
            </div>
                    </div>
                <div class="form-row">
                 <div class="col-12 col-sm-12 mt-3 p-0">
                    <asp:Button ID="btnSetPassword" OnClick="btnSetPassword_Click" runat="server" CssClass="btn btn-primary" Text="Submit" ValidationGroup="SetPassword" />
                </div>
              <%-- <div class="col-12">
                  <div class="backbtnsec">
                    <a href="/" class="gobackbtn">Go back</a>
                  </div>
                </div>--%>
               </div>

          </div>

    </asp:Panel>
    <asp:Panel ID="pnlResetPswInvalidkey" runat="server" Visible="false">
<div class="login_head">
            <h2 class="mb-3">Request Expired</h2>
</div>
          <div class="loginform_wrap">
                    <p>The request was expired. Please login with OTP to activate your account or use the forgot password in the login page to create access.</p>
                  <div class="btn_sec">
                    <a class="btn btn-primary" href="/">Login</a>
                  </div>
              
          </div><!--loginform_wrap-->

    </asp:Panel>

    <asp:Panel ID="pnlResetPswSuccess" runat="server" Visible="false">
          <div class="login_head">
            <h2 class="mb-3">Success!</h2>
          </div><!--login_head-->

          <div class="loginform_wrap">
                    <p>Your password has been created successfully. <asp:Literal ID="ltrlSetPswContinue" runat="server">Please login with your credentials</asp:Literal></p>
        <div class="btn_sec">
            <asp:HyperLink ID="hlSetPswNavigate" CssClass="btn btn-primary" Text="Login" NavigateUrl="/login" runat="server"></asp:HyperLink>
                  </div>

          </div><!--loginform_wrap-->

    </asp:Panel>


</asp:PlaceHolder>

        <asp:HiddenField ID="hidSignupViewType" runat="server" />
              <div class="error_msg_wrap mt-2 mb-1 ht-20">
                    <span class="error_msg_wrap"><asp:Literal ID="ltrResult" runat="server" EnableViewState="False"></asp:Literal></span>
                  </div>


              
          
        </div>
        <!-- login-card-body -->
      </div>

      <div class="copyright copyright_m_view">© 2023 <a href="https://www.grozeo.com" target="_blank">Grozeo</a>. All rights reserved.</div>
    </div>
    <!-- login-box -->
<%if (plcSignupGSTSuccess.Visible)
    {  %>
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />
<% } %>

<div id="modalSkipGST" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">

<div class="modal-content tx-size-sm">
          
          <div class="modal-body">
            <div class="text-center">
              <h5 class="tx-inverse">Welcome to Grozeo - the new world of Retail</h5>
                <% if (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
                    { %>
              <p class="m-0">If you do not have access to the GSTN-registered Email Address and Mobile Number, you can skip this stage by clicking the button below. Since the <%=RetalineProAgent.Service.Store.VATService.VATLabel %> is required for Merchants to operate ecommerce portals in accordance with government legislation, the site will be displayed as <%=RetalineProAgent.Service.Store.VATService.VATLabel %> Verification Pending with Restricted Checkout. If you wish to modify the <%= RetalineProAgent.Service.Store.VATService.VATLabel %>, you can do so by clicking the button below.</p>
                <%}
                    else
                    { %>
                <p class="m-0">If you choose not to be a <%= RetalineProAgent.Service.Store.VATService.VATLabel %> registered merchant, you can skip this step by clicking the button below. It is recommended that you link your <%= RetalineProAgent.Service.Store.VATService.VATLabel %> number in order to gain listing privileges on Grozeo.</p>
                <% } %>
            </div>
          </div><!-- modal-body -->
          <div class="modal-footer d-flex justify-content-center">
              <asp:Button CssClass="btn btn btn-outline-secondary" formnovalidate ID="btnSkipGSTValidation" OnClick="lbtnSkipGSTVerification_Click" runat="server" Text="Skip GST Validation" />
              <asp:Button CssClass="btn btn btn-outline-secondary" formnovalidate ID="btnChangeGSTNumber" OnClick="lbtnChangeGST_Click" runat="server" Text="Change GST Number" />
            <button type="button" formnovalidate class="btn btn btn-outline-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
        
      </div><!-- modal-dialog -->
    </div>
  </div>
<div class="modal fade" id="EnrollmentAgreement">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content tx-size-sm">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h5 class="modal-title tx-dark mb-0">GROZEO GLOBAL HOLDINGS LTD TERMS AND CONDITIONS OF SERVICE</h5>
      </div>
      <div class="modal-body pd-20">
          <div class="row row-sm">
        <div class="col-12">    

          <p>These Terms and Conditions ("Terms") constitute a legal agreement between you and Grozeo ("Grozeo," "we," "our," or "us") and govern your access to and use of our website, app, and other software solutions (collectively, the "Services"). By accessing or using the Services, you agree to be bound by these Terms.</p>

          <h6>1. Accepting the Terms</h6>
          <p>By accessing or using the Services, you agree to these Terms and our Privacy Policy, which is incorporated into these Terms by reference. If you do not agree to these Terms or the Privacy Policy, you must not use or access the Services.</p>

          <h6>2. Changes to the Terms</h6>
          <p>We may modify these Terms from time to time. If we make material changes to these Terms, we will notify you by posting the updated Terms on our website or through other reasonable means. Your continued use of the Services after the effective date of the updated Terms constitutes your agreement to the updated Terms.</p>

          <h6>3. Use of the Services</h6>
          <p>You may use the Services only for lawful purposes and in accordance with these Terms. You must not use the Services in any way that violates any applicable federal, state, local, or international law or regulation (including, without limitation, any laws regarding the export of data or software to and from the United Kingdom or other countries).</p>

          <h6>4. Account Registration</h6>
          <p>To access certain features of the Services, you may be required to create an account with Grozeo. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete. You are responsible for maintaining the confidentiality of your account and password and for restricting access to your computer or mobile device. You agree to accept responsibility for all activities that occur under your account or password.</p>

          <h6>5. Intellectual Property</h6>
          <p>The Services and all content and materials included on or otherwise made available through the Services, including, without limitation, the Grozeo logo, all designs, text, graphics, pictures, information, data, software, sound files, other files and the selection and arrangement thereof (collectively, "Grozeo Content") are the property of Grozeo or its licensors or users and are protected by United Kingdom and international copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>

          <h6>6. User Content</h6>
          <p>You may submit or upload content, including but not limited to, text, images, videos, and other materials (collectively, "User Content") to the Services. By submitting or uploading User Content to the Services, you grant Grozeo a non-exclusive, transferable, sub-licensable, royalty-free, worldwide license to use, copy, modify, create derivative works based on, distribute, publicly display, publicly perform, and otherwise exploit in any manner such User Content in all formats and distribution channels now known or hereafter devised (including in connection with the Services and Grozeo's business and on third-party sites and services), without further notice to or consent from you, and without the requirement of payment to you or any other person or entity.</p>

          <h6>7. Prohibited Uses</h6>
          <p style="margin-bottom: 0;">You may use the Services only for lawful purposes and in accordance with these Terms. You agree not to use the Services:</p>
          <ul>
            <li style="margin-bottom: .5rem;">In any way that violates any applicable federal, state, local, or international law or regulation.</li>
            <li style="margin-bottom: .5rem;">For the purpose of exploiting, harming, or attempting to exploit or harm minors in any way by exposing them to inappropriate content, asking for personally identifiable information, or otherwise.</li>
            <li style="margin-bottom: .5rem;">To transmit, or procure the sending of, any advertising or promotional material, including any "junk mail," "chain letter," "spam," or any other similar solicitation.</li>
            <li style="margin-bottom: .5rem;">To impersonate or attempt to impersonate Grozeo, a Grozeo employee, another user, or any other person or entity (including, without limitation, by using email addresses or screen names associated with any of the foregoing).</li>
            <li style="margin-bottom: .5rem;">To engage in any other conduct that restricts or inhibits anyone's use or enjoyment of the Services, or which, as determined by Grozeo, may harm Grozeo or users of the Services or expose them to liability.</li>
          </ul>

          <h6>8. Termination</h6>
          <p>Grozeo may terminate your access to and use of the Services at any time and for any reason without notice or liability to you. Upon any termination, discontinuation, or cancellation of the Services or your account, the following provisions of these Terms will survive: Sections 5 through 12.</p>

          <h6>9. Disclaimer of Warranties</h6>
          <p>THE SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT. GROZEO DOES NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED OR ERROR-FREE, THAT DEFECTS WILL BE CORRECTED, OR THAT THE SERVICES OR THE SERVER(S) THAT MAKE THE SERVICES AVAILABLE ARE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS. GROZEO DOES NOT WARRANT OR MAKE ANY REPRESENTATIONS REGARDING THE USE OR THE RESULTS OF THE USE OF THE SERVICES IN TERMS OF THEIR CORRECTNESS, ACCURACY, RELIABILITY, OR OTHERWISE. YOU ASSUME ALL RESPONSIBILITY AND RISK FOR YOUR USE OF THE SERVICES.</p>

          <h6>10. Limitation of Liability</h6>
          <p>IN NO EVENT SHALL GROZEO, ITS DIRECTORS, OFFICERS, EMPLOYEES, OR AGENTS BE LIABLE TO YOU OR ANY THIRD PARTY FOR ANY DAMAGES WHATSOEVER, INCLUDING, WITHOUT LIMITATION, INDIRECT, INCIDENTAL, SPECIAL, PUNITIVE, OR CONSEQUENTIAL DAMAGES, ARISING OUT OF OR IN CONNECTION WITH YOUR USE OR INABILITY TO USE THE SERVICES, EVEN IF GROZEO HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. GROZEO'S LIABILITY TO YOU FOR ANY CAUSE WHATSOEVER, AND REGARDLESS OF THE FORM OF THE ACTION, WILL AT ALL TIMES BE LIMITED TO THE AMOUNT PAID, IF ANY, BY YOU TO GROZEO FOR THE SERVICES DURING THE TERM OF YOUR USE OF THE SERVICES.</p>

          <h6>11. Indemnification</h6>
          <p>You agree to indemnify, defend, and hold harmless Grozeo, its affiliates, licensors, and service providers, and its and their respective officers, directors, employees, contractors, agents, licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to your violation of these Terms or your use of the Services.</p>
          
          <h6>12. Governing Law and Jurisdiction</h6>
          <p>These Terms and any dispute or claim arising out of or in connection with them or their subject matter or formation (including non-contractual disputes or claims) shall be governed by and construed in accordance with the laws of the United Kingdom. The courts of the United Kingdom shall have exclusive jurisdiction to settle any dispute or claim that arises out of or in connection with these Terms or their subject matter or formation (including non-contractual disputes or claims).</p>

          <h6>13. Entire Agreement and Severability</h6>
          <p>These Terms constitute the entire agreement between you and Grozeo regarding the Services and supersede all prior and contemporaneous agreements, proposals, or representations, whether written or oral. If any provision of these Terms is held to be invalid, illegal, or unenforceable in any respect under any applicable law or rule in any jurisdiction, such invalidity, illegality, or unenforceability will not affect the validity, legality, or enforceability of any other provision of these Terms, and these Terms will be reformed, construed, and enforced in such jurisdiction as if such invalid, illegal, or unenforceable provision had never been contained herein.</p>

          <h6>14. Waiver</h6>
          <p>No waiver of any provision of these Terms will be deemed a further or continuing waiver of such provision or any other provision, and any failure to assert any right or provision under these Terms will not constitute a waiver of such right or provision.</p>

          <h6>15. Assignment</h6>
          <p>You may not assign or transfer these Terms or any rights granted hereunder, by operation of law or otherwise, without Grozeo's prior written consent, and any attempt by you to do so without such consent will be null and of no effect. Grozeo may assign or transfer these Terms or any rights granted hereunder without restriction or notification.</p>

          <h6>16. Third-Party Services</h6>
          <p>The Services may contain links to or integrate with third-party websites, applications, and services ("Third-Party Services") that are not owned or controlled by Grozeo. Grozeo does not endorse or assume any responsibility for any such Third-Party Services. If you access any Third-Party Services from the Services, you do so at your own risk and you agree that Grozeo will have no liability arising from your use of or access to any Third-Party Services.</p>

          <h6>17. Export Control</h6>
          <p>You agree to comply with all applicable export and import control laws and regulations of the United Kingdom and other applicable jurisdictions, and not to transfer, export, or re-export directly or indirectly, any content, including software, provided through the Services to any country or destination prohibited by such laws and regulations.</p>

          <h6>18. Survival</h6>
          <p>The provisions of these Terms that by their nature should survive the termination of these Terms shall survive such termination, including but not limited to Sections 5 through 12.</p>

          <h6>19. Apps</h6>
          <ol>
            <li style="margin-bottom: .5rem;">App Store Terms: You acknowledge that your use of the Services on the Apple App Store or Android App Store is subject to the terms and conditions of the respective app store. You agree to comply with all applicable terms and conditions of the app store and acknowledge that these terms and conditions are incorporated by reference into these Terms.</li>
            <li style="margin-bottom: .5rem;">App Store License: You acknowledge that the license granted to you under these Terms is limited to the right to use the Services as provided by Grozeo and does not include any right to use the Services on the Apple App Store or Android App Store. You agree to comply with all applicable app store license terms and conditions.</li>
            <li style="margin-bottom: .5rem;">End User License Agreement: If Grozeo provides an End User License Agreement (EULA) for the Services on the Apple App Store or Android App Store, you agree to comply with the terms and conditions of the EULA, which is incorporated by reference into these Terms.</li>
            <li style="margin-bottom: .5rem;">App Store Disclaimer: Grozeo disclaims any and all warranties or liabilities related to the Services on the Apple App Store or Android App Store, and you acknowledge that Grozeo has no responsibility for the availability, performance, or security of the Services on these platforms.</li>
            <li style="margin-bottom: .5rem;">App Store Review and Rating: If you choose to rate or review the Services on the Apple App Store or Android App Store, you agree to do so in compliance with the terms and conditions of the app store and in a manner that is accurate, truthful, and does not violate any applicable laws or regulations.</li>
          </ol>

          <h6>20. Cross-Sales and Promotions</h6>
          <ol>
            <li style="margin-bottom: .5rem;">The User acknowledges and agrees that Grozeo will provide cross-sales and promotional activities with third-party entities ("Partners"). These activities may offer the User and User customers the opportunity to engage with, purchase, or subscribe to products or services offered by Partners.</li>
            <li style="margin-bottom: .5rem;">In facilitating these cross-sales and promotional endeavors, the User expressly consents to the sharing of their business, their customer, and order information with Partners, solely for the purposes of order fulfillment, product delivery, and enhancing the User's experience with the Services and marketing related activities. </li>
          </ol>

          <h6>21. Data Utilization for Customer Sign-Up Simplification</h6>
          <ol>
            <li style="margin-bottom: .5rem;">For the purpose of easing the process of User and User Customer sign-up on Partner platforms, Grozeo may store Users' and User's customer data, including, but not limited to, personal information, contact details, address, and preferences, within the Grozeo database infrastructure.</li>
            <li style="margin-bottom: .5rem;">By consenting to these Terms, the User grants Grozeo the explicit authority to utilize such stored data to simplify the User's and User customer sign-up and authentication process on third-party platforms, leveraging Grozeo's technology.</li>
            <li style="margin-bottom: .5rem;">The User and User Customers retains the right to opt-out of such data sharing and usage at any point. To exercise this right, the User shall contact Grozeo via the designated support channels. Further information on data management practices is delineated in the Grozeo Privacy Policy.</li>
          </ol>

          <h6>22. Responsibilities and Liabilities</h6>
          <p>The User acknowledges that any transactions, interactions, or engagements with Partners through cross-sales or promotional activities are governed by the terms and conditions of the respective Partners. Grozeo disclaims any liability for the products, services, or content provided by Partners.</p>
          <p>The User agrees to indemnify and hold harmless Grozeo, its affiliates, officers, agents, and employees from any claim, demand, loss, damage, cost, or liability arising from the User's and User's Customer engagement in cross-sales or promotions or from the sharing or use of the User's and User’s Customer data in accordance with these Terms.</p>

          <h6>23. Amendment of Terms</h6>
          <p>Grozeo reserves the right to amend these Terms at any time to reflect changes to its cross-sales, cross-promotions, and data-sharing practices. Such amendments will be effective immediately upon posting the revised Terms on the Grozeo website or notifying Users through other reasonable means. Continued use of the Services following such modifications constitutes the User's acceptance of the new Terms.</p>

          <h6>Contact Us</h6>
          <p>If you have any questions about these Terms or the Services, please contact us at support@grozeo.com or info@grozeo.com</p>
          <p>Thank you for using Grozeo!</p>

        </div><!--col-12-->

          </div>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
          </form>

    <uc1:PopupAlert runat="server" id="PopupAlert1" />

<script type="text/javascript">
    var showinvcode = '<%= ConfigurationManager.AppSettings.Get("SignupRestrictByInvite") %>';
    authentication.properties.showInvitationCode = (<%= String.IsNullOrEmpty(CurInvitationCode) ? 1 : 0 %> == 1 && showinvcode === '1' ? true : false);

    function selectstate(stateval) {
        var optionval = $('#<%= selState.ClientID %>').find("option:contains('" + stateval + "')").val();
        if (optionval && optionval != '') {
            $("#<%= selState.ClientID %>").val(optionval);
                if (typeof (Page_ClientValidate) == 'function') {
                    Page_ClientValidate('novalidate');
                }
                $('#<%= selState.ClientID %>').change();
        }
    }

</script>
</body>

</html>
