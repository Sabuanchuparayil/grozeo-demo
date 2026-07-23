<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="settlementdetails.aspx.cs" Inherits="RetalineProAgent.Finance.settlementdetails" %>
 <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">
                                <div class="card-header py-2 px-1 border-0">
                                    <div>
                                      <h6 class="text-uppercase text-dark"><asp:Literal runat="server" ID="ltrsettlementhead"></asp:Literal></h6>
                                    </div>
                                    <div class="row row-sm">                                        
                                        <div class="col-12 col-lg-4">
                                            <label>Order Number: <strong><asp:Literal ID="lborderno" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                           <label>Order Date: <strong><asp:Literal ID="lborderdate" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                           <label>Delivery Confirmed: <strong><asp:Literal ID="lbconfirmed" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                           <label>Settlement Date: <strong><asp:Literal ID="lbsettledate" runat="server"></asp:Literal></strong></label>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                           <label>Settlement Amount: <strong><asp:Literal ID="lbsettleamount" runat="server"></asp:Literal></strong></label>
                                        </div>
                                    </div>
                                </div>
                                     <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvposting" DataSourceID="SDSposting" OnDataBound="lvposting_DataBound" runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Date</th>
                                                        <th id="Td2" runat="server">Voucher No</th>
                                                        <th id="Th9" runat="server">Voucher Type</th>
                                                        <th id="Td3" runat="server">Ledger</th>
                                                        <th id="Th1" runat="server">Event Calaculation</th>
                                                        <th id="Th2" runat="server">Debit</th>
                                                        <th id="Th3" runat="server">Credit</th>                                                       
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr> 
                                                         <tfoot >
                                                        <tr  id="trTotalRow" runat="server">
                                                            <td id="Td4"  runat="server"><b>Total</b></td>                                                           
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal7" runat="server"></asp:Literal></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal8" runat="server"></asp:Literal></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal1" runat="server"></asp:Literal></td>                                                            
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltttotalamount" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltrdr" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="ltrcr" runat="server"></asp:Literal></strong></td>
                                                        </tr>
                                                           <tr  id="trSettleRow" runat="server">
                                                            <td id="Td5" runat="server"><b>Amount to be Settled</b></td>                                                           
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal2" runat="server"></asp:Literal></td>
                                                             <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal3" runat="server"></asp:Literal></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="Literal4" runat="server"></asp:Literal></td>                                                            
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltramonttobepaid" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="Literal9" runat="server"></asp:Literal></strong></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                       <asp:Label ID="Label1" runat="server" Text='<%# Eval("createdOn", "{0:dd MMM yyyy hh:mm tt}") %>'></asp:Label>
                                                    </td>
                                                     <td>
                                                        <asp:Label ID="lbvouchernumber" runat="server" Text='<%# Eval("docSerialNo")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbvoucherno" runat="server" Text='<%# Eval("Voucher")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbLedger" runat="server" Text='<%# Eval("particulars")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbCalaculation" runat="server" Text='<%# Eval("event")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbDebit" runat="server" Text='<%# Eval("dr_amount","{0:n}")%>'></asp:Label>
                                                    </td>
                                                     <td align="right">
                                                        <asp:Label ID="lbCredit" runat="server" Text='<%# Eval("cr_amount","{0:n}")%>'></asp:Label>
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
                                         <asp:SqlDataSource runat="server" ID="SDSposting" OnSelecting="SDSRelationshipOfficer_Selecting" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                             SelectCommand="select d.entity_id, t.data_entry_id,d.createdOn,d.docSerialNo,(select vt.name from voucher_type vt where d.voucher_type_id=vt.id) as Voucher,
                                                d.event, t.particulars,CASE WHEN [isDebtor] = 1 THEN  t.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  t.amount 
                                                END AS cr_amount  from transactions t inner join data_entry d on t.data_entry_id=d.id inner join [ledger] l on l.id=t.ledger_id
                                                where d.[entity_id] = @order_id and l.refId=@storeRefId">
                                             <selectparameters>
                                              <asp:QueryStringParameter QueryStringField="order_order_id" Name="order_id"/>
                                              <asp:QueryStringParameter QueryStringField="storeRefId" Name="storeRefId"/>

                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                </div>
                            </div>                           
                        </div>    
                    </div>