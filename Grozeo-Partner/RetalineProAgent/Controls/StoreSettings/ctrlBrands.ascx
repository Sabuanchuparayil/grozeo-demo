<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlBrands.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlBrands" %>

<div class="card">
    <div class="card-header shadow_top">
            <div class="row row-sm align-items-lg-end">
                
                <div class="col-sm-8 col-lg-6 mt-2 mt-sm-0 d-flex align-items-end">
                    <div class="input-group ">
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <div class="input_search_box">
                          <asp:TextBox ID="txtSearchBrand" runat="server" autocomplete="off" CssClass="form-control" placeholder="Search brand"></asp:TextBox>
                          <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600" OnClick="lbtnSearch_Click" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                        </div>                        
                    </div>
                   <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/Brands.aspx" Text="Reset" />
                </div>
            </div><!--row-->
        </div><!--card heder-->
    <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvMyBrand" GridLines="None" runat="server" CssClass="table table-bordered mg-b-0 gridview_table" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvMyBrand_DataBound" DataSourceID="SDSMyBrands">
                                    <Columns>
                                        <asp:BoundField HeaderText="Brand" DataField="brand_name" SortExpression="brand_name"/>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <div class="float-right">
                                                    <asp:LinkButton ID="lbtnedit" brandId='<%# Eval("brand_id") %>' OnClick="lbtnedit_Click" CssClass="btn btn-outline-primary btn-sm" runat="server" Text="Edit"></asp:LinkButton>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3"><small>You dont have any brand/s to list.</small></h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <SortedAscendingHeaderStyle CssClass="sorting sorting_asc" />
                                    <SortedDescendingHeaderStyle CssClass="sorting sorting_desc" />
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSMyBrands" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT brand_id, brand_name, manufacture_id, mapping_id, storegroup_id FROM mypha_productbrands WHERE storegroup_id = @storegroup 
                                    AND (trim(ifnull(@searchKey, '')) like '' or brand_name like CONCAT('%', @searchKey, '%'))ORDER BY brand_name ASC"
                                    OnSelecting="SDSMyBrands_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                        <asp:ControlParameter Name="searchKey" ControlID="txtSearchBrand" Type="String" ConvertEmptyStringToNull="false" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
            </div>
        <!-- table-responsive -->
    </div>
    <!--card-body-->
</div>
<asp:HiddenField ID="hidbrandId" runat="server" />
<div id="modalBrand" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-body">
                <div id="prd_crt_new_brand" class="prd_crt_new_brand">
                    <h5 class="modal-title tx-dark" id="create_new_ProductsTitle">Edit Brand</h5>
                    <div class="py-3">
                        <h6 class="tx-dark">Edit Brand to proceed with adding new products</h6>
                        <div class="input-group mb-4 flex-wrap">
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtEditBrand" runat="server" CssClass="form-control w-100" autocomplete="off" />
                            <asp:RequiredFieldValidator runat="server" CssClass="error_msg_wrap tx-danger" SetFocusOnError="true" ErrorMessage="Please input brand name." ControlToValidate="txtEditBrand" Display="Dynamic" ValidationGroup="EditBrand"></asp:RequiredFieldValidator>
                            <span class="error_msg_wrap" id="addbranderror"><asp:Literal ID="ltrAddBrandResult" runat="server"></asp:Literal></span>
                            <asp:LinkButton runat="server" Text="Save & Create New Product" OnClick="btnAddBrand_Click" CssClass="btn btn-inline-block btn-primary mt-3" ValidationGroup="EditBrand"></asp:LinkButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
<asp:HiddenField ID="hidShowAddForm" Value="0" runat="server" />

<script type="text/javascript">
    <%--function loadBrand(obj) {
        var brandname = $(obj).attr('brandNane');
        var brandId = $(obj).attr('brandId');
        $('#<%= hidbrandId.ClientID %>').val(brandId);
        $('#<%= txtEditBrand.ClientID%>').val(brandname);
        $('#modalBrand').modal('show');
    }--%>
</script>

<style>
    .select2-container.select2-container--open {
      z-index: 1050;
    }
    .slim-sticky-sidebar .slim-header {
    z-index: 1051;
    }
    .modal-body .form-control + .select2 + span[data-val="true"] {
        bottom: -13px;
        left: 0;
    }
    #dvselectstore {
    display: none;
    }
    #dvselectstore.show {
        display: block;
    }
</style>

