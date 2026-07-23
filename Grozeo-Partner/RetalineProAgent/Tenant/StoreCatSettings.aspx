<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products" AutoEventWireup="true" CodeBehind="StoreCatSettings.aspx.cs" Inherits="RetalineProAgent.StoreCatSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/StoreCategory">Store Category</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Store Category</li>--%>
    <a href="/Tenant/StoreCategory"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Create Store Category Group</h6>
    <p class="mb-0">Create New Store Category Group</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body p-3 shadow_top">
            <div class="form-layout">
                <div class="row row-sm">
                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <div class="form-group-sm">
                            <label class="tx-dark mb-1 w-100">Store Category: <span class="tx-danger">*</span></label>
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />
                            <asp:TextBox ID="txtPrivateCat" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter category " />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtPrivateCat" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Category is required" ValidationGroup="StoreCat" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-sm-8 mb-0">
                        <label class="tx-dark mb-1 w-100">Retail Categories: <span class="tx-danger">*</span></label>
                        <asp:ListBox ID="lstBusinessTypes" SelectionMode="Multiple" OnDataBound="lstBusinessTypes_DataBound" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                            CssClass="form-control select2" multiple="multiple"></asp:ListBox>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="lstBusinessTypes" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Retail category is required" ValidationGroup="StoreCat" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                    <!-- col-4 -->
                    <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT bt.business_type_id,bt.business_type_name FROM mypha_productsubcategory 
                        INNER JOIN finascop_stock_itemmaster ON product_category = sub_category_id 
                        INNER JOIN mypha_productbrands ON brand_id = pdt_brand
                        INNER JOIN `mypha_productcategory` mpc ON `category_id`=`main_category` 
                        INNER JOIN mypha_productparent_category ppc ON parent_category_id = mpc.parent_category
                        INNER JOIN finascop_business_type bt ON bt.business_type_id= ppc.`parent_category_businessType`
                        INNER JOIN finascop_branch_group_business_type bg ON bg.business_type_id = bt.business_type_id
                        WHERE bg.store_group_id=@storegroup AND bt.status=1 GROUP BY business_type_id"
                        OnSelecting="SDSBusinessTypes_Selecting"
                        ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:Parameter Name="storegroup" />
                        </SelectParameters>
                    </asp:SqlDataSource>

                     <div class="col-12 mt-3">
                    <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" CssClass="btn btn-primary mr-2 bd-0" Text="Submit Form" ValidationGroup="StoreCat" />
                    <a href="/Tenant/StoreCategory" class="btn btn-secondary bd-0" style="width: 100px">Cancel</a>
                </div>
                </div>
                <!-- row -->
               
                <div class="form-layout-footer">
                    <asp:Label ID="lblResult" runat="server"></asp:Label>
                </div>
                <!-- form-layout-footer -->

                <div id="modaldemo1" class="modal fade">
                    <div class="modal-dialog modal-dialog-vertical-center" role="document">
                        <div class="modal-content bd-0 tx-14">
                            <div class="modal-header">
                                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">
                                    <asp:Literal ID="ltrPopupTitle" runat="server"></asp:Literal></h6>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body pd-25">
                                <asp:Literal ID="ltrModelBodyContent" runat="server"></asp:Literal>
                                <%--<h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Why We Use Electoral College, Not Popular Vote</a></h5>
            <p class="mg-b-5">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>--%>
                            </div>

                        </div>
                    </div>
                    <!-- modal-dialog -->
                </div>
                <!-- modal -->
            </div>
            <!-- form-layout -->
        </div>
          <%--<label class="section-title">Create New Category Group</label>--%>
          
        </div>
    

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
    
    <style>
        .select2-container--default {
            width:100%!important;
        }
    </style>
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/StoreCategory";
            });
        });
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            $('.duallistbox').bootstrapDualListbox();

        });
    </script>
</asp:Content>