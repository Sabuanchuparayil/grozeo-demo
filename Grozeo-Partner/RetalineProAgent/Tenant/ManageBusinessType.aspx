<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ManageBusinessType.aspx.cs" Inherits="RetalineProAgent.ManageBusinessType" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
    <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">
    <script src="/Content/js/bootstrap-multiselect.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage Retail Categories</li>--%>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Manage Retail Categories</h6>
        <p class="mb-0">Add Retail Category</p>
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
        <asp:PlaceHolder ID="plcListing" runat="server">
        <div class="card-header shadow_top">
            <div class="row row-sm">
          <%--<label class="section-title col-12 p-0 m-0">Retail Categories</label>
        <div class="col-md-10 mg-b-20 pl-0">
            <p class="mg-b-0">List of retail categories linked to the business account. Delete allowed only on those without products</p>
        </div>--%>
                <div class="col-12 col-lg-9">
                    <label class="col-12 p-0 m-0">Retail Categories</label>
                    <p class="mg-b-0">List of retail categories linked to the business account. Delete allowed only on those without products</p>
                </div>
                <div class="col-lg-3 mt-3 mt-lg-0 d-flex align-items-start justify-content-lg-end">
                    <asp:LinkButton runat="server" ID="btnAddMore" OnClick="btnAddMore_Click" CssClass="btn px-4 d-block d-md-inline-block btn-primary"> Add More<i class="icon ion-plus-circled ml-2"></i></asp:LinkButton>
                </div>
        </div><!-- row -->
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
              <asp:GridView ID="gvRetailCategories" runat="server" GridLines="None" DataKeyNames="business_type_id" DataSourceID="SDSRetailCategories" AutoGenerateColumns="false" 
                  CssClass="table table-bordered gridview_table" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" OnRowCommand="gvRetailCategories_RowCommand">
                  <Columns><asp:BoundField HeaderText="Retail Category" DataField="business_type_name" SortExpression="business_type_name" />
                      <asp:BoundField HeaderText="Departments" DataField="parentcategories" SortExpression="parentcategories" />
                      <asp:BoundField HeaderText="Business Categories" DataField="businessCategories" SortExpression="businessCategories" />
                      <asp:BoundField HeaderText="Products" DataField="Product" ItemStyle-CssClass="prodcount" ItemStyle-HorizontalAlign="Right" SortExpression="prodcount" />
                      
                      <asp:TemplateField HeaderText="Actions" ItemStyle-HorizontalAlign="Center"><ItemTemplate>
                          <asp:LinkButton ID="lbtnDelete" CssClass="btndelbtype" OnClick="lbtnDelete_Click" rcid='<%# Eval("business_type_id") %>'  style="color:#DC3545;" runat="server"><i class="icon ion-android-delete"></i></asp:LinkButton>
                                                              </ItemTemplate></asp:TemplateField>
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
              <asp:SqlDataSource ID="SDSRetailCategories" OnSelecting="SDSRetailCategories_Selecting" ProviderName="MySql.Data.MySqlClient" runat="server" 
                  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" SelectCommand="SELECT bt.*, 
(SELECT COUNT(*) FROM finascop_stock_itemmaster i 
INNER JOIN (SELECT stit_id FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id WHERE br_storeGroup=@storegroupid GROUP BY stit_id )br ON br.stit_id=i.stit_id
INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id WHERE parent_category_businessType=bt.business_type_id) AS Product,
(SELECT GROUP_CONCAT(business_category_name) FROM retaline_business_category WHERE FIND_IN_SET( bt.business_type_id, rbc_business_type) > 0 AND store_group_id=0) AS businessCategories, 
(SELECT GROUP_CONCAT(parent_category) FROM mypha_productparent_category WHERE parent_category_businessType=bt.business_type_id) AS parentcategories
FROM `finascop_business_type` bt INNER JOIN `finascop_branch_group_business_type` bgt ON bgt.business_type_id=bt.business_type_id WHERE store_group_id=@storegroupid">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>
          </div><!-- table-responsive -->
        </div><!-- card-body -->
      </asp:PlaceHolder>
        <asp:PlaceHolder ID="plcSettings" runat="server">

        <div class="card-body p-3 shadow_top" style="overflow: visible;">
          <div class="form-layout">
          <%--<label class="slim-card-title">Add Retail Category</label>--%>
          <%--<p class="mg-b-20 mg-sm-b-40">Please input the store short, display name and select business types.</p>--%>
            <div class="row row-sm">

                <asp:Panel ID="pnlBCategories" runat="server" CssClass="col-lg-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label tx-dark">Business Category: <span class="tx-danger">*</span></label>
                        <asp:DropDownList ID="selBusinessTypes" AutoPostBack="true" data-placeholder="Choose business type" runat="server" AppendDataBoundItems="true"
                            DataSourceID="SDSBusinessCategories" DataTextField="business_category_name" DataValueField="business_category_id"
                            CssClass="form-control" Style="width: 100%;">
                            <asp:ListItem Text="Select Business Category" Value=""></asp:ListItem>
                        </asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selBusinessTypes" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Business category is required" ValidationGroup="RetailCategory" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </asp:Panel>
                <!-- col-4 -->
              <asp:Panel ID="pnlRCategories" runat="server" CssClass="col-lg-8 mt-0">              
                  <label class="form-control-label w-100 tx-dark">Retail Categories:</label>
                      <asp:ListBox ID="lstBusinessTypes" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control" ></asp:ListBox>
              </asp:Panel><!-- col-4 -->


                <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSRetailCategories_Selecting"
    SelectCommand="SELECT business_type_id,business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt 
    WHERE STATUS=1 AND EXISTS(SELECT * FROM retaline_business_category bc WHERE business_category_id= @catid AND Store_group_Id=0 AND FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0) AND NOT EXISTS(SELECT * FROM finascop_branch_group_business_type WHERE business_type_id = bt.business_type_id AND store_group_id=@storegroupid)"
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:ControlParameter ControlID="selBusinessTypes" ConvertEmptyStringToNull="false" Name="catid" />
        <asp:Parameter Name="storegroupid" DefaultValue="0" /></SelectParameters></asp:SqlDataSource>

<asp:SqlDataSource ID="SDSBusinessCategories" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSRetailCategories_Selecting"
    SelectCommand="SELECT * FROM retaline_business_category bc WHERE Store_group_Id=0 AND `status`=1 AND EXISTS(SELECT * FROM finascop_business_type bt WHERE FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0
    AND bt.business_type_id NOT IN (SELECT business_type_id FROM finascop_branch_group_business_type WHERE  store_group_id=@storegroupid))"
    ProviderName="MySql.Data.MySqlClient"><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="0" /></SelectParameters></asp:SqlDataSource>
                <div class="col-12 mt-2">
                  <div class="d-inline-block">
                      <asp:Button runat="server" ID="btnAddBTypes" CssClass="btn btn-primary mr-1" Text="Submit" OnClick="btnAddBTypes_Click" ValidationGroup="RetailCategory" />
                      <a href="/Tenant/managebusinesstype" class="btn btn-secondary">Cancel</a>
                      <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                  </div>
              </div>
            </div>
              

              <div class="form-layout-footer">
              

            </div><!-- form-layout-footer -->

           </div>
        </div><!-- card-body -->
    </asp:PlaceHolder>
    </div><!-- card -->
    
        <script>
            $(document).ready(function () {
                $('.select2').select2();
                $('#<%= gvRetailCategories.ClientID%>').find('td.prodcount').each(function () {
                    if ($(this).text() > 0) {
                        $(this).closest('tr').find('a.btndelbtype').on('click', function (e) { alert('Cannot delete business type having products added. You can go to the product manager and clear the selected products in the particular business category first in order to delete.'); return false; });
                        $(this).closest('tr').find('a.btndelbtype').css('pointer-events', 'none !important').css('color','lightgray');
                    }
                    else {
                        $(this).closest('tr').find('a.btndelbtype').on('click', function (e) { return confirm('Are you sure you want to delete the business type? You will not be able to add products in this business category if deleted'); });
                    }
                });
        $('#lstBusinessTypes').multiselect();
            });
        </script>

</asp:Content>
