<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="DailySalesReport.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.DailySalesReport" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Daily GST TCS Report</h6>
    <p class="mb-0">Daily GST TCS Report</p>
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
                                <asp:BoundField HeaderText="Date" DataField="order_invoicedate" SortExpression="order_invoicedate" DataFormatString="{0:dd-MMM-yyyy}" />
                                <asp:BoundField HeaderText="No.of Invoices" DataField="invoicecount" SortExpression="invoicecount"  ItemStyle-HorizontalAlign="Right" />
                                <asp:BoundField HeaderText="Gross Sales(INR)" DataField="MerchantSalesTotal_ForSettlement"  SortExpression="MerchantSalesTotal_ForSettlement" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="GST & Cess(INR)" DataField="gst"  SortExpression="gst" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="Invoice Total(INR)" DataField="MerchantSalesTotal_ForSettlement" DataFormatString="{0:n}" SortExpression="MerchantSalesTotal_ForSettlement"  ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="GST & Cess TCS(INR)" DataField="gsttota"  SortExpression="gsttota" ItemStyle-HorizontalAlign="Right" />                                 
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
                             <PagerStyle CssClass="cssPager" />
                             <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                        </asp:GridView>
                    </div>
                </div>
                <asp:SqlDataSource ID="SDSSalesReports" runat="server" ProviderName="MySql.Data.MySqlClient"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  
                SelectCommand="    SELECT order_invoicedate, COUNT(order_invoiceno) AS invoicecount,fb.store_group_name, SUM(MerchantSalesTotal_ForSettlement) as MerchantSalesTotal_ForSettlement,
    SUM((MerchantSalesIGSTTotal_ForSettlement+MerchantSalesCGSTTotal_ForSettlement+
    MerchantSalesSGSTTotal_ForSettlement+MerchantSalesUTGSTTotal_ForSettlement+
    MerchantSalesCCTotal_ForSettlement)) AS gst,SUM((COALESCE(TCSIGST,0)+ COALESCE(TCSCGST,0)+ COALESCE(TCSSGST,0)+ COALESCE(TCSCC,0))) AS tcs,
    SUM((MerchantSalesIGSTTotal_ForSettlement+MerchantSalesCGSTTotal_ForSettlement+
    MerchantSalesSGSTTotal_ForSettlement+MerchantSalesUTGSTTotal_ForSettlement+
    MerchantSalesCCTotal_ForSettlement+(COALESCE(TCSIGST,0)+ COALESCE(TCSCGST,0)+ COALESCE(TCSSGST,0)+ COALESCE(TCSCC,0))+MerchantSalesTotal_ForSettlement )) AS invoicetotal 
    FROM  finance_autoposting_values fa
    INNER JOIN retaline_customer_order rc ON fa.order_id=rc.order_id
    INNER JOIN finascop_branch_group fb ON rc.storegroup_id = fb.store_group_id
    INNER JOIN `invoice_number` inv ON rc.order_id = inv.order_id
 WHERE invoice_type =1 AND status_id=18 AND
                                 ( @datefilter = 1 AND (@fromDate IS NULL OR @fromDate = '' OR CAST(order_invoicedate AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(order_invoicedate AS DATE) <= CAST(@toDate AS DATE))) OR ( @datefilter = 3 AND DATE_FORMAT(order_invoicedate, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m')
                                    ) OR ( @datefilter = 2 AND DATE_FORMAT(order_invoicedate, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) 
                                    GROUP BY order_invoicedate order by order_invoicedate desc ">
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