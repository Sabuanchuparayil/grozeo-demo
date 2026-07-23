<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="invoice.aspx.cs" Inherits="RetalineProAgent.Tenant.invoice" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/Tenant"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
     <div class="card-body">
 <asp:Literal runat="server" ID="ltrinvoice"></asp:Literal>
     </div>   
</asp:Content>


