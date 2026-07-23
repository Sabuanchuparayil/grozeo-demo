<%@ Page Language="C#" AutoEventWireup="true" Title="Package Types" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="PackageType.aspx.cs" Inherits="RetalineProAgent.PackageType" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
    <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">
    <script src="/Content/js/bootstrap-multiselect.min.js"></script>
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
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Package Type"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> --%>
            </h6>
        <p class="mb-0">Create & List your Package Types</p>
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
                <div class="col-12 col-sm-4 mb-2 form-group">
                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Package Name:<span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtPackage" runat="server" CssClass="form-control" placeholder="Enter package name" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtPackage" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Package name is required" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                <div class="col-12 col-sm-4 mb-2">
                <div class="form-group mb-0">
                  <label class="w-100 text-left tx-dark">Delivery Type:<span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selType" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="">Select delivery type</asp:ListItem>
                              <asp:ListItem Value="1">Express</asp:ListItem>
                              <asp:ListItem Value="2">Courier</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator ValidationGroup="AddPackageType" ControlToValidate="selType" ForeColor="Red" ErrorMessage="Select type" runat="server"></asp:RequiredFieldValidator>
                </div>
              </div>
                <div class="col-12 col-sm-4 mb-2 form-group d-flex">
                    <div class="form-group wd-100 mb-0">
                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Length:<span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtLength" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLength" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Length is required" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                <div class="form-group wd-100 mx-2 mb-0">
                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Breadth:<span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtbreadth" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbreadth" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Breadth is required" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                <div class="form-group wd-100 mb-0">
                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Height:<span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtHeight" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtHeight" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Height is required" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                </div>
                
                <%--<div class="col-12 col-md-3 form-group mg-b-10 mg-lg-b-0" runat="server">
                    <label runat="server" class="tx-dark mb-1 w-100">Select Store:</label>
                    <asp:DropDownList ID="selStore" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" AutoPostBack="true">
                              <asp:ListItem Value="0">Select Store</asp:ListItem>
                              <asp:ListItem Value="1">All Stores</asp:ListItem>
                              <asp:ListItem Value="2">Specific Store</asp:ListItem>
                          </asp:DropDownList>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="selStore" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store is required" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>--%>
                <div class="col-12 col-sm-6 col-lg-5 form-group mg-b-10 mg-sm-b-0" runat="server">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store:<span class="tx-danger">*</span></label>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                    <asp:ListBox ID="lstBranches" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID"
                          CssClass="form-control" multiple="multiple" ></asp:ListBox>
                    <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                        ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="lstBranches" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Store" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                <div class="col-12 col-sm-2 d-flex flex-wrap align-content-end mg-b-0">
                    <asp:LinkButton ID="lbtnAddPackageType" runat="server" CssClass="btn btn-primary w-md-100" Text="Add" OnClick="lbtnAddPackageType_Click" ValidationGroup="AddPackageType" />
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvPackageType" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvPackageType_DataBound" DataSourceID="SDSPackageTypes">
                                    <Columns>
                                        <asp:BoundField HeaderText="Package Name" DataField="rpckm_name" SortExpression="rpckm_name" />
                                        <asp:BoundField HeaderText="Delivery Type" DataField="rpckm_typeName" SortExpression="rpckm_typeName"/>
                                        <asp:BoundField HeaderText="Length" DataField="rpckm_length" SortExpression="rpckm_length"/>
                                        <asp:BoundField HeaderText="Breadth" DataField="rpckm_breadth" SortExpression="rpckm_breadth"/>
                                        <asp:BoundField HeaderText="Height" DataField="rpckm_height" SortExpression="rpckm_height"/>
                                        <asp:BoundField HeaderText="Store" DataField="branchNames" SortExpression="branchNames"/>
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

                                <asp:SqlDataSource runat="server" ID="SDSPackageTypes" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT m.rpckm_id,m.rpckm_name,m.rpckm_type,m.rpckm_length,m.rpckm_breadth,m.rpckm_height,m.rpckm_status,
                                    CASE WHEN m.rpckm_type = 1 THEN 'Quick' WHEN m.rpckm_type = 2 THEN 'Courier' END AS rpckm_typeName,
                                    IF(COUNT(b.br_ID) > 0, GROUP_CONCAT(DISTINCT b.br_Name SEPARATOR ', '), 'All Stores') AS branchNames FROM 
                                    retaline_package_master m LEFT JOIN finascop_branch b ON FIND_IN_SET(b.br_ID, m.branchId) WHERE m.store_group_id = @storegroup 
                                    GROUP BY m.rpckm_id, m.rpckm_name, m.rpckm_type, m.rpckm_length, m.rpckm_breadth, m.rpckm_height, m.rpckm_status
                                    ORDER BY m.rpckm_id DESC" OnSelecting="SDSPackageTypes_Selecting">    
                                    <SelectParameters>
                                    <asp:Parameter Name="storegroup" />
                                </SelectParameters>
                            </asp:SqlDataSource>
               </div>
            </div><!-- card-body -->
    </div><!-- card -->

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
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Cancel</button>
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

            <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <script>
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            $('#lstBranches').multiselect({
                includeSelectAllOption: true,
                nonSelectedText: 'Select',
                nSelectedText: ' - Branches selected',
                allSelectedText: 'All branches Selected ...'
            });
        });
    </script>
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
        .select2.select2-container {
            width:100%!important;
        }
        .form-control + .select2 + span[data-val="true"] {
            bottom: -9px;
        }
        .select2-container {
            height: 28px;
        }

        .form-group .multiselect-native-select + span[data-val="true"] {
            width: 100%;
            left: 9px;
            bottom: -13px;
        }

</style>
</asp:Content>



