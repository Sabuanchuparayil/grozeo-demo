<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ContentsPages.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.ContentsPages" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Appearance">Appearance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Content Pages</li>--%>
    <a href="/Navigations/Appearance"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Content Pages</h6>
        <p class="mb-0">Compelling Content Creation</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/AboutContent" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">About Us</h5>
                <p class="card-text mg-b-8 tx-11">Share your brand story, team details, and values to build trust and connection with customers.</p>
              </div>
            </a>
          </div><!--col-lg-->
          
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/HowItWorks" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-sitemap mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">How it Works</h5>
                <p class="card-text mg-b-8 tx-11"> Explain your website's functionality and features, guiding users on navigation and usage.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/FAQ" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-list-alt mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">FAQs & Support</h5>
                <p class="card-text mg-b-8 tx-11">Provide answers to common queries and offer support resources for seamless customer assistance.</p>
              </div>
            </a>
          </div><!--col-lg-->
          
          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/PrivacyPolicy" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-shopping-basket mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Privacy Policy</h5>
                <p class="card-text mg-b-8 tx-11">Detail data handling practices, ensuring transparency and compliance with privacy regulations.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/TermsOfUse" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa ion-person-stalker mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Terms of Use</h5>
                <p class="card-text mg-b-8 tx-11">Define rules and agreements for website usage, establishing legal obligations and user rights.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa ion-person-stalker mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Footer Content</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Order Pickers are the sales staff working inside your shop who help to pick and pack the orders when your receive an order. You need to provide a unique number to create the Order Picker.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Tenant/FooterContent" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/RefundRetunExchPolicy" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-undo-alt mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Return and Refund Policy</h5>
                <p class="card-text mg-b-8 tx-11"> Learn about our return and refund policy for quick, easy, and hassle-free order resolutions.</p>
              </div>
            </a>
          </div><!--col-lg-->
        </div>
</asp:Content>