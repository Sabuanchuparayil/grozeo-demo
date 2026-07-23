<%@ Page Language="C#" AutoEventWireup="true" Title="Delivery Slot" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="DeliverySlot.aspx.cs" Inherits="RetalineProAgent.DeliverySlot" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Delivery">Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Delivery Slot</li>--%>
    <a href="/Navigations/Delivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Delivery Slot"></asp:Literal> of 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Flexible Time Windows</p>
    </div>
    
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm mt-2">
                <div class="col-lg-2 input-group mg-b-10 mg-lg-b-0">
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
                <div class="col-lg-3 form-group mb-2 mb-lg-0">
                    <label for="ddlTimeFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">Time From:</label>
                    <asp:DropDownList ID="ddlTimeFrom" runat="server" CssClass="form-control select2"></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlTimeFrom" ForeColor="Red" CssClass="errormsg" ErrorMessage="Please select 'time from'" Display="Dynamic" ValidationGroup="AddSlot"></asp:RequiredFieldValidator>
                </div>
                <div class="col-lg-3 input-group mg-b-10 mg-lg-b-0">
                    <label for="ddlTimeTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">Time To:</label>
                    <asp:DropDownList ID="ddlTimeTo" runat="server" CssClass="form-control select2"></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlTimeTo" CssClass="errormsg" ForeColor="Red" ErrorMessage="Please select 'time to'" Display="Dynamic" ValidationGroup="AddSlot"></asp:RequiredFieldValidator>
                </div>
                <div class="col-lg-2 input-group mg-b-10 mg-lg-b-0">
                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Max / Slot:</label>
                    <asp:TextBox ID="txtSlot" runat="server" CssClass="form-control select2"></asp:TextBox>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtSlot" ForeColor="Red" CssClass="errormsg" ErrorMessage="Please select 'max slot'" Display="Dynamic" ValidationGroup="AddSlot"></asp:RequiredFieldValidator>
                </div>
                <div class="col-lg-2 d-flex flex-wrap">
                    <label class="mb-4 d-none d-lg-inline-block w-100"></label>
                    <asp:LinkButton ID="lbtnAdd" runat="server" OnClick="btnAdd_Click" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" Text="Add" ValidationGroup="AddSlot" />
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvDelivSlot" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDelivSlot_DataBound" DataSourceID="SDSDelivSlot">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Time From" SortExpression="rbds_time_from">
                                            <ItemTemplate><%# FormatToShotTime(Eval("rbds_time_from")) %></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Time To" SortExpression="rbds_time_to">
                                            <ItemTemplate><%# FormatToShotTime(Eval("rbds_time_to")) %></ItemTemplate>
                                        </asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Time From" DataField="rbds_time_from" SortExpression="rbds_time_from" />
                                        <asp:BoundField HeaderText="Time To" DataField="rbds_time_to" SortExpression="rbds_time_to"/>--%>
                                        <asp:BoundField HeaderText="Slot / Day" DataField="rbds_time_maxslot" SortExpression="rbds_time_maxslot"/>
                                        <asp:TemplateField ItemStyle-HorizontalAlign="Center">
                                            <ItemTemplate>
                                                <asp:LinkButton runat="server" OnClick="DeleteItem_Click" slotId='<%# Eval("rbds_id") %>'  style="color:#DC3545;" ForeColor="#dc3545" OnClientClick="return confirm('Are you sure you want to delete this time slot?');" ><i class="fa fa-trash"></i></asp:LinkButton>
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

                                <asp:SqlDataSource runat="server" ID="SDSDelivSlot" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT rbds_id,branch_id,rbds_time_from,rbds_time_to,rbds_time_maxslot FROM  
retaline_branch_delivery_slot rpgtr INNER JOIN finascop_branch fb ON branch_id=fb.br_ID WHERE br_storeGroup=@storegroup AND branch_id=@branchId"
                                    OnSelecting="SDSDelivSlot_Selecting">
                                    <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter ControlID="selBranches" Name="branchid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
            </div><!-- card-body -->
        <div class="card-footer d-flex flex-wrap justify-content-lg-between">
            <div class="pagination-wrapper col-12 col-lg-6 p-0 pr-md-2 d-flex justify-content-center justify-content-lg-start">
                    <%--<ul class="pagination pagination-circle mg-b-0 mb-2 mb-lg-0">
                      <li class="page-item hidden-xs-down">
                        <a class="page-link" href="#" aria-label="First"><i class="fa fa-angle-double-left"></i></a>
                      </li>
                      <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous"><i class="fa fa-angle-left"></i></a>
                      </li>
                      <li class="page-item active"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">2</a></li>
                      <li class="page-item hidden-xs-down"><a class="page-link" href="#">3</a></li>
                      <li class="page-item hidden-xs-down"><a class="page-link" href="#">4</a></li>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                      <li class="page-item"><a class="page-link" href="#">10</a></li>
                      <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next"><i class="fa fa-angle-right"></i></a>
                      </li>
                      <li class="page-item hidden-xs-down">
                        <a class="page-link" href="#" aria-label="Last"><i class="fa fa-angle-double-right"></i></a>
                      </li>
                    </ul>--%>
                </div>
            <div class="d-flex align-items-center justify-content-end">
                <div class="d-flex align-items-center flex-wrap flex-sm-nowrap">
                    <label class="form-control-label mr-3 mt-1 tx-dark">Packing Before:</label>
                    <div class="d-flex flex-wrap mr-3 align-items-center" style="width: 90px;">
                        <div class="input-group-sm d-flex w-100">
                            <asp:TextBox runat="server" ID="txtTime" min="0" CssClass="form-control rounded-0 w-100" TextMode="Number" Text="Packing Before"></asp:TextBox>
                            <div class="input-group-prepend">
                                <div class="input-group-text rounded-0">hrs</div>
                            </div>
                        </div>
                        <asp:RequiredFieldValidator runat="server" Display="Dynamic" ControlToValidate="txtTime" ErrorMessage="Please input time" ForeColor="Red" ValidationGroup="DeliveSlot" Style="color: Red; visibility: hidden; font-size: 10px;"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <asp:Button runat="server" ID="btnSave" fsto_id='<%# Eval("id") %>' fsto_uid='<%# Eval("fsto_uid") %>' ValidationGroup="DeliveSlot" OnClick="btnSave_Click" CssClass="btn btn-primary mt-0 px-3" Text="Save" />
            </div>
        </div><!-- card-footer -->
    </div><!-- card -->

    <style>
  .h-28{
    height: 28px;
  }
    .errormsg {
        width:100%;
        display:inline-block;
    }
        .row.row-sm.mt-2 > div{
            align-content:flex-start;
        }
</style>
</asp:Content>



