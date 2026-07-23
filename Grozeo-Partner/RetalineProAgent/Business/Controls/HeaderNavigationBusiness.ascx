<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderNavigationBusiness.ascx.cs" Inherits="RetalineProAgent.Business.Controls.HeaderNavigationBusiness" %>
<ul class="nav nav-sidebar">
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link border-top-0 <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-home") %>" href="/Business/Default">
            <i class="icon ion-ios-speedometer-outline"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <%--<li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-contact") %>" href="/Business/Contacts">
            <i class="fa-thin fa-address-book"></i>
            <span>Contacts</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-leads") %>" href="/Business/AssociateLeads">
            <i class="fa-thin fa-bar-chart"></i>
            <span>Leads</span>
        </a>
    </li>
        
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-prospects") %>" href="/Business/Prospects">
            <i class="fa-thin fa-shopping-cart"></i>
            <span>Prospects</span>
        </a>
    </li>--%>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-contact") %>" href="/Business/ClientManagement?type=contact">
            <i class="fa-thin fa-address-book"></i>
            <span>Contacts</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-leads") %>" href="/Business/ClientManagement?type=lead">
            <i class="fa-thin fa-bar-chart"></i>
            <span>Leads</span>
        </a>
    </li>
    <%--<li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-prospects") %>" href="/Business/ClientManagement?type=prospect">
            <i class="fa-thin fa-shopping-cart"></i>
            <span>Prospects</span>
        </a>
    </li>--%>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-packingDelay") %>" href="/Business/PackingDelayOrders">
            <i class="fa-thin fa-box-taped"></i>
            <span>Packing Delays</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-orderdelivery") %>" href="/Business/DeliveryBookingFailed">
            <i class="fa-thin fa-moped"></i>
            <span>Delivery Delays</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-retailMerchants") %>" href="/Business/CRMRetailers">
            <i class="fa-thin fa-user-o"></i>
            <span>Retailers/Merchants</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-merchandisingPrds") %>" href="/Business/BSponsoredItems">
            <i class="fa-thin fa-cart-flatbed-boxes"></i>
            <span>Sponsored Items</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-support") %>" href="#">
            <i class="fa-thin fa-headset"></i>
            <span>Contact Support</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-navigation-accounts") %>" href="/Business/BusinessNavigations/BusinessAccounts">
            <i aria-hidden="true" class="icon fa fa-university"></i>
            <span>Accounts</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-navigation-resource") %>" href="/Business/BusinessNavigations/Resources">
            <i aria-hidden="true" class="icon ion-ios-people"></i>
            <span>Resources</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-area") %>" href="/Business/Area">
            <i class="icon fa fa-cog" aria-hidden="true"></i>
            <span>Settings</span>
        </a>
    </li>
    

            <%--<li class="sidebar-nav-item">
                <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-navigation-crm") %>" href="/Business/BusinessNavigations/BusinessCRM">
                    <i class="icon ion-android-person"></i>
                    <span>CRM</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-navigation-accounts") %>" href="/Business/BusinessNavigations/BusinessAccounts">
                    <i aria-hidden="true" class="icon fa fa-university"></i>
                    <span>Accounts</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-navigation-resource") %>" href="/Business/BusinessNavigations/Resources">
                    <i aria-hidden="true" class="icon ion-ios-people"></i>
                    <span>Resources</span>
                </a>
            </li>

          <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "business-area") %>" href="/Business/Area">
              <i class="icon fa fa-cog" aria-hidden="true"></i>
              <span>Settings</span>
            </a>
          </li>--%>
</ul>         