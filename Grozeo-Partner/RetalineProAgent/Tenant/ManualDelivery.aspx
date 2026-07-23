<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Manual Delivery" Async="true" AutoEventWireup="true" CodeBehind="ManualDelivery.aspx.cs" Inherits="RetalineProAgent.ManualDelivery" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Manual Delivery"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> --%>
            </h6>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/OrderDelivery">Order Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manual Delivery</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
              <div class="card-body p-3 shadow_top">
                  <div class="row row-sm">
                      <div class="col-12 col-lg-3"><label class="w-100 text-left tx-dark">Order Id</label>
                    <asp:TextBox ID="txtOrdId" Enabled="false" runat="server" CssClass="form-control" deliveredDate="delivered_date" />
                  </div>
                  <div class="col-12 col-lg-3"><label class="w-100 text-left tx-dark">Date</label>
                    <asp:TextBox ID="txtDate"  runat="server" TextMode="Date" CssClass="form-control" deliveredDate="delivered_date" />
                  </div>
                  <div class="col-12 col-lg-3"><label class="w-100 text-left tx-dark">Time</label>
                    <asp:TextBox ID="txtTime" runat="server"  TextMode="Time" CssClass="form-control" deliverderTime="delivered_time"/>
                  </div>
                    <div class="col-12 col-lg-3"><label class="w-100 text-left tx-dark">Delivered By <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtDelivBoy" runat="server" CssClass="form-control" placeholder="Delivered by" autocomplete="nofill"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDelivBoy" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Delivered by is required" ValidationGroup="ManualSchDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                  </div>
                   
                <div class="col-12 mt-3">
                    <div class="d-inline-block">
                        <asp:Button runat="server" ID="btnAdd" OnClick="btnManualDeliverySubmit_Click" CssClass="btn btn-primary mr-1" Text="Submit" order_id='<%# Eval("order_id") %>'  ValidationGroup="ManualSchDelivery"/>
                        <a href="/Tenant/MerchantDelivery" class="btn btn-secondary">Cancel</a>
                        <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                    </div>
                </div>

                </div>
                  </div>
              </div>

</asp:Content>
