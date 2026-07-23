<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlSetPassword.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlSetPassword" %>

<div id="modalsetpsw" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Set Password</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-25">
              <p class="mg-b-5">Set password to generate your login credentials. You will be able to login with your email and the new password once you set your password to access the account.</p>
              <div class="form-group">
                    <asp:TextBox ID="txtSetPassword1" runat="server" TextMode="Password" CssClass="form-control pd-y-12" placeholder="Enter Password" ValidationGroup="SetPassword"></asp:TextBox>
                  <asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtSetPassword1" ErrorMessage="Please enter password" ForeColor="Red" ValidationGroup="SetPassword"></asp:RequiredFieldValidator>
              </div>
              <div class="form-group">
                    <asp:TextBox ID="txtSetPassword2" runat="server" TextMode="Password" CssClass="form-control pd-y-12" placeholder="Re-enter password" ValidationGroup="SetPassword" ></asp:TextBox>                    
                  <asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtSetPassword2" ErrorMessage="Please input your password again" ForeColor="Red" ValidationGroup="SetPassword"></asp:RequiredFieldValidator>
                  <asp:CompareValidator ID="CompareValidator1" ControlToValidate="txtSetPassword2" ControlToCompare="txtSetPassword1" ValidationGroup="SetPassword" runat="server" ForeColor="Red" ErrorMessage="Password 1 and Password 2 should match"></asp:CompareValidator>
              </div>
              
              <asp:Label ID="lblResult" runat="server"></asp:Label>
          </div>
          <div class="modal-footer">
              <asp:LinkButton ID="lbtnSetPassword" runat="server" Text="Submit" CssClass="btn btn-primary" ValidationGroup="SetPassword" OnClick="btnSetPassword_Click" ></asp:LinkButton>
              <%--<asp:Button ID="btnSetPassword" runat="server" Text="Submit" CssClass="btn btn-primary" ValidationGroup="SetPassword" OnClick="btnSetPassword_Click" />--%>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
 
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
