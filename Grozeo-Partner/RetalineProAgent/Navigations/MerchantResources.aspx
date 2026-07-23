<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="MerchantResources.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.MerchantResources" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Resources</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Store/Users" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-handshake-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Store Staff</h5>
                        <p class="card-text mg-b-8 tx-11"></p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/OrderPicker" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-handshake-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Order Pickers</h5>
                        <p class="card-text mg-b-8 tx-11"></p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/DeliveryStaffs" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-id-badge mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Agents</h5>
                        <p class="card-text mg-b-8 tx-11"></p>
                    </div>
                </a>
            </div>
        </div>

</asp:Content>