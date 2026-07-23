<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderUser.ascx.cs" Inherits="RetalineProAgent.Controls.HeaderUser" %>

            <a href="#" class="logged-user" data-toggle="dropdown">
              <img src="/Content/images/userImage.png" alt="">
              <span><asp:Literal ID="ltrUserShort" runat="server"></asp:Literal></span>
              <i class="fa fa-angle-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <nav class="nav">
                <a href="/tenant/profile" class="nav-link"><i class="fa-thin fa-user-vneck mr-2"></i> View Profile</a>
                <%--<a href="/profile" class="nav-link"><i class="icon ion-compose"></i> Edit Profile</a>--%>
                  <asp:PlaceHolder ID="plcManager" runat="server" Visible="false">
                    <a href="/manage/UserToStore" class="nav-link"><i class="icon ion-ios-bolt"></i>Manage Users</a>
                  </asp:PlaceHolder>
                  <% if (ConfigurationManager.AppSettings.Get("IsDemo") == "1")
                      { %>
                  <asp:LinkButton ID="lbtnDeleteMyAccount" CssClass="nav-link" runat="server" OnClick="lbtnDeleteMyAccount_Click" OnClientClick="return confirm('Are you sure you want to delete your account?')"><i class="icon ion-android-delete"></i> Delete My Account</asp:LinkButton>
                    <a href="/appview" class="nav-link"><i class="icon ion-iphone"></i>App Demo</a>
                  <% } %>
                <a href="/Navigations/StoreSettings" class="nav-link"><i class="fa-thin fa-gear-complex mr-2"></i> Account Settings</a>
                  <asp:LoginStatus CssClass="nav-link" LogoutText="<i class='fa-thin fa-right-from-bracket mr-2'></i> Sign Out" runat="server"  />
                <%--<a href="page-signin.html" class=""><i class="icon ion-forward"></i> Sign Out</a>--%>
              </nav>
            </div><!-- dropdown-menu -->
        

<asp:PlaceHolder runat="server" Visible="false">
            <asp:Literal ID="ltrUserLong" runat="server"></asp:Literal>
            <asp:Literal ID="ltrSmallText" runat="server"></asp:Literal>
            <asp:Literal ID="ltrEmail" runat="server"></asp:Literal>
<asp:Literal ID="ltrRole" runat="server"></asp:Literal>


</asp:PlaceHolder>

