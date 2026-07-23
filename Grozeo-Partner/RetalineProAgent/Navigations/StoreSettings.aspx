<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="StoreSettings.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.StoreSettings" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlInventorySetup.ascx" TagPrefix="uc1" TagName="ctrlInventorySetup" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlCreateStore.ascx" TagPrefix="uc1" TagName="ctrlCreateStore" %>
<%@ Register Src="~/Controls/PopupUpgradeConsent.ascx" TagPrefix="uc1" TagName="PopupUpgradeConsent" %>


<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settings</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    
    <li class="breadcrumb-item">
         <a href="#" class="btn btn-primary px-3 py-2 mr-2" title="" runat="server" visible="false">
            <i class="fa-regular fa-download"></i>
            <span class="ml-2 d-none d-lg-inline-block">Delivery App</span>
        </a>
        <a href="#" class="btn btn-primary px-3 py-2 mr-2" title="" runat="server" visible="false">
            <i class="fa-regular fa-download"></i>
            <span class="ml-2 d-none d-lg-inline-block">Packsure App</span>
        </a>
        <a class="btn btn-outline-primary  px-3 py-2" href="/">
            <i class="fa fa-reply" aria-hidden="true"></i>
            <span class="ml-2 d-none d-lg-inline-block">Back</span>
        </a>
    </li>
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
            <h6 class="slim-pagetitle">Settings and Configurations</h6>
    </asp:PlaceHolder>
    
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
<uc1:PopupUpgradeConsent runat="server" ID="PopupUpgradeConsent1" UpgradeName="Add Store Consent" />
<asp:PlaceHolder ID="plcConf" runat="server">

    <div class="row row-sm menucard ">
        <div class="col-md mb-3">
                        <a href="/Navigations/Appearance" class="card h-100 py-3 px-2">
                            <div class="card-body p-0 position-relative">
                                <div class="d-flex align-items-center mb-3">
                                 <i class="fa-thin fa-frame mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35">Appearance</h5>
                                </div>
                                
                                <div class="col-12 p-0 mb-2 d-flex align-items-center">
                                    <span class="d-flex justify-content-center align-items-center wd-30 ht-30 tx-white bg-dark rounded-circle mr-2 lh-normal"><asp:Literal ID="ltrBanners" Text="0" runat="server"></asp:Literal></span>
                                    <span class="tx-13">Banners</span>
                                </div>
                                <div class="col-12 p-0 d-flex align-items-center">
                                    <span class="d-flex justify-content-center align-items-center wd-32 ht-32 tx-white bg-dark rounded-circle mr-2 lh-normal"><asp:Literal ID="ltrContentPages" Text="0" runat="server"></asp:Literal></span>
                                    <span class="tx-13">Content</span>
                                </div>
                                
                                
                            </div>
                        </a>
                    </div>
                    <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                        { %>
                    <div class="col-sm mb-3" runat="server" visible="false">
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

        <%--<% if (!Page.User.IsInRole("BranchManager"))
            { %>
        <div class="col-sm mb-3">
            <a href="/Tenant/Branches" class="card h-100 py-3 px-2">
                <div class="card-body p-0 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fa-thin fa-shop mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35">Manage Stores</h5>
                    </div>
                    <div class="w-100">
                        <p class="card-text mg-b-8 tx-13">Effortlessly create and manage stores for smooth operations.</p>
                    </div>
                </div>
            </a>
        </div>
        <% } %>--%>
                    <div class="col-sm mb-3">
                        <a href="/Navigations/Delivery" class="card h-100 py-3 px-2">
                            <div class="card-body p-0 position-relative">
                                <div class="d-flex align-items-center mb-3">
                                <i class="fa-thin fa-truck-bolt mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                                <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35">Packing & Delivery</h5>
                                </div>
                                <div class="w-100">
                                <p class="card-text mg-b-8 tx-13">Seamlessly manage Package types, delivery rules and slots for order fulfillment.</p>
                                </div>
                                
                            </div>
                        </a>
                    </div>

                    <% if (!Page.User.IsInRole("BranchManager") && !Page.User.IsInRole("StoreManager"))
                        { %>

                    <div class="col-sm mb-3" runat="server" visible="false">
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

        <%--<div class="col-md mb-3">
            <a href="/Navigations/crm" class="card h-100 py-3 px-2">
                <div class="card-body p-0 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fa-thin fa-user-headset mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35">Customer Relation</h5>
                    </div>
                    <div class="w-100">
                        <p class="card-text mg-b-8 tx-13">Boost customer relationships with effective lead management and campaigns.</p>
                    </div>
                </div>
            </a>
        </div>--%>  

        <div class="col-md mb-3">
            <a href="/Tenant/PaymentConfig" class="card h-100 py-3 px-2">
                <div class="card-body p-0 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fa-thin fa-bank mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35 ">Payment Gateway</h5>
                    </div>
                    <div class="w-100">
                        <p class="card-text mg-b-8 tx-13">Connect or set up your payment gateway to start accepting online payments.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm mb-3">
            <a href="/Tenant/MarketingTools" class="card h-100 py-3 px-2">
                <div class="card-body p-0 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fa-thin fa-truck-bolt mr-2 tx-18 tx-primary wd-32 ht-32 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mb-0 tx-14 tx-gray-800 d-flex align-items-center ht-35">Marketing Tools</h5>
                    </div>
                    <div class="w-100">
                        <p class="card-text mg-b-8 tx-13">Effectively plan, execute, and track your marketing campaigns to boost visibility and drive growth.</p>
                    </div>
                </div>
            </a>
        </div>

                    <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                        { %>
                    <div class="col-sm mb-3" runat="server" visible="false">
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
                <div class="row row-sm multicard_view">
                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card h-100 p-0">
                            <div class="card-body shadow_top">
                                <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                                    <div class="d-block pr-2">
                                        <h5 class="card-title tx-medium mb-1 tx-14 tx-gray-800">Stores</h5>
                                        <p class="m-0 tx-11">Manage the store details here</p>
                                    </div>
                                    <div class="d-flex align-items-center storeinfstg">
                                        <div class="str_groupid mr-2 tx-14 tx-dark fw-600"><%= String.Format("{0}{1}{2}", ConfigurationManager.AppSettings.Get("CountryCode"), CurrentUser.CreatedOn.ToString("MMyy"), CurrentUser.APIStoreId.ToString("0000"))%></div>
                                        <a href="/Tenant/ManageBusinessSettings" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage Stores">
                                            <i class="fa-regular fa-gear"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="settinginfo py-3 px-2">
                                    <div class="row row-sm">
                                        <div class="col border-dotted">
                                            <%--<span class="count tx-20 tx-dark tx-semibold lh-0">1</span>--%>
                                            <strong class="count tx-20 tx-dark tx-semibold lh-0"><asp:Literal ID="ltrTotalStores" runat="server"></asp:Literal></strong>
                                            <p class="m-0 lh-1">Registered</p>
                                        </div>
                                        <div class="col border-dotted">
                                            <%--<span class="count tx-20 tx-dark tx-semibold lh-0">0</span>--%>
                                            <strong class="count tx-20 tx-dark tx-semibold lh-0"><asp:Literal ID="ltrOnlineStores" runat="server"></asp:Literal></strong>
                                            <p class="m-0 lh-1">Online</p>

                                            <% if (this.CurrentUser.TenantType != 1)
                                                {  %>
                                , Type: <span class="alert-danger"><% = RetalineProAgent.Service.Common.TenantTypeText(this.CurrentUser.TenantType) %></span>
                                            <% } %>
                                        </div>
                                        <div class="col-12 mt-4 text-center">
                                            <%--<span>Sponsored Products: <strong class="tx-dark">Enabled</strong></span>--%>
                                            <p class="m-0 tx-12 tx-dark">Sponsored Products:<strong class="tx-dark ml-1"><asp:Literal ID="ltrSponsoredPrd" Text="0" runat="server"></asp:Literal></strong>
                                             <asp:LinkButton ID="PageShow" runat="server" CssClass="btn btn-outline-primary ml-2 p-1 lh-1" OnClick="PageShow_Click" Text="Change"></asp:LinkButton>
                                         </p>
                                            <%--<a href="#" class="btn btn-outline-primary ml-2 p-1 lh-1">Change</a>--%>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card h-100 p-0">
                            <div class="card-body shadow_top">
                                <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                                    <div class="d-block pr-2">
                                        <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">Resources</h5>
                                        <p class="m-0 tx-11">Add or edit the resources here</p>
                                    </div>
                                    <a href="/Tenant/Store/Users" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage Resources">
                      <i class="fa-regular fa-gear "></i>
                    </a>
                                </div>
                                <div class="settinginfo py-3 px-2">

                                    <div class="row row-sm pb-3 flex-nowrap">
                                        <div class="border-dotted d-flex align-items-center w-100">
                                            <span class="count tx-13 tx-dark tx-semibold lh-1">Order Picker</span>
                                        </div>
                                        <div class="border-dotted" style="width: 75px;">
                                            <asp:Literal ID="ltrOrderPickersCount" runat="server">0</asp:Literal>
                                            <p class="m-0 lh-1">Created</p>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-inline-block">
                                                <asp:Literal ID="ltrOrderPickersOnlineCount" runat="server">0</asp:Literal>
                                                <p class="m-0 lh-1">Online</p>
                                            </div>
                                            <a class="wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="View" href="/Tenant/OrderPicker">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <!--row-->

                                    <div class="row row-sm pt-3 border-top flex-nowrap">
                                        <div class="border-dotted d-flex align-items-center w-100">
                                            <span class="count tx-13 tx-dark tx-semibold lh-1">Delivery Staff</span>
                                        </div>
                                        <div class="border-dotted" style="width: 75px;">
                                            <asp:Literal ID="ltrDriversCount" runat="server">0</asp:Literal>
                                            <p class="m-0 lh-1">Created</p>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-inline-block">
                                                <asp:Literal ID="ltrDriversOnlineCount" runat="server">0</asp:Literal>
                                                <p class="m-0 lh-1">Online</p>
                                            </div>
                                            <a class="wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="View" href="/Tenant/DeliveryStaffs">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div><!--col-6-->



             <div class="col-12 col-sm-6 col-lg-4 mb-3">
              <div class="card h-100 p-0">
                <div class="card-body shadow_top">
                  <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                    <div class="d-block pr-2">
                      <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 "><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> Linked</h5>
                      <p class="m-0 tx-11"><%=  (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1"? "Add more GST and map to store" : "VAT linked will be getting preferrence when listed") %></p>
                    </div>
                    <a href="/Tenant/Store/GST" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage GSTIN">
                      <i class="fa-regular fa-gear "></i>
                    </a>
                  </div><!--settinghead-->
                  <div class="settinginfo py-3 px-2">
                    <div class="row row-sm">
                      <div class="col-12 mb-4 d-flex align-items-center">
                        <span><strong class="tx-dark"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN/s" : "VAT") %> Added</strong></span>
                       <span class="d-flex justify-content-center align-items-center wd-30 ht-30 tx-white bg-dark rounded-circle ml-2 lh-normal"><asp:Literal ID="ltrGSTINNum" runat="server">
                           </asp:Literal></span>
                      </div>
                      <div class="col border-dotted">
                        <span class="count tx-20 tx-dark tx-semibold lh-0"> 
                                        <asp:Literal ID="ltrGSTINSNotVerified" runat="server"></asp:Literal></span>
                        <p class="m-0 lh-1"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTINs" : "VATs") %> not verified</p>
                      </div>
                      <div class="col border-dotted">
                        <span class="count tx-20 tx-dark tx-semibold lh-0"><asp:Literal ID="ltrGSTNotLinked" runat="server"></asp:Literal></span>
                        <p class="m-0 lh-1"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %> not linked to store</p>
                      </div>
                    </div>
                  </div>
                </div><!--card-body-->
                
              </div>
            </div><!--col-lg-4-->

                    <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                        {  %>
                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card h-100 p-0">
                            <div class="card-body shadow_top">
                                <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                                    <div class="d-block pr-2">
                                        <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">Pan Details</h5>
                                        <p class="m-0 tx-11">Manage your PAN details here</p>
                                    </div>
                                    <a href="#" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage Pan">
                                        <i class="fa-regular fa-gear "></i>
                                    </a>
                                </div>
                                <!--settinghead-->
                                <div class="settinginfo py-3 px-2">
                                    <div class="row row-sm">
                                        <div class="col-12 mb-4 d-flex align-items-center">
                                            <span><strong class="tx-dark">Pan Accounts</strong></span>
                                            <span class="d-flex justify-content-center align-items-center wd-30 ht-30 tx-white bg-dark rounded-circle ml-2 lh-normal">0</span>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">0</span>
                                            <p class="m-0 lh-1">Pan details linked to store</p>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">0</span>
                                            <p class="m-0 lh-1">Stores with PAN Account linked</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--card-body-->

                        </div>
                    </div>
                    <!--col-lg-4-->


                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card h-100 p-0">
                            <div class="card-body shadow_top">
                                <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                                    <div class="d-block pr-2">
                                        <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">FSSAI Details</h5>
                                        <p class="m-0 tx-11">Add, Edit or Map FSSAI here</p>
                                    </div>
                                    <a href="/Tenant/Store/FSSAI" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage FSSAI">
                                        <i class="fa-regular fa-gear "></i>
                                    </a>
                                </div>
                                <!--settinghead-->
                                <div class="settinginfo py-3 px-2">
                                    <div class="row row-sm">
                                        <div class="col-12 mb-4 d-flex align-items-center">
                                            <span><strong class="tx-dark">FSSAI Registrations</strong></span>
                                            <span class="d-flex justify-content-center align-items-center wd-30 ht-30 tx-white bg-dark rounded-circle ml-2 lh-normal">
                                                <asp:Literal ID="ltrFSSAICount" runat="server"></asp:Literal></span>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">
                                                <asp:Literal ID="ltrFssaiLinkedToStore" runat="server"></asp:Literal></span>
                                            <p class="m-0 lh-1">FSSAI details linked to store</p>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">
                                                <asp:Literal ID="ltrFssaiNotLinked" runat="server"></asp:Literal></span>
                                            <p class="m-0 lh-1">Stores without FSSAI account linked</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--card-body-->
                        </div>
                    </div>
                    <!--col-lg-4-->

                    <% } %>

                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card h-100 p-0">
                            <div class="card-body shadow_top">
                                <div class="d-flex justify-content-between align-items-center settinghead px-2 pb-2 pt-3 border-bottom">
                                    <div class="d-block pr-2">
                                        <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">Bank Details</h5>
                                        <p class="m-0 tx-11">Add and Manage Bank details</p>
                                    </div>
                                    <a href="/Tenant/Store/BankAccount" class="tx-12 wd-25 ht-25 d-flex justify-content-center align-items-center rounded-circle settingicon" title="Manage Bank">
                                        <i class="fa-regular fa-gear "></i>
                                    </a>
                                </div>
                                <!--settinghead-->
                                <div class="settinginfo py-3 px-2">
                                    <div class="row row-sm">
                                        <div class="col-12 mb-4 d-flex align-items-center">
                                            <span><strong class="tx-dark">Bank Accounts</strong></span>
                                            <span class="d-flex justify-content-center align-items-center wd-30 ht-30 tx-white bg-dark rounded-circle ml-2 lh-normal">
                                                <asp:Literal ID="ltrBankAccountNum" runat="server"></asp:Literal></span>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">
                                                <asp:Literal ID="ltrAccountsLinkedToStore" runat="server"></asp:Literal></span>
                                            <p class="m-0 lh-1">Bank details linked to store</p>
                                        </div>
                                        <div class="col border-dotted">
                                            <span class="count tx-20 tx-dark tx-semibold lh-0">
                                                <asp:Literal ID="ltrStoresWithBank" runat="server"></asp:Literal></span>
                                            <p class="m-0 lh-1">Stores with bank account linked</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--card-body-->

                        </div>
                    </div>
                    <!--col-lg-4-->
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

          
    <%--</div><!-- row -->--%>


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