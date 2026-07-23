<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products" AutoEventWireup="true" CodeBehind="ProductsSettings.aspx.cs" Inherits="RetalineProAgent.ProductsSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/">Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Product</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Create Product</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="section-wrapper">
          <label class="section-title">Create New Product</label>
          <div class="form-layout">
            <div class="row mg-b-25">
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selSubCat" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT sub_category_id, sub_category FROM mypha_productsubcategory ORDER BY sub_category"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Brand: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selBrand" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id"><asp:ListItem Text="Select brand" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT brand_id, brand_name FROM mypha_productbrands ORDER BY brand_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Product Master: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selProductMaster" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSProductMaster" DataTextField="item_name" DataValueField="itemname_id"><asp:ListItem Text="Select product master" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSProductMaster" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT itemname_id, item_name FROM finascop_stock_itemmastername ORDER BY item_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Varient: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtVarient" runat="server" required CssClass="form-control" placeholder="Enter varient"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtQuantity" runat="server" required CssClass="form-control" placeholder="Enter quantity"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Unit: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selUnit" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Net Weight: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtNetWeight" runat="server" required CssClass="form-control" placeholder="Enter net weight"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">HSN: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selHSN" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id"><asp:ListItem Text="Select HSN" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtGSTVAT" runat="server" required CssClass="form-control" placeholder="Enter GST / VAT %"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Display label in web: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtDisplayLbL" runat="server" required CssClass="form-control" placeholder="Enter display label in web"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Return Time (days): <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtReturnTime" runat="server" required CssClass="form-control" placeholder="Enter return time (days)"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Edible: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selFoodType" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="0">Select from list</asp:ListItem>
                              <asp:ListItem>Not Edible</asp:ListItem>
                              <asp:ListItem>Vegetarian</asp:ListItem>
                              <asp:ListItem>Non Vegetarian</asp:ListItem>
                              <asp:ListItem>Vegan</asp:ListItem>
                          </asp:DropDownList>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Country of Orgin: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selCountry" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" DataValueField="country_id"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-3-->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkRawProduct" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("directPurchase").Equals("Active") %>'/>
                <span>Raw Product</span>
                </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkFeatured" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("featured").Equals("Active") %>'/>
                <span>Featured</span>
                </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkPopular" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("popular").Equals("Active") %>'/>
                <span>Popular</span>
                </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkCourierDeliv" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("courierDelivery").Equals("Active") %>'/>
                <span>Courier Delivery</span>
                </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkDirectDeliv" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("directDelivery").Equals("Active") %>'/>
                <span>Direct Delivery</span>
                </div><!-- col-3 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="chkRRP" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("isRRPApplicable").Equals("Active") %>'/>
                <span>RRP</span>
                </div><!-- col-3 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Short Discription: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="srtDiscp" style="padding:150px"></textarea>
                </div>
              </div><!-- col-4 -->&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Long Discription: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="lngDisp" style="padding:150px"></textarea>
                    <%--<richtextbox runat="server" id="lngDisp"></richtextbox>--%>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Ingredients: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="ingredientt" style="padding:150px"></textarea>
                </div>
              </div><!-- col-4 -->&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Preparation & Use: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="prepUse" style="padding:150px"></textarea>
                    <%--<richtextbox runat="server" id="lngDisp"></richtextbox>--%>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Allergens: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="allergen" style="padding:150px"></textarea>
                </div>
              </div><!-- col-4 -->&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Nutrtion Label: <span class="tx-danger">*</span></label>
                  <%--<asp:TextBox ID="TextBox1" runat="server" required CssClass="form-control" placeholder="Enter second name"/>--%>
                    <textarea runat="server" id="nutriLabel" style="padding:150px"></textarea>
                    <%--<richtextbox runat="server" id="lngDisp"></richtextbox>--%>
                </div>
              </div><!-- col-4 -->
                
            <%--<div class="form-layout-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                <%--<asp:Button runat="server" ID="btnCancel"  CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled" CssClass="btn btn-secondary bd-0" Text="Cancel" PostBackUrl="~/DeliveryBoys"/>--%>
                <%--<a href="/Products" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>--%>
            <%--</div>--%>
          <%--</div><!-- form-layout -->
        <div class="form-layout">--%>
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Primary Package: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selPackage" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSPackage" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Value="0" Text="Select primary package"></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSPackage" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">SKU: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selSKU" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSSku" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Text="Select SKU" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSSku" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 mg-t-35">
                    <asp:CheckBox ID="stdpack" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("stit_stdPacking").Equals("Active") %>'/>
                <span>Apply Standard Packing</span>
                </div><!-- col-3 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Nos: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtnumbers1" runat="server" required CssClass="form-control" placeholder="Enter nos."/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Contains: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtcontains1" runat="server" required CssClass="form-control" placeholder="Enter contains"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Purchase Unit: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtpurchseUnit1" runat="server" required CssClass="form-control" placeholder="Enter purchasing unit"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 mg-t-30">
                    <%--<asp:Button ID="Button1" runat="server" Text="Add" OnClick="Add" CommandName="EmptyDataTemplate" />--%>
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled" Text="Add"/>
            </div>
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbNumbers2" runat="server">Nos: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtnumbers2" runat="server" required CssClass="form-control" placeholder="Enter nos." Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbContains2" runat="server">Contains: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtcontains2" runat="server" required CssClass="form-control" placeholder="Enter contains" Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbPurchaseUnit2" runat="server" visible="false">Purchase Unit: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selPurchaseUnit2" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSPurchaseUnit2" DataTextField="package_type_name" DataValueField="package_type_id" Visible="false"><asp:ListItem Text="Select purchasing unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSPurchaseUnit2" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 mg-t-30">
                <asp:Button runat="server" ID="delete1" OnClick="delete1_Click" CssClass="btn btn-primary bd-0" Text="Delete" Visible="false" CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled"/>
            </div>
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbNumbers3" runat="server">Nos: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtnumbers3" runat="server" required CssClass="form-control" placeholder="Enter nos." Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbContains3" runat="server">Contains: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtcontains3" runat="server" required CssClass="form-control" placeholder="Enter contains" Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbPurchaseUnit3" runat="server" visible="false">Purchase Unit: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selPurchaseUnit3" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSPurchaseUnit3" DataTextField="package_type_name" DataValueField="package_type_id" Visible="false"><asp:ListItem Text="Select purchasing unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSPurchaseUnit3" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 mg-t-30">
                <asp:Button runat="server" ID="delete2" OnClick="delete2_Click" CssClass="btn btn-primary bd-0" Text="Delete" Visible="false" CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled"/>
            </div>
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbNumbers4" runat="server">Nos: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtnumbers4" runat="server" required CssClass="form-control" placeholder="Enter nos." Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbContains4" runat="server">Contains: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtcontains4" runat="server" required CssClass="form-control" placeholder="Enter contains" Visible="false"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label" id="lbPurchaseUnit4" runat="server" visible="false">Purchase Unit: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selPurchaseUnit4" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSPurchaseUnit4" DataTextField="package_type_name" DataValueField="package_type_id" Visible="false"><asp:ListItem Text="Select purchasing unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSPurchaseUnit4" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 mg-t-30">
                <asp:Button runat="server" ID="delete3" OnClick="delete3_Click" CssClass="btn btn-primary bd-0" Text="Delete" Visible="false" CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled"/>
            </div>
                <div class="col-lg-4">
                    <label class="form-control-label">Level 1 (Qty in nos): <span class="tx-danger">*</span></label>
                <div class="form-group">
                  <%--<label class="form-control-label"><span class="tx-danger">*</span></label>--%>
                  <asp:TextBox ID="optQty1" runat="server" required CssClass="form-control" placeholder="Opti Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <%--<label class="form-control-label"><span class="tx-danger">*</span></label>--%>
                  <asp:TextBox ID="minQty1" runat="server" required CssClass="form-control" placeholder="Min Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <%--<label class="form-control-label"><span class="tx-danger">*</span></label>--%>
                  <asp:TextBox ID="maxQty1" runat="server" required CssClass="form-control" placeholder="Max Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                    <label class="form-control-label">Level 2 (Qty in nos): <span class="tx-danger">*</span></label>
                <div class="form-group">
                  <asp:TextBox ID="optQty2" runat="server" required CssClass="form-control" placeholder="Opti Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <asp:TextBox ID="minQty2" runat="server" required CssClass="form-control" placeholder="Min Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <asp:TextBox ID="maxQty2" runat="server" required CssClass="form-control" placeholder="Max Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                    <label class="form-control-label">Level 3 (Qty in nos): <span class="tx-danger">*</span></label>
                <div class="form-group">
                  <asp:TextBox ID="optQty3" runat="server" required CssClass="form-control" placeholder="Opti Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <asp:TextBox ID="minQty3" runat="server" required CssClass="form-control" placeholder="Min Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <asp:TextBox ID="maxQty3" runat="server" required CssClass="form-control" placeholder="Max Qty"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                    <label class="form-control-label">Buffer %: <span class="tx-danger">*</span></label>
                <div class="form-group">
                  <asp:TextBox ID="txtBuffer" runat="server" required CssClass="form-control" placeholder="Distributor Buffer %"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4 mg-t-30">
                <div class="form-group">
                  <asp:TextBox ID="TextBox2" runat="server" required CssClass="form-control" placeholder="Retailer Buffer %"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-12 mg-t-35">
                    <asp:CheckBox ID="chkSaleUnit" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("stit_salesUnit").Equals("Active") %>'/>
                <span>Sales Unit Applicable</span>
                </div><!-- col-3 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Online Sales: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selOnlineSale" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSOnlineSale" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Text="Purchasing Unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSOnlineSale" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Counter Sales: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selCounterSale" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSCounterSale" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Text="Package Type" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSCounterSale" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Distributor Sales: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDistSale" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSDistSale" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Text="From CS as" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSDistSale" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Stokist Sale: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selStokistSale" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSStokistSale" DataTextField="package_type_name" DataValueField="package_type_id"><asp:ListItem Text="Central Store Package" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSStokistSale" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT package_type_id,package_type_name FROM mypha_productpackage_type WHERE STATUS = 1 ORDER BY package_type_name"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                </div><!-- row -->
            <div class="form-layout-footer">
                <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                <a href="/Tenant/Products" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>
            </div>
          </div><!-- form-layout -->
        </div>
</asp:Content>