<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="FSSAI-Add.aspx.cs" Inherits="RetalineProAgent.Tenant.Store.FSSAI_Add" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Store/FSSAI">FSSAI Details</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add FSSAI Account</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href=" /Tenant/Store/FSSAI"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
   
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle"> Add FSSAI Number</h6>
    <p class="mb-0">Input FSSAI Number and submit for verification.</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">

          <div class="form-layout">
            <div class="row row-sm">
              <div class="col-lg-8">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">FSSAI Number: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtAccountNumber" CssClass="form-control" runat="server" placeholder="Enter FSSAI number" autocomplete="off"></asp:TextBox>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAccountNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="FSSAI number is required" ValidationGroup="AddAccount" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->

                <div class="col-12 mt-1">
                <div class="d-inline-block">
                        <asp:Button ID="btnAddFSSAI" runat="server" Text="Submit" OnClick="btnAddFSSAI_Click" CssClass="btn btn-primary mr-2" ValidationGroup="AddAccount" />
                        <a href="/Tenant/Store/FSSAI" class="btn btn-secondary bd-0">Cancel</a>
                    <asp:Label ID="lblResult" runat="server"></asp:Label>
                </div>
              </div>
            </div><!-- row -->


          </div><!-- form-layout -->
        </div><!-- card-body -->
    </div>


</asp:Content>


