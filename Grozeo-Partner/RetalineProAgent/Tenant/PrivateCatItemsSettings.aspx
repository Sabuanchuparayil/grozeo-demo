<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Private Category Items" AutoEventWireup="true" CodeBehind="PrivateCatItemsSettings.aspx.cs" Inherits="RetalineProAgent.PrivateCatItemsSettings" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/">Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PrivateCategory">Private Category</a></li>
    <%--<li class="breadcrumb-item"><a href="/PrivateCatItems">Private Category Items</a></li>--%>
    <li class="breadcrumb-item active" aria-current="page">Create Private Category Items</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Products"></asp:Literal> of
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                      <div class="card-tools">
                          <div class="d-flex align-items-center justify-content-between">
                              <div class="col-sm-3 input-group-sm">
                      <label for="txtSearchProduct" runat="server">Search by:</label>
                      <asp:TextBox ID="txtSearchProduct" runat="server" CssClass="form-control" placeholder="Product name"></asp:TextBox> 
                  </div>
                         <div class="d-sm-flex p-3 wiz_btnsect justify-content-center">
              <asp:Button CssClass="btn btn-primary btn-block mx-2 wd-sm-auto-force px-4" ID="btnSaveProducts" OnClick="btnSaveProducts_Click" runat="server" Text="Save Products" />
          </div>     
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="input-group-btn">
                    
                        </div>
                    </div>
                </div>
              </div>
            </div>
                    
                
                <asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" OnDataBound="lstProducts_DataBound" 
                   OnItemDataBound="lstProducts_ItemDataBound" ItemPlaceholderID="plsProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" >
                <LayoutTemplate>
				<table class="table table-bordered mg-b-0">
                                    <thead>
                                        <tr>
                                            <th>
<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
<i class="fa fa-square tx-white"></i>
                    </button>
                    </th>
                            <th>Product Name</th>
                            <th>Product Master</th>
                            <th>Brand</th>
                            <th>Sub Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>

                                        <tr>
            <td colspan = "2">
                <asp:DataPager ID="DataPager1" runat="server" PagedControlID="lstProducts" PageSize="20">
                    <Fields>
                        <asp:NextPreviousPagerField ButtonType="Link" ButtonCssClass="btn btn-default btn-sm" PreviousPageText="<" ShowFirstPageButton="false" ShowPreviousPageButton="true" ShowNextPageButton="false" />
                        <asp:NumericPagerField ButtonType="Link" NumericButtonCssClass="btn btn-default btn-sm" />
                        <asp:NextPreviousPagerField ButtonType="Link" NextPageText=">" ShowNextPageButton="true" ButtonCssClass="btn btn-default btn-sm" ShowLastPageButton="false" ShowPreviousPageButton = "false" />
                    </Fields>
                </asp:DataPager>
            </td>                       
        </tr>

				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>

                            <tr class="<%# ( !IsSelected(Eval("stit_Id").ToString())) %>">
                                <td><asp:CheckBox ID="chkProductItem" onclick="updateSelection(this)" OnCheckedChanged="chkProductItem_CheckedChanged" virtualCat_id='<%# Eval("vc_id") %>' itemid='<%# Eval("stit_Id") %>' itemType='<%# Eval("stit_type") %>' Checked='<%# IsSelected(Eval("stit_Id").ToString()) %>' runat="server" /></td>
						        <td><%# Eval("stit_SKU") %></td>
                                <td><%# Eval("stit_itemName") %></td>
                                <td><%# Eval("stit_brand_name") %></td>
                                <td><%# Eval("stit_category_name") %></td>
					        </tr>
                    

                    
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>
                <asp:SqlDataSource ID="SDSProducts" runat="server" OnSelected="SDSProducts_Selected"  OnSelecting="SDSBrands_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
 SelectCommand="SELECT stit_itemName,sit.stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,vci.vc_id,vci.stit_id,vci.stit_type,
(SELECT category_name FROM mypha_productcategory mc WHERE category_id = (SELECT main_category FROM mypha_productsubcategory 
WHERE sub_category_id = product_category)) AS mainCategory,(SELECT parent_category FROM mypha_productparent_category DEP 
WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory mc WHERE category_id = 
(SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS department FROM finascop_stock_itemmaster sit
INNER JOIN retaline_vc_items vci ON vci.stit_id=sit.stit_ID WHERE 1=1 AND isMedicine=0 AND stit_status = 1 
AND (trim(ifnull(@searchKey, '')) like '' or stit_SKU like CONCAT('%', @searchKey, '%'))  ORDER BY stit_SKU ASC" ProviderName="MySql.Data.MySqlClient">

<SelectParameters>
    <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" ConvertEmptyStringToNull="false" />
    <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
    <%--<asp:Parameter Name="brand" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="category" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="department" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />--%>
</SelectParameters>
</asp:SqlDataSource>
                
                </div>
            </div>
            </div>
   <asp:HiddenField ID="hidSelectedItems" runat="server" />
    <script type="text/javascript">
    function updateSelection(obj) {
        if ($(obj).is(':checkbox')) {
            var id = $(obj).closest('span').attr('itemid');
            if (!id)
                return;

            if ($(obj).is(':checked')) {
                addItem(id);
                $(obj).closest('tr').addClass('checked_now')
            }
            else {
                removeItem(id);
                $(obj).closest('tr').removeClass('checked_now').removeClass('already_added');
            }
        }
    }

    function addItem(id) {
        var ids = new Array();
        if ($('#<%= hidSelectedItems.ClientID %>').val() != '')
            ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        if(id)
            ids.push(id);

        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));

    }
    function removeItem(id) {
        var ids = $('#<%= hidSelectedItems.ClientID %>').val().split(',');
        ids = jQuery.grep(ids, function (value) {
            return value != id;
        });
        $('#<%= hidSelectedItems.ClientID %>').val(ids.join(","));
        }

    </script>
</asp:Content>
