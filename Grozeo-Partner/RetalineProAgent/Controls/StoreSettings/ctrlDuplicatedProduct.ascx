<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlDuplicatedProduct.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlDuplicatedProduct" %>
                    <div class="form-layout">
                    <div class="row row-sm ">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="form-control-label" style="width: 100%;">Brand: <span class="tx-danger">*</span> 
                                    <span class="addbrandpopup" data-toggle="modal" runat="server" visible="false" data-target="#addbrand" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Add Brand</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtSelectedBrand" runat="server" CssClass="form-control" Enabled="false" autocomplete="off" />
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-lg-8">
                            <div class="form-group-sm">
                                <label class="form-control-label">Product Name: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtPrdName" Enabled="false" runat="server" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtPrdName" ForeColor="Red" ErrorMessage="Product name is required" runat="server"></asp:RequiredFieldValidator>
                                <asp:Label ID="lblProductNameResult" runat="server" ForeColor="Red"></asp:Label>
                            </div>
                        </div>
                        <!-- col-4 -->


                    <div class="col-lg-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Retailer Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selRetCat" Enabled="false" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRetCat" DataTextField="business_type_name" AppendDataBoundItems="true" DataValueField="business_type_id"><asp:ListItem Text="Select retailer category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSRetCat" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt
INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id
WHERE bt.status=1 GROUP BY business_type_id ORDER BY bt.business_type_name">
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selRetCat" ForeColor="Red" ErrorMessage="Select retailer category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selCat" Enabled="false" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" OnDataBound="selCat_DataBound" DataSourceID="SDSCat" DataTextField="category_name" DataValueField="category_id"><asp:ListItem Text="Select category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc
                            INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id WHERE ppc.parent_category_businessType=@bussinessType AND pc.status='1'">
                        <SelectParameters>
                            <asp:ControlParameter Name="bussinessType" ControlID="selRetCat" DefaultValue="0" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCat" ForeColor="Red" ErrorMessage="Select category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                    <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selSubCat" Enabled="false" runat="server" CssClass="form-control select2" OnDataBound="selSubCat_DataBound" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT msc.sub_category_id,msc.sub_category,msc.main_category,pc.category_name,pc.category_id FROM mypha_productsubcategory msc
                                        INNER JOIN mypha_productcategory pc ON pc.category_id=msc.main_category WHERE msc.main_category=@catName AND msc.status=1">
                        <SelectParameters>
                            <asp:ControlParameter Name="catName" ControlID="selCat" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selSubCat" ForeColor="Red" ErrorMessage="Select sub category" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Varient: <%--<span class="tx-danger">*</span>--%></label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtVarient" runat="server" CssClass="form-control" placeholder="Enter varient" autocomplete="off"/>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtVarient" ForeColor="Red" ErrorMessage="Input varient" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtQuantity" runat="server" CssClass="form-control" placeholder="Enter quantity" autocomplete="off"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtQuantity" ForeColor="Red" ErrorMessage="Input quantity" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Unit: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selUnit" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id" OnDataBound="selUnit_DataBound"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selUnit" ForeColor="Red" ErrorMessage="Select unit" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">HSN/SAC: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selHSN" Enabled="false" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id" OnDataBound="selHSN_DataBound"><asp:ListItem Text="Select HSN/SAC" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selHSN" ForeColor="Red" ErrorMessage="Select HSN/SAC" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    <asp:HiddenField ID="hidVarientGroupName" runat="server" /><asp:HiddenField ID="hidGroupItem" ClientIDMode="Static" runat="server" />
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: <%--<span class="tx-danger">*</span>--%></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                        <asp:DropDownList ID="selType" Enabled="false" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSType" DataTextField="hsnGst" DataValueField="id"></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSType" ProviderName="MySql.Data.MySqlClient" 
                        SelectCommand="SELECT id, hsnGst, hsnId, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id">
                        <SelectParameters>
                            <asp:ControlParameter Name="hsnId" ControlID="selHSN" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selHSN" ForeColor="Red" ErrorMessage="Select" runat="server"></asp:RequiredFieldValidator>--%>
                    <%--<asp:TextBox ID="txtGSTVAT" runat="server" Enabled="false" CssClass="form-control" autocomplete="off"/>--%>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtGSTVAT" ForeColor="Red" ErrorMessage="Input tax" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Barcode</label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtBarcode" runat="server" autocomplete="off" CssClass="form-control"/>
                    </div>
                    </div><!-- col-4 -->
    
                    <%--<div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">ERP ID</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtERPId" runat="server" autocomplete="off" CssClass="form-control"/>
                    </div>
                    </div><!-- col-4 -->--%>
    

                        <%--<div class="col-lg-3">
                    <asp:CheckBox ID="chkManualSchedule" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->--%>

                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Return Days: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtReturn" runat="server" autocomplete="off" CssClass="form-control"/>
                    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtReturn" ErrorMessage="Select return" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Edible: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selFoodType" Enabled="false" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="">Select from list</asp:ListItem>
                              <asp:ListItem Value="0">Not Edible</asp:ListItem>
                              <asp:ListItem Value="4">Edible</asp:ListItem>
                              <asp:ListItem Value="1">Edible - Vegetarian</asp:ListItem>
                              <asp:ListItem Value="2">Edible - Non Vegetarian</asp:ListItem>
                              <asp:ListItem Value="3">Edible - Vegan</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selFoodType" ForeColor="Red" ErrorMessage="Select food type" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Country of Orgin: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selCountry" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" DataValueField="country_id" OnDataBound="selCountry_DataBound"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
                    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCountry" ForeColor="Red" ErrorMessage="Select country" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-3-->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Delivery Mode: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selDelMode" Enabled="true" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="">Select delivery mode</asp:ListItem>
                              <asp:ListItem Value="1" Enabled="true">Courier</asp:ListItem>
                              <asp:ListItem Value="2">Express</asp:ListItem>
                              <asp:ListItem Value="3" Enabled="true">Both</asp:ListItem>
                          </asp:DropDownList>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selDelMode" ID="rfvDelivMode" Visible="true" ForeColor="Red" ErrorMessage="Select delivery mode" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
                        <div class="col-lg-12">
                            <div class="form-group-sm mb-3">
                                <label class="form-control-label">Product name to display on website: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtProductWebName" runat="server" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtProductWebName" ForeColor="Red" ErrorMessage="Product name is required" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3" runat="server" visible="false">
                            <label class="form-control-label mb-0 mb-lg-4 w-100"></label>
                            <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->

    
                    <div class="col-lg-4">
    
                    <div class="form-group">
                    <label class="form-control-label">Short Description <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtShortDescription" runat="server" CssClass="form-control" Height="235px" TextMode="MultiLine"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtShortDescription" ForeColor="Red" ErrorMessage="Input short description" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-8 mb-3 mb-lg-0">
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
                              <asp:Label ID="lblProd1" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg1" runat="server" />
                                <asp:Image runat="server" ID="productImg1" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload2" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd2" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg2" runat="server" />
                                <asp:Image runat="server" ID="productImg2" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload3" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd3" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg3" runat="server" />
                                <asp:Image runat="server" ID="productImg3" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload4" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd4" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg4" runat="server" />
                                <asp:Image runat="server" ID="productImg4" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload5" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd5" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg5" runat="server" />
                                <asp:Image runat="server" ID="productImg5" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
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
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUploadVideo" CssClass="fileupload_productimage" />
                              <span class="remove">X</span>
                              <img class="uploadimg" id="video_img" src='' alt="Upload" />                              
                            </div><!--upload_box-->
                          </div><!--upload_box_wrap-->
                        </div>--%>
                        
                      </div><!--uploadsec-->
                    </div>
                    </div><!-- row -->

                        <div class="d-sm-flex wiz_btnsect justify-content-center justify-content-lg-start floting_btn_sec">
                            <% if (IsEditView)
                            { %>
                          <asp:Button ID="btnEditProduct" OnClientClick="return validateEditItem()" OnClick="btnEditProduct_Click" runat="server" Text="Save" ValidationGroup="CreateProduct" CssClass="btn btn-primary ml-lg-2 " />

                        <% }
                            else
                            { %>
                        <%--<button onclick="validateAddItem(); return false;" class="btn btn-primary btn-drk-green btn-block mr-2 wd-sm-auto-force px-4">Save</button>--%>
                            <%--<asp:Button ID="btnAddPrivateProduct" runat="server" OnClick="btnAddProduct_Click" Text="Add Product" ValidationGroup="CreateProduct" CssClass="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4" />--%>
                            <a href="javascript:void(0)" class="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4" onclick="if (validateEditItem()) $('#addVariantGroup').modal('show');" >Add Product</a>
                            <%--<button class="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4" onclick="if (validateEditItem()) $('#addVariantGroup').modal('show');" >Add Product</button>--%>
                        <% } %>
                            <%--<asp:Button ID="btnCancelSaveProduct" runat="server" Text="Cancel" OnClientClick="$(this).closest('form').attr('childobj', this.id);" CssClass="btn btn-secondary btn-drk-green  m-0 mx-2 px-4"  CausesValidation="false" formnovalidate />--%>
                            <div class="d-inline-block">
                                <a href="/Tenant/MyProducts" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div><!-- form-layout -->

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
                    <div class="input-group wd-120-force input_search_box">
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
                    <div class="input-group wd-120-force input_search_box">
                      <%--<input type="number" value="5" id="newPercentage123" class="form-control" placeholder="Discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                      <asp:TextBox runat="server" ID="newPercentage" Text="1" ClientIDMode="Static" CssClass="form-control" onfocus="this.select()" placeholder="Discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
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
                    <div class="input-group wd-120-force input_search_box">
                        <%--<input name="quantity" type="number" value="10" id="newquantity123" class="form-control" placeholder="Quantity" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                      <asp:TextBox runat="server" ClientIDMode="Static" Text="1" ID="newquantity" CssClass="form-control" onfocus="this.select()" placeholder="Quantity" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
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
            <button type="button" class="btn btn-secondary mx-2 wd-sm-auto-force px-4" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<div id="addVariantGroup" class="modal fade">
  <div class="modal-dialog modal-dialog-vertical-center" role="document">
    <div class="modal-content bd-0 tx-14">
      <div class="modal-body">

        <div class="section-wrapper p-0 border-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="row row-sm">
            <div class="col-12"><h6 class="mb-2 tx-dark">Add to Variant Group</h6></div>
            <div class="col-12">
		<p>Would you like to group the item with the other one chosen for duplication? The grouped items will appear on the product details page as options for selecting the preferred variant.</p>
            </div>
  
          </div> <!--row-->
        </div><!--section-wrapper-->       
      </div><!--modal-body-->
      <div class="modal-footer">
          <asp:LinkButton runat="server" Text="Yes" ID="lbtnVariantGroupYes" OnClick="lbtnVariantGroupYes_Click" CssClass="btn btn-primary btn-drk-green"></asp:LinkButton>
          <asp:LinkButton runat="server" Text="No" ID="lbtnVariantGroupNo" OnClick="lbtnVariantGroupNo_Click" CssClass="btn btn-secondary btn-drk-green" ></asp:LinkButton>
      </div>
    </div>
  </div><!-- modal-dialog -->
</div>


<script type="text/javascript">
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
    function validateEditItem() {
        if (typeof (Page_ClientValidate) == 'function') {
            Page_ClientValidate('CreateProduct');
        }
        if (Page_IsValid) {
            return true;
        }
        return false;
    }

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
    $('div.upload_box_wrap input.fileupload_productimage').unbind('change').on('change', function (e) {
        const [file] = this.files
        if (file) {
            $(this).closest('div.upload_box').find('img.uploadimg').attr('src', URL.createObjectURL(file));
            $(this).closest('div.upload_box').find('img.uploadimg').show();
        }
    });

    $("span.remove").click(function () {
        <% if (IsEditView)
    {%>
        if (!confirm("Are you sure you want to delete this image?"))
            return;

        <% }%>
        $(this).closest(".upload_box").find(".uploadimg").attr('src', '');
        $(this).closest(".upload_box").find("input[type=hidden]").val($(this).closest(".upload_box").find(".uploadimg").attr('imgid'));
        //$(this).attr('deleted', '1');
        $(this).closest(".upload_box").find("input[type=file]").val("");
    });

    $(document).ready(function () {
        $('.select2-show-search').select2({
            minimumResultsForSearch: ''
        });
    });

    $('#<%= txtPrdName.ClientID %>').on('input propertychange paste', function () { buildDisplayName(); });
    $('#<%= txtVarient.ClientID %>').on('input propertychange paste', function () { buildDisplayName(); });
    $('#<%= txtQuantity.ClientID %>').on('input propertychange paste', function () { buildDisplayName(); });
    $('#<%= selUnit.ClientID %>').on('change', function () { buildDisplayName(); });

    function buildDisplayName() {
        var brandname = $('#<%=txtSelectedBrand.ClientID%>').val();
        var varientname = $('#<%=txtVarient.ClientID%>').val();
        var qtyname = $('#<%=txtQuantity.ClientID%>').val();
        var unitname = $("#<%=selUnit.ClientID%> option:selected").text();
        var prdname = $('#<%= txtPrdName.ClientID %>').val();
        var namevals = [];
        if (brandname != 'Generic')
            namevals.push(brandname);
        if (prdname != '')
            namevals.push(prdname);
        if (varientname != '')
            namevals.push(varientname);
        if (qtyname != '')
            namevals.push(qtyname);
        if (unitname != '' && unitname.toLowerCase() != 'select unit')
            namevals.push(unitname);

        var displayname = namevals.join(" "); //(brandname == 'Generic' ? '' : brandname) + ' ' + prdname + ' ' + varientname + ' ' + qtyname + ' ' + unitname;
        $('#<%= txtProductWebName.ClientID%>').val(displayname);

    }
</script>

<script>
    $(function () {
        $(".fileupload_productimage").change(function () {
            let file = $(this), imgControlName = file.data('target');
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = e => {
                    file.parent().addClass("rmvbg").find(imgControlName).attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        $('.remove').click(function () {
            let imgControlName = $(this).data('target');
            $(this).parent().removeClass("rmvbg").find(imgControlName).attr('src', "");
        });
    });
</script>

