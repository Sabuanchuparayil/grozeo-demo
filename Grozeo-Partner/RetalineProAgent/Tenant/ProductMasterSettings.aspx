<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Product Master" AutoEventWireup="true" CodeBehind="ProductMasterSettings.aspx.cs" Inherits="RetalineProAgent.ProductMasterSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/">Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/ProductMaster">Product Master</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Product Master</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Create Product Master</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="section-wrapper">
          <label class="section-title">Create New Product Master</label>
          <div class="form-layout">
            <div class="row mg-b-25">
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtName" runat="server" required CssClass="form-control" placeholder="Enter product master name"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Status: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="DropDownList1" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="0">Please Select</asp:ListItem>
                              <asp:ListItem>Active</asp:ListItem>
                              <asp:ListItem>Inactive</asp:ListItem>
                          </asp:DropDownList>
                </div>
              </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkGrpProducts" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("isItemGroup").Equals("Active") %>'/>
                <span>Group products under the product master</span>
                </div><!-- col-3 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label" id="lbDisplayName" runat="server">Display Name:<span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtDisplayName" runat="server" required CssClass="form-control" placeholder="Enter display name" Visible="false"/>
                </div>
              </div><!-- col-4 -->
            </div><!-- row -->
            <div class="form-layout-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                <asp:Button runat="server" ID="btnVerify" OnClick="btnVerify_Click" CssClass="btn btn-primary bd-0" Text="Verify" Visible="true" />
                <a href="/Tenant/ProductMaster" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>
            </div>
          </div><!-- form-layout -->
        </div>
</asp:Content>