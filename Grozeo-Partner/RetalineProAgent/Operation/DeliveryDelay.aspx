<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Operation/OperationMaster.master" CodeBehind="DeliveryDelay.aspx.cs" Inherits="RetalineProAgent.Operation.DeliveryDelay" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle" runat="server" Text="Delivery Booking Failed & Pickup Delayed Orders"></asp:Literal></h6>
        <p class="mb-0">Consignment failed to book and orders delayed for pickup</p>
    </div>
    <%--<script type="text/javascript">
        window.onload = function () {
            document.getElementById('<%= txtOrderId.ClientID %>').setAttribute('autocomplete', 'off');
        };
    </script>--%>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <asp:HiddenField ID="hfFilterType" runat="server" />
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
                                    <asp:LinkButton ID="lbtnPendingJobs" runat="server" OnClick="btnFilterType_Click" CommandArgument="0" CssClass="btn btn-block btn-outline-primary active" OnClientClick="setActiveClass(this);">Pending Jobs <span class="sr-only">(current)</span></asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnAPIBookingFailed" runat="server" OnClick="btnFilterType_Click" CommandArgument="6" CssClass="btn btn-block btn-outline-primary">API Booking Failed</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnHyperlocalPending" runat="server" OnClick="btnFilterType_Click" CommandArgument="2" CssClass="btn btn-block btn-outline-primary">Hyperlocal Pending</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnLocalExpressPending" runat="server" OnClick="btnFilterType_Click" CommandArgument="3" CssClass="btn btn-block btn-outline-primary">Local Express Pending</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnCourierPickupDelayed" runat="server" OnClick="btnFilterType_Click" CommandArgument="1" CssClass="btn btn-block btn-outline-primary">Courier Pickup Delayed</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnParcelBooking" runat="server" OnClick="btnFilterType_Click" CommandArgument="4" CssClass="btn btn-block btn-outline-primary">Parcel Booking</asp:LinkButton>
                                </li>
                                <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                    <asp:LinkButton ID="lbtnCargoBooking" runat="server" OnClick="btnFilterType_Click" CommandArgument="5" CssClass="btn btn-block btn-outline-primary">Cargo Booking</asp:LinkButton>
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
                            runat="server" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" AppendDataBoundItems="true" AutoPostBack="">
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
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-sm-0 ml-2" PostBackUrl="~/Business/DeliveryBookingFailed.aspx" Text="Reset" />
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="accordion" class="table-responsive">
                <asp:HiddenField ID="hdRowIndex" runat="server" />
                <asp:GridView AutoGenerateColumns="false" ID="gvFailedOrders" runat="server" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"
                    AllowPaging="true" AllowSorting="true" ShowFooter="false" OnRowDataBound="gvFailedOrders_RowDataBound" PagerSettings-Visible="true"
                    PageSize="10" OnPageIndexChanging="gvFailedOrders_PageIndexChanging" PagerStyle-CssClass="pg_table">
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
                            Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) == 2
                            ? "API Booking Failed" :
                            Convert.ToInt32(Eval("DeliveryMode")) == 1 ? "Courier Pickup Delayed" :
                            Convert.ToInt32(Eval("DeliveryMode")) == 2 ? "Hyper Local Pending" :
                            Convert.ToInt32(Eval("DeliveryMode")) == 3 ? "Local Express Pending" :
                            Convert.ToInt32(Eval("DeliveryMode")) == 4 ? "Parcel Booking" :
                            Convert.ToInt32(Eval("DeliveryMode")) == 5 ? "Cargo Booking" :
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
                                %><asp:Literal runat="server" Text='<%# string.Format("<br><small>({0})</small>", Eval("ModeMethod")) %>' Visible='<%# !string.IsNullOrEmpty(Eval("ModeMethod").ToString()) %>'></asp:Literal>
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

                                <%-- <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>
                                </td></tr>--%>

                                <div class="action_arrow tx-center position-relative" data-id='<%# Eval("orderOrderID") %>' data-uuid='<%# Eval("uuid") %>' data-tstamp='<%# Eval("timestamp") %>'
                                    aria-expanded="false" aria-controls="collapseOne" onclick="onActionClick(this,'<%# String.Format("#collapse{0}", Container.DataItemIndex) %>')">
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    <span class="loading-spinner position-absolute l-5 b--15" style="display: none;">
                                        <i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
                                </div>
                                </td></tr>
          
          <td colspan="8" class="hiddenRow">
              <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">

                  <asp:LinkButton ID="btnAssignRider" Text="Assign Rider" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="btnAssignRider_Click"
                      CommandArgument='<%# Eval("orderOrderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) != 2 %>' />

                  <asp:LinkButton ID="btnLEAssignRider" Text="Assign Rider" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="btnLEAssignRider_Click"
                      CommandArgument='<%# Eval("orderOrderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 3%>' />

                  <asp:LinkButton ID="btnLECancelOrder" Text="Cancel Order" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="btnLECancelOrder_Click"
                      CommandArgument='<%# Eval("orderOrderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 3%>' />

                  <asp:LinkButton ID="btnCancelOrder" Text="Cancel Order" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="btnCancelOrder_Click"
                      CommandArgument='<%# Eval("orderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) != 2 %>' />

                  <asp:LinkButton ID="lbtnRetryAPIBooking" Text="Retry API Booking" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnRetryAPIBooking_Click"
                      CommandArgument='<%# Eval("orderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) == 2 %>' />

                  <asp:LinkButton ID="lbtnAPIBookManually" Text="Book Manually" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnAPIBookManually_Click"
                      CommandArgument='<%# Eval("orderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) == 2 %>' />

                  <asp:LinkButton ID="lbtnAPICancelOrder" Text="Cancel Order" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnAPICancelOrder_Click"
                      CommandArgument='<%# Eval("orderID") %>'
                      Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 2 && Convert.ToInt32(Eval("Mode")) == 2 %>' />

                  <asp:LinkButton ID="lbtnBookManual" Text="Book Manually" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnBookManual_Click"
                      CommandArgument='<%# Eval("orderID") %>' Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 1 && Convert.ToInt32(Eval("action")) == 5 %>' />

                  <asp:LinkButton ID="lbtnCancelBooking" Text="Convert to Manual booking" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3"
                      runat="server" OnClick="lbtnCancelBooking_Click"
                      CommandArgument='<%# Eval("orderID") %>' Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 1 && Convert.ToInt32(Eval("action")) == 0 %>' />

                  <asp:LinkButton ID="lbtnSkip" Text="Skip for a Day" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnSkip_Click"
                      CommandArgument='<%# Eval("orderID") %>' Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 1 %>' />

                  <asp:LinkButton ID="lbtnViewDetails" Text="View Order Details" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3"
                      runat="server" OnClick="lbtnViewDetails_Click"
                      CommandArgument='<%# Eval("orderID") %>' Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 1%>' />

                  <asp:LinkButton ID="lbtnDeliveryUpdate" Text="Update Delivery" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" runat="server" OnClick="lbtnDeliveryUpdate_Click"
                      CommandArgument='<%# Eval("orderID") + "," + Eval("paymentMode") %>' Visible='<%# Convert.ToInt32(Eval("DeliveryMode")) == 1 && Convert.ToInt32(Eval("action")) == 6 %>' />
          </td>
                                </tr>
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

                <asp:ObjectDataSource ID="ODSFailedOrders" runat="server" TypeName="RetalineProAgent.Core.Services.Order.OrderService" SelectMethod="DelayedOrders">
                    <SelectParameters>
                        <asp:Parameter Name="branchid" Type="Int32" DefaultValue="0" />
                        <asp:Parameter Name="orderID" Type="string" DefaultValue="" ConvertEmptyStringToNull="false" />
                    </SelectParameters>
                </asp:ObjectDataSource>
            </div>
        </div>

    </div>

    <asp:HiddenField ID="hdnOrderId" runat="server" />
    <asp:HiddenField ID="hdnQuorId" runat="server" />
    <asp:HiddenField ID="hdnstatusId" runat="server" />
    <asp:HiddenField ID="hduuid" runat="server" />
    <asp:HiddenField ID="hdtstamp" runat="server" />
    <div id="modalDeliveryDetails" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse ">Order Dispatch Details</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12 mb-2" id="divCourierDropdown">
                            <label class="w-100 text-left tx-dark">Select Courier <span class="tx-danger">*</span></label>
                            <asp:DropDownList ID="selCourier" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCourier" DataTextField="mst_courier_name" AppendDataBoundItems="true" DataValueField="mst_courier_id">
                                <asp:ListItem Text="Select Cargo / Courier" Value=""></asp:ListItem>
                            </asp:DropDownList>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSCourier" ProviderName="MySql.Data.MySqlClient" SelectCommand="SELECT 
                                 COALESCE(mst_courier_id, -1) AS mst_courier_id, -- Replace NULL with -1
                                 COALESCE(mst_courier_name, 'Unknown') AS mst_courier_name -- Replace NULL with 'Unknown'
                             FROM 
                                 mst_courier 
                             WHERE 
                                 STATUS = 1 
                             UNION 
                             SELECT 
                                 -1 AS mst_courier_id, 
                                 'Others' AS mst_courier_name"></asp:SqlDataSource>
                            <asp:RequiredFieldValidator ValidationGroup="CreateDelivery" ControlToValidate="selCourier" ForeColor="Red" ErrorMessage="Select Cargo / Courier" runat="server"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 mb-2 textbox-container" id="divCourierTextbox" style="display: none;">
                            <label class="w-100 text-left tx-dark">Enter Courier Name <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtCourierName" runat="server" CssClass="form-control" placeholder="Enter Courier Name" AutoComplete="off"></asp:TextBox>
                            <%--<asp:RequiredFieldValidator ID="txtCourierNameValidator" ValidationGroup="CreateDelivery" ControlToValidate="txtCourierName" ForeColor="Red" ErrorMessage="Enter courier name" runat="server" Display="Dynamic" EnableClientScript="true"></asp:RequiredFieldValidator>--%>
                        </div>
                        <div class="col-12 mb-2" id="divTrackingURL">
                            <label class="w-100 text-left tx-dark">Tracking URL if any</label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtTrackingURL" runat="server" CssClass="form-control" placeholder="Enter tracking URL" AutoComplete="off"></asp:TextBox>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="w-100 text-left tx-dark">Tracking Number <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtTrackingNo" runat="server" CssClass="form-control" placeholder="Enter tracking number" autocomplete="off" />
                            <asp:RequiredFieldValidator ValidationGroup="CreateDelivery" ControlToValidate="txtTrackingNo" ForeColor="Red" ErrorMessage="Enter tracking number" runat="server"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="w-100 text-left tx-dark">Date of Booking <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtDispatchDate" runat="server" CssClass="form-control" TextMode="Date" placeholder="Date of Booking" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDispatchDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date of booking is required" ValidationGroup="CreateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-center">
                                <asp:LinkButton runat="server" ID="btnDispatch" CssClass="btn btn-primary mr-1" OnClick="btnDispatch_Click" Text="Order Dispatched" ValidationGroup="CreateDelivery" />
                                <%--<a href="/Tenant/OrderDelivery" class="btn btn-secondary">Cancel</a>
                             <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />--%>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <asp:HiddenField ID="hidOrderId" runat="server" />
    <asp:HiddenField ID="hidCancelReason" runat="server" />
    <asp:HiddenField ID="hidUuid" runat="server" />
    <asp:HiddenField ID="hidtstamp" runat="server" />
    <div id="modalCancelOrder" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center modal-lg" role="document">
            <div class="modal-content bd-0 ">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse">Cancel the Order</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="row row-sm">
                        <asp:SqlDataSource ID="SDSCancelOrder" runat="server" ProviderName="MySql.Data.MySqlClient"
                            ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT order_id,item_order_id, item_product_id, COUNT(rco.order_id) AS ItemCount, 
                        order_total_amount, order_delivery_charge, total, 
                        mp.sub_category_id, mp.isPerishable, mp.hasRestaurantService,
                        SUM(isPerishable) AS Perishable,
                        SUM(hasRestaurantService) AS Restaurant,
                        SUM(CASE WHEN mp.isPerishable = 0 AND mp.hasRestaurantService = 0 THEN 1 ELSE 0 END) AS Resellable,
                        DATE_FORMAT(rco.created_at, '%d %b %Y') AS OrderDate
                        FROM retaline_customer_order_items rci 
                        INNER JOIN retaline_customer_order rco ON rco.order_id = rci.customer_order_id
                        INNER JOIN finascop_stock_itemmaster fs ON fs.stit_id = rci.item_product_id
                        INNER JOIN mypha_productsubcategory mp ON mp.sub_category_id = fs.product_category 
                        WHERE order_id = @orderId
                        GROUP BY item_order_id">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hidOrderId" Name="OrderId" />
                            </SelectParameters>
                        </asp:SqlDataSource>

                        <asp:Repeater ID="rptCancelOrder" runat="server" DataSourceID="SDSCancelOrder">
                            <ItemTemplate>
                                <div class="col-12 mb-2 d-flex" id="divOrderId">
                                    <label class="text-left tx-dark mb-0 d-flex align-items-center">Order Number:</label>
                                    <span class="ml-2 tx-dark"><strong><%# Eval("item_order_id") %></strong></span>
                                </div>

                                <div class="col-12 mb-2 ordercardlist">
                                    <h6 class="mb-0 tx-dark">Content Details</h6>
                                    <div class="row row-sm">
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Restaurant Items:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("Restaurant") %></strong></span>
                                        </div>

                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Perishable Items:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("Perishable") %></strong></span>
                                        </div>

                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Normal (Resellable) Items:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("Resellable") %></strong></span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12 mb-2 ordercardlist">
                                    <h6 class="mb-0 tx-dark">Order Details</h6>
                                    <div class="row row-sm">
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Cart Value:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("order_total_amount") %></strong></span>
                                        </div>
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Delivery Charges:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("order_delivery_charge") %></strong></span>
                                        </div>
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Total Amount:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("total") %></strong></span>
                                        </div>
                                    </div>

                                    <div class="row row-sm">
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Total Items:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("ItemCount") %></strong></span>
                                        </div>
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Order Date:</label>
                                            <span class="ml-2 tx-dark"><strong><%# Eval("OrderDate") %></strong></span>
                                        </div>
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Packing Time:</label>
                                            <span class="ml-2 tx-dark"><strong>03 Sep,11:22 AM</strong></span>
                                        </div>
                                    </div>


                                </div>

                                <div class="col-12 row row-sm d-flex align-items-end">
                                    <div class="form-group mb-0 col">
                                        <label class="w-100 text-left tx-dark">Reason for cancellation<span class="tx-danger">*</span></label>
                                        <asp:DropDownList ID="ddlCancelReason" runat="server" CssClass="form-control select2" ForeColor="GrayText" AutoPostBack="false" OnSelectedIndexChanged="ddlCancelReason_SelectedIndexChanged">
                                            <asp:ListItem Text="Select reason for cancellation" Value="-1"></asp:ListItem>
                                            <asp:ListItem Text="Requested by customer" Value="0"></asp:ListItem>
                                            <asp:ListItem Text="Order picker not available" Value="1"></asp:ListItem>
                                            <asp:ListItem Text="Delivery boy not available" Value="2"></asp:ListItem>
                                            <asp:ListItem Text="Ordered items not available" Value="3"></asp:ListItem>
                                            <asp:ListItem Text="Delivery area not reachable" Value="4"></asp:ListItem>
                                            <asp:ListItem Text="Unforseen reason" Value="5"></asp:ListItem>
                                        </asp:DropDownList>
                                    </div>
                                    <div class="d-flex justify-content-center col-auto">
                                        <asp:Button runat="server" ID="InitiateCancellation" CssClass="btn btn-primary mr-1"
                                            Text="Proceed" OnClick="InitiateCancellation_Click" />
                                        <asp:Button runat="server" ID="Close" CssClass="btn btn-secondary"
                                            Text="Close" data-dismiss="modal" />
                                    </div>
                                </div>

                            </ItemTemplate>
                        </asp:Repeater>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <asp:HiddenField ID="hdnPayementMode" runat="server" />
    <asp:HiddenField ID="hdnUOrderId" runat="server" />
    <asp:HiddenField ID="hdnUQuorId" runat="server" />
    <div id="modalDeliveryUpdate" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse">Delivery Update</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm" runat="server" id="dvPaymentMode">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <asp:RadioButton ID="rbCash" CssClass="form-control-label mb-0 mr-3 tx-dark" GroupName="rbgStore" runat="server" Text="Cash" />
                            <asp:RadioButton ID="rbBank" CssClass="form-control-label mb-0 tx-dark" GroupName="rbgStore" runat="server" Text="Bank" />
                        </div>
                        <div class="col-12 col-md-6 d-flex" id="dvtansactionId" runat="server">
                            <asp:TextBox ID="txtTransactionId" runat="server" CssClass="form-control" placeholder="Enter transaction ID" autocomplete="off" />
                        </div>
                    </div>
                    <div class="row row-sm">
                        <div class="col-12">
                            <div>
                                <label class="w-100 text-left tx-dark">Remarks</label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtDeliveryRemarks" runat="server" TextMode="MultiLine" CssClass="form-control" autocomplete="off" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDeliveryRemarks" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Remarks is required" ValidationGroup="UpdateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="w-100 text-left tx-dark">Date<span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtDeliveryDate" runat="server" CssClass="form-control" TextMode="Date" placeholder="Date of Booking" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDeliveryDate" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date required" ValidationGroup="UpdateDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-center">
                                <asp:Button runat="server" ID="btnDeliveryUpdate" CssClass="btn btn-primary mr-1" OnClick="btnDeliveryUpdate_Click" Text="Submit" ValidationGroup="UpdateDelivery" />
                                <a href="/Tenant/MerchantDelivery" class="btn btn-secondary">Cancel</a>
                                <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <asp:HiddenField ID="hdnCanOrderId" runat="server" />
    <asp:HiddenField ID="hdnUUID" runat="server" />
    <asp:HiddenField ID="hdntStamp" runat="server" />
    <div id="modalmanualDelivery" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse ">Manual Delivery</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <asp:HiddenField ID="hidmanualDelivId" runat="server" />
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-4 mb-2">
                            <label>Date: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtDate" runat="server" TextMode="Date" CssClass="form-control" deliveredDate="delivered_date" />
                        </div>
                        <div class="col-4 mb-2">
                            <label>Time: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtTime" runat="server" TextMode="Time" CssClass="form-control" deliverderTime="delivered_time" />
                        </div>
                        <div class="col-4 mb-2">
                            <label>Delivered By: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtDelivBoy" runat="server" CssClass="form-control" placeholder="Delivered By" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDelivBoy" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Delivered by is required" ValidationGroup="ManualSchDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12">
                            <label>Remarks: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtRemarks" runat="server" CssClass="form-control" placeholder="Remarks" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRemarks" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Remarks is required" ValidationGroup="ManualSchDelivery" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <div class="modal-footer d-flex flex-wrap justify-content-lg-end">
                        <asp:Button runat="server" ID="btnManualDeliverySubmit" OnClick="btnManualDeliverySubmit_Click" CssClass="btn btn-primary" Text="Submit" ValidationGroup="ManualSchDelivery" />&nbsp;
                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <asp:HiddenField ID="hiddenOrderId" runat="server" />
    <asp:HiddenField ID="hiddenUUID" runat="server" />
    <asp:HiddenField ID="hiddentstamp" runat="server" />
    <div id="modalAssignRider" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 ">
                <div class="modal-header">
                    <h6 class="tx-14 mg-b-0 tx-inverse">Assign Delivery Agent</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <asp:ObjectDataSource ID="ODSLiveVehicles" runat="server" TypeName="RetalineProAgent.Core.Services.APIService" SelectMethod="LoadVehicle">
                    <SelectParameters>
                        <asp:QueryStringParameter Name="branchid" QueryStringField="brId" DefaultValue="0" />
                        <asp:QueryStringParameter Name="pickupLat" QueryStringField="lat" />
                        <asp:QueryStringParameter Name="pickupLng" QueryStringField="long" />
                        <asp:QueryStringParameter Name="UserType" QueryStringField="userType" />
                        <asp:QueryStringParameter Name="UserId" QueryStringField="userId" />
                    </SelectParameters>
                </asp:ObjectDataSource>

                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12 mb-2 d-flex">
                            <label class="text-left tx-dark mb-0 d-flex align-items-center">Area:</label>
                            <span class="ml-2 tx-dark"><strong>
                                <asp:Label ID="lblArea" runat="server"></asp:Label>
                            </strong></span>
                        </div>
                        <div class="col-12 mb-2" id="divAssignRider">
                            <label class="w-100 text-left tx-dark">Available Delivery Agents<span class="tx-danger">*</span></label>
                            <asp:DropDownList ID="ddlLiveDrivers" runat="server" CssClass="form-control select2" ForeColor="GrayText" AppendDataBoundItems="true"
                                DataSourceID="ODSLiveVehicles" DataTextField="DName" DataValueField="VId" AutoPostBack="false" OnSelectedIndexChanged="ddlLiveDrivers_SelectedIndexChanged">
                                <asp:ListItem Text="Select drivers" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                        </div>

                        <%-- <asp:RequiredFieldValidator  runat="server" ControlToValidate="ddlLiveDrivers" 
                             InitialValue="-1" ErrorMessage="Please select a driver to assign." ForeColor="Red" />--%>

                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-center">
                                <asp:Button runat="server" ID="btnAssignAgent" CssClass="btn btn-primary mr-1" OnClick="btnAssignAgent_Click"
                                    Text="Assign Agent" />
                            </div>
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
                        <asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal>
                    </p>

                    <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->
    <asp:HiddenField ID="hdOID" runat="server" />
    <asp:HiddenField ID="hdUID" runat="server" />
    <asp:HiddenField ID="hdttstmp" runat="server" />
    <asp:HiddenField ID="hdCustId" runat="server" />
    <div id="modalCancelCourierOrder" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h6 class="tx-14 mg-b-0 tx-inverse">
                        <!-- Static Title -->
                        Are you sure you want to proceed?
                    </h6>
                </div>
                <div class="modal-body tx-center pd-y-20 pd-x-20">

                    <div class="row row-sm">
                        <div class="col-12">
                            <p class="mg-b-20 mg-x-20 tx-dark">
                                <!-- Static Content -->
                                If you convert to Manual Booking, existing courier booking will be cancelled. Do you want to proceed?
                            </p>
                        </div>

                        <div class="col-12 row row-sm d-flex align-items-end">
                            <div class="form-group mb-0 col">
                                <label class="w-100 text-left tx-dark">Reason for cancellation<span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="ddlCourierCancelReason" runat="server" CssClass="form-control select2" ForeColor="GrayText" AutoPostBack="false">
                                    <asp:ListItem Text="Select reason for cancellation" Value="-1"></asp:ListItem>
                                    <asp:ListItem Text="Requested by customer" Value="0"></asp:ListItem>
                                    <asp:ListItem Text="Order picker not available" Value="1"></asp:ListItem>
                                    <asp:ListItem Text="Delivery boy not available" Value="2"></asp:ListItem>
                                    <asp:ListItem Text="Ordered items not available" Value="3"></asp:ListItem>
                                    <asp:ListItem Text="Delivery area not reachable" Value="4"></asp:ListItem>
                                    <asp:ListItem Text="Unforseen reason" Value="5"></asp:ListItem>
                                </asp:DropDownList>
                            </div>

                            <div class="d-flex justify-content-center col-auto">
                                <asp:Button runat="server" ID="btnYes" CssClass="btn btn-primary mr-2 bd-0" Text="Yes" OnClick="btnYes_Click" />
                                <asp:Button runat="server" ID="btnNo" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" data-toggle="modal" Text="No" />
                            </div>

                            <!-- Yes/No buttons -->
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <asp:HiddenField ID="hfOrderID" runat="server" />
    <asp:HiddenField ID="hfUUID" runat="server" />
    <asp:HiddenField ID="hftstamp" runat="server" />
    <div id="modalHandledOrder" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalHandledOrderLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-body tx-center pd-y-20 pd-x-20">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="fa-thin fa-triangle-exclamation text-warning tx-100 lh-1 mg-t-20 d-inline-block"></i>
                    <%--<i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>--%>
                    <h4 class="text-warning tx-semibold mg-b-20" id="modalTitle"></h4>
                    <p class="mg-b-20 mg-x-20" id="modalContent" style="font-weight: 600;"></p>
                    <asp:Button ID="btnHandledOrderOK" runat="server" Text="OK" CssClass="btn btn-primary pd-x-25" OnClick="btnHandledOrderOK_Click" />
                </div>
            </div>
        </div>
    </div>

    <!----Modal Courier ---->
    <div id="modalviewCourierDetials" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-inverse">View Order Details</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="ordr_dtail_wrap border border-bottom-0 mb-3">
                <h6 class="ordr_dtail_head slim-card-title bg-light p-3 m-0 border-bottom">Order Details</h6>
                <div class="ordr_dtail_body">
                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Order ID</div>
                        <div class="ordr_dtail_info_dtails">
                            <asp:Literal ID="ltrOrder" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">From Address</div>
                        <div class="ordr_dtail_info_dtails">
                            <asp:Literal ID="ltrFromAddress" runat="server"></asp:Literal>
                            <span class="w-100 d-inline-block">
                                <asp:Literal ID="ltrfPhone" runat="server"></asp:Literal>
                            </span>
                            <span class="w-100 d-inline-block">
                                 <asp:Literal ID="ltrfEmail" runat="server"></asp:Literal>
                            </span>
                     </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">To Address</div>
                        <div class="ordr_dtail_info_dtails">
                            <asp:Literal ID="ltrToAddress" runat="server"></asp:Literal>
                                                        
                            <span class="w-100 d-inline-block">
                                <asp:Literal ID="ltrTPhone" runat="server"></asp:Literal>
                            </span>

                            <span class="w-100 d-inline-block">
                                <asp:Literal ID="ltrTEmail" runat="server"></asp:Literal>
                            </span>
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="ordr_dtail_wrap border border-bottom-0 mb-3">
                <h6 class="ordr_dtail_head slim-card-title bg-light p-3 m-0 border-bottom">Packing Info</h6>
                <div class="ordr_dtail_body">
                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">No. of Packets</div>
                        <div class="ordr_dtail_info_dtails">
                            <asp:Literal ID="ltrNoPacket" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Packet Dimension</div>
                        <div class="ordr_dtail_info_dtails">
                            <asp:Literal ID="ltrPacket" runat="server"></asp:Literal>
                        </div>
                    </div>
                </div>

                <div class="ordr_dtail_info">
                    <div class="ordr_dtail_info_items">Packet Weight</div>
                    <div class="ordr_dtail_info_dtails">
                        <asp:Literal ID="ltrPacketWeight" runat="server"></asp:Literal>
                    </div>
                </div>
            </div>

            <div class="ordr_dtail_wrap border border-bottom-0 mb-3">
                <h6 class="ordr_dtail_head slim-card-title bg-light p-3 m-0 border-bottom">Order Cost Details</h6>
                <div class="ordr_dtail_body">
                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Cart Value</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrSubTotal" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Delivery Charge</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrDeliveryCharge" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Taxes</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrGST" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">CESS</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrCess" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Round Off</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrRoundOff" runat="server"></asp:Literal>
                        </div>
                    </div>

                    <div class="ordr_dtail_info">
                        <div class="ordr_dtail_info_items">Total</div>
                        <div class="ordr_dtail_info_dtails">
                            <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>
                            <asp:Literal ID="ltrOrdAmt" runat="server"></asp:Literal>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        @media (min-width: 992px) {
            .modal-lg {
                max-width: 800px;
            }
        }

        .hiddenRow {
            padding: 0 !important;
        }

        tr[data-toggle="collapse"] {
            cursor: pointer;
        }

        tr[aria-expanded="true"] > td .action_arrow .fa-chevron-down::before {
            content: "\f077";
        }

        .ordr_dtail_info {
            width: 100%;
            display: flex;
            color: #2b2b2b;
            border-bottom: 1px solid #dfdfdf;
        }

        .ordr_dtail_info_items {
            width: 35%;
            border-right: 1px solid #dfdfdf;
            padding: 1rem;
        }

        .ordr_dtail_info_dtails {
            font-weight: 500;
            padding: 1rem;
            width: calc(100% - 35%);
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#<%= selCourier.ClientID %>').change(function () {
            var selectedValue = $(this).val();

            if (selectedValue === '-1') {
                $('#divCourierDropdown').hide();
                $('#divCourierTextbox').show();
                $('#divCourierTextbox label').html('Enter Courier Name<span id="selectDropdown" style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;">Select Courier</span>');
                $('#<%= txtCourierName.ClientID %>').focus();
            } else {
                $('#divCourierDropdown').show();
                $('#divCourierTextbox').hide();
                $('#divTrackingURL').show();
                var pageUrl = '<%= ResolveUrl("~/OrderDelivery.aspx") %>';
                $.ajax({
                    type: "POST",
                    url: pageUrl + "/GetTrackingURL",
                    data: '{ courierId: "' + selectedValue + '" }',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (response) {
                        $('#<%= txtTrackingURL.ClientID %>').val(response.d);
                    },
                    failure: function (response) {
                        alert("Failed to load tracking URL.");
                    }
                });
            }
        });

        $(document).on('click', '#selectDropdown', function () {
            $('#divCourierDropdown').show();
            $('#divCourierTextbox').hide();
            $('#divTrackingURL').show();
            $('#<%= selCourier.ClientID %>').val("");
        });
    });

        // check if the order is handled
        onActionClick = function (element, child) {

            if ($(child).hasClass('show'))
                $(child).removeClass('show');
            else {

                var orderID = element.getAttribute('data-id');
                var uuid = element.getAttribute('data-uuid');
                var timestamp = element.getAttribute('data-tstamp');


                document.getElementById('<%= hfOrderID.ClientID %>').value = orderID;
        document.getElementById('<%= hfUUID.ClientID %>').value = uuid;
        document.getElementById('<%= hftstamp.ClientID %>').value = timestamp;

                var spinner = element.querySelector('.loading-spinner');
                spinner.style.display = 'inline-block';

                onSuccess = function (response) {
                    spinner.style.display = 'none';
                    if (response.status == 'Delayed') {
                        $('#modalHandledOrder').modal('show');
                        $('#modalTitle').text('Order Status Update');
                        $('#modalContent').text('This order is no more a delayed order: ' + orderID);
                    } else {
                        $(child).addClass('show');
                    }
                };

                onError = function (data) {
                    alert('Operation failed');
                };

                retMaster.ajax.JSONRequest('/api/Home/OrderStatus', 'POST', { orderID: orderID }, onSuccess, onError);
            }
        };

    </script>

</asp:Content>

