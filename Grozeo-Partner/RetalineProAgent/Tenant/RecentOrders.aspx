<%@ Page Language="C#" AutoEventWireup="true" Title="" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="RecentOrders.aspx.cs" Inherits="RetalineProAgent.RecentOrders" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <style type="text/css">
        div.pac-container {
    z-index: 99999999999 !important;
        }
    </style>
</asp:Content>  

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="row">
       <!-- left column -->
          <div class="col-md-6" id="dvColInfo" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Recent Order Details</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
                                        <th>Order ID</th>
                                        <td><asp:Literal ID="ltrOrdID" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>From</th>  
                                        <td><asp:Literal ID="ltrFROM" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>To</th>
                                        <td><asp:Literal ID="ltrTO" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Amount</th>
                                        <td><asp:Literal ID="ltrAMOUNT" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
              
            <%--</div>--%>
                <%--</div>--%>
            </div>
        </div>
    </div>


<br />
    
    
</asp:Content>