<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SelectProductOld.aspx.cs" Inherits="RetalineProAgent.SelectProductOld" %>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Grozeo Login</title>
<link href="<%= RetalineProAgent.Service.Common.FavIcon %>" rel="shortcut icon" type="image/x-icon" />

  <!-- Google Font: Source Sans Pro -->
  <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> 

  <link rel="stylesheet" href="/content/css/slim.css">

  <link href="content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">


  <link rel="stylesheet" href="/Content/css/custom/custom.css">

  <!-- jQuery -->
          <script src="/content/lib/jquery/js/jquery.js"></script>
    <script src="/content/lib/bootstrap/js/bootstrap.js"></script>

<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>

</head>
<body class="hold-transition login-page">
    <form runat="server">
  <div class="page_header">
    <div class="grozeologo">
      <a href="#">
        <img src="/content/images/login/grozeo_logo.svg">
      </a>
    </div>
    <h4>Already a Grozeo Partner? <a href="#">Log in</a></h4>
  </div>
  <div class="login_sec_wrp d-flex flex-wrap select_prodect_page">

    <div class="login_img p-10">
      
      <div class="login_nfographic">
        <img src="/content/images/login/login_img_01.png"/>
      </div>
    </div>

  

    <div class="login-box p-0">
      

      <div class="card mx-wd-100p-force">
        <div class="card-body login-card-body">
          

          <div class="wizard_wrap p-3">
            <div class="wizard_fliter">

                              <div class="row row-sm align-items-center">
                <div class="col-lg-7 mg-b-10 mg-lg-b-0">
                  <h5 class="tx-dark m-0 float-left mr-2">Select Products from Brand Gallery</h5>
                  <button class="btn btn-drk-green m-0 wd-sm-auto-force px-4 btn-sm" data-toggle="modal" data-target="#create_new_product">Add Product</button>
                </div>
                <div class="col-lg-5 text-left text-lg-right mg-lg-b-0">
                    
                  <div class="input-group mg-0">
                      <asp:TextBox ID="txtSearchProduct" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                    <span class="input-group-btn">
                        <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 btn-drk-green tx-gray-600 d-flex align-items-center" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                    </span>
                  </div><!-- input-group -->
                    
                  </div>
                </div>
              </div> <!--row-->


              <div class="filter_expand_wrap navbar-expand-lg">
                <a class="navbar-brand d-lg-none tx-dark tx-14 mx-wd-100p mr-0" href="#">Filter by</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#filter_expand" aria-controls="filter_expand" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                  </span>
                </button>
  
                <div class="collapse navbar-collapse flex-wrap filter_expand" id="filter_expand">
                  <div class="row row-sm mt-3 mx-wd-auto wd-sm-100p">
                    <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                      <div class="form-group mb-0">
                        <label class="form-control-label m-0">Department</label>
                      <asp:DropDownList ID="selDepartment" OnSelectedIndexChanged="selDepartment_SelectedIndexChanged" AutoPostBack="true" runat="server" DataSourceID="SDSDepartments" DataTextField="parent_category" DataValueField="parent_category_id" AppendDataBoundItems="true" CssClass="form-control select2"><asp:ListItem Text="All Departments" Value="0"></asp:ListItem></asp:DropDownList>
                      </div>
                    </div><!--col-lg-3-->
      
                    <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                      <div class="form-group mb-0">
                        <label class="form-control-label m-0">Category</label>
                      <asp:DropDownList ID="selCategory" runat="server" AutoPostBack="true" DataSourceID="SDSCategory" OnDataBound="selCategory_DataBound" DataTextField="category_name" DataValueField="category_id" OnSelectedIndexChanged="Reload_Products" CssClass="form-control select2"><asp:ListItem Text="All Categories" Value="0"></asp:ListItem></asp:DropDownList>
                      </div>
                    </div><!--col-lg-3-->
      
                    <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                      <div class="form-group mb-0 ">
                        <label class="form-control-label m-0">Brand</label>
                      <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" OnSelectedIndexChanged="Reload_Products" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2"><asp:ListItem Text="All Brands" Value="0"></asp:ListItem></asp:DropDownList>
                      </div>
                    </div><!--col-lg-3-->      
                  </div> <!--row-->
      
                  <div class="row row-sm mt-3 wd-100p-force pagenation">
                    <div class="col-12 col-lg-8 d-flex flex-wrap">
                      <label class="mr-4 tx-dark wd-100p wd-sm-auto-force">Show</label>
                      <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbAllProducts" OnCheckedChanged="rbProducts_CheckedChanged" Checked="true" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <span>All Products</span>
                      </label>
      
                      <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <span>Products added</span>
                      </label>
      
                      <label class="rdiobox">
                          <asp:RadioButton ID="rbNotAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                        <span>Products not added</span>
                      </label>
                    </div> 
                      <div class="col-12 col-lg-4 text-left text-lg-right mg-lg-b-0">
                          <asp:Literal runat="server" ID="ltrPagingCurStart" Text=""></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPagingCurTotal" Text=""></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text=""></asp:Literal>
                  <div class="btn-group ml-2">
                      <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" PreviousPageText="<i class='fa fa-angle-left'></i>" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" NextPageText="<i class='fa fa-angle-right'></i>" ShowNextPageButton="true" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>
                      </div>
                  </div><!--row-->
    
                </div><!--filter_expand-->
  
              </div><!--filter_expand_wrap-->


            <div class="wizard_body">  
             <div class=" wizard-cont-wrap">

                 <div class="table-responsive mailbox-messages">
                      <div id="overlay" onclick="off()">
          <div class="w-100 d-flex justify-content-center align-items-center">
            <div class="spinner"></div>
          </div>
        </div>
<asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT bnd.brand_id, bnd.brand_name, bnd.manufacture_id, bnd.img_url FROM mypha_productbrands bnd  
     INNER JOIN finascop_stock_itemmaster i ON i.pdt_brand = bnd.brand_id      
     INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
     INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
     INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
     INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType
     INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id AND bbt.store_group_id=@storeId
    WHERE stit_status = 1 AND (@category <= 0 OR c.category_id = @category)
    AND (@department <= 0 OR pc.parent_category_id = @department) GROUP BY bnd.brand_id ORDER BY bnd.brand_name" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
    <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    </SelectParameters>
</asp:SqlDataSource>

<asp:ObjectDataSource ID="ODSCategoriesDirect" runat="server" TypeName="RetalineProAgent.Core.Services.APIService"
       SelectMethod="Categories" OnSelecting="OBJ_Selecting" >
        <SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:ObjectDataSource>
<asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" OnDataBound="lstProducts_DataBound" 
                   ItemPlaceholderID="plsProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered mg-b-0">
                                    <thead>
                                        <tr>
                                            <th>
<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
<i class="fa fa-square tx-white"></i>
                    </button>
                    <div class="dropdown-menu">
                        <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                      <asp:LinkButton ID="lbtnSelectAll" runat="server" OnClick="lbtnAll_Click" CssClass="dropdown-item" Text="Select all" OnClientClick="if(!confirm('Select all items listed?')) return false; else on();"></asp:LinkButton></asp:PlaceHolder>
                        <%--<asp:LinkButton ID="lbtnRemoveAll" runat="server" OnClick="lbtnAll_Click" CssClass="dropdown-item" Text="Remove all" OnClientClick="return confirm('Remove all items listed?')"></asp:LinkButton>--%>
                      <div class="dropdown-divider"></div>
                        <asp:LinkButton runat="server" CssClass="dropdown-item" Text="Refresh"></asp:LinkButton>
                    </div>

            <%--<asp:CheckBox ID="chkProductHItem" AutoPostBack="true" OnCheckedChanged="chkProductHItem_CheckedChanged1" runat="server" />--%></th>
        <th>Name</th>
        <th>Brand</th>
        <th>Sub Category
<div class="float-right">
                  
                  <div class="btn-group">

                      

                  </div>

                  <!-- /.btn-group -->
                </div>

                                            </th>
                                            <%--<th>MRP</th>--%>
											<%--<th>Stock</th>
											<th>Margin %</th>--%>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>

                                        <tr>
            <td colspan = "2">
                <asp:DataPager ID="DataPager1" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm" PreviousPageText="<" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NumericPagerField ButtonType="Link" NumericButtonCssClass="btn btn-default btn-sm" />
                        <asp:NextPreviousPagerField ButtonType="Link" NextPageText=">" ShowNextPageButton="true" ButtonCssClass="btn btn-default btn-sm" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>
            </td>
        </tr>

				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>

                            <tr class="<%# ( !IsSelected(Eval("stit_Id").ToString()) ? "" : (Convert.ToInt32(Eval("instock")) > 0 ? "already_added" : "checked_now" )) %>">
                                <td><asp:CheckBox ID="chkProductItem" onclick="updateSelection(this);" OnCheckedChanged="chkProductItem_CheckedChanged" itemmrp='<%# Eval("stit_MRP") %>' itemid='<%# Eval("stit_Id") %>' erpid='<%# Eval("stit_HSNCode") %>' Checked='<%# IsSelected(Eval("stit_Id").ToString()) %>' runat="server" /></td>
						        <td><asp:Image Width="30" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    <asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal> 
                                    <%--<br /><small>Category: <b><%# Eval("stit_category_name") %></b>, Brand: <b><%# Eval("stit_brand_name") %></b>, By: <b><%# Eval("med_manufacturename") %></b></small>--%>
						        </td>
						        <%--<td><asp:Literal ID="ltrProductItemMrp" runat="server" ></asp:Literal></td>--%>
						        <%--<td><asp:TextBox ID="txtPStock" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
						        <td> <asp:TextBox ID="txtPCustomMargine" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>--%>
                                <td><%# Eval("stit_brand_name") %></td>
                                <td><%# Eval("stit_category_name") %></td>
					        </tr>
                    

                    
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>				
                <!-- /.table -->
              </div>

                  </div>
            </div>


            </div>
        </div><!--wizard_wrap-->
                      <label for="txtSearchProduct" visible="false" runat="server"></label>
                      <label for="txtDateFrom" visible="false" runat="server"></label>

                  <asp:Literal ID="ltrItemFilterName" runat="server"></asp:Literal>
                <asp:HiddenField ID="hidProductPager" Value="1" runat="server" />

        </div>
        
        <div class="d-sm-flex p-3 wiz_btnsect justify-content-center floting_btn_sec">
         <asp:LinkButton runat="server" CssClass="btn btn-primary btn-drk-green disabled btn-block mx-2 wd-sm-auto-force px-4" OnClick="btnSaveProducts_Click" Text="Save Products" novalidate></asp:LinkButton>
         <asp:HyperLink NavigateUrl="/" CssClass="btn btn-primary btn-drk-green btn-block m-0 mx-2 wd-sm-auto-force px-4" ID="hlSaveProductsMoveNext" runat="server" Text="Next"></asp:HyperLink>
        </div>
      </div><!--card-->

      <div class="copyright">© <a href="https://grozeo.com">grozeo.com</a></div>





    </div>
    <!-- /.login-box -->
<asp:SqlDataSource ID="SDSProducts" runat="server" OnSelected="SDSProducts_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  OnSelecting="SDSBrands_Selecting"
 SelectCommand="select * from( 
        SELECT stit_Id, stit_itemId, stit_itemERPId, stit_SKU, stit_HSNCode, stit_MRP, stit_brand_name, stit_category_name, med_manufacturename,  
        (SELECT image_url FROM finascop_stock_item_images WHERE product_id= i.stit_ID LIMIT 1) AS imageurl, 
           CASE WHEN EXISTS(SELECT * FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE bi.stit_id = i.stit_Id AND b.br_storeGroup=@storeId) THEN 1 ELSE 0 END AS instock     
        FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
         INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
         INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType 
         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id AND bbt.store_group_id=@storeId
        WHERE stit_status = 1 AND  (@brand <= 0 OR pdt_brand = @brand) AND (@category <= 0 OR c.category_id = @category) AND (@department <= 0 OR pc.parent_category_id = @department)
         GROUP BY stit_Id
 ) myProducts where @type = 0 or (@type = 1 and  myProducts.instock > 0) or (@type = 2 and  myProducts.instock <= 0)  ORDER BY stit_SKU" ProviderName="MySql.Data.MySqlClient">

<SelectParameters>
    <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selBrand" Name="brand" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
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
                      <label class="form-control-label">Brand: <span class="tx-danger">*</span></label>
                      <asp:DropDownList ID="selBrd" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT brand_id,brand_name FROM mypha_productbrands">
                        <%--<SelectParameters>
            <asp:ControlParameter Name="catName" ControlID="selCat" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
        </SelectParameters>--%>
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
                      <asp:DropDownList ID="selUnit" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
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
                      <label class="form-control-label">GST / VAT %: <span class="tx-danger">*</span></label>
                      <asp:TextBox ID="txtGSTVAT" runat="server" ReadOnly="true" CssClass="form-control"/>
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
                      <label class="form-control-label">Short Description <span class="tx-danger">*</span></label>
                      <asp:TextBox ID="TextBox1" runat="server" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
                    </div>
                  </div><!-- col-4 -->

                  

                    <div class="col-lg-8">
                    <div class="form-group m-0">
                      <label class="form-control-label">Long Description</label>
                      <div id="summernote2"> 
                          <asp:TextBox ID="summernote" runat="server" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
                      </div>
                    </div>
                  </div><!-- col-4 -->
                    </div><!-- row -->
                <div class="form-layout-footer">
                    <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                    <button type="button" class="btn btn-secondary bd-0" data-dismiss="modal">Cancel</button>
                    <%--<a href="/InventoryMapping" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>--%>
                                      <div class="error_msg_wrap mt-2 mb-1 ht-20"><asp:Literal ID="ltrResult" runat="server"></asp:Literal></div>
                </div>
              </div><!-- form-layout -->
            </div>

          </div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
    
      <asp:HiddenField ID="hidShowAddForm" Value="0" runat="server" />




  </div><!--login_sec_wrp-->


  </form>

  




<script type="text/javascript">


    $(document).ready(function () {


        $(function () {

            // Summernote editor
            $('#summernote').summernote({
                height: 197,

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
</script>
</body>
</html>
