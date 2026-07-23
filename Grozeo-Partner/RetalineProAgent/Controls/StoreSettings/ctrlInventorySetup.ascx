<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlInventorySetup.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlInventorySetup" %>
<style>
    .tbl_prod_img{
        width:auto; max-width: 30px; max-height: 28px;
    }
</style>
        <div class="card-header">

            <div class="row row-sm align-items-lg-end">


                <div class="col-lg-2 input-group-sm mg-b-10 mg-lg-b-0">
                    <div class="form-group mb-0">
                        <label class="form-control-label mb-1">Department</label>
                        <asp:DropDownList ID="selDepartment" OnSelectedIndexChanged="selDepartment_SelectedIndexChanged" AutoPostBack="true" runat="server" DataSourceID="SDSDepartments" DataTextField="parent_category" DataValueField="parent_category_id" AppendDataBoundItems="true" CssClass="form-control select2">
                            <asp:ListItem Text="All Departments" Value="0"></asp:ListItem>
                        </asp:DropDownList>

                    </div>
                </div>
                <div class="col-lg-2 input-group-sm mg-b-10 mg-lg-b-0">
                    <div class="form-group mb-0">
                        <label class="form-control-label mb-1">Category</label>
                        <asp:DropDownList ID="selCategory" runat="server" AutoPostBack="true" DataSourceID="SDSCategory" OnDataBound="selCategory_DataBound" DataTextField="category_name" DataValueField="category_id" OnSelectedIndexChanged="Reload_Products" CssClass="form-control select2">
                            <asp:ListItem Text="All Categories" Value="0"></asp:ListItem>
                        </asp:DropDownList>

                    </div>
                </div>
                <div class="col-lg-2 input-group-sm mg-b-10 mg-lg-b-0">
                    <div class="form-group mb-0 ">
                        <label class="form-control-label mb-1">Brand</label>
                        <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" OnSelectedIndexChanged="Reload_Products" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
                            <asp:ListItem Text="All Brands" Value="0"></asp:ListItem>
                        </asp:DropDownList>

                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="input-group mg-t-20">
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <div class="input_search_box">
                          <asp:TextBox ID="txtSearchProduct" runat="server" autocomplete="off" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                          <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                        </div>
                    </div>
                    <!-- input-group -->
                </div>
                <div class="col-lg-2 d-flex justify-content-lg-end align-items-lg-end">
                    <a href="/Tenant/PrivateInventory" type="button" class="btn btn-primary mt-2 mt-lg-0">Create New<i class="icon ion-plus-circled ml-2"></i></a>
                </div>

            </div><!--row-->

            <div class="row row-sm mt-3 wd-100p-force">
                <div class="col-12 d-flex flex-wrap">
                    <label class="mr-4 tx-dark wd-100p wd-sm-auto-force">Showing</label>
                    <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbAllProducts" OnCheckedChanged="rbProducts_CheckedChanged" Checked="true" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <%--<input name="products_filter" type="radio" checked="">--%>
                        <span>All Products</span>
                    </label>

                    <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <%--<input name="products_filter" type="radio">--%>
                        <span>Products added</span>
                    </label>

                    <label class="rdiobox">
                        <asp:RadioButton ID="rbNotAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <span>Products not added</span>
                    </label>
                </div>
            </div><!--row-->

            <div class="row row-sm align-items-center">
                <%--<div class="col-lg-8">
                    <h5 class="tx-dark m-0">Select Products from Brand Gallery</h5>
                </div>--%>
                <div class="col-lg-4 text-left text-lg-right mg-lg-b-0">
                    <%--<a href="/privateproduct" class="btn btn-primary m-0 wd-sm-auto-force px-4 btn-sm">Create New Product</a>--%>
                    
                    <%--<button class="btn btn-primary m-0 wd-sm-auto-force px-4 btn-sm" data-toggle="modal" data-target="#create_new_product">Create New Product</button>--%>
                    <%--<asp:Literal runat="server" ID="ltrPagingCurStart" Text=""></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPagingCurTotal" Text=""></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text=""></asp:Literal>
                  <div class="btn-group ml-2">
                      <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" PreviousPageText="<i class='fa fa-angle-left'></i>" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" NextPageText="<i class='fa fa-angle-right'></i>" ShowNextPageButton="true" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>--%>
                    <%--<a id="cpMainContent_lbtnPagerLeft" class="btn btn-default btn-sm page-link" href="javascript:__doPostBack('ctl00$cpMainContent$lbtnPagerLeft','')">
                      <i class="fa fa-angle-left"></i>
                    </a>
                    <a id="cpMainContent_lbtnPagerRight" class="btn btn-default btn-sm page-link" href="javascript:__doPostBack('ctl00$cpMainContent$lbtnPagerRight','')">
                      <i class="fa fa-angle-right"></i>
                    </a>--%>
                </div>
            </div>
        </div><!--card heder-->

    
        <div class="card-body">

            <div class="table-responsive mailbox-messages">
                <div id="overlay" onclick="off()">
                    <div class="w-100 d-flex justify-content-center align-items-center">
                        <div class="spinner"></div>
                    </div>
                </div>
                <asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" OnDataBound="lstProducts_DataBound"
                    OnItemDataBound="lstProducts_ItemDataBound" ItemPlaceholderID="plsProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true">
                    <LayoutTemplate>
                        <table class="table table-bordered mg-b-0">
                            <thead>
                                <tr>
                                    <th>
                                        <%--<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-square tx-white"></i>
                        </button>
                        <div class="dropdown-menu">
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                            <asp:LinkButton ID="lbtnSelectAll" runat="server" OnClick="lbtnAll_Click" CssClass="dropdown-item" Text="Select all" OnClientClick="if(!confirm('Select all items listed?')) return false; else on();"></asp:LinkButton></asp:PlaceHolder>
                            <asp:LinkButton ID="lbtnRemoveAll" runat="server" OnClick="lbtnAll_Click" CssClass="dropdown-item" Text="Remove all" OnClientClick="return confirm('Remove all items listed?')"></asp:LinkButton>
                            <div class="dropdown-divider"></div>
                            <asp:LinkButton runat="server" CssClass="dropdown-item" Text="Refresh"></asp:LinkButton>
                        </div>--%>

                                        <%--<asp:CheckBox ID="chkProductHItem" AutoPostBack="true" OnCheckedChanged="chkProductHItem_CheckedChanged1" runat="server" />--%></th>
                                    <th>Name</th>
                                    <th>MRP/RRP</th>
                                    <th>Brand</th>
                                    <th>Sub Category
                                        <div class="float-right">

                                            <div class="btn-group">
                                            </div>

                                            <!-- /.btn-group -->
                                        </div>

                                    </th>
                                    <%--<th>Action</th>--%>

                                    <%--<th>Stock</th>
											    <th>Margin %</th>--%>
                                </tr>
                            </thead>
                            <tbody>

                                <asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>

                                

                            </tbody>
                        </table>
                    </LayoutTemplate>
                    <ItemTemplate>

                        <tr class="<%# ( !IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) ? "" : (Convert.ToInt32(Eval("instock")) > 0 ? "already_added" : "checked_now" )) %>">
                            <td>
                                <asp:CheckBox ID="chkProductItem" CssClass="productcheck" onclick="updateSelection(this);" itemmrp='<%# Eval("stit_MRP") %>' itemid='<%# Eval("stit_Id") %>' erpid='<%# Eval("stit_HSNCode") %>' mrpid='<%# Eval("mrpid") %>' Checked='<%# IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) %>' runat="server" /></td>
                            <td>
                                <asp:Image CssClass="tbl_prod_img" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                <asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal>
                                <%--<br /><small>Category: <b><%# Eval("stit_category_name") %></b>, Brand: <b><%# Eval("stit_brand_name") %></b>, By: <b><%# Eval("med_manufacturename") %></b></small>--%>
                            </td>
                            <%--<td style="text-align: right;"><asp:Literal ID="ltrProductItemMrp" runat="server" Text='<%# (String.IsNullOrEmpty(Eval("itemMSRP").ToString()) || Eval("itemMSRP").ToString() == "0" ? "" : String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("itemMSRP"))) %>' ></asp:Literal></td>--%>
                            <%--<td style="text-align: right;"><%# (String.IsNullOrEmpty(Eval("itemMSRP").ToString()) || Eval("itemMSRP").ToString() == "0" ? "" : ConfigurationManager.AppSettings.Get("CurrencySymbol")) %><label class="labelamout"><%# Eval("itemMSRP") %></label></td>--%>
                            <%--<td><asp:TextBox ID="txtPStock" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
						            <td> <asp:TextBox ID="txtPCustomMargine" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>--%>
                            <td align="right">
                                <label class="labelamout" mrpid='<%# Eval("mrpid") %>'><%# Eval("itemMSRP") %></label>
                                <asp:TextBox ID="txtMRP" TextMode="Number" runat="server" itemid='<%# Eval("stit_Id") %>' mrpid='<%# Eval("mrpid") %>' CssClass="d-none mrpinput editamout text-right" Text='<%# Eval("itemMSRP") %>' onfocus="this.select()" onchange="if($(this).val() == '' || $(this).val() <=0) $(this).data('title', 'Value should be greater than 0').addClass('error'); else $(this).removeClass('error').tooltip('dispose');"></asp:TextBox>
                            </td>
                            <td><%# Eval("stit_brand_name") %></td>
                            <td><%# Eval("stit_category_name") %></td>
                            <%--<td data-bootstrap-switch>
                                        <div class="d-flex align-items-center">
                                            <div class="toggle-wrapper"><div class="toggle toggle-light success" data-toggle-on="<%# Eval("stit_custInitiate").Equals(1) ? "true" : "false" %>"></div></div>
                                        <asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" style="display: none;" AutoPostBack="true" runat="server" itemId='<%# Eval("stit_Id") %>' Checked='<%# Eval("stit_custInitiate").Equals(1) %>'/>
                                        <asp:TextBox ID="txtReturnDays" runat="server" placeholder="No. of days" CssClass="form-control text-right" Width="100px"></asp:TextBox>
                                        </div>
                                  
                                    </td>--%>
                        </tr>



                    </ItemTemplate>
                    <EmptyItemTemplate>
                        <div class="text-center">
                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                            <h6 class="mb-3">No record available</h6>
                        </div>
                    </EmptyItemTemplate>
                </asp:ListView>
                <!-- /.table -->
            </div>
        </div><!--card-body-->


<%--<div class="card-footer d-flex flex-wrap justify-content-between">
            <div class="pagination-wrapper">
<asp:Literal runat="server" ID="ltrPagingCurTotal" Text=""></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text=""></asp:Literal>
                  <div class="btn-group ml-2">
                      <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" PreviousPageText="<i class='fa fa-angle-double-left'>" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" NextPageText="<i class='fa fa-angle-left'></i>" ShowNextPageButton="true" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>
                  </div>
                </div>--%>
                      


        <div class="card-footer d-flex flex-wrap justify-content-between">
            <div class="pagination-wrapper">
                <%--<ul class="pagination pagination-circle mg-b-0 p-lg-0">
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
                <asp:DataPager ID="DataPager1" runat="server" PagedControlID="lstProducts" PageSize="10">
                                            <Fields>
                                                <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm" PreviousPageText="<" ShowFirstPageButton="true" FirstPageText="<<" LastPageText=">>" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                                                <asp:NumericPagerField ButtonType="Link" NumericButtonCssClass="btn btn-default btn-sm" />
                                                <asp:NextPreviousPagerField ButtonType="Link" NextPageText=">" ShowNextPageButton="true" ButtonCssClass="btn btn-default btn-sm" ShowLastPageButton="true" ShowPreviousPageButton="false" LastPageText=">>" />
                                            </Fields>
                                        </asp:DataPager>
            </div>

            <div class="d-sm-flex mt-3 mt-lg-0 wiz_btnsect justify-content-center">
                <asp:LinkButton runat="server" CssClass="btn btn-primary btn-block mx-2 wd-sm-auto-force px-4" OnClick="btnSaveProducts_Click" Text="Save Products" novalidate></asp:LinkButton>
              <%--<asp:Button CssClass="btn btn-primary btn-block mx-2 wd-sm-auto-force px-4" ID="btnSaveProducts" OnClick="btnSaveProducts_Click" CausesValidation="false" ValidateRequestMode="Disabled" runat="server" Text="Save Products" novalidate="novalidate" />--%>
              <asp:HyperLink NavigateUrl="/tenant/StockPrice" CssClass="btn btn-secondary btn-block m-0 mx-2 wd-sm-auto-force px-4" ID="hlSaveProductsMoveNext" runat="server" Text="Next"></asp:HyperLink>
            </div>
        </div><!--card-footer-->


          <%--<div class="wizard_wrap p-3">


              <div class="filter_expand_wrap navbar-expand-lg">


                  <div class="collapse navbar-collapse flex-wrap filter_expand" id="filter_expand">
                      <div class="row row-sm mt-3 mx-wd-auto wd-sm-100p">


                          


                          <div class="col-lg-3 input-group-sm mg-b-10 mg-lg-b-0">
                              
                          </div><!--col-lg-3-->

                          <div class="col-lg-3 input-group-sm mg-b-10 mg-lg-b-0">
                              
                          </div><!--col-lg-3-->

                          <div class="col-lg-3 input-group-sm mg-b-10 mg-lg-b-0">
                              
                          </div><!--col-lg-3-->

                          <div class="col-lg-3 input-group-sm">
                              
                          </div><!--col-lg-3-->




                      </div><!--row-->

                      <div class="row row-sm mt-3 wd-100p-force pagenation">
                          <div class="col-12 col-lg-8 d-flex flex-wrap">

                              

                          </div>
                          <!--col-12-->
                          <div class="col-12 col-lg-4 text-left text-lg-right mg-lg-b-0">
                              <asp:Literal runat="server" ID="ltrPagingCurStart" Text=""></asp:Literal>-
                              <asp:Literal runat="server" ID="ltrPagingCurTotal" Text=""></asp:Literal>/
                              <asp:Literal runat="server" ID="ltrPagingTotal" Text=""></asp:Literal>
                              <div class="btn-group ml-2">
                                  <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                                      <Fields>
                                          <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" PreviousPageText="<i class='fa fa-angle-left'></i>" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                                          <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" NextPageText="<i class='fa fa-angle-right'></i>" ShowNextPageButton="true" ShowLastPageButton="false" ShowPreviousPageButton="false" />
                                      </Fields>
                                  </asp:DataPager>
                          </div>

                      </div>
                      <!--pagenation-->

                  </div>
                  <!--filter_expand_wrap-->

              </div><!--filter_expand_wrap-->





            
            </div>
        </div>--%>
          <div class="d-sm-flex p-3 wiz_btnsect justify-content-center">
              
          </div>

<asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT brand_id,brand_name FROM mypha_productbrands pb INNER JOIN finascop_stock_itemmaster i ON pb.brand_id=i.pdt_brand 
INNER JOIN (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
	INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id INNER JOIN finascop_branch_group_business_type bgt 
                        ON bgt.business_type_id= pc.parent_category_businessType AND bgt.store_group_id=@storeId)sc ON i.product_category =  sc.sub_category_id 
                        WHERE IFNULL(brand_name, '') NOT LIKE '' GROUP BY brand_id ORDER BY brand_name" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storeId" DefaultValue="0" /></SelectParameters>
</asp:SqlDataSource>


<asp:ObjectDataSource ID="ODSCategoriesDirect" runat="server" TypeName="RetalineProAgent.Core.Services.APIService"
       SelectMethod="Categories" OnSelecting="OBJ_Selecting" >
        <SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:ObjectDataSource>

                      <label for="txtSearchProduct" visible="false" runat="server"></label>
                      <label for="txtDateFrom" visible="false" runat="server"></label>

                  <asp:Literal ID="ltrItemFilterName" runat="server"></asp:Literal>
                <asp:HiddenField ID="hidProductPager" Value="1" runat="server" />

<div class="row">

        <!-- /.col -->

      </div>


<asp:SqlDataSource ID="SDSProducts" runat="server" OnSelected="SDSProducts_Selected"  OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
 SelectCommand="select myProducts.*, ifnull(item_mrp.itemMrp, 0) as itemMSRP, item_mrp.id AS mrpid from( 
        SELECT stit_Id, stit_itemId, stit_custInitiate, stit_itemReturnTime, stit_itemERPId, stit_SKU, stit_HSNCode, stit_MRP, stit_brand_name, stit_category_name, med_manufacturename,  
        (SELECT image_url FROM finascop_stock_item_images WHERE product_id= i.stit_ID LIMIT 1) AS imageurl, 
           CASE WHEN EXISTS(SELECT * FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE bi.stit_id = i.stit_Id AND b.br_storeGroup=@storeId) THEN 1 ELSE 0 END AS instock     
        FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
         INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
         INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType 
         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id AND bbt.store_group_id=@storeId
        WHERE stit_status = 1 and (ifnull(i.stit_StoreGroup, 0) <= 0 or i.stit_StoreGroup = @storeId ) AND (@brand <= 0 OR pdt_brand = @brand) AND (@category <= 0 OR c.category_id = @category) AND (@department <= 0 OR pc.parent_category_id = @department)
    AND (trim(ifnull(@searchKey, '')) like '' or stit_SKU like CONCAT('%', @searchKey, '%')) 
         GROUP BY stit_Id
 ) myProducts inner join item_mrp on item_mrp.stit_id=myProducts.stit_id AND item_mrp.itemMrp > 0 where @type = 0 or (@type = 1 and  myProducts.instock > 0) or (@type = 2 and  myProducts.instock <= 0)  ORDER BY stit_SKU" ProviderName="MySql.Data.MySqlClient">

<SelectParameters>
    <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selBrand" Name="brand" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" ConvertEmptyStringToNull="false" />
    <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
    <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />
</SelectParameters>
</asp:SqlDataSource>

<asp:SqlDataSource ID="SDSDepartments" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT pc.parent_category_id, pc.parent_category FROM mypha_productparent_category pc 
     INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType
     INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id WHERE bbt.store_group_id= @storeId" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
</SelectParameters>
</asp:SqlDataSource>
    
<asp:SqlDataSource ID="SDSCategory" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT c.* FROM mypha_productcategory c INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
     INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id
	 WHERE bbt.store_group_id= @storeId and (@department = 0 or pc.parent_category_id = @department) GROUP BY category_id" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        <asp:ControlParameter Name="department" ControlID="selDepartment" />
</SelectParameters>
</asp:SqlDataSource>


<asp:SqlDataSource runat="server" ID="SDSInventory" OnSelecting="SDSBrands_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT bi.* FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId">
    <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" /></SelectParameters>
</asp:SqlDataSource>
<asp:HiddenField ID="hidSelectedItems" runat="server" />
<asp:HiddenField ID="hidSelectedItemsWithPrice" runat="server" />
<!-- BASIC MODAL -->
    <div id="create_new_product" class="modal fade create_new_product" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          
          <div class="modal-body pd-25">
            
            <div class="section-wrapper p-0 border-0">
              <label class="section-title">Create New Product</label>
              <div class="form-layout">
                <div class="row row-sm ">
                  <div class="col-lg-4">
                    <div class="form-group-sm">
                      <label class="form-control-label">Retailer Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selRetCat" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSRetCat" DataTextField="business_type_name" DataValueField="business_type_id"><asp:ListItem Text="Select retailer category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSRetCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt
INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id 
INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id
WHERE gbt.store_group_id=@storegroup ORDER BY bt.business_type_name" 
                        OnSelecting="SDSRetCat_Selecting">
                        <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
                    </asp:SqlDataSource>
                    </div>
                  </div><!-- col-4 -->
                  <div class="col-sm-4">
                    <div class="form-group-sm">
                      <label class="form-control-label">Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSCat" DataTextField="category_name" DataValueField="category_id"><asp:ListItem Text="Select category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc
INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id 
WHERE ppc.parent_category_businessType=@bussinessType">
                        <SelectParameters>
            <asp:ControlParameter Name="bussinessType" ControlID="selRetCat" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
        </SelectParameters>
                    </asp:SqlDataSource>
                    </div>
                  </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                      <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selSubCat" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT msc.sub_category_id,msc.sub_category,msc.main_category,pc.category_name,pc.category_id FROM mypha_productsubcategory msc
INNER JOIN mypha_productcategory pc ON pc.category_id=msc.sub_category_id WHERE msc.main_category=@catName">
                        <SelectParameters>
            <asp:ControlParameter Name="catName" ControlID="selCat" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
        </SelectParameters>
                    </asp:SqlDataSource>
                    </div>
                  </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                    <label class="form-control-label w-100">Brand: <span class="tx-danger">*</span> <span class="addbrandpopup" data-toggle="modal" data-target="#addbrand">Add Brand</span></label>
                    <asp:DropDownList ID="selBrd" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id" required><asp:ListItem Text="Select brand" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT brand_id,brand_name FROM mypha_productbrands pb INNER JOIN finascop_stock_itemmaster i ON pb.brand_id=i.pdt_brand 
INNER JOIN (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
	INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id INNER JOIN finascop_branch_group_business_type bgt 
                        ON bgt.business_type_id= pc.parent_category_businessType AND bgt.store_group_id=@storegroup)sc ON i.product_category =  sc.sub_category_id 
                        WHERE IFNULL(brand_name, '') NOT LIKE '' GROUP BY brand_id ORDER BY brand_name" OnSelecting="SDSRetCat_Selecting">
                        <SelectParameters><asp:Parameter Name="storegroup" /></SelectParameters>
                    </asp:SqlDataSource>
    
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-8">
                    <div class="form-group-sm">
                      <label class="form-control-label">Product Name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtPrdName" runat="server" required CssClass="form-control" placeholder="Enter product name"/>
                    </div>
                  </div><!-- col-4 -->
                        <!-- col-4 -->
                    <!-- col-4 -->
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label class="form-control-label">Varient: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtVarient" runat="server" required CssClass="form-control" placeholder="Enter varient"/>
                    </div>
                  </div><!-- col-4 -->
                  <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                      <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtQuantity" runat="server" required CssClass="form-control" placeholder="Enter quantity"/>
                    </div>
                  </div><!-- col-4 -->
                  <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Unit: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selUnit" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
                    </div>
                  </div><!-- col-4 -->
                    <!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">HSN: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selHSN" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id"><asp:ListItem Text="Select HSN" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
                    </div>
                  </div><!-- col-4 -->

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtGSTVAT" runat="server" required CssClass="form-control"/>
                    </div>
                  </div><!-- col-4 -->

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Barcode</label>
                      <asp:TextBox ID="txtBarcode" runat="server" CssClass="form-control"/>
                    </div>
                  </div><!-- col-4 -->

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">ERP ID</label>
                      <asp:TextBox ID="txtERPId" runat="server"  CssClass="form-control"/>
                    </div>
                </div><!-- col-4 -->

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Return Time (days): <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDays" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="0">Select days</asp:ListItem>
                              <asp:ListItem>0</asp:ListItem>
                              <asp:ListItem>1</asp:ListItem>
                              <asp:ListItem>2</asp:ListItem>
                              <asp:ListItem>3</asp:ListItem>
                              <asp:ListItem>4</asp:ListItem>
                              <asp:ListItem>5</asp:ListItem>
                              <asp:ListItem>6</asp:ListItem>
                              <asp:ListItem>7</asp:ListItem>
                              <asp:ListItem>8</asp:ListItem>
                              <asp:ListItem>9</asp:ListItem>
                              <asp:ListItem>10</asp:ListItem>
                              <asp:ListItem>11</asp:ListItem>
                              <asp:ListItem>12</asp:ListItem>
                              <asp:ListItem>13</asp:ListItem>
                              <asp:ListItem>14</asp:ListItem>
                              <asp:ListItem>15</asp:ListItem>
                          </asp:DropDownList>
                    </div>
                  </div><!-- col-4 -->
                    
                    
                    
                    <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Edible: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selFoodType" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="0">Select from list</asp:ListItem>
                              <asp:ListItem Value="1">Not Edible</asp:ListItem>
                              <asp:ListItem Value="2">Vegetarian</asp:ListItem>
                              <asp:ListItem Value="3">Non Vegetarian</asp:ListItem>
                              <asp:ListItem Value="4">Vegan</asp:ListItem>
                          </asp:DropDownList>
                    </div>
                  </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Country of Orgin: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selCountry" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" DataValueField="country_id"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
                    </div>
                  </div><!-- col-3-->
                    <div class="col-lg-2">
                    <div class="form-group">
                      <label class="form-control-label">Delivery Mode: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDelMode" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="">Select delivery mode</asp:ListItem>
                              <asp:ListItem Value="1">Courier</asp:ListItem>
                              <asp:ListItem Value="2">Express</asp:ListItem>
                              <asp:ListItem Value="3">Both</asp:ListItem>
                          </asp:DropDownList>
                    </div>
                  </div><!-- col-4 -->
                    <div class="col-lg-4">
                    <div class="form-group">
                      <label class="form-control-label">Short Description:</label>
                  <asp:TextBox ID="TextBox1" runat="server" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
                    </div>
                  </div><!-- col-4 -->
                    <div class="col-lg-8">
                    <div class="form-group">
                      <label class="form-control-label">Long Description</label>
                      <div id="summernote2"> 
                          <%--<textarea rows="2" runat="server" cols="20" id="summernote"></textarea>--%> 
                          <asp:TextBox ID="summernote" runat="server" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
                      </div>
                    </div>
                  </div><!-- col-4 -->
                    </div><!-- row -->
                <div class="form-layout-footer">
                    <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                    <%--<a href="/InventoryMapping" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>--%>
                    <button type="button" class="btn btn-secondary bd-0" data-dismiss="modal">Cancel</button>
                </div>
                  <div class="error_msg_wrap mt-2 mb-1 ht-20"><asp:Literal ID="ltrResult" runat="server"></asp:Literal></div>
              </div><!-- form-layout -->
            </div>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->



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
<asp:HiddenField ID="hidShowAddForm" Value="0" runat="server" />

<!-- BASIC MODAL -->
    <div id="addbrand" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">

            <div class="section-wrapper p-0 border-0">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="row row-sm">
                <div class="col-12"><h6 class="mb-2 tx-dark">Add New Brand</h6></div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label w-100">Brand</label>
                      <asp:TextBox ID="txtBrand" runat="server" CssClass="form-control float-right" placeholder="Brand Name"></asp:TextBox>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label">Manufacturer</label>
                      <asp:TextBox ID="txtManufacturer" runat="server" CssClass="form-control" placeholder="NA" CausesValidation="false"></asp:TextBox>
                  </div>
                </div>
  
              </div> <!--row-->

            </div><!--section-wrapper-->       

            
          </div><!--modal-body-->
          <div class="modal-footer">
            <asp:Button runat="server" ID="btnSave" OnClick="btnSave_Click" CssClass="btn btn-primary bd-0" Text="Save" CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled"/>
            <a href="/Tenant/InventoryMapping" class="btn btn-secondary bd-0" style="width:100px">Cancel</a>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<script type="text/javascript">
    function updateSelection(obj) {
        if ($(obj).is(':checkbox')) {
            var id = $(obj).closest('span').attr('itemid');
            if (!id)
                return;

            if ($(obj).is(':checked')) {
                addItem(id);
                $(obj).closest('tr').addClass('checked_now');
                var mrp = $(obj).closest('tr').find('label.labelamout').text();//('input.editamout').val();
                var mrpid = $(obj).closest('tr').find('label.labelamout').attr('mrpid');
                selectItemMRP(id, mrp, mrpid);
            }
            else {
                removeItem(id);
                $(obj).closest('tr').removeClass('checked_now').removeClass('already_added');
            }
        }
    }

    function addItem(id) {
        var ids = new Array();
        if ($('#<%= hidSelectedItems.ClientID %>').val() != '')
            ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        if(id)
            ids.push(id);

        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));

    }
    function removeItem(id) {
        var ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        ids = jQuery.grep(ids, function (value) {
            return value != id;
        });
        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));
        //if (ids.length <= 0)
        //    $('#< %= btnSaveSelectedItems.ClientID% >').addClass('disabled');

        ids = $('#<%= hidSelectedItemsWithPrice.ClientID %>').val().split(',');
        ids = jQuery.grep(ids, function (value) {
            return value.split('|')[0] != id;
        });
        $('#<%= hidSelectedItemsWithPrice.ClientID %>').val(ids.join(","));
    }
    function selectItemMRP(id, mrp, mrpid=0) {
        if (!id)
            return;
        var ids = new Array();
        if ($('#<%= hidSelectedItemsWithPrice.ClientID %>').val() != '')
                ids = $('#<%= hidSelectedItemsWithPrice.ClientID %>').val().split(',');

                var updated = 0;
                for (var i = 0; i < ids.length; i++) {
                    var item = ids[i].split('|');
                    if (item[0] == id) {
                        ids[i] = item[0] + '|' + mrp.replace("|", "") + '|' + mrpid.replace("|", "");
                        updated = 1;
                    }
                }
                if (id && updated == 0)
                    ids.push(id + '|' + mrp.replace("|", "") + '|' + mrpid.replace("|", ""));

        $('#<%= hidSelectedItemsWithPrice.ClientID %>').val(ids.join(","));

    }

    $(function () {
        //'use strict';

        // Inline editor
        //var editor = new MediumEditor('.editable');

        // Summernote editor
        $('#<%= summernote.ClientID %>').summernote({
            height: 185,


            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['paragraph', ['paragraph']],
                //['insert', ['link']],

            ]

        });

    });

    $(document).ready(function () {
        /*$('.select2-show-search').select2({
            minimumResultsForSearch: ''
        });*/

        $('#tblSelectedProducts').find('tbody tr input.selectedchangeevent').unbind('change').on('change', function (e) {
            $('#btnSaveSelectedProducts').removeClass('disabled');
            
        });

        $('#price_qunty_alert').on('shown.bs.modal', function (e) {
            
            });
        $('#addproductpopup').on('shown.bs.modal', function (e) {
            $('#mrprrp').focus();
            });


        $('.productcheck input[type="checkbox"]').on('change', function (e) {
            if (e.target.checked) {
                var val = $(this).closest('tr').find('input.editamout').val();
                if (isNaN(val) || val <= 0) {
                    $(this).closest("tr").find(".labelamout").addClass("d-none");
                    $(this).closest("tr").find(".editamout").addClass("d-block");
                    $(this).closest('tr').find('input.editamout').focus();
                }
            }
            else {
                $(this).closest("tr").find(".labelamout").removeClass("d-none");
                $(this).closest("tr").find(".editamout").removeClass("d-block");
            }
        });

        $('input.morethan0').on('change', function (e) {
            if ($(this).val() == '' || $(this).val() <= 0)
                $(this).data('title', 'Value should be greater than 0').addClass('error');
            else
                $(this).removeClass('error').tooltip('dispose');

        });

        //$('.checkboxwrap input[type="checkbox"]').on('change', function (e) {
        //    if (e.target.checked) {
        //        $(this).closest(".checkboxwrap").find(".form-group").addClass("d-block");
        //    }
        //    else {
        //        $(this).closest(".checkboxwrap").find(".form-group").removeClass("d-block");
        //    }
        //});

        $('.editamout').unbind('change').on('change', function (e) {
            var id = $(this).attr('itemid');
            var mrpid = $(this).attr('mrpid');
            var mrp = $(this).val();
            if (id)
                selectItemMRP(id, mrp, mrpid);
        });
        

        $(function () {
            // Summernote editor
            $('#summernote').summernote({
                height: 165,

                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['paragraph', ['paragraph']],
                    //['insert', ['link']],

                ]

            });

        });


    });

    $('#create_new_product').on('shown.bs.modal', function (e) {
        $('#<%= hidShowAddForm.ClientID %>').val('1');
    });
    $('#create_new_product').on('hidden.bs.modal', function (e) {
        $('#<%= hidShowAddForm.ClientID %>').val('0');

    });

    $('.toggle').toggles(
        {
            //on: true,
            height: 26
        },
        //checkbox:
    );
    $('.toggle').on('toggle', function (e, active) {
        $(this).closest('td').find('input[type=checkbox]').trigger('click');
        $(this).addClass('processing_loader');
        //if (active) {
        //    console.log('Toggle is now ON!');
        //} else {
        //    console.log('Toggle is now OFF!');
        //}
    });

</script>
