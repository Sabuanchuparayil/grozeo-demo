<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="RevenueStream.aspx.cs" MasterPageFile="~/Business/BusinessMaster.master" Inherits="RetalineProAgent.BusinessNavigations.RevenueStream" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessAccounts">Accounts</a></li>
    <li class="breadcrumb-item active" aria-current="page">Revenue Streams</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Revenue Streams</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">


            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Subscription Charges Revenue Share</h5>
                        <p class="card-text mg-b-8 tx-11">Efficiently manage subscription charges and revenue share with our dedicated admin panel. Track, analyze, and optimize revenue streams from subscriptions with ease and accuracy.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-sitemap mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Mobile App License Revenue Share</h5>
                        <p class="card-text mg-b-8 tx-11">Take control of mobile app license revenue share with our intuitive admin panel. Effortlessly manage, track, and optimize revenue streams from mobile app licenses for maximum profitability.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-money mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Merchant Order Commission</h5>
                        <p class="card-text mg-b-8 tx-11">Simplify commission management for merchant orders with our comprehensive admin panel. Track, analyze, and optimize order commissions effortlessly, empowering your business growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-headphones mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Support Center Credit Commission</h5>
                        <p class="card-text mg-b-8 tx-11">Effortlessly manage credit commissions for support center transactions with our dedicated admin panel. Streamline tracking, analysis, and optimization of credit commissions for effective support center management.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-gift mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Facilitation Charges</h5>
                        <p class="card-text mg-b-8 tx-11">Efficiently handle delivery facilitation charges with our user-friendly admin panel. Simplify tracking, analysis, and optimization of charges for streamlined delivery operations.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-pie-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Wholesale Revenue Share</h5>
                        <p class="card-text mg-b-8 tx-11">Maximize wholesale revenue share with our comprehensive admin panel. Seamlessly manage, track, and optimize revenue distribution for successful wholesale partnerships and sustainable growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="#" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-laptop mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Advertorial Revenue</h5>
                        <p class="card-text mg-b-8 tx-11">Unlock the full potential of advertorial revenue with our advanced admin panel. Effortlessly manage, track, and optimize revenue from advertorials for impactful advertising campaigns and increased profitability.</p>
                    </div>
                </a>
            </div>


            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-bar-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Subscription Charges Revenue Share</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Efficiently manage subscription charges and revenue share with our dedicated admin panel. Track, analyze, and optimize revenue streams from subscriptions with ease and accuracy.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-sitemap mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Mobile App License Revenue Share</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Take control of mobile app license revenue share with our intuitive admin panel. Effortlessly manage, track, and optimize revenue streams from mobile app licenses for maximum profitability.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-money mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Merchant Order Commission</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Simplify commission management for merchant orders with our comprehensive admin panel. Track, analyze, and optimize order commissions effortlessly, empowering your business growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-headphones mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Support Center Credit Commission</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Effortlessly manage credit commissions for support center transactions with our dedicated admin panel. Streamline tracking, analysis, and optimization of credit commissions for effective support center management.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-gift mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Facilitation Charges</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Efficiently handle delivery facilitation charges with our user-friendly admin panel. Simplify tracking, analysis, and optimization of charges for streamlined delivery operations.</p>
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
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Wholesale Revenue Share</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Maximize wholesale revenue share with our comprehensive admin panel. Seamlessly manage, track, and optimize revenue distribution for successful wholesale partnerships and sustainable growth.</p>
                 <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          

          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-laptop mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Advertorial Revenue</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Unlock the full potential of advertorial revenue with our advanced admin panel. Effortlessly manage, track, and optimize revenue from advertorials for impactful advertising campaigns and increased profitability.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="#" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
        </div>

</asp:Content>