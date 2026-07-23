<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="PayOutReports.aspx.cs"  Inherits="RetalineProAgent.Tenant.Finance.PayOutReports" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
        <a href="/Navigations/Settlement"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report">PayOut Details</asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">Track Earnings and Payouts</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
   <div class="card">
        <div class="card-header shadow_top">
               <div class="row row-sm justify-content-between">
            <div class="col-12">
                <div class="row row-sm">
                    <div class="form-group col-12 col-md-3 mb-2 mb-lg-0 pr-md-1">
                        <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                        <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                    <div class="form-group col-12 col-md-3 mb-2 mb-md-0 pl-md-1">
                        <label for="txtToDate" class="tx-dark" runat="server">To</label>
                        <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                     <div class="input-group input-group col-12 col-md-1 align-items-end pl-md-1 mb-2 mb-md-0">
                        <input type="submit" name="" value="GO" id="" class="btn btn-primary">
                      </div>
                    <div class="form-group col-12 col-md-5 align-items-end mb-0 d-flex">
                          <div class="input_search_box">
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />

                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton runat="server" CssClass="input-group-append">                        
                            </asp:LinkButton>

                            <input type="text" style="display:none" />
                                <input type="password" style="display:none" />
                            <asp:LinkButton ID="lbtnSearch"  CssClass="btn bd bd-l-0 tx-gray-600 "  runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                        </div>
                    </div>
                </div>
                
            </div>  
      </div>
                </div>
        <!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <asp:GridView AutoGenerateColumns="false" DataSourceID="SDSPayoutReport" ID="gvPayoutReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                    <Columns>
                        <asp:BoundField HeaderText="Date" DataFormatString="{0:dd-MMM-yyyy}" DataField="created_at" SortExpression="created_at" />
                        <asp:BoundField HeaderText="Settlement Id" DataField="settlement_id" SortExpression="settlement_id" />
                        <asp:BoundField HeaderText="Amount Paid" DataFormatString="{0:n}" DataField="payout_amount" ItemStyle-HorizontalAlign="Right" SortExpression="payout_amount" />
                        <asp:BoundField HeaderText="Payment Ref." DataField="UTRno" SortExpression="UTRno" ItemStyle-HorizontalAlign="left" />
                        <asp:BoundField HeaderText="No Of Orders" DataField="orders" SortExpression="orders" />
                        <asp:BoundField HeaderText="Total Amount" DataField="sale_proceeds" DataFormatString="{0:n}" SortExpression="sale_proceeds" ItemStyle-HorizontalAlign="Right" />                                               
                        <asp:BoundField HeaderText="Total Deductions" DataField="expenses" DataFormatString="{0:n}" SortExpression="expenses" ItemStyle-HorizontalAlign="Right" />
                        <asp:TemplateField HeaderText="View" >
                                    <ItemTemplate>
                                        <asp:LinkButton ID="lbtnaction" recid='<%# Eval("id") %>' OnClick="lbtnaction_Click" CssClass="btn btn-sm btn-outline-primary"  runat="server">Details</asp:LinkButton>
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
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                </asp:GridView>
            </div>
              <asp:SqlDataSource ID="SDSPayoutReport" runat="server" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSPayoutReport_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT  t.*,settlement_id,t.id,status_id,t.ifsc_code,transaction_type,sale_proceeds,expenses,(SELECT COUNT(*) FROM merchant_settlements ms INNER JOIN finance_transaction_log tl ON tl.ms_id=ms.id 
                        INNER JOIN merchant_settlements_order o ON ms.ref_id=o.ms_ref_id WHERE tl.ft_id=t.id) AS orders
                        FROM   finance_transaction t 
                        INNER JOIN finascop_branch_group fb  ON fb.store_group_id=t.storegroup_id 
                        INNER JOIN finance_transaction_log  tl ON tl.ft_id=t.id 
                        INNER JOIN merchant_settlements ms ON tl.ms_id=ms.id WHERE t.status_id=3 and (trim(@search) like '' or settlement_id like CONCAT('%', @search, '%')) and (@fromDate IS NULL OR @fromDate = '' OR CAST(t.created_at AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(t.created_at AS DATE) <= CAST(@toDate AS DATE)) and t.storegroup_id=@storegroupid Group by t.id">
        <SelectParameters>
            <asp:ControlParameter Name="fromDate" ControlID="txtFromDate" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtToDate" ConvertEmptyStringToNull="false" />
             <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>  
        </div>        <!-- card-body -->       
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
                                <div class="card-header py-2 px-1 border-0">
                                    <div class="row row-sm">                                        
                                        <div class="col-12 col-lg-3">
                                            <label>Store Group Name: <strong><asp:Literal ID="lbstoregroup" runat="server"></asp:Literal></strong></label>
                                        </div>                                       
                                        <div class="col-12 col-lg-3">
                                           <label>Settlement Id: <strong><asp:Literal ID="lbsettlementid" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <label>Amount: <strong><asp:Literal ID="lbamount" runat="server"></asp:Literal></strong></label>
                                        </div>   
                                        <div class="col-12 col-lg-3">
                                           <label>Settlement Due Date: <strong><asp:Literal ID="lbsettlementdate" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-3">
                                           <label>Initiated Date: <strong><asp:Literal ID="lbinitiateddate" runat="server"></asp:Literal></strong></label>
                                        </div>
                                         <div class="col-12 col-lg-3">
                                            <label>Payment Account: <strong><asp:Literal ID="lbbankaccount" runat="server"></asp:Literal></strong></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvsettlement" DataSourceID="SDSsettlement"  OnDataBound="lvsettlement_DataBound" runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Order No</th>
                                                        <th style="width:90px" id="Td2" runat="server">Order Date</th>
                                                        <th id="Td3" runat="server">Delivery Date</th>
                                                        <th id="Th1" runat="server">Delivery Confirmed Date</th>
                                                        <th id="Th3" runat="server">Settlement Date</th>
                                                         <th id="Th4" runat="server">Branch</th>
                                                         <th id="Th5" runat="server">Sale Value Amount</th>
                                                         <th id="Th6" runat="server">Deductions</th>
                                                         <th id="Th7" runat="server">Settle Amount</th>
                                                        <th id="Th8" runat="server">Action</th>
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
                                                        <asp:Label ID="lbOrderNo" runat="server" Text='<%# Eval("order_order_id")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("order_confirmed_on","{0:dd MMM yyyy}")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("quor_DeliveredTime","{0:dd MMM yyyy}")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("quor_DeliveryConfTime","{0:dd MMM yyyy}")%>'></asp:Label>
                                                    </td>               
                                                     <td align="left">
                                                        <asp:Label ID="lbSettlementDate" runat="server" Text='<%# Eval("settlement_date","{0:dd MMM yyyy}")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbBranch" runat="server" Text='<%# Eval("br_name")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbOrderAmound" runat="server" Text='<%# Eval("sale_proceeds","{0:n}")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbDeductions" runat="server" Text='<%# Eval("expenses","{0:n}")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbSettleAmount" runat="server" Text='<%# Eval("amount_due","{0:n}")%>'></asp:Label>
                                                    </td>
                                                     <td align="center"> 
                                                         <div class="d-flex align-item-center">
                                                             <button type="button" id="btnDetails" class="btn mr-1 px-1 py-0 tx-dark" data-toggle="modal" data-target="#Pupsettlementdetails"  onclick="loadsettlementDetails('<%# Eval("order_order_id") %>', '<%# Eval("storeRefId") %>', 1)"><i class="fa-thin fa-eye"></i></button>   
                                                             <button type="button" id="btnDetails1" class="btn ml-1 px-1 py-0 tx-dark" data-toggle="modal" data-target="#Pupsettlementdetails"  onclick="loadsettlementDetails('<%# Eval("order_order_id") %>', '<%# Eval("storeRefId") %>',2)"><i class="fa-thin fa-clipboard-list"></i></button> 
                                                         </div>                                                                                              
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
                                            SelectCommand="SELECT bg.storeRefId, rc.order_order_id,DATE(order_confirmed_on) AS order_confirmed_on,DATE(quor_DeliveredTime) AS quor_DeliveredTime,
                                            DATE(rc.settlement_date) AS settlement_date,
                                            DATE(quor_DeliveryConfTime) AS quor_DeliveryConfTime,so.sale_proceeds,br_name,so.expenses,
                                            so.refunds,so.amount_due,created_date,so.order_id FROM  merchant_settlements_order so 
                                            INNER JOIN  merchant_settlements ms ON so.ms_ref_id= ms.ref_id  
                                            INNER JOIN `finance_transaction_log` tl ON ms.id=tl.ms_id 
                                            INNER JOIN `retaline_customer_order` rc  ON rc.order_id=so.order_id
                                            INNER JOIN finascop_branch fb ON rc.order_branch_id=fb.br_id                                           
                                            INNER JOIN finascop_branch_group bg ON bg.store_group_id=br_storeGroup
                                            INNER JOIN finascop_stock_transfer_order ON fstr_id = rc.order_id AND fsto_orderType = 1 
                                            INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id where tl.ft_id=@Id">
                                        <SelectParameters>
                                            <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="Id" DefaultValue="0" />
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
      <div class="modal" id="Pupsettlementdetails" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered w-100">
            <div class="modal-content">
                <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>                
                <div class="modal-body">
                    <div id="dvpopupsettlementdetails">
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <style>                    
        #Pupsettlementdetails {
            z-index: 1051;
        }
        .modal-backdrop.show:last-child {
            z-index: 1050;
        }
        @media (min-width: 992px) {
            #Pupaction .modal-dialog, #Pupsettlementdetails .modal-dialog,#Puppaymentdetails .modal-dialog {
                max-width: 1106px;
            }
        }
        @media (max-width: 991px) {
            .pg-ntion {
                flex: auto;
                max-width: none;
                width: auto;
            }
        }

        @media (min-width: 576px) {
        }        
    </style>
     <script type="text/javascript">
       
        function loadsettlementDetails(order_order_id, storeRefId, type) {
            $('#dvpopupsettlementdetails').html('<div class="d-flex justify-content-center py-2">Loading .. </div>');
            $('#dvpopupsettlementdetails').load('/Finance/settlementdetails?order_order_id=' + order_order_id + '&storeRefId=' + storeRefId + '&type=' + type);
        }
     </script>
</asp:Content>
