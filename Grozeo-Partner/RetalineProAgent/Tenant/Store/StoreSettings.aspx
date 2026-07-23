<%@ Page Language="C#" Title="Store Settings" MaintainScrollPositionOnPostback="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="StoreSettings.aspx.cs" Inherits="RetalineProAgent.StoreSettings" %>

<%@ Register Src="~/Controls/StoreSettings/ctrlInventorySetup.ascx" TagPrefix="uc1" TagName="ctrlInventorySetup" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlCreateStore.ascx" TagPrefix="uc1" TagName="ctrlCreateStore" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>

</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
<asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Account Settings</li>
</asp:PlaceHolder>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <asp:PlaceHolder ID="plcWizard" Visible="false" runat="server">
        <div class="processingsect ">
            <ul class="processingwrap">
              <li class="active">
                <div class="processing-title">Create Store</div>
              </li>
                <% if (this.CurrentUser.TenantType != 2)
                    { %>
              <li class="">
                <div class="processing-title">Select Products</div>
              </li>
              <li class="">
                <div class="processing-title">Manage Stock</div>
              </li>
                <% } %>
              <li class="">
                <div class="processing-title">Sponsored Products</div>
              </li>
              <li class="">
                <div class="processing-title">Publish Store</div>
              </li>
            </ul>
          </div><!--processingsect-->
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcNoneWizard" Visible="true" runat="server">
        <div>
            <h6 class="slim-pagetitle">Account Settings</h6>
            <p class="mb-0">Profile customization and preferences</p>
        </div>
    </asp:PlaceHolder>
    
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

<asp:PlaceHolder ID="plcConf" runat="server">

        <div class="row row-sm multicard_view">
          <div class="col-lg-4 mb-4">
            <div class="card h-100 p-3">
                <div class="card-contact border-0 p-0">
                    <h6 class="slim-card-title tx-primary">Business Settings</h6>

                    <div class="tx-center">
                        <a id="hlPublicSite" target="_blank" runat="server" href="#">
                            <img src="/Content/images/userImage.png" onerror="this.src='/content/images/face.png'" runat="server" id="imgStore" class="card-img" alt="" width="30" style="max-height: 58px; width: auto; max-width: 100%;"></a>
                        <h5 class="mg-t-10 mg-b-5"><a href="#" class="contact-name">
                            <asp:Literal ID="ltrPageTitle" runat="server" Text="Create Store"></asp:Literal></a>
                            <%--<asp:LinkButton runat="server" OnClick="lbtnEditStore_Click" CssClass="tx-thin" style="font-size: 13px;"><i class="icon ion-social-codepen-outline"></i>Edit</asp:LinkButton>--%>
                            <asp:HyperLink runat="server" NavigateUrl="/Tenant/ManageBusinessInfo" CssClass="tx-thin" Style="font-size: 13px;"><i class="icon ion-social-codepen-outline"></i>Edit</asp:HyperLink>
                        </h5>
                        <asp:LinkButton ID="lbtnEditStore" OnClick="lbtnEditStore_Click" Style="display: none" runat="server"><i class="fa fa-pen"></i></asp:LinkButton>
                        <p>
                            <asp:PlaceHolder ID="plcDomains" runat="server"></asp:PlaceHolder>
                        </p>

                    </div>

                    <p class="contact-item"><span>Created on:</span><span><asp:Literal ID="ltrCreatedon" runat="server"></asp:Literal></span></p>
                    <p class="contact-item"><span>Online branches</span><span><asp:Label ID="lblBranchesOnlineFlag" runat="server" CssClass="square-8 bg-warning mg-r-5 rounded-circle"></asp:Label>
                        <asp:Literal runat="server" ID="ltrOnlineBranches">0</asp:Literal></span></p>


                    <div class="progress mg-b-5">
                        <asp:Panel ID="pnlStoreConfigProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                            <asp:Literal ID="ltrStoreConfigProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
                    </div>
                    <p class="tx-12 mg-b-0">Business Types:
                        <asp:Literal ID="ltrBusinessTypes" runat="server"></asp:Literal></p>
                </div>

            </div><!-- card -->
          </div><!-- col-4 -->
          <div class="col-lg-4 mb-4">
              <div class="card h-100 p-3">
              <h6 class="slim-card-title tx-primary">Stores / Branches</h6>
            <div id="map1" style="min-height: 183px;"><asp:Image ID="imgMap" Visible="false" style="max-width:100%; text-align: center; padding-bottom: 14px;" runat="server" /></div>
              <h6>Stores registered: <asp:Literal ID="ltrTotalStores" runat="server"></asp:Literal>, Online: <asp:Literal ID="ltrOnlineStores" runat="server"></asp:Literal></h6>
                <div class="progress mg-b-5">
                    <asp:Panel ID="pnlPrimaryBranchProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"><asp:Literal ID="ltrPrimaryBranchProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
              </div>
              <p class="tx-12 mg-b-0">Store location and Pincode are mandatory</p>

          </div><!-- card -->
          </div><!-- col-4 -->
<div class="col-lg-4 mb-4">
    <div class="card h-100 p-3">
              <h6 class="slim-card-title tx-primary">Additional Settings</h6>

              <div class="row border-bottom">
                <div class="col">
                  <label class="tx-12 ">Order Pickers</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-person-stalker"></i>&nbsp;<asp:Literal ID="ltrOrderPickersCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="col tx-center">
                  <label class="tx-12">Online</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-person-stalker"></i>&nbsp;<asp:Literal ID="ltrOrderPickersOnlineCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12"></label>
                  <p style="margin-bottom: 10px;"><a href="/Tenant/OrderPicker" class="tx-12"><i class="icon ion-ios-personadd"></i> + Add</a></p>
                </div><!-- col -->
              </div><!-- row -->

<div class="row">
                <div class="col">
                  <label class="tx-12">Drivers</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-android-car"></i>&nbsp;<asp:Literal ID="ltrDriversCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="tx-center col">
                  <label class="tx-12">Online</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-android-car"></i>&nbsp;<asp:Literal ID="ltrDriversOnlineCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12"></label>
                  <p style="margin-bottom: 10px;"><a href="/Tenant/DeliveryStaffs" class="tx-12"><i class="icon ion-android-car"></i> + Add</a></p>
                </div><!-- col -->
              </div>
<p class="tx-12 mg-b-0 border-bottom " style="margin-bottom: 8px;">Make sure the app installed by order pickers and drivers</p>
<div class="row row-sm">
                <div class="col" style="
">
                  <label class="tx-12">Products</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-navicon-round"></i>&nbsp;<asp:Literal ID="ltrProductsCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="col tx-center">
                  <label class="tx-12 ">Out of Stock</label>
                  <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-navicon-round"></i>&nbsp;<asp:Literal ID="ltrOutofStockCount" runat="server">0</asp:Literal></p>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12"></label>
                  <p style="margin-bottom: 10px;"><a href="/Tenant/StockPrice" class="tx-12"><i class="icon ion-navicon-round"></i> + Manage</a></p>
                </div><!-- col -->
              </div>
<div class="progress mg-b-5">
            <asp:Panel ID="pnlAdditionalSettingsProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"><asp:Literal ID="ltrAdditionalSettingsProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
              </div>
              <p class="tx-12 mg-b-0">Ensure selected products for sale and added stock</p>
            </div>

            <%--<div class="card card-contact">
              <h6 class="slim-card-title tx-danger">Additional Settings</h6>
              <div class="row">
                <div class="col">
                  <label class="tx-12">Branches</label>
                  <p>1</p>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12">Users</label>
                  <p>1</p>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12">Products</label>
                  <p>0</p>
                </div><!-- col -->
              </div><!-- row -->

                          <h6 class="">Social Media Settings</h6>
            <p class="tx-12 mg-b-0" style="
    margin-bottom: 5px;
">Just select any of your available social account to get started.</p>
            
<br><div class="tx-20">
              <a href="" class="tx-primary mg-r-5"><i class="fa fa-facebook"></i></a>
              <a href="" class="tx-info mg-r-5"><i class="fa fa-twitter"></i></a>
              <a href="" class="tx-danger mg-r-5"><i class="fa fa-google-plus"></i></a>
              <a href="" class="tx-danger mg-r-5"><i class="fa fa-pinterest"></i></a>
              <a href="" class="tx-inverse mg-r-5"><i class="fa fa-github"></i></a>
              <a href="" class="tx-pink mg-r-5"><i class="fa fa-instagram"></i></a>
            </div>

<br><div class="progress mg-b-5">
                <div class="progress-bar bg-danger wd-35p" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">35%</div>
              </div>
              <p class="tx-12 mg-b-0">Maecenas tempus, tellus eget conditum rhon.</p>
            </div>--%><!-- card -->
          </div>
          <!-- col-4 -->
        



          <div class="col-lg-4 mb-4">
            <div class="card h-100 p-3">
              <h6 class="slim-card-title tx-primary"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> Linked</h6>
              <div class="card-experience media">
                  <div class="media-body ml-0">
                      <h5 class="mg-t-10 mg-b-5"><a href="/Tenant/store/GST" class="contact-name" style="color: inherit"><asp:Literal ID="ltrGSTINNum" runat="server"></asp:Literal>&nbsp;<%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN/s" : "VAT") %> Added</a></h5>
                    <p class="position-company"><a href="/Tenant/store/gst-Add">Add <%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %></a></p>
                    <p class="position-year"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTINs" : "VATs") %> not verified: <asp:Literal ID="ltrGSTINSNotVerified" runat="server"></asp:Literal></p>
                  </div><!-- media-body -->
                </div><!-- row -->
              <p class="contact-item"><span><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> not linked to store</span><span><asp:Literal ID="ltrGSTNotLinked" runat="server"></asp:Literal></span></p>

              <div class="progress mg-b-5">
                <asp:Panel ID="pnlGSTInfoProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><asp:Literal ID="ltrGSTInfoProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
              </div>
              <p class="tx-12 mg-b-0"><%=  (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1"? "GSTIN should be created first for adding new store" : "VAT linked will be getting preferrence when listed") %> </p>
            </div><!-- card -->
          </div><!-- col-4 -->
          <div class="col-lg-4 mb-4">
            <div class="card h-100 p-3">
              <h6 class="slim-card-title tx-primary">Bank Details</h6>
              <div class="card-experience media">
                  <div class="media-body ml-0">
                      <h5 class="mg-t-10 mg-b-5"><a class="contact-name" href="/Tenant/Store/BankAccount" style="color: inherit"><asp:Literal ID="ltrBankAccountNum" runat="server"></asp:Literal></a></h5>
                    <p class="position-company"><a href="/Tenant/Store/BankAccount-Add">Add Bank Account</a></p>
                    <p class="position-year">Accounts linked to store: <asp:Literal ID="ltrAccountsLinkedToStore" runat="server"></asp:Literal></p>
                  </div><!-- media-body -->
                </div><!-- row -->

              <p class="contact-item"><span>Stores with bank account linked</span><span><asp:Literal ID="ltrStoresWithBank" runat="server"></asp:Literal></span></p>
<div class="progress mg-b-5">
                <asp:Panel ID="pnlBankInfoProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><asp:Literal ID="ltrBankInfoProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
              </div>
              <p class="tx-12 mg-b-0">Bank account should be linked to store for payouts.</p>
            </div><!-- card -->
          </div><!-- col-4 -->
          <div class="col-lg-4 mb-4">
            <div class="card h-100 p-3">
              <h6 class="slim-card-title tx-primary">Appearance</h6>
              <div class="row">
                <div class="col">
                  <label class="tx-12">Own Banners</label>
                  <p style="margin-bottom: 14px;"><asp:Literal ID="ltrBanners" Text="0" runat="server"></asp:Literal></p>
<a href="/tenant/appearance/banner" class="tx-12"><i class="icon ion-image"></i> Manage</a>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12">Content Pages</label>
                  <p style="margin-bottom: 14px;"><asp:Literal ID="ltrContentPages" Text="0" runat="server"></asp:Literal></p>
<a href="/Navigations/ContentsPages" class="tx-12"><i class="icon ion-ios-information-outline"></i> Manage</a>
                </div><!-- col -->
                <div class="col">
                  <label class="tx-12">Theme</label>
                  <p style="margin-bottom: 14px;">Default</p>
<a href="/Tenant/Appearance/Themes" class="tx-12"><i class="icon ion-laptop"></i> Manage</a>
                </div><!-- col -->
              </div><!-- row -->

              <p class="contact-item"><span>Show default banners?</span><span>Yes</span></p>
<div class="progress mg-b-5">
    <asp:Panel ID="pnlAppearanceProgress" runat="server" CssClass="progress-bar bg-warning wd-35p" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"><asp:Literal ID="ltrAppearanceProgressVal" runat="server">30</asp:Literal>%</asp:Panel>
              </div>
              <p class="tx-12 mg-b-0">Manage appearance in the settings page.</p>
            </div><!-- card -->
          </div><!-- col-4 -->
    </div><!-- row -->


    <asp:PlaceHolder runat="server" Visible="false">
<asp:Literal ID="ltrStoreTitle" runat="server" Text="Store Info"></asp:Literal>
              
    <asp:Literal ID="ltrPAN" runat="server"></asp:Literal>
    

    </asp:PlaceHolder>




                        </asp:PlaceHolder>
    <uc1:ctrlCreateStore runat="server" Visible="false" id="ctrlCreateStore1" />
    

        <script>
            $(document).ready(function () {
                $('.select2').select2();

                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();

            });

</script>

</asp:Content>