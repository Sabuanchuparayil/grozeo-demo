<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Costentrydetails.aspx.cs" Inherits="RetalineProAgent.Finance.Costentrydetails" %>

<div class="card card-table">
    <div class="card-header bd-b-0-force bg-light">
        <div class="float-left"><b class="mr-1">Voucher Type:</b><asp:Literal ID="lbVoucher" runat="server"></asp:Literal></div>
        <div class="float-right"><b>Date:</b><asp:Literal ID="lbDate" runat="server"></asp:Literal></div>
        
        <div class="float-left w-100"><b class="mr-1">Voucher Number:</b><asp:Literal ID="lbVocherId" runat="server"></asp:Literal></div>
        <div class="float-left w-100"><b class="mr-1"><asp:Literal ID="lbtotal" runat="server"></asp:Literal>:</b><asp:Literal ID="lbramounttotal" runat="server"></asp:Literal><asp:Literal runat="server" ID="lbisdebtor"></asp:Literal></div>
    </div>
    <div class="table-responsive">
        <div class="card-body">
            <asp:ListView ID="lvDataEny" runat="server" DataSourceID="SqlDataEnty" OnDataBound="lvDataEny_DataBound">
                <layouttemplate>
                    <table id="Table1" runat="server" class="table table-bordered">
                        <tr id="Tr1" runat="server" class="TableHeader">
                            <th id="Td1" runat="server" class=" border-top bg-light">Particulars</th>
                            <th id="Td2" runat="server" class=" border-top bg-light">Debit Amount</th>
                            <th id="Td3" runat="server" class=" border-top bg-light">Credit Amount</th>
                        </tr>
                        <tr id="ItemPlaceholder" runat="server">
                        </tr>
                        <tfoot>
                            <tr>
                                <th id="Td4" runat="server">Total</th>
                                <th align="right" style="text-align:right;" >
                                    <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></th>
                                <th align="right" style="text-align:right;">
                                    <asp:Literal ID="ltrCRTotal" runat="server"></asp:Literal></th>
                            </tr> 
                        </tfoot>
                    </table>
                </layouttemplate>
                <itemtemplate>
                    <tr class="TableData">
                        <td>
                            <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("cost_centre_name")%>'></asp:Label>
                        </td>
                        <td align="right">
                            <asp:Label ID="lbDramount" runat="server" Text='<%# Eval("dr_amount", "")%>'></asp:Label>
                        </td>
                        <td align="right">
                            <asp:Label ID="lbCramount" runat="server" Text='<%# Eval("cr_amount", "")%>'></asp:Label>
                        </td>
                    </tr>
                </itemtemplate>
                <emptydatatemplate>No data available</emptydatatemplate>
            </asp:ListView>
            <asp:SqlDataSource runat="server" ID="SqlDataEnty" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                SelectCommand="
SELECT cc.particulars,cc.cost_centre_name,narration,(CASE WHEN cc.isDebtor = 1 THEN [cc].[amount] ELSE ''  END) AS dr_amount,
(CASE WHEN cc.isDebtor = 0 THEN [cc].[amount] ELSE ''  END) as cr_amount FROM transactions tr INNER JOIN  
[data_entry] de ON tr.data_entry_id =de.id inner join cost_centre_entries cc on tr.id=cc.transactions_id WHERE transactions_id = @transactions_id">
                <selectparameters>
                    <asp:QueryStringParameter QueryStringField="transactions_id" Name="transactions_id"/>
                </selectparameters>
            </asp:SqlDataSource>
            <div class="table-responsive">
                <table class="table table-bordered mt-2">
                    <tr>
                        <th class="py-2 bg-light border-top">
                            <span id="cpMainContent_lbNartion">Narration</span>
                        </th>
                    </tr>
                    <tr>
                        <td>
                           <asp:Literal ID="lbNarration" runat="server"> </asp:Literal>
                        </td>
                    </tr>                                  
                </table>
            </div>
        </div>
    </div>
