<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SelectProduct.aspx.cs" ValidateRequest="false" EnableViewState="false" Inherits="RetalineProAgent.SelectProduct" %>

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
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
  <link rel="stylesheet" href="/Content/css/custom/custom.css">
    <link rel="stylesheet" href="/Content/css/toastify.css" />
        <link href="/content/lib/Ionicons/css/ionicons.css" rel="stylesheet">

    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
  <!-- jQuery -->
          <script src="/content/lib/jquery/js/jquery.js"></script>
    <script src="/content/lib/popper.js/js/popper.js"></script>
    <script src="/content/lib/bootstrap/js/bootstrap.js"></script>

<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
<script src="/content/js/toastify.js"></script>
     <script src="/content/lib/select2/js/select2.full.min.js"></script>
<asp:PlaceHolder ID="plsHeaderPostcoder" Visible="false" runat="server">
    <script>
        (function (w, t, c, p, s, e) {
            p = new Promise(function (r) {
                w[c] = {
                    client: function () {
                        if (!s) {
                            s = document.createElement(t); s.src = 'https://js.cobrowse.io/CobrowseIO.js'; s.async = 1;
                            e = document.getElementsByTagName(t)[0]; e.parentNode.insertBefore(s, e); s.onload = function () { r(w[c]); };
                        } return p;
                    }
                };
            });
        })(window, 'script', 'CobrowseIO');
        CobrowseIO.license = "<%= ConfigurationManager.AppSettings.Get("CoBrowserkey") %>";
        CobrowseIO.client().then(function () {
            CobrowseIO.start();
        });
</script>
</asp:PlaceHolder>

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

<div id="dvpopupselectbrand" class="startadding_wrap startadding_popupview invisible">
                           <div class="closepopup invisible" onclick="$(this).closest('div.startadding_popupview').addClass('invisible');">
                            <i class="ion-android-close"></i>
                          </div> 
                          <h4 class="text-center mt-2 ">Add more products to your store from our <strong>Brand
                              Gallery</strong></h4>
                          <div class="inputselectwrap mt-3 mb-3">
                          <asp:DropDownList ID="selPopupBrands" runat="server" AutoPostBack="true" DataSourceID="SDSBrands" OnSelectedIndexChanged="selPopupBrands_SelectedIndexChanged" DataTextField="brand_name" DataValueField="brand_id" AppendDataBoundItems="true" ValidationGroup="SelectFromProdGallery"><asp:ListItem Value="" Text="Select a BRAND to proceed"></asp:ListItem></asp:DropDownList>
                          </div>
                          <span class="d-flex align-items-center justify-content-center mb-3">OR</span>
                          <div class="crtnewprodtbtn d-flex justify-content-center">

                                   <asp:Button runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" OnClick="btnSelectAddProduct_Click" Text="Create a New Product" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4 py-2" formnovalidate />
                          </div>
                        </div>
<script type="text/javascript">
    function selprodchangebranch() {
        if (!($('#btnSaveSelectedProducts').hasClass('disabled')) && $('#<%= hidSelectedItems.ClientID %>').val() != '' && $('#tblselectproduct').find('tr span.chkselectitem input[type="checkbox"]:checked').length > 0) {
            alert('Please save the selection and try again.');
        }
        else {
            $('#dvpopupselectbrand').removeClass('invisible'); $('#dvpopupselectbrand').find('div.closepopup').removeClass('invisible');
        }
    }

</script>

<asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT bnd.brand_id, bnd.brand_name, bnd.manufacture_id, bnd.img_url FROM mypha_productbrands bnd  
     INNER JOIN finascop_stock_itemmaster i ON i.pdt_brand = bnd.brand_id  and ifnull(i.stit_StoreGroup, 0) <= 0 
     INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
     INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType
     INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id AND bbt.store_group_id=@storeId 
    WHERE stit_status = 1 AND EXISTS (SELECT * FROM item_mrp WHERE stit_id=i.stit_ID AND itemMrp > 0) GROUP BY bnd.brand_id ORDER BY bnd.brand_name" 
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storeId" DefaultValue="0" />
    </SelectParameters>
</asp:SqlDataSource>
                <asp:PlaceHolder ID="plcMyProducts" runat="server">
            <nav class="tabnavwrap">
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link <%= hidCurTab.Value != "3" ? "active":"" %>" id="nav-myproducts-tab" data-toggle="tab" href="#nav-myproducts" role="tab" aria-controls="nav-selectproducts" aria-selected="true">My Products</a>
                <%--<a class="nav-item nav-link <%= hidCurTab.Value == "1"? "active":"" %>" id="nav-selectproducts-tab" data-toggle="tab" href="#nav-selectproducts" role="tab" aria-controls="nav-selectproducts" aria-selected="true">Brand Gallery</a>
                <a class="nav-item nav-link <%= hidCurTab.Value == "2"? "active":"" %>" id="nav-addproducts-tab" data-toggle="tab" href="#nav-addproducts" role="tab" aria-controls="nav-addproducts" aria-selected="false">custom Products</a>--%>
                <a class="nav-item nav-link <%= hidCurTab.Value == "3"? "active":"" %>" id="nav-sponsored-tab" >Sponsored Products</a>
              </div>
            </nav>


<div class="tab-content" id="nav-tabContent">
<div class="tab-pane fade <%= String.IsNullOrEmpty(hidCurTab.Value) || hidCurTab.Value == "0"? "show active":"" %>" id="nav-myproducts" role="tabpanel" aria-labelledby="nav-myproducts-tab">
                  <div class="tabcontent_wrap">

                  <div class="wizard_fliter">

                    <div class="row row-sm align-items-end">
                        <div class="col-lg-6 mg-b-10 mg-lg-b-0">
                          <%--<label class="section-title d-inline-block mb-0 w-auto">My Products</label>--%>
                            <p class="m-0 mt-4 section-title-p">You have <strong><asp:Literal ID="ltrMyProductsTotal" runat="server" Text="0"></asp:Literal></strong> products added to your online store</p>

                        </div>
                        <%--<div class="col-lg-6 text-left text-lg-right mg-lg-b-0">
                            <div class="input-group mg-0">
                                <asp:TextBox ID="txtSelectedProductName" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                            <span class="input-group-btn"><asp:LinkButton runat="server" CssClass="btn bd bd-l-0 btn-drk-green tx-gray-600 d-flex align-items-center"><i class="fa fa-search"></i></asp:LinkButton></span>
                            </div><!-- input-group -->

                        </div>--%>
                    </div>
                    </div> <!--row-->

                    <div class="wizard_body mt-3">  
                      <div class=" wizard-cont-wrap">

                        <div class="table-responsive">

                            <asp:ListView ID="lstSelectedProducts" runat="server" DataSourceID="SDSSelectedProducts" 
                    ItemPlaceholderID="plsSelectedProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered mg-b-0" id="tblSelectedProducts">
                         <thead>
                              <tr>
                                <th width="5%"><%--<span><input id="allchecked" type="checkbox" name="allchecked" checked=""></span>--%></th>
                                <th width="58%">Product</th>
                                <th width="12%">MRP/RRP</th>
                                <th width="12%">Selling Price</th>
                                <th width="12%">Quantity </th>                    
                              </tr>
                            </thead>
                                    <tbody>									
				<asp:PlaceHolder ID="plsSelectedProducts" runat="server"></asp:PlaceHolder>
        <tr>
            <td colspan = "5"><div  class="">
                <asp:DataPager ID="DataPager3" runat="server" PagedControlID="lstSelectedProducts" PageSize="20">
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
                            <tr class="trselecteditem">
                                <td><div>
                                      <asp:LinkButton ID="lbDelItem" runat="server" OnClientClick="if(confirm('Are you sure you want to remove this item from your store/s?')) $(this).closest('form').attr('childobj', this.id); else return false; " OnClick="DeleteItem_Click" itemid='<%# Eval("stit_id") %>' ForeColor="#3C3C3C"><i class="ion-ios-close"></i></asp:LinkButton>
                                </div></td>
						        <td><asp:Image Width="30" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    <asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal> 
						        </td>
                                <td align="right">
                                    <%--<label><%# Eval("mrp") %></label>--%>
                                    <asp:Label runat="server" ID="lblMrp" Text='<%# Eval("mrp")%>'></asp:Label>
                                </td>
                                <td align="right"><asp:TextBox ID="txtSelectedProductSellingPrice" CssClass="wd-100-force selected_selling morethan0 selectedchangeevent text-right" onfocus="this.select()" Text='<%# Eval("selling_price") %>' required runat="server"></asp:TextBox></td>
                                <td align="right"><asp:TextBox ID="txtSelectedProductQty" CssClass="wd-100-force selected_qty morethan0 selectedchangeevent text-right" onfocus="this.select()" Text='<%# Eval("item_count") %>' required runat="server"></asp:TextBox></td>
					        </tr>
                </ItemTemplate>
<EmptyDataTemplate>

                        <!--startadding_wrap-->
                          <table class="table table-bordered mg-b-0 al_pv_none" id="">
                            <thead>
                              <tr>
                                <th width="5%">
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="0" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </th>
                                <th width="58%">Product</th>
                                <th width="12%">MRP/RRP</th>
                                <th width="12%">Selling Price</th>
                                <th width="12%">Quantity </th>
                              </tr>
                            </thead>
                            <tbody>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="1" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="2" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="3" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="4" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="5" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="6" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>

                              <tr>
                                <td>
                                  <span class="chkselectitem" itemmrp="" itemid="" erpid="">
                                    <input id="7" type="checkbox" name="" disabled onclick="">
                                  </span>
                                </td>
                                <td><div class="wireframe"></div></td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX . xx</td>
                                <td align="right" class="opacity-50">XXX</td>
                              </tr>
                            </tbody>
                          </table>


                        <div class="myproduct_alertmsg_cont"></div> 

    <script type="text/javascript">
        $(document).ready(function () { $('#dvrowselectedproducts').attr("style", "display: none !important"); $('#dvpopupselectbrand').removeClass('invisible'); if (!($('#dvpopupselectbrand').find('div.closepopup').hasClass('invisible'))) $('#dvpopupselectbrand').find('div.closepopup').addClass('invisible'); });
    </script>

</EmptyDataTemplate>

                            </asp:ListView>
<asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ID="SDSSelectedProducts" runat="server" OnSelecting="SDSBrands_Selecting" OnSelected="SDSSelectedProducts_Selected"
 SelectCommand="SELECT i.stit_Id, i.stit_itemId, i.stit_itemERPId, i.stit_SKU, i.stit_HSNCode, bi.mrp, i.stit_brand_name, bi.selling_price, i.stit_category_name, i.med_manufacturename,
        (SELECT image_url FROM finascop_stock_item_images WHERE product_id= i.stit_ID LIMIT 1) AS imageurl, bi.item_count, (case when ifnull(selling_price, 0) <= 0 and ifnull(item_count, 0) <= 0 then 3 when ifnull(selling_price, 0) <= 0 then 2 when ifnull(item_count, 0) <= 0 then 1 else 0 end) as editorder
        FROM finascop_stock_itemmaster i  INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id=i.stit_id
        INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup = @storeId GROUP BY stit_Id ORDER BY editorder desc " ProviderName="MySql.Data.MySqlClient">
<SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" /></SelectParameters>
</asp:SqlDataSource>                    
                          <!-- /.table -->
                        </div>

                      </div>
                    </div>

                  <div class="d-sm-flex p-3 wiz_btnsect justify-content-center floting_btn_sec" id="dvrowselectedproducts">
                      <asp:Button ID="btnSelectProduct" OnClick="btnSelectProduct_Click" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id); selprodchangebranch(); return false;" Text="Add More Products" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4" formnovalidate />
                    <asp:Button ID="btnSaveSelectedProducts" ClientIDMode="Static" OnClientClick="return validateSelectedProducts(this, true)" OnClick="btnSaveSelectedProducts_Click" runat="server" Text="Save" CssClass="btn btn-primary btn-drk-green disabled btn-block mx-2 wd-sm-auto-force px-4" formnovalidate/>
                    <asp:HyperLink ID="hlNextSelectedProduct" runat="server" NavigateUrl="/SponsoredItems" onclick="$(this).closest('div').addClass('processing_loader');" Text="Next" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4"></asp:HyperLink>
                    <%--<asp:Button ID="btnNextSelectedProduct" OnClientClick="return validateSelectedProducts(this)" OnClick="btnNextSelectedProduct_Click" runat="server" Text="Next" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4" formnovalidate/>--%>
                  </div>

                  </div>
              </div>

</div> <!--tab-content-->
                </asp:PlaceHolder>

            


                <asp:PlaceHolder ID="plcSelectProduct" runat="server">
                    <div class="Products_available_sec wizard_body mt-3 mb-2"><asp:HiddenField ID="hidPopupSelectedBrand" runat="server" />
                        <h4 class="border-bottom m-0 pb-2">Products available in brand - <asp:Literal runat="server" ID="ltrSelectedBrand"></asp:Literal> <a class="ml-2" href="javascript:void(0)" onclick="selprodchangebranch()">change brand</a></h4>

                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                      { %>
                        <div class="col-lg-12" style="text-align: right; width: 100%;">You have <strong><asp:Literal ID="ltrMyProductsTotal2" runat="server" Text="0"></asp:Literal></strong> products added to your online store</div>
                  <% } %>
                        <div class="brandproductwrap  wizard-cont-wrap">
<div class="table-responsive">
                    
<asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" OnDataBound="lstProducts_DataBound" 
                   ItemPlaceholderID="plsProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered mg-b-0" id="tblselectproduct">
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
        <input type="checkbox" onclick="checkall(this)" />
            <%--<asp:CheckBox ID="chkProductHItem" AutoPostBack="true" OnCheckedChanged="chkProductHItem_CheckedChanged1" runat="server" />--%></th>
                
<th width="50%">Product</th>
                    <th width="10%">MRP/RRP</th>
                    <th width="18%">Brand</th>
                    <th width="16%">Category</th>
                    <%--<th width="16%">Sub Category</th>--%>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>

                                        <tr>
            <td colspan = "5">
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

                            <tr class="<%# (IsSelected(Eval("stit_Id").ToString()) ? (Convert.ToInt32(Eval("instock")) > 0 ? "already_added" : "checked_now" ) : "") %>">
                                <td><%--<asp:CheckBox ID="chkProductItem" CssClass="chkselectitem" onclick="updateSelection(this);" itemmrp='<%# Eval("itemMSRP") %>' itemid='<%# Eval("stit_Id") %>' erpid='<%# Eval("stit_HSNCode") %>' Checked="false" runat="server" />--%>
                                    <span class="chkselectitem" itemmrp="<%# Eval("itemMSRP") %>" itemid="<%# Eval("stit_Id") %>" erpid="<%# Eval("stit_HSNCode") %>"><input type="checkbox" onclick="updateSelection(this);" <%# (IsSelected(Eval("stit_Id").ToString())? "checked" : "" )  %> ></span>
                                </td>
						        <td><asp:Image Width="30" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                    <asp:Literal ID="ltrProductItemDesc" runat="server" Text='<%# Eval("stit_SKU")%>'></asp:Literal> 
                                    <%--<br /><small>Category: <b><%# Eval("stit_category_name") %></b>, Brand: <b><%# Eval("stit_brand_name") %></b>, By: <b><%# Eval("med_manufacturename") %></b></small>--%>
						        </td>
						        <td align="right"><%--<asp:Literal ID="ltrProductItemMrp" runat="server" ></asp:Literal>--%>
                                    <label class="labelamout"><%# Eval("itemMSRP") %></label>
                      <%--<input required="" class type="text" value="">--%>
                                    <asp:TextBox ID="txtMRP" TextMode="Number" runat="server" itemid='<%# Eval("stit_Id") %>' CssClass="mrpinput editamout text-right" Text='<%# Eval("itemMSRP") %>' onfocus="this.select()" onchange="if($(this).val() == '' || $(this).val() <=0) $(this).data('title', 'Value should be greater than 0').addClass('error'); else $(this).removeClass('error').tooltip('dispose');"></asp:TextBox>
						        </td>
						        <%--<td><asp:TextBox ID="txtPStock" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
						        <td> <asp:TextBox ID="txtPCustomMargine" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>--%>
                                <td><%# Eval("stit_brand_name") %></td>
                                <td><%# Eval("stit_category_name") %></td>
					        </tr>
                    

                    
                </ItemTemplate>
    <EmptyDataTemplate><br /><br />
        No product available in the brand selected or under the business type you have chosen. Please select another brand and try again or,
        <br /><br />you can create your own products using the "Create Product" button below.</EmptyDataTemplate>
            </asp:ListView>

                    <!-- /.table -->
                    </div>

                        </div>

<div class="d-sm-flex p-3 wiz_btnsect justify-content-center floting_btn_sec">

                    <asp:Button ID="btnSaveSelectedItems" runat="server" OnClick="btnSaveProducts_Click" Text="Save" OnClientClick="return validateselectitems(false)" CssClass="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4" formnovalidate />
     <asp:Button ID="btnSelectAddProduct" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" OnClick="btnSelectAddProduct_Click" Text="Create Product" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4" formnovalidate />

                    <%--<button class="btn btn-primary btn-drk-green <%= (this.CurrentUser.TenantStage == 6 ? "disabled" : "") %> btn-block mt-0 mx-2 wd-sm-auto-force px-4" data-toggle="modal" data-target="#price_qunty_alert">Next</button>--%>
                    <%--<asp:Button ID="btnNextSelectItems" OnClick="btnBrandGalleryNext_Click" runat="server" OnClientClick="$(this).closest('form').attr('childobj', this.id);" Text="Back" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4" formnovalidate />--%>
                    <asp:HyperLink ID="hlNextSelectItems" runat="server" NavigateUrl="/SelectProduct" onclick="$(this).closest('div').addClass('processing_loader')" Text="Back" CssClass="btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4"></asp:HyperLink>
                </div>
                    </div>


<div runat="server" visible="false" class="tab-pane fade" role="tabpanel" aria-labelledby="nav-selectproducts-tab">
                
            <div class="tabcontent_wrap">

                  <div class="wizard_fliter">

                    <div class="row row-sm align-items-end">
                        <div class="col-lg-6 mg-b-10 mg-lg-b-0">
                          <label class="section-title d-inline-block mb-0 w-auto">Select Products for Your Store</label>
                            <p class="m-0 section-title-p">Select Products available in our Brand Gallery to add those to your store</p>
                        </div>
                                              <div class="col-lg-6 text-left  mg-lg-b-0 d-flex align-items-end">
                        
                        <div class="form-group mb-0 wd-100p-force mt-2">
                          <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" OnSelectedIndexChanged="Reload_Products" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2 selproductvalidate"></asp:DropDownList>
                        </div>

                        <button class="btn btn-primary ml-2 bg-drk-green " type="button" onclick="$('#<%= hidSelectItemsFilter.ClientID %>').val(($('#<%= hidSelectItemsFilter.ClientID %>').val() == '0'? '1': '0')); " data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                          Filter <i class="filter_arrow"></i> 
                        </button>
                
                      </div>

                    </div>
                      <asp:HiddenField ID="hidSelectItemsFilter" runat="server" Value="0" />
                      <div class="collapse mt-3 <%= (hidSelectItemsFilter.Value=="1" ? "show":"") %>" id="collapseExample">
                      <div class="row row-sm align-items-end">

                        <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">

                          <div class="form-group mb-0 ">

                            <asp:DropDownList ID="selDepartment" OnSelectedIndexChanged="selDepartment_SelectedIndexChanged" runat="server" DataSourceID="SDSDepartments" DataTextField="parent_category" DataValueField="parent_category_id" AppendDataBoundItems="true" CssClass="form-control select2 selproductvalidate"><asp:ListItem Text="All Departments" Value="0"></asp:ListItem></asp:DropDownList>
                          </div>
                        </div><!--col-lg-4-->

                        <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                          <div class="form-group mb-0">
                            <asp:DropDownList ID="selCategory" runat="server" DataSourceID="SDSCategory" OnDataBound="selCategory_DataBound" DataTextField="category_name" DataValueField="category_id" OnSelectedIndexChanged="Reload_Products" CssClass="form-control select2 selproductvalidate"><asp:ListItem Text="All Categories" Value="0"></asp:ListItem></asp:DropDownList>
                          </div>
                        </div><!--col-lg-4-->


                        <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">

                          <div class="input-group mg-0">
                            <asp:TextBox ID="txtSelectProductName" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                            <span class="input-group-btn"><asp:LinkButton ID="lbSelectedProductSearch" OnClientClick="return validateselectitems(true)" runat="server" CssClass="btn bd bd-l-0 btn-drk-green tx-gray-600 d-flex align-items-center"><i class="fa fa-search"></i></asp:LinkButton>
                            </span>
                          </div><!-- input-group -->
                        </div><!--col-lg-4-->


                      </div><!--row-->

                    </div><!--collapse-->


                    </div> <!--row-->

<asp:PlaceHolder runat="server" Visible="false">
                        <asp:RadioButton ID="rbAllProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                    <asp:RadioButton ID="rbAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" runat="server" />
                    <asp:RadioButton ID="rbNotAddedProducts" OnCheckedChanged="rbProducts_CheckedChanged" AutoPostBack="true" GroupName="rbProducts" Checked="true" runat="server" />
                          <asp:Literal runat="server" ID="ltrPagingCurStart" Text=""></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPagingCurTotal" Text=""></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text=""></asp:Literal>
<asp:DataPager ID="DataPager2" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" PreviousPageText="<i class='fa fa-angle-left'></i>" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm page-link" NextPageText="<i class='fa fa-angle-right'></i>" ShowNextPageButton="true" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>
</asp:PlaceHolder>



                    <div class="wizard_body mt-3 mb-2">  
                    <div class=" wizard-cont-wrap">

                    

                    </div>
                    </div>





                

            </div>


              </div>
                </asp:PlaceHolder>

               <!--tab-pane -->
                <asp:PlaceHolder ID="plcAddProduct" runat="server">
                    <div class="createproducscroll_wrap">
                  <div class="section-wrapper p-0 border-0 mt-1">
                    <label class="section-title mb-0">Create New Product</label>
                    <p class="section-title-p">If you were unable to locate the product in our Brand Gallery, you may create it here. Created products will appear automatically in My Products.</p>
                    <div class="form-layout">
                    <div class="row row-sm ">
                    <div class="col-lg-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Retailer Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selRetCat" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRetCat" DataTextField="business_type_name" AppendDataBoundItems="true" DataValueField="business_type_id"><asp:ListItem Text="Select retailer category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSRetCat" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt
INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id 
INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id
WHERE gbt.store_group_id=@storegroup ORDER BY bt.business_type_name" 
                        OnSelecting="SDSRetCat_Selecting">
                        <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selRetCat" ForeColor="Red" ErrorMessage="Select retailer category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCat" DataTextField="category_name" AppendDataBoundItems="true" DataValueField="category_id"><asp:ListItem Text="Select category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc
                            INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id WHERE ppc.parent_category_businessType=@bussinessType">
                        <SelectParameters>
                            <asp:ControlParameter Name="bussinessType" ControlID="selRetCat" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCat" ForeColor="Red" ErrorMessage="Select category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                    <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selSubCat" runat="server" AutoPostBack="True" CssClass="form-control select2 " AppendDataBoundItems="true" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT msc.sub_category_id,msc.sub_category,msc.main_category,pc.category_name,pc.category_id FROM mypha_productsubcategory msc
                                        INNER JOIN mypha_productcategory pc ON pc.category_id=msc.main_category WHERE msc.main_category=@catName">
                        <SelectParameters>
                            <asp:ControlParameter Name="catName" ControlID="selCat" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selSubCat" ForeColor="Red" ErrorMessage="Select sub category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                    <label class="form-control-label">Brand: <span class="tx-danger">*</span> <span class="addbrandpopup" data-toggle="modal" data-target="#addbrand">Add Brand</span></label>
                    <asp:DropDownList ID="selBrd" runat="server" CssClass="form-control select2 select2-show-search" ForeColor="GrayText" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id" OnDataBound="selBrd_DataBound"><asp:ListItem Text="Select brand" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT brand_id,brand_name FROM mypha_productbrands where ifnull(brand_name, '') not like '' order by brand_name">
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selBrd" ForeColor="Red" ErrorMessage="Select brand" runat="server"></asp:RequiredFieldValidator>
                    </div><asp:HiddenField ID="hidSelectedBrand" runat="server" Value="" />
                    </div><!-- col-4 -->
                    <div class="col-lg-8">
                    <div class="form-group-sm">
                    <label class="form-control-label">Product Name: <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtPrdName" runat="server" CssClass="form-control" placeholder="Enter product name"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtPrdName" ForeColor="Red" ErrorMessage="Input product name" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
    
                    <!-- col-4 -->
                    <!-- col-4 -->
    
                    <div class="col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Varient: </label>
                    <asp:TextBox ID="txtVarient" runat="server" CssClass="form-control" placeholder="Enter varient"/>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtVarient" ForeColor="Red" ErrorMessage="Input varient" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtQuantity" runat="server" CssClass="form-control" placeholder="Enter quantity"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtQuantity" ForeColor="Red" ErrorMessage="Input quantity" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Unit: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selUnit" runat="server" CssClass="form-control select2 " ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selUnit" ForeColor="Red" ErrorMessage="Select unit" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">HSN: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selHSN" runat="server" AutoPostBack="True" CssClass="form-control select2 " ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id"><asp:ListItem Text="Select HSN" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selHSN" ForeColor="Red" ErrorMessage="Select HSN" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtGSTVAT" runat="server" ReadOnly="true" CssClass="form-control"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtGSTVAT" ForeColor="Red" ErrorMessage="Input tax" runat="server"></asp:RequiredFieldValidator>
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
                    <asp:TextBox ID="txtERPId" runat="server" CssClass="form-control"/>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Return Days: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtReturn" runat="server" autocomplete="off" CssClass="form-control"/>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="selDays" ErrorMessage="Select return" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Edible: </label>
                    <asp:DropDownList ID="selFoodType" runat="server" CssClass="form-control select2 " ForeColor="GrayText">
                              <asp:ListItem Value="">Select from list</asp:ListItem>
                              <asp:ListItem Value="0">Not Edible</asp:ListItem>
                              <asp:ListItem Value="1">Vegetarian</asp:ListItem>
                              <asp:ListItem Value="2">Non Vegetarian</asp:ListItem>
                              <asp:ListItem Value="3">Vegan</asp:ListItem>
                          </asp:DropDownList>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selFoodType" ForeColor="Red" ErrorMessage="Select food type" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Country of Orgin: </label>
                    <asp:DropDownList ID="selCountry" runat="server" CssClass="form-control select2 " ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" AppendDataBoundItems="true" DataValueField="country_id"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCountry" ForeColor="Red" ErrorMessage="Select country" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-3-->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Delivery Mode: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selDelMode" runat="server" CssClass="form-control select2 " ForeColor="GrayText">
                              <asp:ListItem Value="">Select delivery mode</asp:ListItem>
                              <asp:ListItem Value="1">Courier</asp:ListItem>
                              <asp:ListItem Value="2">Express</asp:ListItem>
                              <asp:ListItem Value="3">Both</asp:ListItem>
                          </asp:DropDownList>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selDelMode" ForeColor="Red" ErrorMessage="Select delivery mode" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    

                        <div class="col-12 mb-3">
                    <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->

    
                    <div class="col-lg-4">
    
                    <div class="form-group">
                    <label class="form-control-label">Short Description <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtShortDescription" runat="server" CssClass="form-control" Height="223px" TextMode="MultiLine"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtShortDescription" ForeColor="Red" ErrorMessage="Input short description" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-8">
                    <div class="form-group m-0">
                    <label class="form-control-label">Long Description</label>
    
                    <asp:TextBox ID="summernote" runat="server" ClientIDMode="Static" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
    
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-12">
                      <div class="uploadsec">
                        <div class="upload_imgsec">
                          <div class="upload_info">
                            <h5 class="m-0">Upload Product Images</h5>
                            <span class="sizeinfo">(Max Size 512 x 512 px, 100 KB)</span>
                          </div><!--upload_info-->
                          <div class="upload_box_wrap">
                            <div class="upload_box">
                              <asp:FileUpload accept="image/*" runat="server" ID="imgUpload1" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                              <img class="uploadimg" id="img_1" alt="Upload" src="/content/images/1pix.png" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                              <asp:FileUpload accept="image/*" runat="server" ID="imgUpload2" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                              <img class="uploadimg" id="img_2" alt="Upload" src="/content/images/1pix.png" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                              <asp:FileUpload accept="image/*" runat="server" ID="imgUpload3" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                                <img class="uploadimg" id="img_3" alt="Upload" src="/content/images/1pix.png" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                              <asp:FileUpload accept="image/*" runat="server" ID="imgUpload4" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                              <img class="uploadimg" id="img_4" alt="Upload" src="/content/images/1pix.png" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                              <asp:FileUpload accept="image/*" runat="server" ID="imgUpload5" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                                <img class="uploadimg" id="img_5" alt="Upload" src="/content/images/1pix.png" />
                            </div><!--upload_box-->
                          </div><!--upload_box_wrap-->
                        </div><!--upload_imgsec-->

                        <%--<div class="upload_videosec">
                          <div class="upload_info">
                          <h5 class="m-0">Upload Produt Videos</h5>
                          <span class="sizeinfo">(Max Size 10 MB)</span>
                          </div><!--upload_info-->
                          <div class="upload_box_wrap">
                            <div class="upload_box">
                              <input accept="image/*" type='file' class="fileupload_productimage" id="video_up" />
                              <span class="remove">X</span>
                              <img class="uploadimg" id="video_img"  onerror="this.style.display = 'none'" alt="Upload" />                              
                            </div><!--upload_box-->
                          </div><!--upload_box_wrap-->
                        </div>--%>
                        
                      </div><!--uploadsec-->
                    </div>
                    </div><!-- row -->
                    </div><!-- form-layout -->
                    </div>
    
                    
                    <div class="d-sm-flex p-3 wiz_btnsect justify-content-center floting_btn_sec">
                        <button onclick="validateAddItem(); return false;" class="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4">Save</button>
                    <asp:HyperLink ID="hlCancelSaveProduct" runat="server" NavigateUrl="/SelectProduct" onclick="$(this).closest('div').addClass('processing_loader')" Text="Cancel" CssClass="btn btn-primary btn-drk-green btn-block m-0 mx-2 wd-sm-auto-force px-4"></asp:HyperLink>
                    </div>

                    </div>

                </asp:PlaceHolder>

          </div><!--wizard_wrap-->
        </div>
      </div><!--card-->

      <div class="copyright">© <a href="https://grozeo.com">grozeo.com</a></div>
    </div>
    <!-- /.login-box -->

<asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ID="SDSProducts" runat="server" OnSelected="SDSProducts_Selected"  OnSelecting="SDSBrands_Selecting"
 SelectCommand="select myProducts.*, ifnull(item_mrp.itemMrp, 0) as itemMSRP from( 
        SELECT stit_Id, stit_itemId, stit_itemERPId, stit_SKU, stit_HSNCode, stit_MRP, stit_brand_name, stit_category_name, med_manufacturename, (case when ifnull(mrp, 0) <= 0 then stit_MRP else mrp end) as itemMRP, 
        (SELECT image_url FROM finascop_stock_item_images WHERE product_id= i.stit_ID LIMIT 1) AS imageurl, br.mrp, br.selling_price, ifnull(br.brstit_id, 0) as instock
        FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
         INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
         INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType 
         INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id AND bbt.store_group_id=@storeId
    left join((SELECT stit_id as brstit_id, mrp, branch_id, selling_price FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id)) as br on br.brstit_id = i.stit_Id 
        WHERE stit_status = 1 and (ifnull(i.stit_StoreGroup, 0) <= 0 or i.stit_StoreGroup = @storeId ) AND  (@brand <> 0 and pdt_brand = @brand) AND (@category <= 0 OR c.category_id = @category) AND (@department <= 0 OR pc.parent_category_id = @department)
            and (trim(@search) like '' or stit_SKU like CONCAT('%', @search, '%'))
         GROUP BY stit_Id
 ) myProducts inner join item_mrp on item_mrp.stit_id=myProducts.stit_id AND item_mrp.itemMrp > 0 where @type = 0 or (@type = 1 and  myProducts.instock > 0) or (@type = 2 and  myProducts.instock <= 0)  ORDER BY stit_SKU" ProviderName="MySql.Data.MySqlClient">

<SelectParameters>
    <asp:ControlParameter Name="department" ControlID="selDepartment" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selCategory" Name="category" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:ControlParameter ControlID="selPopupBrands" Name="brand" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
    <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
    <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />
    <asp:ControlParameter Name="search" ControlID="txtSelectProductName" ConvertEmptyStringToNull="false" />
</SelectParameters>
</asp:SqlDataSource>

<asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ID="SDSDepartments" runat="server" OnSelecting="SDSBrands_Selecting"
    SelectCommand="SELECT pc.parent_category_id, pc.parent_category FROM mypha_productparent_category pc 
     INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType
     INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id WHERE bbt.store_group_id= @storeId" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
</SelectParameters>
</asp:SqlDataSource>
    
<asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ID="SDSCategory" runat="server" OnSelecting="SDSBrands_Selecting"
    SelectCommand="SELECT c.* FROM mypha_productcategory c INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category 
     INNER JOIN finascop_business_type bt ON bt.business_type_id=pc.parent_category_businessType INNER JOIN finascop_branch_group_business_type bbt ON bbt.business_type_id = bt.business_type_id
	 WHERE bbt.store_group_id= @storeId and (@department = 0 or pc.parent_category_id = @department) GROUP BY category_id" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        <asp:ControlParameter Name="department" ControlID="selDepartment" />
</SelectParameters>
</asp:SqlDataSource>


<asp:HiddenField ID="hidSelectedItems" runat="server" />
<asp:HiddenField ID="hidSelectedItemsWithPrice" runat="server" />
<asp:HiddenField ID="hidItemsInDB" runat="server" />
<asp:HiddenField ID="hidCurTab" Value="0" runat="server" />            
<asp:HiddenField ID="hidSelectView" runat="server" Value="1" />    


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
                    <label class="form-control-label">Brand</label>
                      <asp:TextBox ID="txtBrand" runat="server" CssClass="form-control" onfocus="this.select()" placeholder="Brand Name"></asp:TextBox>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label">Manufacturer</label>
                      <asp:TextBox ID="txtManufacturer" runat="server" CssClass="form-control" placeholder="Manufacturer Name"></asp:TextBox>
                  </div>
                </div>
  
              </div> <!--row-->

            </div><!--section-wrapper-->       

            
          </div><!--modal-body-->
          <div class="modal-footer">
              <span class="error_msg_wrap" id="addbranderror"><asp:Literal ID="ltrAddBrandResult" runat="server"></asp:Literal>
                  <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ErrorMessage="Please input brand name. " ControlToValidate="txtBrand" Display="Dynamic" ValidationGroup="CreateBrand"></asp:RequiredFieldValidator>
                  <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ErrorMessage="Please input manufacturer" ControlToValidate="txtManufacturer" Display="Dynamic" ValidationGroup="CreateBrand"></asp:RequiredFieldValidator>
              </span>
              <asp:LinkButton runat="server" Text="Save" OnClick="btnAddBrand_Click" CssClass="btn btn-primary btn-drk-green" ValidationGroup="CreateBrand"></asp:LinkButton>
              <%--<asp:Button runat="server" Text="Save" ID="btnAddBrand" OnClick="btnAddBrand_Click" CssClass="btn btn-primary btn-drk-green" />--%>
            <%--<button type="button" class="btn btn-primary btn-drk-green">Save</button>--%>
            <button type="button" class="btn btn-secondary btn-drk-green" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->
    
<!-- MODAL ALERT MESSAGE -->
    <div id="price_qunty_alert" class="modal fade price_qunty_alert">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
           <h5 class="m-0">Product Quantity & Selling Price Update</h5>
           <p class="mt-0 mb-3">Manage Quantity and Price of the <%--<b>20</b>--%> products selected</p>
            <div>
                <asp:CheckBox runat="server" ID="common_quantity" onchange="$('#stockpopuperror').text('');" ClientIDMode="Static" Visible="false" /> Apply common <b>Quantity</b> for all the products selected
              <%--<input type="checkbox" id="common_quantity2" onchange="$('#stockpopuperror').text('');" name="common_quantity"> Apply common <b>Quantity</b> for all the products selected--%>
              <div class="form-group mt-1">
                <div class="input-group wd-150-force ">
                    <asp:TextBox runat="server" TextMode="Number" ID="txtSelectProductQuantity" onfocus="this.select()" Text="1" min="1" CssClass="form-control" placeholder="Quantity"></asp:TextBox>
                  <div class="input-group-append">
                    <span class="input-group-text">Nos.</span>
                  </div>
                </div>
                
              </div>
            </div>
            <div>
              <%--<input type="checkbox" id="common_selling2" onchange="$('#stockpopuperror').text('');" name="common_selling">--%>
              <asp:CheckBox runat="server" ID="common_selling" onchange="$('#stockpopuperror').text('');" onfocus="this.select()" ClientIDMode="Static" Visible="false" /> Apply common <b>Discount</b> for all the products selected
              <div class="form-group mt-1">
                <div class="input-group wd-150-force ">
                  <asp:TextBox runat="server" TextMode="Number" Text="0" ID="txtSelectProductPercentage" CssClass="form-control" placeholder="Discount"></asp:TextBox>
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
                
              </div><!--form-group-->
            </div>
            <div class="valuewrapsec  mt-3">
              
            </div><!--valuewrapsec-->
            <div class="btnsec">
                <span class="error_msg_wrap" id="stockpopuperror"></span>
                <asp:Button ID="btnSetStockProceed" OnClick="btnSaveProducts_Click" OnClientClick="return validateStockPopup(this)" CssClass="btn d-inline-block btn-drk-green pd-x-25" Text="Proceed" runat="server" formnovalidate />
              <%--<button type="button" class="btn d-inline-block btn-drk-green pd-x-25" >Proceed</button>--%>
              <button type="button" class="btn d-inline-block btn-drk-green pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
            </div>
            
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

  </div><!--login_sec_wrp-->



    <!-- BASIC MODAL -->
    <div id="addproductpopup" class="addproductpopup modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">

            <div class="section-wrapper p-0 border-0">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <div class="row row-sm">
                <div class="col-12"><h6 class="mb-3 tx-dark">Price and Store Updates</h6></div>
                <div class="col-lg-4">
                  <label class="form-control-label">MRP/RRP</label>
                  <div class="form-group m-0">
                    <div class="input-group wd-150-force ">
                      <%--<input name="mrprrp" type="text" id="mrprrp123" class="form-control" placeholder="MRP/RRP" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                      <asp:TextBox runat="server" ID="mrprrp" CssClass="form-control" placeholder="MRP/RRP" onfocus="this.select()" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                      <div class="input-group-append">
                        <span class="input-group-text"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %></span>
                      </div>
    <asp:RequiredFieldValidator runat="server" ValidationGroup="AddProductPopup" ControlToValidate="mrprrp" Display="Dynamic" ErrorMessage="Input MRP"></asp:RequiredFieldValidator>

                    </div>
                  </div>
                </div>
                <div class="col-lg-4">


                  <div class="form-group m-0">
                    <label class="form-control-label">Discount</label>
                    <div class="input-group wd-150-force">
                      <%--<input type="number" value="5" id="newPercentage123" class="form-control" placeholder="Discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                      <asp:TextBox runat="server" ID="newPercentage" ClientIDMode="Static" CssClass="form-control" onfocus="this.select()" placeholder="Discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
     <asp:RequiredFieldValidator runat="server" ValidationGroup="AddProductPopup" ControlToValidate="newPercentage" Display="Dynamic" ErrorMessage="Input Discount"></asp:RequiredFieldValidator>
                   </div>
                    
                  </div>

                  
                </div>

                <div class="col-lg-4">
                  <label class="form-control-label">Stock</label>
                  <div class="form-group m-0">
                    <div class="input-group wd-150-force ">
                        <%--<input name="quantity" type="number" value="10" id="newquantity123" class="form-control" placeholder="Quantity" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                      <asp:TextBox runat="server" ClientIDMode="Static" TextMode="Number" Text="10" ID="newquantity" CssClass="form-control" onfocus="this.select()" placeholder="Quantity" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                      <div class="input-group-append">
                        <span class="input-group-text">Nos.</span>
                      </div>
    <asp:RequiredFieldValidator runat="server" ValidationGroup="newquantity" ControlToValidate="mrprrp" Display="Dynamic" ErrorMessage="Input Stock"></asp:RequiredFieldValidator>
                    </div>
                    
                  </div>
                  
                </div>
  
              </div> <!--row-->

            </div><!--section-wrapper-->       

            
          </div><!--modal-body-->
          <div class="modal-footer justify-content-center">
            <%--<button type="button" class="btn btn-primary btn-drk-green">Save</button>--%>
              <asp:Button ID="btnAddProduct" runat="server" OnClick="btnAddProduct_Click" Text="Save" CssClass="btn btn-primary btn-drk-green" />
            <button type="button" class="btn btn-primary btn-drk-green mx-2 wd-sm-auto-force px-4" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


  </form>
<script type="text/javascript">
    function validateStockPopup(obj) {
        if ($('#<%= txtSelectProductQuantity.ClientID%>').val() == '') {
            $('#stockpopuperror').text('Please enter quantity');
            return false;
        }
        if ($('#<%= txtSelectProductPercentage.ClientID%>').val() == '') {
            $('#stockpopuperror').text('Please enter percentage');
            return false;
        }
        //if ($('#price_qunty_alert').find('#common_quantity').is(':checked')) {
        //}
        //if ($('#price_qunty_alert').find('#common_selling').is(':checked')) {
        //}

        $('#stockpopuperror').text('');
        //$('#<%= form1.ClientID%>').attr('childobj', '');
        $(obj).closest('div').addClass('processing_loader');
        return true;
    }
    function checkall(obj) {
        if (!$(obj).is(':checkbox'))
            return false;
        var isChecked = $(obj).is(':checked');
        $('#tblselectproduct').find('tr').each(function () {
            var chk = $(this).find('span.chkselectitem input[type="checkbox"]');
            if (chk && $(chk).is(':checked') != isChecked) {
                $(chk).prop('checked', isChecked);
                updateSelection($(chk));
                $(chk).change();
            }
        });

    }
    function updateSelection(obj) {
        if ($(obj).is(':checkbox')) {
            var id = $(obj).closest('span').attr('itemid');
            if (!id)
                return;

            if ($(obj).is(':checked')) {
                addItem(id);
                $(obj).closest('tr').addClass('checked_now');
                $(obj).closest('tr').find('input.editamout').val($(obj).closest('span').attr('itemmrp'));
                var mrp = $(obj).closest('tr').find('input.editamout').val();
                selectItemMRP(id, mrp);
            }
            else {
                removeItem(id);
                $(obj).closest('tr').removeClass('checked_now').removeClass('already_added');
            }
            $('#<%= btnSelectAddProduct.ClientID%>, #<%= hlNextSelectItems.ClientID%>').addClass('disabled');
            <%--$('#<%= selCategory.ClientID%>, #<%= selDepartment.ClientID%>, #<%= selBrand.ClientID%>, #<%= txtSelectProductName.ClientID%>', #<%= lbSelectedProductSearch.ClientID%>).addClass('disabled');--%>

            <%--$('#<%= selBrand.ClientID%>, #<%= selDepartment.ClientID%>, #<%= selCategory.ClientID%>').prop('disabled', true);
            $('#<%= selCategory.ClientID%>').prop('disabled', true);--%>

        }
    }

    function addItem(id) {
        var ids = new Array();
        if ($('#<%= hidSelectedItems.ClientID %>').val() != '')
            ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        if (id)
            ids.push(id);
        if (ids.length > 0)
            $('#<%= btnSaveSelectedItems.ClientID%>').removeClass('disabled');

        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));

    }
    function removeItem(id) {
        var ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        ids = jQuery.grep(ids, function (value) {
            return value != id;
        });
        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));
        if (ids.length <= 0)
            $('#<%= btnSaveSelectedItems.ClientID%>').addClass('disabled');

        ids = $('#<%= hidSelectedItemsWithPrice.ClientID %>').val().split(',');
        ids = jQuery.grep(ids, function (value) {
            return value.split('|')[0] != id;
        });
        $('#<%= hidSelectedItemsWithPrice.ClientID %>').val(ids.join(","));
    }
    function selectItemMRP(id, mrp) {
        if (!id)
            return;
        var ids = new Array();
        if ($('#<%= hidSelectedItemsWithPrice.ClientID %>').val() != '')
                    ids = $('#<%= hidSelectedItemsWithPrice.ClientID %>').val().split(',');

                var updated = 0;
                for (var i = 0; i < ids.length; i++) {
                    var item = ids[i].split('|');
                    if (item[0] == id) {
                        ids[i] = item[0] + '|' + mrp.replace("|", "");
                        updated = 1;
                    }
                }
                if (id && updated == 0)
                    ids.push(id + '|' + mrp.replace("|", ""));

                $('#<%= hidSelectedItemsWithPrice.ClientID %>').val(ids.join(","));

    }
    (function () {
        var previousIndex;

        $("select.selproductvalidate").focus(function () {
            // Store the current value on focus, before it changes
            previousIndex = this.selectedIndex;
        }).change(function (e) {
            if (!validateselectitems(true)) {
                e.target.selectedIndex = previousIndex;
            }
            else {
                var value = $(this).find("option:selected").val();
                __doPostBack($(this).id, value);
            }
        });
    })();


    function validateselectitems(disableQtyAlert) {
        var isvalid = true; var focused = false;
        $('#tblselectproduct').find('tr.checked_now input.mrpinput').each(function () {
            var isselected = $(this).closest('tr').find('input[id*= "_chkProductItem_"]').is(':checked'); // lstProducts_chkProductItem

            if (isselected === true && ($(this).val() == '' || $(this).val() <= 0)) {
                $(this).data("title", 'Value should be greater than 0').addClass("error");
                if (!focused) {
                    $(this).focus();
                    focused = true;
                }
                //if (isvalid)
                //    $(this).tooltip('toggle');
                    isvalid = false;
            }
        });
        if (!disableQtyAlert && isvalid === true)
            $('#price_qunty_alert').modal('show');
        return (disableQtyAlert ? isvalid : false); //isvalid;
    }

    $(document).ready(function () {
        $('.select2-show-search').select2({
            minimumResultsForSearch: ''
        });

        $('#tblSelectedProducts').find('tbody tr input.selectedchangeevent').unbind('change').on('change', function (e) {
            $('#btnSaveSelectedProducts').removeClass('disabled');
            $('#<%= btnSelectProduct.ClientID%>').addClass('disabled');
            $('#<%= hlNextSelectedProduct.ClientID%>').addClass('disabled');
        });

        $('#price_qunty_alert').on('shown.bs.modal', function (e) {
            $('#<%= txtSelectProductQuantity.ClientID %>').focus();
            });
        $('#addproductpopup').on('shown.bs.modal', function (e) {
            $('#mrprrp').focus();
            });
        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
    {  %>

        $('input[type="checkbox"]').on('change', function (e) {
            if (e.target.checked) {
                $(this).closest("tr").find(".labelamout").addClass("d-none");
                $(this).closest("tr").find(".editamout").addClass("d-block");
                $(this).closest('tr').find('input.editamout').focus();
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

        <% } %>

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
            var mrp = $(this).val();
            if (id)
                selectItemMRP(id, mrp);
        });
        $("#<%= form1.ClientID%>").on('submit', function () {
            var obj = $('#' + $(this).attr('childobj')); //$(this).find('input[type=submit][clicked=true]');
            //if (!obj && $(this).attr('childobj') != '')
            //    obj = $('#' + $(this).attr('childobj'));

            if (obj) {
                $(obj).closest('div').addClass('processing_loader');
            }
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

    function validateSelectedProducts(obj, skipChangeValidate) {
        var isValid = true;

        if ($('#tblSelectedProducts').find('tbody tr.trselecteditem').length <= 0) {
            alert('Please select / add products to your store to continue');
            return false;
        }
        if (!skipChangeValidate && !($('#btnSaveSelectedProducts').hasClass('disabled'))) {
            if (!confirm('There are modifications not being saved. It is recommended to click "Save" before proceeding in order to retain the modifications. Otherwise, the updates would be lost. Are you sure you wish to proceed?'))
                return false;
        }
        var showedToolTip = false;
        $('#tblSelectedProducts').find('tbody tr.trselecteditem').each(function () {
            var sellingPrice = $(this).find('input.selected_selling').val();
            if (sellingPrice <= 0) {
                $(this).find('input.selected_selling').data("title", 'Value should be greater than 0').addClass("error");
                if (!showedToolTip) {
                    $(this).find('input.selected_selling').tooltip('toggle');
                    showedToolTip = true;
                }
            }
            var qty = $(this).find('input.selected_qty').val();
            if (qty <= 0) {
                $(this).find('input.selected_qty').data("title", 'Value should be greater than 0').addClass("error");
                if (!showedToolTip) {
                    $(this).find('input.selected_qty').tooltip('toggle');
                    showedToolTip = true;
                }
            }

            if (!qty || !sellingPrice || qty <= 0 || sellingPrice <= 0)
                isValid = false;
        });

        if (!isValid)
            alert('There are items with missing or 0 stock or price. Please add stock and price for all items to continue');
        else if (!obj)
            $(obj).closest('form').attr('childobj', $(obj).id);

        if (isValid && obj.id)
            $(obj).closest('form').attr('childobj', obj.id);

        return isValid;
    }
    function validateAddItem() {
        if (typeof (Page_ClientValidate) == 'function') {
            Page_ClientValidate('CreateProduct');
        }
        if (Page_IsValid) {
            $('#addproductpopup').modal('show');
            //alert("Validations successful");
            //do something
        }
    }

    $('div.upload_box_wrap input.fileupload_productimage').unbind('change').on('change', function (e) {
        const [file] = this.files
        if (file) {
            $(this).closest('div.upload_box').find('img.uploadimg').attr('src', URL.createObjectURL(file));
            $(this).closest('div.upload_box').find('img.uploadimg').show();
        }
    });
    $("span.remove").click(function () {
        $(this).closest(".upload_box").find(".uploadimg").removeAttr('src');
        $(this).closest(".upload_box").find('input').val("");
    });

</script>


</body>
</html>
