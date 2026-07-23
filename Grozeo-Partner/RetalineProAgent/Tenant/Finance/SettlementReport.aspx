<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Merchant Settlement Report" CodeBehind="SettlementReport.aspx.cs" Inherits="RetalineProAgent.Finance.SettlementReport" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settlement Report</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report">Due Order Details</asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">Clear and Concise Settlement Details</p>
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
                        <asp:GridView AutoGenerateColumns="false" DataSourceID="SDSSettlementReport" ID="gvSettlementReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" OnRowDataBound="gvSettlementReport_RowDataBound" OnDataBound="gvSettlementReport_DataBound" ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                            <Columns>
                                <asp:BoundField HeaderText="Date" DataField="order_confirmed_on" DataFormatString="{0:dd MMM yyyy}" SortExpression="order_confirmed_on"  />
                                <asp:BoundField HeaderText="Order Number" DataField="order_order_id"  SortExpression="order_order_id" ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="Order Value" DataField="sale_proceeds" DataFormatString="{0:n}"  SortExpression="sale_proceeds" ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="Deductions" DataField="expenses" DataFormatString="{0:n}"  SortExpression="expenses" ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="Amount Payable"  DataField="amount_due" DataFormatString="{0:n}" SortExpression="amount_due" ItemStyle-HorizontalAlign="Right"  /> 
                                <asp:BoundField HeaderText="Settlement Date"  DataField="settlement_date" SortExpression="settlement_date" DataFormatString="{0:dd MMM yyyy}" ItemStyle-HorizontalAlign="Right"  /> 
                                <asp:TemplateField HeaderText="View" >
                                    <ItemTemplate>
                                        <asp:LinkButton ID="lbtnaction" storeref='<%# Eval("storeRefId") %>' CssClass="btn btn-sm btn-outline-primary" orderid='<%# Eval("order_order_id") %>' runat="server">Details</asp:LinkButton>
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
                                     <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                        </asp:GridView>
                    </div>
        </div><!-- card-body -->
     

    </div><!-- card -->
     
      <asp:SqlDataSource ID="SDSSettlementReport" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
       SelectCommand="SELECT bg.storeRefId,ms.storegroup_id,order_total_amount,rc.order_order_id,DATE(order_confirmed_on) AS order_confirmed_on,order_confirmed_on, DATE(rc.settlement_date) AS settlement_date,settlement_id,
                       so.sale_proceeds,so.expenses,so.refunds,so.amount_due,created_date,so.order_id FROM  merchant_settlements_order so 
                        INNER JOIN  merchant_settlements ms ON so.ms_ref_id= ms.ref_id INNER JOIN `finance_transaction_log` tl ON ms.id=tl.ms_id 
                        INNER JOIN   finance_transaction t ON tl.ft_id=t.id INNER JOIN `retaline_customer_order` rc  ON rc.order_id=so.order_id
                        INNER JOIN finascop_branch_group bg ON bg.store_group_id=t.storegroup_id WHERE (trim(@search) like '' or rc.order_order_id like CONCAT('%', @search, '%')) and ms.storegroup_id=@storegroupid and t.status_id <> 3 and (@fromDate IS NULL OR @fromDate = '' OR CAST(rc.order_confirmed_on AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(rc.order_confirmed_on AS DATE) <= CAST(@toDate AS DATE))"  
                    OnSelecting="SDSSettlementReport_Selecting">
        <SelectParameters>
            <asp:ControlParameter Name="fromDate" ControlID="txtFromDate" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtToDate" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
      </asp:SqlDataSource>

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
                                        <asp:ListView ID="lvsettlement" DataSourceID="SDSsettlement" OnDataBound="lvsettlement_DataBound"  runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Order No</th>
                                                        <th style="width:90px" id="Td2" runat="server">Order Date</th>
                                                        <th id="Td3" runat="server">Particulars</th>
                                                        <th id="Th1" runat="server">Earnings</th>
                                                        <th id="Th3" runat="server">Deductions</th>                                                        
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>
                                                    <tfoot>
                                                        <tr>
                                                            <td id="Td4" runat="server"><b>Total</b></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></td>                                                                                                                      
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltttotalamount" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltrdeduction" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="ltrsettleamount" runat="server"></asp:Literal></strong></td>                                                             
                                                        </tr>
                                                        <tr>
                                                             <td id="Td5" runat="server"><b>Payable</b></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal1" runat="server"></asp:Literal></td>                                                                                                                      
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="Literal2" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="Literal3" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="ltrpayable" runat="server"></asp:Literal></strong></td> 
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbOrderNo"  runat="server" Text='<%# Eval("entity_id")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("createdOn","{0:dd MMM}")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("DisplayLabel")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("dr_amount","{0:n}")%>'></asp:Label>
                                                    </td>                                                   
                                                     <td align="right">
                                                        <asp:Label ID="lbSettlementDate" runat="server" Text='<%# Eval("cr_amount","{0:n}")%>'></asp:Label>
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
                                    <asp:HiddenField ID="hidValueHeadStorRef" runat="server" />
                                    <asp:SqlDataSource ID="SDSsettlement" runat="server" ConnectionString="<%$ connectionStrings:FinascopConnection %>" 
                                        SelectCommand="with x as(
                                            SELECT de.entity_id,data_entry_id,l.refId,l.name,l.id,l.DisplayLabel FROM transactions tr 
                                            INNER JOIN  data_entry de ON tr.data_entry_id =de.id 
                                            inner join [ledger] l on l.id=tr.ledger_id where de.[entity_id] =@Id and refId =@ref
                                            )select de.entity_id, tr.data_entry_id,de.createdOn,de.docSerialNo,(select vt.name from voucher_type vt where de.voucher_type_id=vt.id) as Voucher,de.event, tr.particulars, tr.ledger_id as ledgerId,l.DisplayLabel,CASE WHEN [isDebtor] = 1 THEN  tr.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  tr.amount 
                                            END AS cr_amount from transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id inner join x on x.data_entry_id = tr.data_entry_id inner join [ledger] l on l.id=tr.ledger_id  where x.id!=tr.ledger_id">
                                            <SelectParameters>                                            
                                            <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="Id" DefaultValue="0" />
                                            <asp:ControlParameter ControlID="hidValueHeadStorRef" PropertyName="Value" Name="ref" DefaultValue="0" />
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
         @media (max-width: 991px) {
             .pg-ntion {
                 flex: auto;
                 max-width: none;
                 width: auto;
             }
         }
          @media (min-width: 992px) {
            #Pupaction .modal-dialog{
                max-width: 1106px;
            }
        }
     </style>
    </asp:Content>