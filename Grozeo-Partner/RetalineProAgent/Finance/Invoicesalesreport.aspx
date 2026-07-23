<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Invoicesalesreport.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.Invoicesalesreport" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Daily Sales Summary</h6>
    <p class="mb-0">Daily Sales Summary</p>
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
                                        <asp:ListItem Text="Year Till Date" Value="4"></asp:ListItem>
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
                        <asp:GridView AutoGenerateColumns="false" DataSourceID="SDSGSTdailySalesReports" ID="gvdailySalesReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true"  ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                             <Columns>
                                <asp:BoundField HeaderText="Date" DataField="formatted_date" SortExpression="formatted_date" DataFormatString="{0:dd-MMM-yyyy}" />
                                <asp:BoundField HeaderText="No.of Invoices" DataField="invoicecount" SortExpression="invoicecount"  ItemStyle-HorizontalAlign="Right" />
                                <asp:BoundField HeaderText="Gross Sales(INR)" DataField="total_MerchantSalesTotal_ForSettlement" DataFormatString="{0:n}" SortExpression="total_MerchantSalesTotal_ForSettlement"  ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="IGST" DataField="total_MerchantSalesIGSTTotal_ForSettlement"  SortExpression="total_MerchantSalesIGSTTotal_ForSettlement" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="CGST" DataField="total_MerchantSalesCGSTTotal_ForSettlement"  SortExpression="total_MerchantSalesCGSTTotal_ForSettlement" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="SGST/UTGT" DataField="total_MerchantSalesSGSTTotal_ForSettlement"  SortExpression="total_MerchantSalesSGSTTotal_ForSettlement" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="Compensation Cess(INR)" DataField="total_MerchantSalesCCTotal_ForSettlement"  SortExpression="total_MerchantSalesCCTotal_ForSettlement" ItemStyle-HorizontalAlign="Right" /> 
                                <asp:BoundField HeaderText="GST & Cess (INR)" DataField="total_gsttotal"  SortExpression="total_gsttotal" ItemStyle-HorizontalAlign="Right" />  
                                <asp:BoundField HeaderText="Invoice Total(INR)" DataField="total_invoicetotal"  SortExpression="total_invoicetotal" ItemStyle-HorizontalAlign="Right" />  
                                <asp:TemplateField HeaderText="Action" ItemStyle-HorizontalAlign="Right" >
                                <ItemTemplate>
                                    <asp:LinkButton runat="server" ID="btninvoice" recid='<%# Eval("created_at") %>' OnClick="btninvoice_Click" Text="View"></asp:LinkButton>
                                </ItemTemplate>
                                </asp:TemplateField>                                                                                                                               
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
                <asp:SqlDataSource ID="SDSGSTdailySalesReports" runat="server" ProviderName="MySql.Data.MySqlClient"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  
                SelectCommand="SELECT  DATE_FORMAT(inv.created_at, '%d-%m-%Y') AS formatted_date,COUNT(order_invoiceno) AS invoicecount,inv.created_at,SUM(MerchantSalesTotal_ForSettlement) AS total_MerchantSalesTotal_ForSettlement,SUM(fa.MerchantSalesIGSTTotal_ForSettlement) AS total_MerchantSalesIGSTTotal_ForSettlement,SUM(fa.MerchantSalesCGSTTotal_ForSettlement) AS total_MerchantSalesCGSTTotal_ForSettlement,
                               SUM(IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement)) AS total_MerchantSalesSGSTTotal_ForSettlement,SUM(fa.MerchantSalesUTGSTTotal_ForSettlement) AS total_MerchantSalesUTGSTTotal_ForSettlement,SUM(fa.MerchantSalesCCTotal_ForSettlement) AS total_MerchantSalesCCTotal_ForSettlement, SUM(fa.MerchantSalesIGSTTotal_ForSettlement + fa.MerchantSalesCGSTTotal_ForSettlement +
                               IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement) + fa.MerchantSalesCCTotal_ForSettlement) AS total_gsttotal,SUM(fa.MerchantSalesIGSTTotal_ForSettlement + fa.MerchantSalesCGSTTotal_ForSettlement +
                               IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement) +  fa.MerchantSalesCCTotal_ForSettlement + fa.MerchantSalesTotal_ForSettlement) AS total_invoicetotal,SUM(fa.MerchantSalesCCTotal_ForSettlement) as total_MerchantSalesCCTotal_ForSettlement 
                               FROM finance_autoposting_values fa INNER JOIN retaline_customer_order rc ON fa.order_id = rc.order_id INNER JOIN  finascop_branch_group fb ON rc.storegroup_id = fb.store_group_id INNER JOIN 
                               invoice_number inv ON rc.order_id = inv.order_id  WHERE invoice_type in (2,4) AND status_id=18 AND
                                  (@datefilter = 1 AND (@fromDate IS NULL OR @fromDate = '' OR CAST(inv.created_at AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(inv.created_at AS DATE) <= CAST(@toDate AS DATE))) OR ( @datefilter = 3 AND DATE_FORMAT(inv.created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m')
                                    ) OR ( @datefilter = 2 AND DATE_FORMAT(inv.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) 
                                   OR ( @datefilter = 4 AND DATE_FORMAT(inv.created_at, '%Y') = DATE_FORMAT(CURDATE(), '%Y')) GROUP BY DATE_FORMAT(inv.created_at, '%d-%m-%Y');">
        <SelectParameters>           
            <asp:ControlParameter Name="datefilter" ControlID="seldate" />
            <asp:ControlParameter Name="fromDate" ControlID="txtDateFrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtDateTo" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
            </div>
        </div>
    </div>

    <div class="modal" id="Pupaction" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">               
                 <div class="modal-content">
                <div class="modal-body">
                    <div class="modaltitle">
                        <button type="button" class="close position-absolute mt-2 mr-1" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>                  
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">                                
                                <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvsettlement" DataSourceID="SDSsettlement"  runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Invoice Date</th>
                                                        <th style="width:90px" id="Td2" runat="server">Invoice No</th>
                                                        <th id="Td3" runat="server">Invoiced To</th>
                                                        <th id="Th1" runat="server">"Gross Sales(INR)"</th>
                                                      <%--  <th id="Th2" runat="server">Settlement Rule</th>--%>
                                                        <th id="Th3" runat="server">"IGST(INR)"</th>
                                                         <th id="Th4" runat="server">"CGST(INR)"</th>
                                                         <th id="Th5" runat="server">"SGST/UTGST(INR)"</th>
                                                         <th id="Th6" runat="server">"Compensation Cess(INR)"</th>
                                                         <th id="Th7" runat="server">"Total GST & Cess(INR)"</th>
                                                        <th id="Th8" runat="server">"Invoice Total(INR)"</th>
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>
                                                    <tfoot>
                                                        <tr>
                                                            <td id="Td4" runat="server"><b>Total</b></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrCRTotal" runat="server"></asp:Literal></td>
                                                             <%--<td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal4" runat="server"></asp:Literal></td>--%>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal5" runat="server"></asp:Literal></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal6" runat="server"></asp:Literal></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal7" runat="server"></asp:Literal></td>                                                            
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltttotalamount" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltrdeduction" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="ltrsettleamount" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal8" runat="server"></asp:Literal></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbOrderNo" runat="server" Text='<%# Eval("formatted_date")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("inv_number")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("store_group_name")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("MerchantSalesTotal_ForSettlement")%>'></asp:Label>
                                                    </td>
                                                     <%--<td align="right">
                                                        <asp:Label ID="lbSettlementRule" runat="server" Text='<%# Eval("settlementrule")%>'></asp:Label>
                                                    </td>--%>
                                                     <td align="left">
                                                        <asp:Label ID="lbSettlementDate" runat="server" Text='<%# Eval("MerchantSalesIGSTTotal_ForSettlement")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbBranch" runat="server" Text='<%# Eval("MerchantSalesCGSTTotal_ForSettlement")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbOrderAmound" runat="server" Text='<%# Eval("MerchantSalesSGSTTotal_ForSettlement")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbDeductions" runat="server" Text='<%# Eval("MerchantSalesCCTotal_ForSettlement")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbSettleAmount" runat="server" Text='<%# Eval("gsttotal")%>'></asp:Label>
                                                    </td>
                                                     <td align="center"> 
                                                           <asp:Label ID="lbtotalamount" runat="server" Text='<%# Eval("invoicetotal")%>'></asp:Label>                                                                                           
                                                    </td>
                                                </tr>
                                            </ItemTemplate>                                          
                                            <EmptyDataTemplate>
                                                <div class="text-center">
                                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                                    <h6 class="mb-3">No record available</h6>
                                                </div>     
                                            </EmptyDataTemplate>
                                        </asp:ListView>
                                    </div>
                                     <asp:HiddenField ID="hidValueHeadOrderId" runat="server" />
                                    <asp:SqlDataSource runat="server" ID="SDSsettlement" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                            SelectCommand="SELECT DATE_FORMAT(inv.created_at, '%d-%m-%Y') AS formatted_date,inv.created_at,MerchantSalesTotal_ForSettlement,inv.created_at, fa.MerchantSalesIGSTTotal_ForSettlement,fa.MerchantSalesCGSTTotal_ForSettlement,(SELECT br_GST FROM `finascop_branch` WHERE br_storeGroup = fb.store_group_id LIMIT 1) AS gst,fb.store_group_name,
                                                           IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement) AS MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement, fa.MerchantSalesCCTotal_ForSettlement,inv.inv_number,
                                                           (fa.MerchantSalesIGSTTotal_ForSettlement + fa.MerchantSalesCGSTTotal_ForSettlement + IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement) + fa.MerchantSalesCCTotal_ForSettlement) AS gsttotal,MerchantSalesCCTotal_ForSettlement,
                                                           (fa.MerchantSalesIGSTTotal_ForSettlement + fa.MerchantSalesCGSTTotal_ForSettlement + IFNULL(fa.MerchantSalesSGSTTotal_ForSettlement, fa.MerchantSalesUTGSTTotal_ForSettlement) +  fa.MerchantSalesCCTotal_ForSettlement + fa.MerchantSalesTotal_ForSettlement) AS invoicetotal 
                                                           FROM  finance_autoposting_values fa INNER JOIN retaline_customer_order rc ON fa.order_id = rc.order_id INNER JOIN finascop_branch_group fb ON rc.storegroup_id = fb.store_group_id INNER JOIN invoice_number inv ON rc.order_id = inv.order_id WHERE DATE(inv.created_at)=@date;">
                                        <SelectParameters>
                                            <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="date" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                </div>

                            </div>                           
                        </div>       

                    </div>
                </div>

            </div>
            </div>
        </div>
    </div>
    <style>
 @media (min-width: 1024) {
            #Pupaction .modal-dialog.modal-dialog {
                max-width: 1106px;
            }
        }

    </style>
</asp:Content>
