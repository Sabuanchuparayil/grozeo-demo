<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlOrderPackingDetails.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlOrderPackingDetails" %>


<div class="row">
          <!-- left column -->
          <div class="col-md-6" id="dvColStoreInfo" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Order Packing Details</h3>
              </div>
                <%--<div class="card-body box-profile">--%>
                    <%--<asp:Literal ID="ltrOrderId" runat="server"></asp:Literal>--%>
                    <%--<div class="card">--%>
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
                                        <th>TO No.</th>
                                        <td><asp:Literal ID="ltrOrderId" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Store</th>  
                                        <td><asp:Literal ID="ltrStore" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>Customer</th>
                                        <td><asp:Literal ID="ltrCustomer" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Order Created At</th>
                                        <td><asp:Literal ID="ltrCreateDate" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Order Type</th>
                                        <td><asp:Literal ID="ltrType" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Order Number</th>
                                        <td><asp:Literal ID="ltrOrdNumber" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Order Date</th>
                                        <td><asp:Literal ID="ltrDate" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Scheduler Opening Time</th>
                                        <td><asp:Literal ID="ltrScheduleTime" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Pack Type</th>
                                        <td><asp:Literal ID="ltrPackType" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                          <tr>
                                        <th>Status</th>
                                        <td><asp:Literal ID="ltrStatus" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                            </tbody>
                        </table>
                      </div>
              
            <%--</div>--%>
                <%--</div>--%>
            </div>
        </div>
    </div>


<br />
