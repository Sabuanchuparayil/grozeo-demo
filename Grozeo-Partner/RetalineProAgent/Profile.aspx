<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.Master" Title="Profile" CodeBehind="Profile.aspx.cs" Inherits="RetalineProAgent.Profile" %>
<%@ Register Src="~/Controls/ctrlLanguages.ascx" TagPrefix="uc1" TagName="ctrlLanguages" %>
<%@ Register Src="~/Controls/ctrlSetPassword.ascx" TagPrefix="uc1" TagName="ctrlSetPassword" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
        <div class="row row-sm">
          <div class="col-lg-8">
            <div class="card card-profile">
              <div class="card-body p-3">
                <div class="media">
                  <img src="/Content/images/userImage.png" alt="">
                  <div class="media-body">
                    <h3 class="card-profile-name"><asp:Literal runat="server" ID="ltrFullName"></asp:Literal></h3>
                    <p class="card-profile-position"><asp:Literal ID="ltrRole" runat="server"></asp:Literal> <a href="/"><%= this.CurrentUser.StoreGroupName %></a></p>
                    <%--<p>San Francisco, California</p>--%>

                    <p class="mg-b-0"><asp:Literal ID="ltrAddr" runat="server"></asp:Literal></p>
                    <p ><asp:Literal runat="server" ID="ltrCity"></asp:Literal>&nbsp;
                    <asp:Literal runat="server" ID="ltrState"></asp:Literal>&nbsp;
                    <asp:Literal runat="server" ID="ltrCountry"></asp:Literal><br />
                        Domain: <%= this.CurrentUser.PublicSiteUrl %>
                </p>

                  </div><!-- media-body -->
                </div><!-- media -->
              </div><!-- card-body -->
              <%--<div class="card-footer">
                <a href="//<%= this.CurrentUser.PublicSiteUrl %>" target="_blank" class="card-profile-direct">//<%= this.CurrentUser.PublicSiteUrl %></a>
                <div>
                  <a href="">Edit Profile</a>
                  <a href="">Profile Settings</a>
                    &nbsp;
                </div>
              </div>--%><!-- card-footer -->
            </div><!-- card -->

            <ul class="nav nav-activity-profile mg-t-20">
              <li class="nav-item"><a href="/Navigations/SettingsMenu" class="nav-link"><i class="icon ion-ios-redo tx-purple"></i> Store Settings</a></li>
              <li class="nav-item"><a href="/tenant/Appearance/logo" class="nav-link"><i class="icon ion-image tx-primary"></i> Display Settings</a></li>
              <li class="nav-item"><a href="/tenant/" class="nav-link"><i class="icon ion-document-text tx-success"></i> Dashboard</a></li>
            </ul><!-- nav -->

              <div class="card card-experience mg-t-20">
              <div class="card-body p-3">
                <div class="slim-card-title">Business Information</div>
                 <uc1:ctrlSetPassword runat="server" ID="ctrlSetPassword" />
                <div class="media">
                  <div class="experience-logo p-1 bg-transparent">
                    <%--<i class="icon ion-briefcase"></i>--%>
                      <img style="max-width: 100%;" src="/Content/images/userImage.png" alt="Logo" />
                  </div><!-- experience-logo -->
                  <div class="media-body">
                    <h6 class="position-name"><%= this.CurrentUser.StoreGroupName %></h6>
                    <p class="position-company">Store Status: <%= this.CurrentUser.TenantStage == 1 ? "Active" : "Pending" %>, <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> Status: <%= this.CurrentUser.TenantStatus == 1 ? "Active" : "Pending" %></p>
                    <p class="position-year">Store Created on: <%= this.CurrentUser.CreatedOn %> &nbsp;-&nbsp;</p>
                  </div><!-- media-body -->
                </div><!-- media -->
              </div><!-- card-body -->
              <div class="card-footer">
                <a href="/Navigations/SettingsMenu">Go to Store<span class="d-none d-sm-inline"> Settings</span> <i class="fa fa-angle-down"></i></a>
                <a href="/Navigations/Appearance">Config</a>
              </div><!-- card-footer -->
            </div><!-- card -->

          </div><!-- col-8 -->

          <div class="col-lg-4 mg-t-20 mg-lg-t-0">
            <div class="card">
                <div class="card-body p-3">
                    <div class="slim-card-title">Contact &amp; Personal Info</div>

              <div class="media-list mg-t-25">
                <div class="media">
                  <div><i class="icon ion-link tx-24 lh-0"></i></div>
                  <div class="media-body mg-l-15 mg-t-4">
                    <h6 class="tx-14 tx-gray-700">Websites</h6>
                    <a href="//<%= this.CurrentUser.PublicSiteUrl %>" target="_blank" class="d-block">//<%= this.CurrentUser.PublicSiteUrl %></a>
                  </div><!-- media-body -->
                </div><!-- media -->
                <div class="media mg-t-25">
                  <div><i class="icon ion-ios-telephone-outline tx-24 lh-0"></i></div>
                  <div class="media-body mg-l-15 mg-t-4">
                    <h6 class="tx-14 tx-gray-700">Phone Number</h6>
                    <span class="d-block"><%= this.CurrentUser.Phone %></span>
                  </div><!-- media-body -->
                </div><!-- media -->
                <div class="media mg-t-25">
                  <div><i class="icon ion-ios-email-outline tx-24 lh-0"></i></div>
                  <div class="media-body mg-l-15 mg-t-4">
                    <h6 class="tx-14 tx-gray-700">Email Address</h6>
                    <span class="d-block"><%= this.CurrentUser.Email %></span>
                  </div><!-- media-body -->
                </div><!-- media -->
                <div class="media mg-t-25">
                  <div><i class="ion-android-checkbox-outline tx-18 lh-0"></i></div>
                  <div class="media-body mg-l-15 mg-t-2">
                    <h6 class="tx-14 tx-gray-700">Email Verification Status</h6>
                    <a href="#" class="d-block"><%= this.CurrentUser.HasVerifiedEmail %></a>
                  </div><!-- media-body -->
                </div><!-- media -->
                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                      { %>
                  <div class="media mg-t-25" style="display: none;">
                      <% }
                      else
                      { %>
                      <div class="media mg-t-25">
                          <% } %>
                          <uc1:ctrlLanguages runat="server" ID="ctrlLanguages1" />
                          <div><i class="fa-regular fa-language tx-14 lh-0"></i></div>
                          <div class="media-body mg-l-15 mg-t-2">
                              <h6 class="tx-14 tx-gray-700">Language preference</h6>
                              <a href="javascript:void(0)" onclick="$('#modallanguage').modal('show');" class="d-block">Edit Language Preference</a>
                          </div>
                          <!-- media-body -->               
                          <br /><br /><br /><br /><br /><br /><br /><br />
                      </div>
                      <!-- media -->

                <div class="media mg-t-25">
                  <div><i class="ion-ios-eye tx-22 lh-0"></i></div>
                  <div class="media-body mg-l-15 mg-t-2">
                    <h6 class="tx-14 tx-gray-700">Change Password</h6>
                    <a href="javascript:void(0)" onclick="$('#modalsetpsw').modal('show');" class="d-block">Change</a>
                  </div><!-- media-body -->
                    <br /><br /><br /><br /><br /><br /><br /><br />
                </div><!-- media -->
              </div><!-- media-list -->
                </div>
              
            </div><!-- card -->
          </div><!-- col-4 -->
        </div><!-- row -->

</asp:Content>

