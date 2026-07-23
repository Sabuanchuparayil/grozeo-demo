<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Delivery.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Delivery" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery</li>--%>
    <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Advanced</h6>
        <p class="mb-0">Efficiently manage package types and delivery</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/PackageType" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-light fa-box-taped mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Package Type</h5>
                <p class="card-text mg-b-8 tx-11">Create custom package types for products, boosting flexibility and customer satisfaction on your ecommerce site.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4 d-none">
                <a href="/Tenant/MerchantControls" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-regular fa-rectangle-history-circle-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Packing Details Collection</h5>
                        <p class="card-text mg-b-8 tx-11">Control the information regarding the order invoice and package details here.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Branches?type=DeliveryRates" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-clipboard-list mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Delivery Rates</h5>
                <p class="card-text mg-b-8 tx-11">Customize delivery options for customers with rates & preferences.</p>
              </div>
            </a>
          </div><!--col-lg-->

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/DeliverySlot" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-person-dolly mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Slots</h5>
                <p class="card-text mg-b-8 tx-11">Effortlessly create and manage delivery slots for flexible schedules.</p>
              </div>
            </a>
          </div><!--col-lg-->


            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Deliveryzone" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-light fa-cubes mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Delivery Zones</h5>
                        <p class="card-text mg-b-8 tx-11">Create the specific delivery zones to seamlessly manage the delivery areas of your stores.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/MobileApp" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-mobile mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Android App</h5>
                        <p class="card-text mg-b-8 tx-11">Creating your branded mobile application is just a click away.</p>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/DomainControl" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-link mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Domain Control</h5>
                        <p class="card-text mg-b-8 tx-11">Effortlessly manage and control your website's domain settings.</p>
                        <%--<div class="card_view  p-2 tx-center">
            <% if (this.CurrentUser.PackageId < 2)
               { %>
                  <a href="/Tenant/DomainControl" data-toggle="modal" data-target="#modalupgrade" class="card-link btn btn-secondary" title="Configure custom domain">Configure</a>            
              <% }
                  else
                  { %>
                      <a href="/Tenant/DomainControl" class="card-link btn btn-secondary">Configure</a>
                   <% } %>
          </div>--%>
                    </div>

                </a>
            </div>
            <!--col-lg-->
            
        </div><!--row-->
</asp:Content>