<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="DetailedSalesReport.aspx.cs" Inherits="RetalineProAgent.Finance.DetailedSalesReport" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item"><a href="/navigations/salesReports">Transactions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detailed Sales Report</li>--%>
    <a href="/Navigations/salesReports"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Detailed Sales Report"></asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">In-Depth Sales Analysis</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="card-tools">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                                <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Select</label>
                                <asp:PlaceHolder ID="plcSelectbate" runat="server">
                                    <asp:DropDownList ID="seldate" AutoPostBack="true" CssClass="wd-100p-force form-control" runat="server" OnSelectedIndexChanged="seldate_SelectedIndexChanged"   >
                                        <asp:ListItem Text="Date Range" Value="1"></asp:ListItem>
                                        <asp:ListItem Text=" Month till Date" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Last Month" Value="3"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" Display="Dynamic" SetFocusOnError="true" ControlToValidate="seldate" ValidationGroup="SearchReport" Text="*" ForeColor="Red" ErrorMessage="Select period"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>
                            </div>
                            
                            <div class="col-12 col-lg-8 d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-start">
                                <asp:Panel ID="pnlDateRange" runat="server" CssClass="  d-flex align-items-center w-100 flex-wrap flex-sm-nowrap date_view_wrap">                                    
                                    <div class="input-group ml-0 ml-lg-2 mr-0 mr-sm-2 mb-3 mb-lg-0">
                                        <label for="txtDateFrom" runat="server" class="tx-dark mb-1 w-100">From:</label>
                                        <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>
                                    <div class="input-group ml-0 ml-sm-2 mr-0 mr-sm-2 mb-3 mb-lg-0">
                                        <label for="txtDateTo" runat="server" class="tx-dark mb-1 w-100">To:</label>
                                        <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>

                                </asp:Panel>
                                <div class="wd-150 ml-0 ml-lg-3">
                                    <label class="d-none d-lg-block" runat="server">&nbsp;</label>
                                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary" runat="server">Search</asp:LinkButton>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="accordion" class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" DataSourceID="SDSSalesReports" ID="gvdailySalesReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" OnRowDataBound="gvdailySalesReport_RowDataBound1" OnDataBound="gvdailySalesReport_DataBound" ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                             <Columns>
                                <asp:BoundField HeaderText="Date" DataField="DATE" SortExpression="DATE" DataFormatString="{0:dd-MMM-yyyy}" />
                                <asp:BoundField HeaderText="Order No." DataField="order_order_id" SortExpression="order_order_id"  ItemStyle-HorizontalAlign="Right" />
                                <asp:BoundField HeaderText="Total MRP" DataField="mrp" DataFormatString="{0:n}" SortExpression="mrp" ItemStyle-HorizontalAlign="Right" />                                                               
                                <asp:BoundField HeaderText="Seller Discount" DataField="sellerdiscount" DataFormatString="{0:n}" SortExpression="sellerdiscount"  ItemStyle-HorizontalAlign="Right" />                                
                                <asp:BoundField HeaderText="Coupon Discount" DataField="discount" DataFormatString="{0:n}" SortExpression="discount"  ItemStyle-HorizontalAlign="Right" />                                                               
                                <asp:BoundField HeaderText="Taxable Value" DataField="totalamount" DataFormatString="{0:n}" SortExpression="totalamount"  ItemStyle-HorizontalAlign="Right" /> 
                                <asp:TemplateField HeaderText="Amount Due" ItemStyle-HorizontalAlign="Right" >
                                    <ItemTemplate>
                                        <asp:Literal ID="ltrAmountdue" runat="server"></asp:Literal>
                                    </ItemTemplate>
                                </asp:TemplateField>                                
                                 <asp:TemplateField>
                                    <ItemTemplate>
                                        <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>
                                        </td></tr><tr>
                                            <td colspan="8" class="hiddenRow">
                                                <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                                  

                                                        <div class=" d-flex">
                                                            <div class="p-3 col-12 col-lg-4 p-0">
                                                                <div class=" ">Taxes: <strong><%# Eval("tax","{0:n}") %></strong></div>
                                                                <div class="">Round Off: <strong><%# Eval("order_roundoff","{0:n}") %></strong></div>
                                                                <div class="">Invoice Amount: <strong><%# Eval("total","{0:n}") %></strong></div>
                                                            </div>
                                                            <div class="p-3 col-12 col-lg-4 p-0">
                                                                <div class="">Payment Gateway Charges: <strong><%# Eval("pgcharges","-{0:n}") %></strong></div>
                                                                <div class="">Delivery Expenses: <strong><%# Eval("DeliveryExpenses","-{0:n}") %></strong></div>
                                                                <div class="">TCS(GST): <strong><%# Eval("tcs","-{0:n}") %></strong></div>
                                                            </div>
                                                            <div class="p-3 col-12 col-lg-4 p-0">
                                                                <div class="">TDS(Income Tax): <strong><%# Eval("tds","-{0:n}") %></strong></div>
                                                                <div class="">Order Refund: <strong><%# Eval("orderRefund","-{0:n}") %></strong></div>
                                                                <div class="">Delivery Charges<strong><%# Eval("deliverycharge","-{0:n}") %> </strong></div>
                                                            </div>
                                                        </div>                                                  
                                                </div>
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
                    </div>
                </div>

                <%--<div class="card-footer d-flex flex-wrap justify-content-between">
                    <div class="pagination-wrapper">
                        pagination
                    </div>
                </div>--%>
            </div>
        </div>
        </div>
    <asp:SqlDataSource ID="SDSSalesReports" runat="server" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSSalesReports_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  
                SelectCommand="SELECT * FROM(                           
                    SELECT order_confirm_date AS DATE,order_confirm_date,order_order_id,payment_mode,order_type,
                order_total_amount AS totalamount,total,(order_delivery_charge-order_delivery_charge_gst) AS deliverycharge,(order_total_gst+order_delivery_charge_gst) AS tax,order_mrp AS mrp,subtotal AS subtotal,order_total_cgst AS cgst,
                (CASE WHEN status_id=19 THEN total ELSE 0 END) AS orderRefund,subtotal AS totalprize,order_seller_discount AS sellerdiscount,order_discount_amount AS discount,order_payment_gateway_fees AS pg,
                order_payment_gateway_fees AS bankcharges,order_tdr AS bankchargetax,order_delivery_charge_gst,(order_delivery_charge-order_courier_charge-order_delivery_charge_gst+order_delivery_charge_gst) AS DeliveryExpenses,
                (order_tcs)AS tcs,(order_tds)AS tds,(order_tdr+order_tdr_igst+order_tdr_cgst+order_tdr_sgst) AS pgcharges,order_roundoff 
                FROM retaline_customer_order o WHERE  
                storegroup_id=@storegroupid AND DATE_FORMAT(order_confirm_date,'%Y-%m-%d') <= CURDATE() AND status_id>3  
                 ORDER BY order_confirm_date
                            )tmp WHERE ((@datefilter = 1 AND order_confirm_date BETWEEN @fromDate AND @toDate) OR (@datefilter = 3 AND 
                            YEAR(order_confirm_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(order_confirm_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) OR (@datefilter = 2 AND 
                            YEAR(order_confirm_date) = YEAR(CURDATE()) AND MONTH(order_confirm_date) = MONTH(CURDATE())))">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter Name="datefilter" ControlID="seldate" />
            <asp:ControlParameter Name="fromDate" ControlID="txtDateFrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtDateTo" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
</asp:Content>

