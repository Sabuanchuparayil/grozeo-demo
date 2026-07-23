<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderNavigation.ascx.cs" Inherits="RetalineProAgent.Controls.HeaderNavigation" %>

<ul class="nav nav-sidebar">
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link border-top-0 <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-home") %>" href="/Tenant/Default">
            <i class="fa-thin fa-gauge-max"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-product") %>" href="/Navigations/Products">
            <i class="fa-thin fa-cart-flatbed-boxes"></i>
            <span>Products</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-sales") %>" href="/Tenant/SaleAndReturnOrders">
            <i class="fa-thin fa-bags-shopping"></i>
            <span>Sales & Orders</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-packingDelivery") %>" href="/Tenant/PendingOrders">
            <i class="fa-thin fa-box-taped"></i>
            <span>Order Packing</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-delivery") %>" href="/Tenant/MerchantDelivery">
            <i class="fa-thin fa-moped"></i>
            <span>Order Delivery</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-accounts") %>" href="/Navigations/Accounts">
            <i class="fa-thin fa-file-invoice"></i>
            <span>Accounts & MIS</span>
            <%--<span class="square-8"></span>--%>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-support") %>" href="/Navigations/Support">
            <i class="fa-thin fa-headset"></i>
            <span>Support Center</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-analytics") %>" href="/Tenant/Analytics">
            <i class="fa-thin fa-chart-mixed"></i>
            <span>Analytics</span>
        </a>
    </li>

    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link  <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "tenant-settings") %>" href="/Navigations/StoreSettings">
            <i class="fa-thin fa-gear"></i>
            <span>Settings</span>
        </a>
    </li>

</ul>

