<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Packing.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Packing" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/PackingDelivery">Packing & Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Packing</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Packing</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm">
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-cube mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Packing</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/PendingOrders" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-cubes mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Scheduled Packing</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module focuses on organizing and processing orders that are allocated to specific time slots, ensuring that they are fulfilled on time and in the correct sequence. This may involve monitoring and updating the order status, communicating with customers to confirm the booking, coordinating with delivery personnel or drivers to optimize routes, and resolving any issues or delays that may arise.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/ScheduledPacking" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-circle-o-notch mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Incomplete Orders</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">This module is designed to handle situations where there are insufficient packing items available to fulfill certain orders. When this occurs, the module provides a mechanism for seeking customer approval to proceed with completing the packing process.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/IncompleteOrders" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
        </div><!--row-->
</asp:Content>