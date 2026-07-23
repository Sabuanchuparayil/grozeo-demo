<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderNavigationFinance.ascx.cs" Inherits="RetalineProAgent.Finance.Controls.HeaderNavigationFinance" %>

<ul class="nav nav-sidebar">
    <li class="sidebar-nav-item">
           <a class="sidebar-nav-link border-top-0 <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_home") %>"" href="/Finance">
              <i class="icon ion-ios-speedometer-outline"></i>
              <span>Dashboard</span>
            </a>
          </li>

          <li class="sidebar-nav-item ">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_Accounting") %>" href="/Finance/Navigations/Accounting">
             <i aria-hidden="true" class="icon fa fa-university"></i>
              <span>Accounting</span>
            </a>
          </li>    
    <li class="sidebar-nav-item ">
             <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_AccountBooks") %>" href="/Finance/Navigations/AccountBooks">
              <i class="icon fa fa-book" aria-hidden="true"></i>
              <span>Account Books</span>
            </a>
          </li>

          <li class="sidebar-nav-item ">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_Reports") %>" href="/Finance/Navigations/Reports">
             <i class="icon fa fa-file-text" aria-hidden="true"></i>
              <span>Reports</span>
            </a>
          </li>
          <li class="sidebar-nav-item ">
           <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_TaxesandDuties") %>" href="/Finance/Navigations/TaxesandDuties">
             <i class="icon fa fa-calculator" aria-hidden="true"></i>
              <span>Taxes & Duties </span>
            </a>
          </li>

          <li class="sidebar-nav-item ">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_ChartofAccounts") %>" href="/Finance/Navigations/ChartofAccounts">
             <i class="icon ion-ios-cog-outline" aria-hidden="true"></i>
              <span>Settings</span>
            </a>
          </li>
     <li class="sidebar-nav-item ">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_Costallocationandautoposting") %>" href="/Finance/Navigations/Costallocationandautoposting">
          <i class="icon ion-ios-calculator"></i>
              <span>Masters</span>
            </a>
          </li>
     <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <%= this.ActiveMenuCss(Page.AppRelativeVirtualPath, "finance_Subscription") %>" href="/Finance/FinanceSubscriptions">
             <i aria-hidden="true" class="icon fa fa-university"></i>
              <span>Subscription</span>
            </a>
          </li>
        
</ul>         