<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ProductGroup-Manage.aspx.cs" Inherits="RetalineProAgent.Tenant.ProductGroup_Manage" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">    
        <h6 class="slim-pagetitle m-0"><asp:Literal ID="ltrGroupName" runat="server" Text="Manage Product Group"></asp:Literal></h6>
        <p class="mb-0">Manage product group - add / remove items</p>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server"><a href="/tenant/productgroup"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

<div class="card">
    <div class="card-header shadow_top">
            <div class="row row-sm align-items-lg-end">
                <div class="col-lg-3 input-group-sm mg-b-10 mg-lg-b-0">
                    <h5>Add more products</h5>
                </div>

                <div class="col-lg-6 input-group-sm mg-b-10 mg-lg-b-0">
                    <div class="form-group mb-0">
                        <label class="form-control-label tx-dark mb-1">Product - SKU</label>
                        <asp:DropDownList ID="selProduct" runat="server" DataSourceID="SDSSKU" AppendDataBoundItems="true" DataTextField="stit_SKU" DataValueField="stit_id" CssClass="selectpicker form-control select2" data-live-search="true" data-container="switch_storeGP">
                            <asp:ListItem Text="Select Product" Value="-1"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                </div>
                                
                <div class="col-lg-2 input-group-sm mg-b-10 mg-lg-b-0">
                    <asp:Button runat="server" Text="Add to Group" ID="btnAddToGroup" OnClick="btnAddToGroup_Click" CssClass="btn btn-primary mb-3 mb-lg-0 w-auto w-lg-100" />
                </div>

            </div><!--row-->
        </div><!--card heder-->
    <div class="card-body">
            <div class="table-responsive">
                <asp:GridView AutoGenerateColumns="false" ID="gvProducts" GridLines="None" runat="server" CssClass="table table-bordered mg-b-0 gridview_table"
                    AllowPaging="true" AllowSorting="true" AllowCustomPaging="true" ShowFooter="false" DataKeyNames="stit_id" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSGroupProduct">
                    <Columns>
                        <asp:BoundField HeaderText="Brand" DataField="pg_brand_name" SortExpression="pg_brand_name" />
                        <asp:BoundField HeaderText="SKU Header Name" DataField="stit_itemName" SortExpression="stit_itemName" />
                        <asp:BoundField HeaderText="Variant" DataField="stit_product_variant" SortExpression="stit_product_variant"/>
                        <asp:BoundField HeaderText="Qty / Size" DataField="stit_quantity" SortExpression="stit_quantity"/>
                        <asp:ButtonField CommandName="Delete" ButtonType="Link" HeaderText="Action" Text="Delete" />
                    </Columns>
                    <SortedAscendingHeaderStyle CssClass="sorting sorting_asc" />
                    <SortedDescendingHeaderStyle CssClass="sorting sorting_desc" />
                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                    <EmptyDataTemplate>No product available in this group</EmptyDataTemplate>
                </asp:GridView>

<asp:SqlDataSource runat="server" ID="SDSGroupProduct" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
    OnSelecting="SDSGroupProduct_Selecting" OnDeleting="SDSGroupProduct_Deleting"
 SelectCommand="SELECT i.*, bi.pg_brand_name FROM finascop_stock_itemmaster i INNER JOIN (
  SELECT stit_id, variantGroupId, pb.brand_name AS pg_brand_name FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storegroup=@storeId 
  INNER JOIN product_group pg ON bi.variantGroupId=pg.id LEFT JOIN mypha_productbrands pb ON pg.brandId=pb.brand_id  WHERE pg.id=@groupId GROUP BY bi.stit_id )bi ON i.stit_id=bi.stit_id" 
 DeleteCommand="UPDATE finascop_stock_branch_inventory bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storegroup=@storeId SET bi.variantGroupId=0 WHERE bi.stit_id=@stit_Id">
    <SelectParameters>
        <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        <asp:QueryStringParameter Name="groupId" QueryStringField="id" Type="Int32" DefaultValue="-1" />
    </SelectParameters>
    <DeleteParameters>
        <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        <asp:Parameter Name="stit_Id" Type="Int32" DefaultValue="-1" />
    </DeleteParameters>
</asp:SqlDataSource>

</div><!-- table-responsive -->
        </div><!--card-body-->
</div>

    <asp:SqlDataSource ID="SDSSKU" runat="server" OnSelecting="SDSSKU_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT i.stit_id, i.stit_SKU FROM finascop_stock_itemmaster i INNER JOIN product_group g ON g.brandId=i.pdt_brand AND g.id=@groupid INNER JOIN( 
  SELECT stit_id FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storegroup=@storeId GROUP BY bi.stit_id 
  )bi ON bi.stit_Id=i.stit_id GROUP BY i.stit_id ORDER BY i.stit_SKU" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
        <asp:QueryStringParameter Name="groupId" QueryStringField="id" Type="Int32" DefaultValue="-1" />
    </SelectParameters>
</asp:SqlDataSource>

</asp:Content>
