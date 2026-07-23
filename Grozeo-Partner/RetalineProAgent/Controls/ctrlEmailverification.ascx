<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlEmailverification.ascx.cs" Inherits="RetalineProAgent.Controls.Emailverification" %>
 <div class="verify_strip_wrap">
          <div class="head_verifysec m-100 text-center p-2">
              <% if (!this.CurrentUser.HasVerifiedEmail)
                  { %>
            <p class="m-0">Verify your email ID <%= this.CurrentUser.Email %> <a class="ml-2" id="verifyemail" href="javascript:void(0)" onclick="Emailverificationpopup()">Verify</a><asp:LinkButton CssClass="ml-2" ID="btnchangemail" OnClick="btnchangemail_Click" runat="server"  OnClientClick="Emailverificationpopup()">Change Email</asp:LinkButton></p>
                <% }
                    else if(String.IsNullOrEmpty(this.CurrentUser.Phone))
                    { %>
                     <p class="m-0">Verify your Mobile Number <%= this.CurrentUser.Phone %> <a class="ml-2" id="verifymobile" href="javascript:void(0)" onclick="Emailverificationpopup()">Verify</a><asp:LinkButton CssClass="ml-2" runat="server" OnClientClick="openpopover()">Change Mobile</asp:LinkButton></p>
              <% } else { 
                      %>
                     <p class="m-0">Set Password For <%= this.CurrentUser.Email %><a class="ml-2" id="verifypassword" href="javascript:void(0)" onclick="Emailverificationpopup()">Password</a></p>
              <%
                  } %>
          </div>
         <div class="verify_dropdown_popover Emailverificationpopup d-flex flex-wrap justify-content-center align-items-center ">
      <% if (!this.CurrentUser.HasVerifiedEmail)
         { %>  
             <asp:PlaceHolder runat="server" ID="plcemailverify" Visible="true">
              <div class="emailverification">
                <div class="form-group m-0" >
                  <div class="form-row m-0 justify-content-center" id="emailverify" runat="server">
                    <p class="mb-2 text-center">Enter OTP received in <%= this.CurrentUser.Email %></p>
                    <div class="input-group justify-content-center">
                      <div class="divOuter">
                        <div class="divInner">
                            <asp:TextBox runat="server" ID="txtLoginOTP" CssClass="otp_input_field"  maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"></asp:TextBox>
<%--                          <input type="text" name="txtLoginOTP" id="txtLoginOTP" class="otp_input_field" required="" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off">--%>
                           <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLoginOTP" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="OTP is required" ValidationGroup="OTPverify" ForeColor="Red"></asp:RequiredFieldValidator>

                      </div>
                      </div>
                    </div>
                      <div class="d-flex justify-content-center w-100 mt-3">
                          <asp:LinkButton ID="btnresent"  OnClick="btnresent_Click" CssClass="text-white" runat="server">Re-Send</asp:LinkButton>
                      </div>
                  </div><!--form-row-->
                  <div class="form-row m-0 justify-content-center">
                    <div class="formtbtn d-flex justify-content-center mt-3">
                      <input class="btn btn-outline-primary mx-1" value="Not Now" type="button" id="" onclick="Emailverifcationclosepopup()">
                        <asp:LinkButton runat="server" ID="btnverify" OnClick="btnverify_Click"  CssClass="btn btn-primary mx-1" ValidationGroup="OTPverify" >Verify</asp:LinkButton>
<%--                      <input class="btn btn-primary mx-1" value="Verify" type="submit" id="">--%>
                    </div>
                  </div><!--form-row-->                   
                </div><!--form-group-->
              </div><!--emailverification-->  
               </asp:PlaceHolder>
               
             <asp:PlaceHolder runat="server" ID="plcnewemailadd" Visible="false">
                    <div class="newemailid">
                <div class="form-group  m-0" >
                  <div class="form-row m-0 justify-content-center">
                    <p class="mb-2 text-center">New Email ID</p>
                      <div class="input-group justify-content-center">
<%--                        <input type="email" name="newemail" id="newemail" class="form-control" required="" autocomplete="off">--%>
                          <asp:TextBox ID="txtNewEmail" CssClass="form-control" autocomplete="off" runat="server" ></asp:TextBox>
                         <asp:RequiredFieldValidator runat="server" ControlToValidate="txtNewEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="OTP is required" ValidationGroup="Newmail" ForeColor="Red"></asp:RequiredFieldValidator>

                      </div>                    
                  </div><!--form-row-->
                  <div class="form-row m-0 justify-content-center">
                    <div class="formtbtn d-flex justify-content-center mt-3">
                      <input class="btn btn-outline-primary mx-1" value="Not Now" type="button" id="" onclick="Emailverifcationclosepopup()">
<%--                      <input class="btn btn-primary mx-1" value="Send OTP" type="submit" id="">--%>
                        <asp:Button runat="server" ID="btnsentotp" OnClick="btnsentotp_Click"  CssClass="btn btn-primary mx-1" ValidationGroup="Newmail"  Text="Send OTP" />
                    </div>
                  </div><!--form-row-->
                </div><!--form-group-->
              </div><!--newemailid--> 
              </asp:PlaceHolder>
             <asp:PlaceHolder runat="server" ID="plcgetotp" Visible="false">
                 <div class="emailverification">
                <div class="form-group m-0" >
                  <div class="form-row m-0 justify-content-center" id="Div1" runat="server">
                    <p class="mb-2 text-center">Enter OTP received in <%= (String.IsNullOrEmpty(CurVerificationKey.Value) ? this.CurrentUser.Email : CurVerificationKey.Value) %></p>
                    <div class="input-group justify-content-center">
                      <div class="divOuter">
                        <div class="divInner">
                            <asp:TextBox runat="server" ID="txtChangeEmailVerifyCode" CssClass="otp_input_field"  required="" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"></asp:TextBox>
<%--                          <input type="text" name="txtLoginOTP" id="txtLoginOTP" class="otp_input_field" required="" maxlength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off">--%>
                      </div>
                      </div>
                    </div>
                      <div class="d-flex justify-content-center w-100 mt-3">
                          <asp:LinkButton ID="btnresentbutton" OnClick="btnresentbutton_Click"  CssClass="text-white" runat="server">Re-Send</asp:LinkButton>
                      </div>
                  </div><!--form-row-->
                  <div class="form-row m-0 justify-content-center">
                    <div class="formtbtn d-flex justify-content-center mt-3">
                      <input class="btn btn-outline-primary mx-1" value="Not Now" type="button" id="" onclick="Emailverifcationclosepopup()">
                        <asp:LinkButton runat="server" ID="btnVerifyEmail" OnClick="btnVerifyEmail_Click" CssClass="btn btn-primary mx-1">Verify</asp:LinkButton>
<%--                      <input class="btn btn-primary mx-1" value="Verify" type="submit" id="">--%>
                    </div>
                  </div><!--form-row-->                   
                </div><!--form-group-->
              </div><!--emailverification-->  
             </asp:PlaceHolder>

          <% } 
            else if(this.CurrentUser.HasVerifiedEmail || String.IsNullOrEmpty(this.CurrentUser.Password))
             { %>
             <asp:PlaceHolder runat="server" ID="plcpassword">
               <div class="setpassword">
                <div class="form-group m-0" >
                  <div class="form-row mx-0 mb-3 justify-content-center">
                    <p class="mb-2 text-center">Password (min 6 character length)</p>
                      <div class="input-group justify-content-center">
                          <asp:TextBox runat="server" CssClass="form-control" TextMode="Password" ID="txtSetPassword1" autocomplete="off"></asp:TextBox>
                      </div>                    
                  </div><!--form-row-->
                  <div class="form-row mx-0 mb-3 justify-content-center">
                    <p class="mb-2 text-center">Confirm Password</p>
                      <div class="input-group justify-content-center">
                          <asp:TextBox runat="server" ID="txtSetPassword2" TextMode="Password" CssClass="form-control" autocomplete="off"></asp:TextBox>
                      </div>                    
                  </div><!--form-row-->
                  <div class="form-row mx-0 mb-0 justify-content-center">
                    <div class="formtbtn d-flex justify-content-center">
                      <input class="btn btn-outline-primary mx-1" value="Cancel" type="button" id="" onclick="Emailverifcationclosepopup()">
<%--                      <input class="btn btn-primary mx-1" value="Save" type="submit" id="">--%>
                        <asp:Button  runat="server" CssClass="btn btn-primary mx-1" ID="btnsubmitt" Text="Save" OnClick="btnsubmitt_Click"/>
                    </div>
                  </div><!--form-row-->
                </div><!--form-group-->
              </div>
              </asp:PlaceHolder> 
              <% }%>   
          <asp:Label ID="lblResult" runat="server"></asp:Label>
          </div><!--verify_dropdown_popover-->
        </div>

<script>
    function Emailverificationpopup() {
        $(".Emailverificationpopup").addClass("active");
    }

    function Emailverifcationclosepopup() {
        $(".Emailverificationpopup").removeClass("active");
    }
</script>