<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Delivery Staffs" AutoEventWireup="true" CodeBehind="DeliveryStaffs.aspx.cs" Inherits="RetalineProAgent.DeliveryStaffs" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
     <li class="breadcrumb-item"><a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Delivery Agents"></asp:Literal> at
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
    <p class="mb-0">Timely Deliveries Ensured</p>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-lg-2 input-group mg-b-10 mg-lg-b-0">
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
                <div class="col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label w-100 mb-1 tx-dark">Search</label>
                    <input type="text" style="display: none" />
                    <input type="password" style="display: none" />
                    <asp:TextBox ID="txtFindDBoys" runat="server" placeholder="Search by name, address, phone, etc." CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                </div>
                <div class="col-lg-2 d-flex justify-content-lg-end align-items-lg-end">
                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                    </div>
                <div class="col-sm-4">
                    <div class="float-left float-lg-right mt-3 mt-lg-4">
                        <a href="/Tenant/DeliveryStaffCreate" type="button" class="btn px-4 d-block d-md-inline-block btn-primary">Create Delivery Staff<i class="icon ion-plus-circled ml-2"></i></a>
                </div>
            </div>
          </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliverBoy" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryBoy_DataBound" DataSourceID="SDSDeliveryBoy" DataKeyNames="d_ID,d_Name">
                                    <Columns>
                                        <asp:BoundField HeaderText="Driver" DataField="d_Name" SortExpression="d_Name"/>
                                        <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address"/>
                                        <asp:BoundField HeaderText="Phone" DataField="d_Ph1" SortExpression="d_Ph1"/>
                                        <asp:BoundField HeaderText="Auto Schedule" DataField="d_isallowAutoSchedule" SortExpression="d_isallowAutoSchedule"/>
                                        <asp:BoundField HeaderText="Manual Schedule" DataField="d_isallowManualSchedule" SortExpression="d_isallowManualSchedule"/>
                                        <asp:BoundField HeaderText="Store" DataField="branch" SortExpression="branch"/>
                                        <asp:BoundField HeaderText="Login Status" DataField="driverOnlineStatus" SortExpression="branch"/>
                                        <asp:TemplateField HeaderText="Live Status">
                                            <ItemTemplate>
                                                <asp:Label ID="lblLiveStatus" runat="server" Text="..." />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:HyperLinkField runat="server" Text="Edit" NavigateUrl="~/Tenant/DeliveryStaffCreate" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/Tenant/DeliveryStaffCreate?id={0}" />
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

                                <asp:SqlDataSource runat="server" ID="SDSDeliveryBoy" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT d_ID,d_apikey,IF(d_apikey IS NOT NULL AND d_apikey != '' AND d_apikey != '-', 'Active', 'Inactive') AS driverOnlineStatus,d_Name,IF(d_Add1 <> '', CONCAT_WS(',', NULLIF(d_Add1, ''), NULLIF(d_Add2, ''), NULLIF(d_Add3, '') ),'NIL') AS address, d_Ph1,(SELECT br_Name FROM finascop_branch WHERE 
                                    br_id=qugeo_driver.br_id) AS branch,IF((d_isallowAutoSchedule=1),'Yes','No')  AS d_isallowAutoSchedule,  
                                    IF((d_isallowManualSchedule=1),'Yes','No')  AS d_isallowManualSchedule FROM qugeo_driver 
                                    INNER JOIN finascop_branch fb ON qugeo_driver.br_id=fb.br_ID WHERE fb.br_storeGroup=@storegroup AND (@branchId <= 0 or qugeo_driver.br_id=@branchId)
                                    AND (trim(ifnull(@searchKey, '')) like '' or d_Name like CONCAT('%', @searchKey, '%') or d_Ph1 like CONCAT('%', @searchKey, '%') or CONCAT_WS(',',d_Add1,d_Add2,d_Add3) like CONCAT('%', @searchKey, '%') ) ORDER BY d_Name ASC"
        OnSelecting="SDSDeliveryBoy_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter Name="searchKey" ControlID="txtFindDBoys" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selBranches" PropertyName="Text" Name="branchId" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>
