<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Appearance.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Appearance" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Appearance</li>--%>
    <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> Appearance</h6>
        <p class="mb-0">Enhance Your Branding</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Appearance/Logo" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-file-image mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Logos</h5>
                <p class="card-text mg-b-8 tx-11">Enhance brand design, upload logos and leave lasting impressions.</p>
              </div>
            </a>
          </div><!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Appearance/Themes" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-rectangle-history mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Themes</h5>
                        <p class="card-text mg-b-8 tx-11">Select and customize aesthetic themes to elevate your site's design.</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Appearance/Banner" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-calendar-image mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Banners</h5>
                <p class="card-text mg-b-8 tx-11">Create eye-catching banners for promotions and important messages.</p>
              </div>
            </a>
          </div><!--col-lg-->
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/ContentsPages" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-file-lines mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Content Pages</h5>
                <p class="card-text mg-b-8 tx-11">Craft captivating, informative pages to engage your audience.</p>
              </div>
            </a>
          </div><!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/DomainControl" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-link mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Domain Control</h5>
                <p class="card-text mg-b-8 tx-11">Effortlessly manage and control your website's domain settings.</p>
                  <%--<div class="card_view  p-2 tx-center">
                      <% if (this.CurrentUser.PackageId < 2)
                         { %>
                            <a href="/Tenant/DomainControl" data-toggle="modal" data-target="#modalupgrade" class="card-link btn btn-secondary" title="Configure custom domain">Configure</a>            
                        <% }
                            else
                            { %>
                                <a href="/Tenant/DomainControl" class="card-link btn btn-secondary">Configure</a>
                             <% } %>
                    </div>--%>
              </div>
              
            </a>
          </div><!--col-lg-->
            
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/MobileApp" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-mobile mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Android App</h5>
                <p class="card-text mg-b-8 tx-11">Creating your branded mobile application is just a click away.</p>
              </div>
            </a>
          </div>
            <%--<div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Appearance/Graphics" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-pen-nib mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Graphics</h5>
                <p class="card-text mg-b-8 tx-11">Boost your online presence with customized creatives in just a few clicks.</p>
              </div>
            </a>
          </div>--%>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
                <a href="/Tenant/PrivateCategory?type=featured" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-star mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Featured List</h5>
                        <p class="card-text mg-b-8 tx-11">Highlighted as a standout product for increased visibility.</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/PrivateCategory?type=preferred" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-heart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Preferred List</h5>
                <p class="card-text mg-b-8 tx-11">Marked as a preferred choice for prioritized recognition.</p>
              </div>
            </a>
          </div>
        </div><!--row-->
</asp:Content>