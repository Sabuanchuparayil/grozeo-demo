<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Store Category" AutoEventWireup="true" CodeBehind="StoreCategory.aspx.cs" Inherits="RetalineProAgent.StoreCategory" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>

</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Store Categories</li>--%>
    <a href="/Navigations/Categories"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">Store Categories</h6>
        <p class="mb-0">Organized Product Grouping</p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                      <div class="card-tools">
                          <div class="d-flex align-items-center justify-content-end">
                <%--<div class="d-flex align-items-center justify-content-between">--%>
                    <%--<div class="mg-l-10" style="color:black; width:235px">
                    <asp:Literal ID="ltrTitle" runat="server" Text="Delivery boys at"></asp:Literal>
                        </div>--%>
        <%--<asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                     <span class="tx-dark mr-2">
                        <asp:Literal ID="ltrBranch" runat="server">Branch</asp:Literal>
                    </span>
                   <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="wd-50p-force bd p-2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server"><asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
                </asp:PlaceHolder>--%>

<%--<asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
                    <div class="input-group flex-nowrap">
                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search" CssClass="p-1"></asp:TextBox> 
                    <span class="input-group-btn">
                        <button class="btn-primary bd-transparent pl-3 pr-3 pt-1 pb-1" type="button"><i class="fa fa-search"></i></button>--%>
                    <%--<asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>--%>
                        <%--</span>
                    </div>--%>
                    <%--<div class="col-sm-3"><a href="/PrivateCatItems" class="btn btn-block btn-outline-info btn-sm">Add items</a></div>--%>
                <%--</div>--%>
                              
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="input-group-btn">
                    <a href="/Tenant/StoreCatSettings" type="button" class="btn btn-primary w-lg-100 mt-2 mt-lg-0">
    <i class="icon ion-plus-circled mr-2"></i>Create Store Category</a>
                    
<div class="float-right ml-3 tx-dark" runat="server" visible="false">
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
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvStoreCat" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvStoreCat_DataBound" DataSourceID="SDSStoreCat">
                                    <Columns>
                                        <asp:BoundField HeaderText="Business Category" DataField="business_category_name" SortExpression="business_category_name"/>
                                        <%--<asp:BoundField HeaderText="Retail Category" DataField="rbc_business_type" SortExpression="rbc_business_type"/>--%>
                                        <asp:TemplateField ItemStyle-Width="70%" HeaderText="Retailer Category"><ItemTemplate>
                                            <asp:ListBox ID="lstBusinessTypes" SelectionMode="Multiple" disabled businesstypes='<%# Eval("rbc_business_type") %>' OnDataBound="lstBusinessTypes_DataBound" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
                                                           </ItemTemplate></asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Status" DataField="status" SortExpression="status"/>--%>
                                        <asp:HyperLinkField runat="server" Text="Edit" NavigateUrl="~/Tenant/StoreCatSettings" DataNavigateUrlFields="business_category_id" DataNavigateUrlFormatString="~/Tenant/StoreCatSettings?id={0}" />
                                        <asp:TemplateField ItemStyle-HorizontalAlign="Center">
                                            <ItemTemplate>
                                                <asp:LinkButton runat="server" OnClick="DeleteItem_Click" itemid='<%# Eval("business_category_id") %>' ForeColor="#dc3545" OnClientClick="javascript:confirm('Do you want to delete this?');"><i class="fa fa-trash"></i></asp:LinkButton>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        
                                            
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3"><small>No store category created. You can create your own grouping of retail categories here. This will be shown as tabs in the banner on public site.</small></h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>
                   <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name,IF((status=1),'Active','Inactive') AS status FROM finascop_business_type"
                ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>

                                <asp:SqlDataSource runat="server" ID="SDSStoreCat" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSStoreCat_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT business_category_id,business_category_name,IF((STATUS=1),'Active','Inactive') AS STATUS,
IF((business_category_ingroup=1),'Yes','No')AS business_category_ingroup,rbc_business_type FROM retaline_business_category WHERE store_group_id=@storegroup" 
                                    OnSelecting="SDSStoreCat_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
            </div>
            </div>
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
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
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

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/StoreCategory";
            });
        });
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            $('.duallistbox').bootstrapDualListbox();

        });
    </script>

</asp:Content>
