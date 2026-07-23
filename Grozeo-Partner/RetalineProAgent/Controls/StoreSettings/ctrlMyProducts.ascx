<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlMyProducts.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlMyProducts" %>
<style>
    .tbl_prod_img{
        width:auto; max-width: 30px; max-height: 28px;
    }
</style>
<div class="card">
    <div class="card-header shadow_top">
            <div class="row row-sm align-items-sm-center">
                <div class="col-lg-3 d-flex justify-content-lg-start align-items-lg-end">
                    <a href="#" type="button" class="btn btn-primary mb-2 mb-lg-0 w-auto w-lg-100" data-toggle="modal" data-target="#modalBrand">Add More Products<i class="icon ion-plus-circled ml-2"></i></a>
                </div>
                <div class="col-sm-5 col-lg-4 input-group-sm mg-b-10 mg-sm-b-0">
                    <div class="form-group mb-0">
                        <%--<label class="form-control-label tx-dark mb-1">Brand</label>--%>
                        <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" AppendDataBoundItems="true" OnSelectedIndexChanged="selBrand_SelectedIndexChanged" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
                            <asp:ListItem Text="Filter by Brand" Value="-1"></asp:ListItem>
                            <%--<asp:DropDownList ID="DropDownList1" runat="server" CssClass="form-control select2" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id" AppendDataBoundItems="true" AutoPostBack="true">
                        <asp:ListItem Text="All brands" Value="-1"></asp:ListItem>
                    </asp:DropDownList>--%>
                        </asp:DropDownList>
                    </div>
                </div>
                <div class="col-sm-2 col-lg-1 d-flex justify-content-center">
                    <span class="tx-dark wd-40 ht-40 d-flex justify-content-center align-items-center rounded-circle" style="background-color:#E5F0E3 ;">OR</span>
                </div>
                <div class="col-sm-5 col-lg-4 mt-2 mt-sm-0 d-flex align-items-end">
                    <div class="input-group ">
                        <%--<label class="form-control-label tx-dark mb-1">Search with</label>--%>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <div class="input_search_box">
                          <asp:TextBox ID="txtSearchProduct" runat="server" autocomplete="off" CssClass="form-control" placeholder="Search in Products"></asp:TextBox>
                          <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600" OnClick="lbtnSearch_Click" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                        </div>                        
                    </div>
                    <!-- input-group -->
<%--                    <a class="btn btn-outline-primary mt-2 mt-lg-0 ml-2" href="javascript:void(0)">Reset</a>--%>
                   <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/MyProducts.aspx" Text="Reset" />

                </div>
                

            </div><!--row-->
        </div><!--card heder-->
    <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvProducts" GridLines="None" runat="server" CssClass="table table-bordered mg-b-0 gridview_table" OnRowDataBound="gvProducts_RowDataBound"
                                    AllowPaging="true" AllowSorting="true" AllowCustomPaging="true" ShowFooter="false" PagerSettings-Visible="true" DataKeyNames="Id" PageSize="10" OnDataBound="gvProducts_DataBound" DataSourceID="SDSInventory">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Name">
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center">
                                                    <div class="prodct_img">
                                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.OptimizedImageUrl(Eval("imageurl").ToString(), 150, 150) %>' loading="lazy" />
                                                        <div class="imgpopover">
                                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        </div>
                                                        <asp:HiddenField ID="hidStitID" runat="server" Value='<%# Eval("stit_ID") %>' />
                                                    </div>
                                                    <asp:Label runat="server" ID="lblName" CssClass="prd_name" ToolTip='<%# Bind("stit_SKU") %>'><strong><%# Eval("stit_SKU") %></strong><%# (String.IsNullOrEmpty(Eval("fsipc_code").ToString()) ?"": String.Format(" (Code: {0})", Eval("fsipc_code"))) %></asp:Label>
                                                    <%--<p class="m-0"><%# (String.IsNullOrEmpty(Eval("fsipc_code").ToString()) ?"": String.Format(" (Code: {0})", Eval("fsipc_code"))) %></p>--%>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Brand" DataField="stit_brand_name" SortExpression="stit_brand_name"/>
                                        <asp:BoundField HeaderText="Sub Category" DataField="stit_category_name" SortExpression="stit_category_name" />
                                        <asp:TemplateField HeaderText="HSN/SAC" SortExpression="displayHSN">
                                            <HeaderStyle CssClass="left_align" Width="150px" />
                                            <ItemStyle HorizontalAlign="Right" />
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <%# Eval("displayHSN") %> 
                                                    <span class="btn_vrfy">
                                                        <asp:LinkButton ID="lnkVerify" CssClass='<%# GetVerifyClass(Eval("taxValue")) %>' runat="server" CommandArgument='<%# Eval("taxValue") %>' taxValue='<%# Eval("taxValue") %>' hsncode='<%# Eval("displayHSN") %>' tax='<%# Eval("displayGST") %>' stitid='<%# Eval("stit_ID") %>' store='<%# Eval("fsipc_store") %>' storegroup='<%# Eval("fsipc_storeGroup") %>' branchname='<%# Eval("branchName") %>' branchId='<%# Eval("branch_id") %>' hsnCess='<%# Eval("hsnCess") %>' OnClick="lnkVerify_Click"><%# GetVerifyText(Eval("taxValue")) %></asp:LinkButton>
                                                    </span>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="GST" DataField="displayGST" SortExpression="displayGST" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-Width="100"/>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action">
                                            <ItemTemplate>
                                                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" stitid='<%# Eval("stit_ID") %>' erpid='<%# Eval("fsipc_code") %>' erptype='<%# Eval("fsipc_isCompany") %>' erptypename='<%# Eval("fsipc_codeType") %>' onclick="loadErpId-(this)"><i class="ion-android-menu"></i></a>
                                                <div class="dropdown-menu p-3" role="menu" style="">
                                                    <div class="d-flex pb-2 border-bottom">
                                                        <a href="javascript:void(0)" class="" stitid='<%# Eval("stit_ID") %>' erpid='<%# Eval("fsipc_code") %>' erptype='<%# Eval("fsipc_isCompany") %>' erptypename='<%# Eval("fsipc_codeType") %>' store='<%# Eval("fsipc_store") %>' storeGroup='<%# Eval("fsipc_storeGroup") %>' branchname='<%# Eval("branchName") %>' onclick="loadErpId(this)">Set ERP Id / Barcode</a>
                                                    </div>
                                                    <div class="d-flex py-2 border-bottom">
                                                        <a href="javascript:void(0)" class="" stitid='<%# Eval("stit_ID") %>' spotreturn='<%# Eval("hasSpotReturn") %>' returndays='<%# Eval("returnTime") %>' onclick="loadReturnDays(this)">Return Days</a>
                                                    </div>
                                                     <div class="d-flex py-2 border-bottom">
                                                         <a id="lbtnDuplicate" class=""  href='<%# $"/Tenant/PrivateInventory.aspx?id={Eval("stit_ID")}&id2={Eval("pdt_brand")}&type=2" %>'>Duplicate</a>
                                                    </div>
                                                     <div class="d-flex py-2 border-bottom" runat="server" id="divattribute" visible="false">
                                                    <asp:LinkButton ID="btnattribute" Text="Manage Attribute" runat="server" CommandName="ManageAttribute" CommandArgument='<%# Eval("stit_ID") %>' OnCommand="btnattribute_Command"></asp:LinkButton>
                                                     </div>
                                                     <div class="d-flex py-2 border-bottom" runat="server" visible='<%#(Convert.ToInt32(Eval("stit_StoreGroup")) <= 0 ? true : false) %>'>
                                                        <asp:LinkButton ID="btnpacking" Text="Manage Packing" stitId='<%# Eval("stit_ID") %>' OnClick="btnpacking_Click" runat="server"></asp:LinkButton>
                                                     </div>                                                  
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                            <asp:TemplateField>
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <asp:LinkButton ID="lbtnedit" Text='<%#(Convert.ToInt32(Eval("stit_StoreGroup")) <= 0 ? "View" : "Edit") %>' CssClass="btn btn-outline-primary btn-sm" brandId='<%# Eval("pdt_brand") %>' storeGroupId='<%# Eval("stit_StoreGroup") %>' stitId='<%# Eval("stit_ID") %>' action='<%#(Convert.ToInt32(Eval("stit_StoreGroup")) <= 0 ? "View" : "Edit") %>' OnClick="btnedit_Click" runat="server"></asp:LinkButton>
                                                    <asp:LinkButton ID="btnview" OnClick="btnview_Click" runat="server" stitId='<%# Eval("stit_ID") %>'><i class="fa-solid fa-eye ml-2 tx-gray-600" title="View Product"></i></asp:LinkButton>
                                            </ItemTemplate>
                                            </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3"><small>You dont have any item selected for sale.</small></h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <SortedAscendingHeaderStyle CssClass="sorting sorting_asc" />
                                    <SortedDescendingHeaderStyle CssClass="sorting sorting_desc" />
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSInventory" OnSelecting="SDSInventory_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="GetMyProductsPaged" SelectCommandType="StoredProcedure" OnSelected="SDSInventory_Selected">
        <SelectParameters>
            <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" Type="String" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
            <asp:ControlParameter ControlID="selBrand" Name="brand" DefaultValue="-1" DbType="Int32" PropertyName="Text" />
            <asp:Parameter Name="startIndex" Type="Int32" DefaultValue="1" />
            <asp:Parameter Name="pageSize" Type="Int32" DefaultValue="10" />
            <asp:Parameter Name="totalRecords" Type="Int32" Direction="Output" />
        </SelectParameters>
    </asp:SqlDataSource>
                                </div><!-- table-responsive -->
        </div><!--card-body-->
</div>

<asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT pb.brand_id,pb.brand_name FROM mypha_productbrands pb WHERE EXISTS(SELECT i.* FROM finascop_stock_itemmaster i 
INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id 
AND b.br_storeGroup= @storeId
WHERE i.pdt_brand = pb.brand_id) AND IFNULL(pb.brand_name, '') NOT LIKE '' ORDER BY brand_name"
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
    </SelectParameters>
</asp:SqlDataSource>

<%--<asp:SqlDataSource ID="SDSBrands" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT brand_id,brand_name, storegroup_id,STATUS FROM mypha_productbrands GROUP BY brand_id ORDER BY brand_name" ProviderName="MySql.Data.MySqlClient">
    
</asp:SqlDataSource>--%>


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
                          <asp:RadioButton ID="rbtnBrand" runat="server"  Checked="true" GroupName="rbSelect"/>
                          <span class="tx-dark tx-14">Brand Name</span>
                      </label>
                      <label class="rdiobox">
                          <asp:RadioButton ID="rbtnSubCategory" runat="server" GroupName="rbSelect"/>
                          <span class="tx-dark tx-14">Subcategory</span>
                      </label>
                    </div>


                    <div class="input-group mb-4 flex-nowrap">

                          <div class="w-100 selectbrand"  runat="server">

                            <asp:DropDownList ID="selBrd" runat="server" CssClass="form-control select2" AppendDataBoundItems="true" ForeColor="GrayText" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id" OnDataBound="selectBrand_DataBound">
                            <asp:ListItem Text="Select brand" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selBrd" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select a brand" ValidationGroup="BrandSelect" ForeColor="Red" InitialValue="-1"></asp:RequiredFieldValidator>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                            SelectCommand="SELECT brand_id, brand_name, (CASE WHEN brand_name LIKE 'Generic' THEN 1 ELSE 0 END) AS l_order FROM mypha_productbrands ORDER BY l_order DESC, brand_name"
                            >
                            </asp:SqlDataSource>

                          </div>

                          <div class="w-100 selectsubcategory" runat="server">
                              <asp:DropDownList ID="selSubCategory" runat="server" CssClass="form-control select2" AppendDataBoundItems="true" ForeColor="GrayText" DataSourceID="SDSSubcategory" DataTextField="sub_category" DataValueField="sub_category_id" OnDataBound="selSubCategory_DataBound">
                                  <asp:ListItem Text="Select subcategory" Value="-1"></asp:ListItem>
                              </asp:DropDownList>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="selSubCategory" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select a Subcategory" ValidationGroup="SubcategorySelect" ForeColor="Red" InitialValue="-1"></asp:RequiredFieldValidator>
                              <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubcategory" ProviderName="MySql.Data.MySqlClient"
                                  SelectCommand="SELECT store_group_id AS store,business_type_name AS RetailCategory,parent_category_id,mypha_productparent_category.parent_category AS Department,
                                                 category_id,category_name AS category,sub_category_id,sub_category FROM mypha_productsubcategory INNER JOIN 
                                                 mypha_productcategory ON category_id = main_category INNER JOIN mypha_productparent_category 
                                                 ON mypha_productparent_category.parent_category_id = mypha_productcategory.parent_category
                                                 INNER JOIN finascop_business_type bt ON business_type_id = parent_category_businessType INNER JOIN 
                                                 finascop_branch_group_business_type bgt ON  bt.business_type_id = bgt.business_type_id WHERE store_group_id=@storeId ORDER BY RetailCategory ASC">
                                   
                              <SelectParameters>
                                   <asp:Parameter Name="storeId"/>
                              </SelectParameters>
                              </asp:SqlDataSource>
                          </div>

                          
                          
                          <%--<asp:HyperLink runat="server" CssClass="btn btn-inline-block btn-primary ml-2" NavigateUrl='<%# string.Format("~/Tenant/BrandProduct.aspx?brdId={0}", Eval("brand_id")) %>' Text="GO"></asp:HyperLink>--%>
                          <%--<a href="/Tenant/BrandProduct" brdId='<%# Eval("brand_id") %>' class="btn btn-inline-block btn-primary ml-2">GO</a>--%>
                          <%--<asp:LinkButton ID="lbtnButton" runat="server" Text="Submit" brandId='<%# Eval("brand_id") %>' OnClick="onBrand_Click" CssClass="btn btn-inline-block btn-primary ml-2"></asp:LinkButton>--%>
                          <%--<asp:LinkButton ID="lbtnButton" runat="server" Text="Submit" OnClick="BtnClick" CssClass="btn btn-inline-block btn-primary ml-2" PostBackUrl='<%# string.Format("~/Tenant/BrandProduct.aspx?brdId={0}", Eval("brand_id")) %>'></asp:LinkButton>--%>
                          <%--<asp:LinkButton ID="lbtnButton" runat="server" CssClass="btn btn-inline-block btn-primary ml-2" Text="GO" brandId='<%# Eval("brand_id") %>' OnClick="btnbrand_Click" />--%>
                          <asp:LinkButton ID="lnkSubcategory" runat="server" CssClass="btn btn-inline-block btn-primary ml-2 hide" catId='<%# Eval("category_id") %>' Text="GO" OnClick="lnkSubcategory_Click" ValidationGroup="SubcategorySelect" CausesValidation="true"></asp:LinkButton>
                          <asp:LinkButton ID="lnkBrands" runat="server" CssClass="btn btn-inline-block btn-primary ml-2" branchId='<%# Eval("branch_id") %>' Text="GO" OnClick="lnkBrand_Click" ValidationGroup="BrandSelect" CausesValidation="true"></asp:LinkButton>
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
                      $(".prd_")
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

<asp:HiddenField ID="hidERP_stitid" runat="server" />
    <div id="modalSetErpId" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">
              <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Set ERP Id / Barcode</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="section-wrapper p-0 border-0">
              <div class="row row-sm mb-2">
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <label class="form-control-label w-100">Code <span class="tx-danger">*</span></label>
                      <asp:TextBox ID="txtCode" runat="server" CssClass="form-control" placeholder="ERP / Barcode" ClientIDMode="Static"></asp:TextBox>
                      <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtCode" ErrorMessage="Enter Code" ValidationGroup="SetERPID"></asp:RequiredFieldValidator>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <label class="form-control-label">Type <span class="tx-danger">*</span></label>
                      <asp:DropDownList ID="selERPType" CssClass="form-control" runat="server" ClientIDMode="Static">
                          <asp:ListItem Text="Select Type" Value=""></asp:ListItem>
                          <asp:ListItem Text="Store Code" Value="0"></asp:ListItem>
                          <asp:ListItem Text="Manufacturer Code" Value="1"></asp:ListItem>
                      </asp:DropDownList>
                      <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selERPType" ErrorMessage="Select code type" ValidationGroup="SetERPID"></asp:RequiredFieldValidator>
                  </div>
                </div>
              </div> <!--row-->
                <div id="storeSelectionRow" class="row row-sm">
                    <div class="col-12 col-sm-6 d-flex align-items-center mb-3">
                        <label class="rdiobox mr-4">
                            <asp:RadioButton ID="rbAllStores" runat="server" GroupName="rbgStore" onclick="if ($(this).is(':checked')) $('#dvselectstore').hide(); else $('#dvselectstore').show();" ClientIDMode="Static" />
                            <span>All Stores</span>
                        </label>
                        <label class="rdiobox">
                            <asp:RadioButton ID="rbSelectStore" runat="server" GroupName="rbgStore" onclick="if ($(this).is(':checked')) $('#dvselectstore').show(); else $('#dvselectstore').hide();" />
                            <span>Select Store</span>
                        </label>
                    </div>

                    <div class="col-12 col-sm-6 mb-3" id="dvselectstore" runat="server" style='<%= rbAllStores.Checked ? "display: none": "" %>'>
                        <%--<label class="form-control-label">Select Store:</label>--%>
                        <asp:DropDownList ID="selBranch" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSBranch" DataTextField="br_Name" DataValueField="br_ID" OnDataBound="selBranch_DataBound">
                            <asp:ListItem Text="Select store" Value=""></asp:ListItem>
                        </asp:DropDownList>
                        <asp:SqlDataSource ID="SDSBranch" runat="server"
                            SelectCommand="SELECT br_ID,br_Name FROM finascop_branch WHERE br_storeGroup=@storegroup"
                            OnSelecting="SDSBranch_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:Parameter Name="storegroup" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
            </div><!--section-wrapper-->       

              <div class="modal-btn">
                  <asp:Button runat="server" ID="btnSetErpID" ValidationGroup="SetERPID" OnClientClick="return validatorsIsValid('SetERPID');" OnClick="btnSetErpID_Click" CssClass="btn btn-primary mr-2 bd-0" Text="Save" formnovalidate/>
                    <a href="javascript:void(0)" class="btn btn-secondary bd-0"  data-dismiss="modal" aria-label="Close" style="width:100px">Cancel</a>
              </div>
            
          </div><!--modal-body-->
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<div id="modalReturnDays" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">
              <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Return Days</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
                </div>
            <div class="section-wrapper p-0 border-0">
              
              <div class="row row-sm">
                <div class="col-lg-12">
                  <div class="form-group">
                    <label class="form-control-label w-100">Return Days</label>
                      <asp:TextBox ID="txtReturnDays" runat="server" CssClass="form-control" placeholder="Return days" ValidationGroup="ReturnDays"></asp:TextBox>
                  </div>
                </div>
                  <div class="col-6 mb-6" runat="server" visible="false">
                    <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->
              </div> <!--row-->

            </div><!--section-wrapper-->       

              <div class="modal-btn mt-3">
                  <asp:Button runat="server" ID="btnReturnDays" OnClick="btnReturnDays_Click" CssClass="btn btn-primary mr-2 bd-0" Text="Save" ValidationGroup="ReturnDays" formnovalidate />
                  <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
              </div>
          </div><!--modal-body-->
          
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- Hidden field to track state -->
<asp:HiddenField ID="hdnModalOpen" runat="server" />
<asp:HiddenField ID="hdnHsn" runat="server" />
<asp:HiddenField ID="hdnTax" runat="server" />
<asp:HiddenField ID="hdnCess" runat="server" />

<asp:HiddenField ID="hidStitId" runat="server" />
<!-- Modal -->
<div id="modalVerify" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-vertical-center modal w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-body">
                <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Confirm or Correct HSN & Tax%</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="section-wrapper p-0 border-0">
                    <div class="row row-sm mb-2">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="form-control-label" style="width: 100%;">HSN Code <span class="tx-danger">*</span></label>
                                <%--<asp:DropDownList ID="selHSN" runat="server" CssClass="form-control select2" AutoPostBack="true"
                                    DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id" AppendDataBoundItems="true"
                                    OnSelectedIndexChanged="selHSN_SelectedIndexChanged">
                                    <asp:ListItem Text="Select HSN/SAC" Value="" />
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn"  ProviderName="MySql.Data.MySqlClient" 
                                    SelectCommand="SELECT hsn_id, hsn_code FROM finascop_hsn ORDER BY hsn_code">
                                </asp:SqlDataSource>--%>
                                <asp:TextBox ID="txtHSNCode" runat="server" CssClass="form-control"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtHSNCode" ErrorMessage="Enter HSN Code" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div runat="server" class="form-group">
                                <label class="form-control-label" style="width: 100%;">Tax % <span class="tx-danger">*</span></label>
                                <%--<asp:DropDownList ID="selType" Visible="true" runat="server" CssClass="form-control" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selType_SelectedIndexChanged" OnDataBound="selType_DataBound">
                                    <asp:ListItem Text="Select Tax" Value="" />
                                </asp:DropDownList>--%>
                                <asp:TextBox ID="txtTax" runat="server" CssClass="form-control"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtTax" ErrorMessage="Enter Tax" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-4" runat="server" visible='<%# ConfigurationManager.AppSettings["CountryCode"] == "IN" %>'>
                            <div class="form-group">
                                <label class="form-control-label w-100">CESS: </label>
                                <asp:TextBox ID="txtCess" runat="server" CssClass="form-control"></asp:TextBox>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn">
                        <asp:Button ID="btnHSNVerify" runat="server" CssClass="btn btn-primary" Text="Submit" stitId='<%# Eval("stit_ID") %>' OnClick="btnHSNVerify_Click" ValidationGroup="VerifyHSN"/>
                    </div>
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
            
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


<div class="modal fade" id="ProductDetailesPopup" tabindex="-1" role="dialog" aria-labelledby="ProductDetailesPopupTitle" aria-hidden="true">
  <div class="modal-dialog w-100 " role="document">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <div class="row row-sm">
          <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
          <asp:Image runat="server" ID="imgproduct" onerror="this.src='/content/images/image_on_error.svg'" CssClass="img-fluid"/>
<%--            <img src="" onerror="this.src='index_files/image_on_error.svg'" class="img-fluid" title="" alt="">--%>
          </div>
          <div class="col-12 col-md-7 d-flex flex-wrap align-content-center pt-4">
            <div class="productTitle w-100 mb-2">
              <h4 class="ProdectName tx-18 tx-dark"><asp:Label runat="server" ID="lblproduct"></asp:Label></h4>
              <p class="subcategory mb-0">Category: <strong><asp:Label runat="server" ID="lbcategory"></asp:Label></strong></p>
              <p class="subcategory mb-0">Brand:  <strong><asp:Label runat="server" ID="lbbrand"></asp:Label></strong></p>
            </div>
            <span class="DisPrice_unitprice w-100 mb-3">
              <span class="productDisPrice tx-dark tx-18"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %> <asp:Label runat="server" ID="lbmrp"></asp:Label></span>
            </span>
            <div class="shotdiscrip">
              <h6 class="mb-1">Short Description</h6>
              <p class="mb-0"><asp:Label ID="lbDescription" runat="server"></asp:Label></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalpacking" tabindex="-1" role="dialog" aria-labelledby="modaldemo4Label" aria-hidden="true">
  <div class="modal-dialog w-100" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modaldemo4Label">Manage Packing</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">         
          <div class="form-check p-0">
              <asp:RadioButton runat="server" ID="chkpackindividual" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrdeliverycost" runat="server"></asp:Label>
          </div>
          <div class="form-check p-0">
              <asp:RadioButton runat="server" ID="chkpackgroup" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrmannual" runat="server"></asp:Label>
          </div>
           <div class="form-check p-0">
              <asp:RadioButton runat="server" ID="chkdefault" CssClass="mr-2" GroupName="grpdelivery"/><asp:Label ID="ltrcanel" runat="server"></asp:Label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <asp:LinkButton runat="server" ID="btnshowpopup" OnClick="btnshowpopup_Click"  Text="Confirm" CssClass="btn btn-primary"></asp:LinkButton>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="Popupattribute" data-backdrop="static">
        <div class="modal-dialog w-100 modal-dialog-scrollable">
            <div class="modal-content modal-dialog-scrollable">
               <div class="modal-header">
                   <h5 class="modal-title" id="modaldemo5Label">Manage Attribute</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
                <div class="modal-body">                   
                     <div class="card-body p-3">
                       <div class="row row-sm">
                              <asp:Repeater runat="server" ID="rptattribute" DataSourceID="SDSattribute" OnItemDataBound="rptattribute_ItemDataBound" >
                     <ItemTemplate>
                         
                          <div class="form-group col-12 col-sm-6">
                              <label><asp:Literal runat="server" Text='<%# Eval("name")%>' ID="ltratrribute"></asp:Literal></label>
                              <asp:ListBox ID="selattributevalue" ClientIDMode="Static" SelectionMode="Multiple" runat="server"  
                                  CssClass="form-control select2" DataTextField="valueName" DataValueField="id"></asp:ListBox>
                                   <asp:HiddenField ID="hfAttributeId" runat="server" Value='<%# Eval("attributeId") %>' />
                                 <asp:TextBox runat="server" ID="txtattribute" Visible='<%#((Eval("valueMode")).ToString() == "2" ? true : false) %>'  TextMode="SingleLine"></asp:TextBox>
                                   <asp:TextBox runat="server" ID="txtattibutevalue" Visible='<%#((Eval("valueMode")).ToString() == "3" ? true : false) %>'  TextMode="MultiLine"></asp:TextBox>

                          </div>
                     </ItemTemplate>                   
                 </asp:Repeater>
                                 <asp:HiddenField ID="hdnproductid" runat="server" />
                           <asp:HiddenField ID="HiddenSubCategoryId" runat="server" />
                        <asp:SqlDataSource runat="server" ID="SDSattribute" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                        SelectCommand=" SELECT asm.attributeId,NAME,valueMode,stitId,GROUP_CONCAT(am.attributeValueId) AS selectedValues FROM attributeSubcategoryMap asm 
                                        INNER JOIN attribute ON id = asm.attributeId AND attribute.status=1   INNER JOIN  `attributeValue` av ON av.`attributeId`= asm.`attributeId` 
                                        LEFT JOIN  attributeProductMap am ON am.attributeId=asm.attributeId AND stitId = @Productid WHERE subCategoryId = @subcategory_id 
                                        GROUP BY attributeId ORDER BY valueMode ASC;">   
                            <SelectParameters>
                                  <asp:ControlParameter ControlID="HiddenSubCategoryId" Name="subcategory_id" PropertyName="Value" />
<%--                                <asp:QueryStringParameter QueryStringField="product_category" Name="subcategory_id" />--%>
                                  <asp:ControlParameter ControlID="hdnproductid" Name="Productid" PropertyName="Value" />
                            </SelectParameters>
                     </asp:SqlDataSource>                
                           <div class="col-12 justify-content-end">
                           <asp:LinkButton runat="server" Text="Save" ID="btnattributesave" OnClick="btnattributesave_Click" CssClass="btn btn-primary mr-1"></asp:LinkButton>
<%--                           <asp:LinkButton runat="server" Text="cancel" ID="btncanceattribute" CssClass="btn btn-secondary"></asp:LinkButton>--%>
                               <a href="/Tenant/MyProducts"  class="btn btn-secondary">Cancel</a>
                           </div>
                       </div>                    
                   </div>
                </div>                
            </div>
        </div>
    </div>

<script type="text/javascript">
    // Utility function for validation group
    function validatorsIsValid(vg) {
        if (!vg) vg = '';
        if (typeof (Page_ClientValidate) === 'function') {
            Page_ClientValidate(vg);
        }
        return Page_IsValid;
    }

    var isNewPopup = true;

    // Runs once the document is ready
    $(document).ready(function () {
        // ERP Type change event to toggle store selection
        $('#<%= selERPType.ClientID %>').change(function () {
            if ($(this).val() === "1") {
                $('#storeSelectionRow').hide(); // Manufacturer Code
            } else {
                $('#storeSelectionRow').show(); // Store Code or empty
            }
        }).trigger('change'); // trigger initially

        // Radio button click events for store selection
        $('#<%= rbAllStores.ClientID %>').click(function () {
            if ($(this).is(':checked')) {
                $('#<%= rbSelectStore.ClientID %>').prop('checked', false);
                $('#<%= dvselectstore.ClientID %>').hide();
            }
        });

        $('#<%= rbSelectStore.ClientID %>').click(function () {
            if ($(this).is(':checked')) {
                $('#<%= rbAllStores.ClientID %>').prop('checked', false);
                $('#<%= dvselectstore.ClientID %>').show();
            }
        });
    });

    // Load store data into dropdown
    function loadSelectStoreData(store, branchname) {
        $('#<%= dvselectstore.ClientID %>').show();
        $('#<%= selBranch.ClientID %>').val(store).change();
        $('#<%= selBranch.ClientID %>').attr('title', branchname);
    }

    // Load ERP ID into modal
    function loadErpId(obj) {
        // Reset
        $('#<%= txtCode.ClientID %>').val("");
        $('#<%= selERPType.ClientID %>').val("");
        $('#<%= selBranch.ClientID %>').val("").attr('title', "");
        $('#<%= rbAllStores.ClientID %>').prop('checked', true);
        $('#<%= rbSelectStore.ClientID %>').prop('checked', false);
        $('#<%= dvselectstore.ClientID %>').hide();

        // Get attributes
        var erpid = $(obj).attr('erpid');
        var erptype = $(obj).attr('erptype');
        var stitid = $(obj).attr('stitid');
        var store = $(obj).attr('store');
        var storeGroup = $(obj).attr('storeGroup');
        var branchname = $(obj).attr('branchname');

        // Set field values
        $('#<%= hidERP_stitid.ClientID %>').val(stitid);
        $('#<%= txtCode.ClientID %>').val(erpid);
        if (erptype) $('#<%= selERPType.ClientID %>').val(erptype).trigger('change');

        // Store logic
        if (storeGroup && store !== "0" && branchname) {
            $('#<%= rbSelectStore.ClientID %>').prop('checked', true);
            $('#<%= rbAllStores.ClientID %>').prop('checked', false);
            loadSelectStoreData(store, branchname);
            isNewPopup = false;
        } else if (storeGroup && store === "0" && !branchname) {
            $('#<%= rbAllStores.ClientID %>').prop('checked', true);
            $('#<%= rbSelectStore.ClientID %>').prop('checked', false);
            $('#<%= dvselectstore.ClientID %>').hide();
            isNewPopup = false;
        } else {
            isNewPopup = true;
        }

        // Save button logic
        $('#<%= btnSetErpID.ClientID %>').off('click').on('click', function () {
            if (!validatorsIsValid('SetERPID')) return false;

            // Store values in attributes
            $(obj).attr('erpid', $('#<%= txtCode.ClientID %>').val());
            $(obj).attr('erptype', $('#<%= selERPType.ClientID %>').val());

            var selectedStore = $('#<%= rbSelectStore.ClientID %>').is(':checked') ? $('#<%= selBranch.ClientID %>').val() : "0";
            var selectedGroup = $('#<%= rbAllStores.ClientID %>').is(':checked') ? "1" : "";

            $(obj).attr('store', selectedStore);
            $(obj).attr('storeGroup', selectedGroup);

            // Close modal and refresh
            $('#modalSetErpId').modal('hide');
            window.location.reload();
        });

        // Show modal
        $('#modalSetErpId').modal('show');
    }

    // Load return days modal
    function loadReturnDays(obj) {
        var spotreturn = $(obj).attr('spotreturn');
        var returndays = $(obj).attr('returndays');
        var stitid = $(obj).attr('stitid');
        $('#<%= hidERP_stitid.ClientID %>').val(stitid);
        $('#<%= chkSpotReturn.ClientID%>').val(spotreturn);
        if (returndays)
            $('#<%= txtReturnDays.ClientID%>').val(returndays);
        if (spotreturn == 1)
            $('#<%= chkSpotReturn.ClientID%>').prop('checked', true);
            else
                $('#<%= chkSpotReturn.ClientID%>').prop('checked', false);
            $('#modalReturnDays').find('.modal-footer').removeClass('processing_loader');
            $('#<%= btnReturnDays.ClientID%>').on('click', function () {
                $(obj).attr('spotreturn', $('#<%= chkSpotReturn.ClientID%>').val());
                $(obj).attr('returndays', $('#<%= txtReturnDays.ClientID%>').val());
            });
        $('#modalReturnDays').modal('show');
    }

    <%--function loadVerify(obj) {
        var stitid = $(obj).attr('stitid');
        $('#<%= hidERP_stitid.ClientID %>').val(stitid);
        var hsn = $(obj).attr('hsncode');
        var tax = $(obj).attr('tax');
        $('#<%= selHSN.ClientID%>').val(hsn);
        $('#<%= selType.ClientID%>').val(tax);
        $('#<%= txtTax.ClientID%>').val(tax);
        $('#modalVerify').modal('show');
    }--%>

    function loadVerify(obj) {
        var stitid = $(obj).attr('stitid');
        var hsn = $(obj).attr('hsncode');
        var tax = $(obj).attr('tax');
        var taxValue = $(obj).attr('taxValue');
        // Always set stitid
        $('#<%= hidERP_stitid.ClientID %>').val(stitid);
        $('#<%= hdnHsn.ClientID %>').val(hsn);
        $('#<%= hdnTax.ClientID %>').val(tax);
        $('#<%= hdnCess.ClientID %>').val(tax);

        // Check if tax has value (means verified)
        if (taxValue != null && taxValue !== "") {
            $('#<%= txtHSNCode.ClientID %>').val(hsn); 
        $('#<%= txtTax.ClientID %>').val(tax);
    } else {
        // Unverified — clear fields
        $('#<%= txtHSNCode.ClientID %>').val("");
            $('#<%= txtTax.ClientID %>').val("");
        }

        $('#modalVerify').modal('show');
    }

</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#selERPType').change(function () {
            var selectedValue = $(this).val();

            if (selectedValue === "0") {
                // Store Code selected → Show store selection
                $('#storeSelectionRow').show();
            } else {
                // Manufacturer Code or empty → Hide store selection
                $('#storeSelectionRow').hide();
            }
        });

        // Trigger change on page load to set initial visibility
        $('#selERPType').trigger('change');
    });
</script>


<script>
    $(document).ready(function () {
        if ($('#<%= hdnModalOpen.ClientID %>').val() === '1') {
            $('#modalVerify').modal('show');
        }
    });
    $('#modalVerify').on('hidden.bs.modal', function () {
        $('#<%= hdnModalOpen.ClientID %>').val('0');
    });
    
</script>

<style>
    .select2-container.select2-container--open {
      z-index: 1050;
    }
    .slim-sticky-sidebar .slim-header {
    z-index: 1051;
    }
    .modal-body .form-control + .select2 + span[data-val="true"] {
        bottom: -13px;
        left: 0;
    }
    #dvselectstore {
    display: none;
    }
    #dvselectstore.show {
        display: block;
    }
    .hcn-verify-btn {
        color: white !important;
        border: none;
        padding: 2px 5px;
        margin-left: 8px;
        cursor: pointer;
        font-size: 11px;
        border-radius: 15px;
        line-height: 100%;
    }

    .hcn-verify-btn.disabled {
        pointer-events: none;
        opacity: 0.6;
        cursor: not-allowed;
    }
    .btn_vrfy {
        width:65px;
        display:inline-block;
    }

    #modalVerify .select2-container--default .select2-results__options {
    display: block !important;
    text-align: left !important;
    padding: 0 !important;
    margin: 0 !important;
}

#modalVerify .select2-container--default .select2-results__option {
    text-align: left !important;
    display: block !important;
    margin: 0 !important;
    padding: 6px 12px !important;
}
</style>

