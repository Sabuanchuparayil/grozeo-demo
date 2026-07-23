<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master"  CodeBehind="SponsoredProducts.aspx.cs" Inherits="RetalineProAgent.SponsoredProducts" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sponsored</li>--%>
    <a href="/Navigations/Others"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Sponsored"></asp:Literal>                 
            </h6> 
        <p class="mb-0">Promote with Sponsorships.</p>
    </div>
     
    <style>
    .tbl_prod_img{
        width:auto; max-width: 30px; max-height: 28px;
    }
</style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm mt-2">
                <div class="col-12 col-sm-5 col-md-4 d-flex flex-lg-nowrap form-group mb-2 mb-lg-0">
                    <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
                        <asp:ListItem Text="All Brands" Value="0"></asp:ListItem>
                    </asp:DropDownList>
                    <button class="btn btn-primary ml-2 bg-drk-green " type="button" onclick="$('#<%= hidSelectItemsFilter.ClientID %>').val(($('#<%= hidSelectItemsFilter.ClientID %>').val() == '0'? '1': '0')); " data-toggle="collapse" data-target="#collapseSP" aria-expanded="false" aria-controls="collapseSP">
                        Filter <i class="filter_arrow"></i>
                    </button>
                </div>
                <div class="col-12 col-sm-7 col-md-8 d-flex justify-content-sm-end">
                    <%--<div class="d-flex m-0">
                        <label class="rdiobox mr-4">
                            <asp:RadioButton ID="rdEnabled" runat="server" Checked="true" GroupName="rbgSponsored" OnCheckedChanged="rdEnabled_CheckedChanged" AutoPostBack="true" />
                            <span>Enabled</span>
                        </label>
                        <label class="rdiobox">
                            <asp:RadioButton ID="rdDisabled" runat="server" GroupName="rbgSponsored" OnCheckedChanged="rdDisabled_CheckedChanged" AutoPostBack="true"  />
                            <span>Disabled</span>
                        </label>
                      </div>--%>
                    <p class="m-0 tx-12 tx-dark">
                        Sponsored Products: <strong class="tx-dark"><asp:Literal ID="ltrSponsoredPrd" Text="0" runat="server"></asp:Literal></strong>
                        <asp:LinkButton ID="PageShow" runat="server" CssClass="btn btn-outline-primary ml-2 p-1 lh-1" OnClick="PageShow_Click" Text="Change"></asp:LinkButton>
                    </p>
                </div>
            </div>
            <div class="wizard_fliter">
                                        <asp:HiddenField ID="hidSelectItemsFilter" runat="server" Value="0" />
                                          <div class="collapse mt-3 <%= (hidSelectItemsFilter.Value=="1" ? "show":"") %>"" id="collapseSP">
                                            <div class="row row-sm align-items-end">
                                              <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                                                <div class="form-group mb-0 ">
                                                  <asp:DropDownList ID="selDepartment" OnSelectedIndexChanged="selDepartment_SelectedIndexChanged" AutoPostBack="true" runat="server" DataSourceID="SDSDepartments" DataTextField="parent_category" DataValueField="parent_category_id" AppendDataBoundItems="true" CssClass="form-control select2"><asp:ListItem Text="All Departments" Value="0"></asp:ListItem></asp:DropDownList>
                                                </div>
                                              </div><!--col-lg-4-->
                                              <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                                                <div class="form-group mb-0">
                                          <asp:DropDownList ID="selCategory" runat="server" AutoPostBack="true" DataSourceID="SDSCategory" OnDataBound="selCategory_DataBound" DataTextField="category_name" DataValueField="category_id" CssClass="form-control select2"><asp:ListItem Text="All Categories" Value="0"></asp:ListItem></asp:DropDownList>
                                                </div>
                                              </div><!--col-lg-4-->
                                              <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                                                <div class="input-group input_search_box mg-0">
                                                    <asp:TextBox ID="txtSponsoredProductName" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                                                  <span class="input-group-btn">
                                                      <asp:LinkButton runat="server" CssClass="btn bd bd-l-0 tx-gray-600" ><i class="fa fa-search"></i></asp:LinkButton>
                                                  </span>
                                                </div><!-- input-group -->
                                              </div><!--col-lg-4-->
                                            </div><!--row-->

                                          </div><!--collapse-->

                    </div><!--wizard_fliter--> 
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" 
                    ItemPlaceholderID="plsSpProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" OnItemDataBound="lstProducts_ItemDataBound" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Sub Category
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <asp:PlaceHolder ID="plsSpProducts" runat="server">
                        </asp:PlaceHolder>

                        <tr>
                            <td colspan="4">
                                <%--<div class="">
                                    <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                                        <Fields>
                                            <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm" PreviousPageText="<" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                                            <asp:NumericPagerField ButtonType="Link" NumericButtonCssClass="btn btn-default btn-sm" />
                                            <asp:NextPreviousPagerField ButtonType="Link" NextPageText=">" ShowNextPageButton="true" ButtonCssClass="btn btn-default btn-sm" ShowLastPageButton="false" ShowPreviousPageButton="false" />
                                        </Fields>
                                    </asp:DataPager>
                                </div>--%>
                                <div class="pagenation_listview">
                                    <asp:DataPager ID="DataPager1" runat="server" PageSize="10" PagedControlID="lstProducts">
                            <Fields>
                                                              <asp:NumericPagerField ButtonType="Link" CurrentPageLabelCssClass="btn btn-primary disabled" RenderNonBreakingSpacesBetweenControls="false"
                                                                  NumericButtonCssClass="btn btn-default" ButtonCount="5" NextPageText="..." NextPreviousButtonCssClass="btn btn-default" />
                                                          </Fields>
                        </asp:DataPager>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                                </table>
				</LayoutTemplate>
                    <ItemTemplate>
                        <tr>
                            <td width="50px" align="center"><asp:LinkButton runat="server" Enabled="true"  ID="btnsptdprdt" OnClick="btnsptdprdt_Click"  style="cursor: pointer;"><i class="ion-checkmark-circled"></i></asp:LinkButton></td>
                            <td>
                                <div class="d-flex align-items-center">
                                <div class="prodct_img">
                                    <asp:Image CssClass="tbl_prod_img hoverimgpopover" runat="server" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    <div class="imgpopover">
                                        <asp:Image CssClass="tbl_prod_img hoverimgpopover" runat="server" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    </div>
                                </div>
                                <div class="prd_name">
                                    <strong><asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal></strong>
                                </div>
                                </div>
                            </td>
                            <td><%# Eval("stit_brand_name") %></td>
                            <td><%# Eval("stit_category_name") %></td>
                        </tr>
                    </ItemTemplate>
                    <EmptyItemTemplate>
                        <div class="text-center">
                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                            <h6 class="mb-3"><small>No data available <a href="/Tenant/InventoryMapping">Select items for Sale</a> to select from master data or you can upload CSV. </small></h6>
                        </div>
                    </EmptyItemTemplate>
            </asp:ListView>
            </div>
        </div><!-- card-body -->
    </div><!-- card -->

        
     
    <!-- /.login-box -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modelalert" class="modal fade modelalert">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
            </div>
          <div class="modal-body tx-center pd-y-20 pd-x-20">            
            <p class="mg-b-20 mg-x-20"><asp:Literal runat="server" ID="ltrlupgrade"></asp:Literal></p>
             <asp:Button runat="server" ID="btnyes" Visible="false" OnClick="btnyes_Click" CssClass="btn d-inline-block btn-primary" Text="Yes" />
            <button type="button" class="btn d-inline-block btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
  <!--login_sec_wrp-->

                                           <asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT bnd.brand_id, bnd.brand_name FROM mypha_productbrands bnd 
                         INNER JOIN finascop_stock_itemmaster i ON  i.pdt_brand = bnd.brand_id  INNER JOIN (
                            SELECT bi.stit_id AS br_stitId, b.br_ID, b.br_storeGroup, issponsered, COUNT(*) FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b 
                            WHERE issponsered = 1 OR b.br_storeGroup = @storeId GROUP BY bi.stit_id ORDER BY issponsered 
                        )br ON br.br_stitId=i.stit_id INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category 
                        INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id  AND (@category <= 0 OR c.category_id = @category)
                         INNER JOIN mypha_productparent_category pc  ON pc.parent_category_id=c.parent_category AND (@department <= 0 OR pc.parent_category_id = @department)
                         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = pc.parent_category_businessType AND bbt.store_group_id= @storeId
                            WHERE stit_status = 1  AND issponsered = 1 GROUP BY bnd.brand_id ORDER BY bnd.brand_name" ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:Parameter Name="storeId" DefaultValue="0" />
                            <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                            <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>

                        <asp:SqlDataSource ID="SDSProducts" runat="server"  OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                         SelectCommand="SELECT i.stit_Id, i.stit_itemId, i.stit_itemERPId, i.stit_SKU, i.stit_HSNCode, br.mrp, i.stit_brand_name, br.fpod_customerRatePikup AS br_selling_price, 
                            i.stit_category_name, i.med_manufacturename, (SELECT image_url FROM finascop_stock_item_images WHERE product_id= i.stit_ID LIMIT 1) AS imageurl 
                            FROM finascop_stock_itemmaster i INNER JOIN (
                             SELECT bi.stit_id AS br_stitId, b.br_ID, b.br_storeGroup, issponsered, fpod_customerRatePikup, mrp FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b 
                             WHERE issponsered = 1 OR b.br_storeGroup = @storeId GROUP BY bi.stit_id ORDER BY issponsered 
                        )br ON br.br_stitId=i.stit_id INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category 
                        INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id  AND (@category <= 0 OR c.category_id = @category)
                         INNER JOIN mypha_productparent_category pc  ON pc.parent_category_id=c.parent_category AND (@department <= 0 OR pc.parent_category_id = @department)
                         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = pc.parent_category_businessType AND bbt.store_group_id= @storeId
                            WHERE stit_status = 1  AND issponsered = 1 AND  (@brand <= 0 OR pdt_brand = @brand) AND (trim(ifnull(@searchKey, '')) like '' or stit_SKU like CONCAT('%', @searchKey, '%'))
                                 GROUP BY i.stit_id ORDER BY i.stit_SKU " ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:ControlParameter Name="searchKey" ControlID="txtSponsoredProductName" ConvertEmptyStringToNull="false" />
                            <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                            <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                            <asp:ControlParameter ControlID="selBrand" Name="brand" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
                            <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />
                        </SelectParameters>
                        </asp:SqlDataSource>

                        <asp:SqlDataSource ID="SDSDepartments" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT pc.* FROM mypha_productparent_category pc 
                         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = pc.parent_category_businessType AND bbt.store_group_id= @storeId
                         INNER JOIN mypha_productcategory c ON pc.parent_category_id=c.parent_category  
                         INNER JOIN mypha_productsubcategory sc ON sc.main_category=c.category_id 
                         INNER JOIN finascop_stock_itemmaster i ON sc.sub_category_id = i.product_category
                         INNER JOIN (SELECT bi.stit_id AS br_stitId, b.br_ID, b.br_storeGroup, issponsered, COUNT(*) FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b 
                            WHERE issponsered = 1 OR b.br_storeGroup = @storeId GROUP BY bi.stit_id ORDER BY issponsered 
                        )br ON br.br_stitId=i.stit_id WHERE issponsered = 1 GROUP BY parent_category_id" ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
                        </SelectParameters>
                        </asp:SqlDataSource>
    
                        <asp:SqlDataSource ID="SDSCategory" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT c.* FROM mypha_productcategory c
                         INNER JOIN mypha_productparent_category pc  ON pc.parent_category_id=c.parent_category and (@department = 0 or pc.parent_category_id = @department)
                         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = pc.parent_category_businessType AND bbt.store_group_id= @storeId
                         INNER JOIN mypha_productsubcategory sc ON sc.main_category=c.category_id 
                         INNER JOIN finascop_stock_itemmaster i ON sc.sub_category_id = i.product_category
                         INNER JOIN (SELECT bi.stit_id AS br_stitId, b.br_ID, b.br_storeGroup, issponsered, COUNT(*) FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b 
                            WHERE issponsered = 1 OR b.br_storeGroup = @storeId GROUP BY bi.stit_id ORDER BY issponsered 
                        )br ON br.br_stitId=i.stit_id WHERE issponsered = 1 GROUP BY category_id " ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
                                <asp:ControlParameter Name="department" ControlID="selDepartment" />
                        </SelectParameters>
                        </asp:SqlDataSource>
    
    </asp:Content>




