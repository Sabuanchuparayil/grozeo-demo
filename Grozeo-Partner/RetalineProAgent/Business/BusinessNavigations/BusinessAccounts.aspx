<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="BusinessAccounts.aspx.cs" MasterPageFile="~/Business/BusinessMaster.master" Inherits="RetalineProAgent.BusinessNavigations.BusinessAccounts" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounts</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Accounts</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-6 mb-3 mb-lg-4">
                <a href="/Business/BusinessNavigations/RevenueStream" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Revenue Streams</h5>
                        <p class="card-text mg-b-8 tx-11">Optimize your revenue stream with our comprehensive admin panel. Gain real-time insights, track financial performance, and make data-driven decisions for sustainable revenue growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-6 mb-3 mb-lg-4">
                <a href="/Business/BusinessNavigations/BusinessReports" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-line-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Reports</h5>
                        <p class="card-text mg-b-8 tx-11">Unleash the power of data with our robust admin panel for reports. Generate insightful reports, analyze key metrics, and make informed business decisions with ease and precision.</p>
                    </div>
                </a>
            </div>

            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-bar-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Revenue Streams</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Optimize your revenue stream with our comprehensive admin panel. Gain real-time insights, track financial performance, and make data-driven decisions for sustainable revenue growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/BusinessNavigations/RevenueStream" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-line-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 "></h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Unleash the power of data with our robust admin panel for reports. Generate insightful reports, analyze key metrics, and make informed business decisions with ease and precision.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/BusinessNavigations/BusinessReports" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div>

</asp:Content>