<%@ Page Language="C#" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Order Pickers" AutoEventWireup="true" CodeBehind="OrderPicker.aspx.cs" Inherits="RetalineProAgent.OrderPicker" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Users">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Order Picker</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <li class="breadcrumb-item"><a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Order Pickers"></asp:Literal> at
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Streamline Order Processing</p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-sm-4 col-lg-2 input-group mb-2 mb-sm-0">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
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
                <div class="col-sm-4 form-group mb-2 mb-sm-0">
                    <label class="form-control-label mb-1 w-100 tx-dark">Search: </label>
                    <input type="text" style="display: none" />
                    <input type="password" style="display: none" />
                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name and phone number" CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                </div>
                <div class="col-sm-4 col-lg-2 d-flex justify-content-lg-end align-items-sm-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/OrderPicker.aspx" Text="Reset" />
                </div>
                <div class="col-sm-4">
                    <div class="float-left float-lg-right mt-3 mt-lg-4">
                        <a href="/Tenant/OrderPickerSettings" type="button" class="btn px-4 d-block d-md-inline-block btn-primary">Create Order Picker<i class="icon ion-plus-circled ml-2"></i></a>
                    </div>
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvOrderPicker" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvOrderPicker_DataBound" DataSourceID="SDSOrderPicker">
                                    <Columns>
                                        <asp:BoundField HeaderText="Order Picker" DataField="name" SortExpression="name"/>
                                        <asp:BoundField HeaderText="Phone" DataField="phone" SortExpression="phone"/>
                                        <asp:BoundField HeaderText="Retailer Name" DataField="cpd_name" SortExpression="cpd_name"/>
                                        <asp:BoundField HeaderText="Online Status" DataField="is_offline" SortExpression="is_offline"/>
                                        <asp:BoundField HeaderText="Manual Schedule" DataField="is_allowManualSchedule" SortExpression="is_allowManualSchedule"/>
                                        <asp:BoundField HeaderText="Auto Schedule" DataField="is_allowAutoSchedule" SortExpression="is_allowAutoSchedule"/>
                                        <asp:BoundField HeaderText="Location Updated At" DataField="latlng_updated_at" SortExpression="latlng_updated_at" Visible="false"/>
                                        <asp:BoundField HeaderText="Status" DataField="statusName" SortExpression="statusName"/>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <tb data-bootstrap-switch><asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" AutoPostBack="true" runat="server" boyId='<%# Eval("id") %>' brid='<%# Eval("cpdid") %>' Checked='<%# Eval("status").ToString().Equals("1") %>'/></tb>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:HyperLinkField runat="server" Text="Edit" NavigateUrl="~/Tenant/OrderPickerSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Tenant/OrderPickerSettings?id={0}" />
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

                                <asp:SqlDataSource runat="server" ID="SDSOrderPicker" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSOrderPicker_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT id,name, 
            phone,branch_id AS cpdid,is_cpd,status,IF(status = 1,'Active','Inactive') AS statusName,                 
            CASE  WHEN is_cpd = 1 THEN 'CPD' 
                    WHEN is_cpd = 2 THEN 'Central Store'
                    WHEN is_cpd = 3 THEN 'Distributor'
                    WHEN is_cpd = 4 THEN 'Retailer' END AS type,
                    IF((is_offline=1),'Offline','Online') AS is_offline,
                    latlng_updated_at,
                    IF((is_allowAutoSchedule=1),'Yes','No')  as is_allowAutoSchedule,
                    IF((is_allowManualSchedule=1),'Yes','No')  as is_allowManualSchedule,
                    IF((branch_type_id=1),(SELECT br_Name FROM finascop_branch cp WHERE br_ID = cpdid),(SELECT stpa_Fname FROM finascop_stock_party  WHERE stpa_id = cpdid))  as cpd_name
                    from retaline_godown_boy gb INNER JOIN finascop_branch fb ON gb.branch_id=fb.br_ID WHERE fb.br_storeGroup=@storegroup AND (@branchId <= 0 or gb.branch_id=@branchId)
 AND (trim(ifnull(@searchKey, '')) like '' or name like CONCAT('%', @searchKey, '%') or phone like CONCAT('%', @searchKey, '%')) ORDER BY name ASC"
        OnSelecting="SDSOrderPicker_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="selBranches" Name="branchid" />
            <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
                    </div>
        </div><!-- card-body -->
    </div><!-- card -->
        
    <script type="text/javascript">
        

        $("input[data-bootstrap-switch], tb[data-bootstrap-switch] input[type=checkbox]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('tb[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
            $(this).prop('checked', !state);
            $(this).trigger('click');
        });

    </script>
</asp:Content>
