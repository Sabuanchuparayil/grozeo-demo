<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Async="true" Title="Products" AutoEventWireup="true" CodeBehind="PrivateCatSettings.aspx.cs" Inherits="RetalineProAgent.PrivateCatSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PrivateCategory">Private Category</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Category</li>--%>
    <a href='<%= GetBackLink() %>'><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Add Items</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex align-items-center justify-content-between row row-sm">
                <div class="col-lg-6">
                    <div class="input-group p-0">
                        <label for="txtSearchProduct" class="w-100" runat="server">Search by</label>
                        <input type="text" style="display:none" />
                        <input type="password" style="display:none" />
                        <div class="d-flex w-100">
                            <asp:TextBox ID="txtSearchProduct" runat="server" CssClass="form-control" placeholder="Search by product name, product master, brand, etc." autocomplete="off"></asp:TextBox>
                            <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-inline-block btn-primary d-inline-block ml-2" runat="server">Search</asp:LinkButton>
                        </div>
                    </div>

                </div>
                <%--<div class="d-sm-flex p-3 wiz_btnsect justify-content-center">
              <asp:Button CssClass="btn btn-primary btn-block mx-2 wd-sm-auto-force px-4" ID="btnSaveProducts" OnClick="btnSaveProducts_Click" runat="server" Text="Save Products" />
          </div> --%>
            </div>
        </div>
        <div class="card-body">
            <asp:ListView ID="lstProducts" runat="server" DataSourceID="SDSProducts" OnDataBound="lstProducts_DataBound"
                OnItemDataBound="lstProducts_ItemDataBound" ItemPlaceholderID="plsProducts" AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true">
                <LayoutTemplate>
                    <table class="table table-bordered mg-b-0">
                        <thead>
                            <tr>
                                <th>
                                    <%--<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
<i class="fa fa-square tx-white"></i>
                    </button>--%>
                                </th><th>image</th>
                                <th>Product Name</th>
                                <th>Product Master</th>
                                <th>Brand</th>
                                <th>Sub Category</th>
                            </tr>
                        </thead>
                        <tbody>

                            <asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>

                            <tr>
                                <td colspan="6">
                                <div class="pagenation_listview">
                                    <asp:DataPager ID="DataPager1" runat="server" PageSize="10" PagedControlID="lstProducts">
                            <Fields>
                                                              <asp:NumericPagerField ButtonType="Link" CurrentPageLabelCssClass="btn btn-primary disabled" RenderNonBreakingSpacesBetweenControls="false"
                                                                  NumericButtonCssClass="btn btn-default" ButtonCount="5" NextPageText="..." NextPreviousButtonCssClass="btn btn-default" />
                                                          </Fields>
                        </asp:DataPager>
                                </div>
                            </td>
                            </tr>

                        </tbody>
                    </table>
                </LayoutTemplate>
                <ItemTemplate>
                    <tr isincart="<%# Eval("incat") %>" class="<%# (Convert.ToInt32(Eval("incat")) > 0 ? "already_added" : (IsSelected(Eval("stit_Id").ToString()) ? "checked_now" : "" ))  %>">

                        <td>
                            <asp:CheckBox ID="chkProductItem" onclick="updateSelection(this);" OnCheckedChanged="chkProductItem_CheckedChanged" Checked='<%# IsSelected(Eval("stit_Id").ToString()) %>' itemid='<%# Eval("stit_ID") %>' runat="server" />
                        </td>
                        <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="prodct_img">
                                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'"  ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        <div class="imgpopover">
                                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'"  ImageUrl='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("imageurl").ToString()) %>' />
                                                        </div>
                                                        <asp:HiddenField ID="hidStitID" runat="server" Value='<%# Eval("stit_ID") %>' />
                                                    </div>
                                                </div>

                        </td>
                        <td><%# Eval("stit_SKU") %></td>
                        <td><%# Eval("stit_itemName") %></td>
                        <td><%# Eval("stit_brand_name") %></td>
                        <td><%# Eval("stit_category_name") %></td>
                    </tr>
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>
        </div>
        <div class="card-footer d-flex justify-content-center justify-content-lg-end">
            <div class="d-inline-block">
                <%--<a href="/PrivateCatItems" class="btn btn-primary bd-0" style="height:45px; width:100px">Add Items</a>--%>
                <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" UseSubmitBehavior="false" CssClass="btn btn-primary" Text="Save" />
                <a href='<%= GetBackLink() %>' class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>


    <div class="section-wrapper p-3 d-none">
          <%--<label class="section-title">Add New Category</label>--%>
          <div class="form-layout">
            <div class="row row-sm" runat="server" visible="false">
              <div class="col-lg-4">
                <div class="form-group-sm">
                  <label class="form-control-label">Private Category: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtVirtCat" runat="server" required CssClass="form-control mb-2" placeholder="Enter private category" autocomplete="off"/>
                </div>
              </div><!-- col-4 -->
                              <div class="col-lg-4">
                <asp:Panel ID="pnlUploadImage" runat="server" CssClass="form-group-sm addnew_imagebox">
                  <label>Upload Image</label>
                    <asp:FileUpload runat="server" ID="fileUploadImgs" CssClass="form-control mb-2" />
                </asp:Panel>
                <asp:Panel ID="pnlCategoryImage" Visible="false" runat="server" CssClass="form-group-sm imageprivew_box">
                  <label>&nbsp;</label>
                  <div class="p-1 border">
                      <asp:Image ID="imgCatImage" CssClass="addedimage" runat="server" style="max-width: 50px; max-height: 33px; width: auto; height: auto;border: solid 1px lightgray;"/>
                      <asp:LinkButton runat="server" CssClass="change_image ml-1" ID="lbtnDeleteImg" OnClick="lbtnDeleteImg_Click" Text="Delete image"></asp:LinkButton>
                  </div>

                </asp:Panel>

              </div>

                <div class="col-lg-4 d-flex align-items-center">
                <div class="mg-t-0 mr-2">
                  <asp:CheckBox ID="chkHome" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("vc_isHome").Equals("Active") %>'/>
                <span>Include in Home Menu</span>
                </div>
                
                <div class="mg-t-0">
                  <asp:CheckBox ID="chkCat" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("vc_isInCategory").Equals("Active") %>'/>
                  <span>Show in Category List</span>
                </div>
              </div>

                <div class="col-12">

                    <div class="row row-sm">
                        <div class="col-lg-6">
<div class="form-group-sm">
                  <label class="form-control-label" id="lblDept" runat="server">Include with the department: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDept" runat="server" AutoPostBack="true" CssClass="form-control select2 select2-hidden-accessible mb-3" ForeColor="GrayText" DataSourceID="SDSDepartment" DataTextField="parent_category" AppendDataBoundItems="true" DataValueField="parent_category_id" Visible="false"><asp:ListItem Text="Select department" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSDepartment" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT parent_category_id, parent_category FROM mypha_productparent_category pc
                INNER JOIN finascop_branch_group_business_type bgt ON pc.parent_category_businessType=bgt.business_type_id 
                WHERE store_group_id=@storegroup" OnSelecting="SDSDepartment_Selecting">
                        <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
                    </asp:SqlDataSource>
                </div>    
                        </div>
                        <div class="col-lg-6">
<div class="form-group-sm">
                  <label class="form-control-label" id="lblCat" runat="server">List under category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible mb-3" ForeColor="GrayText" DataSourceID="SDSCat" DataTextField="category_name" AppendDataBoundItems="true" DataValueField="category_id" Visible="false"><asp:ListItem Text="Select category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT category_id, category_name, parent_category_businessType, store_group_id FROM mypha_productcategory mp
                INNER JOIN mypha_productparent_category pc ON mp.parent_category=pc.parent_category_id 
                INNER JOIN finascop_branch_group_business_type bgt ON pc.parent_category_businessType=bgt.business_type_id 
                WHERE store_group_id=@storegroup AND mp.parent_category=@depName" OnSelecting="SDSCat_Selecting">
                        <SelectParameters>
                            <asp:Parameter Name="storegroup" />
                            <asp:ControlParameter Name="depName" ControlID="selDept" />
                        </SelectParameters>
                      </asp:SqlDataSource>
                    </div>
                   </div>
                 </div>
               </div>

                </div><!-- row -->
                <div class="card">
                <div class="card-header">
                      <div class="card-tools">
                          
              </div>
            </div>
                    
                
                


                
                </div>
                
            
          </div><!-- form-layout -->
        </div>

    <asp:SqlDataSource ID="SDSProducts" runat="server" OnSelected="SDSProducts_Selected" OnSelecting="SDS_Selecting"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
  ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT stit_itemName,sit.stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name, 
CASE WHEN EXISTS(SELECT * FROM retaline_vc_items WHERE ifnull(@vcid, 0) > 0 and vc_id = @vcid and stit_Id = sit.stit_Id) THEN 1 ELSE 0 END AS incat 
, (SELECT image_url FROM finascop_stock_item_images WHERE product_id=stit_id ORDER BY image_type DESC LIMIT 1) AS imageurl 
 FROM finascop_stock_itemmaster sit WHERE stit_status = 1 
AND EXISTS(SELECT * FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE stit_id=sit.stit_ID AND b.br_storeGroup= @storeId) 
AND (trim(ifnull(@searchKey, '')) like '' or stit_SKU like CONCAT('%', @searchKey, '%') or stit_itemName like CONCAT('%', @searchKey, '%') or stit_brand_name like CONCAT('%', @searchKey, '%') or stit_category_name like CONCAT('%', @searchKey, '%')) GROUP BY sit.stit_ID ORDER BY stit_SKU ASC"
>

<SelectParameters>
    <asp:ControlParameter Name="searchKey" ControlID="txtSearchProduct" ConvertEmptyStringToNull="false" />
    <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
    <asp:QueryStringParameter QueryStringField="id" Name="vcid" DefaultValue="0" />
    <%--<asp:Parameter Name="brand" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="category" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="department" Type="Int32" DefaultValue="0" />
    <asp:Parameter Name="type" Type="Int32" DefaultValue="0" />--%>
</SelectParameters>
</asp:SqlDataSource>
    <asp:HiddenField ID="hidSelectedItems" runat="server" />
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
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
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
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/PrivateCategory";
            });
        });

    </script>
</asp:Content>