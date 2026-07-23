<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SettingsMenu.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.SettingsMenu" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Appearance</li>--%>
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> Settings</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/BusinessSettings" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-file-image mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Business Settings</h5>
                <p class="card-text mg-b-8 tx-11">Configure core settings such as statutory, address, site settings etc.</p>
              </div>
            </a>
          </div><!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Branches?type=ManageBranch" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-store mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Manage Stores</h5>
                        <p class="card-text mg-b-8 tx-11">Control store profiles, including store details, operating hours and overall store settings.</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/OrderPicker" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-person-dolly mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Order Pickers</h5>
                        <p class="card-text mg-b-8 tx-11">Create order pickers, track their activities, and monitor order collection status.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/DeliveryStaffs" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-truck mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery Agents</h5>
                        <p class="card-text mg-b-8 tx-11">Create delivery agents, track their availability and performance and monitor delivery status.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Store/Users" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-people-roof mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Store Admin Users</h5>
                <p class="card-text mg-b-8 tx-11">Add and manage store-level admin users, define roles and permissions, and control access.</p>
              </div>
            </a>
          </div><!--col-lg-->
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Navigations/Delivery" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-boxes-stacked mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Advanced</h5>
                <p class="card-text mg-b-8 tx-11">Configure system options and custom controls to fine-tune platform behaviour and optimise operations.</p>
              </div>
            </a>
          </div><!--col-lg-->
        </div><!--row-->
</asp:Content>