<%@ Page Language="C#" AutoEventWireup="true" Title="Scheduled Delivery" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="ScheduledDelivery.aspx.cs" Inherits="RetalineProAgent.ScheduledDelivery" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/PackingDelivery">Packing & Delivery</a></li>
    <li class="breadcrumb-item"><a href="/navigations/PackDelivery">Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scheduled Delivery</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="Pending Orders"></asp:Literal></h6>    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
<div class="card card-body">
                   <div class="row row-sm">
                <div class="col-8 col-lg-10">
                <nav class="navbar navbar-expand-lg bg-transparent p-0 justify-content-start">
                <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                  </span>
                </button>
              
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav mr-auto">
                    <li class="nav-item active mx-1">
                        <asp:LinkButton ID="lbtScheduleDelivery" runat="server" typeid="12" OnClick="btnFilterType_Click" CssClass="nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2">Scheduled Delivery</asp:LinkButton>
                    </li>

                    <li class="nav-item dropdown mx-1">
                      <%--<a class="nav-link dropdown-toggle btn btn-block btn-outline-primary btn-sm p-1 px-2 <%= (new int[] { 5,6,7,8,9}).Contains(FilterType)?"active":"" %>" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Other Filters
                      </a>--%>
                      <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <asp:LinkButton ID="lbtnPendingPacking" runat="server" typeid="5" OnClick="btnFilterType_Click" CssClass="dropdown-item">Pending for Packing</asp:LinkButton>
                        <asp:LinkButton ID="lbtnPaymentFailed" runat="server" typeid="6" OnClick="btnFilterType_Click" CssClass="dropdown-item">Payment Failed</asp:LinkButton>
                        <asp:LinkButton ID="lbtnPickupFailed" runat="server" typeid="7" OnClick="btnFilterType_Click" CssClass="dropdown-item">Pickup Failed</asp:LinkButton>
                        <asp:LinkButton ID="lbtnDeliveryFailed" runat="server" typeid="8" OnClick="btnFilterType_Click" CssClass="dropdown-item">Delivery Failed</asp:LinkButton>
                        <asp:HyperLink runat="server" CssClass="dropdown-item" NavigateUrl="/SpotReturn">Returns</asp:HyperLink>
                      <div class="dropdown-divider"></div>
                        <asp:LinkButton ID="lbtnCancelled" runat="server" typeid="9" OnClick="btnFilterType_Click" CssClass="dropdown-item">Cancelled Orders</asp:LinkButton>

                      </div>
                    </li>

                    <%--<li class="nav-item mx-1">
                        <asp:LinkButton ID="lbtnViewAll" runat="server" typeid="0" OnClick="btnFilterType_Click" CssClass="nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2">View All</asp:LinkButton>
                    </li>--%>
                  </ul>
                </div>
              </nav>

            </div>

 <div class="col-4 col-lg-2">
              <div class="float-right">
                  <asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-block btn-primary btn-sm" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download mr-1"></i> Download</asp:LinkButton>
              </div>
            </div>
<%--<div class="col-sm-2 btn-group">
                    <button type="button" class="btn btn-outline-info btn-sm">Other Filters</button>
                    <button type="button" class="btn <%= (FilterType > 4 ? "btn-info" : "btn-outline-info") %> dropdown-toggle dropdown-icon btn-sm" data-toggle="dropdown" aria-expanded="false">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu" style="">
                    </div>
                  </div>--%>
                  


</div>
<%--<hr style="height: 0px; margin: 8px;"/>--%>

            <div class="row row-sm mt-3">
                <div class="col-lg-3 input-group-sm">
                    <label class="mb-0" for="txtSearch">Search by:</label>
                    <asp:TextBox ID="txtOrderId" runat="server" CssClass="form-control" placeholder="Order ID, customer name, email, etc."></asp:TextBox>
                </div>
                <div class="col-lg-3 input-group-sm">
                    <label class="mb-0" for="txtDateFrom">Date - From:</label>
                      <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
                </div>
                <div class="col-lg-3 input-group-sm">
                    <label class="mb-0" for="txtDateTo">Date - To:</label>
                      <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox> 
                </div>
                <div class="col-4 col-lg-1">
                    <label class="mb-0">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm" runat="server">Search</asp:LinkButton>
                </div>
                <div class="col-8 col-lg-2">
                    <label class="mb-0" style="width: 100%">&nbsp;</label>
                    <div class="float-right">
                    <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text=""></asp:Literal>
                    <div class="btn-group ml-2">
                        <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm page-link py-1">
                      <i class="fa fa-angle-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm page-link py-1">
                          <i class="fa fa-angle-right"></i>
                      </asp:LinkButton>
        
                    </div>
                    <!-- /.btn-group -->
                    </div>
                </div>
        
            </div><!--row-->

          <div id="accordion" class="table-responsive" style="margin-top: 8px;">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvPendingOrders" GridLines="None" OnRowDataBound="gvPendingOrders_RowDataBound" runat="server" CssClass="table table-bordered table-hover mg-b-0" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvPendingOrders_DataBound" DataSourceID="SDSPendingOrders" >
                                    
                                    <Columns>
                                        <asp:TemplateField HeaderText="Order ID"><ItemTemplate>
                                            <asp:HyperLink runat="server" Text='<%# Eval("order_order_id") %>' ></asp:HyperLink>
                                            <br />
                                            <small>Total: <b><%# Eval("total") %></b></small>
                                        </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Store"><ItemTemplate>
                                            <i class="fa <%# (Eval("order_branch_type_id").Equals(3) ? "fa-motorcycle" : "fa-truck") %>" aria-hidden="true" style="margin-left: 10px;color: green;"></i>
                                            <%# RetalineProAgent.Service.Common.ShrinkText(Eval("br_Name").ToString(), 20) %><br />
                                            <small>Payment mode: <b><%# Eval("PaymentMode") %></b></small>
                                                                              </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order Date"><ItemTemplate>
                                            <%# Eval("created_at") %><br /><small>Payment Status: <b style="<%# (Eval("payStatus").ToString() == "Success"? "color:green": "color:red") %>" ><%# Eval("payStatus") %></b></small>
                                                                                   </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Delivery Slot"><ItemTemplate>
                                            <%# Eval("slotTime") %><br /><small>Delivery Date: <b><%# Eval("order_slot_date") %></b></small>
                                                                                   </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Customer"><ItemTemplate>
                                            <%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 20) %><br />
                                            <a href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;
                                            <small><%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 28)  %></small>
                                                                                 </ItemTemplate></asp:TemplateField>

                                        <asp:TemplateField HeaderText="Status" SortExpression="order_status"><ItemTemplate><%# Eval("order_status") %><br /><small>Items: <b><%# Eval("itemcount") %></b></small>&nbsp;&nbsp;&nbsp;<small>Worth: <b><%# Eval("order_total_amount") %></b></small></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>

                                                <div class="action_arrow tx-center"  data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne" ><i class="fa fa-chevron-down" aria-hidden="true"></i></div>

                        <asp:Image runat="server" ID="image" ImageAlign="AbsMiddle" width="30" ImageUrl="https://grozeo.azurewebsites.net/images/processing.gif" 
                            Visible='<%# ( (new int[]{5, 11 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>' />

                                            </td></tr><tr><td colspan="6" class="hiddenRow">
                                                <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">

                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{4, 7 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# string.Format("~/AssignOrderPicker.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="Assign order picker"></asp:HyperLink>
                                                <%--<asp:HyperLink runat="server" CssClass="dropdown-item" Visible='<%# ( (new int[]{19, 24}).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# string.Format("~/RevokeOrder.aspx?orderid={0}&toid={1}", Eval("fsto_id"), Eval("fsto_uid")) %>' Text="Revoke"></asp:HyperLink>--%>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{4,5,7 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# string.Format("~/ManualPacking.aspx?fsto_id={0}&orderId={1}", Eval("fsto_id"), Eval("order_order_id")) %>' Text="Manual packing"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{9,12,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/LiveVehicles.aspx?orderid={0}&lat={1}&long={2}&brId={3}&status={4}&quorId={5}", Eval("order_order_id"), Eval("quor_PickupLat"), Eval("quor_PickupLng"), Eval("quor_Deliverybr_id"), Eval("drivetype"), Eval("quor_id")) %>' Text="Assign delivery staff"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{9,12,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/ManualDelivery.aspx?fsto_id={0}", Eval("fsto_id")) %>' Text="Manual delivery"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,24,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/OrderDetails.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View order details"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{23 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/ViewAndUpdate.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View & Update Orders"></asp:HyperLink>
                                                <%--<asp:HyperLink runat="server"  CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# (
                                                        Convert.ToInt32(Eval("StatusId")) == 10 
                                                            ? true: false) %>' Text="Packing Completed"></asp:HyperLink>--%>
                                                    <a class="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa fa-map-marker"></i>&nbsp;
                                            <%--<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 28)  %>--%>
                                                    Click here to view location</a>
                                    <%--<asp:Button runat="server" ID="btnRevoke" OnClick="btnRevoke_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Revoke" transferOrderId='<%# Eval("fsto_id") %>' Visible='<%# ( (new int[]{6}).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' OnClientClick="javascript:confirm('Do you wish to revoke this?');"/>--%>
                                </div>
                                                          </td></tr>
                                                
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        

                                    </Columns><EmptyDataTemplate>No record available</EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSPendingOrders" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSPendingOrders_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DISTINCT bco.order_id,bco.order_order_id,bco.order_slot_date,bco.order_total_amount,order_packedbags_count,bco.order_customer_id,order_branch_type_id,br_Lat,br_Lng,br_ID,
                                                  order_branch_id,so.fsto_id,so.fsto_status,so.fsto_ordertype,so.fstr_id,so.fsto_uid,bco.order_slot_id,bco.total, bco.order_payment_status, bco.payment_mode,
                                                  (SELECT COUNT(fsto_id) FROM finascop_stock_transfer_order_details WHERE fsto_id=so.fsto_id) AS itemcount,
                                                  br_Name,bco.status_id AS StatusId,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on, CASE WHEN bco.payment_mode = 1 THEN 'Pay On Delivery' WHEN bco.payment_mode = 2 THEN 'Online' 
                                                  WHEN bco.payment_mode = 3 THEN 'Wallet' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' WHEN bco.payment_mode = 5 THEN 'Online with Wallet' 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' WHEN bco.payment_mode = 7 THEN 'Cash on Delivery' ELSE '' END AS PaymentMode,IF(bco.order_payment_status IS NOT NULL,bco.order_payment_status,(CASE WHEN bco.payment_mode = 1 THEN 'Yet to be collected' WHEN bco.payment_mode = 2 THEN 'Success' 
                                                  WHEN bco.payment_mode = 3 THEN 'Success' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' WHEN bco.payment_mode = 5 THEN 'Success' 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' WHEN bco.payment_mode = 7 THEN 'Yet to be collected' ELSE '' END )) AS payStatus,
                                                  TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
                                                  admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32, order_latitude AS lat, order_longitude AS lng,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,drivetype,order_land_mark,slotTime,
                                                  CASE
                                                  WHEN order_method = 1 THEN 'Drive Delivery'
                                                  WHEN order_method = 2 THEN 'Customer Collect'
                                                  WHEN order_method = 3 THEN 'Courier Delivery'
                                                  END AS order_method, order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at, IFNULL(order_post, order_pin) AS pin,
                                                  order_latitude,order_longitude, cust_customer_name, cust_mobile, order_house_name, order_city, order_address
                                                  FROM retaline_customer_order bco INNER JOIN retaline_customer c ON c.cust_id=bco.order_customer_id 
                                                  INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                                                  INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                                                  INNER JOIN finascop_branch ON br_ID = order_branch_id and finascop_branch.br_storeGroup=@storegroup
                                                  INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = bco.order_id
                                                  LEFT JOIN (SELECT quor_RefNo,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,
                                                  IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','')) AS drivetype FROM qugeo_order) qo ON qo.quor_RefNo = bco.order_order_id
                                                  LEFT JOIN (SELECT rbds_id, CONCAT(DATE_FORMAT(rbds_time_from, '%H:%i'), '-', DATE_FORMAT(rbds_time_to, '%H:%i')) AS slotTime FROM retaline_branch_delivery_slot) slot ON slot.rbds_id = bco.order_slot_id
                                                  WHERE bco.status_id > 0 AND bco.status_id >= 4 and (ifnull(@filterType, 0) = 0 
                                                     or (@filterType = 1 and bco.status_id NOT IN(17,18,19,23,24,34))  
                                                     or (@filterType = 2 and bco.status_id IN(8,9,10,11,12,13))  
                                                     or (@filterType = 3 and bco.status_id IN(14,15,16))  
                                                     or (@filterType = 4 and bco.status_id IN(17,18)) or (@filterType = 5 and bco.status_id IN(4,5,6,7))  or (@filterType = 6 and bco.status_id IN(21, 2)) or(@filterType = 12 and bco.status_id IN(9) and bco.order_slot_id > 0)
                                                     or (@filterType = 7 and bco.status_id IN(10,7)) or (@filterType = 8 and bco.status_id IN(16)) or (@filterType = 9 and bco.status_id IN(19, 24)) or(@filterType = 10 and bco.status_id IN(23))
                                                    )  AND (trim(ifnull(@orderid, '')) like '' or bco.order_order_id like CONCAT('%', @orderid, '%') or br_Name like CONCAT('%', @orderid, '%')
                                                         or cust_customer_name like CONCAT('%', @orderid, '%') or cust_mobile like CONCAT('%', @orderid, '%') or order_customer_email like CONCAT('%', @orderid, '%')
                                                          or order_house_name like CONCAT('%', @orderid, '%') or order_city like CONCAT('%', @orderid, '%') or order_address like CONCAT('%', @orderid, '%')
                                                    ) AND (trim(ifnull(@datefrom, '')) like '' or bco.created_at >=CONVERT(@datefrom, DATE)) AND (trim(ifnull(@dateto, '')) like '' or bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY)) ORDER BY bco.created_at desc"
        OnSelecting="SDSPendingOrders_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
            <asp:ControlParameter ControlID="txtOrderId" Name="orderid" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
            
          </div><!-- table-responsive -->
        </div><!-- section-wrapper -->


    
    <asp:GridView ID="gvForExportOnly" runat="server" AutoGenerateColumns="false">
        <Columns>
            <asp:BoundField HeaderText="Branch" DataField="br_Name" SortExpression="br_Name" />
            <asp:BoundField HeaderText="Date" DataField="created_at" SortExpression="created_at" />
            <asp:BoundField HeaderText="Customer Name" DataField="cust_customer_name" SortExpression="cust_customer_name" />
            <asp:BoundField HeaderText="Customer Number" DataField="cust_mobile" SortExpression="cust_mobile" />
            <asp:BoundField HeaderText="Delivery Address" DataField="order_address" SortExpression="order_address" />
            <asp:BoundField HeaderText="Delivery City" DataField="order_city" SortExpression="order_city" />
            <asp:BoundField HeaderText="Delivery Pin" DataField="pin" SortExpression="pin" />
            <asp:BoundField HeaderText="Delivery Landmark" DataField="order_land_mark" SortExpression="order_land_mark" />
            <asp:BoundField HeaderText="Total" DataField="total" SortExpression="total" />
            <asp:BoundField HeaderText="Payment Mode" DataField="PaymentMode" SortExpression="PaymentMode" />
            <asp:BoundField HeaderText="Payment Status" DataField="order_payment_status" SortExpression="order_payment_status" />
            <asp:BoundField HeaderText="Order Status" DataField="order_status" SortExpression="order_status" />
                                        
        </Columns>
    </asp:GridView>
    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
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
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/PendingOrders";
            });
        });
    </script>

</asp:Content>



