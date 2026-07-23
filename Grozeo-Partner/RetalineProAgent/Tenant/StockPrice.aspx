<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Stock & Price" MaintainScrollPositionOnPostback="true" CodeBehind="StockPrice.aspx.cs" Inherits="RetalineProAgent.StockPrice" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
   <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
   <link href="/Content/lib/jt.timepicker/css/jquery.timepicker.css" rel="stylesheet">
       <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
    <script src="/Content/lib/jt.timepicker/js/jquery.timepicker.js"></script>
    <script src="/Content/js/custom/stock.js"></script>

     <link rel="stylesheet" href="/Content/custom/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <style>
        .tbl_prod_img{
            width:auto; max-width: 30px; max-height: 28px;
        }
       
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }   
    </style>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
<asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock & Price</li>--%>
    <a href="/Navigations/Products"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:PlaceHolder>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <asp:PlaceHolder ID="plcWizard" Visible="false" runat="server">
        <div class="processingsect ">
            <ul class="processingwrap">
              <li class="active">
                <div class="processing-title">Create Store</div>
              </li>
              <li class="active">
                <div class="processing-title">Select Products</div>
              </li>
              <li class="active">
                <div class="processing-title">Manage Stock</div>
              </li>
              <li class="">
                <div class="processing-title">Sponsored Products</div>
              </li>
              <li class="">
                <div class="processing-title">Publish Store</div>
              </li>
            </ul>
          </div><!--processingsect-->
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcNoneWizard" Visible="true" runat="server">
        <div>
            <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle" runat="server" Text="Stock & Price"></asp:Literal> 
            </h6>
            <p class="mb-0">Efficient Inventory Control</p>
        </div>
    
                
        </asp:PlaceHolder>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">


            <div class="row row-sm align-items-start align-items-sm-end ">


                <div class="col-12 col-md-9 d-flex flex-wrap flex-sm-nowrap justify-content-between">
                    <div class="col-12 d-flex flex-wrap flex-lg-nowrap px-0">
                        <div class="d-flex flex-wrap col-sm-4 input-group pl-0 pr-0 pr-sm-3">
                            <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                            <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">  
                            <%--<span >
                              <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>

                          </span>--%>
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name"  ValidationGroup="StockUpdate" DataValueField="br_ID" CssClass="form-control select2" runat="server"><asp:ListItem Text="Select Branch" Value=""></asp:ListItem></asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" Display="Dynamic" ControlToValidate="selBranches" ValidationGroup="StockUpdate" ForeColor="Red" CssClass="error_msg_wrap" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
                            </asp:PlaceHolder>
                            <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address, br_directDelivery, br_courierDelivery FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                ProviderName="MySql.Data.MySqlClient">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                    <asp:Parameter Name="branchid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>

                        <nav class="navbar col-12 col-sm-8 w-auto w-lg-100 mt-2 mt-sm-0 navbar-expand-sm bg-transparent p-0 justify-content-start align-items-end">
                            <a class="navbar-brand d-sm-none tx-dark tx-14" href="#">Filter by</a>
                            <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                              aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                              <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                                <i class="fa fa-sliders" aria-hidden="true"></i>
                              </span>
                            </button>
                          
                            <div class=" collapse navbar-collapse flex-wrap" id="navbarSupportedContent">
                          
                              <ul class="navbar-nav mr-auto pt-2 pt-sm-0">
                                
                                <li class="nav-item mx-0 mx-sm-1 my-1 my-sm-0 ">
                                    <asp:LinkButton ID="lbtnViewAll" runat="server" typeid="0" ValidationGroup="StockUpdate" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">View All</asp:LinkButton>
                                </li>
                          
                                <li class="nav-item mx-0 mx-sm-1 my-1 my-sm-0">
                                    <asp:LinkButton ID="lbtnPending" runat="server" typeid="1" ValidationGroup="StockUpdate" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">View out of Stock</asp:LinkButton>
                                </li>
                                
                                <li class="nav-item mx-0 mx-sm-1 my-1 my-sm-0">
                                    <a href="/Tenant/MyProducts" class="btn btn-block btn-outline-primary">Add more products</a>
                                    <asp:HiddenField ID="hidInvChanges" ClientIDMode="Static" runat="server" Value="0" />
                                </li>
                          
                              </ul>
                            </div>
                          </nav>
                    </div>
                </div>

                <div class="col-12 col-md-3 flex-wrap align-items-end d-flex justify-content-start justify-content-md-end mt-2 mt-md-0 pl-md-0">
                     <asp:LinkButton ID="lbtnDownloadStock" OnClick="lbtnDownloadExcel_Click" ValidationGroup="StockUpdate" runat="server" typeid="9" CssClass="btn btn-outline-primary mr-2"><i class="fa fa-download mr-2"></i>Export</asp:LinkButton> 
                    <asp:HyperLink ID="hlBulkImport" NavigateUrl="~/Tenant/BulkImport.aspx" CssClass="btn btn-outline-primary" runat="server">Bulk Import</asp:HyperLink>
                    <%--<div class="dropdown m-0">
                        
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Bulk Import
                          </button>
                          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                            <asp:HyperLink ID="hlImportInventory" NavigateUrl="~/Tenant/BulkImport.aspx" runat="server" typeid="5" CssClass="dropdown-item">Bulk Import Stock</asp:HyperLink>
                            <asp:HyperLink ID="lbtnPickupFailed" runat="server" typeid="7" CssClass="dropdown-item">API Stock Import</asp:HyperLink>
                            <div class="dropdown-divider"></div>
                            <asp:LinkButton ID="lbtnDownloadStock" OnClick="lbtnDownloadExcel_Click" ValidationGroup="StockUpdate" runat="server" typeid="9" CssClass="dropdown-item"><i class="fa fa-download"></i> Download Sample File</asp:LinkButton> 
                          </div>
                        </div>--%>
                    </div>
                </div>

            <div class="row row-sm mt-2">

                <div class="col-12 col-sm-6 col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearchProduct" runat="server">Search by:</label>
                    <input type="text" style="display: none" />
                    <input type="password" style="display: none" />
                    <asp:TextBox ID="txtSearchProduct" runat="server" autocomplete="off" CssClass="form-control" placeholder="Product name"></asp:TextBox>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom" runat="server">Brand:</label>
                    <asp:DropDownList ID="selBrand" runat="server" CssClass="form-control select2" DataSourceID="SDSBrand" DataTextField="brand_name" DataValueField="brand_id" AppendDataBoundItems="true" AutoPostBack="true" OnSelectedIndexChanged="selBrand_SelectedIndexChanged">
                        <asp:ListItem Text="All brands" Value="-1"></asp:ListItem>
                    </asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT pb.brand_id,pb.brand_name FROM mypha_productbrands pb WHERE EXISTS(SELECT i.* FROM finascop_stock_itemmaster i 
  INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid 
  WHERE i.pdt_brand = pb.brand_id) AND IFNULL(pb.brand_name, '') NOT LIKE '' ORDER BY brand_name"
                        OnSelecting="SDSBranches_Selecting">
                        <SelectParameters>
                            <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 form-group mb-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateTo" runat="server">Category:</label>
                    <asp:DropDownList ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2" DataSourceID="SDSCat" DataTextField="category_name" DataValueField="category_id" AppendDataBoundItems="true" OnSelectedIndexChanged="selCat_SelectedIndexChanged">
                        <asp:ListItem Text="All categories" Value="-1"></asp:ListItem>
                    </asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT pc.category_id,pc.category_name FROM mypha_productcategory pc WHERE EXISTS(SELECT sc.* FROM mypha_productsubcategory sc 
  INNER JOIN finascop_stock_itemmaster i ON i.product_category = sc.sub_category_id INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID 
  INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid WHERE sc.main_category = pc.category_id)"
                        OnSelecting="SDSBranches_Selecting">
                        <SelectParameters>
                            <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>
                <div class="col-4 col-sm-6 col-lg-2 d-flex align-items-end">
                    <label class="mb-0">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary" runat="server" OnClick="lbtnSearch_Click">Search</asp:LinkButton>
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/StockPrice.aspx" Text="Reset" />

<%--                    <a class="btn btn-outline-primary mt-2 mt-lg-0 ml-2" href="javascript:void(0)">Reset</a>--%>
                    </div>

                <div class="col-12">
                    <asp:PlaceHolder ID="plcMultipleBranchButton" Visible="false" runat="server"><h3 class="card-title">
                  <%--<asp:Literal runat="server" ID="ltrTotalItemsSelected" Text="0"></asp:Literal> Item/s--%>
                  &nbsp;
                  <asp:LinkButton runat="server" Visible="false" Text="Publish Items" ValidationGroup="StockUpdate" ID="btnStockPublishItems" OnClick="btnStockPublishItems_Click" CssClass="btn btn-primary"></asp:LinkButton>
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-select-branch">
                  Publish Items
                </button>
              </h3></asp:PlaceHolder><asp:Label ID="lblResult" runat="server"></asp:Label>
              <small class="text-muted">
                  <asp:Literal ID="ltrComment" runat="server" Text="Please ensure to save changes before leaving the page. The items along with changes will be listed in public site once click on the button 'Save'"></asp:Literal>
                  </small>
                    <asp:PlaceHolder ID="plcMsgRestrictionmode" runat="server" Visible="false">
<p class="mg-b-0 text-info">This store has intra-state restriction mode enabled due to the GST/VAT type. Products will only be listed within the state where the store operates.</p>
                    </asp:PlaceHolder>
                </div>

            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive" id="accordion">
                                <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvProducts" GridLines="None" OnRowCommand="gvProducts_RowCommand" runat="server" CssClass="table table-bordered mg-b-0 gridview_table" OnRowDataBound="gvProducts_RowDataBound"
                                    AllowPaging="true" AllowCustomPaging="true" ShowFooter="false" PagerSettings-Visible="true" DataKeyNames="Id" PageSize="10" OnDataBound="gvProducts_DataBound" DataSourceID="SDSInventory">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Name" ItemStyle-CssClass="pro_disc">
                                            <ItemTemplate>
                                                <div class="d-flex">
                                                    <div class="prodct_img">
                                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.OptimizedImageUrl(Eval("imageurl").ToString(), 150, 150) %>' loading="lazy" />
                                                        <div class="imgpopover">
                                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        </div>
                                                        <asp:HiddenField ID="hidStitID" runat="server" Value='<%# Eval("stit_ID") %>' />
                                                    </div>
                                                    <div class="d-inline-block pl-2 prd_name">
                                                        <asp:Label runat="server" ID="lblName" CssClass="w-100 d-inline-block" ToolTip='<%# Bind("stit_SKU") %>'><strong><%# Eval("stit_SKU") %></strong></asp:Label>
                                                        <p class="m-0"><%# (String.IsNullOrEmpty(Eval("fsipc_code").ToString()) ?"": String.Format(" (Code: {0})", Eval("fsipc_code"))) %></p>
                                                        

                                                        <i runat="server" title="Courier Delivery" visible='<%# (Convert.ToString(Eval("courierDelivery"))  == "1" ? true : false) %>' class="fa-solid fa-person-carry-box" aria-hidden="true"></i>
                                                        <i runat="server" title="Express Delivery" visible='<%# (Convert.ToString(Eval("directDelivery"))  == "1" ? true : false) %>' class="fa-solid fa-moped" aria-hidden="true"></i>
                                                        (<i runat="server" title="Spot Return" visible='<%# (Eval("hasSpotReturn").ToString().Equals("1") ? true:false) %>' class="fa fa-undo tx-12 text-primary" style="padding-left: 2px; padding-right: 2px;" aria-hidden="true"></i>
                                                        
                                                        <small>
                                                        <%# (int.TryParse(Eval("returnTime").ToString(), out int returnTimeValue) && returnTimeValue == 0) 
                                                            ? "<i title='No Return' aria-hidden='true'><img style='max-width: 15px;' src='/content/images/no_Return.svg'></i>" 
                                                            : returnTimeValue.ToString() 
                                                        %>
                                                    </small>)
                                                        <small>Category: <%# Eval("stit_category_name") %>, Brand: <%# Eval("stit_brand_name") %>, By: <%# Eval("med_manufacturename").ToString().Equals("") ? "NA":Eval("med_manufacturename") %></small>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Stock" ItemStyle-CssClass="stock_disc">
                                            <ItemTemplate>
                                                <div class="text-right">
                                                    <asp:Label runat="server" Text='<%# Eval("itemCount") %>'></asp:Label>
                                                </div>
                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="text-right">
                                                        <asp:TextBox ID="txtPStock" Enabled='<%# Eval("isAvailable").ToString().Equals("1") %>' CssClass="form-control text-right px-1 py-0 stock-textbox" onfocus="this.select()" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" onchange="whenInventoryChanged()" Width="55" min="1" ValidationGroup="StockUpdate" Text='<%# Eval("item_count")%>' oldval='<%# Eval("item_count")%>' runat="server" Visible='<%# (Eval("item_count").ToString().Equals("1000") ? false : true)%>'></asp:TextBox>
                                                        <asp:RequiredFieldValidator runat="server" Visible='<%# (Eval("item_count").ToString().Equals("1000") ? false : true)%>' CssClass="position-absolute" SetFocusOnError="true" ControlToValidate="txtPStock" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Qty is required"></asp:RequiredFieldValidator>
                                                        <%--<strong>
                                                            <asp:Label ID="lblOnDemand" runat="server" Visible='<%# (Eval("item_count").ToString().Equals("1000") ? true : false)%>' CssClass="text-right mx-2 tx-dark">On Demand</asp:Label></strong>--%>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField ItemStyle-CssClass="mrp-disc">
                                            <HeaderTemplate>
                                                <%# (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP") %>
                                            </HeaderTemplate>
                                            <ItemTemplate>
                                                <div class="text-right">
                                                    <asp:Label runat="server" Text='<%# Eval("mrp") %>'></asp:Label>
                                                </div>
                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="text-right">
                                                        <asp:TextBox ID="txtMRP" Enabled='<%# Eval("isAvailable").ToString().Equals("1") %>' CssClass="form-control mrp text-right px-1 py-0 mrp-textbox" onfocus="this.select()" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" SetFocusOnError="true" Width="65" Text='<%# Eval("mrp")%>' oldval='<%# Eval("mrp")%>' defaultval='<%# Eval("mrp")%>' onchange='<%# "whenInventoryChanged(); "+( Eval("issponsered").ToString() == "1" ? String.Format("return checksponsoredchange($(this), {0})", Eval("sponsered_margin")) : "" ) %>' ValidationGroup="StockUpdate" min="0" runat="server"></asp:TextBox>
                                                        <asp:RequiredFieldValidator runat="server" CssClass="position-absolute" ControlToValidate="txtMRP" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="MRP is required"></asp:RequiredFieldValidator>

                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Margin ( % )" Visible="false" HeaderStyle-Width="10%" ItemStyle-HorizontalAlign="Right">
                                            <ItemTemplate>
                                                <asp:Label ID="lblPCustomMarginVal" CssClass="lblmarginVal" runat="server"></asp:Label>
                                                &nbsp;(<asp:Label ID="lblPCustomMargine" runat="server" CssClass="lblmargin"></asp:Label>%)
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Selling Price" ItemStyle-CssClass="sellprice-disc">
                                            <ItemTemplate>
                                                <div class="text-right">
                                                    <asp:Label runat="server" Text='<%# Eval("selling_price") %>'></asp:Label>
                                                </div>
                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="text-right">
                                                        <asp:TextBox ID="txtSellingPrice" Enabled='<%# Eval("isAvailable").ToString().Equals("1") %>' CssClass="form-control sellingprice text-right px-1 py-0 sellprice-textbox" oninput='<%# GetOnInputScript(Container.DataItem) %>' SetFocusOnError="true" onfocus="this.select()" Width="65" Text='<%# Eval("selling_price")%>' oldval='<%# Eval("selling_price")%>' ValidationGroup="StockUpdate" min="0" defaultval='<%# Eval("selling_price")%>' onchange='<%# "whenInventoryChanged(); "+( Eval("issponsered").ToString() == "1" ? String.Format("return checksponsoredchange($(this), {0})", Eval("sponsered_margin")) : "" ) %>' runat="server"></asp:TextBox>
                                                        <span id="decimalWarning" style="color: red; display: none; font-size: smaller;">Decimal values are not allowed.</span>
                                                        <asp:RequiredFieldValidator runat="server" CssClass="position-absolute" ControlToValidate="txtSellingPrice" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Selling price is required"></asp:RequiredFieldValidator>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Discount Price" ItemStyle-CssClass="dsellprice-disc">
                                            <ItemTemplate>
                                                <div class="text-right">
                                                    <asp:Label runat="server" Text='<%# Eval("discount_selling_price") %>'></asp:Label>
                                                </div>
                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="text-right">
                                                        <asp:TextBox ID="txtDiscountSP" onDemand='<%# Eval("isOnDemand") %>' Enabled='<%# Eval("isAvailable").ToString().Equals("1") %>' CssClass="form-control idiscountsp text-right px-1 py-0 dsellprice-textbox" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" SetFocusOnError="true" onfocus="this.select()" Width="65" pid='<%# Eval("stit_ID")%>' brId='<%# Eval("branch_id")%>' Text='<%# Eval("discount_selling_price")%>' prdBrand='<%# Eval("pdt_brand")%>' prdCategory='<%# Eval("product_category")%>' itemId='<%# Eval("stit_itemId")%>' oldiscountval='<%# Eval("discount_selling_price")%>' min="0" defaultval='<%# Eval("discount_selling_price")%>' runat="server" Visible='<%# (Eval("store_group_grosmartMerchant").ToString().Equals("1") ? true : false)%>'></asp:TextBox>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Action" ItemStyle-CssClass="action-disc">
                                            <ItemTemplate>
                                                <div class="dropdown-action text-center">
                                                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" stitid='<%# Eval("stit_ID") %>' erpid='<%# Eval("fsipc_code") %>' erptype='<%# Eval("fsipc_isCompany") %>' erptypename='<%# Eval("fsipc_codeType") %>' onclick="loadErpId-(this)"><i class="ion-android-menu"></i></a>
                                                    <div class="dropdown-menu p-3" role="menu" style="">
                                                        <a href="javascript:void(0)" class="" stitid='<%# Eval("stit_ID") %>' erpid='<%# Eval("fsipc_code") %>' erptype='<%# Eval("fsipc_isCompany") %>' erptypename='<%# Eval("fsipc_codeType") %>' onclick="loadErpId(this)">Set ERP Id / Barcode</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a href="javascript:void(0)" class="" stitid='<%# Eval("stit_ID") %>' spotreturn='<%# Eval("hasSpotReturn") %>' returndays='<%# Eval("returnTime") %>' onclick="loadReturnDays(this)">Return Days</a>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="Available" ItemStyle-CssClass="available-disc text-center">
                                            <ItemTemplate>
                                                <div class="toggle-wrapper m-0">
                                                    <div class='toggle toggle-light success'
                                                        data-toggle-on='<%# (Eval("isAvailable").ToString() == "1" && Eval("verification_status").ToString() == "Verified").ToString().ToLower() %>'
                                                        onclick='return handleAvailabilityClick(
                                                         this,
                                                          "<%# Eval("verification_status") %>",
                                                          "<%# Eval("stit_ID") %>",
                                                          "<%# Eval("branch_id") %>",
                                                          "<%# Eval("stit_StoreGroup") %>",
                                                          "<%# ((CheckBox)((GridViewRow)Container).FindControl("chkStatus")).UniqueID %>",
                                                          "<%# Eval("displayHSN") %>",
                                                          "<%# Eval("displayGST") %>",
                                                          "<%# Eval("hsnCess") %>"
                                                     );'>
                                                    </div>
                                                </div>

                                                <asp:CheckBox ID="chkStatus"
                                                    runat="server"
                                                    Style="display: none;"
                                                    mrp='<%# Eval("mrp") %>'
                                                    stitid='<%# Eval("stit_ID") %>'
                                                    brid='<%# Eval("branch_id") %>'
                                                    storeId='<%# Eval("stit_StoreGroup") %>'
                                                    Checked='<%# (Eval("isAvailable").ToString() == "1" && Eval("verification_status").ToString() == "Verified") %>'
                                                    OnCheckedChanged="chkStatus_CheckedChanged"
                                                    AutoPostBack="false" />

                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>"
                                                    aria-labelledby="headingOne"
                                                    data-parent="#accordion">
                                                    <div>
                                                        <asp:LinkButton runat="server"
                                                            Visible='<%# (Eval("isAvailable").ToString() == "1" && Eval("verification_status").ToString() == "Verified") %>'
                                                            Text="Save"
                                                            ValidationGroup="StockUpdate"
                                                            ID="btnStockSaveChanges"
                                                            taxValue='<%# Eval("taxValue") %>' hsncode='<%# Eval("displayHSN") %>' tax='<%# Eval("displayGST") %>' stitid='<%# Eval("stit_id") %>' hsnCess='<%# Eval("hsnCess") %>'
                                                            CommandArgument="<%# ((GridViewRow) Container).RowIndex %>"
                                                            CommandName="Save"
                                                            CssClass="btn btn-primary btn-inline-block btn-sm mx-2">
                                                        </asp:LinkButton>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="On Demand" ItemStyle-CssClass="demand-disc text-center">
                                            <ItemTemplate>
                                                <div class="toggle-wrapper m-0">
                                                    <div class="toggle toggle-light success" data-toggle-on="<%# Eval("isOnDemand").ToString().Equals("1") ? "true" : "false" %>"></div>
                                                </div>
                                                <asp:CheckBox ID="chkOndemand" OnCheckedChanged="chkOndemand_CheckedChanged" Style="display: none;" AutoPostBack="true" runat="server" mrp='<%# Eval("mrp") %>' sellingprice='<%# Eval("selling_price") %>' stitid='<%# Eval("stit_ID") %>' brid='<%# Eval("branch_id") %>' Checked='<%# Eval("isOnDemand").ToString().Equals("1") %>' />
                                                <div class="collapse tx-center <%# String.Format("collapse{0}", Container.DataItemIndex) %>" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div>
                                                        <asp:LinkButton runat="server" Visible='<%# (Eval("isAvailable").ToString().Equals("1") ? true : false)%>' Text="Cancel" ID="btnStockCancelChanges" CommandArgument="<%# ((GridViewRow) Container).RowIndex %>" CommandName="Cancel" CssClass="btn btn-primary btn-inline-block btn-sm mx-2"></asp:LinkButton>
                                                    </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField ItemStyle-CssClass="arrow-disc">
                                            <ItemTemplate>
                                                <div class="action_arrow tx-center" data-toggle="collapse" data-target='<%# String.Format(".collapse{0}", Container.DataItemIndex) %>' aria-expanded="false" aria-controls="collapseOne">
                                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center" id="oneStore" style="display: none;">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3"><small>You dont have any item selected for sale. Please go to the page <a href="/Tenant/InventoryMapping">Select items for Sale</a> to select from master data or you can upload CSV. </small></h6>
                                        </div>
                                        <div class="text-center" id="moreThanOneStr" style="display: none;">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/nostore.svg">
                                            <h6 class="mb-3"><small>Please select a branch to list the items added. </small></h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <SortedAscendingHeaderStyle CssClass="sorting sorting_asc" />
                                    <SortedDescendingHeaderStyle CssClass="sorting sorting_desc" />
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSInventory" OnSelecting="SDSInventory_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="GetMyStockPaged" SelectCommandType="StoredProcedure"
                                        OnSelected="SDSInventory_Selected">
                                        <SelectParameters>
                                            <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" Type="String" ConvertEmptyStringToNull="false" />
                                            <asp:Parameter Name="BranchId" Type="Int32" DefaultValue="-1" ConvertEmptyStringToNull="false" />
                                            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
                                            <asp:Parameter Name="retailerType" DefaultValue="0" />
                                            <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
                                            <asp:ControlParameter ControlID="selBrand" Name="brand" DefaultValue="-1" DbType="Int32" PropertyName="Text" />
                                            <asp:ControlParameter ControlID="selCat" Name="catid" DefaultValue="-1" DbType="Int32" PropertyName="Text" />
                                            <asp:Parameter Name="startIndex" Type="Int32" DefaultValue="0" />
                                            <asp:Parameter Name="pageSize" Type="Int32" DefaultValue="10" />
                                            <asp:Parameter Name="totalRecords" Type="Int32" Direction="Output" />
                                        </SelectParameters>
    </asp:SqlDataSource>

                                </div>
        </div><!-- card-body -->
    </div><!-- card -->
    
    <asp:HiddenField ID="hdnHsn" runat="server" />
    <asp:HiddenField ID="hdnTax" runat="server" />
    <asp:HiddenField ID="hdnCess" runat="server" />

    <asp:PlaceHolder ID="plcSelectBranchModel123" runat="server">
        <div class="modal fade" id="modal-select-branch">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Select Branch</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                  <div class="form-group">
                  </div>                                    
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <asp:LinkButton runat="server" Text="Publish Items" ValidationGroup="StockUpdate" OnClick="btnStockPublishItems_Click" CssClass="btn btn-primary"></asp:LinkButton>
              <%--<button type="button" class="btn btn-primary">Publish Inventory</button>--%>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
    </asp:PlaceHolder>

<script type="text/javascript">
    function validatorsIsValid(vg) {
        if (!vg)
            vg = '';
        if (typeof (Page_ClientValidate) == 'function') {
            Page_ClientValidate(vg);
        }
        if (Page_IsValid)
            return true;
        else
            return false;

    }
   <%-- <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
    {  %>
    $("#txtMRP").attr('readonly', 'readonly');

    <% } %>

    <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
    {  %>
    $("#txtMRP").removeAttr('readonly');

    <% } %>--%>

    $('input.mrp').on('input', function (e) {
        if ($(this).val() != '' && !isNaN($(this).val())) {
            var lblmargin = $(this).closest('tr').find('td span.lblmargin');
            var lblmarginVal = $(this).closest('tr').find('td span.lblmarginVal');
            var lblsellingprice = $(this).closest('tr').find('td span.lblsellingprice');

            if (lblmargin && lblsellingprice && lblmargin.length > 0 && lblsellingprice.length > 0) {
                var mrp = 0; var margin = 0;
                margin = lblmargin[0].innerText;
                mrp = $(this).val();
                if (margin && margin > 0) {
                    var minmargin = (mrp * margin) / 100;
                    lblmarginVal[0].innerText =  minmargin;
                    lblsellingprice[0].innerText = (mrp - minmargin);
                }
            }
        }
    });

</script>
    <%--<script>

        $(document).ready(function () {
             <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
    {  %>
            $('.readonly').change(function () {
                $(this).attr('readonly', true);
            });
            <% } %>
        });
    </script>--%>
    <asp:HiddenField ID="hidERP_stitid" runat="server" />
    <div id="modalSetErpId" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">
              <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Set ERP Id / Barcode</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="section-wrapper p-0 border-0">
              
              <div class="row row-sm">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label w-100">Code*</label>
                      <asp:TextBox ID="txtCode" runat="server" CssClass="form-control" placeholder="ERP / Barcode"></asp:TextBox>
                      <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtCode" ErrorMessage="Enter Code" ValidationGroup="SetERPID"></asp:RequiredFieldValidator>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="form-control-label">Type*</label>
                      <asp:DropDownList ID="selERPType" CssClass="form-control" runat="server">
                          <asp:ListItem Text="Select Type" Value=""></asp:ListItem>
                          <asp:ListItem Text="Store Code" Value="0"></asp:ListItem>
                          <asp:ListItem Text="Company Barcode" Value="1"></asp:ListItem>
                      </asp:DropDownList>
                      <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selERPType" ErrorMessage="Select code type" ValidationGroup="SetERPID"></asp:RequiredFieldValidator>
                  </div>
                </div>
  
              </div> <!--row-->

            </div><!--section-wrapper-->       

              <div class="modal-btn">
                  <asp:Button runat="server" ID="btnSetErpID" ValidationGroup="SetERPID" OnClientClick="return validatorsIsValid('SetERPID');" OnClick="btnSetErpID_Click" CssClass="btn btn-primary mr-2 bd-0" Text="Save" formnovalidate/>
                    <a href="javascript:void(0)" class="btn btn-secondary bd-0"  data-dismiss="modal" aria-label="Close" style="width:100px">Cancel</a>
              </div>
            
          </div><!--modal-body-->
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <div id="modalReturnDays" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-body">
              <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Return Days</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
                </div>
            <div class="section-wrapper p-0 border-0">
              
              <div class="row row-sm">
                <div class="col-lg-12">
                  <div class="form-group">
                    <label class="form-control-label w-100">Return Days</label>
                      <asp:TextBox ID="txtReturnDays" runat="server" CssClass="form-control" placeholder="Return days" ValidationGroup="ReturnDays"></asp:TextBox>
                  </div>
                </div>
                  <div class="col-6 mb-6" runat="server" visible="false">
                    <asp:CheckBox ID="chkSpotReturn" TextAlign="Left" runat="server" Checked='<%# Eval("is_spotReturn").Equals("Active") %>'/>
                <span>Spot Return</span>
                </div><!-- col-3 -->
              </div> <!--row-->

            </div><!--section-wrapper-->       

              <div class="modal-btn mt-3">
                  <asp:Button runat="server" ID="btnReturnDays" OnClick="btnReturnDays_Click" CssClass="btn btn-primary mr-2 bd-0" Text="Save" ValidationGroup="ReturnDays" formnovalidate />
                  <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
              </div>
          </div><!--modal-body-->
          
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
              <div class="modal-title d-flex w-100 justify-content-between">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
              </div>
            
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-secondary pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <!-- Hidden field to track state -->
<asp:HiddenField ID="hdnModalOpen" runat="server" />

    <!-- Modal -->
<div id="modalVerify" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="verifyModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-vertical-center modal w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-body">
                <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Confirm or Correct HSN & Tax%</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="section-wrapper p-0 border-0">
                    <div class="row row-sm mb-2">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="form-control-label" style="width: 100%;">HSN Code <span class="tx-danger">*</span></label>
                                <%--<asp:DropDownList ID="selHSN" runat="server" CssClass="form-control select2" AutoPostBack="true"
                                    DataSourceID="SDSHsn" DataTextField="hsn_code" DataValueField="hsn_id" AppendDataBoundItems="true"
                                    OnSelectedIndexChanged="selHSN_SelectedIndexChanged">
                                    <asp:ListItem Text="Select HSN/SAC" Value="" />
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSHsn"  ProviderName="MySql.Data.MySqlClient" 
                                    SelectCommand="SELECT hsn_id, hsn_code FROM finascop_hsn ORDER BY hsn_code">
                                </asp:SqlDataSource>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selHSN" ErrorMessage="Select HSN/SAC" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>--%>
                                <asp:TextBox ID="txtHSNCode" runat="server" CssClass="form-control"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtHSNCode" ErrorMessage="Enter HSN Code" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div runat="server" class="form-group">
                                <label class="form-control-label" style="width: 100%;">Tax % <span class="tx-danger">*</span></label>
                                <%--<asp:DropDownList ID="selType" Visible="true" runat="server" CssClass="form-control" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selType_SelectedIndexChanged" OnDataBound="selType_DataBound">
                                    <asp:ListItem Text="Select Tax" Value="" />
                                </asp:DropDownList>--%>
                                <%--<asp:TextBox ID="txtTax" runat="server" CssClass="form-control" Visible="false" Enabled="false"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selType" ErrorMessage="Select Tax" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>--%>
                                <asp:TextBox ID="txtTax" runat="server" CssClass="form-control"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtTax" ErrorMessage="Enter Tax" ValidationGroup="VerifyHSN"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-4" runat="server" visible='<%# ConfigurationManager.AppSettings["CountryCode"] == "IN" %>'>
                            <div class="form-group">
                                <label class="form-control-label w-100">CESS: </label>
                                <asp:TextBox ID="txtCess" runat="server" CssClass="form-control"></asp:TextBox>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn">
                        <asp:Button ID="btnHSNVerify" runat="server" CssClass="btn btn-primary" Text="Submit" OnClick="btnHSNVerify_Click" ValidationGroup="VerifyHSN"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript">
        <%--$(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "<%= (this.CurrentUser.TenantStage != 1? "/storecompletion" : "/") %>";
            });
        });--%>
        function whenInventoryChanged() {
            $('#hidInvChanges').val('1');
        }
        function loadErpId(obj) {
            var erpid = $(obj).attr('erpid');
            var erptype = $(obj).attr('erptype');
            var stitid = $(obj).attr('stitid');
            $('#<%= hidERP_stitid.ClientID %>').val(stitid);
            $('#<%= txtCode.ClientID%>').val(erpid);
            if (erptype)
                $('#<%= selERPType.ClientID%>').val(erptype);
            $('#modalSetErpId').find('.modal-footer').removeClass('processing_loader');
            $('#<%= btnSetErpID.ClientID%>').on('click', function () {
                $(obj).attr('erpid', $('#<%= txtCode.ClientID%>').val());
                $(obj).attr('erptype', $('#<%= selERPType.ClientID%>').val());                
            });
            $('#modalSetErpId').modal('show');
        }


function loadReturnDays(obj) {
            var spotreturn = $(obj).attr('spotreturn');
            var returndays = $(obj).attr('returndays');
            var stitid = $(obj).attr('stitid');
            $('#<%= hidERP_stitid.ClientID %>').val(stitid);
            $('#<%= chkSpotReturn.ClientID%>').val(spotreturn);
            if (returndays)
                $('#<%= txtReturnDays.ClientID%>').val(returndays);
            if(spotreturn == 1)
                $('#<%= chkSpotReturn.ClientID%>').prop('checked', true);
            else
                $('#<%= chkSpotReturn.ClientID%>').prop('checked', false);
            $('#modalReturnDays').find('.modal-footer').removeClass('processing_loader');
            $('#<%= btnReturnDays.ClientID%>').on('click', function () {
                $(obj).attr('spotreturn', $('#<%= chkSpotReturn.ClientID%>').val());
                $(obj).attr('returndays', $('#<%= txtReturnDays.ClientID%>').val());                
            });
            $('#modalReturnDays').modal('show');
        }

        function checksponsoredchange(obj, margin) {
            var oldValue = $(obj).attr('defaultval');
            var mrp = $(obj).closest('tr').find('input.mrp').val();
            var sellingprice = $(obj).closest('tr').find('input.sellingprice').val();
            var returnDays = $(obj).closest('tr').find('input.returnDays').val();
            if(mrp && sellingprice) {
                var mrpval = parseFloat(mrp); var sellingval = parseFloat(sellingprice); var marginval = parseFloat(margin);
                if (mrpval > 0 && sellingval > 0 && marginval > 0) {
                    if ((100 - ((sellingval * 100) / mrpval)) < marginval)
                        if (!confirm('The revised price will diminish the agreed margin for this product to sell it in other merchant sites as sponsored product. If you continue, the item will be removed from the list of sponsored products. Are you sure you want to continue?'))
                            $(obj).val(oldValue);
                }
            }

            return true;
        }

        function hideDecimalWarning(element) {
            var hasRestaurantService = $(obj).attr('defaultval');
            var decimalWarning = document.getElementById("decimalWarning");

            // Check the condition
            if (hasRestaurantService === 1 && element.value.includes(".")) {
                // Show warning
                decimalWarning.style.display = "inline";
            } else {
                // Hide warning
                decimalWarning.style.display = "none";
            }
        }
    </script>
    <script type="text/javascript">
        $('.toggle').toggles(
            {
                //on: true,
                height: 26
            },
            //checkbox:
        );
        $('.toggle').on('toggle', function (e, active) {
            $(this).closest('td').find('input[type=checkbox]').trigger('click');
            $(this).addClass('processing_loader');
            //if (active) {
            //    console.log('Toggle is now ON!');
            //} else {
            //    console.log('Toggle is now OFF!');
            //}
        });

    </script>

    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/StockPrice";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('.collapse').on('show.bs.collapse', function () {
                $(this).closest('tr').find('.toggle-wrapper').hide();
                $(this).closest('tr').find('.dropdown-action').addClass('d-none');
                /*$(this).closest('tr').find('.collapse-action').show();*/
                /*$(this).closest('tr').find('.dropdown-menu').addClass('d-none');*/
                $('.stock-textbox').click(function (e) {
                    e.stopPropagation(); 
                });
                $('.mrp-textbox').click(function (e) {
                    e.stopPropagation();
                });
                $('.sellprice-textbox').click(function (e) {
                    e.stopPropagation();
                });
                $('.dsellprice-textbox').click(function (e) {
                    e.stopPropagation();
                });

            });

            $('.collapse').on('hide.bs.collapse', function () {
                $(this).closest('tr').find('.toggle-wrapper').show();
                $(this).closest('tr').find('.dropdown-action').removeClass('d-none');
                /*$(this).closest('tr').find('.collapse-action').hide();*/
                /*$(this).closest('tr').find('.dropdown-menu').removeClass('d-none');*/
            });
        });
    </script>

    <script type="text/javascript">
        function handleAvailabilityClick(toggleDiv, verificationStatus, stitId, branchId, storeGroup, chkUniqueID, hsn, tax, cess) {
            const isOn = toggleDiv.getAttribute("data-toggle-on") === "true";
            const verifiedKey = `verified_${stitId}_${branchId}`;

            if (storeGroup > 0) {
                // No validation needed, just trigger server update
                setTimeout(function () {
                    __doPostBack(chkUniqueID, '');
                }, 0);
                return true;
            }

            // If already marked verified in this session, skip popup
            if (sessionStorage.getItem(verifiedKey) === "true") {
                setTimeout(function () {
                    __doPostBack(chkUniqueID, '');
                }, 0);
                return true;
            }

            // Handle unverified
            if (verificationStatus === "Unverified" && !isOn) {
                alert("This item is unverified. Please verify the HSN before marking as available.");
                var hiddenFieldId = '<%= hidERP_stitid.ClientID %>';
                $('#' + hiddenFieldId).val(stitId);
                $('#<%= hdnHsn.ClientID %>').val(hsn);
                $('#<%= hdnTax.ClientID %>').val(tax);
                $('#<%= hdnCess.ClientID %>').val(cess);
                $('#<%= txtHSNCode.ClientID %>').val(hsn);
                $('#<%= txtTax.ClientID %>').val(tax);
                $('#<%= txtCess.ClientID %>').val(cess);
                $('#modalVerify').modal('show');
                // Store context for modal submit
                $('#modalVerify').data('chkUniqueID', chkUniqueID);
                $('#modalVerify').data('stitId', stitId);
                $('#modalVerify').data('branchId', branchId);
                return false;
            }

            // Normal toggle for verified or OFF
            setTimeout(function () {
                __doPostBack(chkUniqueID, '');
            }, 0);
            return true;
        }

        function onHSNVerified() {
            $('#modalVerify').modal('hide');

            // Get saved context
            const chkUniqueID = $('#modalVerify').data('chkUniqueID');
            const stitId = $('#modalVerify').data('stitId');
            const branchId = $('#modalVerify').data('branchId');

            if (stitId && branchId) {
                const verifiedKey = `verified_${stitId}_${branchId}`;
                sessionStorage.setItem(verifiedKey, "true");
            }

            // Now trigger server update
            if (chkUniqueID) {
                setTimeout(function () {
                    __doPostBack(chkUniqueID, '');
                }, 0);
            }
        }
    </script>

    <style>

.linkbutton:link {
    text-decoration: none;
    color: blue;
}

.linkbutton:visited {
    text-decoration: none;
    color: blue;
}

.linkbutton:hover {
    text-decoration: underline;
    color: blue;
}

.linkbutton:active {
    text-decoration: underline;
    color: blue;

}
        .vld_error {
        position: absolute;
        bottom: -13px;
        font-size: 10px;
        }
        
        .pro_disc {
                width:300px!important;
            }
            .stock_disc {
                width:85px!important;
            }
            .mrp-disc {
                width:10%!important;
            }
            .sellprice-disc {
                width:10%!important;
            }
            .dsellprice-disc {
                width:10%!important;
            }
            .action-disc {
                width:40px !important;
            }
            .available-disc {
                width:10%!important;
            }
            .demand-disc {
                width:10%!important;
            }
            .arrow-disc {
                width:30px !important;
            }
            .form-control {
                display:inline-block;
                transition: none;
            }
            .available-disc .btn, .demand-disc .btn{
                transition:none!important;
            }
    </style>


</asp:Content>