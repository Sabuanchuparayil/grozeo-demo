<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="" AutoEventWireup="true" CodeBehind="TestPagination.aspx.cs" Inherits="RetalineProAgent.TestPagination" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Sales Orders</h1>
          </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
               <div class="card-header">
              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search"></asp:TextBox> 
<div class="float-right">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fas fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fas fa-chevron-right"></i>
                      </asp:LinkButton>
                    
                  </div>
                  <!-- /.btn-group -->
                </div>

                </div>
              </div>
              <br /><br />
            </div>
              <div class="card-body">

               <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvSalesOrders" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvSalesOrders_DataBound" DataSourceID="SDSSalesOrders">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="order_order_id" DataNavigateUrlFields="order_order_id" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Order ID" ItemStyle-Width = "150" SortExpression="order_order_id" />
                                        <asp:BoundField HeaderText="Store" DataField="br_Name" SortExpression="br_Name" />
                                        <asp:BoundField HeaderText="Date" DataField="order_created_on" SortExpression="order_created_on" />
                                        <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" />
                                        <asp:BoundField HeaderText="Delivery Mode" DataField="order_method" SortExpression="order_method" />
                                        <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
                                        <asp:BoundField HeaderText="Mobile" DataField="cust_mobile" SortExpression="cust_mobile" />
                                        <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" />
                                        <asp:BoundField HeaderText="PayGateway ID" DataField="order_payment_gateway_refid" SortExpression="order_payment_gateway_refid" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSSalesOrders" ProviderName="MySql.Data.MySqlClient"
                                 SelectCommand="SELECT bco.order_id,bco.order_order_id,order_packedbags_count,
bco.order_customer_id,order_branch_id,br_Name,
 bco.status_id AS STATUS,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on,
 TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
 admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
CASE
    WHEN order_method = 1 THEN 'Drive Delivery'
    WHEN order_method = 2 THEN 'Customer Collect'
    WHEN order_method = 3 THEN 'Courier Delivery'
END AS order_method,
(SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,
(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at,
            order_latitude,order_longitude
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                        INNER JOIN finascop_branch ON br_ID = order_branch_id 
                        WHERE 1 = 1 AND bco.status_id > 0"
        OnSelected="SDSSalesOrders_Selected">
        <SelectParameters>
            <asp:ControlParameter Name="search" ControlID="txtSearch" Type="String" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
              </div>
            </div>
    </div>
</asp:Content>