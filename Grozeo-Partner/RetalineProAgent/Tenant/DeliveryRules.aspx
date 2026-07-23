<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Delivery Rules" AutoEventWireup="true" CodeBehind="DeliveryRules.aspx.cs" Inherits="RetalineProAgent.DeliveryRules" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Delivery">Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery Rules</li>--%>
    <a href="/Navigations/Delivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Delivery Rules"></asp:Literal> at
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Customizable Delivery Parameters</p>
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
                <div class="col-lg-4 input-group mg-b-10 mg-lg-b-0">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                    <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                        <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" style="margin-right: 10px;" AppendDataBoundItems="true" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                            <asp:ListItem Text="Select Branch" Value=""></asp:ListItem>
                        </asp:DropDownList>
                        <div><asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="ManageRule" Text="Select Store" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator></div>
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
                <%--<div class="col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark">Search: </label>
                    <input type="text" style="display: none" />
                    <input type="password" style="display: none" />
                    <asp:TextBox ID="txtFindDeliRules" runat="server" placeholder="Search by rule name" CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                </div>
                <div class="col-lg-2 d-flex justify-content-lg-end align-items-lg-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                </div>--%>
                <div class="col-sm-8">
                    <div class="float-left float-lg-right mt-3 mt-lg-4">
                        <asp:LinkButton ID="lbManageRule" runat="server" CssClass="btn btn-primary mt-2 mt-lg-0" OnClick="lbManageRule_Click" ValidationGroup="ManageRule">Manage Delivery Rates<i class="icon ion-plus-circled ml-2"></i></asp:LinkButton>
                        <%--<a href="/Tenant/DeliveryRuleSettings" type="button" class="btn btn-primary mt-2 mt-lg-0">Manage Delivery Rule<i class="icon ion-plus-circled ml-2"></i></a>--%>
                    </div>
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliveryRules" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryRules_DataBound" DataSourceID="SDSDeliveryRules" OnRowDataBound="gvDeliveryRules_RowDataBound">
                                    <Columns>
                                        <asp:BoundField HeaderText="Rule Name" DataField="rdr_ruleName" SortExpression="rdr_ruleName" />
                                        <asp:BoundField HeaderText="Rule Type" DataField="ruleType" SortExpression="ruleType" />
                                        <asp:BoundField HeaderText="Delivery Mode" DataField="deliveryMode" SortExpression="deliveryMode" />
                                        <asp:BoundField HeaderText="Calculation Mode" DataField="calculationMode" SortExpression="calculationMode" />
                                        <asp:BoundField HeaderText="Free Above Rs" DataField="freeDelivery" SortExpression="freeDelivery" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <tb data-bootstrap-switch><asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" AutoPostBack="true" runat="server" itemid='<%# Eval("id") %>' Checked='<%# Eval("status").Equals("Active") %>'/></tb>
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
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
<%--                   OR rdr_ruleForId=0 --%>
                                <asp:SqlDataSource runat="server" ID="SDSDeliveryRules" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT rdr_id,rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_ruleFor,is_default,
                                    CASE WHEN rdr_deliveryMode = 1 THEN 'Courier Delivery'
                                    WHEN rdr_deliveryMode = 2 THEN 'Express Delivery'
                                    WHEN rdr_deliveryMode = 3 THEN 'Scheduled Delivery'
                                    END AS deliveryMode,
                                    CASE WHEN rdr_calculationMode = 1 THEN 'Distance Rate'
                                    WHEN rdr_calculationMode = 2 THEN 'Flat Rate'
                                    END AS calculationMode, CASE WHEN rdr_ruleFor = 1 THEN 'Common Rule'
                                    WHEN rdr_ruleFor = 2 THEN 'Store Group' WHEN rdr_ruleFor = 3 THEN 'Store'
                                    END AS ruleType,rdr_isfreeDelivery,IF(rdr_isfreeDelivery = 1,rdr_isfreeDeliveryAmt,0) AS freeDelivery,
                                    rdr_isfreeDeliveryAmt FROM retaline_delivery_rules where is_default = 1 OR rdr_storeGroupId= @storegroupid  AND (@branchId <= 0 OR rdr_ruleFor = 3 AND rdr_ruleForId = @branchId) OR (rdr_storeGroupId=@storegroupid AND rdr_ruleForId=0) ">
                                    <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <%--<asp:ControlParameter Name="searchKey" ControlID="txtFindDeliRules" ConvertEmptyStringToNull="false" />--%>
            <asp:ControlParameter ControlID="selBranches" PropertyName="Text" Name="branchId" />
        </SelectParameters>
                                </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
          
</asp:Content>
