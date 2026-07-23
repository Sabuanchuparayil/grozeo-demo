<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Retailer Leads" AutoEventWireup="true" CodeBehind="CRMRetailers.aspx.cs" Inherits="RetalineProAgent.CRMRetailers" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <script src="/Content/js/custom/home.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Retailers</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Retailers"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row row-sm">
                        <div class="col-12 col-lg-8 mb-2 mb-sm-0">

                            <nav class="navbar float-non float-lg-left mb-2 mb-lg-0 navbar-expand-lg bg-transparent p-0 justify-content-start align-items-end">
                                <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                                <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                                        <i class="fa fa-sliders" aria-hidden="true"></i>
                                    </span>
                                </button>


                                <div class=" collapse navbar-collapse flex-wrap" id="navbarSupportedContent">
                                    <ul class="navbar-nav mr-auto pt-2 pt-lg-0">
                                        <li class="nav-item ml-0 mr-lg-1 my-1 my-lg-0">
                                            <asp:LinkButton ID="lbtnRetailer" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary active">Retailer <span class="sr-only">(current)</span></asp:LinkButton>
                                        </li>
                                        <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                            <asp:LinkButton ID="lbtnMechant" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">Merchant</asp:LinkButton>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <div class="">
                                <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <div class="d-flex pl-0 pl-lg-2">
                                <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by store name, contact number & state" CssClass="form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary d-inline-block w-auto ml-2" runat="server">Search</asp:LinkButton>
                            </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex justify-content-lg-end mt-2 mt-lg-0">
                            <a href="javascript:void(0);" type="button" class="btn btn-primary pb-1 pt-1" data-toggle="modal" data-target="#modalAddRetailer"><i class="icon ion-plus-circled mr-2"></i>Add Retailer</a>
                        </div>
                    </div>
            </div>
                <div class="card-body">
                    <div id="accordion" class="table-responsive">
                        <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvRetailers" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvRetailers_DataBound" OnRowDataBound="gvRetailers_RowDataBound" DataSourceID="SDSRetailers">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Store Name" SortExpression="br_Name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <a href="#" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;
                                                <%# Eval("br_Name") %><br /><small><%# Eval("br_Address") %></small></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Contact" SortExpression="crco_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate><%# Eval("br_Incharge") %><br />
                                            <small><%# Eval("br_Phone") %></small></ItemTemplate>
                                    </asp:TemplateField>
                                        <asp:BoundField HeaderText="Area" DataField="areaName" SortExpression="areaName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="State" DataField="state" SortExpression="state" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate>
                                            <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>
                                            <tr>
                                                <td colspan="5" class="hiddenRow">
                                                    <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">
                                                        <asp:Button runat="server" ID="btnMerchant" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" storegroupId='<%# Eval("br_storeGroup") %>' Text='<%# GetButtonToolTip(Eval("store_group_grosmartMerchant")) %>' ToolTip='<%# GetButtonToolTip(Eval("store_group_grosmartMerchant")) %>' OnClientClick='<%# GetClientClickScript(Eval("store_group_grosmartMerchant")) %>' OnClick="btnMerchant_Click"/>
                                                        <% if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Deligation"))
                                                    { %>
                                                <%--<div class="d-flex py-2 border-bottom">--%>
                                                    <asp:LinkButton runat="server" OnClick="DeligateStore_Click" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Deligate" sgid='<%# Eval("br_storeGroup") %>' OnClientClick="return confirm('Are you sure you want to deligate to this store? The page will be redirected to the merchant page.')"></asp:LinkButton>
                                                <%--</div>--%>
                                                <%} %>
                                                        <asp:Button runat="server" ID="btnPendingActions" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" storegroupId='<%# Eval("br_storeGroup") %>' email='<%# Eval("br_Email") %>' phone='<%# Eval("br_Phone") %>' Text="Pending Actions" ToolTip="Pending Actions" OnClick="btnPendingActions_Click" />
                                                        <asp:Button runat="server" ID="btnPerformanceBoard" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" storegroupId='<%# Eval("br_storeGroup") %>' Text="Performance Board" ToolTip="Performance Board" OnClick="btnPerformanceBoard_Click"/>
                                                        <asp:LinkButton runat="server" ID="btnSourcedProducts" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" storegroupId='<%# Eval("br_storeGroup") %>' Text="Sourced Products" ToolTip="Sourced Products" Visible='<%# Convert.ToInt32(Eval("store_group_grosmartMerchant")) == 1 %>' OnClientClick='<%# "loadSourcedDetails(\"" + Eval("br_storeGroup") + "\"); return false;" %>'></asp:LinkButton>
                                                        <asp:LinkButton runat="server" ID="btnSponsoredSales" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" branchId='<%# Eval("br_ID") %>' storegroupId='<%# Eval("br_storeGroup") %>' Text="Sponsored Sales" ToolTip="Sponsored Sales" Visible='<%# Convert.ToInt32(Eval("store_group_grosmartMerchant")) == 1 %>' OnClientClick='<%# "loadSponsoredDetails(\"" + Eval("br_storeGroup") + "\", \"" + Eval("br_ID") + "\"); return false;" %>'></asp:LinkButton>
                                                    </div>
                                                </td>
                                            </tr>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <asp:Label ID="lblEmptyMessage" runat="server" Text=""></asp:Label>
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSRetailers" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT br_ID, br_Name, br_Address, store_group_name, br_Phone, br_Incharge, br_State, br_storeGroup, store_group_grosmartMerchant, areaid, br_Email, 
                                    (SELECT areaName FROM area_entries WHERE id=areaid) AS areaName, (SELECT st_name FROM finascop_state WHERE st_ID=br_State) AS state FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup=bg.store_group_id
                                    WHERE (b.areaid=@areaId OR bg.store_group_id IN(SELECT storeGroupId FROM  finascop_crm_prospect WHERE areaId = @areaId)) AND (ifnull(@filterType, 0) = 0 
                                    or (@filterType = 1 and store_group_grosmartMerchant=0)  
                                    or (@filterType = 2 and store_group_grosmartMerchant=1))
                                    AND (trim(ifnull(@searchKey, '')) like '' or br_Name like CONCAT('%', @searchKey, '%') or br_Phone like CONCAT('%', @searchKey, '%') or areaid like CONCAT('%', @searchKey, '%')) ORDER BY br_Name ASC" OnSelecting="SDSRetailerLeads_Selecting">
                            <SelectParameters>
                                <%--<asp:Parameter Name="baId" DefaultValue="0" />--%>
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                        <asp:HiddenField ID="hidSelectedItems" runat="server" />
                    </div>
                </div>
              </div>
            </div>
          </div>

    <!-- Pending Actions -->
    <div id="modalStoreDetails" class="modal fade">
        <div class="modal-dialog w-100 modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Pending Actions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="table-responsive border border-top-0">
                        <table class="table">
                            <tbody>
                                <asp:Repeater ID="rptPendingActions" runat="server">
                                    <ItemTemplate>
                                        <tr class="<%# Eval("Name") %>">
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa <%# new RetalineProAgent.PendingActions().GetContent(Eval("Name").ToString(), 1) %> mr-2 pendicon"></i>
                                                    <p class="m-0" style="line-height: 100%;"><%# Eval("Description") %></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <asp:PlaceHolder runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>'>
                                            <tr>
                                                <td class="align-middle" align="center" colspan="2">
                                                    <img style="max-height: 250px; max-width: 100%;" src="/content/images/no_pending_actions.png"></td>
                                            </tr>
                                        </asp:PlaceHolder>
                                    </FooterTemplate>
                                </asp:Repeater>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- modal-dialog -->
    </div>

    <!-- Performance Board -->
    <asp:HiddenField ID="hidstoreId" runat="server" />
    <div id="modalPerformanceBoard" class="modal fade" data-backdrop="static">
        <div class="modal-dialog w-100 modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h5 class="modal-title">Merchant Performace Board</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm mb-4">
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <div class="border p-2 w-100 coutbox-sec">
                                <p class="tx-14 tx-center mb-2 tx-dark">Sales Performance</p>
                                <div class="coutbox-wrap">
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark">Last 7 Dys</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrDaysSales" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark">This Week</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrWeekSales" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark">This Month</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrMonthSales" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="border p-2 w-100 coutbox-sec">
                                <p class="tx-14 tx-center mb-2 tx-dark">Revenue Performance</p>
                                <div class="coutbox-wrap">
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark tx-dark">Last 7 Dys</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrDaysAmt" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark tx-dark">This Week</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrWeekAmt" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                    <div class="coutbox border">
                                        <div class="tx-center tx-10 tx-dark tx-dark">This Month</div>
                                        <div class="tx-14 fw-bold tx-center tx-dark">
                                            <asp:Literal ID="ltrMonthAmt" runat="server"></asp:Literal>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- row -->

                    <!-- First table - Payout Details -->
                    <div class="card mb-3" runat="server" visible="false">
                        <div class="card-header bg-primary text-white">Payout Details</div>
                        <div class="card-body">
                        </div>
                    </div>

                    <!-- Second table -->
                    <div class="modal-body1">
                        <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Support Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <asp:Repeater ID="rptTickets" runat="server" DataSourceID="SDSSupportDetails">
                                    <ItemTemplate>
                                        <tr>
                                            <td>
                                                <div>
                                                    <span><%# Eval("created_On") %></span> - <%# Eval("ticketRemarks") %>
                                                    <span>- <%# Eval("STATUS") %></span> -
                                                    <a href="javascript:void(0);" class="btn pb-1 pt-1 view-ticket text-decoration-underline font-italic" style="text-decoration: underline;" data-ticketid="<%# Eval("ticketId") %>">View Details</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <tr>
                                            <td class="text-center">
                                                <asp:Label ID="defaultItem" CssClass="noitem" runat="server"
                                                    Visible='<%# rptTickets.Items.Count == 0 %>' Text="No items found" />
                                            </td>
                                        </tr>
                                    </FooterTemplate>
                                </asp:Repeater>
                            </tbody>
                        </table>
                    </div>
                    <asp:SqlDataSource runat="server" ID="SDSSupportDetails" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT sl.id, st.ticketId, ticketType, sl.ticketStatus, sl.ticketStage, ticketRemarks, ticketSupportUnit, 
                        sl.createdBy, sl.createdOn, DATE_FORMAT(sl.createdOn,'%d %b %Y') AS created_On,
                        IFNULL(su.name, 'Not Assigned') AS suName, ss.name AS STATUS, 
                        IFNULL(support_ticket_stages.name, 'Created') AS tiketStage,
                        CASE WHEN ticketType=1 THEN 'Internal Note' WHEN ticketType=2 THEN 'External Note' END AS ticketTypeName,
                        CONCAT(FirstName, ' ', LastName) AS createdByName
                        FROM support_ticket_log sl
                        INNER JOIN support_ticket st ON st.ticketId = sl.ticketId
                        LEFT JOIN support_unit su ON su.id = ticketSupportUnit
                        LEFT JOIN support_ticket_status ss ON ss.id = sl.ticketStatus 
                        LEFT JOIN support_ticket_stages ON support_ticket_stages.id = sl.ticketStage
                        LEFT JOIN finascop_usr_profile ON UserId = sl.createdBy 
                        WHERE st.CreatedBy = @storeId">
                        <SelectParameters>
                            <asp:ControlParameter ControlID="hidstoreId" PropertyName="Value" Name="storeId" DefaultValue="0" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- modal-dialog -->
    </div>

    <div id="ticketModal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalTicketContent">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Retailer -->
    <div id="modalAddRetailer" class="modal fade" data-backdrop="static">
        <div class="modal-dialog w-100 modal-dialog-vertical-center modal-lg" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <!-- Modal Header -->
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Add Retailer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Section Wrapper -->
                    <div class="section-wrapper p-0 border-0">
                        <div class="form-layout">
                            <div class="row mg-b-5">
                                <div class="col-sm-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Enter Code: <span class="tx-danger">*</span></label>
                                        <div class="d-flex pl-0">
                                            <input type="text" style="display: none" />
                                            <input type="password" style="display: none" />
                                            <div class="input_search_box position-relative" style="overflow:visible;">
                                                <asp:TextBox ID="txtSearch1" runat="server" CssClass="form-control" autocomplete="off"></asp:TextBox>
                                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtSearch1" CssClass="error_msg_wrap b--15" Display="Dynamic" ErrorMessage="Enter Code" ValidationGroup="InsertRetailer" ForeColor="Red"></asp:RequiredFieldValidator>
                                                <asp:LinkButton ID="lbtnSearch1" CssClass="btn bd bd-l-0 tx-gray-600" OnClick="lbtnSearch_Click" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- col-4 -->
                                <div class="col-sm-5 mb-3">
                                    <div class="form-group-sm">
                                        <label class="form-control-label">Store Name: <span class="tx-danger">*</span></label>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox ID="txtStoreName" runat="server" Enabled="false" CssClass="form-control" autocomplete="off" />
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store name is required" ValidationGroup="InsertRetailer" ForeColor="Red"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                                <div class="col-sm-3 mb-3" runat="server" id="dvCreateCode" visible="false">
                                    <div class="form-group-sm">
                                        <label class="form-control-label">Invitation Code: <span class="tx-danger">*</span></label>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox ID="txtGneratedCode" runat="server" Enabled="false" CssClass="form-control" autocomplete="off" />
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtGneratedCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Invitation code is required" ValidationGroup="InsertRetailer" ForeColor="Red"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                            </div>
                            <!-- row -->
                            <div class="form-layout-footer">
                                <asp:Button runat="server" ID="btnAdd" OnClick="btnRetailerSubmit_Click" CssClass="btn btn-primary" Text="Submit" ValidationGroup="InsertRetailer"/>&nbsp;
                                <a href="/Business/CRMRetailers" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                    <!-- Section Wrapper End -->
                </div>
            </div>
        </div>
    </div>

    <!-- Sourced Products -->
    <div class="modal fade" id="modalSourcedProducts" tabindex="-1" role="dialog" aria-labelledby="modalSourcedProductsLabel" aria-hidden="true">
    <div class="modal-dialog w-100 modal-dialog-vertical-center" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title">Sourced Products</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div id="dvpopupsourceddetails"></div>
            </div>
        </div>
    </div>
</div>

    <!-- Sponsored Sales -->
    <div class="modal fade" id="modalSponsoredSales" tabindex="-1" role="dialog" aria-labelledby="modalSponsoredSalesLabel" aria-hidden="true">
    <div class="modal-dialog w-100 modal-dialog-vertical-center" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title">Sponsored Sales</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div id="dvpopupsponsoreddetails"></div>
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

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Business/CRMRetailers";
            });
        });

    </script>
    <script type="text/javascript">
        function loadSourcedDetails(storegroupId) {
            $('#dvpopupsourceddetails').html('<div>Loading .. </div>');
            $('#dvpopupsourceddetails').load('/Business/SourceProducts/SourcedPrds?storegroupId=' + storegroupId, function () {
                $('#modalSourcedProducts').modal('show'); // Show modal after content is loaded
            });
        }

        function loadSponsoredDetails(storegroupId, branchId) {
            $('#dvpopupsponsoreddetails').html('<div>Loading .. </div>');
            $('#dvpopupsponsoreddetails').load('/Business/SponsoredSale/SponsoredPrdSales?storegroupId=' + storegroupId + '&branchId=' + branchId, function () {
                $('#modalSponsoredSales').modal('show'); // Show modal after content is loaded
            });
        }
    </script>
    <style>
        .coutbox-wrap {
            gap: 10px;
            display: flex;
            align-items: center;
        }

        .coutbox {
            width: 100%;
            padding: 5px;
        }

        .modal-body1 {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>

    

    <script type="text/javascript">
        $("input[data-bootstrap-switch], tb[data-bootstrap-switch] input[type=checkbox]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('tb[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
            $(this).prop('checked', !state);
            $(this).trigger('click');
        });
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1052 + ($('.modal:visible').length * 10);
            $(this).css('z-index', zIndex);
            setTimeout(() => {
                $('.modal-backdrop').not('.modal-backdrop:first').css('z-index', zIndex - 1);
            }, 0);
        });
    </script>

    <script>
        $(document).on("click", ".view-ticket", function () {
            var ticketId = $(this).data("ticketid");
            $("#modalTicketNo").text(ticketId); // Set Ticket No in modal title

            // Fetch Ticket Details via AJAX
            $.ajax({
                url: "/support/TicketDetalisView",
                type: "GET",
                data: { ticketId: ticketId },
                success: function (response) {
                    var modalContent = $("<div>").html(response); // Convert response to jQuery object
                    modalContent.find(".backbtn").remove(); // Remove Back button
                    $("#modalTicketContent").html(modalContent.html()); // Set cleaned content
                    $("#ticketModal").modal("show"); // Open modal
                },
                error: function () {
                    $("#modalTicketContent").html("<p class='text-danger'>Error loading ticket details.</p>");
                }
            });
        });
    </script>
    <style>
        .modal {
            z-index: 1051;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        .modal.show {
            z-index: 1052;
        }
    </style>
</asp:Content>
