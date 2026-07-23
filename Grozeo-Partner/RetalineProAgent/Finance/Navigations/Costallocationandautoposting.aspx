<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Finance" CodeBehind="Costallocationandautoposting.aspx.cs" Inherits="RetalineProAgent.Finance.Navigations.Costallocationandautoposting" %>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Masters</h6>
    <p class="mb-0">Masters</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Finance"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
     <div class="row row-sm menucard">
        
         
            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/GroupManagement" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-sitemap mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Groups</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage the Account Groups and Sub Groups here </p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/LedgerManagement" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="icon ion-arrow-graph-up-right mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Ledgers</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage the parties ledgers through this menu </p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/Costcategory" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Cost Categories</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage the Cost Category masters through this menu</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/CostCentre" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-files-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Cost Centers</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage all the vouchers including those auto generated.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/Costpurpose" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-files-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Manage Cost Purpose</h5>
                  <p class="card-text mg-b-8 tx-12">Create, View, Edit and Manage the Cost Purposes through this menu.</p>
                </div>
              </a>
            </div>
                   <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/orderCalculationHeads" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Order Calculation Heads</h5>
                  <p class="card-text mg-b-8 tx-12">View the Order Value Heads defined in the system through this menu for computations.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/marginapplicable" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Margin Applicable</h5>
                  <p class="card-text mg-b-8 tx-12">Applicable Margins details defined in the system can be access through this menu.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/event_master" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Auto Posting Functions</h5>
                  <p class="card-text mg-b-8 tx-12">View the Auto Posting Functions defined in the system through this menu.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/areatype" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Area Types</h5>
                  <p class="card-text mg-b-8 tx-12">View the various Area Types defined in the system through this menu.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/deliverytype" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Delivery Types</h5>
                  <p class="card-text mg-b-8 tx-12">View the various Delivery Types defined in the system through this menu.</p>
                </div>
              </a>
            </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/payment_type" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Payment Types</h5>
                  <p class="card-text mg-b-8 tx-12">View the different payment types defined in the system through this menu.</p>
                </div>
              </a>
            </div><!--col-lg--> 

            <div class="col-lg-4 mb-3 mb-lg-4">
              <a href="/Finance/valuehead" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-file-text-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                  <h5 class="card-title tx-medium mg-b-4 tx-16 tx-gray-700">Value Head</h5>
                  <p class="card-text mg-b-8 tx-12">View/Edit the different valueheads defined in the system through this menu.</p>
                </div>
              </a>
            </div><!--col-lg--> 
     </div>
</asp:Content>

