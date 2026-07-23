<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="deliveryReport.aspx.cs" Inherits="RetalineProAgent.Tenant.Finance.deliveryReport" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>    
    <li class="breadcrumb-item active" aria-current="page">Delivery Reports</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Delivery Reports"></asp:Literal>       
    </h6>
        <p class="mb-0">Timely Delivery Analytics</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex align-items-end row row-sm">
                <div class="col-sm-4 col-lg-3 mb-2 mb-lg-0 d-flex flex-wrap input-group">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store:</label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Store" runat="server" visible="false">
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
                <div class="col-sm-8 col-lg-3 form-group mb-2 mb-lg-0">
                    <label for="txtSearch1" runat="server" class="form-control-label mb-1 w-100 tx-dark">Search by:</label>
                    <div style="display: none;">
                        <input type="text" name="name_emailField" />
                        <input type="password" name="passwordFiele" />
                    </div>
                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search by Order ID" autocomplete="off"></asp:TextBox>
                </div>
                <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">
                    <label for="txtDateFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">From:</label>
                    <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                </div>
                <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">
                    <label for="txtDateTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">To:</label>
                    <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                </div>
                <div class="col-sm-4 col-lg-2 d-flex align-items">
                    <label class="d-none d-lg-block">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/Finance/deliveryReport.aspx" Text="Reset" />

<%--                     <a class="btn btn-outline-primary mt-2 mt-lg-0 ml-2" href="javascript:void(0)">Reset</a>--%>
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvdelivery" runat="server" CssClass="table table-bordered gridview_table" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvdelivery_DataBound" DataSourceID="SDSdelivery">
                                    <Columns>
                                        <asp:BoundField HeaderText="Date" DataField="order_confirm_date"  DataFormatString="{0:dd/MM/yyyy}" ItemStyle-Width = "150" SortExpression="order_confirm_date"  />                                     
                                        <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" />
                                        <asp:BoundField HeaderText="Order ID." DataField="order_order_id" SortExpression="order_order_id"  />                                                                             
                                       <asp:BoundField HeaderText="Customer Details" DataField="customerDetails" SortExpression="customerDetails"  /> 
                                        <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address"/> 
                                         <asp:BoundField HeaderText="Delivery Mode" DataField="order_method" SortExpression="order_method"  /> 
                                        <asp:BoundField HeaderText="Driver ID/ Name" DataField="d_name" SortExpression="d_name"  /> 
                                         <asp:BoundField HeaderText="Mode of Payment" DataField="payMode" SortExpression="payMode" />
                                         <asp:BoundField HeaderText="Branch" DataField="br_name" SortExpression="br_name"  />                                         
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
                    <asp:SqlDataSource runat="server" ID="SDSdelivery" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="SELECT order_order_id,order_confirm_date,TIME(order_confirmed_on) AS ordertime,br_name,rd.order_customer_id,cust_customer_name,d.d_name,
                                    CONCAT((order_customer_name),',',
                                      (order_city),',',order_post) AS customerDetails,CONCAT((order_house_name),',',
                                      (order_city),',',order_post) AS address,
                                    CASE
                                        WHEN order_method = 1 THEN 'Drive Delivery'
                                        WHEN order_method = 2 THEN 'Customer Collect'
                                        WHEN order_method = 3 THEN 'Courier Delivery'
                                    END AS order_method,
                                    CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 
                                    THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet' 
                                    WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 
                                    THEN 'Cash On Delivery' END AS payMode
                                    FROM  qugeo_driver d 
                                    INNER JOIN finascop_branch b ON d.br_ID=b.br_ID
                                    INNER JOIN retaline_customer_order rc ON order_branch_id=b.br_id
                                    INNER JOIN retaline_customer r ON rc.order_customer_id=r.cust_id
                                    INNER JOIN `retaline_customer_order_delivery_address` rd ON rd.customer_order_id=rc.order_id
                                    where status_id=18 and rc.storegroup_id=@storegroup and (@branchid <= 0 or b.br_ID=@branchid)
                                    AND (trim(ifnull(@datefrom, '')) like '' or order_confirm_date >=CONVERT(@datefrom, DATE)) AND (trim(ifnull(@dateto, '')) like '' or order_confirm_date < DATE_ADD(CONVERT(@dateto, DATE), INTERVAL 1 DAY)) and (trim(ifnull(@search, '')) like ''  or order_order_id like CONCAT('%', @search, '%')) 
                                    AND (trim(ifnull(@search, '')) like '' or order_order_id like CONCAT('%', @search, '%'))  GROUP BY order_order_id"
                                     OnSelecting="SDSdelivery_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                        <asp:Parameter Name="branchId" />
                                        <asp:ControlParameter Name="search" ControlID="txtSearch" Type="String" ConvertEmptyStringToNull="false" />
                                        <asp:ControlParameter ControlID="txtDateFrom" Name="datefrom" ConvertEmptyStringToNull="false" />
                                        <asp:ControlParameter ControlID="txtDateTo" Name="dateto" ConvertEmptyStringToNull="false" />
                                    </SelectParameters>
                                  </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>
