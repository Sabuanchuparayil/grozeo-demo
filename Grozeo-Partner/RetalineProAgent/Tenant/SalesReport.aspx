<%@ Page Language="C#" AutoEventWireup="true" Title="Sales Report" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="SalesReport.aspx.cs" Inherits="RetalineProAgent.SalesReport" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sales Report</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Sales Report"></asp:Literal> of 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Track Your Sales Performance</p>
    </div>
    
<style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex row row-sm">

                <div class="col-12 col-md-4">
                    <div class="row row-sm">
                        <div class="col-lg-7 input-group mg-b-10 mg-lg-b-0">
                            <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store:</label>
                            <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Store" runat="server" visible="false">
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                                    <asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <%--<asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>--%>
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

                        <div class="col-lg-5 form-group mb-2 mb-lg-0">
                            <label for="txtSearch1" runat="server" class="form-control-label mb-1 w-100 tx-dark">Search by:</label>
                             <div style="display:none;">
                                <input type="text" name="name_emailField" />
                                <input type="password" name="passwordFiele" />
                            </div>
                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search by Order ID" autocomplete="off"></asp:TextBox>
                        </div>

                    </div>
                </div>


                <div class="col-12 col-md-8">
                    <div class="row row-sm">
                        <div class="col-lg-5 mb-2 mb-lg-0">
                            <div class="row row-sm">
                                <div class="col-md-6 mb-2 mb-lg-0">
                                    <label for="txtDateFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">Date - From:</label>
                                    <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                </div>
                                <div class="col-md-6">
                                    <label for="txtDateTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">Date - To:</label>
                                    <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="row row-sm">
                                <div class="col-md-7 mb-3 mb-lg-0">
                                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Payment Mode:</label>
                                    <asp:DropDownList ID="selPaymentMode" AutoPostBack="true" CssClass="form-control select2" runat="server" ForeColor="GrayText">
                                        <asp:ListItem Value="">Select Payment Mode</asp:ListItem>
                                        <asp:ListItem Value="1">Pay On Delivery</asp:ListItem>
                                        <asp:ListItem Value="2">Online Payment</asp:ListItem>
                                        <asp:ListItem Value="3">Wallet</asp:ListItem>
                                        <asp:ListItem Value="4">COD With Wallet</asp:ListItem>
                                        <asp:ListItem Value="5">Online With Wallet</asp:ListItem>
                                        <asp:ListItem Value="6">Online On Delivery</asp:ListItem>
                                        <asp:ListItem Value="7">Cash On Delivery</asp:ListItem>
                                    </asp:DropDownList>
                                </div>
                                <div class="col-md-5 d-flex flex-wrap mb-3 mb-lg-0 align-items-md-start">
                                    <label class="w-100">&nbsp;</label>
                                    <div class="d-flex align-items-center">
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-sm-0" runat="server">Search</asp:LinkButton>
                                        <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2" PostBackUrl="~/Tenant/salesreport.aspx" Text="Reset" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12 mt-2 d-flex justify-content-md-end">
                    <asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-inline-block btn-outline-primary" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download mr-1"></i> Download</asp:LinkButton>
                </div>
                
                
                
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">

                                <asp:GridView AutoGenerateColumns="false" ID="gvSalesReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PageSize="10" OnDataBound="gvSalesReport_DataBound" DataSourceID="SDSSalesReport" PagerStyle-CssClass="pg_table">
                                    <Columns>
                                        <asp:BoundField HeaderText="Order ID" DataField="order_order_id" SortExpression="order_order_id"  />
                                        <asp:BoundField HeaderText="Order Date & Time" DataField="order_created_on" SortExpression="order_created_on"  />
                                        <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
                                        <asp:BoundField HeaderText="Order Amount" DataField="order_total_amount" SortExpression="order_total_amount" ItemStyle-HorizontalAlign="Right" />
                                        <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" />
                                        <asp:TemplateField HeaderText="Payment Mode" SortExpression="payMode">
                                            <ItemTemplate>
                                            <%# Eval("payMode") %><br /><small>Status: <b><%# RetalineProAgent.PendingOrders.GetPaymentStatusName(Convert.ToInt32(Eval("payment_mode")), Convert.ToInt32(Eval("STATUS")), "") %></b></small>
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
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSSalesReport" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSSalesReport_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT bco.order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id 
                                    AS STATUS,DATE_FORMAT(bco.created_at, '%d %b %Y %H:%i:%s') AS order_created_on,payment_mode,
                                    TIME_FORMAT(CAST(bco.created_at AS TIME),'%H:%i:%s') AS ordertime,admin_description AS order_status,admin_description,
                                    order_payment_gateway_refid,order_payment_gateway_refid_crc32,cust_customer_name,cust_mobile,order_city,CONCAT(rc.cust_customer_name, ' - ', rda.order_city) AS delivery_to,
                                    order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at,order_total_amount,
                                    CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 
                                    THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet' 
                                    WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 
                                    THEN 'Cash On Delivery' END AS payMode
                                    FROM retaline_customer_order bco 
                                    INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                                    INNER JOIN finascop_branch ON br_ID = order_branch_id 
                                    INNER JOIN retaline_customer_order_delivery_address rda ON rda.customer_order_id = bco.order_id
                                    INNER JOIN retaline_customer rc ON rc.cust_id = bco.order_customer_id
                                    WHERE 1 = 1 AND bco.status_id >= 4 AND bco.storegroup_id=@storegroup AND bco.order_branch_id=@branchId  
                                    AND ((TRIM(IFNULL(@datefrom, '')) = '' OR bco.created_at >= CONVERT(@datefrom, DATE)) AND (TRIM(IFNULL(@dateto, '')) = '' OR bco.created_at < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY))) AND (@searchKey IS NULL OR TRIM(@searchKey) = '' OR order_order_id LIKE CONCAT('%', @searchKey, '%'))
                                    AND (trim(ifnull(@searchKey, '')) like '' or order_order_id like CONCAT('%', @searchKey, '%')) AND (trim(ifnull(@paymentmode, '')) like '' or payment_mode like CONCAT('%', @paymentmode, '%')) "
                                            OnSelecting="SDSOnlineOrders_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroup" />
                                    <asp:Parameter Name="branchId" />
                                    <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                    <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
                                    <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
                                    <asp:ControlParameter ControlID="selPaymentMode" Name="paymentmode" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                                </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->       
        <!-- card-footer -->
    </div><!-- card -->

    <asp:GridView ID="gvForExportOnly" runat="server" AutoGenerateColumns="false">
        <Columns>
            <asp:BoundField HeaderText="Order ID" DataField="order_order_id" SortExpression="order_order_id" />
            <asp:BoundField HeaderText="Order Date & Time" DataField="order_created_on" SortExpression="order_created_on"  />
            <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
            <asp:BoundField HeaderText="Order Amount" DataField="order_total_amount" SortExpression="order_total_amount" />
            <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" />
            <asp:BoundField HeaderText="Payment Mode" DataField="payMode" SortExpression="payMode" />
        </Columns>
    </asp:GridView>
</asp:Content>



