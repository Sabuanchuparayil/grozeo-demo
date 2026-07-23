<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Others.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Others" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Appearance</li>--%>
    <a href="/Navigations/Products"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> Others</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
              <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
  <a href="/Tenant/Brands" class="card h-100 p-4">
    <div class="card-body p-0 tx-left position-relative">
        <i class="fa-thin fa-wreath-laurel mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
      <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Brands</h5>
      <p class="card-text mg-b-8 tx-11">List, view, and edit your brands easily for better management and brand visibility.</p>
    </div>
  </a>
</div><!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
    <a href="/Tenant/productgroup" class="card h-100 p-4">
      <div class="card-body p-0 tx-left position-relative">
          <i class="fa-thin fa-grid-2-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Product Groups</h5>
        <p class="card-text mg-b-8 tx-11">Group product to variant groups. The grouped items will be listed in the product details page as variants. Grouped items can be added to the cart from details page only.</p>
      </div>
    </a>
  </div><!--col-lg-->
            <div class=" col-sm-6 col-lg-4 mb-3 mb-lg-4">
    <a href="/Tenant/SponsoredProducts" class="card h-100 p-4">
      <div class="card-body p-0 tx-left position-relative">
          <i class="fa-thin fa-grid-2-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Sponsored</h5>
        <p class="card-text mg-b-8 tx-11">Improve customer acquisition with effective sponsored product campaigns.</p>
      </div>
    </a>
  </div><!--col-lg-->
          <% if (!Page.User.IsInRole("BranchManager") && !Page.User.IsInRole("StoreManager"))
        { %>

    <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
    <a href="/Tenant/API_connector" class="card h-100 p-4">
      <div class="card-body p-0 tx-left position-relative">
          <i class="fa-thin fa-webhook mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">API</h5>
        <p class="card-text mg-b-8 tx-11">Integrate and sync your platform with various APIs and connectors.</p>
      </div>
    </a>
  </div><!--col-lg-->
  <% } %>

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
    <a href="/Tenant/PrivateCategory?type=featured" class="card h-100 p-4">
        <div class="card-body p-0 tx-left position-relative">
            <i class="fa-thin fa-star mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
            <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Featured List</h5>
            <p class="card-text mg-b-8 tx-11">Highlighted as a standout product for increased visibility.</p>
        </div>
    </a>
</div>

              <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
  <a href="/Tenant/PrivateCategory?type=preferred" class="card h-100 p-4">
    <div class="card-body p-0 tx-left position-relative">
        <i class="fa-thin fa-heart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
      <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Preferred List</h5>
      <p class="card-text mg-b-8 tx-11">Marked as a preferred choice for prioritized recognition.</p>
    </div>
  </a>
</div>
            
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/MobileApp" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-mobile mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Android App</h5>
                <p class="card-text mg-b-8 tx-11">Creating your branded mobile application is just a click away.</p>
              </div>
            </a>
          </div>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
                <a href="/Tenant/PrivateCategory?type=featured" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-star mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Featured List</h5>
                        <p class="card-text mg-b-8 tx-11">Highlighted as a standout product for increased visibility.</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/PrivateCategory?type=preferred" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-heart mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Preferred List</h5>
                <p class="card-text mg-b-8 tx-11">Marked as a preferred choice for prioritized recognition.</p>
              </div>
            </a>
          </div>
        </div><!--row-->
</asp:Content>