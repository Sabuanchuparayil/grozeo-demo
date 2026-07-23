<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Manual Packing" AutoEventWireup="true" CodeBehind="ManualPacking.aspx.cs" Inherits="RetalineProAgent.ManualPacking" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <script type="text/javascript">
        function validateqty() {
            var isValidQty = true;
            $('input.packedqty').each(function () {
                var qty = $(this).attr('orderqty');
                var inputval = $(this).val();
                if (qty - inputval != 0 && $(this).prop('disabled') == false)
                    isValidQty = false;
            });
            
                
            return isValidQty;
        }
        $(document).ready(function () {
            if (!validateqty())
                $('#<%= txtNumBags.ClientID %>').prop('disabled', true);
            
            $('input.packedqty').on('change', function () {
                if ($(this).prop('disabled') == false)
                    $('#<%= txtNumBags.ClientID %>').prop('disabled', !validateqty());
                
            });

        });
    </script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PendingOrders">Order Packing</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manual Packing</li>--%>
    <%--<asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
        <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    </asp:PlaceHolder>--%>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-between">
                <asp:PlaceHolder ID="plcHead" runat="server">
                    <div class="ordr-info d-flex align-items-center flex-wrap flex-md-nowrap">
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal">
                            Order Id:
                            <asp:Literal ID="ltrOrderId" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 tx-15 d-flex align-items-center">
                            <i class="icon ion-android-person mr-2 tx-22" style="position: relative; top: 3px;"></i>
                            <asp:Literal ID="ltrName" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 d-flex align-items-center pl-0 pl-md-3">
                            <i class="icon ion-ios-telephone mr-2 tx-22" style="position: relative; top: 3px;"></i>
                            <asp:Literal ID="ltrMobile" runat="server" Text=""></asp:Literal>
                        </div>
                    </div>

                </asp:PlaceHolder>
                <div>
                    <asp:LinkButton CssClass="btn btn-outline-primary" Visible="false" runat="server" ID="btnprint" OnClick="btnprint_Click"><i class="fa-light fa-print mr-2"></i>Print</asp:LinkButton>
                </div>
            </div>
        </div>
        <!-- card-header -->
        <asp:PlaceHolder runat="server" ID="plcManualPacking">
            <div class="card-body">
                <div class="table-responsive">
                    <asp:GridView AutoGenerateColumns="false" ID="gvManualPacking" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" OnRowDataBound="gvManualPacking_RowDataBound"
                        AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" OnDataBound="gvManualPacking_DataBound" DataSourceID="SDSManualPacking">
                        <Columns>
                            <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                                <ItemTemplate>
                                    <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                </ItemTemplate>
                            </asp:TemplateField>
                            <asp:BoundField HeaderText="Item" DataField="item_name" SortExpression="item_name" />
                            <asp:BoundField DataField="mrp" SortExpression="mrp" ItemStyle-Width="120" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                            <asp:BoundField HeaderText="Order Qty" DataField="fsto_ItemQty" ItemStyle-Width="120" SortExpression="fsto_ItemQty" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                            <asp:TemplateField HeaderText="Packed Qty" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-HorizontalAlign="Left">
                                <ItemTemplate>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtUpdate" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Text='<%#Eval("fsto_pkdQty")%>' CssClass="form-control packedqty text-right" prodid='<%# Eval("fstod_id") %>' Enabled='<%# (Convert.ToInt32(Eval("fsto_isalreadypacked"))  == 1 ? false : true) %>' autocomplete="off"></asp:TextBox>
                                    <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtUpdate" SetFocusOnError="true" Display="Dynamic" ErrorMessage="Please input packed quantity" ValidationGroup="ManualPacking"></asp:RequiredFieldValidator>--%>
                                    <%--<asp:CompareValidator runat="server" style="position: absolute" Type="Double" SetFocusOnError="true" ValueToCompare='<%# Eval("fsto_ItemQty") %>' Operator="Equal" ValidationGroup="ManualPacking" ControlToValidate="txtUpdate" ForeColor="Red" Display="Dynamic" ErrorMessage="Packed quantity should be equal to order quantity"></asp:CompareValidator>--%>
                                </ItemTemplate>
                            </asp:TemplateField>
                            <asp:TemplateField HeaderText="Product picked" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align">
                                <ItemTemplate>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtSubPrd" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Enabled='<%# (Convert.ToInt32(Eval("stit_ParentItemId")) > 0  && (Convert.ToInt32(Eval("br_parentPacking")) == 0 ? true : false)) %>' CssClass="form-control text-right" autocomplete="off" prodid='<%# Eval("fstod_id") %>'></asp:TextBox>
                                    <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtUpdate" SetFocusOnError="true" Display="Dynamic" ErrorMessage="Please input packed quantity" ValidationGroup="ManualPacking"></asp:RequiredFieldValidator>--%>
                                    <%--<asp:CompareValidator runat="server" style="position: absolute" Type="Double" SetFocusOnError="true" ValueToCompare='<%# Eval("fsto_ItemQty") %>' Operator="Equal" ValidationGroup="ManualPacking" ControlToValidate="txtUpdate" ForeColor="Red" Display="Dynamic" ErrorMessage="Packed quantity should be equal to order quantity"></asp:CompareValidator>--%>
                                </ItemTemplate>
                            </asp:TemplateField>
                        </Columns>
                        <EmptyDataTemplate>
                            <div class="text-center">
                                <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                <h6 class="mb-3">No order record available.</h6>
                            </div>
                        </EmptyDataTemplate>
                    </asp:GridView>

                    <asp:SqlDataSource runat="server" ID="SDSManualPacking" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSManualPacking_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT fo.fsto_uid AS fsto_uid,fo.fsto_isalreadypacked,fo.fsto_id AS fsto_id,fd.fsto_ItemId,fd.fstod_id,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_source,
                                    (SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) AS br_parentPacking,
                                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS item_name,
                                    (SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS stit_ParentItemId,
                                    fsto_ItemQty,fsto_pkdQty, (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') as ownInvoice,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch,
                                    fo.fsto_id AS fsto_id,fstro_ItemMRP AS mrp
                                    FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  
                                    AND fo.fsto_id=@fsto_id">
                        <SelectParameters>
                            <asp:QueryStringParameter QueryStringField="fsto_id" Name="fsto_id" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>


            </div>
            <!-- card-body -->
            <div class="card-footer d-flex flex-wrap justify-content-lg-between">
                <div class="row row-sm w-100 d-flex  justify-content-center justify-content-lg-end align-items-start ">
                    <div class="form-group mb-3" style="width: 200px;">
                        <asp:TextBox ID="txtInvDate" runat="server" Visible='<%#Convert.ToInt32(Eval("ownInvoice")) > 0? true : false %>' CssClass="form-control" TextMode="Date" />
                        <asp:RequiredFieldValidator runat="server" ID="rfInvDate" ControlToValidate="txtInvDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date is required" ValidationGroup="ManualPacking" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                    <!--form-group-->
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-end p-0 mb-3">
                        <div class="form-group mb-0">
                            <asp:TextBox runat="server" ID="txtInvNum" Visible='<%#Convert.ToInt32(Eval("ownInvoice")) > 0? true : false %>' CssClass="form-control" Style="width: 120px;" placeholder="Invoice Number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="ManualPacking"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ID="rfInvNum" ControlToValidate="txtInvNum" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Invoice number is required" ValidationGroup="ManualPacking" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                        <div class="form-group mb-0 ml-2">
                            <asp:TextBox runat="server" ID="txtAmt" Visible='<%#Convert.ToInt32(Eval("ownInvoice")) > 0? true : false %>' CssClass="form-control" Style="width: 120px;" placeholder="Invoice Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="ManualPacking"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ID="rfAmount" ControlToValidate="txtAmt" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Invoice amount is required" ValidationGroup="ManualPacking" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!--d-flex-->

                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-end mb-2 pr-0">
                        <div class="form-group mb-0 mr-0 mr-md-3 ">
                            <asp:TextBox runat="server" ID="txtNumBags" CssClass="form-control" Style="width: 120px;" placeholder="No.of bags" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="ManualPacking"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ID="rfValidator" ControlToValidate="txtNumBags" Display="Dynamic" ErrorMessage="Please input number of bags" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                        <div class="form-group mb-0">
                            <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                            <asp:Button runat="server" ID="btnSubmit" fsto_id='<%# Eval("id") %>' fsto_uid='<%# Eval("fsto_uid") %>' ValidationGroup="ManualPacking" OnClick="btnManualPackingSubmit_Click" CssClass="btn btn-primary btn-inline-block mx-2" Text="Submit" OnClientClick="if(!validateqty()) return confirm('Not all products in your order have been picked. Would you like to proceed with an incomplete order?');" />
                            <a href="/Tenant/PendingOrders" class="btn btn-primary btn-inline-block mt-md-0 wd-sm-auto-force">Cancel</a>
                        </div>
                    </div>
                    <!--d-flex-->
                </div>
                <!--row-->
            </div>
            <!-- card-footer -->
        </asp:PlaceHolder>
    </div>
    <!-- card -->



    <asp:PlaceHolder ID="plcPackageType" runat="server">
        <div class="card-body p-3">
            <div class="form-layout">
                <asp:Repeater ID="rptPackageType" runat="server">
                    <ItemTemplate>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="form-control-label">Package Number:</label>
                                    <asp:TextBox ID="txtPackNumber" ReadOnly="true" Text='<%# PackagesCode(Container.ItemIndex) %>' runat="server" CssClass="form-control" placeholder="Enter package number" />
                                </div>
                            </div>
                            <!-- col-4 -->
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="form-control-label">Package Type:</label>
                                    <asp:DropDownList ID="selPackageType" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSPackageType" DataTextField="rpckm_name" AppendDataBoundItems="true" DataValueField="rpckm_id" AutoPostBack="true" OnSelectedIndexChanged="selPackageType_SelectedIndexChanged">
                                        <asp:ListItem Text="Select package type" Value=""></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSPackageType" ProviderName="MySql.Data.MySqlClient"
                                        SelectCommand="select rpckm_id,rpckm_name from retaline_package_master where rpckm_status = 1 and rpckm_type=@packtype UNION SELECT -1 AS rpckm_id,'Custom Package' AS rpckm_name FROM retaline_package_master
                                            ORDER BY rpckm_name"
                                        OnSelecting="SDSPackageType_Selecting">
                                        <SelectParameters>
                                            <asp:Parameter Name="packtype" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selPackageType" ErrorMessage="Please select package type" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 form-group d-flex">
                                <div class="form-group wd-100 mb-0" id="customPackageLength">
                                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Length (cm):</label>
                                    <asp:TextBox ID="txtLength" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtLength" ErrorMessage="Please enter length" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                                <div class="form-group wd-100 mx-2 mb-0" id="customPackageBreadth">
                                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Breadth (cm):</label>
                                    <asp:TextBox ID="txtbreadth" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtbreadth" ErrorMessage="Please enter breadth" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                                <div class="form-group wd-100 mb-0" id="customPackageHeight">
                                    <label runat="server" class="form-control-label mb-1 w-100 tx-dark">Height (cm):</label>
                                    <asp:TextBox ID="txtHeight" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtHeight" ErrorMessage="Please enter height" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <!-- col-4 -->
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="form-control-label">Weight(kg):</label>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtWeight" runat="server" CssClass="form-control" placeholder="Enter weight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off" />
                                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtWeight" ErrorMessage="Please enter weight" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <!-- col-4 -->

                        </div>
                    </ItemTemplate>
                </asp:Repeater>
                <!-- row -->
                <div class="card-footer p-0 mt-0">
                    <asp:Button runat="server" ID="btnPackageSubmit" OnClick="btnPackageSubmit_Click" CssClass="btn btn-primary bd-0 mr-2" ValidationGroup="PackDetails" Text="Submit" />
                    <%--<asp:Button runat="server" ID="btnSkip" OnClick="btnSkip_Click" CssClass="btn btn-primary bd-0" Text="Skip"/>--%>
                    <a href="/Tenant/PendingOrders" class="btn btn-outline-primary mt-md-0 wd-sm-auto-force">Skip</a>
                </div>
            </div>
            <!-- form-layout -->
        </div>
    </asp:PlaceHolder>

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
                        <asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal>
                    </p>
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
                    <i class="icon ion-ios-checkmark-outline tx-100 tx-orange lh-1 mg-t-20 d-inline-block"></i>
                    <h4 class="tx-orange tx-semibold mg-b-20">
                        <asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
                    <p class="mg-b-20 mg-x-20">
                        <asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal>
                    </p>

                    <button type="button" class="btn btn-warning pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <style>
        .form-control + span[data-val="true"], .form-control + .select2 + span[data-val="true"] {
          font-size: 9px;
        }
        .mailbox-messages .table tr > th:nth-child(1){
            width:100px;
        }
        .mailbox-messages .table tr > th:nth-child(3),
        .mailbox-messages .table tr > th:nth-child(4){
            width:160px;
        }
        .mailbox-messages .table tr > th:nth-child(5),
        .mailbox-messages .table tr > td:nth-child(5){
            width:250px;
            text-align:right;
            position:relative;
        }
        .mailbox-messages .table tr > td:nth-child(5) input {
            float:right;
            /*width: 55px!important;*/
        }
        .mailbox-messages .table tr > td:nth-child(5) span {
            right: 0;
            font-size: 10px;
            bottom: 0;
        }
        .right_align { text-align: right; }
        
    </style>
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/PendingOrders";
            });
        });

    </script>


</asp:Content>
