<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlCreateProduct.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlCreateProduct" %>
                    <div class="form-layout">
                    <div class="row row-sm ">

                        <div class="col-sm-6 col-lg-4 mb-3">
                            <div class="form-group-sm">
                                <label class="form-control-label" style="width: 100%;">Brand: <span class="tx-danger">*</span> 
                                    <span class="addbrandpopup" data-toggle="modal" runat="server" visible="false" data-target="#addbrand" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Add Brand</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtSelectedBrand" runat="server" CssClass="form-control" Enabled="false" autocomplete="off" />
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-4 mb-3">
                            <div class="form-group-sm">
                                <label class="form-control-label">Product Name: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtPrdName" Enabled='<%# ViewType != ViewMode.Duplicate %>' runat="server" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtPrdName" ForeColor="Red" ErrorMessage="Product name is required" runat="server"></asp:RequiredFieldValidator>
                                <asp:Label ID="lblProductNameResult" runat="server" ForeColor="Red"></asp:Label>
                            </div>
                        </div>
                        <!-- col-4 -->
    
                    <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                    <label class="form-control-label">Varient: <%--<span class="tx-danger">*</span>--%></label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtVarient" runat="server" CssClass="form-control" placeholder="Enter varient" autocomplete="off"/>
    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtVarient" ForeColor="Red" ErrorMessage="Input varient" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->

                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <div class="d-flex" style="margin-bottom: 2px;">
                                    <div class="d-flex mr-3">
                                        <label class="rdiobox text-nowrap">
                                            <asp:RadioButton ID="rbQty" runat="server" Checked="true" GroupName="Qtysize" OnCheckedChanged="rbQty_CheckedChanged" AutoPostBack="true" />
                                            <span class="p-0">Qty Unit</span>
                                        </label>
                                        <span class="tx-danger p-0">*</span>
                                    </div>
                                    <div class="d-flex">
                                        <label class="rdiobox text-nowrap">
                                            <asp:RadioButton ID="rbSize" runat="server" GroupName="Qtysize" OnCheckedChanged="rbSize_CheckedChanged" AutoPostBack="true" />
                                            <span class="p-0">Size</span>
                                        </label>
                                        <span class="tx-danger p-0">*</span>
                                    </div>
                                </div>
                                
                                <asp:DropDownList ID="selUnit" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSUnit" DataTextField="unit_name" DataValueField="unit_id" OnDataBound="selUnit_DataBound" AutoPostBack="true" OnSelectedIndexChanged="selUnit_SelectedIndexChanged">
                                    <asp:ListItem Text="Select Unit" Value=""></asp:ListItem>
                                </asp:DropDownList>

                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSUnit" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT unit_id, unit_name FROM mypha_unit ORDER BY unit_name "></asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selUnit" ForeColor="Red" ErrorMessage="Select unit" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->

                        <asp:Panel ID="pnlQuantityInput" runat="server" CssClass="col-sm-6 col-lg-2">
                            <div class="form-group mg-b-10-force">
                                <label class="form-control-label">Quantity / Size: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtQuantity" runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" CssClass="form-control" placeholder="Enter quantity" autocomplete="off" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtQuantity" ForeColor="Red" ErrorMessage="Input quantity" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </asp:Panel>

                        <asp:Panel ID="pnlQuantitySelect" runat="server" CssClass="col-sm-6 col-lg-2">
                            <div class="form-group mg-b-10-force">
                                <label class="form-control-label">Quantity: <span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selQuantity" runat="server" CssClass="form-control select2"  ForeColor="GrayText" DataSourceID="SDSQuantity" DataTextField="value" DataValueField="id" OnDataBound="selQuantity_DataBound">
                                    <asp:ListItem Text="Select Quantity" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  runat="server" ID="SDSQuantity" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT * FROM unit_value WHERE unitId = @unitId">
                                    <SelectParameters>
                                        <asp:ControlParameter Name="unitId" ControlID="selUnit" DefaultValue="0" ConvertEmptyStringToNull="false" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selQuantity" ForeColor="Red" ErrorMessage="Select quantity" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </asp:Panel>
                    <!-- col-4 -->
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Display Quantity</label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtDisplayQty" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter display quantity" />
                            </div>
                        </div>
                    <div class="col-sm-6 col-lg-2">
                        <%--<div class="form-group"> 
                            <label class="form-control-label" style="width: 100%;">HSN/SAC: <span class="tx-danger">*</span>
                            <span class="addhsnpopup" data-toggle="modal" runat="server" data-target="#hsnsearch" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Search</span></label>
                            <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selHSN" runat="server" AutoPostBack="True" CssClass="form-control select2-show-search" data-placeholder="HSN/SAC" ForeColor="GrayText" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id" OnDataBound="selHSN_DataBound"><asp:ListItem Text="Select HSN/SAC" AppendDataBoundItems="true"
                                    OnSelectedIndexChanged="selHSN_SelectedIndexChanged"></asp:ListItem></asp:DropDownList>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn ORDER BY hsn_code"></asp:SqlDataSource>
                            <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selHSN" ForeColor="Red" ErrorMessage="Select HSN/SAC" runat="server"></asp:RequiredFieldValidator>
                        </div>--%>
                        <div class="form-group">
                                <label class="form-control-label" style="width: 100%;">HSN/SAC: <span class="tx-danger">*</span>
                            <span class="addhsnpopup" data-toggle="modal" runat="server" data-target="#hsnsearch" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Search</span></label>
                                <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selHSN" runat="server" CssClass="form-control select2-show-search" data-placeholder="HSN/SAC" ForeColor="GrayText" AutoPostBack="true" DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id" AppendDataBoundItems="true" OnSelectedIndexChanged="selHSN_SelectedIndexChanged">
                                    <asp:ListItem Text="Select HSN/SAC" Value="" />
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn"  ProviderName="MySql.Data.MySqlClient" 
                                    SelectCommand="SELECT hsn_id, hsn_code FROM finascop_hsn ORDER BY hsn_code">
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selHSN" ErrorMessage="Select HSN/SAC" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                    </div><!-- col-4 -->
                        <%--<div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: </label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selType" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSType" DataTextField="hsnGst" DataValueField="id" AutoPostBack="true" OnSelectedIndexChanged="selType_SelectedIndexChanged"></asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSType" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT id, hsnGst, hsnId, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id">
                                    <SelectParameters>
                                        <asp:ControlParameter Name="hsnId" ControlID="selHSN" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                            </div>
                        </div>--%>

                        <div class="col-sm-2">
                            <div runat="server" class="form-group">
                                <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> %: </label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:DropDownList ID="selType" Visible="true" runat="server" CssClass="form-control select2" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selType_SelectedIndexChanged" OnDataBound="selType_DataBound">
                                    <asp:ListItem Text="Select Tax" Value="" />
                                </asp:DropDownList>
                                <asp:TextBox ID="txtTax" runat="server" CssClass="form-control" Visible="false" Enabled="false"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selType" ErrorMessage="Select Tax" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-2" runat="server" visible='<%# ConfigurationManager.AppSettings["CountryCode"] == "IN" %>'>
                            <div class="form-group">
                                <label class="form-control-label">CESS</label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtCESS" runat="server" autocomplete="off" CssClass="form-control" Enabled="false" />
                            </div>
                        </div>
    
                    <div class="col-sm-6 col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Barcode</label>
                        <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtBarcode" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter barcode"/>
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

                    <div class="col-sm-6 col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Return Days: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtReturn" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter return days"/>
                    <%--<asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtReturn" ErrorMessage="Retun day/s required" runat="server"></asp:RequiredFieldValidator>--%>
                    </div>
                    </div><!-- col-4 -->
    
    
    
                    <div class="col-sm-6 col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Edible: <span class="tx-danger">*</span></label>
                    <asp:DropDownList  ID="selFoodType" Enabled='<%# ViewType != ViewMode.Duplicate %>' runat="server" CssClass="form-control select2" ForeColor="GrayText">
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
                    <div class="col-sm-6 col-lg-2">
                    <div class="form-group">
                    <label class="form-control-label">Country of Origin: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selCountry" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCountry" DataTextField="country_name" DataValueField="country_id" OnDataBound="selCountry_DataBound"><asp:ListItem Text="Select country of orgin" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCountry" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT country_id,country_name FROM finascop_country WHERE STATUS = 1 ORDER BY country_name"></asp:SqlDataSource>
                    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCountry" ForeColor="Red" ErrorMessage="Select country" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-3-->
                    <div class="col-sm-6 col-lg-2">
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
                        <div class="col-sm-6 col-lg-2">
                     <div class="form-group">
                    <label class="form-control-label">Package weight (kg):<span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtpackageweigt" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter Package weight"/>
                    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtpackageweigt" ErrorMessage="Enter Package weight" runat="server"></asp:RequiredFieldValidator>
                    </div>                        
                       </div>
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Package Length(CM):<span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtPackageLength" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter package length" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtPackageLength" ErrorMessage="Enter package length" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Package Height(CM):<span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtPackageHeight" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter package height" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtPackageHeight" ErrorMessage="Enter package height" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Package Width(CM):<span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtPackageWidth" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter package width" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ForeColor="Red" ControlToValidate="txtPackageWidth" ErrorMessage="Enter package width" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-2">
                            
                                <div class="form-group w-100 mb-0">
                                    <label class="form-control-label">Select Packing Type: <span class="tx-danger">*</span></label>
                                    <asp:DropDownList ID="ddlPackingType" runat="server" CssClass="form-control">
                                        <asp:ListItem Text="-- Select Packing Type --" Value="0"></asp:ListItem>
                                        <asp:ListItem Text="Pack the items independently" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Pack same items together" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Group Packing" Value="3"></asp:ListItem>
                                    </asp:DropDownList>
                                </div>
                        </div>

                        <%--<div class="col-lg-12">
                               <label class="form-control-label">Select Packing Type: <span class="tx-danger">*</span></label>
                            <div class="d-flex p-2 flex-wrap flex-md-nowrap border border-secondary rounded mb-3">
                        <div class="form-group w-100 mb-0">
                            <div class="d-flex mt-2 flex-wrap flex-md-nowrap">
                            <label class="rdiobox mr-4">
                                <asp:RadioButton ID="rbpackindependently" runat="server" GroupName="rbpackingtype" />
                                <span>Pack the items independently</span>                                                                                   
                            </label>
                            <label class="rdiobox mr-4">
                                <asp:RadioButton ID="rbpackingtogather" runat="server" GroupName="rbpackingtype" />
                                <span>Pack same items together</span>
                            </label>
                                  <label class="rdiobox">
                                    <asp:RadioButton ID="rbdefault" runat="server" GroupName="rbpackingtype" />
                                    <span>Group Packing</span>
                                </label>
                            </div>
                        </div>                      
                    </div>
                         </div>--%>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group-sm mb-3">
                                <label class="form-control-label">Product name to display on website: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtProductWebName" runat="server" CssClass="form-control" placeholder="Enter product name" autocomplete="off" />
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtProductWebName" ForeColor="Red" ErrorMessage="Product name is required" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <%--<div class="col-sm-6 col-lg-4 d-flex mb-3">
                            <div class="form-group-sm w-100">
                                <label class="form-control-label">Search the Product Categorisation:</label>
                                <div class="input-group">
                                    <asp:TextBox runat="server" ID="txtsearchinput" CssClass="form-control" placeholder="Enter Possible Product Details" autocomplete="off" value=""></asp:TextBox>
                                </div>
                                <div class="input-group-append">
                                    <asp:Button runat="server" ID="btnhidecategory" OnClick="btnhidecategory_Click" CssClass="btn btn-secondary btn-drk-green ml-2 hidden" Text="Find Category" />
                                </div>
                            </div>
                        </div>
                        </div>--%>
                        <div class="col-sm-6 col-lg-4 d-flex mb-3">
                            <div class="form-group-sm w-100">
                                <label class="form-control-label">Search the Product Categorisation:</label>
                                <div class="input_search_box">
                                    <asp:TextBox runat="server" ID="txtsearchinput" CssClass="form-control" placeholder="Enter Possible Product Details" autocomplete="off" value=""></asp:TextBox>
                                    <asp:Button runat="server" ID="btnhidecategory" OnClick="btnhidecategory_Click" CssClass="btn btn-secondary btn-drk-green" Text="Search" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group-sm">
                                <label class="form-control-label">Retailer Category: <span class="tx-danger">*</span></label>
                                <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selRetCat" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRetCat" DataTextField="business_type_name" AppendDataBoundItems="true" DataValueField="business_type_id">
                                    <asp:ListItem Text="Select retailer category" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSRetCat" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt
                                                    INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id
                                                    WHERE gbt.store_group_id=@storegroup AND bt.status=1 ORDER BY bt.business_type_name"
                                    OnSelecting="SDSRetCat_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selRetCat" ForeColor="Red" ErrorMessage="Select retailer category" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group-sm">
                                <label class="form-control-label">Department: <span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selDepartment" Enabled='<%# ViewType != ViewMode.Duplicate %>' runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSDepartment" DataTextField="parent_category" OnDataBound="selDepartment_DataBound" DataValueField="parent_category_id">
                                    <asp:ListItem Text="Select department" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSDepartment" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT parent_category_id, parent_category_businessType, parent_category FROM mypha_productparent_category 
                                            INNER JOIN finascop_branch_group_business_type gbt ON gbt.business_type_id=parent_category_businessType
                                            WHERE store_group_id=@storegroup AND parent_category_businessType=@business_type_id AND STATUS=1"
                                    OnSelecting="SDSDepartment_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                        <asp:ControlParameter Name="business_type_id" ControlID="selRetCat" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selDepartment" ForeColor="Red" ErrorMessage="Select department" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group-sm">
                                <label class="form-control-label">Category: <span class="tx-danger">*</span></label>
                                <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" OnDataBound="selCat_DataBound" DataSourceID="SDSCat" DataTextField="category_name" DataValueField="category_id">
                                    <asp:ListItem Text="Select category" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc
                                                INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id WHERE pc.parent_category=@department AND pc.status=1
                                             AND (@isNoGST = 0 OR pc.category_id IN (SELECT main_category FROM mypha_productsubcategory WHERE isNonGstRetailer = 1 and status=1 ))"
                                                        OnSelecting="SDSCat_Selecting">
                                    <SelectParameters>
                                        <asp:ControlParameter Name="department" ControlID="selDepartment" DefaultValue="0" />
                                        <asp:Parameter Name="isNoGST" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selCat" ForeColor="Red" ErrorMessage="Select category" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-2">
                            <div class="form-group-sm">
                                <label class="form-control-label">Sub Category: <span class="tx-danger">*</span></label>
                                <asp:DropDownList Enabled='<%# ViewType != ViewMode.Duplicate %>' ID="selSubCat" runat="server" AutoPostBack="true" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSSubCat" DataTextField="sub_category" AppendDataBoundItems="false" OnDataBound="selSubCat_DataBound" DataValueField="sub_category_id" OnSelectedIndexChanged="selSubCat_SelectedIndexChanged">
                                    <asp:ListItem Text="Select sub-category" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSubCat" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSSubCat_Selecting"
                                    SelectCommand="SELECT sub_category_id,sub_category, hasRestaurantService FROM mypha_productsubcategory WHERE main_category=@catid AND status=1 and (@isNoGST = 0 or isNonGstRetailer = 1) order by sub_category">
                                    <SelectParameters>
                                        <asp:ControlParameter Name="catid" ControlID="selCat" DefaultValue="0" ConvertEmptyStringToNull="false" />
                                        <asp:Parameter Name="isNoGST" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="selSubCat" ForeColor="Red" ErrorMessage="Select sub category" runat="server"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->

                        <div class="col-lg-4 mb-3" runat="server" visible="false">
                            <label class="form-control-label mb-0 mb-lg-4 w-100"></label>
                            <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->

    
                    <div class="col-12">
    
                    <div class="form-group">
                    <label class="form-control-label">Short Description <span class="tx-danger">*</span></label>
                    <asp:TextBox ID="txtShortDescription" runat="server" CssClass="form-control" Height="50px" TextMode="MultiLine"/>
    <asp:RequiredFieldValidator ValidationGroup="CreateProduct" ControlToValidate="txtShortDescription" ForeColor="Red" ErrorMessage="Input short description" runat="server"></asp:RequiredFieldValidator>
                    </div>
                    </div><!-- col-4 -->
    
                       <asp:HiddenField ID="hidVarientGroupName" runat="server" />    
                    <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="form-group m-0">
                    <label class="form-control-label d-flex align-items-center objdiv">
                        Long Description
                        <span>
                            <i class="fa-light fa-microchip-ai"></i>
                            <asp:LinkButton runat="server"  ID="btndescription"  OnClick="btnlongdescription_Click" Text="Write using AI"></asp:LinkButton>
                        </span>
                    </label>
    
                    <asp:TextBox ID="summernote" runat="server" ClientIDMode="Static" CssClass="form-control" Height="250px" TextMode="MultiLine"/>
    
                    </div>
                    </div><!-- col-4 -->

                    <div class="col-lg-4">
                      <div class="uploadsec">
                        <div class="upload_imgsec d-flex flex-wrap">
                          <div class="upload_info mb-2 mb-md-0 w-100">
                            <label class="mb-2 tx-dark fw-light">Upload Product Images</label>
                            <span class="sizeinfo mb-2 d-inline-block w-100">(Max Size 512 x 512 px, 100 KB)</span>
                          </div><!--upload_info-->
                          <%--<div class="upload_box_wrap">
                            <div class="upload_box rmvbg">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload1" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd1" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg1" runat="server" />
                                <asp:Image runat="server" ID="productImg1" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box rmvbg">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload2" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd2" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg2" runat="server" />
                                <asp:Image runat="server" ID="productImg2" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box rmvbg">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload3" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd3" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg3" runat="server" />
                                <asp:Image runat="server" ID="productImg3" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box rmvbg">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload4" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd4" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg4" runat="server" />
                                <asp:Image runat="server" ID="productImg4" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                            <div class="upload_box rmvbg">
                                <asp:FileUpload accept="image/*" runat="server" ID="imgUpload5" CssClass="fileupload_productimage" />
                              <asp:Label ID="lblProd5" runat="server" CssClass="remove">X</asp:Label><asp:HiddenField ID="hidProdImg5" runat="server" />
                                <asp:Image runat="server" ID="productImg5" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                            </div><!--upload_box-->
                          </div>--%><!--upload_box_wrap-->
                            <div class="upload_box_wrap">
                                <div class="upload_box" id="uploadBox1">
                                    <asp:FileUpload accept="image/*" runat="server" ID="imgUpload1" CssClass="fileupload_productimage" data-target="#productImg1" />
                                    <asp:Label ID="lblProd1" runat="server" CssClass="remove" data-target="#productImg1">X</asp:Label>
                                    <asp:HiddenField ID="hidProdImg1" runat="server" />
                                    <asp:Image runat="server" ID="productImg1" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                                </div>
                                <div class="upload_box" id="uploadBox2">
                                    <asp:FileUpload accept="image/*" runat="server" ID="imgUpload2" CssClass="fileupload_productimage" data-target="#productImg2" />
                                    <asp:Label ID="lblProd2" runat="server" CssClass="remove" data-target="#productImg2">X</asp:Label>
                                    <asp:HiddenField ID="hidProdImg2" runat="server" />
                                    <asp:Image runat="server" ID="productImg2" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                                </div>
                                <div class="upload_box" id="uploadBox3">
                                    <asp:FileUpload accept="image/*" runat="server" ID="imgUpload3" CssClass="fileupload_productimage" data-target="#productImg3" />
                                    <asp:Label ID="lblProd3" runat="server" CssClass="remove" data-target="#productImg3">X</asp:Label>
                                    <asp:HiddenField ID="hidProdImg3" runat="server" />
                                    <asp:Image runat="server" ID="productImg3" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                                </div>
                                <div class="upload_box" id="uploadBox4">
                                    <asp:FileUpload accept="image/*" runat="server" ID="imgUpload4" CssClass="fileupload_productimage" data-target="#productImg4" />
                                    <asp:Label ID="lblProd4" runat="server" CssClass="remove" data-target="#productImg4">X</asp:Label>
                                    <asp:HiddenField ID="hidProdImg4" runat="server" />
                                    <asp:Image runat="server" ID="productImg4" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                                </div>
                                <div class="upload_box" id="uploadBox5">
                                    <asp:FileUpload accept="image/*" runat="server" ID="imgUpload5" CssClass="fileupload_productimage" data-target="#productImg5" />
                                    <asp:Label ID="lblProd5" runat="server" CssClass="remove" data-target="#productImg5">X</asp:Label>
                                    <asp:HiddenField ID="hidProdImg5" runat="server" />
                                    <asp:Image runat="server" ID="productImg5" AlternateText="Upload" ImageUrl="/content/images/uplad.png" CssClass="uploadimg" />
                                </div>
                            </div>
                        </div><!--upload_imgsec-->
                      </div><!--uploadsec-->
                    </div>
                    </div><!-- row -->

                        <div class="d-sm-flex wiz_btnsect  floting_btn_sec mt-3">
                            <%--<asp:Button runat="server" ID="btnadditemtriggerpopup" ClientIDMode="Static" Text="Save" ValidationGroup="CreateProduct" OnClientClick="Page_ClientValidate('CreateProduct'); alert(Page_IsValid);" class="btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4"/>--%>
                            <% if (ViewType == ViewMode.Edit)
                                { %>
                            <asp:Button ID="btnEditProduct" OnClientClick="return validateEditItem()" OnClick="btnEditProduct_Click" runat="server" Text="Save" ValidationGroup="CreateProduct" CssClass="btn btn-primary ml-lg-2" />
                            <% }
                            else if (ViewType == ViewMode.New)
                            { %>
                            <asp:Button ID="btnAddPrivateProduct" runat="server" OnClick="lbtnVariantGroupNo_Click" Text="Add Product" CssClass="btn btn-primary btn-drk-green px-4" ValidationGroup="CreateProduct" />
                            <% }
                            else if (ViewType == ViewMode.Duplicate)
                            { %>
                       <asp:Button ID="btnDuplicateProduct" runat="server" Text="Add Product"  CssClass="btn btn-primary px-4" ValidationGroup="CreateProduct"  OnClientClick="$('#addVariantGroup').modal('show'); return false;" />
                            <% } %>

                      <asp:Button ID="btnCancelSaveProduct" runat="server" Text="Cancel" OnClientClick="$(this).closest('form').attr('childobj', this.id);" CssClass="btn btn-secondary btn-drk-green  m-0 mx-2 px-4" OnClick="btnCancelSaveProduct_Click" CausesValidation="false" formnovalidate />
                      <%--<button class="btn btn-primary btn-drk-green btn-block m-0 mx-2 wd-sm-auto-force px-4" data-toggle="tab" href="#nav-selectproducts" role="tab" aria-controls="nav-selectproducts" aria-selected="false">Cancel</button>--%>
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
<div id="hsnsearch" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14 ">
            <div class="modal-body">
                <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="section-wrapper p-0 border-0">
                    <div class="row row-sm mb-3">
                        
                        <div class="col-12 d-flex align-content-center">
                            <label class="form-control-label mr-3">Search By</label>
                            <div class="mr-3 checkbox_div">
                                <asp:RadioButton ID="rbCode" CssClass="form-control-label w-100 tx-dark" GroupName="rbgStore" runat="server" Text="Code" />
                            </div>
                            <div class="checkbox_div">
                                <asp:RadioButton ID="rbItem" CssClass="form-control-label w-100 tx-dark" GroupName="rbgStore" runat="server" Text="Item" />
                            </div>
                            <div class="btn-sec ml-4">
                                <%--<asp:Button ID="btnCreateHsn" runat="server" CssClass="btn btn-sm btn-inline-block btn-outline-secondary ml-2" Text="Create HSN" OnClientClick="$('#hsnModal').modal('show'); return false;" />--%>
                                <%--<a href="javascript:void(0)" class="tx-dark" runat="server" id="CreateHsn" onclick="myHsn()" style="text-decoration: underline;">HSN is not listed. Create a new HSN</a>--%>
                                <a href="javascript:void(0)" class="tx-dark tx-12" onclick="$('#hsnModal').modal('show');" style="text-decoration: underline;">Create HSN</a>
                            </div>
                        </div>
                        <div class="col-12 mt-2" id="searchDiv">
                            <div class="form-group-sm d-flex align-content-center">
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" autocomplete="off" placeholder="Search by code/item" />
                                <asp:RequiredFieldValidator ID="rfvSearch" ControlToValidate="txtSearch" ForeColor="Red" ErrorMessage="Search" ValidationGroup="HSNSelect" runat="server" CssClass="b--15"></asp:RequiredFieldValidator>
                                <asp:Label ID="lblErrormsg" CssClass="position-absolute tx-10 b--15" runat="server" ForeColor="Red"></asp:Label>
                                <asp:LinkButton ID="lnkGo" runat="server" CssClass="btn btn-inline-block btn-primary ml-2" Text="GO" OnClick="lnkGo_Click" ValidationGroup="HSNSelect" CausesValidation="true"></asp:LinkButton>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="hsncodeTable" class="table table-bordered table-head-fixed" cellspacing="0" border="1">
                            <thead class="custom-header">
                                <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Tax%</th>
                                <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <asp:Repeater ID="rptDetails" runat="server">
                                    <ItemTemplate>
                                        <tr>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("hsn_code") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("hsn_description") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("gst_percent") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;">
                                                <asp:Button ID="btnSelect" runat="server" Text="Select" CommandName="Select" OnClick="btnSelect_Click" hsnCode='<%# Eval("hsn_id") %>' CommandArgument='<%# Eval("hsn_id") %>' CssClass="btn btn-outline-primary" />
                                            </td>
                                        </tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <tr>
                                            <td colspan="4" style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">
                                                <asp:Label ID="lblEmptyData" runat="server" Visible='<%# (rptDetails).Items.Count == 0 %>' Text="No data found." /></td>
                                        </tr>

                                    </FooterTemplate>
                                </asp:Repeater>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
                <!--section-wrapper-->
            </div>
            <!--modal-body-->
        </div>
    </div>
    <!-- modal-dialog -->
</div>
<!-- modal -->

<asp:SqlDataSource runat="server" ID="SDSCodeList" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT hsn_id,hsn_code,COALESCE(hsn_description, 'NULL') AS hsn_description,gst_percent FROM finascop_hsn 
    WHERE (trim(ifnull(@searchKey, '')) like '' or hsn_code like CONCAT('%', @searchKey, '%')) ORDER BY hsn_code ASC">
    <SelectParameters>
        <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="true" />
    </SelectParameters>
</asp:SqlDataSource>

<asp:SqlDataSource runat="server" ID="SDSItem" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT stit_ID,stit_itemId,stit_SKU,stit_HSNCode,hsn_id,hsn_code,COALESCE(hsn_description, 'NULL') AS hsn_description,gst_percent,stit_GST,stit_HSN_code,stit_itemName 
    FROM finascop_stock_itemmaster INNER JOIN finascop_hsn ON hsn_code=stit_HSN_code  
    WHERE (trim(ifnull(@searchKey, '')) like '' or stit_itemName like CONCAT('%', @searchKey, '%')) ORDER BY stit_SKU ASC">
    <SelectParameters>
        <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="true" />
    </SelectParameters>
</asp:SqlDataSource>

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
          <asp:LinkButton runat="server" Text="Yes" ID="lbtnVariantGroupYes" OnClick="lbtnVariantGroupYes_Click"  CssClass="btn btn-primary btn-drk-green"></asp:LinkButton>
          <asp:LinkButton runat="server" Text="No" ID="lbtnVariantGroupNo" OnClick="lbtnVariantGroupNo_Click"  CssClass="btn btn-secondary btn-drk-green" ></asp:LinkButton>
      </div>
    </div>
  </div><!-- modal-dialog -->
</div>
<asp:HiddenField runat="server" ID="hdnIDS" Value="0" />
<div class="modal" id="PopupFindcategory">
    <div class="modal-dialog modal-dialog-scrollable w-100">
        <div class="modal-content">
            <div class="modal-header">
                   <h5 class="modal-title" id="modaldemo5Label">Find Category</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="FindcategoryTable" class="table table-bordered table-head-fixed" cellspacing="0" border="1">
                            <thead>
                                <tr>
                                    <th>Retailer Category</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>  
        </div>
    </div>
</div>


<!-- Create HSN modal -->
<div id="hsnModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">

            <div class="modal-header">
                <h5 class="modal-title" id="hsnModalLabel">Create HSN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-sm-4">
                        <label for="txtNewHsn">HSN</label>
                        <asp:TextBox ID="txtNewHsn" runat="server" CssClass="form-control"></asp:TextBox>
                    </div>

                    <div class="form-group col-sm-4">
                        <label for="selNewTax"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "Tax %" : "VAT") %></label>
                        <asp:DropDownList ID="selNewTax" runat="server" CssClass="form-control">
                            <asp:ListItem Value="">Select from list</asp:ListItem>
                            <asp:ListItem Text="0%" Value="0"></asp:ListItem>
                            <asp:ListItem Text="5%" Value="5"></asp:ListItem>
                            <asp:ListItem Text="12%" Value="12"></asp:ListItem>
                            <asp:ListItem Text="18%" Value="18"></asp:ListItem>
                            <asp:ListItem Text="28%" Value="28"></asp:ListItem>
                        </asp:DropDownList>
                    </div>

                    <div class="form-group col-sm-4">
                        <label for="txtCess">CESS</label>
                        <asp:TextBox ID="txtNewCess" runat="server" CssClass="form-control"></asp:TextBox>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <asp:Button ID="btnSaveHsn" runat="server" CssClass="btn btn-primary" Text="Save" OnClick="btnSaveHsn_Click" />
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>
<!-- modal -->

<script type="text/javascript">
    $(document).ready(function () {
        // Function to toggle visibility of no data message based on table rows
        function toggleNoDataMessage() {
            var tableRows = $('#hsncodeTable tbody tr');
            if (tableRows.length > 0) {
                $('#lblEmptyData').hide();
            } else {
                $('#lblEmptyData').show();
            }
        }

        // Call the function when the page loads
        toggleNoDataMessage();

        // Call the function after the modal is shown
        $('#hsnsearch').on('shown.bs.modal', function (e) {
            toggleNoDataMessage();
        });

        // Event listener for change in table content
        $('#<%= rptDetails.ClientID %>').bind('DOMSubtreeModified', function () {
            toggleNoDataMessage();
        });

        // Summernote editor initialization
        $('#summernote').summernote({
            height: 165,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['view', ['fullscreen', 'codeview', 'help']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['paragraph', ['paragraph']],
            ]
        });

        // Code for handling file input changes
        $('div.upload_box_wrap input.fileupload_productimage').unbind('change').on('change', function (e) {
            const [file] = this.files
            if (file) {
                $(this).closest('div.upload_box').find('img.uploadimg').attr('src', URL.createObjectURL(file));
                $(this).closest('div.upload_box').find('img.uploadimg').show();
            }
        });

        // Code for removing uploaded images
        $("span.remove").click(function () {
            <% if (ViewType == ViewMode.Edit) { %>
            if (!confirm("Are you sure you want to delete this image?")) {
                return;
            }
            <% } %>
            $(this).closest(".upload_box").find(".uploadimg").attr('src', '');
            $(this).closest(".upload_box").find("input[type=hidden]").val($(this).closest(".upload_box").find(".uploadimg").attr('imgid'));
            $(this).closest(".upload_box").find("input[type=file]").val("");
        });

        // Select2 initialization
        $('.select2-show-search').select2({
            minimumResultsForSearch: ''
        });

        // Event listeners for input changes
        $('#<%= txtPrdName.ClientID %>').on('input propertychange paste', buildDisplayName);
        $('#<%= txtVarient.ClientID %>').on('input propertychange paste', buildDisplayName);
        $('#<%= txtQuantity.ClientID %>').on('input propertychange paste', buildDisplayName);
        $('#<%= selUnit.ClientID %>').on('change', buildDisplayName);
        $('#<%= selQuantity.ClientID %>').on('change', buildDisplayName);

        // Function to build display name
        function buildDisplayName() {
            var brandname = $('#<%=txtSelectedBrand.ClientID%>').val();
            var varientname = $('#<%=txtVarient.ClientID%>').val();
            //var qtyname = $('#<%=txtQuantity.ClientID%>').val();
            var qtyname = <%= (pnlQuantitySelect.Visible ? String.Format("$('#{0} option:selected').text()", selQuantity.ClientID) : String.Format("$('#{0}').val()", txtQuantity.ClientID)) %>

            var unitname = $("#<%=selUnit.ClientID%> option:selected").text();
            var prdname = $('#<%= txtPrdName.ClientID %>').val();
            var namevals = [];
            if (brandname !== 'Generic') {
                namevals.push(brandname);
            }
            if (prdname !== '') {
                namevals.push(prdname);
            }
            if (varientname !== '') {
                namevals.push(varientname);
            }
            if (qtyname !== '') {
                namevals.push(qtyname);
            }
            if (unitname !== '' && unitname.toLowerCase() !== 'select unit') {
                namevals.push(unitname);
            }

            var displayname = namevals.join(" ");
            $('#<%= txtProductWebName.ClientID%>').val(displayname);

            var displayqty = [];
            if (qtyname !== '') {
                displayqty.push(qtyname);
            }
            if (unitname !== '' && unitname.toLowerCase() !== 'select unit') {
                displayqty.push(unitname);
            }
            var displayquantity = displayqty.join(" ");
            $('#<%= txtDisplayQty.ClientID%>').val(displayquantity);
        }
    });
    //Find Category
    function findCategory() {
        var input = document.getElementById('<%= txtsearchinput.ClientID %>').value;
        input = input.trim();
         if (!input) {
             alert("Please enter a search term.");
             return;
         }
         try {

             onSuccess = function (response) {                
                 if (response.status == 'Success') {
                     displayResults(response.data); // Populate the table with results
                     
                     $('#PopupFindcategory').modal('show'); 
                 }
             }
             onError = function (data) {
                 alert('Operation failed');
                 console.error('AJAX Request Failed:', error);
             };
             retMaster.ajax.JSONRequest('/api/Home/ProductCategory', 'POST', { input: input }, onSuccess, onError);
         } catch {

         }
    }
    function displayResults(results) {
        const tbody = document.querySelector('#FindcategoryTable tbody');
        tbody.innerHTML = '';  // Clear existing rows
           if (results && results.length > 0){
 // Loop through each result and create a new table row
        results.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${row.BusinessType.Name}</td>
            <td>${row.ParentCategory.Name}</td>
            <td>${row.Category.Name}</td>
            <td>${row.SubCategory.Name}</td>
            <td><input id="rdIds" type="radio" name="rowSelect" data-names="${row.BusinessType.Name},${row.ParentCategory.Name},${row.Category.Name},${row.SubCategory.Name}"  data-ids= "${row.BusinessType.Id},${row.ParentCategory.Id},${row.Category.Id},${row.SubCategory.Id}"  onclick="saveIds(this)" /></td>`;
            tbody.appendChild(tr); 
        });
  } else {
    // Display "No data available" message with image
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td colspan="5" style="text-align: center;">
            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg" alt="No data available" />
            <h6 class="mb-3">No data available</h6>
        </td>`;
    tbody.appendChild(tr);
}
       
    }
    function saveIds(radioButton){
        const ids = radioButton.getAttribute("data-ids");
        if (ids) {
            const ids = radioButton.getAttribute("data-ids");
            const names = radioButton.getAttribute("data-names");
            $('#<%=hdnIDS.ClientID%>').val(ids);
            closePopup();
            $('#<%= btnhidecategory.ClientID %>').trigger('click');

        } else {
            console.error("Error: No data-ids attribute found!");
        }
    }    
    function closePopup() {
      setTimeout(() => {
            $('#PopupFindcategory').modal('hide')
       }, 1000);            
    } // end of Find category
</script>

<script>
    document.getElementById('<%=rbCode.ClientID%>').addEventListener('change', function () {
        document.getElementById('<%=txtSearch.ClientID%>').setAttribute('placeholder', 'Search by Code');
        document.getElementById('<%=txtSearch.ClientID%>').addEventListener('input', onlyNumbers);
    });

    document.getElementById('<%=rbItem.ClientID%>').addEventListener('change', function () {
        document.getElementById('<%=txtSearch.ClientID%>').setAttribute('placeholder', 'Search by Item');
        document.getElementById('<%=txtSearch.ClientID%>').removeEventListener('input', onlyNumbers);
    });

    function onlyNumbers(event) {
        const input = event.target.value;
        if (!/^\d+$/.test(input)) {
            event.target.value = input.replace(/[^\d]/g, ''); 
        }
    }
    
    function closeModal() {
        $('#hsnsearch').modal('hide');
    }

$('.objdiv').click(function () {
    $(this).addClass('processing_loader'); 
    setTimeout(function () {
        $('.objdiv').removeClass('processing_loader'); 
    }, 10000);
});

</script>



<script>
    $(document).ready(function () {
        // Function to check and toggle visibility of no data message
        function toggleNoDataMessage() {
            var repeaterRows = $('#<%= rptDetails.ClientID %> tbody tr');
            if (repeaterRows.length > 0) {
                $('#lblEmptyData').hide();
            } else {
                $('#lblEmptyData').show();
            }
        }

        // Call the function when the page loads
        toggleNoDataMessage();

        // Call the function on modal show
        $('#hsnsearch').on('shown.bs.modal', function (e) {
            toggleNoDataMessage();
        });

        // Call the function after every postback
        window.pageLoad = function () {
            toggleNoDataMessage();
        };
    });
</script>

<%--<script>
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
</script>--%>

<script>
    $(function () {
        // Check on page load if any image is already present
        $(".upload_box img").each(function () {
            if ($(this).attr('src') !== "/content/images/uplad.png" && $(this).attr('src') !== "") {
                $(this).closest('.upload_box').addClass("rmvbg");
            } else {
                $(this).closest('.upload_box').removeClass("rmvbg");
            }
        });

        // Change event for the FileUpload control
        $(".fileupload_productimage").change(function () {
            let file = $(this), imgControlName = file.data('target');
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = e => {
                    file.closest('.upload_box').addClass("rmvbg").find(imgControlName).attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Click event for the Remove button
        $('.remove').click(function () {
            let imgControlName = $(this).data('target');
            $(this).closest('.upload_box').removeClass("rmvbg").find(imgControlName).attr('src', "/content/images/uplad.png");
        });
    });
</script>

<style>
     
    .checkbox_div label {
        margin-left:0.35rem;
    }
    #hsnsearch .table-responsive {
        max-height: 300px;
    }
    @media (min-width: 768px) {
        #PopupFindcategory .modal-dialog {
            max-width: 900px;
        }
    }
    .hidden {
    display: none;
    }
    .input_search_box .btn {
        width: auto;
    }
    .objdiv {
        gap: 0 40px;
    }
    .modal-backdrop + .modal-backdrop {
       z-index: 1051; 
    }
    #hsnModal {
        z-index: 1052;
    }
</style>





