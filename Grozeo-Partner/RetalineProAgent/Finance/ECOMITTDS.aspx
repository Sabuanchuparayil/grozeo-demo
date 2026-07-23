<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ECOMITTDS.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.ECOMITTDS" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/TDSreport"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Detailed IT-TDS Report</h6>
    <p class="mb-0">Detailed IT-TDS Report</p>
</asp:Content>
      <asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="card-tools">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                                <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Select</label>
                                <asp:PlaceHolder ID="plcSelectbate" runat="server">
                                    <asp:DropDownList ID="seldate" AutoPostBack="true" CssClass="wd-100p-force form-control" runat="server">
                                        <asp:ListItem Text="Date Range" Value="1"></asp:ListItem>
                                        <asp:ListItem Text=" Month till Date" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Last Month" Value="3"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" Display="Dynamic" SetFocusOnError="true" ControlToValidate="seldate" ValidationGroup="SearchReport" Text="*" ForeColor="Red" ErrorMessage="Select period"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>
                            </div>
                            
                            <div class="col-12 col-lg-8 d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-start">
                                <asp:Panel ID="pnlDateRange" runat="server" CssClass="  d-flex align-items-center w-100 flex-wrap flex-sm-nowrap date_view_wrap">                                    
                                    <div class="input-group ml-0 ml-lg-2 mr-0 mr-sm-2 mb-2 mb-lg-0">
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
                            AllowPaging="true" AllowSorting="true"  ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                             <Columns>
                                <asp:BoundField HeaderText="Date" DataField="invoicedate" SortExpression="invoicedate" DataFormatString="{0:dd-MMM-yyyy}" />
                                <asp:BoundField HeaderText="Invoice No" DataField="invoice" SortExpression="invoice"  ItemStyle-HorizontalAlign="Right" />
                                <asp:BoundField HeaderText="Invoiced To" DataField="store_group_name"  SortExpression="store_group_name" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="GSTIN" DataField="gst"  SortExpression="gst" ItemStyle-HorizontalAlign="Right" /> 
                                 <asp:BoundField HeaderText="PAN" DataField=""  SortExpression="" ItemStyle-HorizontalAlign="Right" />  
                                <asp:BoundField HeaderText="Gross Sales(INR)" DataField="amount" DataFormatString="{0:n}" SortExpression="amount"  ItemStyle-HorizontalAlign="Right" />                                
                                <asp:BoundField HeaderText="IT-TDS" DataField="TDS194O_IT_Total" DataFormatString="{0:n}" SortExpression="TDS194O_IT_Total"  ItemStyle-HorizontalAlign="Right" /> 
                             <%--   <asp:TemplateField HeaderText="Amount Due" ItemStyle-HorizontalAlign="Right" >
                                <ItemTemplate>
                                    <asp:LinkButton runat="server" ID="btninvoice"><i class="fa-thin fa-eye"></i></asp:LinkButton>
                                </ItemTemplate>
                                </asp:TemplateField>   --%>                                                                                                                            
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No record available</h6>
                                </div>
                            </EmptyDataTemplate>
                               <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                               <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                        </asp:GridView>
                    </div>
                </div>
                <asp:SqlDataSource ID="SDSSalesReports" runat="server" ProviderName="MySql.Data.MySqlClient"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  
                SelectCommand="SELECT
    inv.inv_number AS invoice,invoice_type,status_id,
    inv.created_at AS invoicedate,
    (SELECT br_GST FROM `finascop_branch` WHERE br_storeGroup = fb.store_group_id LIMIT 1) AS gst,
    fb.store_group_name AS store_group_name,
    fa.TDS194O_IT_Total AS TDS194O_IT_Total,
    fa.RestaurantSalesTotal_RST_ForSettlement AS amount
FROM
    retaline_customer_order rc
    INNER JOIN `invoice_number` inv ON rc.order_id = inv.order_id
    INNER JOIN `finance_autoposting_values` fa ON fa.order_id = rc.order_id
    INNER JOIN finascop_branch_group fb ON rc.storegroup_id = fb.store_group_id WHERE invoice_type IN (1,2)
UNION ALL
SELECT
    CAST(order_invoiceno AS CHAR) AS invoice,invoice_type,status_id,
    order_invoicedate AS invoicedate,
     (SELECT br_GST FROM `finascop_branch` WHERE br_storeGroup = fb.store_group_id LIMIT 1) AS gst,
     fb.store_group_name AS store_group_name,
     fa.TDS194O_IT_Total AS TDS194O_IT_Total,
    fa.MerchantSalesTotal_ForSettlement AS amount
FROM
    retaline_customer_order rc
    INNER JOIN `invoice_number` inv ON rc.order_id = inv.order_id
    INNER JOIN `finance_autoposting_values` fa ON fa.order_id = rc.order_id
    INNER JOIN finascop_branch_group fb ON rc.storegroup_id = fb.store_group_id WHERE invoice_type IN (1,2) AND status_id=18 AND
                                 ( @datefilter = 1 AND (@fromDate IS NULL OR @fromDate = '' OR CAST(inv.created_at AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(inv.created_at AS DATE) <= CAST(@toDate AS DATE))) OR ( @datefilter = 3 AND DATE_FORMAT(inv.created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m')
                                    ) OR ( @datefilter = 2 AND DATE_FORMAT(inv.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m'))">
        <SelectParameters>           
            <asp:ControlParameter Name="datefilter" ControlID="seldate" />
            <asp:ControlParameter Name="fromDate" ControlID="txtDateFrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtDateTo" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
                <%--<div class="card-footer d-flex flex-wrap justify-content-between">
                    <div class="pagination-wrapper">
                        pagination
                    </div>
                </div>--%>
            </div>
        </div>
        </div>
</asp:Content>

