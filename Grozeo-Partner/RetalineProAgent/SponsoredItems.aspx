<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SponsoredItems.aspx.cs" Inherits="RetalineProAgent.SponsoredItems" %>

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
    <link rel="stylesheet" href="/Content/css/toastify.css" />
        <link href="/content/lib/Ionicons/css/ionicons.css" rel="stylesheet">

  <!-- jQuery -->
          <script src="/content/lib/jquery/js/jquery.js"></script>
    <script src="/content/lib/popper.js/js/popper.js"></script>
    <script src="/content/lib/bootstrap/js/bootstrap.js"></script>

<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
<script src="/content/js/toastify.js"></script>
</head>
<body class="hold-transition login-page">
    <form id="form1" runat="server">

  <div class="page_header">
    <div class="grozeologo">
      <a href="/">
        <img src="/content/images/login/grozeo_logo.svg">
      </a>
    </div>
    <%--<h4>Already a Grozeo Partner? <a href="/login">Log in</a></h4>--%>
  </div>
  <div class="login_sec_wrp d-flex flex-wrap select_prodect_page">

    <div class="login_img p-10">
      
      <div class="login_nfographic">
        <img src="/content/images/login/chequeredflag.svg"/>
      </div>
    </div>

  

    <div class="login-box ">
      

      <div class="card mx-wd-100p-force">
        <div class="card-body login-card-body">
          

          <div class="wizard_wrap p-0">
            <nav class="tabnavwrap">
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link" id="nav-myproducts-tab">My Products</a>
                <a class="nav-item nav-link active" id="nav-sponsored-tab" data-toggle="tab" href="#nav-sponsored" role="tab" aria-controls="nav-sponsored" aria-selected="true">Sponsored Products</a>
              </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">

                <asp:PlaceHolder ID="plcSponsoredProducts" runat="server">
<div class="tab-pane fade show active" id="nav-sponsored" role="tabpanel" aria-labelledby="nav-sponsored-tab">
                
                <div class="tabcontent_wrap">


                  <div class="sponseritems_wrap mt-1">

                    <div class="wizard_fliter">
                                    
                      <div class="row row-sm align-items-end">
                        <div class="col-lg-6 mg-b-10 mg-lg-b-0">
                          <label class="section-title d-inline-block mb-0 w-auto">Sponsored Products</label>
                          <p class="m-0 section-title-p">Sponsored products gives you opportunity to earn attractive promotional revenue.</p>
                        </div>

                        <div class="col-lg-6 text-left  mg-lg-b-0 d-flex align-items-end">
                          
                          <div class="form-group mb-0 wd-100p-force mt-2">
                      <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2"><asp:ListItem Text="All Brands" Value="0"></asp:ListItem></asp:DropDownList>
                          </div>


                          <button class="btn btn-primary ml-2 bg-drk-green " type="button" onclick="$('#<%= hidSelectItemsFilter.ClientID %>').val(($('#<%= hidSelectItemsFilter.ClientID %>').val() == '0'? '1': '0')); " data-toggle="collapse" data-target="#collapseSP" aria-expanded="false" aria-controls="collapseSP">
                            Filter <i class="filter_arrow"></i> 
                          </button>

                        </div>

                      </div>
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

                            <div class="input-group mg-0">
                                <asp:TextBox ID="txtSponsoredProductName" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                              <span class="input-group-btn">
                                  <asp:LinkButton runat="server" CssClass="btn bd bd-l-0 btn-drk-green tx-gray-600 d-flex align-items-center" ><i class="fa fa-search"></i></asp:LinkButton>
                              </span>
                            </div><!-- input-group -->
                          </div><!--col-lg-4-->


                        </div><!--row-->

                      </div><!--collapse-->

                    </div><!--wizard_fliter-->  
  
  
                    <asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" 
                    ItemPlaceholderID="plsSpProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered  mg-b-0 mt-3">
                                    <thead>
                                        <tr>
        <th></th>
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
									
				<asp:PlaceHolder ID="plsSpProducts" runat="server"></asp:PlaceHolder>

                                        <tr>
            <td colspan = "4"><div  class="">
                <asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm" PreviousPageText="<" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NumericPagerField ButtonType="Link" NumericButtonCssClass="btn btn-default btn-sm" />
                        <asp:NextPreviousPagerField ButtonType="Link" NextPageText=">" ShowNextPageButton="true" ButtonCssClass="btn btn-default btn-sm" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager></div>
            </td>
        </tr>

				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>

                            <tr>
                                <td width="50px"><span data-toggle="modal" data-target="#modelalert" style="cursor:pointer;"><i class="ion-checkmark-circled"></i></span></td>
						        <td><asp:Image Width="30" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    <asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal> 
						        </td>
                                <td><%# Eval("stit_brand_name") %></td>
                                <td><%# Eval("stit_category_name") %></td>
					        </tr>
                    

                    
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>


  
                  </div>
                  <div class="d-sm-flex p-3 wiz_btnsect justify-content-center floting_btn_sec">
                    <%--<asp:LinkButton ID="LinkButton1" runat="server" Text="Next" CssClass="btn btn-primary btn-drk-green btn-block m-0 mx-2 wd-sm-auto-force px-4" formnovalidate></asp:LinkButton>--%>
                    <asp:HyperLink ID="hlNextSelectItems" runat="server" NavigateUrl="/SelectProduct" onclick="$(this).closest('div').addClass('processing_loader')" Text="Back" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4"></asp:HyperLink>
                      <asp:LinkButton ID="lbtnConfirmSponsored" runat="server" Text="Next" CssClass="btn btn-primary btn-drk-green btn-block m-0 mx-2 wd-sm-auto-force px-4" OnClick="lbtnConfirmSponsored_Click"></asp:LinkButton>
              
                  </div>

                </div>

              </div>
                </asp:PlaceHolder>

            </div> <!--tab-content-->
            
          </div><!--wizard_wrap-->
        </div>
      </div><!--card-->

      <div class="copyright">© <a href="https://grozeo.com">grozeo.com</a></div>
    </div>
    <!-- /.login-box -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modelalert" class="modal fade modelalert">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <p class="mg-b-20 mg-x-20">You can not deselect the sponsored products being a free package user. Please upgrade to our paid plan to manage the Sponsored Products</p>
            <button type="button" class="btn d-inline-block btn-drk-green pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

  </div><!--login_sec_wrp-->



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
    WHERE stit_status = 1  AND issponsered = 1 AND  (@brand <= 0 OR pdt_brand = @brand) 
         GROUP BY i.stit_id ORDER BY i.stit_SKU " ProviderName="MySql.Data.MySqlClient">

<SelectParameters>
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




  </form>

</body>
</html>


