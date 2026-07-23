<%@ Page Language="C#"  CodeBehind="Salesreturns.aspx.cs" AutoEventWireup="true"  Title="Sales and return" ValidateRequest="false"  MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.Salesreturns" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="Sales & Return"></asp:Literal></h6>    
        <p class="mb-0">Monitor and manage sales and return for your business</p>
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
        <div class="row row-sm mt-2">
            <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-lg-b-0">         
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
                <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2"  PostBackUrl="~/Tenant/Salesreturns.aspx" Text="Reset" />
            </div>
        </div>

    </div>
        <div class="card-body">
                <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address, br_directDelivery, br_courierDelivery FROM finascop_branch WHERE br_storeGroup = @storegroup AND (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient">
                    <SelectParameters><asp:Parameter Name="storegroup" DefaultValue="-1" />
                        <asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>

          <div id="accordion" class="table-responsive">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvPendingOrders"   GridLines="None"  runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10"  DataSourceID="SDSPendingOrders" >
                                    
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
                                            <%# Eval("created_at") %><br /><small>Status: 
                                                <b><%# Eval("payStatus") %></b></small>
                                            </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Customer" ><ItemTemplate>
                                            <asp:Label ID="Label2" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 20) %>' ToolTip ='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")),100) %>'></asp:Label>
                                            <br />
                                            <a href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i></a>&nbsp;
                                            <small><%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 20)%></small>
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
                                                    <asp:LinkButton runat="server" data-orderid='<%# Eval("order_id") %>' data-ordermethod='<%# Eval("order_method") %>' ID="btnviewitems" OnClick="btnviewitems_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="View items" ></asp:LinkButton>
                                                    <asp:LinkButton runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Enabled="false" Text="Request Photos" ></asp:LinkButton>
                                                    <asp:LinkButton runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Enabled="false" Text="Initiate Pickup" ></asp:LinkButton>
                                                     <asp:LinkButton runat="server" data-orderid='<%# Eval("order_id") %>' Visible='<%# Eval("rtrqo_status") == DBNull.Value || Eval("rtrqo_status") == null || (Convert.ToInt32(Eval("rtrqo_status")) != 1) %>'  data-ordermethod='<%# Eval("order_method") %>' ID="btnResolveManually" OnClick="btnResolveManually_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3 resolve-btn" Text="Resolve Manually" ></asp:LinkButton>
                                                     <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# String.Format("~/Tenant/OrderDetails.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View order details"></asp:HyperLink>
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
                                <asp:SqlDataSource runat="server" ID="SDSPendingOrders" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSPendingOrders_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DISTINCT bco.order_id,bco.order_method,fsto_status,bco.order_order_id,bco.order_total_amount,fr.rtrqo_status,order_packedbags_count,bco.order_customer_id,order_branch_type_id,br_Lat,br_Lng,br_ID,
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
                                                  FROM retaline_customer_order bco 
                                                  LEFT JOIN finascop_stock_return_request_order fr ON bco.order_id = fr.order_id
                                                  INNER JOIN retaline_customer c ON c.cust_id=bco.order_customer_id 
                                                  INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                                                  INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                                                  INNER JOIN finascop_branch ON br_ID = order_branch_id
                                                  LEFT JOIN finascop_stock_transfer_order so ON so.fstr_id = bco.order_id
                                                  LEFT JOIN (SELECT quor_RefNo,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,
                                                  IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','')) AS drivetype FROM qugeo_order) qo ON qo.quor_RefNo = bco.order_order_id
                                                  LEFT JOIN (SELECT rbds_id, CONCAT(rbds_time_from,'-',rbds_time_to) AS slotTime FROM retaline_branch_delivery_slot) slot ON slot.rbds_id = bco.order_slot_id
                                                 WHERE bco.storegroup_id = @storegroup  AND (@branchid <= 0 OR order_branch_id = @branchid) AND bco.status_id > 0 
                                                    AND bco.status_id = 18 
                                                    AND order_HasReturnRequest > 0 
                                                    AND (trim(ifnull(@orderid, '')) LIKE '' 
                                                        OR bco.order_order_id LIKE CONCAT('%', @orderid, '%') 
                                                        OR br_Name LIKE CONCAT('%', @orderid, '%') 
                                                        OR cust_customer_name LIKE CONCAT('%', @orderid, '%') 
                                                        OR cust_mobile LIKE CONCAT('%', @orderid, '%') 
                                                        OR order_customer_email LIKE CONCAT('%', @orderid, '%') 
                                                        OR order_house_name LIKE CONCAT('%', @orderid, '%') 
                                                        OR order_city LIKE CONCAT('%', @orderid, '%') 
                                                        OR order_address LIKE CONCAT('%', @orderid, '%'))
                                                    AND (trim(ifnull(@datefrom, '')) LIKE '' OR bco.created_at >= CONVERT(@datefrom, DATE))
                                                    AND (trim(ifnull(@dateto, '')) LIKE '' OR bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY))
                                                    ORDER BY bco.created_at DESC;
                                                    ">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="txtOrderId" Name="orderid" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selBranch" Name="branchid" DefaultValue="-1" />
        </SelectParameters>
    </asp:SqlDataSource>
            
          </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div><!--card--> 
     <div id="modalViewreturnorder" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center w-100 modal-dialog-scrollable" role="document">
            <div class="modal-content bd-0 tx-14">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="card">
                    <div class="card-header shadow_top">
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvManualPacking" runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                                AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSManualPacking">
                                <Columns>
                                    <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                                        <ItemTemplate>
                                            <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Item" DataField="productname" SortExpression="productname" />
                                    <asp:TemplateField>
                                        <HeaderTemplate>
                                            <%# (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP") %>
                                        </HeaderTemplate>
                                        <ItemTemplate>
                                            <itemtemplate>
                                                <asp:Label ID="lblMRP" runat="server" Text='<%# Eval("item_retail_price", "{0:0.00}") %>' CssClass="text-right"></asp:Label>
                                            </itemtemplate>
                                            <itemstyle width="120" horizontalalign="Right" />
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Rate" DataField="item_price" SortExpression="item_price" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                                    <asp:BoundField HeaderText="Order Qty" DataField="item_order_qty" SortExpression="item_order_qty" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                                    <asp:BoundField HeaderText="Returned Qty" DataField="item_return_qty_requested" SortExpression="item_return_qty_requested"  ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />                                
                                    </Columns>
                                <EmptyDataTemplate>
                                    <div class="text-center">
                                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                        <h6 class="mb-3">No order record available.</h6>
                                    </div>
                                </EmptyDataTemplate>
                            </asp:GridView>
                            <asp:HiddenField  runat="server" ID="hdnorderid"/>
                            <asp:SqlDataSource runat="server" ID="SDSManualPacking" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT item_order_qty,item_return_qty_sellable,
                                        (SELECT stit_SKU FROM `finascop_stock_itemmaster` fs WHERE fs.stit_ID=ro.item_product_id) AS productname,
                                        item_return_qty_damaged,item_return_qty_damagedinTransit,item_return_qty_requested,
                                        item_price,item_retail_price FROM `retaline_customer_order_items`ro 
                                        INNER JOIN `retaline_customer_order` rc ON  ro.customer_order_id=rc.order_id WHERE order_id=@orderid and item_return_qty_requested >0">
                                <SelectParameters>
                                    <asp:ControlParameter ControlID="hdnorderid" Name="orderid" DefaultValue="0" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modalmanualreturnorder" class="modal fade manualreturnorder">
        <div class="modal-dialog modal-dialog-vertical-center w-100 modal-dialog-scrollable-" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title tx-dark">Resolve Manually</h5>
                </div>

                <div class="modal-body pd-y-20 pd-x-20">

                    <div class="row row-sm">
                       <div class="col-12 mb-2">
                           <div class="input_groupe d-flex pr-3 mb-1">
                               <asp:Label runat="server" CssClass="tx-dark tx-14 mr-3" Text="Item Received in Store?"></asp:Label>

                               <div class="input_groupe mr-3">
                                   <asp:RadioButton ID="rbReceivedYes" runat="server" Text="Yes" GroupName="AddsRang" CssClass="mr-0 adsrange form-control-label mb-0 tx-dark"
                                       AutoPostBack="true" OnCheckedChanged="rbReceivedYes_CheckedChanged" />
                               </div>

                               <div class="input_groupe">
                                   <asp:RadioButton ID="rbReceivedNo" AutoPostBack="true" OnCheckedChanged="rbReceivedNo_CheckedChanged" runat="server" Text="No" GroupName="AddsRang" CssClass="mr-0 adsrange" />
                               </div>
                           </div>
                       </div>
                        <asp:HiddenField runat="server" ID="hdordermethod" />
                        <asp:HiddenField  runat="server" ID="hdnbranchid"/>
                        <div class="col-12">
                             <asp:PlaceHolder Visible="false" ID="plcnotrecived" runat="server">
                                 <div id="notReceivedDetails" class="hidden resolve-form">                               
                                <div class="form-group">
                                    <label>Remark:</label>
                                    <asp:TextBox ID="txtRemark" runat="server" TextMode="MultiLine" CssClass="form-control"></asp:TextBox>
                                </div>

                                <div class="form-group">
                                    <label>Resolution:</label>
                                    <asp:DropDownList ID="ddlResolution" runat="server" CssClass="form-control">
                                        <asp:ListItem Value="1">Refunded</asp:ListItem>
                                        <asp:ListItem Value="2">Replaced</asp:ListItem>
                                    </asp:DropDownList>
                                </div>
                            </div>
                                </asp:PlaceHolder>                            
                            <%--resolve-form--%>
                            <asp:PlaceHolder runat="server" ID="plcreceiveddetails" Visible="false">
                                <div id="yestReceivedDetails" class="hidden resolve-form">
                                <div class="form-group">
                                    <label>Received Item on:</label>
                                    <asp:TextBox ID="txtReceivedItemOn" runat="server" CssClass="form-control" TextMode="Date"></asp:TextBox>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Item Brought By:</label>
                                    <asp:DropDownList ID="ddlItemBroughtBy" runat="server" CssClass="form-control">
                                        <asp:ListItem Text="Select" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Customer" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Staff" Value="2"></asp:ListItem>
                                    </asp:DropDownList>
                                </div>                               
                                <div class="form-group">
                                    <asp:TextBox ID="txtstaffname" runat="server" placeholder="Enter name" CssClass="form-control"></asp:TextBox>
                                </div>
                                <div class="form-group">
                                    <label>Item Condition:</label>
                                    <asp:DropDownList ID="ddlItemCondition" runat="server" CssClass="form-control">
                                        <asp:ListItem Text="Select" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Sellable" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Not Sellable" Value="2"></asp:ListItem>
                                    </asp:DropDownList>
                                </div>
                                 <div class="form-group">
                                      <asp:DropDownList ID="ddlitennosellablereason" runat="server" CssClass="form-control">
                                        <asp:ListItem Text="Reason for Non sellable" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Manufacturing Defect" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Damaged in Transit" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Packing Defect" Value="3"></asp:ListItem>
                                        <asp:ListItem Text="Faulted by Customer" Value="4"></asp:ListItem>
                                    </asp:DropDownList>
                                 </div>
                                 <div class="form-group">
                                      <asp:DropDownList ID="ddlresolutions" runat="server" CssClass="form-control">
                                        <asp:ListItem Text="Resolution" Value="0"></asp:ListItem>
                                        <asp:ListItem Text="Refunded" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Replaced" Value="2"></asp:ListItem>                                       
                                    </asp:DropDownList>
                                 </div>
                                 <div class="form-group">

                                 </div>
                                <div class="form-group">
                                    <asp:TextBox ID="txtaditinalconditions" runat="server" placeholder="Additional Remark" TextMode="MultiLine" CssClass="form-control"></asp:TextBox>
                                </div>
                            </div>
                            </asp:PlaceHolder>                            
                        </div><%--col-12--%>
                        <div class="col-12 d-flex align-item-center justify-content-center">
                              <asp:Button runat="server" Text="Submit Resolution" ID="btnsubmiresolution" OnClick="btnsubmiresolution_Click"  CssClass="btn btn-primary mr-2"></asp:Button>
                               <asp:LinkButton runat="server" ID="btnreject"  Text="Cancel" CssClass="btn btn-secondary"></asp:LinkButton>     
                        </div>
                    </div> <%--row--%>
                </div>
                
                
                
                
            </div>
        </div>
    </div>

  <script>
      $(document).ready(function () {          
        var ddlItemCondition = "<%= ddlItemCondition.ClientID %>";
        var ddlNonSellableReason = "<%= ddlitennosellablereason.ClientID %>";
        var ddlResolutions = "<%= ddlresolutions.ClientID %>";
        $("#" + ddlNonSellableReason).closest(".form-group").hide();
        $("#" + ddlResolutions).closest(".form-group").hide();
          $("#" + ddlItemCondition).change(function () {
              if ($(this).val() === "2") {
                  $("#" + ddlNonSellableReason).closest(".form-group").show();
                  $("#" + ddlResolutions).closest(".form-group").hide(); 
                  $("#" + ddlResolutions).html('<option value="">Select Resolution</option>');
              } else {
                  $("#" + ddlNonSellableReason).closest(".form-group").hide().val('');
                  $("#" + ddlResolutions).closest(".form-group").hide().html('<option value="">Select Resolution</option>');
              }
          });
          $("#" + ddlNonSellableReason).change(function () {
              var value = $(this).val();
              var resolutions = {
                  "1": [
                      { text: "Replaced", value: 2 },
                      { text: "Refunded", value: 1 },
                      { text: "Sent to Warrantor", value: 6 },
                      { text: "Advised Customer for Warranty", value: 7 }
                  ],
                  "2": [
                      { text: "Replaced", value: 2 },
                      { text: "Refunded", value: 1 },
                      { text: "Dispute on Delivery", value: "8" }
                  ],
                  "3": [
                      { text: "Replaced", value: 2 },
                      { text: "Refunded", value: 1 }
                  ],
                  "4": [
                      { text: "Return Request Rejected", value: 9 }
                  ]
              };
              var options = resolutions[value] || [];
              var resolutionOptions = '<option value="">Select Resolution</option>';
              options.forEach(function (opt) {
                  resolutionOptions += `<option value="${opt.value}">${opt.text}</option>`;
              });
              $("#" + ddlResolutions).html(resolutionOptions).closest(".form-group").toggle(options.length > 0);
          });
      });

  </script>

     <style>
         .resolve-form textarea {
             height: 80px;
         }
         .manualreturnorder .rdiobox {
             line-height:22px
         }
            .hiddenRow {
                padding: 0 !important;
            }
            tr[data-toggle="collapse"]{
              cursor: pointer;
            }
            tr[aria-expanded="true"] > td .action_arrow .fa-chevron-down::before {
              content: "\f077";
            }
           @media (min-width: 992px) {
             #modalViewreturnorder .modal-dialog {
                 max-width: 900px;
             }
           }            
       </style>
     
</asp:Content>