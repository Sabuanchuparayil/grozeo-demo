<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products In Selected Brand" AutoEventWireup="true" CodeBehind="BrandProduct.aspx.cs" Inherits="RetalineProAgent.BrandProduct" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/MyProducts">My Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brand Products</li>--%>
    <a href="/Tenant/MyProducts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" Text="Products available in selected brand" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Manage Your Product Portfolio</p>
                <% if (this.CurrentUser.TenantType != 1)
                    {  %>
        <p class="mg-b-0 text-info">The merchant account is registered as Affiliate. Only products without GST enabled will be listed.</p>
                <% } %>
    </div>
    <style>
        table.table table, table.table table td {
            border: 0px !important;
            padding: 5px;
        }
    </style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm align-items-lg-center">

                <div class="col-12 col-sm-5 mb-3 mb-sm-0 d-flex align-items-center">
                    <div class="d-flex flex-lg-nowrap align-items-end">
                        <h5 class="mb-0 tx-dark text-truncate">
                            <asp:Literal ID="ltrProductName" runat="server"></asp:Literal>
                        </h5>
                        <a href="javascript:void(0)" class="tx-primary ml-3 tx-12 text-lg-right" data-toggle="modal" data-target="#modalBrand" style="text-decoration: underline;">Change</a>
                    </div>                     
                </div>
                <!-- col-5-->

                <div class="col-12 col-sm-7">
                    <div class="row row-sm align-items-sm-center">
                        <div class="col-sm-5 input-group-sm mg-b-10 mg-sm-b-0">
                            <div class="form-group mb-0 ">
                                <%--<label class="form-control-label tx-dark mb-1">Filter By</label>--%>
                                <%--<input type="text" style="display:none" />
                                <input type="password" style="display:none" />
                                <asp:TextBox ID="txtSubCat" runat="server" CssClass="form-control" autocomplete="nofill"></asp:TextBox>--%>
                                <asp:DropDownList ID="selCategory" runat="server" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSCat" DataTextField="sub_category" DataValueField="sub_category_id" AppendDataBoundItems="true" OnSelectedIndexChanged="selCategory_SelectedIndexChanged"><asp:ListItem Text="Filter By Sub Category" Value="-1"></asp:ListItem></asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT sub_category_id,sub_category,main_category FROM mypha_productsubcategory 
                                    INNER JOIN finascop_stock_itemmaster ON product_category = sub_category_id 
                                    INNER JOIN mypha_productbrands ON brand_id = pdt_brand
                                    INNER JOIN `mypha_productcategory` mpc ON `category_id`=`main_category` 
                                    INNER JOIN mypha_productparent_category ppc ON parent_category_id = mpc.parent_category
                                    INNER JOIN finascop_business_type bt ON bt.business_type_id= ppc.`parent_category_businessType`
                                    INNER JOIN finascop_branch_group_business_type bg ON bg.business_type_id = bt.business_type_id
                                    WHERE bg.store_group_id=@storegroup AND brand_id=@brandId and (@isNoneGST =0 or isNonGSTRetailer = 1) GROUP BY sub_category_id ORDER BY sub_category ASC" OnSelecting="SDSCat_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                        <asp:Parameter Name="brandId" />
                                        <asp:Parameter Name="isNoneGST" DefaultValue="0" />
                                    </SelectParameters>
                    </asp:SqlDataSource>
                            </div>
                        </div> <!--col-lg-5-->

                        <div class="col-sm-1 px-0 d-flex mb-2 mb-sm-0 justify-content-center">
                            <span class="tx-dark wd-40 ht-40 d-flex justify-content-center align-items-center rounded-circle" style="background-color: #E5F0E3;">OR</span>
                        </div>

                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <div class="input_search_box">
                          <asp:TextBox ID="txtSearchProduct" runat="server" autocomplete="off" CssClass="form-control" placeholder="Search in Products"></asp:TextBox>
                          <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                            </div>
                            <!-- input-group -->
                        </div>
                        <!--col-lg-3-->
                    </div>
                    
                </div><!--row-->
            </div>
          </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <asp:GridView AutoGenerateColumns="false" ID="gvBrandProduct" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvBrandProduct_DataBound" DataSourceID="SDSBrandProduct" OnRowDataBound="gvBrandProduct_RowDataBound">
                    <Columns>
                        <asp:TemplateField Visible="false">
                            <ItemTemplate>
                                <asp:CheckBox ID="chkProductItem" CssClass="productcheck" onclick="updateSelection(this);" itemmrp='<%# Eval("stit_MRP") %>' itemid='<%# Eval("stit_Id") %>' erpid='<%# Eval("stit_HSNCode") %>' mrpid='<%# Eval("mrpid") %>' Checked='<%# IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) %>' runat="server" />
                            </ItemTemplate>
                        </asp:TemplateField>
                        <asp:TemplateField HeaderText="Name" HeaderStyle-Width="40%">
                            <ItemTemplate>
                                <div class="d-flex align-items-center">
                                    <div class="prodct_img">
                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                        <div class="imgpopover">
                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                        </div>
                                        <asp:HiddenField ID="hidStitID" runat="server" Value='<%# Eval("stit_ID") %>' />
                                    </div>
                                    <asp:Label runat="server" ID="lblName" CssClass="prd_name" ToolTip='<%# Bind("stit_SKU") %>'><strong><%# Eval("stit_SKU") %></strong></asp:Label>

                                </div>
                            </ItemTemplate>
                        </asp:TemplateField>
                        <asp:BoundField DataField="itemMSRP" SortExpression="itemMSRP" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-Width="100" />
                        <asp:BoundField HeaderText="Brand" DataField="stit_brand_name" SortExpression="stit_brand_name" />
                        <asp:BoundField HeaderText="Sub Category" DataField="sub_category" SortExpression="sub_category" />
                        <asp:TemplateField HeaderText="Action" ItemStyle-HorizontalAlign="Center">
                            <ItemTemplate>
                                <asp:LinkButton ID="lbtnAdd" CssClass="btndelbtype" itemmrp='<%# Eval("itemMSRP") %>' itemid='<%# Eval("stit_Id") %>' hsncodeid='<%# Eval("stit_hsnId") %>' hsnId='<%# Eval("stit_HSNCode") %>' hsnode='<%# Eval("stit_HSN_code") %>' gst='<%# Eval("stit_GST") %>' cess='<%# Eval("displayCess") %>' mrpid='<%# Eval("mrpid") %>' action='<%# (IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) ? "Delete" : "Add") %>' stitId='<%# Eval("stit_ID") %>' sku='<%# Eval("stit_SKU") %>' OnClientClick='<%# IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) ? "return handleProductClick(this);" : "return showProductPopup(this);" %>' runat="server"><i class="<%# (IsSelected(Eval("stit_Id").ToString(), Eval("mrpid").ToString()) ? "fa-regular fa-trash-can tx-20 tx-danger" : "fa-regular fa-circle-plus tx-22 tx-success") %>"></i></asp:LinkButton>
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
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                </asp:GridView>

                <asp:SqlDataSource runat="server" ID="SDSBrandProduct" OnSelecting="SDSBrandProduct_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="GetBrandProductsPage" SelectCommandType="StoredProcedure">
                    <SelectParameters>
                        <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" ConvertEmptyStringToNull="false" />
                        <asp:ControlParameter ControlID="selCategory" Name="category" DbType="Int32" DefaultValue="-1" />
                        <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
                        <asp:Parameter Name="brandId" />
                        <asp:Parameter Name="catId" />
                        <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />
                        <asp:Parameter Name="isNoneGST" DefaultValue="0" />
                        <asp:Parameter Name="startIndex" Type="Int32" DefaultValue="1" />
                        <asp:Parameter Name="pageSize" Type="Int32" DefaultValue="10" />
                    </SelectParameters>
                </asp:SqlDataSource>
                <asp:HiddenField ID="hidSelectedItems" runat="server" />
                <asp:HiddenField ID="hidSelectedItemsWithPrice" runat="server" />
                <asp:HiddenField ID="hdnHSNCode" runat="server" />
            </div>
        </div>
        <!-- card-body -->
        <div class="card-footer d-flex flex-wrap justify-content-lg-end">
                
                <div class="d-sm-flex mt-3 mt-lg-0 wiz_btnsect justify-content-center">
                    <asp:LinkButton ID="lnkBrand" runat="server" CssClass="btn btn-primary btn-block mx-2 wd-sm-auto-force px-4" OnClientClick="return confirm('Are you sure you want to create a new product?');" branchId='<%# Eval("branch_id") %>' Text="Create a New Product with this Brand" OnClick="lnkBrand_Click"></asp:LinkButton>
                </div>
            </div><!-- card-footer -->
    </div><!-- card -->


    <div id="modalBrand" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          
          <div class="modal-body">
              <button type="button" class="close" id="addProductClose" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              

              <div id="prd_selt_brand" class="prd_selt_brand" runat="server" visible="true">
                  <h5 class="modal-title tx-dark">Add More Products</h5>
                  <div class="py-3">
                      <h6 class="tx-dark">Select a Brand or Subcategory to proceed with adding new products</h6>

                      <div class="d-flex flex-wrap flex-lg-nowrap mb-2">
                          <label class="rdiobox mr-5" id="lblChkenable">
                              <asp:RadioButton ID="rbtnBrand" runat="server" Checked="true" GroupName="rbSelect" />
                              <span class="tx-dark tx-14">Brand Name</span>
                          </label>
                          <label class="rdiobox">
                              <asp:RadioButton ID="rbtnSubCategory" runat="server" GroupName="rbSelect" />
                              <span class="tx-dark tx-14">Subcategory</span>
                          </label>
                      </div>


                      <div class="input-group mb-4 flex-nowrap">

                          <div class="w-100 selectbrand"  runat="server">

                          <asp:DropDownList ID="selBrd" runat="server" CssClass="form-control select2" ForeColor="GrayText" AppendDataBoundItems="true" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id">
                              <asp:ListItem Text="Select brand" Value="-1"></asp:ListItem>
                          </asp:DropDownList>
                          <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                              SelectCommand="SELECT brand_id, brand_name, (CASE WHEN brand_name LIKE 'Generic' THEN 1 ELSE 0 END) AS l_order FROM mypha_productbrands ORDER BY l_order DESC, brand_name">
                              <SelectParameters>
                                  <asp:Parameter Name="storeId" DefaultValue="0" />
                              </SelectParameters>
                          </asp:SqlDataSource>
                       </div>

                          <div class="w-100 selectsubcategory" runat="server">

                              <asp:DropDownList ID="selSubCategory" runat="server" CssClass="form-control select2" AppendDataBoundItems="true" ForeColor="GrayText" DataSourceID="SDSSubcategory" DataTextField="sub_category" DataValueField="sub_category_id">
                                  <asp:ListItem Text="Select subcategory" Value="-1"></asp:ListItem>
                              </asp:DropDownList>
                              
                              <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSSubcategory_Selecting" runat="server" ID="SDSSubcategory" ProviderName="MySql.Data.MySqlClient"
                                  SelectCommand="SELECT store_group_id AS store,business_type_name AS RetailCategory,parent_category_id,mypha_productparent_category.parent_category AS Department,
                    category_id,category_name AS category,sub_category_id,sub_category FROM mypha_productsubcategory INNER JOIN 
                    mypha_productcategory ON category_id = main_category INNER JOIN mypha_productparent_category 
                    ON mypha_productparent_category.parent_category_id = mypha_productcategory.parent_category
                    INNER JOIN finascop_business_type bt ON business_type_id = parent_category_businessType INNER JOIN 
                    finascop_branch_group_business_type bgt ON  bt.business_type_id = bgt.business_type_id WHERE store_group_id=@storeId ORDER BY RetailCategory ASC">

                                  <SelectParameters>
                                      <asp:Parameter Name="storeId" DefaultValue="0"/>
                                  </SelectParameters>
                              </asp:SqlDataSource>

                          </div>
                       
                           <asp:LinkButton ID="lnkBrands" runat="server" CssClass="btn btn-inline-block btn-primary ml-2" branchId='<%# Eval("branch_id") %>' Text="GO" OnClick="lnkBrands_Click"></asp:LinkButton>
                           <asp:LinkButton ID="lnkSubcategory" runat="server" CssClass="btn btn-inline-block btn-primary ml-2 hide" catId='<%# Eval("category_id") %>' Text="GO" OnClick="lnkSubcategory_Click"></asp:LinkButton>
                      </div>
                      <!--input-group-->
                      
                      <a href="javascript:void(0)" class="tx-dark" runat="server" id="brandCreate" onclick="myBrand()" style="text-decoration: underline;">Brand is not listed. Create a new brand</a>
                  </div>
              </div>
       
              <!--prd_selt_brand-->     

              <div id="prd_crt_new_brand" class="prd_crt_new_brand" style="display:none;">
                  <h5 class="modal-title tx-dark" id="create_new_ProductsTitle">Create New Brand</h5>
                  <div class="py-3">
                      <h6 class="tx-dark">Add new Brand to proceed with adding new products</h6>
                      <div class="input-group mb-4 flex-wrap">
                          <%--<input name="" type="text" id="" class="form-control w-100">--%>
                          <input type="text" style="display: none" />
                          <input type="password" style="display: none" />
                          <asp:TextBox ID="txtNewBrand" runat="server" CssClass="form-control w-100" placeholder="Enter new brand" autocomplete="off" />
                          <asp:RequiredFieldValidator runat="server" CssClass="error_msg_wrap tx-danger" SetFocusOnError="true" ErrorMessage="Please input brand name. " ControlToValidate="txtNewBrand" Display="Dynamic" ValidationGroup="CreateBrand"></asp:RequiredFieldValidator>
                          <span class="error_msg_wrap" id="addbranderror"><asp:Literal ID="ltrAddBrandResult" runat="server"></asp:Literal></span>
                          
                          <%--<a id="" class="btn btn-inline-block btn-primary mt-3" href="#">Save & Create New Product</a>--%>
                          <asp:LinkButton runat="server" Text="Save & Create New Product" OnClick="btnAddBrand_Click" CssClass="btn btn-inline-block btn-primary mt-3" ValidationGroup="CreateBrand"></asp:LinkButton>
                      </div>
                      <!--input-group-->
                      <%--<a href="#" class="tx-dark" style="text-decoration: underline;">I will select from the Brands listed</a>--%>
                      <a href="javascript:void(0)" class="tx-dark" runat="server" id="existBrand" onclick="existedBrand()" style="text-decoration: underline;">I will select from the brands listed</a>
                  </div>
              </div>
              <script>
                  function myBrand() {
                      $(".prd_crt_new_brand").show();
                      $(".prd_selt_brand").hide();
                  }
                  $('#addProductClose').click(function () {
                      $('#modalBrand').modal('hide');
                      $(".prd_selt_brand").show();
                      $(".prd_crt_new_brand").hide();
                  })
                  function existedBrand() {
                      $(".prd_crt_new_brand").hide();
                      $(".prd_selt_brand").show();
                  }

                  function showSubCategoryDropdown() {
                      $(".selectbrand").hide();
                      $(".selectsubcategory").show();
                      $('#<%= brandCreate.ClientID %>').hide();
                      $('#<%=lnkSubcategory.ClientID %>').removeClass('hide');
                      $('#<%=lnkBrands.ClientID %>').addClass('hide');

                  }

                  $(document).ready(function () {
                      $('.selectsubcategory').hide()
                      function showBrandDropdown() {
                          $(".selectbrand").show();
                          $('.selectsubcategory').hide();
                          $('#<%= brandCreate.ClientID %>').show();
                          $('#<%=lnkSubcategory.ClientID %>').addClass('hide');
                          $('#<%=lnkBrands.ClientID %>').removeClass('hide');
                      }

                      function showSubCategoryDropdown() {
                          $(".selectbrand").hide();
                          $(".selectsubcategory").show();
                          $('#<%= brandCreate.ClientID %>').hide();
                          $('#<%=lnkSubcategory.ClientID %>').removeClass('hide');
                          $('#<%=lnkBrands.ClientID %>').addClass('hide');

                      }

                      function handleRadioChange() {
                          if ($('#<%= rbtnBrand.ClientID %>').is(':checked')) {
                              showBrandDropdown();
                          } else if ($('#<%= rbtnSubCategory.ClientID %>').is(':checked')) {
                              showSubCategoryDropdown();
                          }
                      }

                      $('#<%= rbtnBrand.ClientID %>').change(handleRadioChange);
                      $('#<%= rbtnSubCategory.ClientID %>').change(handleRadioChange);

                      handleRadioChange();
                  });

              </script>  
              
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <div id="modalAddProduct" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">

                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group mt-4 mb-0">
                                    <p class="m-0 tx-center" runat="server" id="modalMessage"></p>
                                   <%--<p class="m-0 tx-center">The selected brand has no products to add. If you wish to create a new product under this brand, Please click proceed or to choose a different brand, click Cancel.</p> --%>
                                </div>
                            </div>
                        </div>
                        <!--row-->

                    </div>
                    <!--section-wrapper-->

                    <div class="modal-btn mt-3">
                        <asp:Button runat="server" ID="btnAddPrdt" CssClass="btn btn-primary mr-2 bd-0" Text="Proceed" OnClick="btnAddPrdt_Click" />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" data-toggle="modal" data-target="#modalBrand" style="width: 100px">Cancel</a>
                        <%--<a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>--%>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <asp:HiddenField ID="hdnAction" runat="server" />
    <asp:HiddenField ID="hdnSKU" runat="server" />

    <!-- Popup Modal -->
<div id="productModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" runat="server" id="titleName">Add Product</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <asp:HiddenField ID="hdnItemId" runat="server" />
        <asp:HiddenField ID="hdnMrpId" runat="server" />
        
        <!-- First row: HSN, GST, Cess -->
        <div class="form-row">
          <div class="form-group col-sm-4">
            <label>HSN</label>
            <asp:TextBox ID="txtHSN" CssClass="form-control" runat="server" />
          </div>
          <div class="form-group col-sm-4">
            <label><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></label>
            <asp:TextBox ID="txtGST" CssClass="form-control" runat="server" />
          </div>
          <div class="form-group col-sm-4" runat="server" visible='<%# ConfigurationManager.AppSettings["CountryCode"] == "IN" %>'>
            <label>Cess</label>
            <asp:TextBox ID="txtCess" CssClass="form-control" runat="server" />
          </div>
        </div>

        <!-- Second row: Product Code, MRP -->
        <div class="form-row">
          <div class="form-group col-sm-6">
            <label>Product Code</label>
            <asp:TextBox ID="txtProductCode" CssClass="form-control" runat="server" Text='<%# Bind("fsipc_code") %>' DataSourceID="SDSPrdCode" />
          </div>
          <div class="form-group col-sm-6">
            <label>MRP</label>
            <asp:TextBox ID="txtMRP" CssClass="form-control" runat="server" />
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <asp:Button ID="btnSubmitProduct" runat="server" Text="Submit" CssClass="btn btn-primary" OnClick="addProduct_Click" />
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

    <asp:Button ID="btnLoadPopupData" runat="server" Style="display:none" OnClick="btnLoadPopupData_Click" />

    <asp:HiddenField ID="hdnHsnId" runat="server" />
    <asp:HiddenField ID="hdnGst" runat="server" />
    <asp:HiddenField ID="hdnCess" runat="server" />
    <asp:HiddenField ID="hdnOriginalProductCode" runat="server" />

    <asp:SqlDataSource ID="SDSPrdCode" runat="server"
    ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    ProviderName="MySql.Data.MySqlClient"
    SelectCommand="SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id=@stitId LIMIT 1">
    <SelectParameters>
        <asp:ControlParameter Name="stitId" ControlID="hdnItemId" PropertyName="Value" Type="Int32" />
    </SelectParameters>
</asp:SqlDataSource>

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
        if (id)
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
        function selectItemMRP(id, mrp, mrpid = 0) {
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
        });

        function showProductPopup(link) {
            var $link = $(link);

            $('#<%= hdnItemId.ClientID %>').val($link.attr('itemid'));
            $('#<%= hdnMrpId.ClientID %>').val($link.attr('mrpid'));
            $('#<%= hdnHSNCode.ClientID %>').val($link.attr('hsnId'));
            $('#<%= hdnHsnId.ClientID %>').val($link.attr('hsncodeid'));
            $('#<%= hdnGst.ClientID %>').val($link.attr('gst'));
            $('#<%= hdnCess.ClientID %>').val($link.attr('cess'));
            $('#<%= hdnAction.ClientID %>').val($link.attr('action'));
            $('#<%= hdnSKU.ClientID %>').val($link.attr('sku'));

            // Set other visible fields
            $('#<%= txtHSN.ClientID %>').val($link.attr('hsnode'));
            $('#<%= txtGST.ClientID %>').val($link.attr('gst'));
            $('#<%= txtCess.ClientID %>').val($link.attr('cess'));
            $('#<%= txtMRP.ClientID %>').val($link.attr('itemmrp'));

            // Set modal title to SKU
            var sku = $link.attr('sku');
            $('#productModal .modal-title').text(sku);

            // Trigger postback to load Cess
            __doPostBack('<%= btnLoadPopupData.UniqueID %>', '');

            return false; 
        }

        function handleProductClick(link) {
            var $link = $(link);
            var action = $link.attr('action');
            $('#<%= hdnItemId.ClientID %>').val($link.attr('itemid'));

            if (action === "Delete") {
                var confirmDelete = confirm("Do you want to delete this product?");
                if (confirmDelete) {
                    $('#<%= hdnAction.ClientID %>').val("Delete"); 
                    __doPostBack('<%= btnSubmitProduct.UniqueID %>', '');
                }
            }
            return false;
        }
    </script>             
    <script>
        $(document).ready(function () {
            $(document).ready(function () {
                $('.select2').select2();

                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();
            });
        });
    </script>
        <style>
            .select2.select2-container {
                width: 100% !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                display: block;
                line-height: 36px;
            }

            .select2-container.select2-container--open {
                z-index: 1050;
            }

            .slim-sticky-sidebar .slim-header {
                z-index: 1051;
            }
        </style>

</asp:Content>
