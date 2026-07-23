<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="" MasterPageFile="~/AgentMaster.Master" CodeBehind="OnlineOrderDetailsView.aspx.cs" Inherits="RetalineProAgent.OnlineOrderDetailsView" %>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row">
          <!-- left column -->
          <div class="col-md-6" id="dvColStoreInfo" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Order Details</h3>
              </div>
                <%--<div class="card-body box-profile">--%>
                    <%--<asp:Literal ID="ltrOrderId" runat="server"></asp:Literal>--%>
                    <%--<div class="card">--%>
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
                                        <th>Order</th>
                                        <td><asp:Literal ID="ltrOrder" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Date</th>  
                                        <td><asp:Literal ID="ltrDate" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>Current Status</th>
                                        <td><asp:Literal ID="ltrCurrentStatus" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Previous Status</th>
                                        <td><asp:Literal ID="ltrPreviousStatus" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</asp:Content>

