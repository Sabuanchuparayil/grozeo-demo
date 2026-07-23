<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="GST-Test.aspx.cs" Inherits="RetalineProAgent.Tenant.Store.GST_Test" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
     <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/store/gst"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></a></li>
    <li class="breadcrumb-item active" aria-current="page">Test <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> Add <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> Account</h6>
        <p class="mb-0">Test <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> account</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <asp:PlaceHolder ID="plcAddGST" runat="server">
        <div class="card">
            <div class="card-body p-3">
            <p class="mb-2">Input <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %> and submit for verification.</p>

            <div class="form-layout">
                <div class="row row-sm">
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label class="w-100 text-left tx-dark"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %>: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtGST" CssClass="form-control" runat="server" placeholder="Enter GSTIN/VAT" autocomplete="off"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtGST" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="GSTIN / VAT is required" ValidationGroup="ADDGST" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-12 mt-2">
                        <div class="d-inline-block">
                            <asp:Button ID="btnAddGST" runat="server" Text="Submit" OnClick="btnAddGST_Click" CssClass="btn btn-primary mr-1" ValidationGroup="ADDGST" />
                            <a href="/Tenant/store/gst-test" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    <!-- col-4 -->
                </div>
                <!-- row -->

                <div class="form-layout-footer">
                    <asp:Label ID="lblResult" runat="server"></asp:Label>
                </div>
                <!-- form-layout-footer -->

            </div>
            <!-- form-layout -->
        </div>
        <!-- card body -->
        </div>
        
</asp:PlaceHolder>


</asp:Content>

