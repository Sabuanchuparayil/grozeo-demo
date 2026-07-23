<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Products.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Products" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle m-0">Product</h6>
        <p class="mb-0">Enhance Your Product Portfolio</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
        <% if (!Page.User.IsInRole("StoreManager"))
                { %>
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/MyProducts" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">My Products</h5>
                <p class="card-text mg-b-8 tx-11">Enhance customer engagement with up-to-date product listings.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/InventoryMapping" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-th mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Select Products</h5>
                <p class="card-text mg-b-8 tx-11">Grozeo brings you branded products details known as Brand Gallery. You can select the branded products available within the stores from the our Brand Gallery to start selling the products</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/Products" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa fa-cart-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Add Products</h5>
                <p class="card-text mg-b-8 tx-11">If you cant find the product details within our Brand Gallery, you can create the products using this menu. If the products belongs to the Brand we have associated with us, it will be added to Brand Gallery</p>
              </div>
            </a>
          </div><!--col-lg-->
          <% } %>

          <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/StockPrice" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-money-bill-trend-up mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Stock & Price</h5>
                <p class="card-text mg-b-8 tx-11">Enhance inventory management with seamless stock and pricing updates.</p>
                </div>
            </a>
          </div><!--col-lg-->

        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/productgroup" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-grid-2-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Product Groups</h5>
                <p class="card-text mg-b-8 tx-11">Group product to variant groups. The grouped items will be listed in the product details page as variants. Grouped items can be added to the cart from details page only.</p>
              </div>
            </a>
          </div><!--col-lg-->
             <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" style="display:none">
            <a href="/Tenant/Comboproduct" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-tally-4 mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Combo Products</h5>
                <p class="card-text mg-b-8 tx-11">Create Your Combo offer here to sell more product online to the Customers.</p>
                </div>
            </a>
          </div><!--col-lg-->
        <div class=" col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/SponsoredProducts" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-grid-2-plus mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Sponsored</h5>
                <p class="card-text mg-b-8 tx-11">Improve customer acquisition with effective sponsored product campaigns.</p>
              </div>
            </a>
          </div><!--col-lg-->

        <% if (!Page.User.IsInRole("StoreManager"))
                { %>

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/API_connector" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-webhook mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">API / Connectors</h5>
                <p class="card-text mg-b-8 tx-11">Integrate and sync your platform with various APIs and connectors.</p>
              </div>
            </a>
          </div><!--col-lg-->
          <% } %>
           
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/StoreCategory" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-list-ol mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Store Categories</h5>
                <p class="card-text mg-b-8 tx-11">Streamline product categorization for improved customer navigation.</p>
              </div>
              
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/PrivateCategory" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-list-radio mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Private Categories</h5>
                <p class="card-text mg-b-8 tx-11">Create private categories for personalized customer experience.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4" runat="server" visible="false">
            <a href="/Tenant/Brands" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-wreath-laurel mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Brands</h5>
                <p class="card-text mg-b-8 tx-11">List, view, and edit your brands easily for better management and brand visibility.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Navigations/Categories" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-list-radio mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Categories</h5>
                        <p class="card-text mg-b-8 tx-11">View and manage product categories, including creation, editing, organising and mapping of items for better navigation and search.</p>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Navigations/Others" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-list-radio mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Extras</h5>
                        <p class="card-text mg-b-8 tx-11">View and manage miscellaneous products or services that do not fall under standard categories, including their details and status.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

        </div>
</asp:Content>