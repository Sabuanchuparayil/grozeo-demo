<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" Title="Search" CodeBehind="SearchResult.aspx.cs" Inherits="RetalineProAgent.SearchResult" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <link href="/Content/lib/jt.timepicker/css/jquery.timepicker.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
    <script src="/Content/lib/jt.timepicker/js/jquery.timepicker.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Search</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card card-table">
        <div class="card-header">
            <h6 class="slim-card-title">Latest Orders</h6>
        </div>
        <!-- card-header -->
        <div class="table-responsive">
            <table class="table mg-b-0 tx-13" id="tblpendingorders">
                <thead>
                    <tr class="tx-10">
                        <th class="pd-y-5">Content</th>                                               
                    </tr>
                </thead>
                <tbody>

                    <asp:SqlDataSource ID="SDSRecentOrders" runat="server" OnSelecting="SDSRecentOrders_Selecting"
                        SelectCommand="SELECT o.order_id, o.order_group_id, o.order_order_id, o.total, b.br_Name, d.order_city, TIMESTAMPDIFF
(MINUTE, o.created_at, NOW()) AS diff,so.fsto_id,so.fsto_uid FROM retaline_customer_order o 
    INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id
LEFT JOIN finascop_branch b ON o.order_branch_id=b.br_ID
LEFT JOIN retaline_customer_order_delivery_address d ON o.order_order_id=d.order_id 
 WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) 
 AND(TRIM(IFNULL(@key, '')) LIKE '' OR o.order_id LIKE CONCAT('%', @key, '%')) OR order_city LIKE CONCAT('%', @key, '%') OR br_Name LIKE CONCAT('%', @key, '%') 
 AND storegroup_id=@storegroup
  ORDER BY o.created_at DESC LIMIT 10
"
                        ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:Parameter Name="storegroup" />
                         <asp:QueryStringParameter QueryStringField="key" Name="key" />
                            
                        </SelectParameters>
                    </asp:SqlDataSource>

                    <asp:Repeater ID="rptOrders" runat="server" DataSourceID="SDSRecentOrders">
                        <ItemTemplate>
                            <tr>
                                <td>Order id: <a href="<%# String.Format("/OrderDetails.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>"><%# Eval("order_order_id") %></a>
                                    &nbsp; <%# RetalineProAgent.Service.Common.MinutesToDiff(Convert.ToInt32(Eval("diff"))) %>                                
                                Store: <%# Eval("br_Name") %>, Delivery location: <%# Eval("order_city") %>, Amount: <%# String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("total")) %>
                                    </td>                               
                            </tr>

                        </ItemTemplate>
                        <FooterTemplate>
                            <tr>
                                <td colspan="4">
                                    <asp:Label ID="lblEmptyData" runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>' Text="No recent orders" /></td>
                            </tr>

                        </FooterTemplate>
                    </asp:Repeater>
                </tbody>
            </table>
        </div>
        <%--<div class="card-footer tx-12 pd-y-15 bg-transparent">
                <a href="/SaleAndReturnOrders"><i class="fa fa-angle-down mg-r-5"></i>View All Orders</a>
              </div><--%>
    </div>
    <!-- card -->    
        <!-- card-header -->
    <!-- card -->
</asp:Content>
