<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="StoreConfigSett.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.StoreConfigSett" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlInventorySetup.ascx" TagPrefix="uc1" TagName="ctrlInventorySetup" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlCreateStore.ascx" TagPrefix="uc1" TagName="ctrlCreateStore" %>
<%@ Register Src="~/Controls/PopupUpgradeConsent.ascx" TagPrefix="uc1" TagName="PopupUpgradeConsent" %>


<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settings</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
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
            <h6 class="slim-pagetitle">Settings and Configurations</h6>
        </div>
    </asp:PlaceHolder>
    
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
<uc1:PopupUpgradeConsent runat="server" ID="PopupUpgradeConsent1" UpgradeName="Add Store Consent" />
<asp:PlaceHolder ID="plcConf" runat="server">

        <div class="row row-sm multicard_view">

            <div class="col-12 col-lg-8">
                <div class="row row-sm multicard_view">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <h6 class="slim-card-title tx-primary mb-0">Stores</h6>
                              <a href="/Tenant/ManageBusinessInfo" class="tx-12" title="Manage Stores"><i class="fa-regular fa-gear mr-1"></i></a>
                            </div>
                            <div id="map1" style="height: 175px; overflow: hidden; margin-bottom: 10px;">
                                <asp:Image ID="imgMap" Visible="false" Style="max-width: 100%; text-align: center;" runat="server" /></div>

                            <label class="tx-12 tx-bold">Stores registered: 
                                <asp:Literal ID="ltrTotalStores" runat="server"></asp:Literal>, Online:
                                <asp:Literal ID="ltrOnlineStores" runat="server"></asp:Literal>
                                <% if (this.CurrentUser.TenantType != 1)
                                    {  %>
                                , Type: <span class="alert-danger"><% = RetalineProAgent.Service.Common.TenantTypeText(this.CurrentUser.TenantType) %></span>
                                <% } %>
                            </label>
                            <%--<h6>Stores registered:
                                <asp:Literal ID="ltrTotalStores" runat="server"></asp:Literal>, Online:
                                <asp:Literal ID="ltrOnlineStores" runat="server"></asp:Literal></h6>--%>
                            <div class="progress mg-b-5">
                                <asp:Panel ID="pnlPrimaryBranchProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    <asp:Literal ID="ltrPrimaryBranchProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
                            </div>
                            <p class="tx-12 mg-b-0">Store location and Pincode are mandatory</p>

                        </div>
                    </div><!--col-6-->

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 p-3">
                            <h6 class="slim-card-title tx-primary">Resources</h6>

                            <div class="row row-sm border-bottom pb-2 mb-2">
                                <div class="col-12">
                                    <label class="tx-11 tx-bold"><i class="icon ion-person-stalker mr-1"></i>&nbsp;Order Picker</label>
                                    <a class="tx-11 float-right" href="#"><i class="fa-regular fa-arrow-down-to-bracket mr-1"></i>Packsure App</a>
                                    </div>
                                    <div class="col d-flex align-items-center">
                                        <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Created: <asp:Literal ID="ltrOrderPickersCount" runat="server">0</asp:Literal></p>
                                    </div><!-- col -->
                                <div class="col d-flex align-items-center">
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Online: <asp:Literal ID="ltrOrderPickersOnlineCount" runat="server">0</asp:Literal></p>
                                </div>
                                <!-- col -->
                                <div class="col">
                        <p class="mb-1 text-right tx-primary"><a href="/Tenant/OrderPicker" class="tx-12 py-0 btn btn-sm btn-outline-secondary">Manage</a></p>
                      </div>
                                <!-- col -->
                            </div>
                            <!-- row -->

                            <div class="row row-sm border-bottom pb-2 mb-2">
                                <div class="col-12">
                                    <label class="tx-11 tx-bold"><i class="fa-regular fa-truck mr-1"></i>&nbsp;Delivery Staff</label>
                                    <a class="tx-11 float-right" href="#"><i class="fa-regular fa-arrow-down-to-bracket mr-1"></i>Delivery App</a>
                                    </div><!-- col -->
                                <div class="col d-flex align-items-center">
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Created: <asp:Literal ID="ltrDriversCount" runat="server">0</asp:Literal></p>
                                </div><!-- col -->
                                <div class="col d-flex align-items-center">
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Online: <asp:Literal ID="ltrDriversOnlineCount" runat="server">0</asp:Literal></p>
                                </div><!-- col -->
                                <div class="col">
                                    <p class="mb-1 text-right tx-primary"><a href="/Tenant/DeliveryStaffs" class="tx-12 py-0 btn btn-sm btn-outline-secondary">Manage</a></p>
                                </div>
                                <!-- col -->
                            </div><!-- row -->

                            <div class="row row-sm mg-b-10">
                                <div class="col-12">
                                    <label class="tx-11 tx-bold"><i class="fa-regular fa-store mr-1"></i>&nbsp;Store User</label>
                                </div><!-- col -->
                                <div class="col d-flex align-items-center">
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Created: <asp:Literal ID="ltrUserCount" runat="server">0</asp:Literal></p>
                                </div><!-- col -->
                                <%--<div class="col d-flex align-items-center">
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;">Online: <asp:Literal ID="ltrUserOnlineCount" runat="server">0</asp:Literal></p>
                                </div>--%><!-- col -->
                                <div class="col">
                                    <p class="mb-1 text-right tx-primary"><a href="/Tenant/Store/Users" class="tx-12 py-0 btn btn-sm btn-outline-secondary">Manage</a></p>
                                </div>
                                <!-- col -->
                            </div><!-- row -->

                            <%--<p class="tx-12 mg-b-0 border-bottom " style="margin-bottom: 8px;">Make sure the app installed by order pickers and drivers</p>--%>
                            <%--<div class="row row-sm">
                                <div class="col" style="">
                                    <label class="tx-12">Products</label>
                                    <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-navicon-round"></i>&nbsp;<asp:Literal ID="ltrProductsCount" runat="server">0</asp:Literal></p>
                                </div>
                                <!-- col -->
                                <div class="col tx-center">
                                    <label class="tx-12 ">Out of Stock</label>
                                    <p class="tx-primary" style="margin-bottom: 10px;"><i class="icon ion-navicon-round"></i>&nbsp;<asp:Literal ID="ltrOutofStockCount" runat="server">0</asp:Literal></p>
                                </div>
                                <!-- col -->
                                <div class="col">
                                    <label class="tx-12"></label>
                                    <p style="margin-bottom: 10px;"><a href="/Tenant/ItemsForSale" class="tx-12"><i class="icon ion-navicon-round"></i>+ Manage</a></p>
                                </div>
                                <!-- col -->
                            </div>--%>
                            <div class="progress mt-3 mb-2">
                                <asp:Panel ID="pnlAdditionalSettingsProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                                    <asp:Literal ID="ltrAdditionalSettingsProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
                            </div>
                            <p class="tx-12 mg-b-0">Validate added products and stock</p>
                        </div>
                    </div><!--col-6-->

                    <%--<div class="col-lg-6 mb-4">
                        <div class="card h-100 p-3">FSSAI Details</div>
                    </div>--%>

                    <div class="col-lg-6 mb-4">
                  <div class="card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="slim-card-title tx-primary mb-0">FSSAI Details</h6>
                      <a href="/Tenant/Store/FSSAI" class="tx-12" title="Manage FSSAI"><i class="fa-regular fa-gear mr-1"></i></a>
                    </div>
                    <div class="card-experience media">
                      <div class="media-body ml-0">
                        <h5 class="slim-card-title mb-2"><a class="contact-name"><asp:Literal ID="ltrFSSAICount" runat="server"></asp:Literal> FSSAI Registrations</a></h5>
                        <p class=" tx-12"><a href="/Tenant/Store/FSSAI-Add"><i class="fa-solid fa-plus mr-1"></i>Add FSSAI Registrations</a></p>
                        <%--<p class="m-0 tx-12 tx-dark">Accounts linked to store: 1</p>--%>
                          <p class="m-0 tx-12 mb-3 tx-dark"><span>&nbsp;</span></p>
                          <p class="m-0 tx-12 tx-dark">FSSAI Accounts linked to store: <asp:Literal ID="ltrFssaiLinkedToStore" runat="server"></asp:Literal></p>
                      </div><!-- media-body -->
                    </div><!-- row -->
                  
                    <%--<p class="m-0 tx-12 mb-3 tx-dark"><span>Stores with bank account linked</span><span class="ml-2">5</span></p>--%>
                      <asp:PlaceHolder runat="server" Visible="false">
                      <p class="m-0 tx-12 mb-4 tx-dark"><span>Stores without FSSAI account linked</span><span class="ml-2"><asp:Literal ID="ltrFssaiNotLinked" runat="server"></asp:Literal></span></p>
                      </asp:PlaceHolder>
                    <%--<div class="progress mg-b-5">
                      <div id="pnlFssaiInfoProgress" class="progress-bar bg-success wd-100p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                        100%
                      </div>
                    </div>--%>
                      <div class="progress mg-b-5">
                          <asp:Panel ID="pnlFssaiInfoProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                              <asp:Literal ID="ltrFssaiInfoProgressVal" runat="server">0</asp:Literal>%
                          </asp:Panel>
                      </div>
                    <p class="tx-12 mg-b-0">FSSAI Details should be linked to store</p>
                  </div>
                </div>

                     <div class="col-lg-6 mb-4">
                         <div class="card h-100 p-3">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                 <h6 class="slim-card-title tx-primary mb-0">Appearance</h6>
                             <a href="/Navigations/Appearance" class="tx-12" title="Manage Appearance"><i class="fa-regular fa-gear mr-1"></i></a>
                             </div>
                             <div class="row row-sm">
                                 <div class="col border-right">
                                     <label class="tx-11 tx-bold">Banners</label>
                                     <p class="m-0 tx-12 tx-primary">
                                         <asp:Literal ID="ltrBanners" Text="0" runat="server"></asp:Literal></p>
                                     <a href="/tenant/appearance/banner" class="tx-11"><i class="icon ion-image mr-1"></i>Manage</a>
                                 </div>
                                 <!-- col -->
                                 <div class="col border-right">
                                     <label class="tx-11 tx-bold">Content</label>
                                     <p class="m-0 tx-12 tx-primary">
                                         <asp:Literal ID="ltrContentPages" Text="0" runat="server"></asp:Literal></p>
                                     <a href="/Navigations/ContentsPages" class="tx-11"><i class="icon ion-ios-information-outline mr-1"></i>Manage</a>
                                 </div>
                                 <!-- col -->
                                 <div class="col">
                                     <label class="tx-11 tx-bold">Theme</label>
                                     <p class="m-0 tx-12 tx-primary">Default</p>
                                     <a href="/Tenant/Appearance/Themes" class="tx-11"><i class="icon ion-laptop mr-1"></i>Manage</a>
                                 </div><!-- col -->


                                 
                                 <div class="col-12 mt-3 mb-3">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <p class="m-0 tx-12 tx-dark">Sponsored Products:<strong class="ml-2"><asp:Literal ID="ltrSponsoredPrd" Text="0" runat="server"></asp:Literal></strong>
                                             <asp:LinkButton ID="PageShow" runat="server" OnClick="PageShow_Click" Text="Change"></asp:LinkButton>
                                         </p>
                                     </div>
                                 </div>
                                 
                                 
                                 
            
                             </div><!-- row -->

                             <%--<p class="contact-item"><span>Show default banners?</span><span>Yes</span></p>--%>
                             
                             <div class="progress mg-b-5">
                                 <asp:Panel ID="pnlAppearanceProgress" runat="server" CssClass="progress-bar bg-success wd-100p" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                                     <asp:Literal ID="ltrAppearanceProgressVal" runat="server">30</asp:Literal>%</asp:Panel>
                             </div>
                                 <p class="tx-12 mg-b-0">Manage appearance in the settings</p>

                         </div><!-- card -->
                    </div><!-- col-6 -->

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="slim-card-title tx-primary mb-0"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> Linked</h6>
                                <a href="/Tenant/Store/GST" class="tx-12" title="Manage GSTIN"><i class="fa-regular fa-gear mr-1"></i></a>
                            </div>
                            
                            
                            <div class="card-experience media">
                                <div class="media-body ml-0">
                                    <h5 class="slim-card-title mb-2"><a class="contact-name" style="color: inherit">
                                        <asp:Literal ID="ltrGSTINNum" runat="server"></asp:Literal>&nbsp;<%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN/s" : "VAT") %> Added</a></h5>
                                    <p class=" tx-12"><i class="fa-solid fa-plus mr-1"></i><a href="/Tenant/store/gst-Add">Add <%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %></a></p>
                                    <p class="m-0 tx-12 tx-dark"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTINs" : "VATs") %> not verified:
                                        <asp:Literal ID="ltrGSTINSNotVerified" runat="server"></asp:Literal></p>
                                </div>
                                <!-- media-body -->
                            </div>
                            <!-- row -->
                            <p class="m-0 tx-12 mb-4 tx-dark"><span><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> not linked to store</span><span class="ml-2"><asp:Literal ID="ltrGSTNotLinked" runat="server"></asp:Literal></span></p>

                            <div class="progress mg-b-5">
                                <asp:Panel ID="pnlGSTInfoProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                    <asp:Literal ID="ltrGSTInfoProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
                            </div>
                            <p class="tx-12 mg-b-0"><%=  (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1"? "GSTIN should be created first for adding" : "VAT linked will be getting preferrence when listed") %> </p>
                        </div>
                        <!-- card -->
                    </div>
                    <!-- col-6 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="slim-card-title tx-primary mb-0">Bank Details</h6>
                            <a href="/Tenant/Store/BankAccount" class="tx-12" title="Manage Bank"><i class="fa-regular fa-gear mr-1"></i></a>
                            </div>
                            
                            <div class="card-experience media">
                                <div class="media-body ml-0">
                                    <h5 class="slim-card-title mb-2"><a class="tx-dark" style="color: inherit">
                                        <asp:Literal ID="ltrBankAccountNum" runat="server"></asp:Literal></a></h5>
                                    
                                    <p class=" tx-12"><a href="/Tenant/Store/BankAccount-Add"><i class="fa-solid fa-plus mr-1"></i>Add Bank Account</a></p>
                                    <p class="m-0 tx-12 tx-dark">Accounts linked to store:
                                        <asp:Literal ID="ltrAccountsLinkedToStore" runat="server"></asp:Literal></p>
                                </div>
                                <!-- media-body -->
                            </div>
                            <!-- row -->

                            <p class="m-0 tx-12 mb-4 tx-dark"><span>Stores with bank account linked</span><span><asp:Literal ID="ltrStoresWithBank" runat="server"></asp:Literal></span></p>
                            <div class="progress mg-b-5">
                                <asp:Panel ID="pnlBankInfoProgress" runat="server" CssClass="progress-bar bg-danger wd-0p" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                    <asp:Literal ID="ltrBankInfoProgressVal" runat="server">0</asp:Literal>%</asp:Panel>
                            </div>
                            <p class="tx-12 mg-b-0">Bank account should be linked to store</p>
                        </div>
                        <!-- card -->
                    </div>
                    <!-- col-6 -->

                </div>
            </div><!--col-lg-8-->

            <div class="col-12 col-lg-4">
                <div class="row row-sm menucard">
                    <div class="col-12 mb-4">
                        <a href="/Navigations/Products" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-cart-flatbed-boxes mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800">Manage Products</h5>
                                <div class="row row-sm">
                                    <div class="col d-flex flex-wrap align-items-center border-right" style=""><label class="tx-12 mb-0 w-100 tx-bold">Total Products</label>
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;"><asp:Literal ID="ltrProductsCount" runat="server">0</asp:Literal></p></div>
                                <div class="col d-flex flex-wrap align-items-center"><label class="tx-12 mb-0 w-100 tx-bold">Out of Stock</label>
                                    <p class="tx-12 m-0 tx-primary" style="margin-bottom: 10px;"><asp:Literal ID="ltrOutofStockCount" runat="server">0</asp:Literal></p></div>
                                </div>
                                
                                
                            </div>
                        </a>
                    </div>
                    <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                        { %>
                    <div class="col-12 mb-4" runat="server" visible="false">
                        <a href="/Navigations/BusinessSettings" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-business-time mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Business Settings</h5>
                                <p class="card-text mg-b-8 tx-11">Customize business ops: accounts, GST, bank accounts, retail categories.</p>
                            </div>
                        </a>
                    </div>
                    <!--col-lg-->
                    <% } %>

                    <% if (!Page.User.IsInRole("BranchManager"))
                        { %>
                    <div class="col-12 mb-4">
                        <a href="/Tenant/Branches" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-shop mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800  ">Manage Stores</h5>
                                <p class="card-text mg-b-8 tx-11">Effortlessly create and manage branches for smooth operations.</p>
                            </div>
                        </a>
                    </div>
                    <!--col-lg-->


                    <% } %>

                    <div class="col-12 mb-4">
                        <a href="/Navigations/Delivery" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-truck-bolt mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800">Delivery</h5>
                                <p class="card-text mg-b-8 tx-11">Seamlessly manage delivery rules and slots for order fulfillment.</p>
                            </div>
                        </a>
                    </div>
                    <!--col-lg-->

                    <% if (!Page.User.IsInRole("BranchManager") && !Page.User.IsInRole("StoreManager"))
                        { %>

                    <div class="col-12 mb-4" runat="server" visible="false">
                        <a href="/Navigations/Appearance" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-laptop mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800 ">Appearance</h5>
                                <p class="card-text mg-b-8 tx-11">Personalize website identity with custom logos, banners, and themes.</p>
                            </div>
                        </a>
                    </div>
                    <!--col-lg-->
                    <% } %>

                    <div class="col-12 mb-4">
                        <a href="/Navigations/crm" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-user-headset mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800 ">Customer Relation</h5>
                                <p class="card-text mg-b-8 tx-11">Boost customer relationships with effective lead management and campaigns.</p>
                            </div>
                        </a>
                    </div>
                    <!--col-lg-->

                    <div class="col-12 mb-4">
            <a href="/Tenant/ManageBusinessType" class="card h-100 p-3">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-square-list mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800 ">Retail Category Section</h5>
                <p class="card-text mg-b-8 tx-11">Broaden horizons with new retail categories for diverse products.</p>
              </div>
            </a>
          </div><!--col-lg-->

                    <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                        { %>
                    <div class="col-12 mb-4" runat="server" visible="false">
                        <a href="/Navigations/Users" class="card h-100 p-3">
                            <div class="card-body p-0 tx-left position-relative">
                                <i class="fa-thin fa-users mb-2 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-1 tx-15 tx-gray-800 ">Users</h5>
                                <p class="card-text mg-b-8 tx-11">Enable efficient collaboration through user account management.</p>
                            </div>
                        </a>
                    </div><!--col-lg-->

                    <% } %>
                </div>
            </div>


          <div class="col-lg-4 mb-4" runat="server" visible="false">
            <div class="card h-100 p-3">
                <div class="card-contact border-0 p-0">
                    <h6 class="slim-card-title tx-primary">Business Settings</h6>

                    <div class="tx-center">
                        <a id="hlPublicSite" target="_blank" runat="server" href="#">
                            <img src="/Content/images/userImage.png" onerror="this.src='/content/images/face.png'" runat="server" id="imgStore" class="card-img" alt="" width="30" style="max-height: 58px; width: auto; max-width: 100%;"></a>
                        <h5 class="mg-t-10 mg-b-5"><a href="#" class="contact-name">
                            <asp:Literal ID="ltrPageTitle" runat="server" Text="Create Store"></asp:Literal></a>
                            
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

            </div>
          </div><!-- col-4 -->

          
    </div><!-- row -->


    <asp:PlaceHolder runat="server" Visible="false">
<asp:Literal ID="ltrStoreTitle" runat="server" Text="Store Info"></asp:Literal>
              
    <asp:Literal ID="ltrPAN" runat="server"></asp:Literal>
    

    </asp:PlaceHolder>




                        </asp:PlaceHolder>
    <uc1:ctrlCreateStore runat="server" Visible="false" id="ctrlCreateStore1" />
    

        <%--<script>
            $(document).ready(function () {
                $('.select2').select2();

                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();

            });

        </script>--%>
</asp:Content>