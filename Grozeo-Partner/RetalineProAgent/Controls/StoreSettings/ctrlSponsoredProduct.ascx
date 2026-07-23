<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlSponsoredProduct.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlSponsoredProduct"%>
    <%--<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center justify-content-between">                               
                                <div class="col-sm-3 d-flex flex-wrap">
                                    <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Sponsored Products</label>
                                    <div class="col-lg-6 text-left  mg-lg-b-0 d-flex align-items-end">
                                        <div class="form-group mb-0 wd-100p-force mt-2">
                                            <asp:DropDownList ID="selBrand" runat="server" DataSourceID="SDSBrands" OnDataBound="selBrand_DataBound" AutoPostBack="true" DataTextField="brand_name" DataValueField="brand_id" CssClass="form-control select2">
                                                <asp:ListItem Text="All Brands" Value="0"></asp:ListItem>
                                            </asp:DropDownList>
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
                          </div>
                            div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">
                            <div class="form-group mb-0">
                      <asp:DropDownList ID="selCategory" runat="server" AutoPostBack="true" DataSourceID="SDSCategory" OnDataBound="selCategory_DataBound" DataTextField="category_name" DataValueField="category_id" CssClass="form-control select2"><asp:ListItem Text="All Categories" Value="0"></asp:ListItem></asp:DropDownList>
                            </div>--%>
                          <%--</div><!--col-lg-4-->
                          <div class="col-lg-4 input-group-sm mg-b-10 mg-lg-b-0">

                            <div class="input-group mg-0">
                                <asp:TextBox ID="txtSponsoredProductName" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                              <span class="input-group-btn">
                                  <asp:LinkButton runat="server" CssClass="btn bd bd-l-0 btn-drk-green tx-gray-600 d-flex align-items-center" ><i class="fa fa-search"></i></asp:LinkButton>
                              </span>
                            </div>--%><!-- input-group -->
                          <%--</div>--%><!--col-lg-4-->
                        <%--</div>--%><!--row-->
                      <%--</div>--%><!--collapse-->
                    <%--</div>--%><!--wizard_fliter-->  
                            <!--col-lg-4-->
                            <%--<div class="card-body">
                                <div class="table-responsive mailbox-messages">
                                    <asp:GridView AutoGenerateColumns="false" ID="gvsponsoredpd" runat="server" CssClass="table" GridLines="None" BorderColor="#ECECEC"
                                        AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvSalesReport_DataBound" DataSourceID="SDSSponsoredproduct">
                                        <Columns>
                                            <asp:BoundField HeaderText="NAME" DataField="stit_SKU" SortExpression="stit_SKU" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                            <asp:BoundField HeaderText="BRAND" DataField="stit_brand_name" SortExpression="br_Name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                            <asp:BoundField HeaderText="SUB CATEGORY" DataField="stit_category_name" SortExpression="stit_category_name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                            <%--<asp:BoundField HeaderText="PayGatewayId" DataField="order_payment_gateway_refid" SortExpression="order_payment_gateway_refid" />--%>
                                        <%--</Columns>
                                    </asp:GridView>
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
                                </div>
                            </div>
                        </div>
                      </div>
                            </div>
                        </div>
        </div>--%>--%>
 




