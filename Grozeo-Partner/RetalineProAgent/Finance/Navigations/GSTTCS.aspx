<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="GSTTCS.aspx.cs"  MasterPageFile="~/Finance/FinanceMaster.master"  Inherits="RetalineProAgent.Finance.Navigations.GSTTCS" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
           <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">GST-TCS</h6>
    <p class="mb-0">GST-TCS</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-6 mb-3 mb-lg-0">
              <a href="/Finance/DailySalesReport" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-calculator mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Daily GST TCS Report</h5>
                  <p class="card-text mg-b-8 tx-12">Daily GST TCS Report are defined here for a detailed analysis of statutory compliance</p>
                </div>
              </a>
            </div><!--col-lg-->

            <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i class="fa fa-calculator mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">GST</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Taxation on Goods and Services: GST is applicable to the supply of both goods and services</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

            <div class="col-lg-6 mb-3 mb-lg-0">
              <a href="/Finance/DetailedSalesReport.aspx" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Detailed GST TCS Report</h5>
                  <p class="card-text mg-b-8 tx-12">Read and Verify tax deductions at the time of releasing the payments from the list.</p>
                </div>
              </a>
            </div><!--col-lg-->
          
          <%--<div class="col-lg">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
               <i class="fa fa-file-text mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">TDS</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">TDS stands for Tax Deducted at Source. It is a system of collecting taxes in India at the time of making certain payments, such as salaries, interest, rent, or professional fees.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg--> --%>          
        </div>

</asp:Content>