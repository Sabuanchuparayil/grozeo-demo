<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Support.aspx.cs" Inherits="RetalineProAgent.Navigations.Support" %>


<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Support</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Support</h6>
    <p class="mb-0">Explore helpful tools and resources for a smooth user experience.</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/BusinessFAQ" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-square-list mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Business FAQ</h5>
                <p class="card-text mg-b-8 tx-11">Easily find responses to frequently asked business questions.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class=" col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/contact" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-envelope mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Customer Center</h5>
                <p class="card-text mg-b-8 tx-11">Enhance customer satisfaction by managing interactions and preferences.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/MessageCenter" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-messages mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Message Center</h5>
                <p class="card-text mg-b-8 tx-11">Simplify customer message handling for improved communication.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/CustCommunication" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-comments-question-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Customer Communications</h5>
                <p class="card-text mg-b-8 tx-11">Enhance engagement by effectively communicating with your audience.</p>
              </div>
            </a>
          </div><!--col-lg-->
        </div><!--row-->

</asp:Content>