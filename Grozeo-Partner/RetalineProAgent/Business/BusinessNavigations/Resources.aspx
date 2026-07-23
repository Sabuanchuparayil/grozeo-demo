<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Resources.aspx.cs" MasterPageFile="~/Business/BusinessMaster.master" Inherits="RetalineProAgent.BusinessNavigations.Resources" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resources</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Resources</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row row-sm menucard">

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/RelationshipOfficer" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-handshake-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Relationship Officer</h5>
                        <p class="card-text mg-b-8 tx-11">Simplify your role as a Relationship Officer with our user-friendly admin panel. Streamline tasks, access crucial information, and enhance productivity effortlessly.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/AreaManager" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-handshake-o mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Area Manager</h5>
                        <p class="card-text mg-b-8 tx-11">Optimize your role as an Area Manager with our powerful admin panel. Gain valuable insights, streamline operations, and drive effective decision-making effortlessly.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Business/DeliveryStaff" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-id-badge mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Staff</h5>
                        <p class="card-text mg-b-8 tx-11">Empower your delivery staff with our intuitive admin panel. Seamlessly manage orders, track deliveries, and enhance efficiency for a smooth and successful delivery process.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Business/DeliveryRate" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-clipboard-list mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery Rates</h5>
                <p class="card-text mg-b-8 tx-11">Customize delivery options for customers with rates & preferences.</p>
              </div>
            </a>
          </div><!--col-lg-->


            <%--<div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="fa fa-handshake-o mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Relationship Officer</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Simplify your role as a Relationship Officer with our user-friendly admin panel. Streamline tasks, access crucial information, and enhance productivity effortlessly.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/RelationshipOfficer" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>
          
          <%--<div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="icon fa fa-users mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Area Manager</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Optimize your role as an Area Manager with our powerful admin panel. Gain valuable insights, streamline operations, and drive effective decision-making effortlessly.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/AreaManager" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg-->--%>

            <%--<div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header tx-medium bd-0 bg-white tx-center p-2">
                <i class="icon fa fa-id-badge mg-y-3 tx-34 tx-primary" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Staff</h5>
              </div>
              <div class="card-body pd-t-0-force p-2 tx-center position-relative">
                <p class="card-text mg-b-8 tx-11">Empower your delivery staff with our intuitive admin panel. Seamlessly manage orders, track deliveries, and enhance efficiency for a smooth and successful delivery process.</p>
                <div class="card_view  p-2 tx-center">
                  <a href="/Business/DeliveryStaff" class="card-link btn btn-secondary">Access Now</a>
                </div>
              </div>
            </div>
          </div><!--col-lg----%>
        </div>

</asp:Content>