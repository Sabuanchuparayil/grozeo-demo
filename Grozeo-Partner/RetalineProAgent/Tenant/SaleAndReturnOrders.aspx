<%@ Page Language="C#" AutoEventWireup="true" Title="Sale Orders" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="SaleAndReturnOrders.aspx.cs" Inherits="RetalineProAgent.SaleAndReturnOrders" %>

<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sales & Return</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="Sales & Orders"></asp:Literal></h6>    
        <p class="mb-0">Monitor and manage sales and orders for your business</p>
    </div>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
    <script type="text/javascript">
        window.onload = function () {
            document.getElementById('<%= txtOrderId.ClientID %>').setAttribute('autocomplete', 'off');
        };
    </script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
        <div class="row row-sm">
            <div class="col-12 col-sm-6 mb-2 mb-sm-0">

                    <nav class="navbar col-12 w-100 mt-2 mt-sm-0 navbar-expand-sm bg-transparent p-0 justify-content-start align-items-end">
                              <a class="navbar-brand d-sm-none tx-dark tx-14" href="#">Filter by</a>
                              <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                                  <i class="fa fa-sliders" aria-hidden="true"></i>
                                </span>
                              </button>

                            
                              <div class=" collapse navbar-collapse flex-wrap" id="navbarSupportedContent">
                            
                                <ul class="navbar-nav mr-auto pt-2 pt-sm-0">
                                  
                                  <li class="nav-item ml-0 mr-sm-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnPending" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary active">Pending Orders <span class="sr-only">(current)</span></asp:LinkButton>
                                  </li>
                            
                                  <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnViewAll" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary ml-2">View All Orders</asp:LinkButton>
                                  </li>
                                  <%--<li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtOnhold" runat="server" typeid="5" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">Scheduled</asp:LinkButton>
                                  </li>
                                  <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnPacked" runat="server" typeid="6" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">Packed</asp:LinkButton>
                                  </li>
                                  <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnAll" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">All Orders</asp:LinkButton>
                                  </li>--%>
                            
                                </ul>
                              </div>
                            </nav>
                    <%--<asp:LinkButton ID="lbtnProcessing" runat="server" typeid="3" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary ml-2">Processing</asp:LinkButton>--%>
                    <%--<asp:LinkButton ID="lbtnCancel" runat="server" typeid="7" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary ml-2">Cancelled</asp:LinkButton>--%>
                </div>
            <%--<div class="col-12 col-sm-10 mb-2 mb-sm-0">
                <asp:LinkButton ID="lbtnPending" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary active">Successful Orders <span class="sr-only">(current)</span></asp:LinkButton>
                <asp:LinkButton ID="lbtnViewAll" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="btn btn-inline-block btn-outline-primary ml-2">View All Orders</asp:LinkButton>
            </div>--%>
            <div class="col-12 col-sm-6 d-flex justify-content-sm-end align-items-center">
                <asp:LinkButton runat="server" ID="lbtnWishlist" CssClass="btn btn-inline-block btn-outline-primary" OnClick="lbtnWishlist_Click"> Customer Wishlist</asp:LinkButton>
                <%--<asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-inline-block btn-outline-primary ml-2" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download mr-1"></i> Download</asp:LinkButton>--%>
                <button type="button" class="btn btn-inline-block btn-outline-primary ml-2" data-toggle="modal" data-target="#dateRangeModal"><i class="fa fa-download mr-1"></i> Download</button>
            </div>
        </div>
        <div class="row row-sm mt-2">
            <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-lg-b-0">
                <%--<asp:Panel ID="pnlSelectBranchModel" runat="server" CssClass="input-group">
                    <label class="form-control-label mb-1 w-100" for="txtSearch">Select Branch:</label>
                    <asp:DropDownList ID="selBranch" AutoPostBack="true" DataSourceID="SDSBranches" OnDataBound="selBranches_DataBound" AppendDataBoundItems="true" DataTextField="br_Name"  ValidationGroup="StockUpdate" DataValueField="br_ID" CssClass="form-control" runat="server"><asp:ListItem Text="All" Value="-1"></asp:ListItem></asp:DropDownList>
                </asp:Panel>--%>

                <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store</label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                    <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                    <asp:DropDownList ID="selBranch" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranch" AppendDataBoundItems="true" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                        <asp:ListItem Text="All Branch" Value="-1"></asp:ListItem></asp:DropDownList>
                </asp:PlaceHolder>
              <asp:SqlDataSource ID="SDSBranch" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient">
                    <SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" />
                    <asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters>
                    </asp:SqlDataSource>
            </div>
                <div class="col-sm-8 col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
<%--                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />--%>
                    <div style="display:none;">
                        <input type="text" name="name_emailField" />
                        <input type="password" name="passwordFiele" />
                    </div>
                    <asp:TextBox ID="txtOrderId" runat="server" autocomplete="off" CssClass="form-control" placeholder="Order ID, customer_name, etc." name="uniqueOrderId"></asp:TextBox>
                </div>
            <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">
                <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom">From</label>
                      <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
            </div>
            <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">
                <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateTo">To</label>
                      <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
            </div>
            <div class="col-sm-4 col-lg-2 d-flex justify-content-lg-end align-items-sm-end d-flex align-items-end">
                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-sm-0" runat="server">Search</asp:LinkButton>
                <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2"  PostBackUrl="~/Tenant/SaleAndReturnOrders.aspx" Text="Reset" />
<%--                 <a class="btn btn-outline-primary mt-2 mt-lg-0 ml-2" href="javascript:void(0)">Reset</a>--%>
            </div>
        </div>

    </div>

        <div class="card-body">
                <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address, br_directDelivery, br_courierDelivery FROM finascop_branch WHERE br_storeGroup = @storegroup AND (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient">
                    <SelectParameters><asp:Parameter Name="storegroup" DefaultValue="-1" />
                        <asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>

          <div id="accordion" class="table-responsive">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvPendingOrders" GridLines="None" OnRowDataBound="gvPendingOrders_RowDataBound" runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataKeyNames="order_id" OnRowCommand="gvPendingOrders_RowCommand" PageSize="10" OnDataBound="gvPendingOrders_DataBound" DataSourceID="SDSPendingOrders" >
                                    
                                    <Columns>
                                        <asp:TemplateField HeaderText="Order ID" ><ItemTemplate>
                                            <asp:HyperLink runat="server" Text='<%# Eval("order_order_id") %>'>
                                            </asp:HyperLink> <i class="fa-light fa-handshake ml-1" title="Sponsored Orders" runat="server" visible='<%#((Convert.ToInt32(Eval("sponprd")) == 1 ? false : true)) %>'  aria-hidden="true"></i>
                                            <br />
                                            <small>Total: <b><%# Eval("total") %></b></small>
                                        </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Store" HeaderStyle-Width="20%"><ItemTemplate>
                                            <div class="w-100">
                                                <asp:Label ID="Label1" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(Eval("br_Name").ToString(), 12) %>' ToolTip ='<%# Bind("br_Name") %>'></asp:Label> &nbsp;- 
                                                <i class="fa-solid fa-moped ml-1" runat="server" title="Express Delivery" visible='<%# (Convert.ToString(Eval("order_method"))  == "1" ? true : false) && ((Eval("order_slot_id")) == DBNull.Value ? true : false) %>' aria-hidden="true"></i>
                                                <i class="fa-solid fa-person-carry-box ml-1" title="Courier Delivery" runat="server" visible='<%# (Convert.ToString(Eval("order_method"))  == "3" ? true : false) && ((Eval("order_slot_id")) == DBNull.Value ? true : false) %>' aria-hidden="true"></i>
                                                <i class="fa-regular fa-truck" title="Scheduled Delivery" runat="server" visible='<%# ((Eval("order_slot_id")) != DBNull.Value ? true : false) && (Convert.ToInt32(Eval("order_slot_id")) > 0 ? true : false) %>' aria-hidden="true"></i>
                                            </div>
                                            <small>Mode: <b><%# Eval("PaymentMode") %></b></small>
                                                                              </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order Date" ><ItemTemplate>
                                            <%# Eval("created_at") %><br /><small>Delivery: 
                                              <b><%# Eval("delivery_rule_type").ToString() == "6"  ? "<span style='color:red;'>Hold</span>":(Eval("delivery_rule_type").ToString() == "1"  ? "By System" : "By Store") %></b><a class="ml-1"><asp:LinkButton runat="server" CommandArgument='<%# Eval("order_id") + "|6" %>' CommandName="Update" OnClientClick="return confirm('Do yoou really want to hold the delivery?');" ID="btndeliveryhold" orderid='<%# Eval("order_id") %>' Visible='<%# Eval("delivery_rule_type").ToString() == "1" && Convert.ToInt32(Eval("StatusId")) < 9 %>' CssClass="tx-bold" style="text-decoration: underline;" Text="Hold"></asp:LinkButton><asp:LinkButton runat="server" ID="btnresume" CommandArgument='<%# Eval("order_id") + "|1" %>' CommandName="Update"  orderid='<%# Eval("order_id") %>' Visible='<%# Eval("delivery_rule_type").ToString() == "6"&& Convert.ToInt32(Eval("StatusId")) < 9 %>'  CssClass="tx-bold" style="text-decoration: underline;" Text="Resume"></asp:LinkButton></a></small>
                                            </ItemTemplate>                                            
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Customer" ><ItemTemplate>
                                            <asp:Label ID="Label2" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 20) %>' ToolTip ='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 100) %>'></asp:Label>
                                            <br />
                                            <a href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i></a>&nbsp;
                                            <small><%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 20)  %></small>
                                                                                 </ItemTemplate></asp:TemplateField>

                                        <asp:TemplateField HeaderText="Status" SortExpression="order_status" >
                                            <ItemTemplate><asp:Label ID="Label3" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(Eval("order_status").ToString(), 15) %>' ToolTip ='<%# Bind("order_status") %>'></asp:Label>
                                                <br /><%# (Convert.ToInt32(Eval("itemcount")) > 0 ? $"<small>Items: <b>{Eval("itemcount")}</b></small>" : string.Empty) %>&nbsp;&nbsp;&nbsp;<small>Worth: <b><%# Eval("order_total_amount") %></b></small></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>

                                                <div class="action_arrow tx-center"  data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne" ><i class="fa fa-chevron-down" aria-hidden="true"></i></div>

                        <asp:Image runat="server" ID="image" ImageAlign="AbsMiddle" width="13" ImageUrl="/content/images/processing.gif" 
                            Visible='<%# ( (new int[]{5, 11 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>' />

                                            </td></tr><tr><td colspan="6" class="hiddenRow">
                                                <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <asp:HyperLink runat="server"  CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# (
                                                        Convert.ToInt32(Eval("StatusId")) == 10 
                                                            ? true: false) %>' Text="Packing Completed"></asp:HyperLink>
                                                    <asp:HyperLink runat="server" CssClass='<%# (Eval("StatusId") != DBNull.Value && Eval("fsto_id") != DBNull.Value && Eval("fsto_uid") != DBNull.Value && Eval("order_id") != DBNull.Value) ? "btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" : "btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3 disabled" %>'
                                                        Visible='<%# ( (new int[]{0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>' NavigateUrl='<%# String.Format("~/Tenant/OrderDetailsNew.aspx?orderid={0}&toid={1}&ordId={2}&page=Sales", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>'
                                                        Text='<%# (Eval("fsto_id") != DBNull.Value && Eval("fsto_uid") != DBNull.Value && Eval("order_id") != DBNull.Value) ? "View order details" : "Processing..." %>'></asp:HyperLink>
                                                    <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# "https://maps.google.com/?q=" + Eval("lat") + "," + Eval("lng") %>' Target="_blank"
                                                        Visible='<%# (Eval("StatusId") != DBNull.Value && Eval("fsto_id") != DBNull.Value && Eval("fsto_uid") != DBNull.Value && Eval("order_id") != DBNull.Value) %>'><i class="fa-regular fa-location-dot"></i>&nbsp; Click here to view location</asp:HyperLink>

                                                    <asp:HyperLink runat="server"  CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# String.Format("/Tenant/invoice.aspx?ordId={0}", Eval("order_id")) %>' Text="Invoice" Visible='<%# ( (new int[]{8,9,10,11,12,13,14,15,16,17,18,20,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>'></asp:HyperLink>
                                                </div>
                                                          </td></tr>  
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSPendingOrders" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSPendingOrders_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DISTINCT bco.order_id,delivery_rule_type,bco.order_method,bco.order_order_id,bco.order_total_amount,order_packedbags_count,bco.order_customer_id,order_branch_type_id,br_Lat,br_Lng,br_ID,
                                                    CASE WHEN br_storeGroup =(SELECT storegroup_id FROM retaline_customer_order WHERE  storegroup_id= @storegroup LIMIT 1) THEN 1  ELSE 0  END AS sponprd, 
                                                  order_branch_id,so.fsto_id,so.fstr_id,so.fsto_uid, bco.total, bco.order_payment_status, bco.payment_mode,bco.order_slot_id,
                                                  (SELECT COUNT(fsto_id) FROM finascop_stock_transfer_order_details WHERE fsto_id=so.fsto_id) AS itemcount,
                                                  br_Name,bco.status_id AS StatusId,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on, CASE WHEN bco.payment_mode = 1 THEN 'Pay On Delivery' WHEN bco.payment_mode = 2 THEN 'Online' 
                                                  WHEN bco.payment_mode = 3 THEN 'Wallet' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' WHEN bco.payment_mode = 5 THEN 'Online with Wallet' 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' WHEN bco.payment_mode = 7 THEN 'Cash on Delivery' ELSE '' END AS PaymentMode,IF(bco.order_payment_status IS NOT NULL,bco.order_payment_status,(CASE WHEN bco.payment_mode = 1 THEN 'To be collected' WHEN bco.payment_mode = 2 THEN 'Online' 
                                                  WHEN bco.payment_mode = 3 THEN 'Success' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' WHEN bco.payment_mode = 5 THEN 'Success' 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' WHEN bco.payment_mode = 7 THEN 'Cash on Delivery' ELSE '' END )) AS payStatus,
                                                  TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
                                                  admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32, order_latitude AS lat, order_longitude AS lng,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,drivetype,order_land_mark,
                                                  CASE
                                                  WHEN order_method = 1 THEN 'Drive Delivery'
                                                  WHEN order_method = 2 THEN 'Customer Collect'
                                                  WHEN order_method = 3 THEN 'Courier Delivery'
                                                  END AS order_method, order_HasReturn,order_ItemsReturned,order_ReturnVerified,DATE_FORMAT(bco.created_at,'%d %b %Y  %H:%i') AS created_at, IFNULL(order_post, order_pin) AS pin,
                                                  order_latitude,order_longitude, cust_customer_name, cust_mobile, order_house_name, order_city, order_address, CONCAT(order_house_name,', ',order_city,', ',order_post) AS orderAddress
                                                  FROM retaline_customer_order bco INNER JOIN retaline_customer c ON c.cust_id=bco.order_customer_id 
                                                  INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                                                  INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                                                  INNER JOIN finascop_branch ON br_ID = order_branch_id
                                                  LEFT JOIN finascop_stock_transfer_order so ON so.fstr_id = bco.order_id
                                                  LEFT JOIN (SELECT quor_RefNo,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,
                                                  IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','')) AS drivetype FROM qugeo_order) qo ON qo.quor_RefNo = bco.order_order_id
                                                  LEFT JOIN (SELECT rbds_id, CONCAT(rbds_time_from,'-',rbds_time_to) AS slotTime FROM retaline_branch_delivery_slot) slot ON slot.rbds_id = bco.order_slot_id
                                                  WHERE bco.storegroup_id = @storegroup AND (@branchid <= 0 or order_branch_id = @branchid) and bco.status_id > 0 AND bco.status_id >= 4 and (ifnull(@filterType, 0) = 0 
                                                     or (@filterType = 1 and bco.status_id NOT IN(0,1,2,18,19,21,24,34,54,56))  
                                                     or (@filterType = 2 and bco.status_id IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,40,41,42,43,44,45,50))  
                                                     or (@filterType = 3 and bco.status_id IN(14,15,16))  
                                                     or (@filterType = 4 and bco.status_id IN(17,18)) or (@filterType = 5 and bco.status_id IN(4,5,6,7))  or (@filterType = 6 and bco.status_id IN(21, 2))
                                                     or (@filterType = 7 and bco.status_id IN(10,7)) or (@filterType = 8 and bco.status_id IN(16)) or (@filterType = 9 and bco.status_id IN(19, 24))
                                                    ) 
                                                    AND (trim(ifnull(@orderid, '')) like '' or bco.order_order_id like CONCAT('%', @orderid, '%') or br_Name like CONCAT('%', @orderid, '%')
                                                         or cust_customer_name like CONCAT('%', @orderid, '%') or cust_mobile like CONCAT('%', @orderid, '%') or order_customer_email like CONCAT('%', @orderid, '%')
                                                          or order_house_name like CONCAT('%', @orderid, '%') or order_city like CONCAT('%', @orderid, '%') or order_address like CONCAT('%', @orderid, '%')
                                                    ) AND (trim(ifnull(@datefrom, '')) like '' or bco.created_at >=CONVERT(@datefrom, DATE)) AND (trim(ifnull(@dateto, '')) like '' or bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY))
                                                    ORDER BY bco.created_at desc" UpdateCommand="UPDATE retaline_customer_order SET delivery_rule_type = @delivery_rule_type WHERE order_id = @order_id"
        OnSelecting="SDSPendingOrders_Selecting">
         <UpdateParameters>
        <asp:Parameter Name="order_id" Type="String" />
        <asp:Parameter Name="delivery_rule_type" Type="Int32" />
    </UpdateParameters>
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
            <asp:ControlParameter ControlID="txtOrderId" Name="orderid" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selBranch" Name="branchid" DefaultValue="-1" />
        </SelectParameters>
    </asp:SqlDataSource>
            
          </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div><!--card-->
    
    <asp:GridView ID="gvForExportOnly" runat="server" AutoGenerateColumns="false">
        <Columns>
            <asp:BoundField HeaderText="Branch" DataField="br_Name" SortExpression="br_Name" />
            <asp:BoundField HeaderText="Date" DataField="created_at" SortExpression="created_at" />
            <asp:BoundField HeaderText="Customer Name" DataField="cust_customer_name" SortExpression="cust_customer_name" />
            <asp:BoundField HeaderText="Customer Number" DataField="cust_mobile" SortExpression="cust_mobile" />
            <asp:BoundField HeaderText="Delivery Address" DataField="orderAddress" SortExpression="orderAddress" />
            <asp:BoundField HeaderText="Delivery City" DataField="order_city" SortExpression="order_city" />
            <asp:BoundField HeaderText="Delivery Pin" DataField="pin" SortExpression="pin" />
            <asp:BoundField HeaderText="Delivery Landmark" DataField="order_land_mark" SortExpression="order_land_mark" />
            <asp:BoundField HeaderText="Total" DataField="total" SortExpression="total" />
            <asp:BoundField HeaderText="Payment Mode" DataField="PaymentMode" SortExpression="PaymentMode" />
            <asp:BoundField HeaderText="Payment Status" DataField="payStatus" SortExpression="payStatus" />
            <asp:BoundField HeaderText="Order Status" DataField="order_status" SortExpression="order_status" />
                                        
        </Columns>
    </asp:GridView>

    <!-- Modal for Date Range Selection -->
    <div id="dateRangeModal" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateRangeModalLabel">Select Date Range</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="txtDateFromModal">From</label>
                            <asp:TextBox ID="txtDateFromModal" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="txtDateToModal">To</label>
                            <asp:TextBox ID="txtDateToModal" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <asp:Button runat="server" ID="btnDownloadWithDateRange" CssClass="btn btn-primary" Text="Download" OnClick="btnDownloadWithDateRange_Click" />
                    <asp:Button runat="server" ID="btnCancel" CssClass="btn btn-primary" Text="Cancel" PostBackUrl="~/Tenant/SaleAndReturnOrders.aspx" />
                    <%--<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--%>
                </div>
            </div>
        </div>
    </div>
   
    <script>
        $(document).ready(function () {
            // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split("T")[0];

            // Set the max attribute for the date input fields
            const fromDateField = $('#<%= txtDateFromModal.ClientID %>');
        const toDateField = $('#<%= txtDateToModal.ClientID %>');

        if (fromDateField.length) fromDateField.attr("max", today);
        if (toDateField.length) toDateField.attr("max", today);
    });
</script>
     <style>
            .hiddenRow {
                padding: 0 !important;
            }
            tr[data-toggle="collapse"]{
              cursor: pointer;
            }
            tr[aria-expanded="true"] > td .action_arrow .fa-chevron-down::before {
              content: "\f077";
            }
            
       </style>

</asp:Content>



