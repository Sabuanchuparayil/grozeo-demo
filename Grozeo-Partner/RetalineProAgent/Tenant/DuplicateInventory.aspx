<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products" ValidateRequest="false" AutoEventWireup="true" CodeBehind="DuplicateInventory.aspx.cs" Inherits="RetalineProAgent.DuplicateInventory" %>

<%@ Register Src="~/Controls/StoreSettings/ctrlDuplicatedProduct.ascx" TagPrefix="uc1" TagName="ctrlDuplicatedProduct" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlMessagebox.ascx" TagPrefix="uc1" TagName="ctrlMessagebox" %>



<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/MyProducts">My Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Product</li>--%>
    <a href="/Tenant/MyProducts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Create Product</h6>
    <p class="mb-0">Add a New Product</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
     <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body p-3 shadow_top">
          <%--<label class="section-title">Create New Product</label>--%>
        <uc1:ctrlDuplicatedProduct runat="server" ID="ctrlDuplicatedProduct" />
        </div>
    </div>

    <uc1:ctrlMessagebox runat="server" id="ctrlMessagebox" />

</asp:Content>