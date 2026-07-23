<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="BusinessCRM.aspx.cs" MasterPageFile="~/Business/BusinessMaster.master" Inherits="RetalineProAgent.BusinessNavigations.BusinessCRM" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">CRM</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">CRM</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/Contacts" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-address-book mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Contacts</h5>
                        <p class="card-text mg-b-8 tx-11">Simplify your contact management with our comprehensive admin panel. Easily organize and access important contact information, streamline communication, and stay connected effortlessly.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/AssociateLeads" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-bar-chart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Leads</h5>
                        <p class="card-text mg-b-8 tx-11">Maximize your potential with our admin panel designed for retailer leads. Effortlessly manage and track leads, streamline communication, and convert prospects into valuable retail partnerships.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/Prospects" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-shopping-cart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Prospects</h5>
                        <p class="card-text mg-b-8 tx-11">Empower your retail business with our dynamic admin panel. Effortlessly manage and update retailer information, track performance, and foster strong partnerships for sustainable growth.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/CRMRetailers" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-shopping-cart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Retailers</h5>
                        <p class="card-text mg-b-8 tx-11">Empower your retail business with our dynamic admin panel. Effortlessly manage and update retailer information, track performance, and foster strong partnerships for sustainable growth.</p>
                    </div>
                </a>
            </div>


            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-address-book mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Contacts </h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Simplify your contact management with our comprehensive admin panel. Easily organize and access important contact information, streamline communication, and stay connected effortlessly.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/Contacts" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-bar-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Leads</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Maximize your potential with our admin panel designed for retailer leads. Effortlessly manage and track leads, streamline communication, and convert prospects into valuable retail partnerships.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/AssociateLeads" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-shopping-cart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Prospects</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Empower your retail business with our dynamic admin panel. Effortlessly manage and update retailer information, track performance, and foster strong partnerships for sustainable growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/Prospects" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

            <%--<div class="col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-shopping-cart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Retailers</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Empower your retail business with our dynamic admin panel. Effortlessly manage and update retailer information, track performance, and foster strong partnerships for sustainable growth.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/CRMRetailers" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->

            <div class="col-lg-3 mb-4" runat="server" visible="false">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-area-chart mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Wholesaler Leads</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Unlock opportunities with our dedicated admin panel for wholesaler leads. Seamlessly manage and nurture leads, streamline communication, and establish profitable partnerships with ease.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/WholesalerLeads" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

            

            

        </div>

</asp:Content>