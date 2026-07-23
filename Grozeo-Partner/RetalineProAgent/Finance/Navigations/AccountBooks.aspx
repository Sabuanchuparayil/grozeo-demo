<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Finance" CodeBehind="AccountBooks.aspx.cs" Inherits="RetalineProAgent.Finance.Navigations.AccountBooks" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Account Book</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Account Book</h6>
    <p class="mb-0">Account Book</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3">
              <a href="/Finance/Daybook" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-files-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Transaction Register</h5>
                  <p class="card-text mg-b-8 tx-12">Get a glance of all accounting activities as an eagle eye point to manage those.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3">
              <a href="/Finance/Ledger" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa icon ion-filing mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Ledger</h5>
                  <p class="card-text mg-b-8 tx-12">Keep a track on detailed ledger postings for an in-depth management of accounts.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3">
              <a href="/Finance/CostAllocationReports" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Cost Allocation Entry</h5>
                  <p class="card-text mg-b-8 tx-12">View and manage the allocations distributed to various cost centers to manage .</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3">
              <a href="/Finance/SettlementReports" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Settlement</h5>
                  <p class="card-text mg-b-8 tx-12">Sales stakeholders settlements cycles are elaborated here on a sequential orders.</p>
                </div>
              </a>
            </div>
             <div class="col-lg-4 mb-3">
              <a href="/Finance/ManageBulkUpload" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Bulk Transfer</h5>
                  <p class="card-text mg-b-8 tx-12">Manage All File Created For bulk Bank Transfer.</p>
                </div>
              </a>
            </div>


            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i class="fa fa-files-o mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 "></h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Manage the registered business details that you have created while joining Grozeo. If you want to change the details, access this page. You can also manage the admin staff here</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/Daybook" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i aria-hidden="true" class="fa icon ion-filing mg-y-3 tx-34 tx-primary"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Ledger</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The information contained in the ledger is also used to prepare tax returns and to provide information to investors, creditors, and other stakeholders.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/Ledger" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
              <i class="fa fa-file-text-o mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Cost Alloction Entry</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Cost Alloction Entry.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/CostAllocationReports" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div>--%>
            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Finance/Navigations/MiscellaneousReport" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa fa-bars mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Miscellaneous</h5>
                        <p class="card-text mg-b-8 tx-12">View and download the settlement data here.</p>
                    </div>
                </a>
            </div>
        </div>

</asp:Content>