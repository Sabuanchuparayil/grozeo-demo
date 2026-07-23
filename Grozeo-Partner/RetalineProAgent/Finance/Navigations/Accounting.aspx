<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Accounting.aspx.cs" Title="Finance" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.Navigations.Accounting" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Accounting</h6>
    <p class="mb-0">Accounting</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">
            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/DataEntry" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Vouchers</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage all the vouchers including those auto generated.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i class="fa fa-file-text-o mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 "></h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11"> vouchers offer a flexible and cost-effective way for businesses to incentivize and engage customers while also driving sales and revenue.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/DataEntry" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div>--%><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/PendingEntries" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-exchange mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Transaction Logs</h5>
                  <p class="card-text mg-b-8 tx-12">System-generated vouchers on events that could not be tallied will list here</p>
                </div>
              </a>
            </div><!--col-lg-->
          
             <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/Autopostingvalues" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Order Value Logs</h5>
                  <p class="card-text mg-b-8 tx-12">Get a detailed one-stop report on all computable values of every transactions.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/FailedOrderValuelog" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Failed Order Value Logs</h5>
                  <p class="card-text mg-b-8 tx-12">Get a detailed one-stop report on all computable values of every transactions of Failed orders.</p>
                </div>
              </a>
            </div><!--col-lg-->                    
             <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="#" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-bars mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Cost Centre Logs</h5>
                  <p class="card-text mg-b-8 tx-12">Cost Centre entries log can be viewed here.</p>
                </div>
              </a>
            </div><!--col-lg-->


         <%-- <div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i class=" mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Subscriptions</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">The transaction log is used to create other financial records, such as the general ledger and financial statements</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Finance/FinanceSubscriptions" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div>

</asp:Content>