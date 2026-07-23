<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" AutoEventWireup="true" CodeBehind="salesReports.aspx.cs" Inherits="RetalineProAgent.Navigations.salesReports" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transactions</li>--%>
<a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Transactions</h6>
        <p class="mb-0">Manage Financial Transactions</p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
     <div class="row row-sm menucard">
          <div class="col-sm-6 col-lg-6 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/SalesReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-display-chart-up-circle-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Daily Sales Report</h5>
                <p class="card-text mg-b-8 tx-11">Get a comprehensive overview of your daily sales performance</p>
              </div>
            </a>
          </div>
         <div class="col-sm-6 col-lg-6 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/DetailedSalesReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-mixed-up-circle-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Detailed Sales Report</h5>
                <p class="card-text mg-b-8 tx-11">Dive into detailed data to understand sales trends and patterns</p>
              </div>
            </a>
          </div>
     </div>
</asp:Content>