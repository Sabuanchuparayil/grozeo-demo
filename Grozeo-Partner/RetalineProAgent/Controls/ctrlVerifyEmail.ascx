<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlVerifyEmail.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlVerifyEmail" %>

<div id="modalverifyemail" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">VERIFY EMAIL</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-25">
            <h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Verify your E-mail</a></h5>
            <p class="mg-b-5">We have sent an email confirmation code to <%= this.CurrentUser.Email %>. Please verify your mail by entering the confirmation code hereunder.</p>
              <div class="form-group mb-0">
                  <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtVerifyCode" runat="server" autocomplete="off" CssClass="form-control pd-y-12" ValidationGroup="VerifyEmailCode"></asp:TextBox>
                  <asp:RequiredFieldValidator ID="reqVerifyCode" runat="server" ControlToValidate="txtVerifyCode" ErrorMessage="Please enter verification code" ForeColor="Red" ValidationGroup="VerifyEmailCode"></asp:RequiredFieldValidator>
                  <%--<asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtVerifyCode" ErrorMessage="Please enter the verification code" ForeColor="Red" ValidationGroup="VerifyEmailCode"></asp:RequiredFieldValidator>--%>
              </div>
              
              <asp:Label ID="lblResult" runat="server"></asp:Label>
          </div>
          <div class="modal-footer">
              <a href="javascript:void(0)" onclick="$('#modalverifyemail').modal('hide'); $('#modalchangecuremail').modal('show');" class="btn btn-outline-primary">Change email</a>
              <asp:LinkButton ID="lbtnResendCode" runat="server" Text="Re-Send" OnClick="lbtnResendCode_Click" CausesValidation="false" class="btn btn-outline-primary" ></asp:LinkButton>
              <asp:LinkButton runat="server" Text="Verify" CssClass="btn btn-primary" ValidationGroup="VerifyEmailCode" OnClick="btnVerifyEmail_Click"></asp:LinkButton>
              <%--<asp:Button ID="btnVerifyEmail" runat="server" Text="Verify" CssClass="btn btn-primary" ValidationGroup="VerifyEmailCode" OnClick="btnVerifyEmail_Click" />--%>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
