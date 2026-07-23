<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Settlement.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Title="Settlement" Inherits="RetalineProAgent.Navigations.Settlement" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Settlement</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

            <div class="row row-sm menucard">
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/SettlementReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-line mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Due Order Details</h5>
                <p class="card-text mg-b-8 tx-11">Fetch a detailed list of all orders where the payment settlement is still pending, including its payout calculations</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/PayOutReports" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-gantt mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Payout Details</h5>
                <p class="card-text mg-b-8 tx-11">View all payouts with date, amount, payment reference, linked orders, and details of the settlements.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/PaymentConfig" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-bar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Withdrawal Account</h5>
                <p class="card-text mg-b-8 tx-11">View and manage the bank or payment accounts linked for receiving payouts, including store mapping.</p>
              </div>
            </a>
          </div><!--col-lg-->                 
    </div><!--row-->

</asp:Content>