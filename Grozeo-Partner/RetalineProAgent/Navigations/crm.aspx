<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="crm.aspx.cs" Inherits="RetalineProAgent.Navigations.crm" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Relation</li>--%>
  <%--  <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">CRM</h6>
        <p class="mb-0">Nurture Connections</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Customers" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-calendar-circle-user mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Customers</h5>
                        <p class="card-text mg-b-8 tx-11">Nurture lasting customer relationships for repeat business success.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Navigations/Support" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-calendar-circle-user mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Support</h5>
                        <p class="card-text mg-b-8 tx-11">View and track customer support tickets, issues, and resolutions, including status, response times, and performance metrics.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Leads" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-calendar-lines-pen mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Leads</h5>
                <p class="card-text mg-b-8 tx-11">Efficiently manage and convert potential customers into valuable leads.</p>
              </div>
            </a>
          </div><!--col-lg-->

          

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Campaigns" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-message-lines mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Campaigns</h5>
                <p class="card-text mg-b-8 tx-11">Strategize and execute targeted campaigns to reach your audience.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4"">
            <a href="/Tenant/DiscountCoupons" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-badge-percent mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Coupons</h5>
                <p class="card-text mg-b-8 tx-11">Manage all your discount coupons effortlessly with our user-friendly tool.</p>
              </div>
            </a>
          </div><!--col-lg-->
        </div><!--row-->

</asp:Content>