<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products" AutoEventWireup="true" CodeBehind="Products.aspx.cs" Inherits="RetalineProAgent.Products" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Products</li>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Add Products"></asp:Literal>
                <!--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> -->
            </h6>
        <p class="mb-0">Add your products</p>
    </div>
    
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">

<div class="row row-sm">
<asp:PlaceHolder ID="plcSelectBranchModel" Visible="false" runat="server">
                     <span class="tx-dark mr-2">
                        <asp:Literal ID="ltrBranch" runat="server">Branch</asp:Literal>
                    </span>
                   <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="wd-50p-force bd p-2" DataTextField="br_Name" DataValueField="br_ID" runat="server"><asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
<asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
                </asp:PlaceHolder>

                  <div class="col-lg-2 input-group-sm">
                      <label>Brand:</label>
<asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" AppendDataBoundItems="true" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2"><asp:ListItem Text="All Brands" Value="0"></asp:ListItem></asp:DropDownList>
                      <%--<select class="form-control select2" style="width: 100%;">
                    <option selected="selected">Select Brand</option>
                  </select>--%>
                  </div>
<asp:SqlDataSource ID="SDSBrands" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT bnd.brand_id, bnd.brand_name, bnd.manufacture_id, bnd.img_url FROM mypha_productbrands bnd  
     INNER JOIN finascop_stock_itemmaster i ON i.pdt_brand = bnd.brand_id 
    WHERE i.stit_status = 1 and stit_StoreGroup=@storegroup GROUP BY bnd.brand_id ORDER BY bnd.brand_name" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:Parameter Name="storegroup" /></SelectParameters>
</asp:SqlDataSource>

                  <div class="col-lg-2 input-group-sm">
                      <label for="txtDateTo">Category:</label>
                  <asp:DropDownList ID="selSubCat" runat="server" AutoPostBack="True" AppendDataBoundItems="true" CssClass="form-control select2" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="All Categories" Value="0"></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT sc.sub_category_id,sc.sub_category, sc.main_category FROM mypha_productsubcategory sc
INNER JOIN finascop_stock_itemmaster i ON i.product_category = sc.sub_category_id WHERE i.stit_status = 1 and i.stit_StoreGroup=@storegroup group by sub_category_id order by sub_category">
                        <SelectParameters><asp:Parameter Name="storegroup" /></SelectParameters>
                    </asp:SqlDataSource>

                  </div>
                  <div class="col-lg-2 input-group-sm">
                      <label for="txtFindProducts">Search by:</label><br />
                      <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                      <asp:TextBox ID="txtFindProducts" runat="server" placeholder="Search " CssClass="form-control"  autocomplete="products-search-name"></asp:TextBox>
                  </div>

                      <div class="col-lg-1">
                      <label class="w-100">&nbsp;</label>
                    <a id="cpMainContent_lbtnSearch" class="btn btn-success btn-sm" href="javascript:__doPostBack('ctl00$cpMainContent$lbtnSearch','')">Search</a>
                  </div>
<div class="col-lg-5 text-right">
    
<a href="/Tenant/PrivateInventory" type="button" class="btn btn-info pb-1 pt-1">
    <i class="icon ion-plus-circled mr-2"></i>Create Product</a>&nbsp;
<%--<div class="float-right">
                 <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                              <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm page-link">
                      <i class="fa fa-angle-left"></i>
                      </asp:LinkButton>
                              <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm page-link">
                          <i class="fa fa-angle-right"></i>
                      </asp:LinkButton>
                  </div>

                  <!-- /.btn-group -->
                </div>--%>
    <label class="mb-0 mt-2"><a href="/Tenant/StockPrice" type="button" class="" style="margin-left: 20px;text-align: right;"><i class="icon ion-plus-circled mr-2"></i>Manage Stock &amp; Price</a>
        <a href="/Tenant/inventorymapping" type="button" class="" style="margin-left: 20px;text-align: right;"><i class="icon ion-checkmark-circled mr-2"></i>Select more products</a>
    </label>
</div>

                </div>


                      
            </div>
                    
                <div class="card-body">
                    <div class="table-responsive mailbox-messages">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvMainProducts" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvMainProducts_DataBound" DataSourceID="SDSMainProducts">
                                    <Columns>
                                        <%--<asp:BoundField HeaderText="Retailer Category" DataField="retailerCategory" SortExpression="retailerCategory" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                        <%--<asp:BoundField HeaderText="Category" DataField="mainCategory" SortExpression="mainCategory" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                        <asp:TemplateField HeaderText="Product name" SortExpression="stit_itemName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <asp:Image Width="30" runat="server" Visible='<%# (String.IsNullOrEmpty(Eval("imageurl").ToString())? false:true) %>' ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' style="max-width: 30px; max-height: 30px; width: auto!important" />
                                                <%# Eval("stit_itemName") %>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Product Name" DataField="stit_itemName" SortExpression="stit_itemName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                        <asp:BoundField HeaderText="Category" DataField="mainCategory" SortExpression="mainCategory" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Brand" DataField="stit_brand_name" SortExpression="stit_brand_name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Varient" DataField="stit_product_variant" SortExpression="stit_product_variant" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Tenant/PrivateInventory" DataNavigateUrlFields="stit_ID" DataNavigateUrlFormatString="~/Tenant/PrivateInventory?id={0}" />
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No products created.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" OnSelected="SDSMainProducts_Selected" ID="SDSMainProducts" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT i.stit_ID,i.stit_SKU,i.stit_product_variant,i.stit_itemName,i.stit_category_name,i.stit_brand_name,
(SELECT CONCAT(c.category_name, ' / ', sc.sub_category) FROM mypha_productcategory c inner join mypha_productsubcategory sc on sc.main_category = c.category_id  WHERE sc.sub_category_id = i.product_category) AS mainCategory, 
(SELECT image_url FROM finascop_stock_item_images WHERE IFNULL(image_url, '') <> '' AND product_id=i.stit_ID ORDER BY image_type DESC LIMIT 1) AS imageurl
 FROM finascop_stock_itemmaster i WHERE stit_StoreGroup=@storegroup and (ifnull(@brand, 0) <= 0 or pdt_brand=@brand ) and (trim(@search) like '' or stit_SKU like CONCAT('%', @search, '%') 
  or stit_brand_name like CONCAT('%', @search, '%') or stit_category_name like CONCAT('%', @search, '%') or stit_product_variant like CONCAT('%', @search, '%')) 
and (@cat = 0 or product_category = @cat) order by stit_SKU"
        OnSelecting="SDS_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
            <asp:ControlParameter Name="search" ControlID="txtFindProducts" Type="String" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="brand" ControlID="selBrand" />
            <asp:ControlParameter Name="cat" ControlID="selSubCat" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
            </div>

            </div>
   
</asp:Content>
