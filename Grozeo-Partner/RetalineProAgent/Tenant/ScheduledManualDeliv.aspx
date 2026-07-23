<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Manual Delivery" Async="true" AutoEventWireup="true" CodeBehind="ScheduledManualDeliv.aspx.cs" Inherits="RetalineProAgent.ScheduledManualDeliv" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/PackingDelivery">Fulfilment</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/MerchantDelivery">Order Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manual Delivery</li>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Manual Delivery</h3>
              </div>
              <div class="card-body">
                  <div class="row">
                  <div class="col-3"><label>Date</label>
                    <asp:TextBox ID="txtDate" runat="server" TextMode="Date" CssClass="form-control" deliveredDate="delivered_date" />
                  </div>
                  <div class="col-2"><label>Time</label>
                    <asp:TextBox ID="txtTime" runat="server" TextMode="Time" CssClass="form-control" deliverderTime="delivered_time"/>
                  </div>
                    <div class="col-4"><label>Delivered By*</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtDelivBoy" runat="server" required CssClass="form-control" placeholder="Delivered By" autocomplete="nofill"/>
                  </div>
                      <div class="col-4"><label>Delivered By*</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtRemarks" runat="server" required CssClass="form-control" placeholder="Remarks" autocomplete="nofill"/>
                  </div>
<div class="col-3"><br />
            <asp:Button runat="server" ID="btnAdd" OnClick="btnManualDeliverySubmit_Click" CssClass="btn btn-success" Text="Submit" order_id='<%# Eval("order_id") %>' />&nbsp;
            <asp:Button ID="btnReset" runat="server" CausesValidation="false" ValidateRequestMode="Disabled" Text="Clear" CssClass="btn btn-secondary" />&nbsp;
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>
        </div>

                </div>&nbsp;&nbsp;
                  </div>
              </div>
<div class="row">
        
      </div>

</asp:Content>
