<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Finance" CodeBehind="ChartofAccounts.aspx.cs" Inherits="RetalineProAgent.Finance.Navigations.ChartofAccounts" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Settings</h6>
    <p class="mb-0">Settings</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/AccountSetup" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Accounting Structure</h5>
                  <p class="card-text mg-b-8 tx-12">Go through the details of accounting structures here with a tree based listing rules</p>
                </div>
              </a>
            </div><!--col-lg-->
        <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/costallocationrules" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-files-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Cost Allocation Rules</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View and Manage the cost allocation rules for managing the Cost Centers.</p>
                </div>
              </a>
            </div><!--col-lg-->
            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/Autopostingsettings" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Auto Posting Rules</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View and Manage the Financial Posting rules to auto post in various ledgers</p>
                </div>
              </a>
            </div><!--col-lg-->
             <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/Settlementaplications" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Settlement Application</h5>
                  <p class="card-text mg-b-8 tx-12">Find the application for settlement account creation from merchant to manage it</p>
                </div>
              </a>
            </div><!--col-lg-->
        </div>

</asp:Content>