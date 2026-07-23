<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlChangeEmail.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlChangeEmail" %>

<div id="modalchangecuremail" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Change Email</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-25">
              <div id="modalverifyemailcontent" style="display:none;">
            <h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Verify your<%= (String.IsNullOrEmpty(CurVerificationKey.Value) ? "" : " new") %> E-mail</a></h5>
            <p class="mg-b-5">We have sent an email confirmation code to <%= (String.IsNullOrEmpty(CurVerificationKey.Value) ? this.CurrentUser.Email : CurVerificationKey.Value) %>. Please verify your mail by entering the confirmation code hereunder.</p>
              <div class="form-group">
                  <asp:TextBox ID="txtChangeEmailVerifyCode" CssClass="form-control pd-y-12" runat="server"></asp:TextBox>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtChangeEmailVerifyCode" ErrorMessage="Please enter verification code" ForeColor="Red" ValidationGroup="ChangeEmailVerifyCode"></asp:RequiredFieldValidator>
                  <%--<asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtVerifyCode" ErrorMessage="Please enter the verification code" ForeColor="Red" ValidationGroup="VerifyEmailCode"></asp:RequiredFieldValidator>--%>
              </div>

              </div>
              <div id="modalverifychangeemail">
            <h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Change E-mail Id</a></h5>
            <p class="mg-b-5">You can change your email here. On submit will send a verification mail to the email id. You can complete the verification using the code send to the email id on submit. </p>
              <div class="form-group">
                  <asp:TextBox ID="txtNewEmail" runat="server" TextMode="Email" CssClass="form-control pd-y-12" placeholder="Enter new email id"></asp:TextBox>
                  <asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtNewEmail" ErrorMessage="Please enter the email id" ForeColor="Red" ValidationGroup="ChangeEmail"></asp:RequiredFieldValidator>
                  <asp:RegularExpressionValidator runat="server" ControlToValidate="txtNewEmail" ValidationGroup="ChangeEmail" ValidationExpression="\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*"></asp:RegularExpressionValidator>
              </div>

              </div>
              <asp:Label ID="lblResult" runat="server"></asp:Label>
          </div>
          <div class="modal-footer" id="footerverifyemail" style="display:none;">
              <asp:LinkButton ID="lbtnResendCode" runat="server" Text="Re-Send" CausesValidation="false"></asp:LinkButton>
              <asp:LinkButton runat="server" Text="Verify" CssClass="btn btn-primary" ValidationGroup="ChangeEmailVerifyCode" OnClick="btnVerifyEmail_Click"></asp:LinkButton>
              <%--<asp:Button ID="btnVerifyEmail" runat="server" Text="Verify" CssClass="btn btn-primary" ValidationGroup="VerifyEmailCode" OnClick="btnVerifyEmail_Click" />--%>
              <% if (String.IsNullOrEmpty(CurVerificationKey.Value))
                  { %>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <% }
                  else
                  { %>
              <asp:Button ID="btnCancel" runat="server" CssClass="btn btn-secondary" Text="Close" CausesValidation="false" OnClick="btnCancel_Click" />
              <% } %>
          </div>
          <div class="modal-footer" id="footerchangeemail">
              <asp:LinkButton ID="lbtnChangeEmail" runat="server" CssClass="btn btn-primary" Text="Submit" ValidationGroup="ChangeEmail" OnClick="btnChangeEmail_Click"></asp:LinkButton>
              <%--<asp:Button ID="btnChangeEmail" runat="server" CssClass="btn btn-primary" Text="Submit" ValidationGroup="ChangeEmail" OnClick="btnChangeEmail_Click" />--%>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
<script type="text/javascript">
    function changemailview() {
        $('#modalverifychangeemail').hide();
        $('#footerchangeemail').hide();
        $('#modalverifyemailcontent').show();
        $('#footerverifyemail').show();
    }
    $('#modalverifyemail').on('hidden.bs.modal', function (e) {
        $('#modalverifychangeemail').show();
        $('#footerchangeemail').show();
        $('#modalverifyemailcontent').hide();
        $('#footerverifyemail').hide();
        //$(retMasterPrivateMethods.controls.modalupgrade).find("div.modal-footer .disabled").removeClass("disabled");
    });
</script>
