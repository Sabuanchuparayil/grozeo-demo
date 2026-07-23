<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderNavigationAdmin.ascx.cs" Inherits="RetalineProAgent.AdminControls.HeaderNavigationAdmin" %>
<%@ Import Namespace="System.Text.RegularExpressions"%>

    <asp:SiteMapDataSource ID="SMDataSource" runat="server"  StartingNodeUrl="~/" ShowStartingNode="false"  />
<ul class="nav nav-sidebar">
<asp:Repeater ID="navRepeater" runat="server" DataSourceID="SMDataSource">
    <ItemTemplate>
    <li class="sidebar-nav-item">
        <asp:HyperLink ID="hlNav" runat="server" NavigateUrl='<%# Eval("url") %>' CssClass='<%# string.Format("sidebar-nav-link {0}", 
    (string.Equals(Eval("url").ToString().TrimEnd(".aspx".ToCharArray()).TrimStart("~/".ToCharArray()), Page.AppRelativeVirtualPath.TrimEnd(".aspx".ToCharArray()).TrimStart("~/".ToCharArray()), StringComparison.OrdinalIgnoreCase) 
        ? "mega-dropdown active" : "")) %>'>
            <i class="<%# Eval("description") %>"></i>
            <span><%# Eval("title") %></span>
        </asp:HyperLink>
    </li>
    </ItemTemplate>
</asp:Repeater>

        </ul>

