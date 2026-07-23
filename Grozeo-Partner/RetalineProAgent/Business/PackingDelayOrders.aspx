<%@ Page Language="C#" AutoEventWireup="true" Async="true" title="Packing Delayed Orders" MasterPageFile="~/Business/BusinessMaster.master" CodeBehind="PackingDelayOrders.aspx.cs" Inherits="RetalineProAgent.Business.PackingDelayOrders" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle" runat="server" Text="Packing Delayed Orders"></asp:Literal>
        </h6>
        <p class="mb-0">Consignment failed to pack or packing not completed</p>
    </div>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-sm-10 mb-2 mb-sm-0">
                    <nav class="navbar col-12 w-100 mt-2 mt-lg-0 navbar-expand-lg bg-transparent p-0 justify-content-start align-items-end">
                        <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                        <button class="navbar-toggler p-0" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                                <i class="fa fa-sliders" aria-hidden="true"></i>
                            </span>
                        </button>
                        <div class="collapse navbar-collapse flex-wrap" id="navbarSupportedContent">
                            <ul class="navbar-nav mr-auto pt-2 pt-lg-0">
                                <li class="nav-item ml-0 mr-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnPendingJobs" runat="server" CommandArgument="0" CssClass="btn btn-block btn-outline-primary active" OnClientClick="setActiveClass(this);">Pending Jobs <span class="sr-only">(current)</span></asp:LinkButton>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="row row-sm mt-2">
                <div class="col-sm-4 col-lg-3 input-group mg-b-10 mg-sm-b-0">
                    <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store</label>
                    <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                        <asp:DropDownList ID="selBranches" CssClass="form-control select2" DataTextField="StoreName" DataValueField="StoreID"
                            runat="server" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" AppendDataBoundItems="true" AutoPostBack="true">
                            <asp:ListItem Text="Select Store" Value="-1"></asp:ListItem>
                        </asp:DropDownList>
                    </asp:PlaceHolder>
                </div>
                <div class="col-sm-4 col-lg-5 form-group mb-2 mb-sm-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
                    <div style="display: none;">
                        <input type="text" name="name_emailField" />
                        <input type="password" name="passwordField" />
                    </div>
                    <asp:TextBox ID="txtOrderId" runat="server" autocomplete="off" CssClass="form-control" placeholder="Order ID" name="uniqueOrderId"></asp:TextBox>
                </div>

                <div class="col-sm-4 col-lg-4 d-flex justify-content-sm-end align-items-sm-end">
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary inline-block w-sm-auto px-4 mt-2 mt-sm-0" runat="server" OnClick="lbtnSearch_Click">Search</asp:LinkButton>
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2" PostBackUrl="~/Business/PackingDelayOrders.aspx" Text="Reset" />
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="accordion" class="table-responsive">
                <asp:HiddenField ID="hdRowIndex" runat="server" />
                <asp:GridView AutoGenerateColumns="false" ID="gvPackingFailedOrders" runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                    AllowPaging="true" AllowSorting="true" ShowFooter="false" OnRowDataBound="gvPackingFailedOrders_RowDataBound" PagerSettings-Visible="true"
                    PageSize="10" OnPageIndexChanging="gvPackingFailedOrders_PageIndexChanging" PagerStyle-CssClass="pg_table">
                    <Columns>
                        <asp:TemplateField HeaderText="ID" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" CommandArgument='<%# Eval("orderID") %>'></asp:LinkButton>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Payment Mode" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" CommandArgument='<%# Eval("paymentMode") %>'></asp:LinkButton>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="UUID" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" ID="uuidLinkButton" CommandArgument='<%# Eval("uuid") %>' />
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Timestamp" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" ID="tstampLinkButton" CommandArgument='<%# Eval("timestamp") %>' />
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:BoundField DataField="Mode" Visible="false" />
                        <asp:BoundField DataField="DeliveryMode" Visible="false" />
                        <asp:BoundField DataField="Type" Visible="false" />
                        <asp:BoundField DataField="action" Visible="false" />
                        <asp:TemplateField HeaderText="Order ID">
                            <ItemTemplate>
                                <asp:HyperLink runat="server" Text='<%# Eval("orderOrderID") %>'></asp:HyperLink>
                                <br />
                                <small>Total: <b><%# Eval("orderTotal") %></b></small>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Delivery Mode">
                            <ItemTemplate>
                                <asp:HyperLink runat="server" Text='<%# 
                                //Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) == 2
                                //? "API Booking" :
                                Convert.ToInt32(Eval("DeliveryMode")) == 1 ? "Courier Pickup" :
                                Convert.ToInt32(Eval("DeliveryMode")) == 2 ? "Hyper Local" :
                                Convert.ToInt32(Eval("DeliveryMode")) == 3 ? "Local Express" :
                                Convert.ToInt32(Eval("DeliveryMode")) == 4 ? "Parcel" :
                                Convert.ToInt32(Eval("DeliveryMode")) == 5 ? "Cargo" :
                                "" %>'>
                                </asp:HyperLink>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Customer" ItemStyle-Width="30%">
                            <ItemTemplate>
                                <strong><%# RetalineProAgent.Service.Common.ShrinkText(Eval("CustomerDetails").ToString(), 50) %></strong>
                                <br />
                                <small>
                                    <a href="https://maps.google.com/?q=<%# Eval("Address") %>" target="_blank">
                                        <i class="fa-regular fa-location-dot"></i>
                                    </a>
                                    <%# RetalineProAgent.Service.Common.ShrinkText(Eval("Address").ToString(), 100) %>
                                </small>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Order Date" ItemStyle-Width="170px">
                            <ItemTemplate>
                                <%# Convert.ToDateTime(Eval("OrderDate")).ToString("dd MMM yyyy HH:mm") %><br />
                                <small>Item Worth: <b><%# Eval("OrderSubTotal") %></b></small>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Delayed By">
                            <ItemTemplate>
                                <%# 
                                   (DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalMinutes < 1 ? "Just now" :
                                   (DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalMinutes < 60 ? 
                                   ((int)(DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalMinutes).ToString() + " minute(s)" :
                                   (DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalHours < 24 ? 
                                   ((int)(DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalHours).ToString() + " hour(s)" :
                                   ((int)(DateTime.Now - Convert.ToDateTime(Eval("OrderDate"))).TotalDays).ToString() + " day(s)"
                                %>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="MerchantDetails" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" ID="MerDetailsLinkButton" CommandArgument='<%# Eval("MerchantDetails") %>' />
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="BranchID" Visible="false">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" ID="BranchIdLinkButton" CommandArgument='<%# Eval("BranchID") %>' />
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Store">
                            <ItemTemplate>
                                <asp:HyperLink runat="server" Text='<%# Eval("MerchantName") %>'></asp:HyperLink>
                                <br />
                                <small>Mode: <b><%# 
                                Convert.ToInt32(Eval("paymentMode")) == 1 ? "Pay on Delivery" :
                                Convert.ToInt32(Eval("paymentMode")) == 2 ? "Online" :
                                Convert.ToInt32(Eval("paymentMode")) == 3 ? "Wallet" :
                                Convert.ToInt32(Eval("paymentMode")) == 4 ? "COD with Wallet" :
                                Convert.ToInt32(Eval("paymentMode")) == 5 ? "Online with Wallet" :
                                Convert.ToInt32(Eval("paymentMode")) == 6 ? "Online on Delivery" :
                                Convert.ToInt32(Eval("paymentMode")) == 7 ? "Cash on Delivery" :"" %></b></small>
                            </ItemTemplate>
                        </asp:TemplateField>

                         <asp:TemplateField>
                             <ItemTemplate>

                        <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>
                     </td></tr>
                                <%-- <div class="action_arrow tx-center position-relative" data-id='<%# Eval("orderOrderID") %>' data-uuid='<%# Eval("uuid") %>' data-tstamp='<%# Eval("timestamp") %>'
                                     aria-expanded="false" aria-controls="collapseOne" onclick="onActionClick(this,'<%# String.Format("#collapse{0}", Container.DataItemIndex) %>')">
                                     <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                     <span class="loading-spinner position-absolute l-5 b--15" style="display: none;">
                                         <i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
                                 </div>
                                 </td></tr>--%>
                                 <td colspan="8" class="hiddenRow">
                                     <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">
                                         <asp:LinkButton ID="lbtnFetchOrderPicker" Text="Fetch Details" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnFetchOrderPicker_Click"
                                             CommandArgument='<%# Eval("orderOrderID") %>' />
                                         <asp:LinkButton ID="lbtnCancelOrder" Text="Cancel Order" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnCancelOrder_Click"
                                             CommandArgument='<%# Eval("orderOrderID") %>' />
                                         <asp:LinkButton ID="lbtnViewOrderDetails" Text="View Order Details" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnViewOrderDetails_Click"
                                             CommandArgument='<%# Eval("orderID") %>' />
                             </ItemTemplate>
                         </asp:TemplateField>

                    </Columns>

                    <EmptyDataTemplate>
                        <div class="text-center">
                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                            <h6 class="mb-3">No record available</h6>
                        </div>
                    </EmptyDataTemplate>
                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                </asp:GridView>

                <asp:ObjectDataSource ID="ODSPackingDelayedOrders" runat="server" TypeName="RetalineProAgent.Core.Services.Order.OrderService" SelectMethod="PackingDelayedOrders">
                    <SelectParameters>
                        <asp:Parameter Name="branchid" Type="Int32" DefaultValue="0" />
                        <asp:Parameter Name="orderID" Type="string" DefaultValue="" ConvertEmptyStringToNull="false" />
                    </SelectParameters>
                </asp:ObjectDataSource>
            </div>
        </div>
    </div>

<asp:HiddenField ID="hidOrderId" runat="server" />
<div id="modalOrderPicker" class="modal fade">
    <div class="modal-dialog w-100 modal-dialog-vertical-center modal-lg" role="document">
        <div class="modal-content bd-0">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-inverse">Available Order Picker</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row row-sm">
                    <div class="col-12">
                        <div class="order-picker-info-wrap">
                            <asp:Repeater ID="rptOrderPickers" runat="server" DataSourceID="SDSOrderPicker">
                                <ItemTemplate>
                                    <div class="order-picker-info w-100 mb-2 border-bottom">
                                        <div class="name-phone-icon">
                                            <span class="name">
                                                <%# Eval("Order Picker") %>
                                            </span>
                                            <span class="phone ml-2">
                                                <%# Eval("Phone") %>
                                            </span>
                                            <asp:LinkButton ID="lbtnClicktocall" runat="server" OnClick="lbtnClicktocall_Click" CommandArgument='<%# Eval("Phone") %>'>
                                               <%-- <i class="fa-regular fa-circle-phone">--%>
                                                </i></asp:LinkButton>
                                            <%--<a href="javascript:void(0)" onclick="handlePhoneClick()">
                                               <i class="fa-regular fa-circle-phone"></i>
                                            </a>--%>
                                        </div>
                                    </div>
                                </ItemTemplate>
                                                          
                            </asp:Repeater>

                            <div id="noOrderPickersMessage" style="display: none;">
                                No order pickers are available.
                            </div>

                        </div>
                    </div>

                    <asp:SqlDataSource ID="SDSOrderPicker" runat="server" ProviderName="MySql.Data.MySqlClient"
                        ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT CONCAT(NAME, ' ', lname) AS 'Order Picker', Phone FROM `retaline_customer_order` c 
                        INNER JOIN `retaline_godown_boy` g ON c.order_branch_id = g.branch_id 
                        WHERE g.is_offline = 0 AND c.order_order_id = @orderId ORDER BY CONCAT(NAME, ' ', lname);">
                        <SelectParameters>
                            <asp:ControlParameter ControlID="hidOrderId" Name="OrderId" />
                        </SelectParameters>
                    </asp:SqlDataSource>

                </div>
            </div>
        </div>
    </div>
</div>

<asp:HiddenField ID="hdOrderId" runat="server" />
<div id="modalViewOrderDetails" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 ">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-inverse">Order Details</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row row-sm">
                    <asp:SqlDataSource ID="SDSViewOrderDetails" runat="server" ProviderName="MySql.Data.MySqlClient"
                        ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT order_id,item_order_id,order_customer_id,cust_customer_name,cust_mobile,cust_email,item_product_id, COUNT(rco.order_id) AS ItemCount,total,  
                        mp.sub_category_id, mp.isPerishable, mp.hasRestaurantService,
                        SUM(isPerishable) AS Perishable,
                        SUM(hasRestaurantService) AS Restaurant,
                        SUM(CASE WHEN mp.isPerishable = 0 AND mp.hasRestaurantService = 0 THEN 1 ELSE 0 END) AS Resellable,
                        DATE_FORMAT(rco.created_at, '%d %b %Y') AS OrderDate
                        FROM retaline_customer_order_items rci 
                        INNER JOIN retaline_customer_order rco ON rco.order_id = rci.customer_order_id
                        INNER JOIN finascop_stock_itemmaster fs ON fs.stit_id = rci.item_product_id
                        INNER JOIN mypha_productsubcategory mp ON mp.sub_category_id = fs.product_category 
                        INNER JOIN retaline_customer rc ON rc.cust_id=rco.order_customer_id
                        WHERE order_id = @orderId
                        GROUP BY item_order_id">
                        <SelectParameters>
                            <asp:ControlParameter ControlID="hdOrderId" Name="OrderId" />
                        </SelectParameters> 
                    </asp:SqlDataSource>

                    <asp:Repeater ID="rptCancelOrder" runat="server" DataSourceID="SDSViewOrderDetails">
                        <ItemTemplate>
                            <div class="col-12 mb-2 d-flex mb-3" id="divOrderId">
                                <label class="text-left tx-dark mb-0 d-flex align-items-center">Order Number:</label>
                                <span class="ml-2 tx-dark"><strong><%# Eval("item_order_id") %></strong></span>
                            </div>


                            <div class="col-12">

                                <div class="ordercardlist">

                                    <h6 class="mb-2 tx-dark">Customer Details</h6>

                                    <div class="table-responsive">
                                        <table class="table table-bordered gridview_table">
                                            <tr>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th>Email</th>
                                            </tr>
                                            <tr>
                                                <td><strong><%# Eval("cust_customer_name") %></strong></td>
                                                <td><strong><%# Eval("cust_mobile") %></strong></td>
                                                <td><strong><%# Eval("cust_email") %></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    </div>

                                    <div class="ordercardlist">
                                    <h6 class="mb-2 tx-dark">Content Details</h6>

                                    <div class="table-responsive">
                                        <table class="table table-bordered gridview_table">
                                            <tr>
                                                <th>Restaurant Items</th>
                                                <th>Perishable Items</th>
                                                <th>Resellable Items</th>
                                            </tr>
                                            <tr>
                                                <td><strong><%# Eval("Restaurant") %></strong></td>
                                                <td><strong><%# Eval("Perishable") %></strong></td>
                                                <td><strong><%# Eval("Resellable") %></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    </div>

                                <div class="ordercardlist">
                                    <h6 class="mb-2 tx-dark">Order Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered gridview_table">
                                            <tr>
                                                <th>Cart Value</th>
                                                <th>Total Items</th>
                                                <th>Order Date</th>
                                                <th>Packing Time</th>
                                            </tr>
                                            <tr>
                                                <td><strong><%# Eval("total") %></strong></td>
                                                <td><strong><%# Eval("ItemCount") %></strong></td>
                                                <td><strong><%# Eval("OrderDate") %></strong></td>
                                                <td><strong>03 Sep,11:22 AM</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                            </div>


                            </div>

                             </div>
                            </div>

                        </ItemTemplate>
                    </asp:Repeater>
                </div>
            </div>
        </div>
    </div>
    </div>

    <asp:HiddenField ID="hidCanOrderId" runat="server" />
    <asp:HiddenField ID="hidUuid" runat="server" />
    <asp:HiddenField ID="hidtstamp" runat="server" />
    <asp:HiddenField ID="hidStoreId" runat="server" />
    <asp:HiddenField ID="hidMerDetails" runat="server" />
    <div id="modalCancelOrder" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog w-100" role="document">
            <div class="modal-content">
                <div class="modal-header pr-5">
                    <h6 class="tx-semibold mb-0 pr-2">Are you sure you want to proceed with cancellation?</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="col-12 row row-sm d-flex align-items-end">
                        <div class="form-group mb-0 col">
                            <label class="w-100 text-left tx-dark">
                                Reason for cancellation
                            <span class="tx-danger">*</span>
                            </label>
                            <asp:DropDownList ID="ddlCancelReason" runat="server" CssClass="form-control select2" ForeColor="GrayText" AutoPostBack="false">
                                <asp:ListItem Text="Select reason for cancellation" Value="-1"></asp:ListItem>
                                <asp:ListItem Text="Requested by customer" Value="0"></asp:ListItem>
                                <asp:ListItem Text="Order picker not available" Value="1"></asp:ListItem>
                                <asp:ListItem Text="Delivery boy not available" Value="2"></asp:ListItem>
                                <asp:ListItem Text="Ordered items not available" Value="3"></asp:ListItem>
                                <asp:ListItem Text="Delivery area not reachable" Value="4"></asp:ListItem>
                                <asp:ListItem Text="Unforeseen reason" Value="5"></asp:ListItem>
                            </asp:DropDownList>
                        </div>

                        <div class="d-flex justify-content-center col-auto">
                            <asp:Button runat="server" ID="btnYes" CssClass="btn btn-primary mr-2 bd-0" Text="Yes" OnClick="btnYes_Click" />
                            <asp:Button runat="server" ID="btnNo" CssClass="btn btn-secondary bd-0" Text="No" data-dismiss="modal" aria-label="Close" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .ordercardlist{
            margin-bottom:10px;
        }
    </style>

</asp:Content>

