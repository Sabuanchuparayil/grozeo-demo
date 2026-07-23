<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Customers" AutoEventWireup="true" CodeBehind="Customers.aspx.cs" Inherits="RetalineProAgent.Customers" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/crm">Customer Relation</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customers</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
    <a href="/Navigations/crm"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle1" runat="server" Text="Customers"></asp:Literal>
            at
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
        </h6>
        <p class="mb-0">Valued Clientele</p>
    </div>

    <style>
        table.table table, table.table table td {
            border: 0px !important;
            padding: 5px;
        }
    </style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-sm-6 col-lg-4 input-group mg-b-10 mg-lg-b-0">
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
                <div class="col-sm-6 col-lg-4 form-group mb-2 mb-lg-0">
                <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by:</label> 
                     <div style="display:none;">
                        <input type="text" name="name_emailField" />
                        <input type="password" name="passwordFiele" />
                    </div>
                    <asp:TextBox ID="txtFindCustomer" runat="server" placeholder="Search by name, phone, email, etc." CssClass="form-control" autocomplete="off"></asp:TextBox>
                  </div>
                
            <div class="col-lg-4 d-flex justify-content-lg-end align-items-lg-end">
                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0 mr-2" runat="server">Search</asp:LinkButton>
                <asp:LinkButton ID="lbtnDownload" Enabled="true" OnClick="lbtnDownload_Click" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0 ml-2" runat="server">Download</asp:LinkButton>
            </div>
                
            </div>
            
                
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvCustomers" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" 
                                    OnDataBound="gvCustomers_DataBound" DataSourceID="SDSCustomers">
                                    <Columns>
                                        <%--<asp:HyperLinkField DataTextField="vehno" DataNavigateUrlFields="vehno" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Vehicle No." ItemStyle-Width = "150" SortExpression="vehno" />--%>
                                        <%--<asp:BoundField HeaderText="Customer ID" DataField="cust_id" SortExpression="cust_id"/>--%>
                                        <asp:BoundField HeaderText="Mobile" DataField="cust_mobile" SortExpression="cust_mobile"/>
                                        <asp:BoundField HeaderText="Name" DataField="cust_customer_name" SortExpression="cust_customer_name"/>
                                        <asp:BoundField HeaderText="Email" DataField="cust_email" SortExpression="cust_email"/>
                                        <asp:BoundField HeaderText="Registered On" DataField="cust_created_at" SortExpression="cust_created_at"/>
                                        <%--<asp:BoundField HeaderText="Active Address" DataField="activeAddress" SortExpression="activeAddress" />--%>
                                        <asp:BoundField HeaderText="Orders" DataField="cust_orders" SortExpression="cust_orders"/>
                                        <asp:BoundField HeaderText="Customer Type" DataField="customer_type" SortExpression="customer_type"/>
                                        <%--<asp:BoundField HeaderText="Wallet Balance" DataField="cust_walletbalance" SortExpression="cust_walletbalance"/>--%>
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default">Action</button>
                                                    <button type="button" class="btn btn-default dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                                      <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu" role="menu">
                                                       <asp:HyperLink runat="server" CssClass="dropdown-item" NavigateUrl='<%# string.Format("~/DeliveryAddress.aspx?cust_id={0}", Eval("cust_id")) %>' Text="View Delivery Address And Wallet History"></asp:HyperLink>
                                                       <asp:HyperLink runat="server" CssClass="dropdown-item" NavigateUrl='<%# string.Format("~/WalletHistory.aspx?orderid={0}", Eval("order_id")) %>' Text="View Wallet History"></asp:HyperLink>
                                                   </div>
                                                  </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                        <asp:TemplateField HeaderText="Action">
                                             <ItemTemplate>
                                                 <asp:LinkButton runat="server" recid='<%# Eval("cust_id") %>' ID="btnview" OnClick="btnview_Click" CssClass="btn btn-primary py-1">View</asp:LinkButton>
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

                                <asp:SqlDataSource runat="server" ID="SDSCustomers" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT rc.cust_id,rc.cust_customer_id,rc.cust_customer_name,rc.cust_mobile,rc.cust_email,
                                    (SELECT COUNT(*) FROM retaline_customer_order co WHERE co.order_customer_id = rc.cust_id AND (@branchid > 0 
                                    AND co.order_branch_id = @branchid OR @branchid <= 0 AND co.order_branch_id IN 
                                    (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storegroup))) AS cust_orders,
                                    DATE_FORMAT(rc.cust_created_at, '%d-%m-%Y %H:%i:%s') AS cust_created_at, 
                                    CASE WHEN rc.cust_id IS NOT NULL AND rco.order_customer_id IS NULL THEN 'Signed up'
                                    WHEN rc.cust_id IS NOT NULL AND rco.order_customer_id IS NOT NULL AND rc.storegroup_id = @storegroup THEN 
                                    CASE WHEN @branchid <= 0 THEN 'Purchased' WHEN EXISTS (SELECT 1 FROM retaline_customer_order co WHERE co.order_customer_id = rc.cust_id AND co.order_branch_id = @branchid
                                    ) THEN 'Purchased from this branch' ELSE 'Purchased from other branch of this store' END WHEN rc.cust_id IS NOT NULL AND rco.order_customer_id IS NOT NULL AND rc.storegroup_id != @storegroup THEN 'Sponsored'
                                    ELSE 'Unknown' END AS customer_type FROM retaline_customer rc
                                    LEFT JOIN retaline_customer_order rco ON rc.cust_id = rco.order_customer_id  
                                    LEFT JOIN finascop_branch fb ON fb.br_ID = rco.order_branch_id
                                    WHERE rc.storegroup_id = @storegroup OR (fb.br_storeGroup = @storegroup 
                                    AND (rco.order_branch_id = @branchid OR @branchid <= 0))
                                    AND (trim(ifnull(@searchKey, '')) like '' or cust_customer_name like CONCAT('%', @searchKey, '%') or cust_mobile like CONCAT('%', @searchKey, '%') or cust_email like CONCAT('%', @searchKey, '%') or cust_walletbalance like CONCAT('%', @searchKey, '%')) GROUP BY cust_id ORDER BY cust_id ASC"
OnSelecting="SDSCustomers_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="selBranch" Name="branchid" DefaultValue="-1" />
            <asp:ControlParameter Name="searchKey" ControlID="txtFindCustomer" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
    <div class="modal" id="Pupaction" data-backdrop="static">
        <div class="modal-dialog w-100">
                 <div class="modal-content">
                <div class="modal-body">
                    <div class="modaltitle">
                        <button type="button" class="close position-absolute mt-2 mr-1" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>                  
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">                                
                                <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0 mt-3" style="max-height: 300px;">
                                        <asp:ListView ID="lvsettlement" DataSourceID="SDSorderdetails" runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Order No</th>
                                                        <th style="width:90px" id="Td2" runat="server">Date</th>
                                                        <th id="Td3" runat="server">Order Amount </th>
                                                        <th id="Th1" runat="server">Mode of Payment</th>
                                                        <th id="Th3" runat="server">Store</th>
                                                         <th id="Th4" runat="server">Status</th>
                                                         <th id="Th5" runat="server">Invoice</th>                                                         
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>                                                   
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbOrderNo" runat="server" Text='<%# Eval("order_order_id")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("order_confirmed_on","{0:dd/MM/yyyy}")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("total","{0:n}")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("payMode")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbSettlementRule" runat="server" Text='<%# Eval("br_name")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbSettlementDate" runat="server" Text='<%# Eval("customer_description")%>'></asp:Label>
                                                    </td>                                                                                                                                                            
                                                     <td align="center"> 
                                                         <div class="d-flex align-item-center">
<%--                                                             <asp:LinkButton runat="server"></i></asp:LinkButton>--%>
                                                             <asp:HyperLink runat="server" NavigateUrl='<%# String.Format("/Tenant/invoice.aspx?ordId={0}", Eval("order_id")) %>' Text="Invoice"><i class="fa-thin fa-eye"></i></asp:HyperLink>
                                                         </div>                                                                                              
                                                    </td>
                                                </tr>
                                            </ItemTemplate>                                          
                                            <EmptyDataTemplate>
                                                <div class="text-center">
                                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                                    <h6 class="mb-3">No record available</h6>
                                                </div>     
                                            </EmptyDataTemplate>
                                        </asp:ListView>
                                    </div>
                                     <asp:HiddenField ID="hidValueHeadOrderId" runat="server" />
                                     <asp:HiddenField ID="hidestoreid" runat="server" />
                                    <asp:SqlDataSource runat="server" ID="SDSorderdetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                        SelectCommand="SELECT  rc.order_order_id,rc.total,rc.order_branch_id,rc.order_confirmed_on,rc.order_id,rc.order_customer_id,rc.order_invoiceno,fb.br_Name,inv_number,CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 
                                                       THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet'  WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7  THEN 'Cash On Delivery' END AS payMode,rs.customer_description
                                                       FROM retaline_customer_order rc INNER JOIN finascop_branch fb  ON fb.br_ID = rc.order_branch_id LEFT JOIN invoice_number vn ON vn.order_id=rc.order_id INNER JOIN retaline_customer_order_status rs ON  rs.status_id= rc.status_id WHERE order_customer_id=@Id and storegroup_id=@storeid and rc.status_id >= 4">
                                        <SelectParameters>
                                            <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="Id" DefaultValue="0" />
                                             <asp:ControlParameter ControlID="hidestoreid" PropertyName="Value" Name="storeid" DefaultValue="0" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                </div>
                            </div>                           
                        </div>
                    </div>
                </div>

            </div>           
        </div>
    </div>
      <style>
       
        @media (min-width: 992px) {
            #Pupaction .modal-dialog {
                max-width: 1106px;
            }
        }        
    </style>
</asp:Content>
