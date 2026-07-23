<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Create Coupon" AutoEventWireup="true" CodeBehind="CreateCoupon.aspx.cs" Inherits="RetalineProAgent.CreateCoupon" %>


<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
    <link rel="stylesheet" href="/Content/css/bootstrap-multiselect.min.css">
    <script src="/Content/js/bootstrap-multiselect.min.js"></script>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
   <a href="/Tenant/DiscountCoupons"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="section-wrapper p-3">
        <div class="form-layout">
            <div class="row mg-b-25">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-control-label">All Stores / Store </label>
                        <asp:DropDownList ID="selStore" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                            <asp:ListItem Value="0">Select All Stores / Store</asp:ListItem>
                            <asp:ListItem Value="1">All Stores</asp:ListItem>
                            <asp:ListItem Value="2">Store</asp:ListItem>
                        </asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selStore" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select all stores or a store" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-control-label">Select Store </label>
                        <asp:ListBox ID="lstBranches" Enabled="false" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID"
                            CssClass="form-control" multiple="multiple"></asp:ListBox>
                        <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                            ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="lstBranches" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Branch/s" ValidationGroup="AddPackageType" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                    <asp:HiddenField ID="hdnSelectedBranches" runat="server" />
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-control-label">Discount Type </label>
                        <asp:DropDownList ID="selDiscountType" runat="server" CssClass="form-control select2 enable-1 enable-2 enable-3 enable-4 enable-5" AutoPostBack="true" ForeColor="GrayText" OnSelectedIndexChanged="selDiscountType_SelectedIndexChanged">
                            <asp:ListItem Value="0">Select Discount Type</asp:ListItem>
                            <asp:ListItem Value="1">Flat Discount</asp:ListItem>
                            <asp:ListItem Value="2">Invoice Target</asp:ListItem>
                            <%-- <asp:ListItem Value="3">Product Discount</asp:ListItem>
                            <asp:ListItem Value="4">Buy X Get Y</asp:ListItem>
                            <asp:ListItem Value="5">Delivery Discount</asp:ListItem>--%>
                        </asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selDiscountType" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select all stores or a store" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label class="form-control-label">Discount Mode </label>
                        <asp:DropDownList ID="selDiscountMode" runat="server" CssClass="form-control select2 discount-control enable-1 enable-2 enable-3 enable-5" ForeColor="GrayText">
                            <asp:ListItem Value="">Discount Mode</asp:ListItem>
                            <asp:ListItem Value="0">Offer Percentage</asp:ListItem>
                            <asp:ListItem Value="1">Coupon Offers</asp:ListItem>
                        </asp:DropDownList>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">Value </label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtValue" runat="server" CssClass="form-control discount-control enable-1 enable-2 enable-3 enable-5" autocomplete="nofill" />
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">Max Discount Amount </label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtmaxamount" runat="server" CssClass="form-control discount-control enable-1 enable-2 enable-3 enable-5" autocomplete="nofill" />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-control-label">Applicable For </label>
                        <asp:DropDownList ID="selApplicableFor" runat="server" CssClass="form-control select2 discount-control enable-3 enable-4" ForeColor="GrayText" AutoPostBack="true" OnSelectedIndexChanged="selApplicableFor_SelectedIndexChanged">
                        </asp:DropDownList>
                        <%--                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selApplicableFor" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select all stores or a store" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                    </div>
                </div>
                <asp:PlaceHolder ID="plcSelect" runat="server" Visible="false">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-control-label">Select</label>
                            <asp:DropDownList ID="selSelect" runat="server" CssClass="form-control select2 discount-control enable-3 enable-4" ForeColor="GrayText">
                                <asp:ListItem Value="0">Select</asp:ListItem>
                            </asp:DropDownList>
                        </div>
                    </div>
                </asp:PlaceHolder>
                <asp:PlaceHolder ID="plcCategoryExpand" runat="server" Visible="false">
                    <div class="col-lg-6" data-index="2">
                        <div class="form-group">
                            <label class="form-control-label">Select Category</label>
                            <asp:DropDownList ID="selCategory" runat="server" CssClass="form-control select2" DataSourceID="SDSCat" DataTextField="sub_category" AppendDataBoundItems="true" DataValueField="sub_category_id" data-placeholder="Select Category">
                                <asp:ListItem Value="" Text="Select Category"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selCategory" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Category" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient"
                                SelectCommand="SELECT sc.* FROM mypha_productsubcategory sc 
  INNER JOIN finascop_stock_itemmaster i ON i.product_category = sc.sub_category_id INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID 
  INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid WHERE sc.STATUS=1 GROUP BY sc.sub_category_id"
                                OnSelecting="SDSApplicableFor_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </asp:PlaceHolder>
                <asp:PlaceHolder Visible="false" ID="plcProductExpand" runat="server">
                    <div class="col-lg-6" data-index="4">
                        <div class="form-group">
                            <label class="form-control-label">Select SKU </label>
                            <asp:DropDownList ID="selProduct" runat="server" DataSourceID="SDSInventory" DataTextField="stit_SKU" DataValueField="stit_id" CssClass="form-control select2" AppendDataBoundItems="true" data-placeholder="Select Product">
                                <asp:ListItem Value="" Text="Select Product"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selProduct" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Product" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>
                            <asp:SqlDataSource runat="server" ID="SDSInventory" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSApplicableFor_Selecting" ProviderName="MySql.Data.MySqlClient"
                                SelectCommand="SELECT i.stit_SKU, i.stit_id FROM finascop_stock_itemmaster i INNER JOIN finascop_stock_branch_inventory bi ON i.stit_Id=bi.stit_id 
                    INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id AND b.br_storeGroup=@storegroupid WHERE stit_status=1 GROUP BY i.stit_id ORDER BY stit_SKU">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" Type="Int32" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </asp:PlaceHolder>
                <asp:PlaceHolder Visible="false" ID="plcBrandExpand" runat="server">
                    <div class="col-lg-6" data-index="5">
                        <div class="form-group">
                            <label class="form-control-label">Select Brand </label>
                            <asp:DropDownList ID="selBrand" runat="server" CssClass="form-control select2" DataSourceID="SDSBrand" DataTextField="brand_name" AppendDataBoundItems="true" DataValueField="brand_id" data-placeholder="Select Brand">
                                <asp:ListItem Value="" Text="Select Brand"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selBrand" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Brand" ValidationGroup="AddBanner" ForeColor="Red"></asp:RequiredFieldValidator>

                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSBrand" ProviderName="MySql.Data.MySqlClient"
                                SelectCommand="SELECT pb.brand_id,pb.brand_name FROM mypha_productbrands pb WHERE EXISTS(SELECT i.* FROM finascop_stock_itemmaster i 
  INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storeGroup= @storegroupid 
  WHERE i.pdt_brand = pb.brand_id) AND STATUS=1 AND IFNULL(pb.brand_name, '') NOT LIKE '' ORDER BY brand_name"
                                OnSelecting="SDSApplicableFor_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </asp:PlaceHolder>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-control-label">Target Amount </label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtTargetAmount" runat="server" CssClass="form-control discount-control enable-2 enable-5" autocomplete="nofill" />
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-control-label">Buy </label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtBuy" runat="server" CssClass="form-control discount-control enable-4" autocomplete="nofill" />
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-control-label">Get </label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtGet" runat="server" CssClass="form-control discount-control enable-4" autocomplete="nofill" />
                        <%--                        <asp:RequiredFieldValidator runat="server" ID="rqdGet" ControlToValidate="txtGet" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="License is required" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Coupon Code</label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtCouponCode" runat="server" CssClass="form-control discount-control enable-1 enable-2 enable-3 enable-5" autocomplete="nofill" />
                        <asp:RequiredFieldValidator runat="server" ID="reqCouponCode" ControlToValidate="txtCouponCode"
                            CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Coupon Code is required"
                            ValidationGroup="DiscountCoupon" ForeColor="Red" />
                        <asp:RegularExpressionValidator ID="revCouponCode" runat="server" ControlToValidate="txtCouponCode"
                            ValidationExpression="^[a-zA-Z0-9]*$"
                            ErrorMessage="Only alphanumeric characters are allowed."
                            CssClass="error_msg_wrap" Display="Dynamic"
                            ForeColor="Red" ValidationGroup="DiscountCoupon" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Expiry Date</label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtExpDate" runat="server" CssClass="form-control" TextMode="Date" autocomplete="nofill" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtExpDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Coverage KM date is required" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Redemption</label>
                        <asp:DropDownList ID="selRedemption" runat="server" OnSelectedIndexChanged="selRedemption_SelectedIndexChanged" CssClass="form-control select2 discount-control enable-1 enable-2 enable-3 enable-5" ForeColor="GrayText">
                            <asp:ListItem Value="0">Please Select</asp:ListItem>
                            <asp:ListItem Value="1">Onetime Use</asp:ListItem>
                            <asp:ListItem Value="2">Multi Use</asp:ListItem>
                        </asp:DropDownList>
              <asp:RequiredFieldValidator runat="server" ControlToValidate="selRedemption" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Employee type is required" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Customer Can</label>
                        <asp:DropDownList ID="selcuscan" runat="server" Enabled="false" CssClass="form-control select2" ForeColor="GrayText">
                            <asp:ListItem Value="0">Please Select</asp:ListItem>
                            <asp:ListItem Value="1">Redeems Once</asp:ListItem>
                            <asp:ListItem Value="2">Redeems Many</asp:ListItem>
                        </asp:DropDownList>
                        <%--<input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtCustomerCan" runat="server" CssClass="form-control discount-control enable-1 enable-2 enable-3 enable-5" autocomplete="nofill" /--%>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Coupon Name</label>
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtCouponName" runat="server" CssClass="form-control enable-1 enable-2 enable-3 enable-4 enable-5" autocomplete="nofill" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtCouponName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Give a Coupon Name" ValidationGroup="DiscountCoupon" ForeColor="Red"></asp:RequiredFieldValidator>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">&nbsp;</label>
                        <div>
                            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Create Coupon" ValidationGroup="DiscountCoupon" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="modaldemo5" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-body tx-center pd-y-20 pd-x-20">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
                    <h4 class="tx-danger mg-b-20">
                        <asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
                    <p class="mg-b-20 mg-x-20">
                        <asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
                    <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-body tx-center pd-y-20 pd-x-20">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
                    <h4 class="tx-success tx-semibold mg-b-20">
                        <asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
                    <p class="mg-b-20 mg-x-20">
                        <asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

                    <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <script type="text/javascript">
        $(function () {
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/DiscountCoupons";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            var storeValue = $('#<%= selStore.ClientID %>').val();

             if (storeValue == "2")
                 restoreSelectedBranches()

             // Initialize Select2
             $('.select2').select2();
             //Initialize MultiSelect
             $('#lstBranches').multiselect({
                 includeSelectAllOption: false,
                 nonSelectedText: 'Select Branch/s',
                 nSelectedText: ' - Branches selected',
                 allSelectedText: 'All branches Selected ...'
             });
             // Function to update the state of lstBranches based on selStore value
             function updateLstBranchesState() {
                 var storeValue = $('#<%= selStore.ClientID %>').val();

                 if (storeValue == "2") {
                     $('#lstBranches').multiselect('enable');

                 } else {
                     $('#lstBranches').multiselect('disable');
                     $('#lstBranches').multiselect('deselectAll', false);
                     $('#lstBranches').multiselect('updateButtonText');
                 }
             }
             // Initial state
             updateLstBranchesState();
             disableAllFields();
             updateDiscountModeOptions($('#<%= selDiscountType.ClientID %>').val());

             // Event listeners
             $('#<%= selStore.ClientID %>').change(updateLstBranchesState);

             $('#<%= selDiscountType.ClientID %>').change(function () {
                 updateDiscountModeOptions($(this).val());
             });

             const selcuscan = document.getElementById('<%= selcuscan.ClientID %>');
             const selRedemption = document.getElementById('<%= selRedemption.ClientID %>');
             $('#<%= selRedemption.ClientID %>').change(() => {
                 console.log('#<%= selRedemption.ClientID %>')
            selcuscan.disabled = selRedemption.value !== "2";
        });

         });
        // Function to update the discount mode options and enable/disable controls
        function updateDiscountModeOptions(discountType) {
            saveSelectedBranches()
            var $applicableFor = $('#<%= selApplicableFor.ClientID %>');
            $(`.enable-${discountType}`).removeClass('disabled-class').prop('disabled', false);
        }

        // Function to disable all fields
        function disableAllFields() {
            $('.discount-control').each(function () {
                $(this).addClass('disabled-class').prop('disabled', true).val('');
            });
        }
        function saveSelectedBranches() {
            var lstBranches = document.getElementById('<%= lstBranches.ClientID %>');
             var hdnSelectedBranches = document.getElementById('<%= hdnSelectedBranches.ClientID %>');

            console.log(lstBranches.options)
            var selectedValues = Array.from(lstBranches.options)
                .filter(option => option.selected)
                .map(option => option.value);
            hdnSelectedBranches.value = selectedValues.join(',');
        }
        function restoreSelectedBranches() {
            var lstBranches = document.getElementById('<%= lstBranches.ClientID %>');
             var selectedValues = document.getElementById('<%= hdnSelectedBranches.ClientID %>').value.split(',');
            for (var i = 0; i < lstBranches.options.length; i++) {
                lstBranches.options[i].selected = selectedValues.includes(lstBranches.options[i].value);
            }
        }

    </script>

    <script>
        $(document).ready(function () {
            $('#<%= txtCouponCode.ClientID %>').on('keypress', function (e) {
                var keyCode = e.keyCode || e.which;
                var regex = /^[a-zA-Z0-9]*$/;
                var key = String.fromCharCode(keyCode);
                if (!regex.test(key)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>

    <style>
        .h-28 {
            height: 28px;
        }

        .errormsg {
            width: 100%;
            display: inline-block;
        }

        .row.row-sm.mt-2 > div {
            align-content: flex-start;
        }

        .select2.select2-container {
            width: 100% !important;
        }

        .form-control + .select2 + span[data-val="true"] {
            bottom: -9px;
        }

        .select2-container {
            height: 28px;
        }

        .form-group .multiselect-native-select + span[data-val="true"] {
            width: 100%;
            left: 9px;
            bottom: -13px;
        }
    </style>

</asp:Content>
