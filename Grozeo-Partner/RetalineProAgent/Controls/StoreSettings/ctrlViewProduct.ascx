<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlViewProduct.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlViewProduct" %>
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
                                <asp:TextBox ID="txtPrdName" AutoPostBack="true" runat="server" Enabled="false" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                                <asp:Label ID="lblProductNameResult" runat="server" ForeColor="Red"></asp:Label>
                            </div>
                        </div>
                        <!-- col-4 -->


                    <div class="col-lg-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Retailer Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selRetCat" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRetCat" DataTextField="business_type_name" AppendDataBoundItems="true" DataValueField="business_type_id"><asp:ListItem Text="Select retailer category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSRetCat" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt
INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id
WHERE bt.status=1 ORDER BY bt.business_type_name">
                    </asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group-sm">
                    <label class="form-control-label">Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selCat" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCat" DataTextField="category_name" DataValueField="category_id"><asp:ListItem Text="Select category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc
                            INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id WHERE pc.status='1'">
                        </asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-sm-4">
                    <div class="form-group">
                    <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selSubCat" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" DataValueField="sub_category_id"><asp:ListItem Text="Select sub category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT msc.sub_category_id,msc.sub_category,msc.main_category,pc.category_name,pc.category_id FROM mypha_productsubcategory msc
                                        INNER JOIN mypha_productcategory pc ON pc.category_id=msc.main_category WHERE msc.status=1">
                        </asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Varient: <%--<span class="tx-danger">*</span>--%></label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtVarient" runat="server" CssClass="form-control" Enabled="false" placeholder="Enter varient" autocomplete="off"/>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtQuantity" Enabled="false" runat="server" CssClass="form-control" placeholder="Enter quantity" autocomplete="off"/>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Unit: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selUnit" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id"><asp:ListItem Text="Select unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
                    <!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">HSN/SAC: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selHSN" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id"><asp:ListItem Text="Select HSN/SAC" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: <%--<span class="tx-danger">*</span>--%></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                        <asp:DropDownList ID="selType" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSType" DataTextField="hsnGst" DataValueField="id"></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSType" ProviderName="MySql.Data.MySqlClient" 
                        SelectCommand="SELECT id, hsnGst, hsnId, hsnCess FROM hsn_value ORDER BY id">
                        </asp:SqlDataSource>
                    </div>
                    </div><!-- col-4 -->
    
                    <div class="col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Barcode</label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtBarcode" runat="server" Enabled="false" autocomplete="off" CssClass="form-control"/>
                    </div>
                    </div><!-- col-4 -->

                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Return Days: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtReturn" runat="server" Enabled="false" autocomplete="off" CssClass="form-control"/>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Edible: </label>
                    <asp:DropDownList ID="selFoodType" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="">Select from list</asp:ListItem>
                              <asp:ListItem Value="0">Not Edible</asp:ListItem>
                              <asp:ListItem Value="4">Edible</asp:ListItem>
                              <asp:ListItem Value="1">Edible - Vegetarian</asp:ListItem>
                              <asp:ListItem Value="2">Edible - Non Vegetarian</asp:ListItem>
                              <asp:ListItem Value="3">Edible - Vegan</asp:ListItem>
                          </asp:DropDownList>
                    </div>
                    </div><!-- col-4 -->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Country of Orgin: </label>
                    <asp:DropDownList ID="selCountry" Enabled="false" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" DataValueField="country_id"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
                    </div>
                    </div><!-- col-3-->
                    <div class="col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Delivery Mode: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selDelMode" Enabled="false" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="">Select delivery mode</asp:ListItem>
                              <asp:ListItem Value="1" Enabled="true">Courier</asp:ListItem>
                              <asp:ListItem Value="2">Express</asp:ListItem>
                              <asp:ListItem Value="3" Enabled="true">Both</asp:ListItem>
                          </asp:DropDownList>
                    </div>
                    </div><!-- col-4 -->
    
                        <div class="col-lg-12">
                            <div class="form-group-sm mb-3">
                                <label class="form-control-label">Product name to display on website: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtProductWebName" Enabled="false" runat="server" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3" runat="server" visible="false">
                            <label class="form-control-label mb-0 mb-lg-4 w-100"></label>
                            <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" Enabled="false" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->

    
                    <div class="col-lg-4">
    
                    <div class="form-group">
                    <label class="form-control-label">Short Description <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtShortDescription" Enabled="false" runat="server" CssClass="form-control" Height="235px" TextMode="MultiLine"/>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="form-group m-0">
                    <label class="form-control-label">Long Description</label>
    
                    <asp:TextBox ID="summernote" runat="server" Enabled="false" ClientIDMode="Static" CssClass="form-control" Height="235px" TextMode="MultiLine"/>
    
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
                                <asp:FileUpload accept="image/*" runat="server" Enabled="false" ID="imgUpload1" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd1" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg1" runat="server" />
                                <asp:Image runat="server" ID="productImg1" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" Enabled="false" ID="imgUpload2" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd2" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg2" runat="server" />
                                <asp:Image runat="server" ID="productImg2" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" Enabled="false" ID="imgUpload3" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd3" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg3" runat="server" />
                                <asp:Image runat="server" ID="productImg3" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" Enabled="false" ID="imgUpload4" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd4" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg4" runat="server" />
                                <asp:Image runat="server" ID="productImg4" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box">
                                <asp:FileUpload accept="image/*" runat="server" Enabled="false" ID="imgUpload5" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd5" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg5" runat="server" />
                                <asp:Image runat="server" ID="productImg5" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                          </div><!--upload_box_wrap-->
                        </div><!--upload_imgsec-->
                      </div><!--uploadsec-->
                    </div>
                    </div><!-- row -->
                    </div><!-- form-layout -->

    


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


