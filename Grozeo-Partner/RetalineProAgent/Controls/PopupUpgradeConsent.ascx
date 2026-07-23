<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="PopupUpgradeConsent.ascx.cs" Inherits="RetalineProAgent.Controls.PopupUpgradeConsent" %>

      <div id="modalupgradeconsent" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          
          <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="modal-title tx-dark mb-3"><asp:Literal ID="ltrTitle" runat="server" Text=""></asp:Literal></h5>
            <h6 class=" lh-3 mb-2"><a class="tx-inverse hover-primary"><asp:Literal ID="ltrBodyContent1" runat="server" Text=""></asp:Literal></a></h6>
            <p class="mg-b-5"><asp:Literal ID="ltrBodyContent2" runat="server" Text=""></asp:Literal></p>
            <p class="mb-3"><asp:Literal ID="ltrBodyContent3" runat="server" Text=""></asp:Literal></p>

            <%--<div class="section-wrapper upgrade_otp p-3 mb-3" style="display:none;">
              <label class="section-title text-capitalize">Enter the OTP received in mobile</label>
              <p class="mg-b-20 mg-sm-b-20">OTP has been send to your registred mobile number.</p>

              <div class="form-layout form-layout-4">
                <div class="row">
                  <div class="col-sm-6 mg-t-10 mg-sm-t-0" style="overflow:hidden;">
                      <div >
                          <asp:TextBox ID="txtConsentOTP" CssClass="partitioned" runat="server" autocomplete="off" placeholder="" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                      </div>
                  </div>
                  <div class="col-sm-3 mg-t-10 mg-sm-t-0">
                      <asp:Button runat="server" ID="btnVerifyConsentOTP" Text="Verify" CssClass="btn btn-primary btn-block btn-drk-green" OnClick="btnVerifyConsentOTP_Click" />
                  </div>

                </div><!-- row -->

              </div><!-- form-layout -->
                <asp:Label ID="lblResult" runat="server" ForeColor="Red"></asp:Label>
            </div>--%><!-- section-wrapper -->

              <div class="modal-btn d-inline-block">
                  <a href="/tenant/subscription" class="btn btn-primary">Upgrade</a>
                  <%--<button type="button" class="btn btn-primary" upgradetype="2" id="upgradebtn_submit">Continue</button>--%>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>

          </div><!-- modal-body -->
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
<script type="text/javascript">
    function failureotp(msg) {
        <%--$('#<%= lblResult.ClientID%>').html(msg);--%>
        $('#modalupgradeconsent').find('div.modal-footer').hide();
        $('#modalupgradeconsent').find('.upgrade_otp').show();
        $('#txtUpgradeTOP').focus();
        $('#modalupgradeconsent').modal('show');
    }
</script>