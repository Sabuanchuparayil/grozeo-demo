<%@ Page Language="C#" AutoEventWireup="true" Title="Online Orders" MasterPageFile="~/AgentMaster.Master" Async="true"  CodeBehind="OnlineOrders.aspx.cs" Inherits="RetalineProAgent.OnlineOrders" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Online Orders</h1>
          </div>
    <script></script>
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
                   <div class="row"><div class="col-sm-1"><button type="button" class="btn btn-block btn-outline-info btn-sm">View All</button></div>
<div class="col-sm-1"><button type="button" class="btn btn-block bg-gradient-info btn-sm">Pending</button></div>
<div class="col-sm-1"><button type="button" class="btn btn-block btn-outline-info btn-sm">Packed</button></div>
<div class="col-sm-1"><button type="button" class="btn btn-block btn-outline-info btn-sm">Shipped</button></div>
<div class="col-sm-1"><button type="button" class="btn btn-block btn-outline-info btn-sm">Delivered</button></div>

<div class="col-sm-2 btn-group">
                    <button type="button" class="btn btn-outline-info btn-sm">Other Filters</button>
                    <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-icon btn-sm" data-toggle="dropdown" aria-expanded="false">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu" style="">
                      <a class="dropdown-item" href="#">Action</a>
                      <a class="dropdown-item" href="#">Another action</a>
                      <a class="dropdown-item" href="#">Something else here</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="#">Separated link</a>
                    </div>
                  </div>
                  

</div>
<hr />
                   <div class="row">
                  <div class="col-sm-2 input-group-sm">
                      <label for="txtSearch" runat="server">Search by - Order ID</label>
                      <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Order ID" autocomplete="nofill"></asp:TextBox> 
                  </div>
                  <div class="col-sm-2 input-group-sm">
                      <label for="txtDateFrom" runat="server">Date - From:</label>
                      <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
                  </div>
                  <div class="col-sm-2 input-group-sm">
                      <label for="txtDateTo" runat="server">Date - To:</label>
                      <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
                  </div>
                      <div class="col-sm-1">
                      <label runat="server">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm" runat="server"><i class="fa fa-search"></i> Search</asp:LinkButton>
                  </div>
<div class="col-sm-5">
    <label style="width: 100%">&nbsp;</label>
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

            </div>
              <div class="card-body">

               <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvOnlineOrders" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvOnlineOrders_DataBound" DataSourceID="SDSOnlineOrders">
                                    <Columns>
                                        <%--<asp:HyperLinkField DataTextField="order_order_id" DataNavigateUrlFields="order_order_id" DataNavigateUrlFormatString="~/OnlineOrderDetailsView.aspx?id={0}"
            HeaderText="Order ID" ItemStyle-Width = "150" SortExpression="order_order_id" />--%>
                                        <asp:BoundField HeaderText="Order ID" DataField="order_order_id" SortExpression="order_order_id" />
                                        <asp:BoundField HeaderText="Branch" DataField="br_Name" SortExpression="br_Name" />
                                        <asp:BoundField HeaderText="Date" DataField="order_created_on" SortExpression="order_created_on" />
                                        <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" />
                                        <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
                                        <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" />
                                        <%--<asp:BoundField HeaderText="PayGatewayId" DataField="order_payment_gateway_refid" SortExpression="order_payment_gateway_refid" />--%>
                                        <asp:TemplateField HeaderText="">
                                            <ItemTemplate>
                                                <div class="btn-group">
                    <button type="button" class="btn btn-default">Action</button>
                    <button type="button" class="btn btn-default dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                                                             <a Class="dropdown-item" href="/PrintPackingSlip">Print Packing Slip</a>
                                                             <a Class="dropdown-item" href="/PrintInvoice">Print Invoice</a>
                                                             <a Class="dropdown-item" href="/OrderHistory">Order History</a>
                                                             <a Class="dropdown-item" href="/Delivery">Delivery</a>
                                                             <a Class="dropdown-item" href="/ReturnedItems">Returned Items</a>

                        </div></div>

                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSOnlineOrders" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT bco.order_id,bco.order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,
                                    br_Name,bco.status_id AS StatusId,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on,
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
        OnSelecting="SDSOnlineOrders_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
              </div>
            </div>
    </div>
</asp:Content>



