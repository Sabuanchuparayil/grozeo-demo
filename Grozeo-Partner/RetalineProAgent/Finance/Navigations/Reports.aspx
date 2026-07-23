<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Finance" CodeBehind="Reports.aspx.cs" Inherits="RetalineProAgent.Finance.Navigations.Reports" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
       <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Reports</h6>
    <p class="mb-0">Reports</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-0">
              <a href="/Finance/TrialBalance" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Trial Balance</h5>
                  <p class="card-text mg-b-8 tx-12">Read through the Trial Balance of the company here with date-based analysis </p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-0">
              <a href="/Finance/ProfitAndLoss" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-area-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Profit & Loss A/c</h5>
                  <p class="card-text mg-b-8 tx-12">Go through the up-to-date Profit and Loss reporting for a deep understanding</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-0">
              <a href="/Finance/BalanceSheet" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="icon ion-arrow-graph-up-right mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Balance Sheet</h5>
                  <p class="card-text mg-b-8 tx-12">Read the Balance Sheet prepared for the date to understand the accurate perfromance</p>
                </div>
              </a>
            </div><!--col-lg-->

            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-bar-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Trial Balance</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">A trial balance is a statement that lists the balances of all general ledger accounts in a company's accounting system at a specific point in time</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/TrialBalance" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
             <i class="fa fa-area-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Profit & Loss A/c</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The Profit and Loss Account, also known as the Income Statement or Statement of Comprehensive Income, is a financial statement that summarizes a company's revenues, expenses, gains, and losses over a specific period of time</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/ProfitAndLoss" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="icon ion-arrow-graph-up-right mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Balance Sheet</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">A balance sheet is a financial statement that provides a snapshot of a company's financial position at a specific point in time</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/BalanceSheet" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div>

</asp:Content>