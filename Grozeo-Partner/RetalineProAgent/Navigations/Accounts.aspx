<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" AutoEventWireup="true" Title="Reports" CodeBehind="Accounts.aspx.cs" Inherits="RetalineProAgent.Navigations.Accounts" %>


<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounts & MIS</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Reports</h6>
        <p class="mb-0">Enhance decision-making with the comprehensive Reports module.</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

            <div class="row row-sm menucard">

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/salesreport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-display-chart-up-circle-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Sales</h5>
                <p class="card-text mg-b-8 tx-11">Access comprehensive reports for a deeper understanding of sales.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/packingReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-box-taped mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Packing</h5>
                <p class="card-text mg-b-8 tx-11">Access reports for better planning and execution of packing tasks.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/deliveryReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-person-carry-box mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery</h5>
                <p class="card-text mg-b-8 tx-11">Leverage delivery data analysis for enhanced service efficiency.</p>
              </div>
            </a>
          </div><!--col-lg-->  
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/Finance/PayOutReports" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-pie-simple-circle-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Pay Out Reports</h5>
                <p class="card-text mg-b-8 tx-11">Get detailed insights into your financial performance and payout status</p>
              </div>
            </a>
          </div><!--col-lg--> 
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/PerformanceReports" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-line mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Performance Reports</h5>
                <p class="card-text mg-b-8 tx-11">Analyze key metrics and gain valuable insights to drive growth and success</p>
              </div>
            </a>
          </div><!--col-lg--> 
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Finance/MerchantPassbook" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-address-card mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Merchant Passbook</h5>
                <p class="card-text mg-b-8 tx-11">Show Merchant's debit, credit, opening balance and closing balance for a given period.</p>
              </div>
            </a>
          </div><!--col-lg--> 

          <%--<div class="col-lg-3 mb-4 d-none" >
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="icon ion-connection-bars mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Pay Out Reports</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Based on the agreements, Grozeo releases the payments automatically to the bank account you updated. All the details of pay outs and its calculations will be available through the Pay Out Report available here</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/Finance/PerformanceReports" class="card-link btn btn-secondary">More link</a>
                </div>
              </div>
            </div>
          </div>--%><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/passbook" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-tachograph-digital mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Passbook</h5>
                <p class="card-text mg-b-8 tx-11">Access a comprehensive history of transactions in your passbook.<br /><br /></p>
              </div>
            </a>
          </div><!--col-lg--> 
        
         <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                    <a href="/Tenant/Finance/MerchantTaxreport" class="card h-100 p-4">
                      <div class="card-body p-0 tx-left position-relative">
                          <i class="fa-thin fa-chart-column mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Tax Report</h5>
                        <p class="card-text mg-b-8 tx-11">Generate comprehensive tax reports to ensure accurate filings.</p>
                      </div>
                    </a>
                  </div><!--col-lg-->

         <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/navigations/salesReports" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-mixed mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Transactions</h5>
                <p class="card-text mg-b-8 tx-11">Maintain a comprehensive record of all business transactions.</p>
              </div>
            </a>
          </div><!--col-lg-->
         <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/Finance/SettlementReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-line-down mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Settlement Report</h5>
                <p class="card-text mg-b-8 tx-11">Effortlessly review and track settlements for improved efficiency.</p>
              </div>
            </a>
          </div><!--col-lg-->
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/ReceiveCash" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-hand-holding-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Receive Cash</h5>
                <p class="card-text mg-b-8 tx-11">Seamlessly manage and track cash receipts for accurate records.</p>
              </div>
            </a>
          </div><!--col-lg-->
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/SMSReport" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-comment-sms mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">SMS Report</h5>
                <p class="card-text mg-b-8 tx-11">SMS Report.</p>
              </div>
            </a>
          </div><!--col-lg-->

                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                    <a href="#" class="card h-100 p-4">
                        <div class="card-body p-0 tx-left position-relative">
                            <i class="fa-thin fa-arrow-right-arrow-left mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Returns</h5>
                            <p class="card-text mg-b-8 tx-11">View all returned orders with details such as return date, reason, status, and associated refund or replacement information.</p>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                    <a href="#" class="card h-100 p-4">
                        <div class="card-body p-0 tx-left position-relative">
                            <i class="fa-thin fa-messages-question mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Communications</h5>
                            <p class="card-text mg-b-8 tx-11">View and manage all customer and seller interactions, including messages, notifications etc. with status and history.
</p>
                        </div>
                    </a>
                </div>
    </div><!--row-->

</asp:Content>
