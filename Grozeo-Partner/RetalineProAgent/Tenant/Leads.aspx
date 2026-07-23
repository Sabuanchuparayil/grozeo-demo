<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Leads" AutoEventWireup="true" CodeBehind="Leads.aspx.cs" Inherits="RetalineProAgent.Leads" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/crm">Leads & Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Leads</li>--%>
    
     <a href="/Navigations/crm"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Leads"></asp:Literal>
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Prospect Engagement</p>
    </div>
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
                <div class="col-lg-2 input-group mg-b-10 mg-lg-b-0" runat="server" visible="false">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                    <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                        <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataTextField="br_Name" DataValueField="br_ID" runat="server">
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
                <div class="col-lg-6 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by:</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name and phone number" CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                  </div>
                <div class="col-lg-2 d-flex justify-content-lg-end align-items-lg-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                </div>
                <div class="col-sm-4">
    <div class="float-right mt-4">
        <a href="/Tenant/LeadSettings" type="button" class="btn px-4 d-block d-md-inline-block btn-primary">Create Lead<i class="icon ion-plus-circled ml-2"></i></a></div>
</div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvLeads" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvLead_DataBound" DataSourceID="SDSLead">
                                    <Columns>
                                        <asp:BoundField HeaderText="Lead Name" DataField="name" SortExpression="name"/>
                                        <asp:BoundField HeaderText="Contact Number" DataField="phone" SortExpression="phone"/>
                                        <asp:BoundField HeaderText="Email Address" DataField="email" SortExpression="email"/>
                                        <asp:BoundField HeaderText ="Type" DataField="ctype" SortExpression="ctype" />
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

                <asp:SqlDataSource runat="server" ID="SDSLead" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="SELECT c.*, rc.storegroup_id, (CASE WHEN IFNULL(rc.storegroup_id, -1) < 0 THEN 'Lead' WHEN rc.storegroup_id = c.storeGroup THEN 'Customer' ELSE 'Prospect' END ) AS ctype FROM merchant_contact c LEFT JOIN retaline_customer rc ON CONVERT(c.phone, CHAR(20)) LIKE CONVERT(rc.cust_mobile, CHAR(20)) WHERE c.storeGroup=@storegroup AND (TRIM(IFNULL(@searchKey, '')) LIKE '' OR c.name LIKE CONCAT('%', @searchKey, '%') OR c.phone LIKE CONCAT('%', @searchKey, '%') OR c.email LIKE CONCAT('%', @searchKey, '%'))"
                    OnSelecting="SDSLead_Selecting">
                    <SelectParameters>
                        <asp:Parameter Name="storegroup" />
                        <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                    </SelectParameters>
                </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>
