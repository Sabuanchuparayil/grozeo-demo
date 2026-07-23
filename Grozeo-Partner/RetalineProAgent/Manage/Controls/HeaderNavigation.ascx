<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderNavigation.ascx.cs" Inherits="RetalineProAgent.Manage.Controls.HeaderNavigation" %>

<ul class="nav nav-sidebar">
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link border-top-0 <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "manage-home") %>" href="/Manage/Default">
            <i class="icon ion-ios-speedometer-outline"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "manage-contact") %>" href="/Manage/CustomDomain">
            <i class="fa-thin fa-address-book"></i>
            <span>Custom Domain</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "manage-leads") %>" href="/Manage/mystores">
            <i class="fa-thin fa-bar-chart"></i>
            <span>Stores</span>
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "manage-prospects") %>" href="/Manage/SupportTicket">
            <i class="fa-thin fa-shopping-cart"></i>
            <span>Tickets</span>
        </a>
    </li>


</ul>
