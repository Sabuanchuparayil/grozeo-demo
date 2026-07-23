<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="signupOld.aspx.cs" Inherits="RetalineProAgent.signupOld" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<%@ Register Src="~/Controls/ctrlSignupLeadPopup.ascx" TagPrefix="uc1" TagName="ctrlSignupLeadPopup" %>
<%@ Register Src="~/Controls/PopupAlert.ascx" TagPrefix="uc1" TagName="PopupAlert" %>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Partner | Sign up</title>
<link href="<%= RetalineProAgent.Service.Common.FavIcon %>" rel="shortcut icon" type="image/x-icon" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> 
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/Content/custom/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="/Content/css/custom/intlTelInput.css">
  <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">

  <link rel="stylesheet" href="/Content/css/custom/custom.css">
 
          <script src="/content/lib/jquery/js/jquery.js"></script>
    <script src="/content/lib/popper.js/js/popper.js"></script>
    <script src="/content/lib/bootstrap/js/bootstrap.js"></script>
<!-- Bootstrap 4 -->
<script src="/Content/custom/dist/js/adminlte.min.js"></script>
<script src="/Content/js/custom/intlTelInput-jquery.min.js"></script>

    <script src="/content/js/slim.js"></script>
<script src="/Content/js/custom/master.js"></script>
<script src="/Content/js/custom/auth.js"></script>
<script src="/Content/js/bootstrap-multiselect.min.js"></script>
    <% int maxbusinessTypeRestricted =  0; 
        try { maxbusinessTypeRestricted= Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted")??"0"); } catch { maxbusinessTypeRestricted = 0; } 
        %>

<script src="<%= String.Format("https://www.google.com/recaptcha/api.js?render={0}", ConfigurationManager.AppSettings.Get("Recaptcha.Key")) %>"></script>

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
                  });
        authentication.url.captchaVerification = '/api/auth/VerifyCaptchaToken';
        authentication.properties.captchaKey = '<%= ConfigurationManager.AppSettings.Get("Recaptcha.Key") %>';
        authentication.url.getOtp = '/api/auth/GetOTP';
        authentication.properties.countryPhoneCode = '<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>';
        authentication.properties.mobilepatern = <%= (ConfigurationManager.AppSettings.Get("IsDemo") == "1"? "null" : (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? @"/^[0-9]{9,13}$/" : @"/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/") ) %>

        function businessTypeValidation(source, arguments) {
            var selectedOptions = jQuery('#lstBusinessTypes option:selected');
            arguments.IsValid = selectedOptions.length > 0;
            //$(source).parents("div").css({ "background-color": "red" });
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
    <style>
        .mx-wt-150{
            max-width: 157px;
        }
       .min-margin-8{
           margin-left: 10px;
       }
       .partitioned6 {
            padding-left: 22px;
            letter-spacing: 40px;
            border: 0;
            background-image: linear-gradient(to left, #8a8a8a 70%, rgba(255, 255, 255, 0) 0%);
            background-position: bottom;
            background-size: 54px 1px;
            background-repeat: repeat-x;
            background-position-x: 50px;
            width: 420px;
            min-width: 420px;
            font-size: 25px;
            background-color: transparent;
        }
    </style>

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

<div class="page_header">
    <div class="grozeologo">
      <a href="<%= String.Format("//{0}", ConfigurationManager.AppSettings.Get("newsitedomain").Replace("[title]-{0}.", "")) %>">
        <img src="/content/images/login/grozeo_logo.svg">
      </a>
    </div>
    <h4>Already a Grozeo Partner? <a href="/login">Log in</a></h4>
  </div>
  <div class="login_sec_wrp d-flex flex-wrap <%= (plcSignupGSTSuccess.Visible ? "create_store_page" : "" )%>">

    <div class="login_img p-10">
      
      <div class="login_nfographic">
          <% if (CurViewType == "2" || CurViewType == "3")
              { %>
        <img src="<%= String.Format("/content/images/login/{0}", (CurVATType == 2 ? "unlocksales.svg" : "unlocksales_vat.svg")) %>"/>

          <% }
              else if (CurViewType == "6")
              { %>
        <img src="<%= String.Format("/content/images/login/{0}", (CurVATType == 2 ? "proudpromoter.svg" : "proud_promoter_vat.svg")) %>"/>

          <% }
              else if (CurViewType == "4")
              { %>
        <img src="/content/images/login/retailrevolution.svg"/>
          <% }
              else
              { %>
        <img src="/content/images/login/getsetgo.svg"/>
          <% } %>
      </div>
    </div>

    <div class="login-box">
          <form runat="server" id="form1">

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
              <h2 class="mb-2">Verify Mobile with OTP</h2>
                <p class="login-box-msg">In order to keep the communication with you in future, we need a mobile number that you have access. Please provide a valid mobile number and enter the OTP received to validate the same.</p>
            </div><!--login_head-->

            <div class="loginform_wrap">
    
              <div class="form-row m-0 dvmobilenum">
                <label>Your Mobile Number</label>
                  <div class="input-group d-flex flex-nowrap">
                    <input id="txtSignupMobileNumber" runat="server" type="tel" autofocus="autofocus" class="form-control txtPhone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off" name="Phone" required>
                    <div class="formtbtn"><input class="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2" value="Send OTP" type="submit" id="btn_Submit_mobile"></div>
                  </div>
              </div>
              <div class="form-row m-0 dvinvcode" style="display:none;">
                  <p class="login-box-msg">Grozeo currently operates in selected areas only. If you have received an invitation code from us, please enter it here or request one.</p>
                <label class="mt-3">Invitation Code</label>
                <div class="input-group d-flex flex-nowrap">
                    <asp:TextBox ID="txtInvitationCode" runat="server" ClientIDMode="Static" CssClass="form-control" autocomplete="nonetxtuser" aria-autocomplete="none" placeholder="Enter invitation code"></asp:TextBox>
                  <%--<input type="tel" id="txtPhone" style="display: none;" required class="form-control" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10">--%>
                  <div class="formtbtn"><input class="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2" value="Submit" type="submit" id="btn_Submit_invitationcode"></div>
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
                <h5 class="mb-3 font-weight-normal">Enter the OTP received in mobile</h5>
                  <asp:Panel ID="pnlOTP" runat="server" DefaultButton="lbtnSignupOtpVerify" CssClass="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="partitioned" id="txtOTP" runat="server" name="txtOTP" required type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"/>
                      </div>
                  </div>
                  </div>
                    <asp:LinkButton ID="lbtnSignupOtpVerify" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Verify" OnClick="btnVerifyOTP_Click" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left"></asp:LinkButton>
                  <%--<input type="submit" required name="btnVerifyOTP" value="Verify" id="btnVerifyOTP" class="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left">--%>
                </asp:Panel>

                <div class="loginlinks_option mt-4">
                  <span class="reciveotp">
                    <a id="resend-trigger" href="javascript:void(0)" onclick="">Resend OTP</a>
                  </span>
                    <a id="lbEditMobile" href="">Change Mobile Number</a>
                </div>

              </div>
              

            </div><!--loginform_wrap-->

    </asp:PlaceHolder>

</asp:PlaceHolder>

<asp:PlaceHolder runat="server" ID="plcSignupGST">

<div class="login_head">
              <h2 class="mb-3"><%= (CurVATType == 2 ? "Verify GSTIN with OTP" : "Verify VAT") %></h2>
              <p id="pGSTText" runat="server" class="login-box-msg">GSTN Registration is mandatory to operate online store in India.<br>Provide your GST Number to register as Merchant Partner </p>
            </div><!--login_head-->

            <div class="loginform_wrap">
    
              <div class="form-row m-0">
                <label><%= (CurVATType == 2 ? "Your GST Number" : "Your VAT Number") %></label>
                <div class="input-group d-flex flex-nowrap">
                    <input id="txtGSTNumber" runat="server" autocomplete="off" type="text" class="form-control gstnumber" name="gstnumber" placeholder="Enter GSTIN #" required>
                    <div class="formtbtn"><asp:Button ID="btnSubmitGSTNumber" OnClick="lbtnGSTSendOtp_Click" runat="server" Text="Continue" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 " /></div>
                </div>
              </div>
              <div class="co-12 mt-2">
                <p id="pGSTINRequest" runat="server" class="mentioned dark_green mb-3">OTP will be send to the mobile and email that are linked to the GSTN.</p>
                <p id="pGSTINOTP" runat="server" class="dark_green">
                    <% if (CurVATType == 2)
                        { %>
                    OTP sent to 
                    <asp:Literal ID="ltrGSTMaskedEmail" runat="server"></asp:Literal>
                     and <asp:Literal ID="ltrGSTMaskedMobile" runat="server"></asp:Literal> &nbsp; &nbsp;
                    <% } %>
                    <asp:LinkButton ID="lbtnChangeGST" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" OnClick="lbtnChangeGST_Click" CssClass="changegstin">Change GSTIN</asp:LinkButton>
                    </p>
              </div>

<asp:PlaceHolder ID="plcSignupGSTOTP" Visible="false" runat="server">
    <asp:PlaceHolder ID="plcSignupGSTShowVerification" Visible="true" runat="server">
              <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal">Enter the OTP received in mobile/Email</h5>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="partitioned" id="gstOTP" runat="server" autocomplete="off" aria-autocomplete="none" required type="text" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                      </div>
                  </div>
                  </div>
                    <div class="formtbtn mt-3"><asp:Button ID="btnGSTOTPVerify" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 flot-left" Text="Continue" OnClick="btnGSTOTPVerify_Click" /></div>
                </div>

                <div class="loginlinks_option">
                  <span class="reciveotp mt-4">
                      <a href="" data-toggle="modal" data-target="#modalSkipGST">I don't have access to GST authorised mobile & Email</a>
                  </span>
                </div>

              </div>
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcSignupGSTSkipVerification" Visible="false" runat="server">
              <div style="margin-top: 40px;">
                <h5 class="mb-3 font-weight-normal"><%= (CurVATType == 2 ? "GST" : "VAT") %> Details</h5>
                  <p class="login-box-msg">Please verify that the <%= (CurVATType == 2 ? "GST" : "VAT") %> information displayed is accurate. Your store's tax inputs, etc., will be added to this <%= (CurVATType == 2 ? "GST" : "VAT") %> account.</p>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter">
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
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#modalSkipGST">I don't have <%= (CurVATType == 2 ? "GST" : "VAT") %></a>
                <% } %>
                </div>
            </div><!--loginform_wrap-->



</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupNoGST" runat="server" Visible="false">
    <asp:PlaceHolder ID="plcAdharView" runat="server">

        <div class="login_head">
              <h2 class="mb-3">Register without <%= (CurVATType == 2 ? "GST" : "VAT") %> Number</h2>
            <% if (CurVATType == 2)
                { %>
              <p class="login-box-msg">GST Registration is required to operate online store in India. Still you can register with your Adhaar account but only intra state operation is permitted.</p>
            <%}
                  %>
            </div><!--login_head-->


            <% if (CurVATType == 2)
                { %>
            <div class="loginform_wrap">
    
              <div class="form-row m-0">
                <label>Your Adhaar Number</label>
                <div class="input-group d-flex flex-nowrap">                  
                    <asp:TextBox ID="txtAdharNum" runat="server" CssClass="form-control gstnumber" TextMode="Number" MaxLength="12" autocomplete="off" placeholder="" required="required"></asp:TextBox>
                    <div class="formtbtn"><asp:Button ID="btnAdharSubmit" OnClick="btnAdharSubmit_Click" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 triger_submit" runat="server" Text="Submit" /></div>
                  <%--<input class="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 triger_submit" value="Submit" type="submit" id="btn_Submit_pan">--%>
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

                <asp:PlaceHolder ID="plcAdharVerify" runat="server" Visible="false">
                    <div class="co-12 mt-2">
                
                <p class="otpsent dark_green" style="display: block;">OTP has been sent to the registered number from UIDAI.</p>
              </div>
                                    <h5 class="mt-40 mb-3 font-weight-normal">Enter the OTP received in mobile</h5>
                <div class="form-row m-0">
                  <div class="input-group">
                    <div class="divOuter">
                      <div class="divInner">
                          <input class="partitioned6" id="txtAdharOTP" runat="server" required type="text" maxlength="6" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                      </div>
                  </div>
                  </div>
                  <asp:Button ID="btnAdharOTPSubmit" OnClientClick="$(this).closest('form').attr('childobj', this.id);" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 mt-3 flot-left" Text="Continue" OnClick="btnAdharOTPSubmit_Click" />

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
              <h2 class="mb-3">Register without <%= (CurVATType == 2 ? "GST" : "VAT") %> Number</h2>
            <% if (CurVATType == 2)
                { %>
              <p class="login-box-msg">GST Registration is mandatory to operate online store in India. Still you can register as an affiliate partner  of Grozeo to promote business among your contacts. You need to verify your PAN to register as an Affiliate Partner.</p>
            <%}
                else
                { %>
                <p><%= (CurVATType == 2 ? "GST" : "VAT") %> Priority will be given to registered merchants when listing on Grozeo. Set up your affiliate store if you dont have <%= (CurVATType == 2 ? "GST" : "VAT") %>.</p>
            <% } %>
            </div><!--login_head-->


            <% if (CurVATType == 2)
                { %>
            <div class="loginform_wrap">
    
              <div class="form-row m-0">
                <label>Your PAN Number</label>
                <div class="input-group d-flex flex-nowrap">                  
                  <input type="text" id="txtPAN" runat="server" required class="form-control gstnumber" autocomplete="off" placeholder="" maxlength="15">
                    <div class="formtbtn"><asp:Button ID="btnPANSubmit" OnClick="btnPANSubmit_Click" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 triger_submit" runat="server" Text="Submit" /></div>
                  <%--<input class="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 triger_submit" value="Submit" type="submit" id="btn_Submit_pan">--%>
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
              <h2 class="mb-0"><span>Welcome</span> <asp:Literal ID="ltrGstOrganization" runat="server"></asp:Literal></h2>
              <p class="login-box-msg"><asp:Literal ID="ltrGstAddress" runat="server"></asp:Literal></p>
            </div><!--login_head-->

            <div class="loginform_wrap">
                <label>Create your first store here</label>
              <div class="form-row m-0 row row-sm mt-1">
                <div class="input-group col-12 col-sm-6">
                    <asp:TextBox ID="txtStoreName" runat="server" autocomplete="off" CssClass="form-control mb-3"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Store Name"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store name is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>
                <div class="input-group col-12 col-sm-6">
                    <input id="txtContactPerson" runat="server" type="text" class="form-control mb-3" name="ContactPerson " autocomplete="off" placeholder="Contact Name">
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPerson" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact person is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>
                <div class="input-group col-12 col-sm-6">
                    <asp:TextBox ID="txtContactPhone" runat="server" autocomplete="off" CssClass="form-control mb-3" placeholder="Telephone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10"></asp:TextBox>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPhone" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact phone is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>

                <div class="input-group col-12 col-sm-6">
                    <input id="txtLoginEmail" runat="server" type="email" class="form-control mb-3" autocomplete="off" placeholder="Email (User ID)">
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLoginEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact email is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                  <%--<input name="EmailID" id="EmailID" type="email" class="form-control mb-3" placeholder="Email (User ID)" required="">--%>
                </div>
                <div class="input-group col-12 col-sm-6">
                    <asp:DropDownList ID="selBusinessTypes" AutoPostBack="true" data-placeholder="Choose business type" runat="server" AppendDataBoundItems="true" DataSourceID="SDSBusinessCategories" DataTextField="business_category_name" DataValueField="business_category_id"
                          CssClass="form-control" style="width: 100%;"><asp:ListItem Text="Select Business Category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selBusinessTypes" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select Business Category" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>
                <div class="input-group col-12 col-sm-6">
                                          <asp:ListBox ID="lstBusinessTypes" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
                    <asp:CustomValidator ControlToValidate="lstBusinessTypes" ClientValidationFunction="businessTypeValidation" ValidateEmptyText="true" runat="server" CssClass="col-12 error_msg_wrap" ErrorMessage="Please select retail category" ValidationGroup="CreateStore" />
                </div>

                  <label><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Search / " : "") %>Enter Store Address</label>
                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                      { %>
                    <div class="input-group col-12 col-sm-12" id="postcode_lookup"></div>
                      <div class="input-group col-12">
                        <asp:TextBox ID="txtAddr1UK" runat="server" autocomplete="off" CssClass="form-control mb-3 w-100 mx-wd-100p-force"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Address"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr1UK" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Address is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                      </div>
                  <% } %>

                <div class="input-group col-12 col-sm-6">
                    <asp:TextBox ID="txtLocation" onfocus="if(!authentication.properties.mapTriggered){$('#ADDRESS').modal('show'); authentication.properties.mapTriggered=true;}" runat="server" data-toggle="modal" data-backdrop="static" autocomplete="off" data-keyboard="false" data-target="#ADDRESS" required CssClass="form-control mb-3" placeholder="Click to load map"/>
                    <i class="icon_map"></i>
                  <%--<input name="StoreLocate" id="StoreLocate" type="text" class="form-control mb-3" placeholder="Locate store in map" required="">--%>
                    <asp:HiddenField ID="hidMapAddr" runat="server" />
                </div>
                <div class="input-group col-12 col-sm-6">
                    <asp:TextBox ID="txtPinCode" autocomplete="off" runat="server" CssClass="form-control mb-3" placeholder="Postcode"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtPinCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Post code is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>

                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") != "UK")
                      { %>
                 <div class="input-group col-12">
                  <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control mb-3 w-100 mx-wd-100p-force" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder="Address"/>
                </div>
                  <% } %>


                <div class="input-group col-12 col-sm-6">                
                  <asp:DropDownList ID="selState" OnSelectedIndexChanged="selState_SelectedIndexChanged" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control mb-3" style="width: 100%;" AppendDataBoundItems="true" ></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selState" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select state" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    <asp:HiddenField ID="hidState" runat="server" />
                </div>
                <div class="input-group col-12 col-sm-6">                
                  <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="false" ></asp:DropDownList>
                    <asp:HiddenField ID="hidDistrict" runat="server" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selDistrict" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select district" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                </div>


                <div class="btnsec col-12">
                    <div class="error_msg_wrap mb-3 ht-20 mandatory">
                    <span class="error_msg">*All fields are mandatory.</span>
                  </div>
                    <div class="formtbtn"><asp:Button ID="btnSubmitAccount" ValidationGroup="CreateStore" OnClientClick="if(validateCreateStore()){$(this).closest('form').attr('childobj', this.id);}else{return false;}" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140" Text="Create Store" OnClick="btnSubmitAccount_Click" /></div>
                    <asp:HyperLink ID="hlGoHome" runat="server" NavigateUrl="/signup" Visible="false" Text="Verify" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140"></asp:HyperLink>
                  <%--<input class="btn btn-primary btn-block btn-drk-green mx-w-140" value="Verify" type="submit" id="Verify_store">--%>
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
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />
                        <asp:HiddenField ID="hidLat" runat="server" />
                        <asp:HiddenField ID="hidLong" runat="server" />
                        <asp:HiddenField ID="HiddenField1" runat="server" />

        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        { %>

<script src="https://cdn.getaddress.io/scripts/getaddress-find-2.0.0.min.js"></script>
<script>
    getAddress.find(
        'postcode_lookup',
        '<%= ConfigurationManager.AppSettings.Get("GetAddressIOAPIKey")%>',
        {
            input: {
                id: 'getaddress_input',  /* The id of the textbox' */
                name: 'getaddress_input',  /* The name of the textbox' */
                class: 'form-control mb-3 mx-wt-150',  /* The class of the textbox' */
                label: 'Enter your Postcode'  /* The label of the textbox' */
            },
            button: {
                id: 'getaddress_button',  /* The id of the botton' */
                class: 'btn btn-primary btn-block btn-drk-green mx-wt-150 ml-2',  /* The class of the botton' */
                label: 'Find Address',  /* The label of the botton' */
                disabled_message: 'disabled message'  /* The disabled message of the botton' */
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
        //console.log(e.address);

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



<%--<script src="https://cdn.getaddress.io/scripts/getaddress-autocomplete-1.1.3.min.js"></script>
<script>
        getAddress.autocomplete('<%= txtAddr1UK.ClientID%>',
        'cfZrFf-w5EKBKcerWmJejA37605', {
        container_class_names:['input-group', 'col-12', 'col-sm-12'],
        css_prefix:'',
        show_all_for_postcode:true,

        //output_fields:{
        //    postcode:'#<%= txtPinCode.ClientID%>',  /* The id of the element bound to 'postcode' */
        //},

    });
    document.addEventListener("getaddress-autocomplete-address-selected", function(e){
        //console.log(e.address);
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

        $('#<%= selState.ClientID %>').change();

    })

    <%--getAddress.location(
        '<%= txtAddr2.ClientID%>',
        'cfZrFf-w5EKBKcerWmJejA37605',
        {
            container_class_names:['col-sm-12'],
            show_all_for_postcode:true
        } 
    );
</script>--%>
    <%} %>


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
                    <asp:Button ID="btnSetPassword" OnClick="btnSetPassword_Click" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2" Text="Submit" ValidationGroup="SetPassword" />
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
                    <a class="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2" href="/">Login</a>
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
            <asp:HyperLink ID="hlSetPswNavigate" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140 ml-2" Text="Login" NavigateUrl="/login" runat="server"></asp:HyperLink>
                  </div>

          </div><!--loginform_wrap-->

    </asp:Panel>


</asp:PlaceHolder>

        <asp:HiddenField ID="hidSignupViewType" runat="server" />
              <div class="error_msg_wrap mt-2 mb-1 ht-20">
                    <span class="error_msg_wrap"><asp:Literal ID="ltrResult" runat="server" EnableViewState="False"></asp:Literal></span>
                  </div>


          
        </div>
        <!-- /.login-card-body -->
      </div>
<div id="modalSkipGST" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">

<div class="modal-content tx-size-sm">
          
          <div class="modal-body">
            <div class="text-center">
              <h5 class="tx-inverse">Welcome to Grozeo - the new world of Retail</h5>
                <% if (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
                    { %>
              <p class="m-0">If you do not have access to the GSTN-registered Email Address and Mobile Number, you can skip this stage by clicking the button below. Since the GST is required for Merchants to operate ecommerce portals in accordance with government legislation, the site will be displayed as GST Verification Pending with Restricted Checkout. If you wish to modify the GST, you can do so by clicking the button below.</p>
                <%}
                    else
                    { %>
                <p class="m-0">If you choose not to be a <%= (CurVATType == 2 ? "GST" : "VAT") %> registered merchant, you can skip this step by clicking the button below. It is recommended that you link your <%= (CurVATType == 2 ? "GST" : "VAT") %> number in order to gain listing privileges on Grozeo.</p>
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
          </form>
                <div class="copyright">© <a href="https://grozeo.com">grozeo.com</a></div>
    </div>
    <!-- /.login-box -->

  </div><!--login_sec_wrp-->


<script type="text/javascript">

<%--    function showPostcoder(obj) {
        var txtsearch = $("#<%= txtAddrSearch.ClientID %>");
        if (txtsearch && IdealPostcodes) {
            $('#txtAddrSearch').closest('div').show(); $('#txtAddrSearch').show();
            $(obj).closest('div').hide();
            $('txtAddrSearch').focus();
            $('txtAddrSearch').val($(obj).val());
        }
    }--%>

    $(document).ready(function () {

        //$(".triger_submit").click(function () {
        //    $(".otp_toggle").slideDown();
        //    $(".mentioned").hide();
        //    $(".otpsent").show();
        //    $(this).addClass("disabled");
        //});

        $(function () {
            var code = "<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode")?? "+91" %>"; // Assigning value from model.
            $('.txtPhone').val(code);
            $('.txtPhone').intlTelInput({
                autoHideDialCode: true,
                autoPlaceholder: "ON",
                dropdownContainer: document.body,
                formatOnDisplay: true,
                hiddenInput: "full_number",
                initialCountry: "<%= ConfigurationManager.AppSettings.Get("CountryCode")??"IN" %>",
                nationalMode: true,
                placeholderNumberType: "MOBILE",
                preferredCountries: ['<%= ConfigurationManager.AppSettings.Get("CountryCode")??"IN" %>'],
                separateDialCode: true
            });
        });

    });

</script>

<asp:PlaceHolder ID="plsNewUI" runat="server" Visible="false">
  <div class="login_sec_wrp d-flex flex-wrap">


        <div class="login_img">
      <img src="/content/images/login/login_img.png"/>
    </div>

    <div class="login-box">
      <div class="card">
        <div class="card-body login-card-body">
            <form>


<asp:PlaceHolder ID="plcSignupMobileOld" runat="server">

          <asp:Panel ID="pnlSignupMobile" runat="server">

          <div class="login_head">
            <div class="login-logo">
              <h5 class="tx-dark">Welcome to Grozeo - the new world of Retail</h5>
            </div>
            <h2>Sign up with Mobile</h2>
            <p class="login-box-msg p-0">Enter your mobile number to receive the verification code</p>
          </div><!--login_head-->

                    
          <div class="loginform_wrap">            
    
              <div class="form-row">
    
                <div class="input-group mb-3 col-12">
              
                </div>
    
              </div>
    
              
              
              <div class="form-row">
                <div class="col-12">
                <asp:Button ID="btnVerifyMobile" CssClass="btn btn-primary btn-block btn-drk-green" runat="server" Visible="false" Text="Continue" OnClick="btnVerifyMobile_Click" />
                    <button id="mobileloginbtn" class="btn btn-primary btn-block btn-drk-green" value="Continue"></button>
                </div>
                
              </div>

              <div class="form-row dvmobileotp" style="display: none;">    
                <div class="onetime_mobile_otpcod">

                  <div id="otp" class="inputs d-flex flex-row justify-content-between mt-3 mb-3 otpinput">
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="Number1" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="Number2" autocomplete="off" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="Number3" autocomplete="off" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="Number4" autocomplete="off" maxlength="1" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                    </div>
                    
                  </div>
      
                </div> <!--onetime_mobile_otpcod-->
              </div> <!--form-row-->




          </div><!--loginform_wrap-->
          </asp:Panel>

          <asp:Panel ID="pnlSignupMobileOTP" Visible="false" runat="server">
              <asp:Label ID="countdown" CssClass="otp_countdown" runat="server" ClientIDMode="Static"></asp:Label>
          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
            <h2>Verify Mobile Number</h2>
            <p class="login-box-msg">Enter the verification code received in <asp:Literal runat="server" ID="ltrCurMobileNum"></asp:Literal></p>
          </div><!--login_head-->
                    
          <div class="loginform_wrap">
            
              <div class="form-row">    
                <div class="onetime_mobile_otpcod">

                  <div id="otp" class="inputs d-flex flex-row justify-content-between mt-3 mb-3 otpinput">
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="first" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="second" autocomplete="off" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="third" autocomplete="off" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" id="fourth" autocomplete="off" maxlength="1" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                    </div>
                    
                  </div>
      
                </div> <!--onetime_mobile_otpcod-->
              </div> <!--form-row-->
              
              <div class="form-row">
                <div class="col-12">
                <%--<asp:Button ID="btnVerifyOTP" runat="server" CssClass="btn btn-primary btn-block mt-4 btn-drk-green" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Continue" OnClick="btnVerifyOTP_Click" />--%>
                </div>
                <div class="col-12">
                  <div class="loginlinks_option">
                    <span class="reciveotp" >Didnt Recieve OTP? <asp:LinkButton runat="server" OnClick="btnVerifyMobile_Click">Resend</asp:LinkButton></span>
                      <asp:LinkButton runat="server" ID="lbEditMobile" Text="Edit Mobile Number" OnClick="lbEditMobile_Click"></asp:LinkButton>
                  </div>
                </div>
              </div>
          </div><!--loginform_wrap-->

          </asp:Panel>


</asp:PlaceHolder>

<asp:PlaceHolder runat="server" ID="plcSignupGSTOld">

          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
            <h2>Validate Business with GST</h2>
            <p class="login-box-msg p-0 mb-3">As per the statutory rules, GST is mandatory to be an Ecom operator India Provide your GST and get it verified now</p>
          </div><!--login_head-->

          <div class="loginform_wrap">
    
              <div class="form-row">    
                <div class="input-group mb-3 col-12">
                    
                </div>
    
              </div>
              
              <div class="form-row">
                <div class="col-12">
                    
                </div>
                <div class="col-12">
                    <div class="backbtnsec">
                      
                  </div>
                  <%--<div class="backbtnsec flex-wrap">
                      <asp:LinkButton ID="lbtnGSTSendOtp" runat="server" OnClick="lbtnGSTSendOtp_Click">Send OTP to the Mobile Number/ Email ID registered with GST</asp:LinkButton>
                      <asp:LinkButton Text="I don't remember the GSTIN Username" OnClick="lbtnNoGST_Click" runat="server"></asp:LinkButton>
                  </div>--%>
                </div>
              </div>
          </div><!--loginform_wrap-->


</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupGSTOTPOld" Visible="false" runat="server">

          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
            <h2>Validate GST with OTP</h2>
            <p class="login-box-msg p-0 mb-3">We have sent validation code to the contacts registered with GSTN<br>Enter the Code received in <strong></strong></p>
          </div><!--login_head-->

                    
          <div class="loginform_wrap">
            
              <div class="form-row">
    
                <div class="input-group mb-3 col-12">
                    <input id="txtGSTNum" runat="server" type="number" class="form-control" name="Phone" placeholder="" disabled>
                </div>

                <div class="onetime_mobile_otpcod">

                  <div id="otp" class="inputs d-flex flex-row justify-content-between mt-3 mb-3 otpinput">
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="go1" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="go2" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="go3" maxlength="1" />
                    </div>
                    <div class="input-group">
                      <input runat="server" class="m-2 text-center form-control rounded" required type="number" autocomplete="off" id="go4" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="1" />
                    </div>
                    
                  </div>
      
                </div> <!--onetime_mobile_otpcod-->
    
              </div>
    
              <div class="form-row">
                <div class="col-12">
                  
                </div>
                <div class="col-12">
                  <div class="backbtnsec">
                      <%--<asp:LinkButton ID="lbtnChangeGST" OnClick="lbtnChangeGST_Click" CssClass="text-center" runat="server">I don't have access to the mentioned mobile number and Email ID</asp:LinkButton>--%>
                  </div>
                </div>
                
                  <%--<div class="col-12">
                  <div class="backbtnsec">
                      <asp:LinkButton ID="lbtnSkipGSTVerification" OnClick="lbtnSkipGSTVerification_Click" CssClass="text-center" runat="server">Skip GST Verification. I'll verify later.</asp:LinkButton>
                  </div>
                </div>--%>

              </div>
              
          </div><!--loginform_wrap-->


<!-- LARGE MODAL -->
    <!-- modal -->



<div id="modalSkipGST123" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">GST Validation Alert</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-25">
              <asp:Literal ID="ltrModelBodyContent" runat="server"></asp:Literal>
            <h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">GST Validation is Pending</a></h5>
            <p class="mg-b-5">It is strongly recomend to complete the GST validation in order to activate your online store. If you want to try another GST, please click on the change GST button.<br /><br /> You can also skip the validation step and do it later but the store created will be inactive till you complete the GST validation which can do in the GST page after creating your store.</p>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->



</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupGSTSuccessOld" runat="server" Visible="false">

          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
            <h2><asp:Literal ID="ltrGSTVerifiedSuccessfully" runat="server">Congratulations</asp:Literal>
                <asp:Literal ID="ltrGSTVerificationSkiped" Visible="false" runat="server">You have selected to SKIP the GST verification</asp:Literal>
            </h2>
            <asp:PlaceHolder ID="plcGSTVerificationSkiped" runat="server" Visible="false">
              <p class="login-box-msg p-0 mb-3" style="color: orange;">Your store will be inactive until you complete the GST verification.</p>
            </asp:PlaceHolder>
            <p class="login-box-msg p-0 mb-3">Please check and approve the details of your organisation</p>
          </div><!--login_head-->

    
    <div class="gst_verification_succes">
            <div class="text-center gstinfosct">
              <p>Details of your GST <strong><asp:Literal ID="ltrGSTGSTIN" runat="server"></asp:Literal></strong> as follows</p>
              <p>Organisation: <strong></strong></p>
              <p>Incorporation Type: <strong><asp:Literal ID="ltrGstCorpType" runat="server"></asp:Literal></strong></p>
              <p>Registered Address: <strong></strong></p>
              <p>District: <strong><asp:Literal ID="ltrGstDistrict" runat="server"></asp:Literal>, State: <asp:Literal ID="ltrGstState" runat="server"></asp:Literal>, PIN: <asp:Literal ID="ltrGstPin" runat="server"></asp:Literal></strong></p>
            </div>            
   <p class="login-box-msg p-0 mt-3 mb-3">Complete your Registration providing</p>

            <div class="">
                <div class="form-row">
                  <div class="input-group col-12 col-sm-6 mb-2">
                    
                  </div>
                  <div class="input-group col-12 col-sm-6 mb-2">
                    
                  </div>

                 <div class="input-group col-12 col-sm-6 mb-3">
                    <input type="text" class="form-control" name="Store Contact Number" autocomplete="off" placeholder="Store Contact Number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" required>
                  </div>
                 
                  <div class="input-group col-12 col-sm-6 mb-3">
                    <input type="number" class="form-control" name="Year of Establishment" autocomplete="off" placeholder="Year of Establishment" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="4" required>                    
                  </div>

                </div>
                <div class="col-12 col-sm-12 p-0">
                  <asp:Button ID="btnCompleteGSTSignup" OnClick="btnCompleteGSTSignup_Click" CssClass="btn btn-success btn-block" runat="server" Text="Register" />
                </div>
            </div>
          </div>

</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupComplete" runat="server">

          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
          </div><!--login_head-->

                    
          <div class="registration_successful">
            <h2>THANK YOU</h2>
            <p class="mb-0">You have successfully registered as a Grozeo Partner.</p>
            <p>Welcome to the new world  where you meet the future of retail !</p>
            <div class="massage_Rsuccesful">Please check yor email to get the Store Activation link and Code <asp:Literal Visible="false" ID="ltrCompletionEmail" runat="server"></asp:Literal></div>

            <p class="login-box-msg p-0 mt-3">Want to proceed with instant activation?</p>
          <p class="login-box-msg p-0 mt-1 mb-2">Enter the Activation Code received in Email now.</p>

              <div class="d-flex align-items-center justify-content-center bg-gray-100 ht-md-80 bd pd-x-20">
            <div class="d-md-flex pd-y-20 pd-md-y-0">
                <asp:TextBox ID="txtVerifyEmailConfirmCode" required runat="server" autocomplete="off" TextMode="Number" CssClass="form-control" placeholder="Verification Key"></asp:TextBox>
            </div>

<div class="d-md-flex pd-y-20 pd-md-y-0" style="padding-left: 10px;">
    <asp:Button ID="btnVerifyEmailConfirm" runat="server" OnClick="btnVerifyEmailConfirm_Click" CssClass="btn btn-primary pd-y-13 pd-x-20 bd-0 mg-md-l-10 mg-t-10 mg-md-t-0" Text="Verify" />
            </div>            
          </div>


          </div>

</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupNoGSTOld" runat="server" Visible="false">

          <div class="login_head">
            <div class="login-logo">
              <a href="/">
                <img src="/content/images/login/partner_logo.png"/>
              </a>
            </div>
            <h2>GSTN was Mandatory to do business online!!</h2>
          </div><!--login_head-->

                    
          <div class="gstn_mandatory">
            <p>GSTN was mandatory for all merchants who want to do online business till 10 July 2022.</p>
            <p>How ever Government of India decided to support the small scale merchants to do e-commerce business without GSTN. This decision has taken in the meeting of GST Council but this law is expected to come in effect only from January 2023</p>
            <div class="gstn_mandatory_form">
                <div class="form-row">
                  <label>Please provide your email id here to be in waiting list to start your online business</label>
    
                  <div class="input-group mb-3 col-12">
                    <input type="email" class="form-control" placeholder="Email">
                  </div>              
                </div>
                <div class="col-12 col-sm-12 p-0">
                  <button type="submit" class="btn btn-primary btn-block btn-drk-green">Submit</button>
                </div>
            </div>
          </div>

</asp:PlaceHolder>





          


          </form>
        </div>
        <!-- /.login-card-body -->
      </div>

    </div>
    <!-- /.login-box -->

</div>

<script type="text/javascript">

    var autofocusobj = '<asp:Literal ID="ltrAutoFocusObj" runat="server"/>';
    $(document).ready(function () {
        //if (autofocusobj != '')
        //    $('#' + autofocusobj).focus();

        $(function () {
            var code = "<%= ConfigurationManager.AppSettings.Get("PhoneCountryCode") %>";
            $('#<%= txtSignupMobileNumber.ClientID%>').val(code);
            $('#<%= txtSignupMobileNumber.ClientID%>').intlTelInput({
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

    function OTPInput() {
        const inputs = document.querySelectorAll('#otp > div > *[id]');
        for (let i = 0; i < inputs.length; i++) { inputs[i].addEventListener('keydown', function (event) { if (event.key === "Backspace") { inputs[i].value = ''; if (i !== 0) inputs[i - 1].focus(); } else { if (i === inputs.length - 1 && inputs[i].value !== '') { return true; } else if (event.keyCode > 47 && event.keyCode < 58) { inputs[i].value = event.key; if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } else if (event.keyCode > 64 && event.keyCode < 91) { inputs[i].value = String.fromCharCode(event.keyCode); if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } } }); }
    } //OTPInput();

</script>

</asp:PlaceHolder>  

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
    <uc1:PopupAlert runat="server" id="PopupAlert1" />
</body>
</html>

