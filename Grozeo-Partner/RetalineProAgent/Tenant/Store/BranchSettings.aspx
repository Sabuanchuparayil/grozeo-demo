<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="Create Branch" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BranchSettings.aspx.cs" Inherits="RetalineProAgent.BranchSettings" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlCreateStore.ascx" TagPrefix="uc1" TagName="ctrlCreateStore" %>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <uc1:ctrlCreateStore runat="server" IsBranchView="True" Visible="false" id="ctrlCreateStore1" />





</asp:Content>
