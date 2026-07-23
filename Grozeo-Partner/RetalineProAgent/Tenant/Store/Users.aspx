<%@ Page Language="C#" AutoEventWireup="true" Title="Users" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Users.aspx.cs" Inherits="RetalineProAgent.Users" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Users">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Admin Users</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="Store Users"></asp:Literal></h6> 
    <p class="mb-0">Seamless System Control</p>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <asp:SqlDataSource runat="server" ID="SDSUsers" ConnectionString="<%$ ConnectionStrings:conn %>" OnSelecting="SDSUsers_Selecting"
         SelectCommand="SELECT u.*, ur.RoleName FROM [User] u 
left join User_UserRole_Mapping um on um.UserId=u.Id and (um.RoleId=1 or um.RoleId=2 or um.StoreGroupId= @storegroupid)
        left join UserRole ur on ur.Id=um.RoleId
		where (um.RoleId is null or um.RoleId >= (select top 1 RoleId from User_UserRole_Mapping m inner join [User] u on u.Id=m.UserId where u.Email like @user order by RoleId asc))
and (EXISTS(SELECT * FROM User_UserRole_Mapping where StoreGroupId = @storegroupid and UserId=u.Id)) 
AND (isnull(@Search, '') = '' OR Email like '%'+@Search+'%' OR Mobile like '%'+@Search+'%'
         OR FullName like '%'+@Search+'%' OR [Address] like '%'+@Search+'%' OR [City] like '%'+@Search+'%')">
        <SelectParameters>
            <%--<asp:ControlParameter ControlID="txtFindUsers" DefaultValue="" Type="String" Name="Search" ConvertEmptyStringToNull="false" />--%>
            <asp:Parameter Name="Search" DefaultValue="" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storegroupid" Type="Int32" DefaultValue="-1" />
            <asp:Parameter Name="user" DefaultValue="" />
        </SelectParameters>
    </asp:SqlDataSource>
        
    <div class="card">
        <div class="card-body">
        <div class="p-3 shadow_top">
                <div class="row row-sm">
                    <div class="col-12 col-sm-9">
                        <h6 class="mb-1 tx-dark">Admin Users</h6>
                        <p class="mg-b-0">Seamlessly manage admin user accounts for efficient system operations.</p>
                    </div>
                    <div class="col-sm-3 mt-2 mt-sm-0 d-flex align-items-start justify-content-sm-end">
                        <a href="/Tenant/Store/ManageUser" class="btn px-4 d-block d-md-inline-block btn-primary">Add User <i class="icon ion-plus-circled ml-2"></i></a>
                    </div>
                </div>
            </div>
        <div class="table-responsive">
              <asp:GridView ID="gvUser" runat="server" GridLines="None" DataSourceID="SDSUsers" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                  <Columns><asp:BoundField HeaderText="Full Name" DataField="FullName" />
                      <asp:BoundField HeaderText="Phone" DataField="Mobile" />
                      <asp:BoundField HeaderText="Email" DataField="Email" />
                      <asp:BoundField HeaderText="Role" DataField="RoleName" />
                      <asp:TemplateField ItemStyle-HorizontalAlign="Center">
                          <ItemTemplate>
                              <%--<span class="tag tag-success"><%# (((bool)Eval("[Status]") ? "Active":"Disabled")) %></span>--%>
                              <asp:HyperLink runat="server" NavigateUrl='<%# String.Format("/Tenant/Store/ManageUser?id={0}", Eval("Id")) %>' Text="Edit"></asp:HyperLink>
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
            
          </div><!-- table-responsive -->
        
    </div><!-- card-body -->
        <div class="card-body mt-3">
        <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-sm-9">
                    <h6 class="mb-1 tx-dark">Order Pickers</h6>
                    <p class="mg-b-0">Effortless order picker account management for swift fulfillment.</p>
                </div>
                <div class="col-sm-3 mt-2 mt-sm-0 d-flex align-items-start justify-content-sm-end">
                    <a href="/Tenant/OrderPickerSettings" class="btn px-4 d-block d-md-inline-block btn-primary">Add <i class="icon ion-plus-circled ml-2"></i></a>
                </div>
            </div>
        </div>

        <div class="table-responsive">

            <asp:GridView AutoGenerateColumns="false" ID="gvOrderPicker" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" 
                AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSOrderPicker">
                <Columns>
                    <asp:BoundField HeaderText="Order Picker" DataField="name" SortExpression="name" />
                    <asp:BoundField HeaderText="Phone" DataField="phone" SortExpression="phone" />
                    <asp:BoundField HeaderText="Retailer Name" DataField="cpd_name" SortExpression="cpd_name" />
                    <asp:BoundField HeaderText="Status" DataField="is_offline" SortExpression="is_offline" />
                    <asp:BoundField HeaderText="Manual Schedule" DataField="is_allowManualSchedule" SortExpression="is_allowManualSchedule" />
                    <asp:BoundField HeaderText="Auto Schedule" DataField="is_allowAutoSchedule" SortExpression="is_allowAutoSchedule" />
                    <asp:BoundField HeaderText="Location Updated At" DataField="latlng_updated_at" SortExpression="latlng_updated_at" />
                    <asp:BoundField HeaderText="Status" DataField="statusName" SortExpression="statusName" />
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

            <asp:SqlDataSource runat="server" ID="SDSOrderPicker" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT id,name, 
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
                    from retaline_godown_boy gb INNER JOIN finascop_branch fb ON gb.branch_id=fb.br_ID WHERE fb.br_storeGroup=@storegroup "
                OnSelecting="SDSOrderPicker_Selecting">
                <SelectParameters>
                    <asp:Parameter Name="storegroup" />
                </SelectParameters>
            </asp:SqlDataSource>
        </div><!-- table-responsive -->
        
    </div><!-- card-body -->
        <div class="card-body mt-3">

        <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-sm-9">
                    <h6 class="mb-1 tx-dark">Delivery Staffs</h6>
                    <p class="mg-b-0">Effortlessly manage delivery staff accounts for prompt shipments.</p>
                </div>
                <div class="col-sm-3 mt-2 mt-sm-0 d-flex align-items-start justify-content-sm-end">
                    <a href="/Tenant/DeliveryStaffCreate" class="btn px-4 d-block d-md-inline-block btn-primary">Add <i class="icon ion-plus-circled ml-2"></i></a>
                </div>
            </div>
        </div>


        <div class="table-responsive">

            <asp:GridView AutoGenerateColumns="false" ID="gvDeliverBoy" runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSDeliveryBoy">
                <Columns>
                    <asp:BoundField HeaderText="Driver" DataField="d_Name" SortExpression="d_Name" />
                    <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address" />
                    <asp:BoundField HeaderText="Phone" DataField="d_Ph1" SortExpression="d_Ph1" />
                    <asp:BoundField HeaderText="Auto Schedule" DataField="d_isallowAutoSchedule" SortExpression="d_isallowAutoSchedule" />
                    <asp:BoundField HeaderText="Manual Schedule" DataField="d_isallowManualSchedule" SortExpression="d_isallowManualSchedule" />
                    <asp:BoundField HeaderText="Branch" DataField="branch" SortExpression="branch"/>
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
                SelectCommand="SELECT d_ID, d_Name,CONCAT_WS(',',d_Add1,d_Add2,d_Add3) AS address, d_Ph1,(SELECT br_Name FROM finascop_branch WHERE 
br_id=qugeo_driver.br_id) AS branch,IF((d_isallowAutoSchedule=1),'Yes','No')  AS d_isallowAutoSchedule,  IF((d_isallowManualSchedule=1),'Yes','No')  AS d_isallowManualSchedule FROM qugeo_driver 
INNER JOIN finascop_branch fb ON qugeo_driver.br_id=fb.br_ID WHERE fb.br_storeGroup=@storegroup"
                OnSelecting="SDSDeliveryBoy_Selecting">
                <SelectParameters>
                    <asp:Parameter Name="storegroup" />
                </SelectParameters>
            </asp:SqlDataSource>

        </div>
        <!-- table-responsive -->
        
    </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>