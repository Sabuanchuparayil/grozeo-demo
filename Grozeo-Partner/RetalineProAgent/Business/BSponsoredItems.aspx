<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Sponsored Items" AutoEventWireup="true" CodeBehind="BSponsoredItems.aspx.cs" Inherits="RetalineProAgent.Business.BSponsoredItems" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sponsored Items</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <script src="../Content/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Sponsored Items"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <div class="d-flex">
                                <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by product name & branch." CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" Style="height: 33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvSProducts" runat="server" CssClass="table table-bordered" OnRowCommand="gvSProducts_RowCommand" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSSProducts">
                                    <Columns>
                                        <asp:BoundField HeaderText="Product" DataField="stit_SKU" SortExpression="stit_SKU" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Category" DataField="stit_category_name" SortExpression="stit_category_name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Sub-Category" DataField="subcategory" SortExpression="subcategory" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="MRP" DataField="mrp" SortExpression="mrp" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Selling Price" DataField="selling_price" SortExpression="selling_price" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Margin" DataField="grozeo_margin" SortExpression="grozeo_margin" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Merchant" DataField="storeGroupName" SortExpression="storeGroupName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Area" DataField="areaName" SortExpression="areaName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:TemplateField HeaderText="Others" SortExpression="other_merchants_count" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center">
                                                    <asp:Label CssClass="mr-2 tx-bold" ID="lblTotalCount" runat="server" Text='<%# Eval("other_merchants_count") %>'></asp:Label>
                                                    <button type="button" id="btnView"  class="btn btn-outline-primary btn-sm" itemId='<%# Eval("stit_id") %>' productname='<%# Eval("stit_SKU") %>' data-toggle="modal" data-target="#modalDetails" data-id='<%# Eval("stit_id") %>' onclick="loadDetails(<%# Eval("stit_id") %>)">View</button>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No sponsored items.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                        <asp:SqlDataSource runat="server" ID="SDSSProducts" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="WITH ranked_products AS (SELECT itm.stit_id,itm.stit_SKU,stit_category_name,product_category, br_Name,
                            IFNULL(bi.mrp, IFNULL(itemMrp, IFNULL(itm.stit_MRP, 0))) AS mrp,IFNULL(bi.selling_price, 0) AS selling_price,
                            (SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = product_category) AS subcategory,
                            (SELECT areaName FROM area_entries WHERE id=@areaId) AS areaName,
                            (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = br_storeGroup) AS storeGroupName,issponsered,
                            fb.br_Name AS branchName,fb.br_storeGroup,grozeo_margin,bi.discount_selling_price,fb.areaId AS areaId,bi.branch_id,
                            COUNT(*) OVER (PARTITION BY bi.stit_ID) AS total_count,
                            ROW_NUMBER() OVER (PARTITION BY itm.stit_SKU ORDER BY bi.grozeo_margin DESC, discount_selling_price ASC) AS rn FROM finascop_stock_branch_inventory bi
                            INNER JOIN finascop_stock_itemmaster itm ON bi.stit_ID = itm.stit_id
                            INNER JOIN finascop_branch fb ON fb.br_ID = bi.branch_id AND fb.areaId = @areaId
                            INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup AND store_group_grosmartMerchant = 1
                            LEFT JOIN (SELECT stit_Id, itemMrp FROM item_mrp WHERE stit_Id = stit_ID GROUP BY stit_Id) itemBewMrp
                            ON itemBewMrp.stit_Id = itm.stit_ID WHERE bi.issponsered = 1) SELECT stit_id,stit_SKU,stit_category_name,product_category,
                            subcategory,mrp,selling_price,grozeo_margin,branchName,areaName,storeGroupName, CONCAT(storeGroupName, '-', br_Name) AS storeName,branch_id,total_count, (total_count - 1) AS other_merchants_count FROM ranked_products WHERE rn = 1
                            AND (trim(ifnull(@searchKey, '')) like '' or stit_SKU like CONCAT('%', @searchKey, '%') 
                            or branchName like CONCAT('%', @searchKey, '%'))"
                            OnSelecting="SDSSponsoredPrd_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
              </div>
            </div>
          </div>

    <asp:HiddenField ID="hidstitId" runat="server" />
    <div class="modal fade" id="modalDetails" tabindex="-1" role="dialog" aria-labelledby="modalDetailsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="dvpopupdetails">
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        function loadDetails(itemId) {
            $('#dvpopupdetails').html('<div>Loading .. </div>');
            $('#dvpopupdetails').load('/Business/MerchandisingPrds/MerchandisingPrdsdetails?stit_id=' + itemId);
        }
    </script>

   
    <style>
        .table-bordered > thead > tr th, .table-bordered > thead > tr td {
            color: #fff;
            border-color: #13977f;
            background-color: #13977f !important;
        }
    </style>
</asp:Content>
