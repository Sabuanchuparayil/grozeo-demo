<%@ Page Language="C#" AutoEventWireup="true" Title="Pending Orders" ValidateRequest="false"  MasterPageFile="~/Tenant/TenantMaster.master"  CodeBehind="PendingOrders.aspx.cs" Inherits="RetalineProAgent.PendingOrders" %>
<%@ Register Src="~/Controls/Orders/CtrlViewOrders.ascx" TagPrefix="uc1" TagName="CtrlViewOrders" %>
<%@ Register Src="~/Controls/Orders/CtrlAssignOrderPicker.ascx" TagPrefix="uc1" TagName="CtrlAssignOrderPicker" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle1" runat="server" Text="Order Packing"></asp:Literal> from
            <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
        </h6>
        <p class="mb-0">Organize and prepare orders efficiently for shipment</p>
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
   
});
</script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-12  mb-0">
                    <div class="card-menufilter d-flex justify-content-between align-items-start">
                        <div id="orderDropdownMenu" class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle d-lg-none " type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Orders Menu
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <asp:LinkButton ID="lbtnAll" runat="server" typeid="0" OnClick="btnFilterType_Click" CssClass="dropdown-item allitems active">All Orders</asp:LinkButton>
                                <asp:LinkButton ID="lbtnnewjob" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="dropdown-item pending_items">New Orders</asp:LinkButton>
                                <asp:LinkButton ID="lbnpending" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="dropdown-item packing_items">Pending</asp:LinkButton>
                                <asp:LinkButton ID="lbnProcessing" runat="server" typeid="3" OnClick="btnFilterType_Click" CssClass="dropdown-item trasit_items">Processing</asp:LinkButton>
                                <asp:LinkButton ID="lbtnPacked" runat="server" typeid="4" OnClick="btnFilterType_Click" CssClass="dropdown-item delivered_items">Completed</asp:LinkButton>
                                <asp:LinkButton ID="lbtOnhold" runat="server" typeid="5" OnClick="btnFilterType_Click" CssClass="dropdown-item failed_items">On Hold</asp:LinkButton>
                                <asp:LinkButton ID="lbrerack" runat="server" typeid="6" OnClick="btnFilterType_Click" CssClass="dropdown-item hold_items">Rerack</asp:LinkButton>
                            </div>
                        </div>

                        <div class="d-flex card-menufilter-result">
                            <div runat="server" id="dvSlider" visible="false">
                                <asp:PlaceHolder ID="plcInTransit" runat="server" Visible="false">
                                    <div class="input-group mg-b-0">
                                        <asp:DropDownList ID="ddlOrderStatus" runat="server" CssClass="form-control select2 mr-1" AutoPostBack="true" OnSelectedIndexChanged="ddlOrderStatus_SelectedIndexChanged">
                                            <asp:ListItem Text="Processing" Value="3" />
                                            <asp:ListItem Text="Order Picking" Value="7" />
                                            <asp:ListItem Text="Ready to Invoice" Value="8" />
                                            <asp:ListItem Text="Ready to Pack" Value="9" />
                                        </asp:DropDownList>
                                    </div>
                                </asp:PlaceHolder>
                                <asp:PlaceHolder ID="plcDelivered" runat="server" Visible="false">
                                    <div class="input-group mg-b-0">
                                        <asp:DropDownList ID="ddlSuccessfulOrders" runat="server" CssClass="form-control select2 mr-1" AutoPostBack="true" OnSelectedIndexChanged="ddlSuccessfulOrders_SelectedIndexChanged">
                                            <asp:ListItem Text="Successful Orders" Value="10" Selected="True" />
                                            <asp:ListItem Text="All Orders" Value="0" />
                                            <asp:ListItem Text="Cancelled Orders" Value="11" />
                                        </asp:DropDownList>
                                    </div>
                                </asp:PlaceHolder>
                            </div>


                            <button class="navbar-toggler d-lg-none btn btn-outline-primary fiterbtn" type="button" data-toggle="collapse" data-target="#FilterSearch" aria-controls="FilterSearch" aria-expanded="false" aria-label="Filter Search">
                                <i class="fa-regular fa-magnifying-glass"></i>
                            </button>

                            <asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-inline-block btn-outline-primary" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download"></i></asp:LinkButton>
                        </div>
                    </div>
                </div>
            </div>


            <div class="navbar-expand-lg filtersearch_collapsewrap">
                <div class="collapse navbar-collapse" id="FilterSearch">

                    <div class="row row-sm mt-2">
                        <uc1:CtrlViewOrders runat="server" ID="CtrlViewOrders1" />
                        <uc1:CtrlAssignOrderPicker runat="server" ID="CtrlAssignOrderPicker1" />
                        <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-lg-b-0">
                            <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store</label>
                            <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name" DataValueField="br_ID" runat="server">
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
                        <div class="col-sm-8 col-lg-4 form-group mb-2 mb-lg-0">
                            <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
                            <%--                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />--%>
                            <div style="display: none;">
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
                            <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2" PostBackUrl="~/Tenant/PendingOrders.aspx" Text="Reset" />
                            <%--                     <a class="btn btn-outline-primary mt-2 mt-lg-0 ml-2" href="javascript:void(0)">Reset</a>--%>
                        </div>
                    </div>

                </div>
            </div>



            
        </div><!--card-head-->
        <div class="card-body">

          <div id="accordion" class="table-responsive">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvPendingOrders" GridLines="None" OnRowDataBound="gvPendingOrders_RowDataBound" runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                                    AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" OnPageIndexChanging="gvPendingOrders_PageIndexChanging" OnDataBound="gvPendingOrders_DataBound" DataSourceID="SDSPendingOrders" >
                                     
                                    <Columns>
                                        <asp:TemplateField HeaderText="Order ID"><ItemTemplate>
                                            <asp:HyperLink runat="server" Text='<%# Eval("order_order_id") %>' ></asp:HyperLink>
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
                                            <small>Mode: <b><%# GetDeliveryMode(Convert.ToString(Eval("order_method")), Eval("order_slot_id")) %></b></small>
                                                                              </ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order Date"><ItemTemplate>
                                            <%# Eval("created_at") %><br /><small>Status: <b 
                                                <%--style="<%# (Eval("payStatus").ToString() == "Success"? "color:green": "color:red") %>" --%>
                                                ><%# GetPaymentStatusName(Convert.ToInt32(Eval("payment_mode")), Convert.ToInt32(Eval("status_id")), "") %></b></small>
                                                                                   </ItemTemplate></asp:TemplateField>
                                        <%--<asp:TemplateField HeaderText="Delivery Slot"><ItemTemplate>
                                            <%# Eval("created_at") %><br /><small>Payment Status: <b style="<%# (Eval("order_payment_status").ToString() == "Success"? "color:green": "color:red") %>" ><%# Eval("order_payment_status") %></b></small>
                                                                                   </ItemTemplate></asp:TemplateField>--%>
                                        <asp:TemplateField HeaderText="Customer" ><ItemTemplate>
                                            <asp:Label ID="Label2" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 20) %>' ToolTip ='<%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0}, {1}", Eval("cust_customer_name"), Eval("cust_mobile")), 100) %>'></asp:Label>
                                            <br />
                                            <a href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i></a>&nbsp;
                                            <small><%# RetalineProAgent.Service.Common.ShrinkText(String.Format("{0} {1} {2} {3}", Eval("order_house_name"), Eval("order_address"), Eval("order_city"), Eval("pin")), 20)  %></small>
                                                                                 </ItemTemplate></asp:TemplateField>

                                        <asp:TemplateField HeaderText="Numbers" Visible="false"><ItemTemplate>
                                            <%# Eval("fsto_pickingNumber") %><br /></ItemTemplate></asp:TemplateField>

                                        <asp:TemplateField HeaderText="Status" SortExpression="order_status"><ItemTemplate>
                                            <asp:Label ID="Label3" runat="server" Text='<%# RetalineProAgent.Service.Common.ShrinkText(Eval("order_status").ToString(), 15) %>' ToolTip ='<%# Bind("order_status") %>'></asp:Label>
                                            <br /><small>Items: <b><%# Eval("newitemcount") %></b></small>&nbsp;&nbsp;&nbsp;<small>Worth: <b><%# Eval("order_total_amount") %></b></small></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>

                                                <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>

                                                <asp:Image runat="server" ID="image" ImageAlign="AbsMiddle" Width="13" ImageUrl="/content/images/processing.gif"
                                                      Visible='<%# ( (new int[]{5}).Contains(Convert.ToInt32(Eval("StatusId"))) && !IsButtonVisible((Eval("fsto_status").ToString()),(Eval("order_id").ToString()))) %>' />

                                                </td></tr><tr>
                                                    <td colspan="7" class="hiddenRow">
                                                        <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">

                                                            <asp:Button runat="server" ID="btnvieworder" Text="View Order" Visible='<%# Eval("fsto_status") != DBNull.Value && (new int[] { 6,20,9,1,2,3 }).Contains(Convert.ToInt32(Eval("fsto_status"))) ||IsButtonVisible(Eval("fsto_status").ToString(), Eval("order_id").ToString()) %>' data-fsto_id='<%# Eval("fsto_id") %>' data-orderId='<%# Eval("order_order_id") %>' data-status='<%# Eval("order_status") %>' data-order_id='<%# Eval("order_id") %>' data-orderMethod='<%# Eval("order_method") %>' data-orderStatus='<%# Eval("status_id") %>' data-incompleteorder='<%# Convert.ToInt32(Eval("status_id")) == 23 ? "1" : "0" %>' OnClick="btnvieworder_Click"  CssClass="btnvieworder btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3"></asp:Button>
                                                            <asp:Button ID="btnAssignOrderPicker" runat="server" CssClass="btnAssignOrderPicker btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# Eval("fsto_status") != DBNull.Value && ((new int[]{ 4 }).Contains(Convert.ToInt32(Eval("fsto_status"))) ? true : false) %>' OnClick="btnAssignOrderPicker_Click" Text="Assign order picker" data-fsto_id='<%# Eval("fsto_id") %>' data-order_orderId='<%# Eval("order_order_id") %>' data-orderid='<%# Eval("order_id") %>' data-fsto_uid='<%# Eval("fsto_uid") %>' />
                                                            <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# Eval("fsto_status") != DBNull.Value && ( (new int[]{4,12,8,5,23}).Contains(Convert.ToInt32(Eval("fsto_status")))
                                                            ? true: false) %>' NavigateUrl='<%# string.Format("~/Tenant/ManualPacking.aspx?fsto_id={0}&orderId={1}&orderMethod={2}&orderStatus={3}&statusName={4}", Eval("fsto_id"), Eval("order_order_id"), Eval("order_method"), Eval("StatusId"), Eval("order_status")) %>' Text='<%# (Convert.ToString(Eval("fsto_status"))  == "12" ? "Pack This Order" : "Manual Packing") %>'></asp:HyperLink>
                                                            <asp:Button runat="server" ID="btnRevoke" OnClick="btnRevoke_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Revert" transferOrderId='<%# Eval("fsto_id") %>' Visible='<%# Eval("fsto_status") != DBNull.Value && (new int[] { 8, 5, 23,9,20 }).Contains(Convert.ToInt32(Eval("fsto_status"))) %>' OnClientClick="javascript:return confirm('Do you wish to revert this?');" />
                                                            <asp:Button runat="server" ID="btnrerack" Text="Rerack"  Visible='<%# Eval("fsto_status") != DBNull.Value && (Convert.ToInt32(Eval("fsto_status")) == 15) && Convert.ToInt32(Eval("fsto_isalreadypacked")) == 1 && Convert.ToInt32(Eval("is_replenished")) == 0 %>' tranferorderid='<%# Eval("fsto_id") %>'  OnClick="btnrerack_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3"></asp:Button>
                                                            <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# Eval("fsto_status") != DBNull.Value && ( (new int[]{12,8,5,23,10,15}).Contains(Convert.ToInt32(Eval("fsto_status")))
                                                    ? true: false) %>'
                                                                NavigateUrl='<%# String.Format("~/Tenant/OrderDetailsNew.aspx?orderid={0}&toid={1}&ordId={2}&page=Packing", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View order details"></asp:HyperLink>
                                                            <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# String.Format("/Tenant/invoice.aspx?ordId={0}", Eval("order_id")) %>' Text="Invoice" Visible='<%# Eval("fsto_status") != DBNull.Value && ( (new int[]{10}).Contains(Convert.ToInt32(Eval("fsto_status"))) ? true: false) %>'></asp:HyperLink>
                                                            <asp:LinkButton runat="server" ID="btnprint" OnClick="btnprint_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" order_id='<%# Eval("order_order_id") %>' Text="Packing Label" Visible='<%#Eval("fsto_status") != DBNull.Value && ( (new int[]{10}).Contains(Convert.ToInt32(Eval("fsto_status"))) ? true: false) %>'></asp:LinkButton>
                                                            <%-- <asp:HyperLink runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Visible='<%# ( (new int[]{23 }).Contains(Convert.ToInt32(Eval("StatusId")))
                                    ? true: false) %>'
                                                                NavigateUrl='<%# String.Format("~/Tenant/ViewAndUpdate.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>' Text="View & Update Orders"></asp:HyperLink>--%>
                                                            <a class="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" href="https://maps.google.com/?q=<%# Eval("lat") %>,<%# Eval("lng") %>" target="_blank"><i class="fa-regular fa-location-dot"></i>&nbsp;
                                                    Click here to view location</a>
                                                            <%-- <asp:HyperLink runat="server"  CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" NavigateUrl='<%# String.Format("/Tenant/invoice.aspx?ordId={0}", Eval("order_id")) %>' Text="Invoice" Visible='<%# ( (new int[]{8,9,10,11,12,13,14,15,16,17,18,20,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>'></asp:HyperLink>
                                                    <asp:LinkButton runat="server" ID="btnprint" OnClick="btnprint_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" order_id='<%# Eval("order_order_id") %>' Text="Packing Label" Visible='<%# ( (new int[]{8,9,10,11,12,13,14,15,16,17,18,20,22,27,28,29,30,31,32,33,34 }).Contains(Convert.ToInt32(Eval("StatusId"))) ? true: false) %>' ></asp:LinkButton>--%>
                                                        </div>
                                                    </td>
                                                </tr>

                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <%--<PagerSettings Mode="NextPreviousFirstLast" FirstPageText="<<" NextPageText=">" PreviousPageText="<" LastPageText=">>" PageButtonCount="5" />--%>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSPendingOrders" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSPendingOrders_Selected" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT order_order_id,total,br_Name,order_method,fsto_pickingNumber,fsto_status,fsto_isalreadypacked,is_replenished,
                                order_slot_id,payment_mode,cust_customer_name,cust_mobile,order_latitude AS lat, order_longitude AS lng,
                                order_house_name,order_address,order_city, TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
                                (SELECT COUNT(item_product_id) FROM retaline_customer_order_items WHERE item_order_id=bco.order_order_id) AS newitemcount,bco.status_id AS StatusId,
                                fsto_id,fsto_uid,bco.order_id,bco.status_id,DATE_FORMAT(bco.created_at,'%d %b %Y  %H:%i') AS created_at,IFNULL(order_post, order_pin) AS pin, CASE
                                 WHEN order_method = 1 THEN 'Drive Delivery'  WHEN order_method = 2 THEN 'Customer Collect' WHEN order_method = 3 THEN 'Courier Delivery'
                                END AS order_method,order_total_amount
                                FROM retaline_customer_order bco 
                                INNER JOIN retaline_customer c ON c.cust_id=bco.order_customer_id 
                                INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id 
                                INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                                INNER JOIN finascop_branch ON br_ID = order_branch_id and finascop_branch.br_storeGroup=@storegroupid
                                LEFT JOIN finascop_stock_transfer_order so ON so.fstr_id = bco.order_id
                                WHERE so.fstr_id IS NOT NULL and (bco.storegroup_id=@storegroupid OR finascop_branch.br_storeGroup=@storegroupid) AND (@branchId <= 0 OR order_branch_id = @branchId) AND  bco.status_id not in (0,11,13,14,15,16,54,55) AND (IFNULL(@filterType, -1) = -1 
                                OR (@filterType = 0 AND bco.status_id >= 0) -- All Orders 
                                OR (@filterType = 1 AND (fsto_status < 4 AND bco.status_id = 4)) -- New Orders 
                                OR (@filterType = 2 AND fsto_status = 4)  -- Pending
                                OR (@filterType = 3 AND fsto_status IN (2, 3, 6, 7, 8))  -- Processing
                                OR (@filterType = 4 AND fsto_status = 10 AND bco.status_id = 9) -- Completed
                                OR (@filterType = 5 AND fsto_status in (9, 11, 20))  -- On Hold
                                OR (@filterType = 6 AND fsto_status = 15  AND fsto_isalreadypacked = 1 AND is_replenished = 0) -- Rerack
                                OR (@filterType = 7 AND fsto_status IN (4, 8))  -- Order Picking
                                OR (@filterType = 8 AND fsto_status = 10 AND bco.status_id = 9) -- Ready to Invoice
                                OR (@filterType = 10 AND bco.status_id >= 4) -- Successful Orders
                                OR (@filterType = 11 AND bco.status_id = 19) -- Cancelled
                                OR (@filterType = 9 AND fsto_status IN (6, 8)))  -- Ready to Pack 
                                 AND (TRIM(IFNULL(@orderid, '')) LIKE '' OR bco.order_order_id LIKE CONCAT('%', @orderid, '%') OR br_Name LIKE CONCAT('%', @orderid, '%')
                                 OR cust_customer_name LIKE CONCAT('%', @orderid, '%') OR cust_mobile LIKE CONCAT('%', @orderid, '%') OR order_customer_email LIKE CONCAT('%', @orderid, '%')
                                  OR order_house_name LIKE CONCAT('%', @orderid, '%') OR order_city LIKE CONCAT('%', @orderid, '%') OR order_address LIKE CONCAT('%', @orderid, '%')
                                ) AND (TRIM(IFNULL(@datefrom, '')) LIKE '' OR bco.created_at >=CONVERT(@datefrom, DATE)) AND (TRIM(IFNULL(@dateto, '')) LIKE '' OR bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY)) ORDER BY bco.created_at DESC">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
            <asp:ControlParameter ControlID="txtOrderId" Name="orderid" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selBranches" PropertyName="Text" Name="branchId" />
        </SelectParameters>
    </asp:SqlDataSource>
            
          </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div><!-- card -->
    
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
    <div id="modalrerack" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-between">
                        <asp:PlaceHolder ID="plcHead" runat="server">
                            <div class="ordr-info d-flex align-items-center flex-wrap flex-md-nowrap">
                                <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal">
                                    <strong class="tx-dark">Rerack</strong>
                                </div>
                                <div class="col-12 col-md-auto p-0 tx-15 d-flex align-items-center">
                                       <strong>Order Id:</strong>
                                    <strong class="tx-dark"><asp:Literal ID="ltrorderid" runat="server" Text=""></asp:Literal></strong>                                   
                                </div>
                                <div class="col-12 col-md-auto p-0 d-flex align-items-center pl-0 pl-md-3">
                                    <strong>Basket No:</strong>
                                    <strong class="tx-dark"><asp:Literal ID="ltrbasketno" runat="server" Text=""></asp:Literal></strong>                                   
                                </div>
                            </div>
                        </asp:PlaceHolder>                        
                    </div>
                </div>
                <!-- card-header -->
                <div class="card-body p-3 pt-0">
                    <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" ID="gvManualPacking"  runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                            AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSrerack">
                            <Columns>
                                <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                                    <ItemTemplate>
                                        <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:BoundField HeaderText="Item" DataField="item_name" SortExpression="item_name" />
                                <asp:TemplateField>
                                    <HeaderTemplate>
                                        <%# (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP") %>
                                    </HeaderTemplate>
                                    <ItemTemplate>
                                        <itemtemplate>
                                            <asp:Label ID="lblMRP" runat="server" Text='<%# Eval("mrp", "{0:0.00}") %>' CssClass="text-right"></asp:Label>
                                        </itemtemplate>
                                        <itemstyle width="120" horizontalalign="Right" />
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:TemplateField HeaderText="Packed Qty"  ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-HorizontalAlign="Left">
                                    <ItemTemplate>
                                        <asp:Label runat="server" ID="lblrerackqty" Text='<%# Eval("fsto_ItemQty") %>'></asp:Label>                                        
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No order record available.</h6>
                                </div>
                            </EmptyDataTemplate>
                        </asp:GridView>   
                        <asp:HiddenField runat="server" ID="hdnfstoid" />
                        <asp:SqlDataSource runat="server" ID="SDSrerack" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT fo.fsto_uid AS fsto_uid,fo.fsto_isalreadypacked,fo.fsto_id AS fsto_id,fd.fsto_ItemId,fd.fstod_id,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_source,
                                    (SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) AS br_parentPacking,
                                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS item_name,
                                    (SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS stit_ParentItemId,
                                    fsto_ItemQty,fsto_pkdQty, (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') as ownInvoice,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch,
                                    fo.fsto_id AS fsto_id,fstro_ItemMRP AS mrp
                                    FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  
                                    AND fo.fsto_id=@fstoid">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hdnfstoid" Name="fstoid" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>


                </div>
                <!-- card-body -->
                <div class="card-footer d-flex flex-wrap justify-content-center">
                    <asp:Button runat="server" Text="Rerack" ID="btnrerackorder" OnClick="btnrerackorder_Click" CssClass="btn btn-primary mr-2"></asp:Button>
                    <asp:LinkButton runat="server" ID="btnreject"  Text="Cancel" CssClass="btn btn-secondary"></asp:LinkButton>                   
                    <!--row-->
                </div>
                <!-- card-footer -->
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
            .row-new        { background-color: #ffcccc; }   /* lighter red */
            .row-pending    { background-color: #ffe5e5; }   /* lightest red */
            .row-processing { background-color: #fffacd; }   /* light yellow */
            .row-completed  { background-color: #ffffff; }   /* white */
            .row-onhold     { background-color: #cce5ff; }   /* light blue */
            .row-rerack     { background-color: #e6ccff; }   /* light purple */
            .row-cancelled  { background-color: #f2f2f2; }   /* light gray */
       </style>
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/PendingOrders";
            });
          
        });
        
    </script>
   <script>
       $(document).ready(function () {
           $('.btnvieworder').on('click', function (e) {            
               $('#<%= CtrlViewOrders1.hdnfstoid_ClientId %>').val($(this).data('fsto_id'))
               $('#<%= CtrlViewOrders1.hdnorderid_ClientId %>').val($(this).data('orderid'))
               $('#<%= CtrlViewOrders1.hdnordermethod_ClientId %>').val($(this).data('ordermethod'))
               $('#<%= CtrlViewOrders1.hdnIncompleteorder_ClientId%>').val($(this).data('incompleteorder'))
           });
           $('.btnAssignOrderPicker').on('click', function (e) {               
               assignOrderPicker(this)
           });
       });

       function assignOrderPicker(obj) {
           $('#<%= CtrlAssignOrderPicker1.hdnfstoid_ClientId %>').val($(obj).data('fsto_id'))
           $('#<%= CtrlAssignOrderPicker1.hdnorderorderid_ClientId %>').val($(obj).data('order_orderId'))
           $('#<%= CtrlAssignOrderPicker1.hdntoid_ClientId %>').val($(obj).data('fsto_uid'))
           $('#<%= CtrlAssignOrderPicker1.hdnorderid_ClientId%>').val($(obj).data('orderid'))
       }
   </script>

    <style>
.PagerRowStyle
{
    background-color: #ddd;
    text-align: right;
}
 @media (min-width: 992px) {
    #modalrerack .modal-dialog{
     max-width: 900px;
    }
 }   
    </style>     

</asp:Content>



