<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ProductGroup.aspx.cs" Inherits="RetalineProAgent.Tenant.ProductGroup" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">    
        <h6 class="slim-pagetitle m-0">Product Groups</h6>
        <p class="mb-0">Group products</p>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Others"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

<div class="card">
    <div class="card-header shadow_top">
            <div class="row row-sm align-items-sm-end">
                <div class="col-sm-4 col-lg-3 input-group-sm mg-b-10 mg-sm-b-0">
                    <div class="form-group mb-0">
                        <label class="form-control-label tx-dark mb-1">Brand</label>
                        <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" AppendDataBoundItems="true" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
                            <asp:ListItem Text="All Brands" Value="-1"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-5 input-group-sm mg-b-10 mg-sm-b-0">
                    <div class="form-group mb-0">
                        <label class="form-control-label tx-dark mb-1">Search</label>
                        <asp:TextBox ID="txtSearchProduct" runat="server" autocomplete="off" CssClass="form-control" placeholder="Search in Products"></asp:TextBox>
                    </div>
                </div>
                
                <div class="col-4 col-sm-2 input-group-sm mg-b-10 mg-sm-b-0">
                    <asp:Button runat="server" Text="Search" CssClass="btn btn-primary w-100" />
                </div>
                <div class="col-8 col-lg-2 input-group-sm mb-0 mt-xs-0 mt-sm-2 mt-lg-0">
                    <a href="/tenant/productgroup" type="button" class="btn btn-outline-primary ml-0 ml-lg-2" >Reset</a>
                    <a href="javascript:void(0)" onclick="addEditGroup()" type="button" class="btn btn-outline-primary ml-2" >Add</a>
                </div>

            </div><!--row-->
        </div><!--card heder-->
    <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvProducts" GridLines="None" runat="server" CssClass="table table-bordered mg-b-0 gridview_table"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataKeyNames="Id" PageSize="10" DataSourceID="SDSGroups">
                                    <Columns>
                                        <asp:BoundField HeaderText="Brand" DataField="brand_name" SortExpression="brand_name" />
                                        <asp:TemplateField HeaderText="Group Name" SortExpression="Name">
                                            <ItemTemplate><a href="javascript:void(0)" class="nav-link" gpid="<%# Eval("Id") %>" brandId="<%# Eval("brandId") %>" onclick="addEditGroup(this, '<%# Eval("Name") %>');"><%# Eval("Name") %> <i class="icon ion-compose"></i></a></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Cnt." DataField="cnt" SortExpression="cnt"/>
                                        <asp:HyperLinkField DataNavigateUrlFormatString="/Tenant/productgroup-manage?id={0}" DataNavigateUrlFields="id" Text="Edit" />
                                    </Columns>
                                    <SortedAscendingHeaderStyle CssClass="sorting sorting_asc" />
                                    <SortedDescendingHeaderStyle CssClass="sorting sorting_desc" />
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSGroups" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSGroups_Selecting"
                                 SelectCommand="SELECT g.*, pb.brand_name, (SELECT COUNT(*) FROM(SELECT DISTINCT stit_id FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id WHERE variantGroupId = g.id AND br_storegroup = @storeId)tmp) as cnt 
                                    FROM product_group g LEFT JOIN mypha_productbrands pb ON g.brandId=pb.brand_id WHERE  StoreGroupId = @storeId and (@brand <=0 or @brand= g.brandId) and (@search = '' or g.`Name` like CONCAT('%', @search, '%'))">
        <SelectParameters>
            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
            <asp:ControlParameter ControlID="selBrand" Name="brand" DefaultValue="-1" DbType="Int32" PropertyName="Text" />
            <asp:ControlParameter ControlID="txtSearchProduct" Name="search" ConvertEmptyStringToNull="false" PropertyName="Text" />

        </SelectParameters>
    </asp:SqlDataSource>

                                </div><!-- table-responsive -->
        </div><!--card-body-->
</div>

    <asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT pb.brand_id,pb.brand_name FROM mypha_productbrands pb INNER JOIN product_group g ON g.brandId=pb.brand_id WHERE StoreGroupId=@storeId group by pb.brand_id order by pb.brand_name"
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
    </SelectParameters>
</asp:SqlDataSource>




    <!-- BASIC MODAL -->
    <div id="addgroup" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">

            <div class="section-wrapper p-0 border-0">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="row row-sm">
                <div class="col-12"><h6 class="mb-2 tx-dark" id="hNewGroupName">New Group Name</h6></div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label">Enter the SKU name for the product group</label>
                      <asp:TextBox ID="txtGroupName" runat="server" CssClass="form-control" onfocus="this.select()" ValidationGroup="CreateGroup" placeholder="Group Name"></asp:TextBox>
                  <asp:RequiredFieldValidator runat="server" ForeColor="Red" SetFocusOnError="true" ErrorMessage="Please input group name. " ControlToValidate="txtGroupName" Display="Dynamic" ValidationGroup="CreateGroup"></asp:RequiredFieldValidator>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label">Brand</label>
<asp:DropDownList ID="selGroupBrand" runat="server" DataSourceID="SDSStoreBrands" AppendDataBoundItems="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
    <asp:ListItem Text="Select Brand" Value=""></asp:ListItem>
</asp:DropDownList>
                  <asp:RequiredFieldValidator runat="server" ForeColor="Red" SetFocusOnError="true" ErrorMessage="Please select brand" ControlToValidate="selGroupBrand" Display="Dynamic" ValidationGroup="CreateGroup"></asp:RequiredFieldValidator>

                  </div>
                </div>
      <asp:SqlDataSource ID="SDSStoreBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT brand_id,brand_name FROM mypha_productbrands WHERE brand_id IN(SELECT DISTINCT pdt_brand FROM finascop_stock_itemmaster i 
          INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id=i.stit_id INNER JOIN finascop_branch b ON bi.branch_id=b.br_ID WHERE b.br_storegroup=@storeId)"
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
    </SelectParameters>
</asp:SqlDataSource>


              </div> <!--row-->

            </div><!--section-wrapper-->       

            
          </div><!--modal-body-->
          <div class="modal-footer">
              <span class="error_msg_wrap" id="addbranderror"><asp:Literal ID="ltrAddGroupResult" runat="server"></asp:Literal>
              </span><asp:HiddenField ID="hidGpId" runat="server" />
              <asp:LinkButton runat="server" Text="Save" OnClick="ProductGroupAdd_Click" CssClass="btn btn-primary btn-drk-green" ValidationGroup="CreateGroup"></asp:LinkButton>
            <button type="button" class="btn btn-secondary btn-drk-green" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <script type="text/javascript">
        function addEditGroup(obj, gpName) {
            $('#<%= txtGroupName.ClientID%>').val('');
            $('#<%= hidGpId.ClientID%>').val('');
            $('#hNewGroupName').text('New Group Name');
            $('#<%= selGroupBrand.ClientID %> option:eq(0)').prop('selected', true)
            if (obj) {
                var groupId = $(obj).attr('gpid');
                var brandId = $(obj).attr('brandId');
                if (groupId > 0) {
                    $('#<%= txtGroupName.ClientID%>').val(gpName);
                    $('#<%= hidGpId.ClientID%>').val(groupId);
                    $('#hNewGroupName').text('Edit Group Name');
                    $('#<%= selGroupBrand.ClientID %> option[value="' + brandId + '"]').prop('selected', true)
                }
            }
            $('#addgroup').modal('show');
        }
    </script>

</asp:Content>


