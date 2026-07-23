<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="Order Details" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="DelivOrderDetails.aspx.cs" Inherits="RetalineProAgent.DelivOrderDetails" %>


<%--<asp:Content ContentPlaceHolderID="cntHeaderContainer" runat="server"></asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/OrderDelivery">Order Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Order Details</li>--%>
    <a href="/Tenant/MerchantDelivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
       <a href="/Tenant/OrderDelivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    </div>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

<asp:PlaceHolder ID="plcActionButtonsRow" runat="server">
    <div class="card bg-white" style="border-radius:10px!important">
        <div class="card-header p-0 m-0" style="overflow:hidden!important">
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

            <asp:Image runat="server" ID="imgProgressing" ImageAlign="AbsMiddle" width="30" ImageUrl="https://grozeo.azurewebsites.net/images/processing.gif" Visible="false"/>
            
            <div id="dvNoneSponsored" runat="server" class="d-flex align-items-center flex-wrap flex-md-nowrap p-3">
                <div class="col-12 col-md-auto p-0 mb-2 mb-md-0 mr-0 mr-md-3 mb-3 mb-md-0" runat="server" id="dvAbtnManualDelivery">
                    <asp:HyperLink runat="server" ID="hlManualDelivery" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-user"></i> Manual Delivery</asp:HyperLink></div>
                <div class="col-12 col-md-auto p-0 mb-2 mb-md-0" runat="server" id="dvAbtnActiveDeliveryBoys">
                    <asp:HyperLink runat="server" ID="hlActiveDeliveryBoys" CssClass="btn btn-inline-block btn-outline-primary" Visible="false"><i class="fa fa-bell"></i> Assign Delivery Staff</asp:HyperLink></div>
                <%--<div class="col-md-3" runat="server" ID="dvAbtnDeliveryDetails"><asp:HyperLink runat="server" ID="hlDeliveryDetails" CssClass="btn btn-block btn-outline-primary btn-xs" NavigateUrl="/ViewOrderDetails.aspx?fsto_id={0}"><i class="fa fa-table"></i> View Order Details</asp:HyperLink></div>
    <div class="col-md-3" runat="server" ID="dvAbtnPackingCompleted"><asp:HyperLink runat="server" ID="hlPackingCompleted"  CssClass="btn btn-block btn-outline-primary btn-xs"><i class="fa fa-edit"></i> Packing Completed</asp:HyperLink></div>--%>
            </div>

        </div><!--card-header-->
    </div><!-- card -->
            
    


<div id="dvSponsored" runat="server" visible="false" class="">
    <div class="d-flex align-items-center flex-wrap flex-md-nowrap p-3 bg-white">
        <a Class="btn btn-inline-block btn-outline-primary">Sponsored Order</a>
    </div>    
</div>

</asp:PlaceHolder>

    <div class="row row-sm mt-3">
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
                                    <%--<tr>
                                        <td>Order Status</td>
                                        <td>
                                            <asp:Literal ID="ltrOrdStatus" runat="server"></asp:Literal></td>
                                    </tr>--%>
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
                                    <tr id="trcess" runat="server">
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
                            <%--<div class="float-right">
                    1 - 2 / 2
                    <div class="btn-group ml-2">
                        <a id="cpMainContent_lbtnPagerLeft" class="btn btn-default btn-sm page-link py-1" href="javascript:__doPostBack('ctl00$cpMainContent$lbtnPagerLeft','')">
                        <i class="fa fa-angle-left"></i>
                        </a>
                        <a id="cpMainContent_lbtnPagerRight" class="btn btn-default btn-sm page-link py-1" href="javascript:__doPostBack('ctl00$cpMainContent$lbtnPagerRight','')">
                        <i class="fa fa-angle-right"></i>
                        </a>
                    </div>
                  </div>--%>
                        </div>
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvItemDetails" PageSize="10" runat="server" GridLines="None" CssClass="table table-bordered"
                                AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSItemDetails">
                                <Columns>
                                    <%--<asp:TemplateField HeaderText="Serial NO." ItemStyle-Width="100">
                                    <ItemTemplate>
                                        <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                    </ItemTemplate>
                                </asp:TemplateField>--%>
                                    <asp:BoundField HeaderText="Item Name" DataField="stit_SKU" />
                                    <asp:BoundField HeaderText="Rate" DataField="order_item_mrp"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Tax %" DataField="hsnGst"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                    <asp:BoundField HeaderText="Amount" DataField="item_price"  ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align"/>
                                </Columns>
                            </asp:GridView>

                        <asp:SqlDataSource runat="server" ID="SDSItemDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT customer_order_id,item_sales_price,
                            order_item_basket_price_et,IFNULL((SELECT fsi.fsipc_code FROM finascop_stock_itemmaster_product_codes fsi
                            WHERE fsi.fsipc_stit_id = fs.stit_ID  AND(fsi.fsipc_store = fb.br_ID OR fsipc_isCompany = 1) ORDER BY fsipc_store DESC LIMIT 1),
                            'Not Applicable') AS itemcode, order_item_mrp_et, stit_SKU, item_sales_price, order_item_mrp, IFNULL(item_order_qty, 0) 
                            AS item_order_qty, hsnGst, hsnCess, item_price, order_item_seller_discount, stit_HSN_code  FROM retaline_customer_order re
                            INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id 
                            INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID
                            LEFT JOIN hsn_value hs ON hs.id = fs.stit_hsnId
                            INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID where customer_order_id =@orderId"
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
                                    <%--<asp:TemplateField HeaderText="Serial NO." ItemStyle-Width="100">
                                    <ItemTemplate>
                                        <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                    </ItemTemplate>
                                </asp:TemplateField>--%>
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
                                   <%-- <tr>
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
                                    </tr>--%>
                                    <tr>
                                        <td>No. of Packets</td>
                                        <td>
                                            <asp:Literal ID="ltrNoPacket" runat="server"></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <asp:Literal ID="ltrPacket" runat="server" Mode="PassThrough"></asp:Literal>
                                        </td>
                                    </tr>
                                    <%--<tr>
                                    <td>Consigner</td>
                                    <td>
                                        <asp:Literal ID="ltrConsigner" runat="server"></asp:Literal></td>
                                </tr>--%>

                                    <%--<tr>
                                    <td>Order Created At</td>
                                    <td>
                                        <asp:Literal ID="ltrOrdCreatedAt" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <%--<tr>
                                    <td>Order Type</td>
                                    <td>
                                        <asp:Literal ID="ltrOrdType" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <tr runat="server" visible="false">
                                        <td>Order Number</td>
                                        <td>
                                            <asp:Literal ID="ltrOrdNo" runat="server"></asp:Literal></td>
                                    </tr>
                                    <%--<tr>
                                    <td>Order Date</td>
                                    <td>
                                        <asp:Literal ID="ltrOrdDate" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <%--<tr>
                                    <td>Slot Date & Time</td>
                                    <td>
                                        <asp:Literal ID="ltrSchOpnTime" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <%--<tr>
                                    <td>Pack Type</td>
                                    <td>
                                        <asp:Literal ID="ltrPackType" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <%--<tr>
                                    <td>Packing User</td>
                                    <td>
                                        <asp:Literal ID="ltrPackUser" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <%--<tr>
                                        <th>Slot Date</th>
                                        <td><asp:Literal ID="ltrSlotDate" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Slot Time</th>
                                        <td><asp:Literal ID="ltrSlotTime" runat="server"></asp:Literal></td>
                                      </tr>--%>
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
                                    <%--<tr>
                                        <td>Tracking URL</td>
                                        <td>
                                            <asp:Literal ID="ltrTrackingUrl" runat="server"></asp:Literal></td>
                                    </tr>--%>
                                    <tr>
                                        <td>Current Status</td>
                                        <td>
                                            <asp:Literal ID="ltrManifestUpte" runat="server"></asp:Literal>
                                            <asp:HyperLink ID="hlTrackingUrl" runat="server" Text="Track Now" Style="word-break: break-all; text-decoration: underline; color: blue;" Target="_blank"></asp:HyperLink>
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
                                    <%--<tr>
                                    <td>Pack Count</td>
                                    <td>
                                        <asp:Literal ID="ltrPackCount" runat="server"></asp:Literal></td>
                                </tr>--%>
                                    <tr runat="server" visible="false" id="shipmentLabel">
                                        <td>Shipment Label</td>
                                        <td>
                                            <img src="../Content/images/pdfprint.png" alt="Label Icon" style="width: 35px; height: 35px; margin-right: 5px; vertical-align: middle;" />
                                            <%--<asp:HyperLink ID="hlShipmentLabel" runat="server" Target="_blank" Style="word-break: break-all;"></asp:HyperLink>--%>
                                            <asp:HyperLink ID="hlShipmentLabel" runat="server" Target="_blank" Text="Print Label" Style="word-break: break-all; text-decoration: underline;"></asp:HyperLink>
                                            <asp:Literal ID="ltrShippingUrl" runat="server"></asp:Literal>
                                        </td>
                                    </tr>
                                    <%--<tr runat="server" visible="false" id="shipmentLabel">
                                        <td>Shipment Label</td>
                                        <td>
                                            <!-- Container with border for the URL display -->
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="border: 1px solid #ccc; padding: 8px; word-break: break-all; width: 80%;">
                                                    <asp:HyperLink ID="hlShipmentLabel1" runat="server" Text="Print Label" Target="_blank" Style="word-break: break-all;"></asp:HyperLink>
                                                </div>
                                                <!-- Print Button to print the linked page -->
                                                <asp:HyperLink ID="hlShipmentLabel" runat="server" Text="Print Label" Target="_blank" Style="word-break: break-all;"></asp:HyperLink>
                                            </div>
                                            <asp:Literal ID="ltrShippingUrl" Text="Print Label" runat="server"></asp:Literal>
                                        </td>
                                    </tr>--%>
                                </tbody>
                            </table>


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

