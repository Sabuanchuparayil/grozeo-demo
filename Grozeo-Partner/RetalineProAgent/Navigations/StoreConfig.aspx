<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="StoreConfig.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.StoreConfig" %>

<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settings</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Settings</h6>
        <p class="mb-0">Configuration and customization options</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                { %>
            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/BusinessSettings" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-business-time mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Business Settings</h5>
                <p class="card-text mg-b-8 tx-11">Customize business ops: accounts, GST, bank accounts, retail categories.</p>
              </div>
            </a>
          </div><!--col-lg-->
          <% } %>

        <% if (!Page.User.IsInRole("BranchManager"))
                { %>
          <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Branches" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-shop mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Manage Stores</h5>
                <p class="card-text mg-b-8 tx-11">Effortlessly create and manage branches for smooth operations.</p>
              </div>
            </a>
          </div><!--col-lg-->

          
          <% } %>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-shopping-basket mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Products</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Select your Products from our Brand Gallery or create private products exclusively for your store. You can manage only 100 private products in your free plan.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Navigations/Products" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa ion-person-stalker mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Order Picker</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Order Pickers are the sales staff working inside your shop who help to pick and pack the orders when your receive an order. You need to provide a unique number to create the Order Picker.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/OrderPicker" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/Delivery" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-truck-bolt mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery</h5>
                <p class="card-text mg-b-8 tx-11">Seamlessly manage delivery rules and slots for order fulfillment.</p>
              </div>
            </a>
          </div><!--col-lg-->
          
        <% if (!Page.User.IsInRole("BranchManager") && !Page.User.IsInRole("StoreManager"))
                { %>

          <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/Appearance" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-laptop mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Appearance</h5>
                <p class="card-text mg-b-8 tx-11">Personalize website identity with custom logos, banners, and themes.</p>
              </div>
            </a>
          </div><!--col-lg-->
          <% } %>
        
        <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/crm" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-user-headset mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Customer Relation</h5>
                <p class="card-text mg-b-8 tx-11">Boost customer relationships with effective lead management and campaigns.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                { %>
          <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/Users" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-users mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Users</h5>
                <p class="card-text mg-b-8 tx-11">Enable efficient collaboration through user account management.</p>
              </div>
            </a>
          </div><!--col-lg-->


            <% } %>


        </div>

</asp:Content>