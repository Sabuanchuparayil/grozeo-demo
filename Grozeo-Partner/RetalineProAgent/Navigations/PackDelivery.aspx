<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="PackDelivery.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.PackDelivery" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/PackingDelivery">Packing & Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Delivery</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm">
            <div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-motorcycle mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">This module focuses on managing the delivery process of ordered items against specific packing IDs. Delivery management is a critical aspect of the order fulfillment process, as it involves ensuring that the correct items are delivered to the right customers in a timely and efficient manner.This may include manual delivery, where the items are delivered by a staff member, or assigning an delivery boy or courier to handle the delivery.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/MerchantDelivery" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
            <div class="col-lg">
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
          </div><!--col-lg-->
        </div><!--row-->
</asp:Content>