<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="PackingDelivery.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.PackingDelivery" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Fulfilment</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Fulfilment</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm">
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-gift mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Packing</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/PendingOrders" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-location-arrow mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module manages the delivery process of ordered items against specific packing IDs. Delivery management is critical for correct, timely, and efficient delivery to customers. It includes manual delivery by staff or assigning a delivery boy or courier.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/MerchantDelivery" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
            <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-money mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Receive Cash</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The module manages COD payments by delivery personnel or partners. COD is a popular payment method where customers pay in cash upon delivery. Effective COD management reduces fraud risk, improves cash flow, and enhances the customer experience.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/ReceiveCash" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->
        </div><!--row-->
</asp:Content>