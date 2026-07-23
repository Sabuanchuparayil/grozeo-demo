<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlMenuNavigation.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlMenuNavigation" %>

    <%@ Import Namespace="System.Text.RegularExpressions"%>

    <asp:SiteMapDataSource ID="SMDataSource" runat="server"  StartingNodeUrl="~/" ShowStartingNode="false"  />

    <a href="" id="slimSidebarMenuDesktop" class="slim-sidebar-menu slimSidebarMenu slimSidebarMenuDesktop w-100 justify-content-start">
    <i class="burgermenu_icon">
        <img class="burgermenu-left" src="/Content/images/burgermenu-left.svg">
        <img class="burgermenu-right" src="/Content/images/burgermenu-right.svg">
    </i>
    <div class="mtext" ><asp:Literal ID="ltrMenuTitle" runat="server" Text="Menu List"></asp:Literal></div>
    </a>

<ul class="nav nav-sidebar">
<asp:Repeater ID="navRepeater" runat="server" DataSourceID="SMDataSource">
    <ItemTemplate>
    <li class="sidebar-nav-item">
        <asp:HyperLink ID="hlNav" runat="server" NavigateUrl='<%# Eval("url") %>' CssClass='<%# string.Format("sidebar-nav-link {0}", 
    (string.Equals(Eval("url").ToString().TrimEnd(".aspx".ToCharArray()).TrimStart("~/".ToCharArray()), Page.AppRelativeVirtualPath.TrimEnd(".aspx".ToCharArray()).TrimStart("~/".ToCharArray()), StringComparison.OrdinalIgnoreCase) 
        ? "mega-dropdown active" : "")) %>'>
            <i class="fa-thin <%# Eval("description") %>"></i>
            <span><%# Eval("title") %></span>
        </asp:HyperLink>
    </li>
    </ItemTemplate>
</asp:Repeater>

        </ul>

<asp:SiteMapDataSource ID="SDSRootNodes" runat="server"  StartingNodeUrl="~/" ShowStartingNode="false"  />
<asp:Repeater ID="Repeater1" runat="server" DataSourceID="SDSRootNodes" OnPreRender="Repeater1_PreRender">
    <ItemTemplate>
        <asp:HyperLink Visible='<%# ShowParentNode(Eval("url"), Eval("title")) %>' NavigateUrl=<%# Eval("url") %> runat="server" CssClass="slimSidebarMenuDesktop w-100 justify-content-start">
        <i class="burgermenu_icon">
            <img class="burgermenu-left" src="/Content/images/burgermenu-left.svg">
        </i>
        <div class="mtext" ><%# Eval("title") %></div>
        </asp:HyperLink>
    </ItemTemplate>
</asp:Repeater>
