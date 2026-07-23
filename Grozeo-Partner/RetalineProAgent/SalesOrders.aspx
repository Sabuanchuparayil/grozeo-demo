<%@ Page Language="C#" AutoEventWireup="true" Title="Sales Orders" MasterPageFile="~/AgentMaster.Master" Async="true"  CodeBehind="SalesOrders.aspx.cs" Inherits="RetalineProAgent.SalesOrders" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Sales Orders</h1>
          </div>
     <style type="text/css">
        *
        {
            margin: 0px;
            padding: 0px;
        }
        #menu ul
        {
            list-style: none;
        }
        #menu ul li
        {
            background-color: lightseagreen;
            border: 1px solid white;
            width: 125px;
            height: 35px;
            line-height: 35px;
            text-align: center;
            float:left;
            position: relative;
        }
        #menu ul li a
        {
            text-decoration: none;
            color: white;
            display:block;
        }
        #menu ul li a:hover
        {
            background-color: lightgreen;
        }
        #menu ul ul
        {
            position: absolute;
            display: none;
        }
        #menu ul li:hover > ul
        {
            display: block;
            margin-right: 125px;
            margin-top: -4px;
    z-index: 2;
        }
    </style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
               <div class="card-header">
              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
<div class="float-right">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fa fa-chevron-right"></i>
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
                                        <asp:TemplateField HeaderText="Action">
                                            <ItemTemplate>
                                                <div id="menu">
                                                 <ul>
                                                     <li>
                                                         <a href="#">Choose Action</a>
                                                         <ul>
                                                             <li><a href="/PrintSalesPackingSlip">Print Packing Slip</a></li>
                                                             <li><a href="#">Print Invoice</a></li>
                                                             <li><a href="#">Order History</a></li>
                                                             <li><a href="#">Returned Items</a></li>
                                                             <li><a href="#">View Map</a></li>
                                                         </ul>
                                                     </li>
                                                 </ul>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSSalesOrders" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
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
                        WHERE 1 = 1 AND bco.status_id > 0 AND storegroup_id = @storegroup"
        OnSelecting="SDSSalesOrders_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
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



