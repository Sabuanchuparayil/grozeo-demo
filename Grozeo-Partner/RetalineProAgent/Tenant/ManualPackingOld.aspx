<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ManualPackingOld.aspx.cs" Title="Manual Packing"  MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.ManualPacking" %>

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
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-between">
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
            </div>
        </div>
        <div class="card-body">
            <asp:GridView AutoGenerateColumns="false" ID="gvManualPacking" runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSManualPacking">
                <Columns>
                    <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                        <ItemTemplate>
                            <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                        </ItemTemplate>
                    </asp:TemplateField>
                    <asp:BoundField HeaderText="Item" DataField="item_name" SortExpression="item_name" />
                    <asp:TemplateField SortExpression="mrp">
                        <HeaderTemplate>
                            <%# ConfigurationManager.AppSettings["CountryCode"] == "IN" ? "MRP" : "RRP" %>
                        </HeaderTemplate>
                        <ItemTemplate>
                            <%# Eval("mrp", "{0:0.00}") %>
                        </ItemTemplate>
                        <ItemStyle Width="120" HorizontalAlign="Right" />
                        <HeaderStyle HorizontalAlign="Right" CssClass="left_align" />
                    </asp:TemplateField>
                    <asp:BoundField HeaderText="Order Qty" DataField="fsto_ItemQty" ItemStyle-Width="120" SortExpression="fsto_ItemQty" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                    <asp:TemplateField HeaderText="Packed Qty" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-HorizontalAlign="Left">
                        <ItemTemplate>
                            <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtUpdate" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Text='<%#Eval("fsto_pkdQty")%>' CssClass="form-control packedqty text-right" prodid='<%# Eval("fstod_id") %>' Enabled='<%# (Convert.ToInt32(Eval("fsto_isalreadypacked"))  == 1 ? false : true) %>' autocomplete="off"></asp:TextBox>
                        </ItemTemplate>
                    </asp:TemplateField>
                    <asp:TemplateField HeaderText="Product picked" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align">
                        <ItemTemplate>
                            <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtSubPrd" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Enabled='<%# (Convert.ToInt32(Eval("stit_ParentItemId")) > 0  && (Convert.ToInt32(Eval("br_parentPacking")) == 0 ? true : false)) %>' CssClass="form-control text-right" autocomplete="off" prodid='<%# Eval("fstod_id") %>'></asp:TextBox>
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
            <asp:SqlDataSource runat="server" ID="SDSManualPacking" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
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
    </div>   <!-- card-body -->
    <div class="card-footer d-flex flex-wrap justify-content-lg-between">
        <div class="row row-sm w-100 d-flex  justify-content-center justify-content-lg-end align-items-start ">
            <div class="d-flex flex-wrap justify-content-center justify-content-lg-end mb-2 pr-0">
                <div class="form-group mb-0 mr-0 mr-md-3 ">
                    <asp:TextBox runat="server" ID="txtNumBags" CssClass="form-control" Style="width: 120px;" placeholder="No.of bags" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="ManualPacking"></asp:TextBox>
                    <asp:RequiredFieldValidator runat="server" ID="rfValidator" ControlToValidate="txtNumBags" Display="Dynamic" ErrorMessage="Please input number of bags" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
                <div class="form-group mb-0">
                    <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                    <asp:Button runat="server" ID="btnSubmit" fsto_id='<%# Eval("id") %>' fsto_uid='<%# Eval("fsto_uid") %>' ValidationGroup="ManualPacking" OnClick="btnSubmit_Click" CssClass="btn btn-primary btn-inline-block mx-2" Text="Submit" OnClientClick="if(!validateqty()) return confirm('Not all products in your order have been picked. Would you like to proceed with an incomplete order?');" />
                    <a href="/Tenant/PendingOrders" class="btn btn-primary btn-inline-block mt-md-0 wd-sm-auto-force">Cancel</a>
                </div>
            </div>
            <!--d-flex-->
        </div>
        <!--row-->
    </div>
            <!-- card-footer -->
    <div class="modal" id="PopupManualpacking">
        <div class="modal-dialog w-100 modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom flex-wrap p-3">
                    <!-- Modal Heading -->
                    <h5 class="modal-title w-100 mb-2 tx-16" id="modaldemo5Label">Invoice Details</h5>
                    <div class="container mw-100 p-0">
                        <div class="row row-sm">                        
                        <div class="col-12 col-md-4">
                            <!-- Invoice Date -->
                            <div class="form-group mb-2 mb-md-0">
                                <asp:Label runat="server" Text="Invoice Date"></asp:Label>
                                <asp:TextBox ID="txtInvDate" runat="server" Visible='<%# Convert.ToInt32(Eval("ownInvoice")) > 0 %>' CssClass="form-control" TextMode="Date" placeholder="Invoice Date" />
                                <asp:RequiredFieldValidator runat="server" ID="rfInvDate" ControlToValidate="txtInvDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date is required" ValidationGroup="PackDetails" ForeColor="Red" />
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <!-- Invoice Number -->
                            <div class="form-group mb-2 mb-md-0">
                             <asp:Label runat="server" Text="Invoice Number"></asp:Label>
                                <asp:TextBox runat="server" ID="txtInvNum" Visible='<%#Convert.ToInt32(Eval("ownInvoice")) > 0%>' CssClass="form-control" placeholder="Invoice Number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="PackDetails" />
                                <asp:RequiredFieldValidator runat="server" ID="rfInvNum" ControlToValidate="txtInvNum" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Invoice number is required" ValidationGroup="PackDetails" ForeColor="Red" />
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <!-- Invoice Amount -->                       
                            <div class="form-group mb-0">
                             <asp:Label runat="server" Text="Invoice Amount"></asp:Label>
                                <asp:TextBox runat="server" ID="txtAmt" Visible='<%#Convert.ToInt32(Eval("ownInvoice")) > 0%>' CssClass="form-control" placeholder="Invoice Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" ValidationGroup="PackDetails" />
                                <asp:RequiredFieldValidator runat="server" ID="rfAmount" ControlToValidate="txtAmt" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Invoice amount is required" ValidationGroup="PackDetails" ForeColor="Red" />
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="modal-body">
                    <h5 class="modal-title w-100 mt-2 tx-16 mb-2"">Packing Details</h5>
                    <asp:Repeater ID="rptPackageType" runat="server">
                        <ItemTemplate>
                            <div class="border border-secondary rounded mb-3 p-2 pb-3">
                                <div class="row row-sm">
                                    <div class="col-12 col-sm-6 mb-2">Package Number:<asp:TextBox ID="txtPackNumber" ReadOnly="true" Text='<%# PackagesCode(Container.ItemIndex) %>' runat="server" CssClass="form-control" placeholder="Enter package number"></asp:TextBox></div>
                                    <div class="col-12 col-sm-6 mb-2">
                                        Package Type:<asp:DropDownList ID="selPackageType" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSPackageType" DataTextField="rpckm_name" AppendDataBoundItems="true" DataValueField="rpckm_id" AutoPostBack="true" OnSelectedIndexChanged="selPackageType_SelectedIndexChanged">
                                            <asp:ListItem Text="Select package type" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSPackageType" ProviderName="MySql.Data.MySqlClient"
                                            SelectCommand="select rpckm_id,rpckm_name from retaline_package_master where rpckm_status = 1 and rpckm_type=@packtype UNION SELECT -1 AS rpckm_id,'Custom Package' AS rpckm_name FROM retaline_package_master
                                            ORDER BY rpckm_name"
                                            OnSelecting="SDSPackageType_Selecting1">
                                            <SelectParameters>
                                                <asp:Parameter Name="packtype" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selPackageType" ErrorMessage="Please select package type" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                                <div class="row row-sm">
                                    <div class="col-6 col-sm-6 col-md-3 mb-2 mb-md-0">
                                        Length (cm):
                                        <asp:TextBox ID="txtLength" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtLength" ErrorMessage="Please enter length" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3 mb-2 mb-md-0">
                                        Breadth (cm):<asp:TextBox ID="txtbreadth" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtbreadth" ErrorMessage="Please enter breadth" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3 mb-2 mb-md-0">
                                        Height (cm):<asp:TextBox ID="txtHeight" runat="server" CssClass="form-control" placeholder="in cm" autocomplete="nofill" />
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtHeight" ErrorMessage="Please enter height" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3 mb-2 mb-md-0">
                                        Weight(kg):<asp:TextBox ID="txtWeight" runat="server" CssClass="form-control" placeholder="Enter weight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off" />
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="txtWeight" ErrorMessage="Please enter weight" ValidationGroup="PackDetails" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                            </div>
                        </ItemTemplate>
                    </asp:Repeater>                    
                </div>

                <div class="modal-footer">
                    <asp:Button runat="server" ID="btnPackageSubmit" OnClick="btnPackageSubmit_Click" CssClass="btn btn-primary bd-0 mr-2" ValidationGroup="PackDetails" Text="Submit" />
                    <a href="/Tenant/PendingOrders" class="btn btn-secondary btn-inline-block mt-md-0 wd-sm-auto-force">Cancel</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="PopupManualpackingDetalies">
        <div class="modal-dialog w-100">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <h5 class="modal-title text-center w-100">Order Packing Completed</h5>
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12 col-md-4 mb-2 mb-sm-0">
                              <asp:Label runat="server">Order Number:</asp:Label> 
                            <asp:Label ID="lblordernumber" runat="server"  Font-Bold="True"></asp:Label>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-sm-0">
                           <asp:Label runat="server"> Invoice Count:</asp:Label> 
                            <asp:Label ID="lblinvoicecount" runat="server"  Font-Bold="True"></asp:Label>
                        </div>
                        <div class="col-12 col-md-4">
                             <asp:Label runat="server">Packet Count:</asp:Label>  
                            <asp:Label runat="server" ID="lblpacketcount" Font-Bold="True"></asp:Label>
                        </div>
                    </div>
                    <div class="row row-sm">
                        <div class="col-12 mt-2">
                                <asp:Repeater runat="server" ID="rptPackageTypedetails">
                                    <ItemTemplate>
                                        <div class="packet_number my-2 text-center">
                                         <asp:Label runat="server" Text='<%# "Packet " + (Container.ItemIndex + 1).ToString() + ":" %>'></asp:Label>
                                         <asp:Label runat="server" Text='<%# PackagesCode(Container.ItemIndex) %>' ID="ltrPacketId"></asp:Label>
                                        </div>
                                    </ItemTemplate>
                                </asp:Repeater>                           
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <asp:LinkButton CssClass="btn btn-primary" runat="server" ID="btnprint" OnClick="btnprint_Click">Print Packing slip</asp:LinkButton>
                    <asp:LinkButton runat="server" CssClass="btn btn-primary" Visible="false" ID="btnpackingprint" Text="Print Packet Id"></asp:LinkButton>
                    <a href="/Tenant/PendingOrders" class="btn btn-primary btn-inline-block mt-md-0 wd-sm-auto-force">Cancel</a>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media (min-width: 992px) {
            #PopupManualpacking.modal-dialog {
                max-width: 1106px;
            }
        }
    </style>

</asp:Content>
