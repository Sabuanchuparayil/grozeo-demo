<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="BusinessReports.aspx.cs" MasterPageFile="~/Business/BusinessMaster.master" Inherits="RetalineProAgent.BusinessNavigations.BusinessReports" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessAccounts">Accounts</a></li>
    <li class="breadcrumb-item active" aria-current="page">Revenue Streams</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Reports</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-area-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Area Sales Report</h5>
                        <p class="card-text mg-b-8 tx-11">Gain valuable insights with our efficient admin panel for area sales reports. Analyze sales performance, track trends, and make data-driven decisions to drive growth in your target areas.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-signal mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">RO Sales Report</h5>
                        <p class="card-text mg-b-8 tx-11">Effortlessly track and analyze sales performance with our dedicated admin panel for RO sales reports. Gain valuable insights, optimize strategies, and drive revenue growth as a Relationship Officer.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-line-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Merchant Sales Report</h5>
                        <p class="card-text mg-b-8 tx-11">Optimize your merchant sales strategy with our comprehensive admin panel for merchant sales reports. Track and analyze sales performance, identify trends, and drive revenue growth for successful merchant partnerships.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-fa-pie-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Revenue Summary</h5>
                        <p class="card-text mg-b-8 tx-11">Get a comprehensive view of your revenue with our powerful admin panel for revenue summary. Track, analyze, and optimize your overall revenue performance for informed decision-making and business growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Detailed Revenue Report</h5>
                        <p class="card-text mg-b-8 tx-11">Unlock detailed insights into your revenue streams with our comprehensive admin panel for detailed revenue reports. Analyze, track, and optimize revenue across various metrics for strategic decision-making and sustainable growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-file-text mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Settlement Report</h5>
                        <p class="card-text mg-b-8 tx-11">Streamline your financial operations with our efficient admin panel for settlement reports. Gain comprehensive insights into settlements, track transactions, and ensure accurate financial reconciliation for smooth business operations.</p>
                    </div>
                </a>
            </div>

            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-area-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Area Sales Report</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Gain valuable insights with our efficient admin panel for area sales reports. Analyze sales performance, track trends, and make data-driven decisions to drive growth in your target areas.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-signal mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">RO Sales Report</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Effortlessly track and analyze sales performance with our dedicated admin panel for RO sales reports. Gain valuable insights, optimize strategies, and drive revenue growth as a Relationship Officer.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-line-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Merchant Sales Report</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Optimize your merchant sales strategy with our comprehensive admin panel for merchant sales reports. Track and analyze sales performance, identify trends, and drive revenue growth for successful merchant partnerships.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-pie-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Revenue Summary</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Get a comprehensive view of your revenue with our powerful admin panel for revenue summary. Track, analyze, and optimize your overall revenue performance for informed decision-making and business growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-bar-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Detailed Revenue Report</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Unlock detailed insights into your revenue streams with our comprehensive admin panel for detailed revenue reports. Analyze, track, and optimize revenue across various metrics for strategic decision-making and sustainable growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i aria-hidden="true" class="fa fa-file-text mg-y-3 tx-34 tx-primary"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Settlement Report</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Streamline your financial operations with our efficient admin panel for settlement reports. Gain comprehensive insights into settlements, track transactions, and ensure accurate financial reconciliation for smooth business operations.</p>
                 <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div>

</asp:Content>