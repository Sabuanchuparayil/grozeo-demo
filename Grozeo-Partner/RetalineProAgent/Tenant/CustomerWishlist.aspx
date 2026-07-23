<%@ Page Language="C#" AutoEventWireup="true" Title="Customer Wishlist" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="CustomerWishlist.aspx.cs" Inherits="RetalineProAgent.Tenant.CustomerWishlist" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="SaleAndReturnOrders"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle" runat="server" Text="Customer Wishlist"></asp:Literal></h6>
        <p class="mb-0">View and Manage customer wishlist from here</p>
    </div>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">

                <div class="col-12 d-flex justify-content-end">
                    <asp:LinkButton runat="server" ID="lbtnDownloadExcel" CssClass="btn btn-inline-block btn-outline-primary" OnClick="lbtnDownloadExcel_Click"><i class="fa fa-download mr-1"></i> Download</asp:LinkButton>
                </div>
            </div>


            <div class="row row-sm">
                <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-lg-b-0">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Filter By</label>
                    <asp:DropDownList ID="selStatus" OnSelectedIndexChanged="selStatus_SelectedIndexChanged" AutoPostBack="true" CssClass="form-control select2" runat="server">
                        <asp:ListItem Text="Status" Value="-1"></asp:ListItem>
                        <asp:ListItem Text="Item without Stock" Value="1"> </asp:ListItem>
                        <asp:ListItem Text="Item without Price" Value="2"> </asp:ListItem>
                    </asp:DropDownList>
                </div>

                <div class="col-sm-8 col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
                    <div style="display: none;">
                        <input type="text" name="name_emailField" />
                        <input type="password" name="passwordField" />
                    </div>
                    <asp:TextBox ID="txtSKU" runat="server"  autocomplete="off" CssClass="form-control" placeholder="SKU Name" name="SKUName"></asp:TextBox>
                </div>

                <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom">From</label>
                    <asp:TextBox ID="txtFromDate" runat="server" TextMode="Date" CssClass="form-control" placeholder="From Date" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>
                </div>
                <div class="col-sm-4 col-lg-2 input-group mg-b-10 mg-sm-b-0">

                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateTo">To</label>
                    <asp:TextBox ID="txtToDate" runat="server" TextMode="Date" CssClass="form-control" placeholder="To Date" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>

                </div>
                <div class="col-sm-4 col-lg-2 d-flex justify-content-lg-end align-items-lg-end d-flex align-items-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                    <asp:LinkButton runat="server" ID="btnreset"  CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2" OnClick="btnreset_Click" Text="Reset"></asp:LinkButton>

                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <asp:GridView AutoGenerateColumns="false" DataSourceID="SSDSCustomerWishlist" ID="gvCustomerWishlist" runat="server" CssClass="table table-bordered gridview_table" GridLines="none" BorderColor="#ECECEC"
                AllowPaging="true" AllowSorting="false" HorizontalScrollBarMode="Visible" verticalScrollBarMode="Visible"
                ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                <Columns>
                    <asp:BoundField HeaderText="SKU" DataField="SKU" SortExpression="SKU" />
                    <asp:BoundField HeaderText="Total Count" DataField="TotalCount" SortExpression="TotalCount" />
                    <asp:BoundField HeaderText="Last Added" DataFormatString="{0:dd-MMM-yyyy}" DataField="LastAdded" SortExpression="LastAdded" />
                                  
                </Columns>

                <EmptyDataTemplate>
                    <div class="text-center">
                       <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                        <h6 class="mb-3">No record available</h6>
                    </div>
                </EmptyDataTemplate>
                <PagerStyle CssClass="cssPager" HorizontalAlign="Center"/>
                <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                     
            </asp:GridView>

         </div>
    </div>

    <asp:SqlDataSource runat="server" ID="SSDSCustomerWishlist" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT r.product_id,bi.branch_id,f.stit_SKU AS SKU,COUNT(r.product_id) AS TotalCount,DATE(MAX(r.updated_at)) AS LastAdded,bi.item_count,bi.selling_price
                       FROM retaline_saved_items r INNER JOIN finascop_stock_itemmaster f ON r.product_id = f.stit_ID
                       INNER JOIN finascop_stock_branch_inventory bi ON f.stit_ID = bi.stit_id
                       WHERE bi.branch_id=@BranchId AND
                       (trim(@search) like '' or f.stit_SKU like CONCAT('%', @search, '%'))
                       AND ( @status = -1 
                             OR (@status = 0 AND IFNULL(@status, -1) = 0) 
                             OR (@status = 1 AND bi.item_count = 0) 
                             OR (@status = 2 AND bi.selling_price = 0) 
                           ) 
                      AND (@fromDate is null or @fromDate = '' or CAST(r.updated_at AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate is null or @toDate = '' or CAST(r.updated_at AS DATE) <= CAST(@toDate AS DATE))
                      GROUP BY r.product_id, f.stit_SKU, bi.item_count, bi.selling_price;">

        <SelectParameters>

            <asp:ControlParameter Name="fromDate" ControlID="txtFromDate" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtToDate" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="search" ControlID="txtSKU" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="status" ControlID="selStatus" ConvertEmptyStringToNull="false" />
            <asp:QueryStringParameter Name="BranchId" QueryStringField="BranchId" />
          
        </SelectParameters>

    </asp:SqlDataSource>

</asp:Content>

