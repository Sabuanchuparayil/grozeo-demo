<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlSignupResetPassword.ascx.cs" Inherits="RetalineProAgent.Controls.SignupControl.ctrlSignupResetPassword" %>

    <asp:PlaceHolder ID="plcResetPswView" runat="server" Visible="false">
        <div class="loginform_wrap">

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

       </div>
    </asp:PlaceHolder>

    <asp:PlaceHolder ID="plcResetPswInvalidkey" runat="server">
<div class="loginform_wrap">

<div class="login_head">
            <h2 class="mb-3">Request Expired</h2>
</div>
          <div class="loginform_wrap">
                    <p>The request was expired. Please login with OTP to activate your account or use the forgot password in the login page to create access.</p>
                  <div class="btn_sec">
                    <a class="btn btn-primary" href="/">Login</a>
                  </div>
              
          </div><!--loginform_wrap-->
        </div>
    </asp:PlaceHolder>

    <asp:PlaceHolder ID="plcResetPswSuccess" runat="server">
<div class="loginform_wrap">

          <div class="login_head">
            <h2 class="mb-3">Success!</h2>
          </div><!--login_head-->

          <div class="loginform_wrap">
                    <p>Your password has been created successfully. <asp:Literal ID="ltrlSetPswContinue" runat="server">Please login with your credentials</asp:Literal></p>
        <div class="btn_sec">
            <asp:HyperLink ID="hlSetPswNavigate" CssClass="btn btn-primary" Text="Login" NavigateUrl="/login" runat="server"></asp:HyperLink>
                  </div>

          </div><!--loginform_wrap-->

</div>
    </asp:PlaceHolder>

