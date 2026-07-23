<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="GSTReports.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.Navigations.GSTReports" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/TaxesandDuties"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">GST Reports</h6>
    <p class="mb-0">GST Reports</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-6 mb-3 mb-lg-0">
              <a href="/Finance/GST_on_Sales95" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-calculator mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Daily Report on GST on Sales u/s 9(5) by ECO</h5>
                  <p class="card-text mg-b-8 tx-12">Daily Report on GST on Sales u/s 9(5) by ECO</p>
                </div>
              </a>
            </div><!--col-lg-->


            <div class="col-lg-6 mb-3 mb-lg-0">
              <a href="/Finance/GST_on_Sales95_Detailed" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-calculator mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">GST on Sales u/s 9(5) by ECO Daily Detailed Report</h5>
                  <p class="card-text mg-b-8 tx-12">GST on Sales u/s 9(5) by ECO Detailed Report</p>
                </div>
              </a>
            </div><!--col-lg-->       
        </div>

</asp:Content>