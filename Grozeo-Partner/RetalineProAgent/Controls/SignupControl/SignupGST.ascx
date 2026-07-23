<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="SignupGST.ascx.cs" Inherits="RetalineProAgent.Controls.SignupControl.SignupGST" %>

<asp:PlaceHolder runat="server" ID="plcSignupGST">

<div class="login_head">
              <h2>Join Grozeo - <%= (CurVATType == 2 ? "Verify "+RetalineProAgent.Service.Store.VATService.VATLabel+" with OTP" : "Verify "+ RetalineProAgent.Service.Store.VATService.VATLabel) %></h2>
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
    <br /><br />
    <asp:LinkButton runat="server" Text="I already have a Grozeo account" OnClick="BackToLogin_Click"></asp:LinkButton>
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
                </div>
              </div>

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
