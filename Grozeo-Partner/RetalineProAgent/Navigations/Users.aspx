<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Users.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Users" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Users</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Users</h6>
        <p class="mb-0">Effortlessly Create & Manage Accounts</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/OrderPicker" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-user-tag mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Order Pickers</h5>
                <p class="card-text mg-b-8 tx-11">Effortless order picker account management for swift fulfillment.</p>
              </div>
            </a>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/DeliveryStaffs" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-user-helmet-safety mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery Staff</h5>
                <p class="card-text mg-b-8 tx-11">Effortlessly manage delivery staff accounts for prompt shipments.</p>
              </div>
            </a>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Store/Users" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-user-tie mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Admin Users</h5>
                <p class="card-text mg-b-8 tx-11">Seamlessly manage admin user accounts for efficient system operations.</p>
              </div>
            </a>
          </div><!--col-lg-->
           <%-- <div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-truck mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Scheduled Delivery</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module manages orders received in slot delivery mode, where customers get a delivery time slot. It optimizes delivery routes to fulfill orders efficiently within their respective delivery slots. It assigns delivery tasks to personnel, optimizes the route based on location, and tracks delivery status in real-time.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/ScheduledDelivery" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div><!--row-->
</asp:Content>