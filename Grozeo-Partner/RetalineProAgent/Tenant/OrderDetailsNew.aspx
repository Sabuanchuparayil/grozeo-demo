<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="Order Details" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="OrderDetailsNew.aspx.cs" Inherits="RetalineProAgent.OrderDetailsNew" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href='<%= GetBackLink() %>'><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

<asp:PlaceHolder ID="plcActionButtonsRow" runat="server">
    <div class="card">
        <div class="card-header p-0" style="overflow: hidden!important">
            <div class="d-inline-block w-100 p-3" style="background-color: #E5F0E3;">
                <div class="row">
                    <div class="col-md-12">
                        <p class="tx-15 m-0 tx-dark">
                            Order Id: <b>
                                <asp:Literal ID="ltrTitleOrderId" runat="server" Text=""></asp:Literal></b> | Total: <b><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrTitleTotal" runat="server" Text=""></asp:Literal>
                                </b>| Payment Mode: <b>
                                    <asp:Literal ID="ltrPayMode" runat="server" Text=""></asp:Literal></b> | Payment Status: <b>
                                        <asp:Literal ID="ltrPayStatus" runat="server" Text=""></asp:Literal></b>
                            | Status: <b>
                                <asp:Literal ID="ltrTitleStatus" runat="server" Text=""></asp:Literal></b>
                        </p>
                    </div>
                </div>
            </div>
            <asp:Image runat="server" ID="imgProgressing" ImageAlign="AbsMiddle" Width="30" ImageUrl="https://grozeo.azurewebsites.net/images/processing.gif" Visible="false" />
            <div id="dvNoneSponsoredPack" runat="server" class="row row-sm p-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-4 d-flex mb-2 mb-md-0">
                    <div runat="server" id="dvAbtnAssignOrderPicker" class="mr-2">
                        <asp:HyperLink runat="server" ID="hlAssignOrderPicker" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-user"></i> Assign Order Picker</asp:HyperLink>
                    </div>
                    <div runat="server" id="dvAbtnManualPacking">
                        <asp:HyperLink runat="server" ID="hlManualPacking" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-user"></i> Manual Packing</asp:HyperLink>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8">
                    <div runat="server" id="dvOrderNotes" class="">
                        <label class="form-control-label tx-dark">Customer Notes:</label>

                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <asp:TextBox ID="txtCustomerNotes" runat="server" CssClass="form-control w-100" autocomplete="off" />
                    </div>
                </div>
            </div>

            <div id="dvNoneSponsoredDelivery" runat="server" class="d-flex align-items-center flex-wrap flex-md-nowrap p-3">
                <div class="col-12 col-md-auto p-0 mb-2 mb-md-0 mr-0 mr-md-3 mb-3 mb-md-0" runat="server" id="dvAbtnManualDelivery">
                    <asp:HyperLink runat="server" ID="hlManualDelivery" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-user"></i> Manual Delivery</asp:HyperLink></div>
                <div class="col-12 col-md-auto p-0 mb-2 mb-md-0" runat="server" id="dvAbtnActiveDeliveryBoys">
                    <asp:HyperLink runat="server" ID="hlActiveDeliveryBoys" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-bell"></i> Assign Delivery Staff</asp:HyperLink></div>
            </div>

        </div>
        <!-- card-header -->
    </div>
    <!-- card -->
    
<div id="dvSponsored" runat="server" visible="false" class="">
    <div class="d-flex align-items-center flex-wrap flex-md-nowrap p-3 bg-white">
        <a Class="btn btn-inline-block btn-outline-primary">Sponsored Order</a>
    </div>    
</div>
</asp:PlaceHolder>
    <div class="row row-sm">
        <!-- left column -->
        <div class="col-12">
            <asp:Panel runat="server" ID="pnlInvalidOrder" Visible="false" CssClass="myproduct_alertmsg_wrap">
                <div class="myproduct_alertmsg">
                    <h4>Invalid Order</h4>
                    <div class="myproduct_alertmsg_cont">
                        <br />
                        <br />
                        <p>The order is invalid or you don't have permission to access the data.</p>
                        <br />
                        <br />
                        <div class="btn-sec text-center d-flex justify-content-center">
                            <asp:HyperLink runat="server" NavigateUrl="/Tenant/pendingorders" Text="Go to My Orders" class="btn btn-primary btn-drk-green float-none mx-2 wd-sm-auto-force p-2 px-4"></asp:HyperLink>
                        </div>
                    </div>
                </div>

            </asp:Panel>
            <asp:Panel ID="pnlValidOrder" runat="server" CssClass="row row-sm">
                <div class="col-12 col-md-6 card-columns card_columns_one">
                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                            <h3 class="slim-card-title text-capitalize">Order Details</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Order ID</td>
                                        <td>
                                            <asp:Literal ID="ltrOrder" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Order Group</td>
                                        <td>
                                            <asp:Literal ID="ltrOrdId" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Date of Order</td>
                                        <td>
                                            <asp:Literal ID="ltrOrdDte" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Customer</td>
                                        <td>
                                            <asp:Literal ID="ltrCustName" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Contact No.</td>
                                        <td>
                                            <asp:Literal ID="ltrCntNo" runat="server"></asp:Literal></td>
                                    </tr>
                                     <tr>
                                        <td>Address</td>
                                        <td>
                                            <asp:Literal ID="ltrAdd1" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>
                                            <asp:Literal ID="ltrAdd2" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Contact No.</td>
                                        <td>
                                            <asp:Literal ID="ltrDelivNumber" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Contact Email</td>
                                        <td>
                                            <asp:Literal ID="ltrCntEmail" runat="server"></asp:Literal></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                            <h6 class="slim-card-title text-capitalize">Order Cost Details</h6>
                        </div>
                        <div class="table-responsive">

                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Cart Value</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrSubTotal" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Charge</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrDeliveryCharge" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Taxes</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrGST" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr id="tsccess" runat="server">
                                        <td>CESS</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrCess" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Round Off</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrRoundOff" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td align="right"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrOrdAmt" runat="server"></asp:Literal></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header mb-0 bd-b-0-force bg-light mb-0card-header bd-b-0-force bg-light d-flex justify-content-between align-items-center">
                            <h6 class="slim-card-title text-capitalize">Order Items Details</h6>
                        </div>
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvItemDetails" PageSize="10" runat="server" GridLines="None" CssClass="table table-bordered"
                                AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSItemDetails">
                                <Columns>
                                    <asp:BoundField HeaderText="Item Name" DataField="stit_SKU" />
                                    <asp:BoundField HeaderText="Rate" DataField="order_item_mrp"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Tax %" DataField="hnsvalue"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Amount" DataField="item_price"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Ordered Quantity" DataField="item_order_qty"  ItemStyle-HorizontalAlign="Center" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>

                                </Columns>
                            </asp:GridView>

                        <asp:SqlDataSource runat="server" ID="SDSItemDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT customer_order_id,item_sales_price,order_item_basket_price_et,order_item_mrp,item_price,item_order_qty_scanned,stit_SKU,item_order_qty,(SELECT hsnGst FROM hsn_value hs WHERE hs.id = fs.stit_hsnId) AS hnsvalue 
                                           FROM retaline_customer_order_items ro INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID WHERE customer_order_id =@orderId"
                            OnSelecting="SDSItemDetails_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="orderId" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header mb-0 bd-b-0-force bg-light mb-0card-header bd-b-0-force bg-light d-flex justify-content-between align-items-center">
                            <h6 class="slim-card-title text-capitalize">Tax Details</h6>
                        </div>
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvTaxDetails" PageSize="10" runat="server" GridLines="None" CssClass="table table-bordered"
                                AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSTaxDetails">
                                <Columns>
                                    <asp:BoundField HeaderText="Tax Rate" DataField="hsnGst"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Taxable Amt" DataField="order_item_basket_price_et" ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Amount" DataField="item_price" ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                </Columns>
                            </asp:GridView>

                        <asp:SqlDataSource runat="server" ID="SDSTaxDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT customer_order_id,item_sales_price,
                            order_item_basket_price_et,IFNULL((SELECT fsi.fsipc_code FROM finascop_stock_itemmaster_product_codes fsi
                            WHERE fsi.fsipc_stit_id = fs.stit_ID  AND(fsi.fsipc_store = fb.br_ID OR fsipc_isCompany = 1) ORDER BY fsipc_store DESC LIMIT 1),
                            'Not Applicable') AS itemcode, order_item_mrp_et, stit_SKU, item_sales_price, order_item_mrp, IFNULL(item_order_qty, 0) 
                            AS item_order_qty, hsnGst, hsnCess, item_price, order_item_seller_discount, stit_HSN_code  FROM retaline_customer_order re
                            INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id 
                            INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID
                            LEFT JOIN hsn_value hs ON hs.id = fs.stit_hsnId
                            INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID where customer_order_id=@custOrderId"
                            OnSelecting="SDSTaxDetails_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="custOrderId" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 card-columns card_columns_one">
                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                            <h6 class="slim-card-title text-capitalize">Packing Info</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>TO Number</td>
                                        <td>
                                            <asp:Literal ID="ltrToNo" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Packed By / Mode</td>
                                        <td>
                                            <asp:Literal ID="ltrPackedBy" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Order for</td>
                                        <td>
                                            <asp:Literal ID="ltrConsigne" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>No. of Packets</td>
                                        <td>
                                            <asp:Literal ID="ltrNoPacket" runat="server"></asp:Literal></td>
                                    </tr>                                                                        
                                    <tr runat="server" visible="false">
                                        <td>Order Number</td>
                                        <td>
                                            <asp:Literal ID="ltrOrdNo" runat="server"></asp:Literal>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                            <h6 class="slim-card-title text-capitalize">Delivery Details</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Delivery Type</td>
                                        <td>
                                            <asp:Literal ID="ltrType" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Picked Up Date</td>
                                        <td>
                                            <asp:Literal ID="ltrPackedUpdte" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery By</td>
                                        <td>
                                            <asp:Literal ID="ltrCourierParCom" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Way Bill No.</td>
                                        <td>
                                            <asp:Literal ID="ltrConsNum" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Current Status</td>
                                        <td>
                                            <asp:Literal ID="ltrManifestUpte" runat="server"></asp:Literal>
                                            <asp:HyperLink ID="hlTrackingUrl" runat="server"  Style="word-break: break-all; text-decoration: underline; color: blue;" Target="_blank"></asp:HyperLink>
                                            <asp:Literal ID="ltrTrackUrl" runat="server"></asp:Literal>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Manifest Update</td>
                                        <td>
                                            <asp:Literal ID="ltrManifestUpteTime" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Expected Delivery</td>
                                        <td>
                                            <asp:Literal ID="ltrExpectDeliv" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Actual Delivery Date</td>
                                        <td>
                                            <asp:Literal ID="ltrDevDate" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Confirmed Time</td>
                                        <td>
                                            <asp:Literal ID="ltrDelivConDate" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Confirmed By</td>
                                        <td>
                                            <asp:Literal ID="ltrDelivConBy" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr runat="server" visible="false" id="shipmentLabel">
                                        <td>Shipment Label</td>
                                        <td>
                                            <img src="../Content/images/pdfprint.png" alt="Label Icon" style="width: 35px; height: 35px; margin-right: 5px; vertical-align: middle;" />
                                            <asp:HyperLink ID="hlShipmentLabel" runat="server" Target="_blank" Text="Print Label" Style="word-break: break-all; text-decoration: underline;"></asp:HyperLink>
                                            <asp:Literal ID="ltrShippingUrl" runat="server"></asp:Literal>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card bg-white" style="border-radius: 10px !important;">
                        <div class="card-header mb-0 bd-b-0-force bg-light mb-0card-header bd-b-0-force bg-light d-flex justify-content-between align-items-center">
                            <h6 class="slim-card-title text-capitalize">Package Details</h6>
                        </div>
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvPackagedetails" PageSize="10" runat="server" GridLines="None" CssClass="table table-bordered"
                                AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSPackagedetails">
                                <Columns>
                                    <asp:TemplateField HeaderText="Packet Id" ItemStyle-Width="90px">
                                        <ItemTemplate>
                                            <%# "Packet " + (Container.DataItemIndex + 1) %>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Packet Number" DataField="rtopd_packets" ItemStyle-Width="35px" ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:TemplateField HeaderText="Packet Dimension (L * B * H) " ItemStyle-Width="110px">
                                        <ItemTemplate>
                                            <%# (Eval("rtpod_length") ?? 0) + " * " +  (Eval("rtpod_breadth") ?? 0) + " * " + (Eval("rtpod_height") ?? 0) %> 
                                        </ItemTemplate> <ItemStyle HorizontalAlign="Right" /><HeaderStyle HorizontalAlign="Right" CssClass="left_align" />
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Packet Weight" ItemStyle-Width="25px" DataField="rtopd_packetweigh" ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:TemplateField HeaderText="Package Name" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align">
                                        <ItemTemplate>
                                            <%# Eval("rtopd_packaging").ToString() == "-1" ? "Custom Package" : Eval("packagename") %>
                                        </ItemTemplate><ItemStyle HorizontalAlign="Right" />
                                    </asp:TemplateField>
                                </Columns>
                            </asp:GridView>
                        <asp:SqlDataSource runat="server" ID="SDSPackagedetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT rtopd_fstoId,rtopd_packets,rtopd_packaging,rtpod_length,rtpod_breadth,rtpod_height,rtopd_packetweigh,
                                           (SELECT rpckm_name FROM `retaline_package_master` WHERE rtopd_packaging=rpckm_id) AS packagename
                                           FROM `retaline_transfer_order_pack_details` WHERE rtopd_fstoId= @orderid">
                            <SelectParameters>
                                <asp:QueryStringParameter Name="orderid" QueryStringField="orderid" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                    </div>

                </div>
            </asp:Panel>
        </div>
    </div>

<style>
      .card_columns_one{
        column-count: 1;
      }
      .card_columns_two{
        column-count: 2;
      }
      table td {
        color: #5b636a;
        font-weight:400;
    }
    table td:nth-child(2) {
        color:#000;
    }

      @media (max-width: 991px) {
        .card_columns_two{
          column-count: 1;
        }
      }
    </style>

        
</asp:Content>

