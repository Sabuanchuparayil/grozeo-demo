<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BannerSettings.aspx.cs" Inherits="RetalineProAgent.Appearance.BannerSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/appearance">Appearance</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Appearance/Banner">Banner</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Banner</li>--%>
    <a href="/Tenant/Appearance/Banner"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><%= (String.IsNullOrEmpty(Request.QueryString["advId"]) ? "Add Banner" : "Edit Date") %></h6>
        <p class="mb-0"><%= (String.IsNullOrEmpty(Request.QueryString["advId"]) ? "Add Banner" : "Edit Date") %></p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">

        <div class="card-body p-3 shadow_top">

            <div class="d-flex flex-wrap flex-lg-nowrap">

                <div class="form-layout addbanner_form col-12 col-lg-9 rounded">

                    <label class="slim-card-title">Add new banner</label>
                    <!-- <div>
              <small class="mg-b-20 mg-sm-b-40">Please ensure the branch location selected in map using the button 'Load Map'.</small>
            </div> -->

                    <div class="row row-sm mb-3">
                        <div class="col-12 col-md-4">
                            <label for="theme" class="col-form-label">Theme</label>
                        </div>
                        <div class="col-12 col-md-8">
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtTheme" Enabled="false" runat="server" CssClass="form-control" autocomplete="off" />
                            <%--<asp:DropDownList ID="selTheme" DataSourceID="SDSTheme" DataTextField="title" DataValueField="id" runat="server" AppendDataBoundItems="true" CssClass="form-control select2" data-placeholder="Select Theme">
                                <asp:ListItem Value="" Text="Select theme"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ForeColor="Red" ErrorMessage="Please select theme" ControlToValidate="selTheme" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>--%>
                        </div>
                        <%--<asp:SqlDataSource ID="SDSTheme" ProviderName="MySql.Data.MySqlClient" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT id, title, NAME FROM theme"></asp:SqlDataSource>--%>
                    </div>
                    <div class="row row-sm mb-3">
                        <div class="col-12 col-md-4">
                            <label for="selectbannerlocation" class="col-form-label">Select Banner Location</label>
                        </div>
                        <div class="col-12 col-md-8">
                            <asp:DropDownList ID="selBannerLocation" DataSourceID="ODSThemeJson" DataTextField="type" DataValueField="typeId" runat="server" AppendDataBoundItems="true" CssClass="form-control select2" onchange="updateBannerPreview(this)" data-placeholder="Select Banner Location">
                                <asp:ListItem Value="" Text="Select banner location"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ForeColor="Red" ErrorMessage="Please select banner location" ControlToValidate="selBannerLocation" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>
                          </div>
                    </div>
                    <asp:ObjectDataSource ID="ODSThemeJson" runat="server" SelectMethod="GetThemeLocations" TypeName="RetalineProAgent.Appearance.BannerSettings" OnSelecting="ODSThemeJson_Selecting">
                        <SelectParameters>
                            <asp:Parameter Name="themeLocation" Type="String" DefaultValue="themes" />
                            <asp:Parameter Name="storetheme" />
                        </SelectParameters>
                    </asp:ObjectDataSource>
                   <%-- <asp:SqlDataSource ID="SDSAdZone" ProviderName="MySql.Data.MySqlClient" runat="server" OnSelecting="SDSAdZone_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT *, REPLACE(adzone_name, ' ', '_') AS adval FROM `app_adzones` WHERE adzone_type='advertisement' AND adzone_screen = 'Home' AND adzone_status=1 AND adzone_theme=@themeId">
                        <SelectParameters>
                            <asp:Parameter Name="themeId" />
                        </SelectParameters>
                    </asp:SqlDataSource>--%>


                    <div class="row mb-3">
                        <div class="col-12 col-md-4">
                            <label for="Category" class="col-form-label">Category</label>
                        </div>
                        <!--col-lg-4-->

                        <div class="col-12 col-md-8 d-flex flex-wrap flex-lg-nowrap BannerTypeOfCategory">
                            <asp:DropDownList ID="selCatType" runat="server" AutoPostBack="true" CssClass="form-control select2  mb-1 mb-lg-1" data-placeholder="Type of Category">
                                <asp:ListItem Value="">Type of Category</asp:ListItem>
                                <asp:ListItem Value="1">Select Business Category</asp:ListItem>
                                <asp:ListItem Value="2">Select Retail Category</asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ForeColor="Red" CssClass="b--10"  Display="Dynamic" ErrorMessage="Select category type" ControlToValidate="selCatType" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>

                            <asp:DropDownList ID="selBannerBusinessType" DataSourceID="SDSBusinessType" AppendDataBoundItems="true" DataTextField="business_category_name" DataValueField="business_category_id" runat="server" CssClass="form-control select2 ml-lg-2 ">
                                <asp:ListItem Text="All Business Categories" Value="0"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:DropDownList ID="selBannerRetailType" DataSourceID="SDSRetailCategories" AppendDataBoundItems="true" DataTextField="business_type_name" DataValueField="business_type_id" runat="server" CssClass="form-control select2 ml-lg-2">
                                <asp:ListItem Text="All Retail Categories" Value="0"></asp:ListItem>
                            </asp:DropDownList>

                            <asp:SqlDataSource ID="SDSBusinessType" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT business_category_id, business_category_name FROM retaline_business_category where Store_group_Id=@storegroupid"
                                ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSHomeBanners_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" />
                                </SelectParameters>
                            </asp:SqlDataSource>

                            <asp:SqlDataSource ID="SDSRetailCategories" OnSelecting="SDSHomeBanners_Selecting" ProviderName="MySql.Data.MySqlClient" runat="server"
                                ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" SelectCommand="SELECT bt.* FROM `finascop_business_type` bt INNER JOIN `finascop_branch_group_business_type` bgt ON bgt.business_type_id=bt.business_type_id WHERE store_group_id=@storegroupid">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" />
                                </SelectParameters>
                            </asp:SqlDataSource>


                        </div>
                        <!--col-lg-7-->
                    </div>
                    <!--row-->

                    <div class="row row-sm mb-3">
                        <div class="col-12 col-lg-4">
                            <label for="LinkBanner" class="col-form-label">Link Banner on click</label>
                        </div>
                        <!--col-lg-4-->

                        <div class="col-12 col-lg-8">

                            <div class="d-flex align-items-center flex-wrap radiogroup_wrap" style="gap: 8px;">

                                <div class="input_groupe pr-3" hidden="hidden">
                                    <label class="rdiobox m-0">
                                        <asp:RadioButton ID="rdOffer" runat="server" Checked="false" AutoPostBack="true" GroupName="linkType" />
                                        <span>Offer</span>
                                    </label>
                                </div>

                                <div class="input_groupe"  style="gap: 10px;">
                                    <label class="rdiobox m-0 d-flex align-items-center">
                                        <asp:RadioButton ID="rdProducts" runat="server" AutoPostBack="true" GroupName="linkType" />
                                        <span>SKU</span>
                                    </label>
                                </div>

                               <div class="input_groupe d-flex"  style="gap: 10px;">
                                    <label class="rdiobox m-0 d-flex align-items-center">
                                        <asp:RadioButton ID="rdBrand" runat="server" AutoPostBack="true" GroupName="linkType" />
                                        <span>Brand</span>
                                    </label>
                                </div>

                                <div class="input_groupe d-flex"  style="gap: 10px;">
                                    <label class="rdiobox m-0 d-flex align-items-center">
                                        <asp:RadioButton ID="rdSubCategory" runat="server" AutoPostBack="true" GroupName="linkType" />
                                        <span>Subcategory</span>
                                    </label>
                                </div>

                                <div class="input_groupe d-flex"  style="gap: 10px;">
                                    <label class="rdiobox m-0 d-flex align-items-center">
                                        <asp:RadioButton ID="rdCategory" runat="server" AutoPostBack="true" GroupName="linkType" />
                                        <span>Category</span>
                                    </label>
                                </div>

                                <div class="input_groupe d-flex"  style="gap: 10px;">
                                    <label class="rdiobox m-0 d-flex align-items-center">
                                        <asp:RadioButton ID="rdDepartment" runat="server" AutoPostBack="true" GroupName="linkType" />
                                        <span>Department</span>
                                    </label>
                                </div>
                            </div>
                            <!--radiogroup_wrap-->

                            <div class="offer_expant">
                                <div class="d-flex align-items-center mt-3 row row-sm">
                                    <asp:PlaceHolder ID="plcOfferExpand" runat="server">

                                        <div class="input_groupe input-group col-lg-3 pr-2" hidden="hidden">
                                            <input type="text" style="display: none" />
                                            <input type="password" style="display: none" />
                                            <asp:TextBox ID="txtPercentage" runat="server" CssClass="form-control" MaxLength="3" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                            <span class="input-group-text">%</span>
                                            <asp:RequiredFieldValidator runat="server" CssClass="input-group-text" Text="*" Display="Dynamic" ForeColor="Red" ControlToValidate="txtPercentage" ErrorMessage="Percentage value is required" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>
                                        </div>
                                        <div class="input_groupe input-group col-lg-4 px-2" hidden="hidden">
                                            <asp:DropDownList ID="selOfferOn" runat="server" AutoPostBack="true" CssClass="form-control select2" data-placeholder="Select Type">
                                                <asp:ListItem Value="">Select Type</asp:ListItem>
                                                <asp:ListItem Value="Product" Text="Product"></asp:ListItem>
                                                <asp:ListItem Value="Category" Text="Category"></asp:ListItem>
                                                <asp:ListItem Value="Brand" Text="Brand"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" CssClass="input-group-text" Text="*" Display="Dynamic" ForeColor="Red" ControlToValidate="selOfferOn" ErrorMessage="Select type" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>
                                        </div>
                                    </asp:PlaceHolder>

                                    <asp:PlaceHolder ID="plcCategoryExpand" Visible="false" runat="server">
                                        <div class="input_groupe input-group col-lg-5 px-2">
                                            <asp:DropDownList ID="selCategory" runat="server" CssClass="form-control select2" DataSourceID="SDSCat" DataTextField="category_name" AppendDataBoundItems="true" DataValueField="category_id" data-placeholder="Select Category">
                                                <asp:ListItem Value="" Text="Select Category"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selCategory" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Category" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                                                SelectCommand="SELECT pc.category_id, pc.category_name, ppc.parent_category_businessType FROM mypha_productcategory pc
                                                INNER JOIN mypha_productparent_category ppc ON pc.parent_category = ppc.parent_category_id
                                                INNER JOIN finascop_branch_group_business_type gbt ON gbt.business_type_id = ppc.parent_category_businessType
                                                WHERE gbt.store_group_id = @storegroupid AND pc.status = 1 ORDER BY pc.category_name" 
                                                OnSelecting="SDSHomeBanners_Selecting">
                                                <SelectParameters>
                                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                                </SelectParameters>
                                            </asp:SqlDataSource>
                                        </div>
                                    </asp:PlaceHolder>
                                    <asp:PlaceHolder Visible="false" ID="plcProductExpand" runat="server">
                                        <div class="input_groupe input-group col-lg-5 px-2">
                                            <asp:DropDownList ID="selProduct" runat="server" DataSourceID="SDSInventory" DataTextField="stit_SKU" DataValueField="stit_id" CssClass="form-control select2" AppendDataBoundItems="true" data-placeholder="Select SKU">
                                                <asp:ListItem Value="" Text="Select SKU"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selProduct" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select SKU" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                        <asp:SqlDataSource runat="server" ID="SDSInventory" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSHomeBanners_Selecting" ProviderName="MySql.Data.MySqlClient"
                                            SelectCommand="SELECT i.stit_SKU, i.stit_id FROM finascop_stock_itemmaster i INNER JOIN finascop_stock_branch_inventory bi ON i.stit_Id=bi.stit_id 
INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id AND b.br_storeGroup=@storegroupid WHERE stit_status=1 GROUP BY i.stit_id ORDER BY stit_SKU">
                                            <SelectParameters>
                                                <asp:Parameter Name="storegroupid" Type="Int32" DefaultValue="-1" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>

                                    </asp:PlaceHolder>
                                    <asp:PlaceHolder Visible="false" ID="plcBrandExpand" runat="server">
                                        <div class="input_groupe input-group col-lg-5 px-2">
                                            <asp:DropDownList ID="selBrand" runat="server" CssClass="form-control select2" DataSourceID="SDSBrand" DataTextField="brand_name" AppendDataBoundItems="true" DataValueField="brand_id" data-placeholder="Select Brand">
                                                <asp:ListItem Value="" Text="Select Brand"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selBrand" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Brand" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                        <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                                            SelectCommand="SELECT pb.brand_id,pb.brand_name FROM mypha_productbrands pb WHERE EXISTS(SELECT i.* FROM finascop_stock_itemmaster i 
  INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid 
  WHERE i.pdt_brand = pb.brand_id) AND STATUS=1 AND IFNULL(pb.brand_name, '') NOT LIKE '' ORDER BY brand_name"
                                            OnSelecting="SDSHomeBanners_Selecting">
                                            <SelectParameters>
                                                <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>
                                    </asp:PlaceHolder>

                                    <asp:PlaceHolder Visible="false" ID="plcSubcategory" runat="server">
                                        <div class="input_groupe input-group col-lg-5 px-2">
                                            <asp:DropDownList ID="selSubcategory" runat="server" CssClass="form-control select2" DataSourceID="SDSSubCategory" DataTextField="sub_category" AppendDataBoundItems="true" DataValueField="sub_category_id" data-placeholder="Select Sub category">
                                                <asp:ListItem Value="" Text="Select Sub category"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selSubcategory" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Sub category" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                        <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubCategory" ProviderName="MySql.Data.MySqlClient"
                                            SelectCommand="SELECT sc.* FROM mypha_productsubcategory sc 
  INNER JOIN finascop_stock_itemmaster i ON i.product_category = sc.sub_category_id INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID 
  INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid WHERE sc.STATUS=1 GROUP BY sc.sub_category_id ORDER BY sc.sub_category ASC"
                                            OnSelecting="SDSHomeBanners_Selecting">
                                            <SelectParameters>
                                                <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>
                                    </asp:PlaceHolder>

                                    <asp:PlaceHolder Visible="false" ID="plcDepartment" runat="server">
                                        <div class="input_groupe input-group col-lg-5 px-2">
                                            <asp:DropDownList ID="selDepartment" runat="server" CssClass="form-control select2" DataSourceID="SDSDepartment" DataTextField="parent_category" AppendDataBoundItems="true" DataValueField="parent_category_id" data-placeholder="Select Department">
                                                <asp:ListItem Value="" Text="Select Department"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selDepartment" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Department" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                        <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSDepartment" ProviderName="MySql.Data.MySqlClient"
                                            SelectCommand="SELECT gbt.store_group_id, mppc.parent_category_id, mppc.parent_category_businessType, mppc.parent_category FROM mypha_productparent_category mppc
                                            INNER JOIN finascop_branch_group_business_type gbt ON gbt.business_type_id = mppc.parent_category_businessType
                                            WHERE gbt.store_group_id = @storegroupid AND mppc.status = 1 ORDER BY mppc.parent_category"
                                            OnSelecting="SDSHomeBanners_Selecting">
                                            <SelectParameters>
                                                <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>
                                    </asp:PlaceHolder>
                                </div>
                            </div>


                        </div>
                        <!--col-lg-7-->
                    </div>
                    <!--row-->

                    <div class="row row-sm mb-3">
                        <div class="col-12 col-lg-4 flex-wrap d-flex align-items-center mb-3 mb-lg-0">

                            <div class="input_groupe pr-3 mb-1">
                                <label class="rdiobox m-0">
                                    <input type="radio" runat="server" id="rdAddsRangNo" name="AddsRang" checked="" class="mr-0 adsrange">
                                    <%--<asp:RadioButton ID="rdAdsRangePermanent" runat="server" GroupName="AddsRang" CssClass="mr-0 adsrange" />--%>
                                    <span>Permanent</span>
                                </label>
                            </div>
                            <div class="input_groupe">
                                <label class="rdiobox m-0">
                                    <input type="radio" runat="server" id="rdAddsRangYes" name="AddsRang" class="mr-0 adsrange">
                                    <%--<asp:RadioButton ID="rdAdsRangeDate" runat="server" GroupName="AddsRang" CssClass="mr-0 adsrange" />--%>
                                    <span>Date Range</span>
                                </label>
                            </div>

                        </div>
                        <!--col-lg-6-->

                        <div class="col-12 col-lg-8 d-flex align-items-center flex-wrap flex-lg-nowrap daterangecaldr">

                            <div class="wd-250">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <%--<div class="input-group-text">
                        <i class="icon ion-calendar tx-16 lh-0 op-6"></i>
                      </div>--%>
                                    </div>
                                    <asp:TextBox ID="txtDateFrom" runat="server" CssClass="form-control dateinput" TextMode="Date" disabled="disabled" />
                                    <%--<asp:TextBox ID="txtDateFrom" runat="server" CssClass="form-control fc-datepicker dateinput" disabled="disabled" placeholder="MM/DD/YYYY"></asp:TextBox>--%>
                                    <%--<input id="txtPassportNumber" type="text" class="form-control fc-datepicker dateinput" disabled="disabled" placeholder="MM/DD/YYYY">--%>
                                    <asp:RequiredFieldValidator ID="reqDateFrom" Width="100%" Enabled="false" runat="server" ValidationGroup="AddBanner" ControlToValidate="txtDateFrom" CssClass="b--15" ErrorMessage="Select from date" Display="Dynamic" ForeColor="Red" Visible="false"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <!-- wd-250 -->

                            <span class="p-2">To</span>

                            <div class="wd-250">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <%--<div class="input-group-text">
                        <i class="icon ion-calendar tx-16 lh-0 op-6"></i>
                      </div>--%>
                                    </div>
                                    <asp:TextBox ID="txtDateTo" runat="server" CssClass="form-control dateinput" TextMode="Date" disabled="disabled" />
                                    <%--<asp:TextBox ID="txtDateTo" runat="server" CssClass="form-control fc-datepicker2 dateinput" disabled="disabled" placeholder="MM/DD/YYYY"></asp:TextBox>--%>
                                    <%--<input type="text" class="form-control fc-datepicker2 dateinput" disabled="disabled" placeholder="MM/DD/YYYY">--%>
                                    <asp:RequiredFieldValidator ID="reqDateTo" runat="server" Width="100%" Enabled="false" ValidationGroup="AddBanner" ControlToValidate="txtDateTo" CssClass="b--15" ErrorMessage="Select to date" Display="Dynamic" ForeColor="Red" Visible="false"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <!-- wd-250 -->

                        </div>

                    </div>
                    <!--row-->


                    <div class="row">
                        <div class="col-12">
                            <div class="banner_list UploadBanner border d-flex">

                                <div class="uploadbanner_wrap rounded">
                                    <span class="btn_upload">
                                        <span id="image_size_dimension"></span>
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="BannerImgUploade" ForeColor="Red" Display="Dynamic" CssClass="imgerror" ErrorMessage="Please select image to upload" ValidationGroup="AddBanner"></asp:RequiredFieldValidator>
                                        <asp:FileUpload runat="server" ID="BannerImgUploade" ClientIDMode="Static" title="Select image" data-target="#BannerImgPreview" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg" />
                                        <%--<input type="file" id="BannerImgUploade" title="" data-target="#BannerImgPreview" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg">--%>
                                    </span>
                                    <div class="ImgPreview_wap">
                                        <img id="BannerImgPreview" src="" class="preview_img">
                                    </div>
                                    <div class="remove_preview_wrap">
                                        <a data-target="#BannerImgPreview" href="javascript: void(0)" data-file="#BannerImgUploade" class="btn_rmv_remove"><i class="icon ion-trash-a"></i>Delete Banner</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-12 form-group mb-0 mt-4">
                            <div class="input-group w-auto d-inline-block">                                
                                <a href="/Tenant/Appearance/Banner" class="btn btn-secondary float-right">Cancel</a>
                                <asp:Button ID="btnSaveBanner" runat="server" Text="Save Changes" OnClick="btnSaveBanner_Click" ValidationGroup="AddBanner" CssClass="btn btn-primary float-right mr-2" />
                                <%--<input type="submit" name="ctl00$" value="Save changes" id="" class="btn btn-success float-right ml-2">  
                  <input type="submit" name="ctl00$" value="Cancel" id="" class="btn btn-secondary float-right">--%>
                            </div>
                        </div>

                    </div>


                    <script src="/Content/lib/jquery/js/jquery-ui.js"></script>
                  

                    <script>
                        function readURL(input, imgControlName) {
                            var selectedOption = $("#<%= selBannerLocation.ClientID%>")[0].selectedIndex;
                            var selectedText = $("#<%= selBannerLocation.ClientID%> option:selected").text().trim();

                            var objSelLocation = null;

                            for (var key in bannerData) {
                                if (bannerData.hasOwnProperty(key)) {
                                    var item = bannerData[key];
                                    if (item && item.name && item.name.trim() === selectedText) {
                                        objSelLocation = item;
                                        break;
                                    }
                                }
                            }

                            // Fallback if not matched
                            if (!objSelLocation) {
                                objSelLocation = bannerData["default"];
                            }

                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    var image = new Image();
                                    image.src = e.target.result;
                                    image.onload = function () {
                                        if (selectedOption > 0 && objSelLocation) {
                                            if ((this.width === objSelLocation.width) && (this.height === objSelLocation.height)) {
                                                $(imgControlName).attr('src', this.src);
                                                $(input).closest('.uploadbanner_wrap').find('.btn_rmv_remove').addClass('rmv');
                                                $(input).parent('.btn_upload').addClass('rmvbg');
                                            } else {
                                                alert('Image size must be ' + objSelLocation.width + 'px X ' + objSelLocation.height + 'px');
                                                $(".input-img").val('');
                                                $(input).closest('.uploadbanner_wrap').find('.btn_rmv_remove').removeClass('rmv');
                                                $(input).parent('.btn_upload').removeClass('rmvbg');
                                            }
                                        }
                                    };
                                };
                                reader.readAsDataURL(input.files[0]);
                            }
                        }


                        $(".input-img").change(function () {
                            var imgControlName = $(this).data('target');
                            readURL(this, imgControlName);
                            $(this).closest('.uploadbanner_wrap').find('.btn_rmv_remove').attr('hiddenfld', 1);
                        });

                        $(".btn_rmv_remove").click(function (e) {
                            var hiddenfld = $(this).attr('hiddenfld');
                            if (hiddenfld && hiddenfld != '') {
                                if (!confirm('Are you sure you want to delete this image?')) {
                                    return false;
                                }
                                $(this).removeAttr('hiddenfld');
                            }
                            e.preventDefault();
                            var imgFile = $(this).data('target');
                            var imgSelect = $(this).data('file');
                            $(imgSelect).val("");
                            $(imgFile).attr("src", "");
                            $(this).removeClass('rmv');
                            $(imgSelect).parent('.btn_upload').removeClass('rmvbg');
                        });

                        
                        $('.uploadbanner_wrap .input-img').on('click', function (e) {
                            if ($("#<%= selBannerLocation.ClientID%>").val() == '') {
                        alert("Please select image location first");
                        return false;
                    }
                });
                        //$('.uploadbanner_wrap .input-img').prop('disabled', true);

                        $("#<%= selBannerLocation.ClientID%>").change(function () {
                            $('.uploadbanner_wrap .input-img').val('');
                            var content = '';
                            var selectedOption = $(this).val();
                            var selectedIndx = $("#<%= selBannerLocation.ClientID%>")[0].selectedIndex; //$(this).val();
                    var selectedlocation = $("#<%= selBannerLocation.ClientID%> option:selected").text();
                    var objSelLocation = imagedata.find(item => item.type === selectedlocation.toLowerCase());

                    //$(".btn_rmv_remove").click();

                    if (selectedIndx >= 0 && objSelLocation) {
                        //$('.uploadbanner_wrap .input-img').prop('disabled', false);
                        $('#imgBannerLocation').attr('src', '<%= ViewState["ThemeBannerPreview"] %>');
                        content = '(size ' + objSelLocation.width + 'px X ' + objSelLocation.height + 'px) Max file size 150 kb';
                    }
                    else {
                        $('#imgBannerLocation').attr('src', '<%= ViewState["ThemeBannerPreview"] %>');
                        //$('.uploadbanner_wrap .input-img').prop('disabled', true);
                    }
                    $('#image_size_dimensions').text(content);

                    $(".btn_rmv_remove").removeAttr('hiddenfld');
                    var imgFile = $(".btn_rmv_remove").data('target');
                    var imgSelect = $(".btn_rmv_remove").data('file');
                    $(imgSelect).val("");
                    $(imgFile).attr("src", "");
                    $(".btn_rmv_remove").removeClass('rmv');
                    $(imgSelect).parent('.btn_upload').removeClass('rmvbg');

                        });

                        var imagedata = [
                            {},
                            {
                                'width': 1260,
                                'height': 380,
                                'size': 500000,
                                'type': 'home top banner',
                                'img': 'Home_Top_Banner.png'
                            },
                            {
                                'width': 197,
                                'height': 286,
                                'size': 500000,
                                'type': 'home left banner',
                                'img': 'Home_Left_Banner.png'
                            },
                            {
                                'width': 830,
                                'height': 299,
                                'size': 500000,
                                'type': 'offer 1',
                                'img': 'Offer_1.png'
                            },
                            {
                                'width': 320,
                                'height': 217,
                                'size': 500000,
                                'type': 'offer 2',
                                'img': 'Offer_2.png'
                            },
                            {
                                'width': 513,
                                'height': 217,
                                'size': 500000,
                                'type': 'offer 3',
                                'img': 'Offer_3.png'
                            },
                            {
                                'width': 407,
                                'height': 531,
                                'size': 500000,
                                'type': 'offer 4',
                                'img': 'Offer_4.png'
                            },
                            {
                                'width': 197,
                                'height': 286,
                                'size': 500000,
                                'type': 'side small banner',
                                'img': 'Home_Left_Banner.png'
                            },
                            {
                                'width': 197,
                                'height': 286,
                                'size': 500000,
                                'type': 'inner left banner',
                                'img': 'Home_Left_Banner.png'
                            }
                        ];


                        $(document).ready(function () {
                            var content = '';
                            var selLocIndex = $("#<%= selBannerLocation.ClientID%>")[0].selectedIndex;
                    var selectedlocation = $("#<%= selBannerLocation.ClientID%> option:selected").text();
                    var objSelLocation = imagedata.find(item => item.type === selectedlocation.toLowerCase());

                    if (selLocIndex > 0 && objSelLocation) {
                        //$('.uploadbanner_wrap .input-img').prop('disabled', false);
                        $('#imgBannerLocation').attr('src', '<%= ViewState["ThemeBannerPreview"] %>');
                        content = '(size ' + objSelLocation.width + 'px X ' + objSelLocation.height + 'px) Max file size 150 kb';
                    }
                    else {
                        $('#imgBannerLocation').attr('src', '<%= ViewState["ThemeBannerPreview"] %>');
                        //$('.uploadbanner_wrap .input-img').prop('disabled', true);
                    }
                    $('#image_size_dimensions').text(content);
                });

                        $(function () {
                            $("input.adsrange").click(function () {
                                if ($("#<%= rdAddsRangYes.ClientID %>").is(":checked")) {
                            $(".dateinput").removeAttr("disabled");

                            ValidatorEnable(document.getElementById('<%= reqDateFrom.ClientID%>'), true);
                            ValidatorEnable(document.getElementById('<%= reqDateTo.ClientID%>'), true);

                            $("#<%= txtDateFrom.ClientID%>").focus();

                        } else {
                            $(".dateinput").attr("disabled", "disabled");
                            ValidatorEnable(document.getElementById('<%= reqDateFrom.ClientID%>'), false);
                            ValidatorEnable(document.getElementById('<%= reqDateTo.ClientID%>'), false);
                        }
                    });
                });


                        $(function () {
                            'use strict'

                            // Datepicker
                            $('.fc-datepicker').datepicker({
                                showOtherMonths: true,
                                selectOtherMonths: true
                            });

                            $('.fc-datepicker2').datepicker({
                                showOtherMonths: true,
                                selectOtherMonths: true
                            });

                        });
                    </script>
                    <style>
                        #image_size_dimension {
                            width: 300px;
                        }
                        .banner_list .btn_upload {
                            overflow:visible;
                        }
                        .imgerror {
                            left:-10px;
                            bottom:-25px!important;
                        }
                    </style>

                </div>
                <!-- form-layout -->

                <div class="col-12 col-lg-3 banner_prv_sec rounded">
                    <div class="bannerviewwrap">
                        <label class="form-label w-100 text-center font-weight-bold text-dark mb-3" runat="server" id="txtloadtheme"></label>
                        <div class="banner_view_intheme">
                        <img id="imgLocation" class="mx-wd-100p" src="" />
                        </div>
                    </div>
                    <!--bannerviewwrap-->
                </div>
            </div>
        </div>
    </div>

     <script type="text/javascript">
         // Inject banner data from server
         var bannerData = [];
         var bannerData = <%= GetBannerJsObject() %>;

         // Function to update banner preview and size
         function updateBannerPreview(dropdown) {

             if (!bannerData || typeof bannerData !== "object") {
                 console.warn("bannerData is not valid.");
                 return;
             }
             var selectedValue = dropdown?.value || ""; // handle null safely

             var banner = bannerData[selectedValue] || bannerData["default"];

             var imgElement = document.getElementById('imgLocation');
             var sizeLabel = document.getElementById('image_size_dimension');
             var banner = bannerData[selectedValue] || bannerData["default"];

             if (!imgElement) {
                 console.warn("imgBannerLocation not found");
                 return;
             }
             if (banner && banner.url) {
                 var encodedUrl = banner.url.replace(/ /g, '%20');
                 imgElement.src = encodedUrl;
                 sizeLabel.innerText = "Size: " + banner.width + "px × " + banner.height + "px";

             } else {
                 imgElement.src = '/content/images/theme_banner_view/DefaultNew/nobanner.png';
                 sizeLabel.innerText = "";
             }
         }
         $(document).ready(function () {
             var dropdown = document.getElementById("<%= selBannerLocation.ClientID %>");
             if (!dropdown || !dropdown.value || !bannerData[String(dropdown.value)]) {
                 updateBannerPreview(null); 
             } else {
                 updateBannerPreview(dropdown);
             }

         });
     </script>
</asp:Content>