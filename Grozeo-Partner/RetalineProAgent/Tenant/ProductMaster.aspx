<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Product Master" AutoEventWireup="true" CodeBehind="ProductMaster.aspx.cs" Inherits="RetalineProAgent.ProductMaster" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Product Master</li>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Product Master"></asp:Literal> at
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                      <div class="card-tools">
                          <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <%--<div class="mg-l-10" style="color:black; width:235px">
                    <asp:Literal ID="ltrTitle" runat="server" Text="Delivery boys at"></asp:Literal>
                        </div>--%>
        <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                     <span class="tx-dark mr-2">
                        <asp:Literal ID="ltrBranch" runat="server">Branch</asp:Literal>
                    </span>
                   <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="wd-50p-force bd p-2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server"><asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
                </asp:PlaceHolder>

<asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
                    <div class="input-group">
                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search" CssClass="p-1" autocomplete="nofill"></asp:TextBox> 
                    <span class="input-group-btn">
                        <button class="btn-primary bd-transparent pl-3 pr-3 pt-1 pb-1" type="button"><i class="fa fa-search"></i></button>
                    <%--<asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>--%>
                        </span>
                    </div>
                </div>
                              
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="input-group-btn">
                    <a href="/Tenant/ProductMasterSettings" type="button" class="btn btn-info pb-1 pt-1">
    <i class="icon ion-plus-circled mr-2"></i>Create Product Master</a>
                    
<div class="float-right ml-3 tx-dark">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                              <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm page-link">
                      <i class="fa fa-angle-left"></i>
                      </asp:LinkButton>
                              <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm page-link">
                          <i class="fa fa-angle-right"></i>
                      </asp:LinkButton>
                  </div>
                  <!-- /.btn-group -->
                        </div>
                        </div>
                    </div>
                </div>
              </div>
            </div>
                    
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvProductMaster" runat="server" CssClass="table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvProductMaster_DataBound" DataSourceID="SDSProductMaster">
                                    <Columns>
                                        <asp:BoundField HeaderText="Product Master Name" DataField="item_name" SortExpression="item_name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Status" DataField="status" SortExpression="status" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Is Verified" DataField="isVerified" SortExpression="isVerified" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Grouping" DataField="itemDisplayName" SortExpression="itemDisplayName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Tenant/ProductMasterSettings" DataNavigateUrlFields="itemname_id" DataNavigateUrlFormatString="~/Tenant/ProductMasterSettings?id={0}" />
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No products created.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSProductMaster" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT itemname_id,item_name,isItemGroup,IF((isItemGroup=1),'Yes','No') AS itemDisplayName,
IF((STATUS=1),'Active','Inactive')AS STATUS,IF((isVerified=1),'Yes','No')AS isVerified FROM finascop_stock_itemmastername
 WHERE (trim(ifnull(@searchKey, '')) like '' or item_name like CONCAT('%', @searchKey, '%') or status like CONCAT('%', @searchKey, '%'))">
        <SelectParameters>
            <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
            </div>

            </div>
   
</asp:Content>
