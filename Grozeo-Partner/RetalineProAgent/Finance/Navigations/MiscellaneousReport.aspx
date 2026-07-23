<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="MiscellaneousReport.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master"   Inherits="RetalineProAgent.Finance.Navigations.MiscellaneousReport" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <%-- <li class="breadcrumb-item"><a href="/">Home</a></li>
   <li class="breadcrumb-item active" aria-current="page">Miscellaneous Report</li>--%>
   <a href="/Navigations/AccountBooks"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Miscellaneous Report</h6>
    <p class="mb-0">Miscellaneous Report</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row row-sm menucard">
          <div class="col-lg-4 mb-3">
              <a href="/Finance/SettlementReportDownload" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-files-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Settlement Data</h5>
                  <p class="card-text mg-b-8 tx-12">View and download the settlement data here</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3">
              <a href="/Finance/MerchantBankDetails" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa icon ion-filing mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Merchant Bank Details</h5>
                  <p class="card-text mg-b-8 tx-12">View and Download merchant bank details from here</p>
                </div>
              </a>
            </div><!--col-lg-->

    </div>
</asp:Content>

