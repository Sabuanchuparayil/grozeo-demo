<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Private Category Items" AutoEventWireup="true" CodeBehind="PrivateCatItems.aspx.cs" Inherits="RetalineProAgent.PrivateCatItems" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PrivateCategory">Private Category</a></li>
    <li class="breadcrumb-item active" aria-current="page">Private Category Items</li>--%>
    <a href='<%= GetBackLink() %>'><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Products"></asp:Literal> of
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                
                    
                <div class="card-body">
               <div class="table-responsive">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvPrivateCatItems" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvPrivateCatItems_DataBound" DataSourceID="SDSPrivateCatItems">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Item Name">
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center">
                                                    <div class="prodct_img">
                                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'"  ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        <div class="imgpopover">
                                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'"  ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        </div>
                                                        <asp:HiddenField ID="hidStitID" runat="server" Value='<%# Eval("stit_id") %>' />
                                                    </div>
                                                        <asp:Label runat="server" ID="lblName" CssClass="prd_name" ToolTip ='<%# Eval("itemName") %>'><%# Eval("itemName") %></asp:Label>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>

                                        <%--<asp:BoundField HeaderText="Item Name" DataField="itemName" SortExpression="itemName"/>--%>
                                        <asp:BoundField HeaderText="Item Type" DataField="itemType" SortExpression="itemType"/>
                                        <asp:BoundField HeaderText="Sub Category" DataField="stit_category_name" SortExpression="stit_category_name"/>
                                        <asp:BoundField HeaderText="Brand" DataField="stit_brand_name" SortExpression="stit_brand_name"/>
                                        <asp:BoundField HeaderText="Quantity" DataField="stit_quantity" SortExpression="stit_quantity"/>
                                        <asp:BoundField HeaderText="Least Packing Unit" DataField="least_package_type_name" SortExpression="least_package_type_name"/>
                                        <%--<asp:BoundField HeaderText="Delete"  HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSPrivateCatItems" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT vc_id, retaline_vc_items.stit_id, stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,finascop_stock_itemmaster.stit_id AS itemId,
 (SELECT image_url FROM finascop_stock_item_images WHERE product_id=finascop_stock_itemmaster.stit_id ORDER BY image_type DESC LIMIT 1) AS imageurl, 
stit_SKU AS itemName,stit_brand_name,stit_quantity,least_package_type_name,stit_category_name FROM retaline_vc_items 
INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_id= retaline_vc_items.stit_id WHERE vc_id=@prdtCatId"
                                    >
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="id" Name="prdtCatId" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
            </div>
            </div>
   
</asp:Content>

