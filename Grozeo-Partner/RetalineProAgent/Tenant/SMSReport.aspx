<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="SMS Report" AutoEventWireup="true" CodeBehind="SMSReport.aspx.cs" Inherits="RetalineProAgent.SMSReport" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/crm">Leads & Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Leads</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="SMS Report"></asp:Literal></h6>
        <p class="mb-0">SMS Report</p>
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
                <div class="col-sm-6 form-group mb-2 mb-sm-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by:</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by phone number or SMS" CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                  </div>
                <div class="col-sm-2 d-flex align-items-sm-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                </div>
                
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvSms" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="20" OnDataBound="gvSms_DataBound" DataSourceID="SDSSms">
                                    <Columns>
                                        <asp:BoundField HeaderText="Mobile" DataField="smsemail_id" SortExpression="smsemail_id"/>
                                        <asp:BoundField HeaderText="Date" DataField="smsemail_datetime" SortExpression="smsemail_datetime" ItemStyle-Width="150px"/>
                                        <asp:BoundField HeaderText="SMS" DataField="smsemail_text" SortExpression="smsemail_text"/>
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

                                <asp:SqlDataSource runat="server" ID="SDSSms" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT smsemaillog_id,smsemail_id,DATE_FORMAT(smsemail_datetime,'%d %b %Y  %H:%i') AS smsemail_datetime,smsemail_text,issms,sms_responseid,storeGroupId FROM sms_email_logs WHERE issms = 1 AND storeGroupId=@storegroup 
                                    AND (trim(ifnull(@searchKey, '')) like '' or smsemail_id like CONCAT('%', @searchKey, '%') or smsemail_text like CONCAT('%', @searchKey, '%'))  ORDER BY smsemaillog_id DESC"
        OnSelecting="SDSSms_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>
