<%@ Page Language="C#" AutoEventWireup="true" Title="Order Delivery" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="OrderDelivery.aspx.cs" Inherits="RetalineProAgent.OrderDelivery" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>

<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery</li>
</asp:Content>--%>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Order Delivery"></asp:Literal> from
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Ensure smooth and on-time delivery of orders to customers</p>
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
                <div class="col-12 col-sm-3 col-lg-7 mb-2 pr-3 pr-lg-0 mb-md-0 d-flex flex-wrap flex-lg-nowrap align-items-end">
                    <nav class="navbar col navbar-expand-lg bg-transparent col-md-auto p-0 justify-content-start align-items-end">
                        <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                        <button class="navbar-toggler p-0 collapsed" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                  </span>
                </button>
                        <div class="navbar-collapse flex-wrap collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav mr-auto pt-2 pt-lg-0">
                                 <li class="nav-item ml-0 mr-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbnpending" runat="server" typeid="0" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary btn-sm active">Pending<span class="sr-only">(current)</span></asp:LinkButton>
                                </li>
                                <li class="nav-item ml-0 mr-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnDelivery" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary btn-sm">Hyper Delivery</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtScheduleDelivery" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary btn-sm">Scheduled Delivery</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnDelivered" runat="server" typeid="4" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary btn-sm">Delivered</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnInTransit" runat="server" typeid="5" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary btn-sm">In Transit</asp:LinkButton>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    
                </div>
                <div class="col-12 col-sm-9 col-lg-5 align-items-start flex-wrap flex-sm-nowrap align-items-lg-end d-flex justify-content-start justify-content-md-end">

                    <div id="scheduleDeliv" runat="server" visible="false" class="col-md-auto mb-2 mr-2 mr-lg-0 mb-sm-0 d-flex px-0 px-lg-1 justify-content-md-end">
                            <asp:LinkButton id="lbnDelivStaff" Enabled="true" CssClass="btn btn-inline-block btn_assign btn-primary btn-sm ml-0 ml-lg-1 mr-0 mr-lg-1 btn-disabled" Text="Assign Delivery Staff" OnClick="lbnDelivStaff_Click" runat="server" />
                            <asp:LinkButton id="lbnManualDeliv" Enabled="true" CssClass="btn btn-inline-block btn_assign btn-primary btn-sm ml-0 ml-lg-1 mr-0 mr-lg-1 btn-disabled" Text="Manual Delivery" OnClick="lbnManualDeliv_Click" runat="server"/>
                        </div>
                    
                    <div class="float-md-right d-flex">
                        <asp:LinkButton runat="server" ID="lbtndeliveryonhold" typeid="11" CssClass="btn btn-inline-block btn-outline-primary mr-2" OnClick="btnFilterType_Click" > Delivery On Hold</asp:LinkButton>
                        <asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-inline-block btn-outline-primary" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download mr-1"></i> Download</asp:LinkButton>
                    </div>
                </div>
            </div>
            <div class="row row-sm mt-2">



                <div class="col-12 col-lg-5">
                    <div class="row row-sm">
                        <div class="col-sm-6 input-group mg-b-10 mg-lg-b-0">
                            <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store</label>
                            <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                                    <asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                            </asp:PlaceHolder>
                            <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                ProviderName="MySql.Data.MySqlClient">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                    <asp:Parameter Name="branchid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                        <div class="col-sm-6 form-group mb-2 mb-lg-0">
                            <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
                            <div style="display: none;">
                                <input type="text" name="name_emailField" />
                                <input type="password" name="passwordFiele" />
                            </div>
                            <asp:TextBox ID="txtOrderId" runat="server" autocomplete="off" CssClass="form-control" placeholder="Order ID, customer name, etc." name="uniqueOrderId"></asp:TextBox>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="row row-sm">
                        <div class="col-sm-3 input-group mg-b-10 mg-sm-b-0">
                            <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom">From</label>
                            <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                        </div>

                        <div class="col-sm-3 input-group mg-b-10 mg-sm-b-0">
                            <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateTo">To</label>
                            <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                        </div>

                        <div class="col-sm-3 input-group mg-b-10 mg-sm-b-0" runat="server" id="slotContainer" visible="false">
                            <label runat="server" class="tx-dark mb-1 w-100">Select Slot</label>
                            <asp:PlaceHolder ID="plcSelectSlotl" runat="server">
                                <asp:DropDownList ID="selSlot" AutoPostBack="true" CssClass="form-control select2" AppendDataBoundItems="true" DataSourceID="SDSSlot" DataTextField="formatted_slot_date" DataValueField="formatted_slot_date" runat="server">
                                    <asp:ListItem Text="Select Delivery Slot" Value="" />
                                </asp:DropDownList>
                            </asp:PlaceHolder>
                            <asp:SqlDataSource ID="SDSSlot" runat="server" OnSelecting="SDSSlot_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT order_id,rbds_id,CONCAT(DATE_FORMAT(order_slot_date, '%d %b %Y'), ' - ', slot.rbds_time_from, ' to ', slot.rbds_time_to) AS formatted_slot_date,
                                order_slot_date,slot.rbds_time_from,slot.rbds_time_to,storegroup_id FROM retaline_customer_order bco
                                INNER JOIN (SELECT rbds_id, rbds_time_from, rbds_time_to FROM retaline_branch_delivery_slot) slot ON slot.rbds_id = bco.order_slot_id
                                WHERE order_slot_id > 0 AND storegroup_id = @storegroupid AND bco.order_slot_date IS NOT NULL AND bco.status_id IN(9) AND bco.order_slot_id > 0 GROUP BY order_slot_date,slot.rbds_time_from,slot.rbds_time_to,storegroup_id 
                                ORDER BY formatted_slot_date ASC" ProviderName="MySql.Data.MySqlClient">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>

                        <div class="col-sm-3 d-flex align-items-sm-end d-flex align-items-end">
                            <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-sm-0" runat="server">Search</asp:LinkButton>
                            <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2" PostBackUrl="~/Tenant/OrderDelivery.aspx" Text="Reset" />
                        </div>

                    </div>
                </div>
            </div>
        </div><!-- card-head -->
        <div class="card-body">
          <div id="accordion" class="table-responsive">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvPendingOrders" GridLines="None" OnRowDataBound="gvPendingOrders_RowDataBound" runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvPendingOrders_DataBound" DataSourceID="SDSPendingOrders" >
                                    
                                    <Columns>
                                        <asp:TemplateField Visible="false">
                                            <HeaderTemplate>
                                                <asp:CheckBox ID="chkAll" runat="server" AutoPostBack="true" OnCheckedChanged="chkAll_CheckedChanged"/>
                                            </HeaderTemplate>
                                            <ItemTemplate>
                                            <asp:CheckBox ID="chkDelivery" orderId='<%# Eval("order_order_id") %>' mapLat='<%# Eval("quor_PickupLat") %>' mapLong='<%# Eval("quor_PickupLng") %>' orderType='<%# Eval("drivetype") %>' quorId='<%# Eval("quor_id") %>' orderBranchId='<%# Eval("order_branch_id") %>' fstoId='<%# Eval("fsto_id") %>' runat="server" AutoPostBack="true" OnCheckedChanged="chkDelivery_CheckedChanged"/>
                                        </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order ID"><ItemTemplate>
                                            <asp:HyperLink runat="server" Text='<%# Eval("order_order_id") %>' ></asp:HyperLink>
                                            <br />
                                            <small>Total: <b><%# Eval("total") %></b></small>
                                        </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Customer" ><ItemTemplate>
                                            <asp:Label ID="Label2" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 20) %>' ToolTip ='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 100) %>'></asp:Label>
                                            <br />
                                            <a href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i></a>&nbsp;
                                            <small><%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 20)  %></small>
                                                                                 </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order Date"><ItemTemplate>
                                            <%# Eval("created_at") %><br /><small>Status: 
                                                <b 
                                                    <%--style="<%# (Eval("payStatus").ToString() == "Success"? "color:green": "color:red") %>"--%>
                                                    >
                                                    <%# Eval("payStatus") %></b></small>
                                                                                   </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Delivery Slot" Visible="false"><ItemTemplate>
                                            <%# Eval("slotTime") %><br /><small>Delivery Date: <b><%# Eval("formattedslotdate") %></b></small>
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

                                        <asp:TemplateField HeaderText="Status" SortExpression="statusDeliv"><ItemTemplate>
                                            <asp:Label ID="Label3" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(Eval("statusDeliv").ToString(), 15) %>' ToolTip ='<%# Bind("statusDeliv") %>'></asp:Label><br /><small>Items: <b><%# Eval("itemcount") %></b></small>&nbsp;&nbsp;&nbsp;<small>Worth: <b><%# Eval("order_total_amount") %></b></small></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Reason" HeaderStyle-Width="20%" Visible="false"><ItemTemplate>
                                            <%# Eval("statusDeliv") %><br /></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>

                                                <div class="action_arrow tx-center"  data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne" ><i class="fa fa-chevron-down" aria-hidden="true"></i></div>

                        <asp:Image runat="server" ID="image" ImageAlign="AbsMiddle" width="13" ImageUrl="/content/images/processing.gif" 
                            Visible='<%# (Eval("qugeoStatus") != DBNull.Value && (new int[] { 23, 31, 32 }).Contains(Convert.ToInt32(Eval("qugeoStatus")))) %>' />

                                            </td></tr><tr><td colspan="8" class="hiddenRow">
                                                <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">

                                                <%--<asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{9,12,13,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) && (Convert.ToString(Eval("order_method"))  == "3" ? false : true) && (FilterType == 2 ? false : true)  %>' NavigateUrl='<%# String.Format("/Tenant/LiveVehicles.aspx?orderid={0}&lat={1}&long={2}&brId={3}&status={4}&quorId={5}", Eval("order_order_id"), Eval("quor_PickupLat"), Eval("quor_PickupLng"), Eval("quor_Deliverybr_id"), Eval("drivetype"), Eval("quor_id")) %>' Text="Assign delivery staff"></asp:HyperLink>--%>
                                                    <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# Eval("qugeoStatus") != DBNull.Value && ( (new int[]{22 }).Contains(Convert.ToInt32(Eval("qugeoStatus")))
                                    ? true: false) && (Convert.ToString(Eval("order_method"))  == "3" ? false : true) && (FilterType == 2 ? false : true)  %>' 
                                              NavigateUrl='<%# String.Format("/Tenant/LiveVehicles.aspx?orderid={0}&lat={1}&long={2}&brId={3}&status={4}&quorId={5}", Eval("order_order_id"), Eval("quor_PickupLat"), Eval("quor_PickupLng"), Eval("quor_Deliverybr_id"), Eval("drivetype"), Eval("quor_id")) %>' Text="Assign delivery staff"></asp:HyperLink>


                                                    <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%#Eval("StatusId") != DBNull.Value && ( (new int[]{9,12,13,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) && (FilterType == 2 ? false : true) %>' NavigateUrl='<%# String.Format("/Tenant/ManualDelivery.aspx?fsto_id={0}", Eval("fsto_id")) %>' Text="Manual delivery"></asp:HyperLink>

                                                    <asp:Button runat="server" UseSubmitBehavior="false" CausesValidation="false" ID="btnmanualSchedule" OnClick="btnmanualSchedule_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Manual Schedule" orderId='<%# Eval("order_id") %>' quorId='<%# Eval("quor_id") %>' qugeoStatus='<%# Eval("qugeoStatus") %>' Visible='<%# ( (new string[]{"9", "10", "11", "12", "13", "14", "23", "24", "25", "26", "27", "28", "29", "35", "36", "37"}).Contains(Eval("qugeoStatus").ToString())
                                    ? true: false) && (Convert.ToString(Eval("order_method"))  == "3" ? false : true) && (FilterType == 1 ? false : true) %>' OnClientClick="ConfirmManualSch()"/>
                                                    <%--<asp:Button runat="server" ID="btnFailed" OnClick="btnFailed_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Failed" qugeoStatus='<%# Eval("qugeoStatus") %>' Visible='<%# ( (new int[]{10, 11, 38}).Contains(Convert.ToInt32(Eval("qugeoStatus")))
                                    ? true: false) %>' OnClientClick="ConfirmFailed()"/>
                                                    <asp:Button runat="server" ID="btnDelivered" OnClick="btnDelivered_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Delivered" qugeoStatus='<%# Eval("qugeoStatus") %>' Visible='<%# ( (new int[]{38}).Contains(Convert.ToInt32(Eval("qugeoStatus")))
                                    ? true: false) %>' OnClientClick="ConfirmDelivered()"/>--%>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,24,27,28,29,30,31,32,33,34,55 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/Tenant/OrderDetailsNew.aspx?orderid={0}&toid={1}&ordId={2}&status={3}&page=Delivery", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id"), Eval("qugeoStatus")) %>' Text="View order details"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{23 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>' NavigateUrl='<%# String.Format("/Tenant/ViewAndUpdate.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View & Update Orders"></asp:HyperLink>
                                                    <a class="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i>&nbsp;
                                                    Click here to view location</a>
                                                     <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# String.Format("/Tenant/invoice.aspx?ordId={0}", Eval("order_id")) %>' Text="Invoice" Visible='<%# ( (new int[]{8,9,10,11,12,13,14,15,16,17,18,20,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>'></asp:HyperLink>
                                                    <asp:LinkButton ID="lbtnOrderDetails" order_id='<%# Eval("order_id") %>' CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" OnClick="lbtnOrderDetails_Click" runat="server"  Visible='<%# Eval("qugeoStatus") != DBNull.Value && ((new int[]{22 }).Contains(Convert.ToInt32(Eval("qugeoStatus"))) ? true: false) && (FilterType == 2 ? false : true) && (Convert.ToString(Eval("order_method"))  == "3" ? true : false) %>'  quorId='<%# Eval("quor_id") %>' Text="Update delivery details"></asp:LinkButton>

                                                    <asp:LinkButton ID="lbtnDeliveryUpdate" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" OnClick="lbtnDeliveryUpdate_Click" runat="server" Visible='<%# Eval("qugeoStatus") != DBNull.Value && (new int[] { 9 }).Contains(Convert.ToInt32(Eval("qugeoStatus"))) %>' orderId='<%# Eval("order_id") %>'  quorId='<%# Eval("quor_id") %>' paymentMode='<%# Eval("payment_mode") %>' Text="Update delivery"></asp:LinkButton>
                                          <asp:LinkButton runat="server" ID="lbtmanagedelivery" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Manage Delivery" OnClick="lbtmanagedelivery_Click" Visible='<%#Eval("status_id") != DBNull.Value && Eval("status_id").ToString() == "55" %>' quorId='<%# Eval("quor_id") %>' orderId='<%# Eval("order_id") %>'></asp:LinkButton>
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
                                 SelectCommand = "SELECT DISTINCT bco.order_id,bco.order_method, ' '+ IFNULL(qo.quor_Status, 0) AS qugeoStatus,bco.order_order_id,bco.order_slot_date,DATE_FORMAT(bco.order_slot_date, '%d %b %Y') AS formattedslotdate,bco.order_total_amount,order_packedbags_count,bco.order_customer_id,order_branch_type_id,br_Lat,br_Lng,br_ID,bco.status_id,
                                                  order_branch_id,bco.storegroup_id,so.fsto_id,so.fsto_status,so.fsto_ordertype,so.fstr_id,so.fsto_uid,bco.order_slot_id,bco.total, bco.order_payment_status, bco.payment_mode,
                                                  (SELECT COUNT(fsto_id) FROM finascop_stock_transfer_order_details WHERE fsto_id=so.fsto_id) AS itemcount,
                                                  br_Name,bco.status_id AS StatusId,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on, CASE WHEN bco.payment_mode = 1 THEN 'Pay On Delivery' WHEN bco.payment_mode = 2 THEN 'Online' 
                                                  WHEN bco.payment_mode = 3 THEN 'Wallet' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' WHEN bco.payment_mode = 5 THEN 'Online with Wallet' 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' WHEN bco.payment_mode = 7 THEN 'Cash on Delivery' ELSE '' END AS PaymentMode,CASE WHEN (bco.payment_mode = 1 AND bco.status_id != 19) THEN 'To be collected' 
                                                  WHEN (bco.payment_mode = 1 AND bco.status_id = 19) THEN 'Cancelled' 
                                                  WHEN bco.payment_mode = 2 THEN (COALESCE('Paid Online' ,'Payment Failed',bco.order_payment_status)) 
                                                  WHEN bco.payment_mode = 3 THEN 'Success' WHEN bco.payment_mode = 4 THEN 'COD with Wallet' 
                                                  WHEN bco.payment_mode = 5 THEN (COALESCE('Paid Online with Wallet' ,'Payment Failed',bco.order_payment_status)) 
                                                  WHEN bco.payment_mode = 6 THEN 'Online on Delivery' 
                                                  WHEN (bco.payment_mode = 7 AND bco.status_id != 19) THEN 'To be collected' 
                                                  WHEN (bco.payment_mode = 7 AND bco.status_id = 19) THEN 'Cancelled'  ELSE '' END  AS payStatus,
                                                  TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
                                                  admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32, order_latitude AS lat, order_longitude AS lng,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,drivetype,order_land_mark,slotTime,
                                                  CASE
                                                  WHEN order_method = 1 THEN 'Drive Delivery'
                                                  WHEN order_method = 2 THEN 'Customer Collect'
                                                  WHEN order_method = 3 THEN 'Courier Delivery'
                                                  END AS order_method, order_HasReturn,order_ItemsReturned,order_ReturnVerified,DATE_FORMAT(bco.created_at,'%d %b %Y  %H:%i') AS created_at, IFNULL(order_post, order_pin) AS pin,
                                                  order_latitude,order_longitude, cust_customer_name, cust_mobile, order_house_name, order_city, order_address, CONCAT(order_house_name,', ',order_city,', ',order_post) AS orderAddress,
                                                  (SELECT dls_DelStatus FROM qugeo_deliverystatus WHERE dls_ID=IFNULL(qo.quor_Status, 0)) AS statusDeliv
                                                  FROM retaline_customer_order bco INNER JOIN retaline_customer c ON c.cust_id=bco.order_customer_id 
                                                  INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                                                  INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                                                  INNER JOIN finascop_branch ON br_ID = order_branch_id and finascop_branch.br_storeGroup=@storegroup
                                                  INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = bco.order_id
                                                  LEFT JOIN (SELECT quor_RefNo,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,
                                                  IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','PICKUP')) AS drivetype FROM qugeo_order) qo ON qo.quor_RefNo = bco.order_order_id
                                                  LEFT JOIN (SELECT rbds_id, CONCAT(rbds_time_from, ' to ', rbds_time_to) AS slotTime FROM retaline_branch_delivery_slot) slot ON slot.rbds_id = bco.order_slot_id
                                                  WHERE (bco.storegroup_id=@storegroup OR finascop_branch.br_storeGroup=@storegroup) AND (@branchId <= 0 or order_branch_id=@branchId) AND bco.status_id > 0 AND bco.status_id >= 4 and (ifnull(@filterType, 0) = -1 
                                                     or (@filterType = 0 and qo.quor_Status IS NOT NULL AND qo.quor_Status NOT IN (15, 38, 40))
                                                     or (@filterType = 1 and IFNULL(qo.quor_Status, 0) NOT IN(15, 38, 40) and bco.order_slot_id IS NULL)  
                                                     or (@filterType = 2 and bco.status_id IN(9) and bco.order_slot_id > 0)  
                                                     or (@filterType = 3 and bco.status_id IN(14,15,16))  
                                                     or (@filterType = 4 and bco.status_id IN(17,18)) or (@filterType = 5 AND ((qo.quor_Type IN (2,3,4) AND IFNULL(qo.quor_Status, 0)=9) OR (qo.quor_Type=1 AND IFNULL(qo.quor_Status, 0) IN 
                                                     (9,10,11,12,13,14,23,24,25,26,27,28,29,32,33,34,35,36,37,38))))  or (@filterType = 6 and bco.status_id IN(21, 2))
                                                     or (@filterType = 7 and bco.status_id IN(10,7)) or (@filterType = 8 and bco.status_id IN(16)) or (@filterType = 9 and bco.status_id IN(19, 24)) or(@filterType = 10 and bco.status_id IN(23)) OR (@filterType =11 AND bco.status_id IN(55))
                                                    )  AND (trim(ifnull(@orderid, '')) like '' or bco.order_order_id like CONCAT('%', @orderid, '%') or br_Name like CONCAT('%', @orderid, '%')
                                                         or cust_customer_name like CONCAT('%', @orderid, '%') or cust_mobile like CONCAT('%', @orderid, '%') or order_customer_email like CONCAT('%', @orderid, '%')
                                                          or order_house_name like CONCAT('%', @orderid, '%') or order_city like CONCAT('%', @orderid, '%') or order_address like CONCAT('%', @orderid, '%')
                                                    ) AND (trim(ifnull(@datefrom, '')) like '' or bco.created_at >=CONVERT(@datefrom, DATE)) AND (trim(ifnull(@dateto, '')) like '' or bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY)) 
                                    AND (@slotDate IS NULL OR @slotDate = '' OR TRIM(CONCAT(DATE_FORMAT(order_slot_date, '%d %b %Y'), ' - ', slotTime)) LIKE TRIM(@slotDate))
                                     ORDER BY bco.created_at desc"
        OnSelecting="SDSPendingOrders_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
            <asp:ControlParameter ControlID="txtOrderId" Name="orderid" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selBranches" PropertyName="Text" Name="branchId" />
            <asp:ControlParameter ControlID="selSlot" Name="slotDate" PropertyName="SelectedValue" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
          </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div><!-- card -->

    <asp:HiddenField ID="hidLat" runat="server" />
              <asp:HiddenField ID="hidLong" runat="server" />
              <asp:HiddenField ID="hidOrderId" runat="server" />
              <asp:HiddenField ID="hidStatus" runat="server" />
              <asp:HiddenField ID="hidQuorId" runat="server" />
    
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


    <div id="modalmanualDelivery" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-inverse ">Manual Delivery</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
              <asp:HiddenField ID="hidmanualDeliv" runat="server"/>
          </div>
          <div class="modal-body">
              <div class="row row-sm">
                  <div class="col-4 mb-2"><label>Date: <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtDate" Enabled="false" runat="server" TextMode="Date" CssClass="form-control" deliveredDate="delivered_date" />
                  </div>
                  <div class="col-4 mb-2"><label>Time: <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtTime" Enabled="false" runat="server" TextMode="Time" CssClass="form-control" deliverderTime="delivered_time"/>
                  </div>
                    <div class="col-4 mb-2"><label>Delivered By: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtDelivBoy" runat="server" CssClass="form-control" placeholder="Delivered By" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDelivBoy" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Delivered by is required" ValidationGroup="ManualSchDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                  </div>
                      <div class="col-12"><label>Remarks: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtRemarks" runat="server" CssClass="form-control" placeholder="Remarks" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRemarks" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Remarks is required" ValidationGroup="ManualSchDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                  </div>
                </div>
            <%--<h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Why We Use Electoral College, Not Popular Vote</a></h5>
            <p class="mg-b-5">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>--%>
          </div>
          <div class="modal-footer d-flex flex-wrap justify-content-lg-end">
            <asp:Button runat="server" ID="Button1" OnClick="btnManualDeliverySubmit_Click" CssClass="btn btn-primary" Text="Submit" ValidationGroup="ManualSchDelivery" />&nbsp;
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


    <div id="modalDeliveryStaff" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-inverse tx-bold">Delivery Staffs</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
              <asp:HiddenField ID="hidDelivStaff" runat="server"/>
          </div>
          <div class="modal-body">
               <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvLiveVehicles" runat="server" CssClass="table table-borderd gridview_table" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" 
                                    DataSourceID="ODSLiveVehicles">
                                    <Columns>
                                        <asp:TemplateField Visible="false">
                                            <ItemTemplate><asp:HiddenField ID="hidAPIId" runat="server" Value='<%# Eval("VId") %>' /></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="V. Reg. No." DataField="VRegNo" SortExpression="VRegNo" />
                                        <asp:BoundField HeaderText="D. Name" DataField="DName" SortExpression="DName" />
                                        <asp:BoundField HeaderText="V. Type" DataField="VType" SortExpression="VType" />
                                        <asp:BoundField HeaderText="Last Updation" DataField="LastUpdation" SortExpression="LastUpdation" />
                                        <asp:TemplateField HeaderText = "" ItemStyle-Width="100">
                                            <ItemTemplate>
                                                <asp:LinkButton runat="server" OnClick="btnDeliveryBoyAssign_Click" CausesValidation="false" vehicleId='<%# Eval("VId") %>' CssClass="btn btn-primary float-right" Text="Assign" Width="70px" ></asp:LinkButton>
                                                <asp:Button runat="server" ID="btnAssign" OnClick="btnDeliveryBoyAssign_Click" Visible="false" CausesValidation="false" vehicleId='<%# Eval("VId") %>' CssClass="btn btn-primary float-right" Text="Assign" Width="70px" />&nbsp;
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                     <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No vehicle available. Please add drivers or verify that the registered vehicle/driver is logged in and available within the delivery radius.</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    </asp:GridView>

                   <asp:ObjectDataSource ID="ODSLiveVehicles" runat="server" TypeName="RetalineProAgent.Core.Services.APIService" SelectMethod="LoadVehicle">
                       <SelectParameters>
                           <asp:QueryStringParameter Name="branchid" QueryStringField="brId" />
                           <asp:QueryStringParameter Name="pickupLat" QueryStringField="lat" />
                           <asp:QueryStringParameter Name="pickupLng" QueryStringField="long" />
                           <asp:QueryStringParameter Name="UserType" QueryStringField="userType" DefaultValue="0" />
                           <asp:QueryStringParameter Name="UserId" QueryStringField="userId" DefaultValue="0" />
                       </SelectParameters>
                   </asp:ObjectDataSource>

                                <%--<asp:SqlDataSource runat="server" ID="SDSLiveVehicles" ProviderName="MySql.Data.MySqlClient"
                                 SelectCommand = "SELECT d_Name FROM qugeo_driver WHERE d_Active=1 AND br_id=@branchid"
OnSelecting="SDSLiveVehicles_Selecting">
        <SelectParameters>
            <asp:Parameter Name="branchid" />
            <asp:ControlParameter Name="search" ControlID="txtSearch" Type="String" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>--%>
               </div>
                </div>
            <%--<h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Why We Use Electoral College, Not Popular Vote</a></h5>
            <p class="mg-b-5">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>--%>
          </div>
          <%--<div class="modal-footer">
            <asp:Button runat="server" ID="Button2" OnClick="btnManualDeliverySubmit_Click" CssClass="btn btn-success" Text="Submit" order_id='<%# Eval("order_id") %>' />&nbsp;
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>--%>
        </div>
      </div><!-- modal-dialog -->
    <%--</div><!-- modal -->--%>
    <asp:HiddenField ID="hdnQuorId" runat="server" />
    <div id="modalDeliveryDetails" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse ">Order Dispatch Details</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12 mb-2" id="divCourierDropdown">
                            <label class="w-100 text-left tx-dark">Select Courier <span class="tx-danger">*</span></label>
                            <asp:DropDownList ID="selCourier" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCourier" DataTextField="mst_courier_name" AppendDataBoundItems="true" DataValueField="mst_courier_id">
                                <asp:ListItem Text="Select Cargo / Courier" Value=""></asp:ListItem>
                            </asp:DropDownList>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCourier" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT 
                                    COALESCE(mst_courier_id, -1) AS mst_courier_id, -- Replace NULL with -1
                                    COALESCE(mst_courier_name, 'Unknown') AS mst_courier_name -- Replace NULL with 'Unknown'
                                FROM 
                                    mst_courier 
                                WHERE 
                                    STATUS = 1 
                                UNION 
                                SELECT 
                                    -1 AS mst_courier_id, 
                                    'Others' AS mst_courier_name"></asp:SqlDataSource>
                            <asp:RequiredFieldValidator ValidationGroup="CreateDelivery" ControlToValidate="selCourier" ForeColor="Red" ErrorMessage="Select Cargo / Courier" runat="server"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 mb-2 textbox-container" id="divCourierTextbox" style="display: none;">
                            <label class="w-100 text-left tx-dark">Enter Courier Name <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtCourierName" runat="server" CssClass="form-control" placeholder="Enter Courier Name" AutoComplete="off"></asp:TextBox>
                            <%--<asp:RequiredFieldValidator ID="txtCourierNameValidator" ValidationGroup="CreateDelivery" ControlToValidate="txtCourierName" ForeColor="Red" ErrorMessage="Enter courier name" runat="server" Display="Dynamic" EnableClientScript="true"></asp:RequiredFieldValidator>--%>
                        </div>
                        <div class="col-12 mb-2" id="divTrackingURL">
                            <label class="w-100 text-left tx-dark">Tracking URL if any</label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtTrackingURL" runat="server" CssClass="form-control" placeholder="Enter tracking URL" AutoComplete="off"></asp:TextBox>
                        </div>
                        <div class="col-12 col-md-6"> 
                            <label class="w-100 text-left tx-dark">Tracking Number <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtTrackingNo" runat="server" CssClass="form-control" placeholder="Enter tracking number" autocomplete="off" />
                            <asp:RequiredFieldValidator ValidationGroup="CreateDelivery" ControlToValidate="txtTrackingNo" ForeColor="Red" ErrorMessage="Enter tracking number" runat="server"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="w-100 text-left tx-dark">Date of Booking <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtDispatchDate" runat="server" CssClass="form-control" TextMode="Date" placeholder="Date of Booking" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDispatchDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date of booking is required" ValidationGroup="CreateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-center">
                                <asp:Button runat="server" ID="btnAdd" CssClass="btn btn-primary mr-1" OnClick="btnAdd_Click" Text="Order Dispatched" order_id='<%# Eval("order_id") %>' quorId='<%# Eval("quor_id") %>' ValidationGroup="CreateDelivery" />
                                <%--<a href="/Tenant/OrderDelivery" class="btn btn-secondary">Cancel</a>
                                <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />--%>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <asp:HiddenField ID="hdnPayementMode" runat="server" />
    <asp:HiddenField ID="hdnOrderId" runat="server" />
    <div id="modalDeliveryUpdate" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-inverse">Delivery Update</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row row-sm" runat="server" id="dvPaymentMode">
                    <div class="col-12 col-md-6 d-flex align-items-center">
                        <asp:RadioButton ID="rbCash" CssClass="form-control-label mb-0 mr-3 tx-dark" GroupName="rbgStore" runat="server" Text="Cash" />
                        <asp:RadioButton ID="rbBank" CssClass="form-control-label mb-0 tx-dark" GroupName="rbgStore" runat="server" Text="Bank" />
                    </div>
                    <div class="col-12 col-md-6 d-flex" id="dvtansactionId" runat="server">
                        <asp:TextBox ID="txtTransactionId" runat="server" CssClass="form-control" placeholder="Enter transaction ID" autocomplete="off" />
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="col-12">
                        <div>
                            <label class="w-100 text-left tx-dark">Remarks</label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtDeliveryRemarks" runat="server" TextMode="MultiLine" CssClass="form-control" autocomplete="off" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDeliveryRemarks" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Remarks is required" ValidationGroup="UpdateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="w-100 text-left tx-dark">Date<span class="tx-danger">*</span></label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtDeliveryDate" runat="server" CssClass="form-control" TextMode="Date" placeholder="Date of Booking" autocomplete="nofill" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDeliveryDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date required" ValidationGroup="UpdateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="d-flex justify-content-center">
                            <asp:Button runat="server" ID="btnDeliveryUpdate" CssClass="btn btn-primary mr-1" OnClick="btnDeliveryUpdate_Click" Text="Submit" order_id='<%# Eval("order_id") %>' quorId='<%# Eval("quor_id") %>' ValidationGroup="UpdateDelivery" />
                            <a href="/Tenant/OrderDelivery" class="btn btn-secondary">Cancel</a>
                            <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

            <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <div class="modal fade" id="modaldeliverycharge" tabindex="-1" role="dialog" aria-labelledby="modaldemo4Label" aria-hidden="true">
  <div class="modal-dialog w-100" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modaldemo4Label">Delivery Charges Increased</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         <asp:Literal ID="ltrdelivery" runat="server"></asp:Literal>        
          <div class="form-check p-0" runat="server" id="chkdelivery">
              <asp:RadioButton runat="server" ID="chkdeliverycost" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrdeliverycost" runat="server"></asp:Label>
          </div>
          <div class="form-check p-0">
              <asp:RadioButton runat="server" ID="chkmannual" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrmannual" runat="server"></asp:Label>
          </div>
           <div class="form-check p-0">
              <asp:RadioButton runat="server" ID="chkcancel" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrcanel" runat="server"></asp:Label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <asp:LinkButton runat="server" ID="btnshowpopup" OnClick="btnshowpopup_Click" Text="Confirm" CssClass="btn btn-primary"></asp:LinkButton>
      </div>
    </div>
  </div>
</div>

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
             .btn_assign {
            color:#13977F;
             }
            .btn_assign:hover, .btn_assign.btn-primary {
              color: #fff!important;
            }
         .btn-disabled {
             background-color: #ccc !important;
             border-color: #ccc !important;
             pointer-events: none;
             opacity: 0.65;
         }
       </style>
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/OrderDelivery";
            });
        });
    </script>

    <script type = "text/javascript">
        function ConfirmManualSch() {
            var confirm_value = document.createElement("INPUT");
            confirm_value.type = "hidden";
            confirm_value.name = "confirm_value";
            if (confirm("Are you sure to push manual sheduling ? ")) {
                confirm_value.value = "Yes";
            } else {
                confirm_value.value = "No";
            }
            document.forms[0].appendChild(confirm_value);
        }
    </script> 


    <script type="text/javascript"> 
        $(document).ready(function () {
            // Hide the dvtansactionId div initially
            $('#<%= txtTransactionId.ClientID %>').hide();

        // Handle click events for radio buttons
        $('#<%= rbBank.ClientID %>').click(function () {
            $('#<%= txtTransactionId.ClientID %>').show();
        });

        $('#<%= rbCash.ClientID %>').click(function () {
            $('#<%= txtTransactionId.ClientID %>').hide();
        });
    });
    </script>
    
    <script>
        $(document).ready(function () {
            $('#<%= selCourier.ClientID %>').change(function () {
            var selectedValue = $(this).val();
            if (selectedValue === '-1') {
                $('#divCourierDropdown').hide();
                $('#divCourierTextbox').show();
                $('#divCourierTextbox label').html('Enter Courier Name<span id="selectDropdown" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Select Courier</span>');
                $('#<%= txtCourierName.ClientID %>').focus();
                <%--$('#<%= txtCourierNameValidator.ClientID %>').show();
                $('#<%= txtTrackingURL.ClientID %>').prop('readonly', false);--%>
            } else {
                $('#divCourierDropdown').show();
                $('#divCourierTextbox').hide();
                $('#divTrackingURL').show();
                <%--$('#<%= txtCourierNameValidator.ClientID %>').hide();
                $('#<%= txtTrackingURL.ClientID %>').prop('readonly', true);--%>
                var pageUrl = '<%= ResolveUrl("~/OrderDelivery.aspx") %>';
                $.ajax({
                    type: "POST",
                    url: pageUrl + "/GetTrackingURL",
                    data: '{ courierId: "' + selectedValue + '" }',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (response) {
                        $('#<%= txtTrackingURL.ClientID %>').val(response.d);
                    },
                    failure: function (response) {
                        alert("Failed to load tracking URL.");
                    }
                });
            }
        });

        $(document).on('click', '#selectDropdown', function () {
            $('#divCourierDropdown').show();
            $('#divCourierTextbox').hide();
            $('#divTrackingURL').show();
            $('#<%= selCourier.ClientID %>').val("");
        });
    });
    </script>
    <script type="text/javascript">
        function toggleAssignBtn() {
            const $checkboxes = $("input[id*='chkDelivery']");
            const $assignBtn = $("#<%= lbnDelivStaff.ClientID %>");
            const $manualBtn = $("#<%= lbnManualDeliv.ClientID %>");
            const $loader = $("#processing_loader");

            const isAnyChecked = $checkboxes.is(":checked");

            $assignBtn.prop("disabled", !isAnyChecked).toggleClass("btn-disabled", !isAnyChecked);
            $manualBtn.prop("disabled", !isAnyChecked).toggleClass("btn-disabled", !isAnyChecked);

            // Show or hide loader
            if ($loader.length) {
                isAnyChecked ? $loader.hide() : $loader.show();
            }
        }

        $(document).ready(function () {
            $("input[id*='chkDelivery']").on("change", toggleAssignBtn);
            toggleAssignBtn(); 
        });
    </script>
    </asp:Content>

   



