<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Delivery Staff" AutoEventWireup="true" CodeBehind="DeliveryStaff.aspx.cs" Inherits="RetalineProAgent.DeliveryStaff" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/Resources">Resources</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery Staffs</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Delivery staffs"></asp:Literal></h6>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                                      <div class="col-lg-5">
                                          <label class="form-control-label w-100 mb-1">Search: </label>
                                          <input type="text" style="display:none" />
                                          <input type="password" style="display:none" />
                                          <div class="d-flex">
                                            <asp:TextBox ID="txtFindDBoys" runat="server" placeholder="Search by name, address, phone, etc." CssClass="p-1 form-control" autocomplete="nofill"></asp:TextBox>
                                            <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:32px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                          </div>
                                      </div>

<div class="col-lg-7">
    <div class="float-right mt-4"><a href="/Business/DeliveryStaffSettings" type="button" class="btn btn-primary pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Delivery Staff</a></div>
</div>

</div>            
            </div>
                    
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliverBoy" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryBoy_DataBound" DataSourceID="SDSDeliveryBoy">
                                    <Columns>
                                        <asp:BoundField HeaderText="Driver" DataField="d_Name" SortExpression="d_Name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Phone" DataField="d_Ph1" SortExpression="d_Ph1" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Auto Schedule" DataField="d_isallowAutoSchedule" SortExpression="d_isallowAutoSchedule" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Manual Schedule" DataField="d_isallowManualSchedule" SortExpression="d_isallowManualSchedule" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/DeliveryStaffSettings" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/Business/DeliveryStaffSettings?id={0}" />
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No delivery staff created.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSDeliveryBoy" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT d_ID, d_Name,CONCAT_WS(',',d_Add1,d_Add2,d_Add3) AS address, d_Ph1,(SELECT br_Name FROM finascop_branch WHERE 
                                    br_id=qugeo_driver.br_id) AS branch,IF((d_isallowAutoSchedule=1),'Yes','No')  AS d_isallowAutoSchedule,  
                                    IF((d_isallowManualSchedule=1),'Yes','No')  AS d_isallowManualSchedule FROM qugeo_driver WHERE sourceId=@baId
                                    AND (trim(ifnull(@searchKey, '')) like '' or d_Name like CONCAT('%', @searchKey, '%') or d_Ph1 like CONCAT('%', @searchKey, '%') or CONCAT_WS(',',d_Add1,d_Add2,d_Add3) like CONCAT('%', @searchKey, '%') ) ORDER BY d_Name ASC"
                                    OnSelecting="SDSDeliveryBoy_Selecting">
        <SelectParameters>
            <asp:Parameter Name="baId" />
            <asp:ControlParameter Name="searchKey" ControlID="txtFindDBoys" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
            </div>

            </div>
</asp:Content>
