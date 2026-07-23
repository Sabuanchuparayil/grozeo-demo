<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Leads" AutoEventWireup="true" CodeBehind="LeadSettings.aspx.cs" Inherits="RetalineProAgent.LeadSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/crm">Leads & Customers</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Leads">Leads</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Lead</li>--%>
    <a href="javascript:void(0)" onclick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Add New Lead</h6>
        <p class="mb-0">Create New Lead</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">
            <div class="form-layout">
                <div class="row row-sm mg-b-5">
                    <div class="col-sm-4 col-lg-3 mb-2 mb-sm-0">
                        <div class="form-group mb-0">
                            <label class="form-control-label">Name: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtName" runat="server" CssClass="form-control" placeholder="Enter name" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="SaveLead" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-sm-4 col-lg-3 mb-2 mb-sm-0">
                        <div class="form-group mb-0">
                            <label class="form-control-label">Phone: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtMobile" runat="server" CssClass="form-control" placeholder="Enter phone" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobile" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Phone number is required" ValidationGroup="SaveLead" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-sm-4 col-lg-3">
                        <div class="form-group mb-0">
                            <label class="form-control-label">Email: </label>
                            <asp:TextBox ID="txtEmail" runat="server" CssClass="form-control" TextMode="Email" placeholder="Enter email" autocomplete="nofill" />
                        </div>
                    </div>
                    <!-- col-4 -->

                    <div class="col-lg-3 mt-2 mt-lg-0 d-flex align-items-end">
                        <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary mr-2" Text="Save" ValidationGroup="SaveLead" />
                        <a href="/Tenant/Leads" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
                <!-- row -->

            </div>
            <!-- form-layout -->
        </div>
    </div>

</asp:Content>
