<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Profit And Loss" CodeBehind="ProfitAndLoss.aspx.cs" Inherits="RetalineProAgent.Finance.ProfitAndLoss" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="/Finance/Navigations/Reports"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   
        <h6 class="slim-pagetitle">Profit And Loss</h6>
          <p class="mb-0">You can see Profit And Loss here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

    <div class="card">
        <div class="card-header shadow_top">
            <%--<div class="d-flex flex-wrap filter_ledger row">
                <div class="col-md-8">
                    <h4>Unit Name</h4>
                </div>
                <div class="p-2 flex-fill bd-highlight">
                    <asp:TextBox ID="TextBox1" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
            </div>--%>
            <div class="row row-sm">
                <div class="form-group m-0 col-sm-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-sm-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-sm-2 px-1">
                    <asp:LinkButton ID="lbtnSearch" dataid='<%# Eval("id") %>' CssClass="btn w-lg-100 btn-primary" runat="server">Search</asp:LinkButton>
                </div>
            </div>
        </div>         
        <div id="accordion" class="card-body">
                        <div class="table-responsive mailbox-messages">

                            <table class="table table-borderless gridview_table">
                        <tr id="Tr1" runat="server" class="TableHeader">
                            <th id="Td1"  runat="server" style="width: 35%;">Particulars</th>
                            <th id="Td2" runat="server" style="width: 15%; text-align:right;"><asp:Literal ID="ltrIncHeader" runat="server" Text="Amount"></asp:Literal></th>
                            <th id="Th1"  runat="server" style="width: 35%; border-left: solid 1px">Particulars</th>
                            <th id="Th3" runat="server" style="width: 15%; text-align:right;"><asp:Literal ID="ltrExpHeader" runat="server" Text="Amount"></asp:Literal></th>
                        </tr>
                             <asp:Repeater ID="rptGP" runat="server" OnItemDataBound="rptGP_ItemDataBound">
                                 <ItemTemplate>
                                     <tr class="TableData">
                                         <td><%# Eval("Particulars1") %>
                                             <asp:Repeater ID="rptGPChild1" runat="server"><HeaderTemplate><table class="table table-borderless"></HeaderTemplate>
                                                 <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%"><%# String.Format("{0:0.00}",( Eval("total"))) %></td></tr></ItemTemplate>
                                                 <FooterTemplate><tr><td></td><td style="border-top: solid 1px"></td></tr></table></FooterTemplate>
                                             </asp:Repeater>
                                         </td>
                                         <td align="right" style="text-align: right; font-weight: bold"><%# String.Format("{0:0.00}", (Eval("Amt1"))) %></td>                                     
                                         <td style="border-left: solid 1px"><%# Eval("Particulars2") %>
                                             <asp:Repeater ID="rptGPChild2" runat="server"><HeaderTemplate><table class="table table-borderless"></HeaderTemplate>
                                                 <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%"><%# String.Format("{0:0.00}",ConvertToMinus( Eval("total"))) %></td></tr></ItemTemplate>
                                                 <FooterTemplate><tr><td></td><td style="border-top: solid 1px"></td></tr></table></FooterTemplate>
                                             </asp:Repeater>
                                         </td>
                                         <td align="right" style="text-align: right; font-weight: bold"><%# String.Format("{0:0.00}", ConvertToMinus(Eval("Amt2"))) %></td>                                     
                                     </tr>
                                 </ItemTemplate>
                                 <FooterTemplate>
                                      <tr class="TableData">
                                         <td><asp:Literal ID="ltrGP" runat="server"></asp:Literal></td>
                                         <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrGPAmt" runat="server"></asp:Literal></td>                                     
                                         <td style="border-left: solid 1px"><asp:Literal ID="ltrGL" runat="server"></asp:Literal></td>
                                         <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrGLAmt" runat="server"></asp:Literal></td>                                     
                                     </tr>
                                      <tr class="TableData">
                                         <td></td>
                                         <td align="right" style="text-align: right; font-weight: bold; border-top: solid 1px; border-bottom: solid 1px"><asp:Literal ID="ltrDETotal" runat="server"></asp:Literal></td>                                     
                                         <td style="border-left: solid 1px"></td>
                                         <td align="right" style="text-align: right; font-weight: bold; border-top: solid 1px; border-bottom: solid 1px"><asp:Literal ID="ltrDITotal" runat="server"></asp:Literal></td>                                     
                                     </tr>
                                 </FooterTemplate>
                             </asp:Repeater>   
                             <asp:Repeater ID="rptNP" runat="server" OnItemDataBound="rptNP_ItemDataBound">
                                 <ItemTemplate>
                                     <tr class="TableData">
                                         <td><%# Eval("Particulars1") %>
                                             <asp:Repeater ID="rptNPChild1" runat="server"><HeaderTemplate><table class="table table-borderless"></HeaderTemplate>
                                                 <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%"><%# String.Format("{0:0.00}", Eval("total")) %></td></tr></ItemTemplate>
                                                 <FooterTemplate><tr><td></td><td style="border-top: solid 1px"></td></tr></table></FooterTemplate></asp:Repeater>
                                         </td>
                                         <td align="right" style="text-align: right; font-weight: bold"><%# ((Eval("Amt1").ToString()) == "0"? "" : String.Format("{0:0.00}",(Eval("Amt1")))) %></td>                                     
                                         <td style="border-left: solid 1px"><%# Eval("Particulars2") %>
                                             <asp:Repeater ID="rptNPChild2" runat="server"><HeaderTemplate><table class="table table-borderless"></HeaderTemplate>
                                                 <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%"><%# String.Format("{0:0.00}",ConvertToMinus(Eval("total"))) %></td></tr></ItemTemplate>
                                                 <FooterTemplate><tr><td></td><td style="border-top: solid 1px"></td></tr></table></FooterTemplate></asp:Repeater>
                                         </td>
                                         <td align="right" style="text-align: right; font-weight: bold"><%# String.Format("{0:0.00}",ConvertToMinus(Eval("Amt2"))) %></td>                                     
                                     </tr>
                                 </ItemTemplate>
                                 <FooterTemplate>
                                      <tr class="TableData">
                                         <td><asp:Literal ID="ltrNP" runat="server"></asp:Literal></td>
                                         <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrNPAmt" runat="server"></asp:Literal></td>                                     
                                         <td style="border-left: solid 1px"><asp:Literal ID="ltrNL" runat="server"></asp:Literal></td>
                                         <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrNLAmt" runat="server"></asp:Literal></td>                                     
                                     </tr>
                                      <tr class="TableData" style="background-color: #E6E6E6; color: #717171;">
                                         <td><b>Total</b></td>
                                         <td align="right" style="text-align: right; font-weight: bold;"><asp:Literal ID="ltrTotal1" runat="server"></asp:Literal></td>                                     
                                         <td style="border-left: solid 1px"><b>Total</b></td>
                                         <td align="right" style="text-align: right; font-weight: bold;"><asp:Literal ID="ltrTotal2" runat="server"></asp:Literal></td>                                     
                                     </tr>
                                 </FooterTemplate>

                             </asp:Repeater>   
                         <tfoot>
                        </tfoot>
                    </table>

                            <asp:Repeater ID="rptPandL" runat="server"></asp:Repeater>
            
                            </div>
            <asp:SqlDataSource ID="SDSPAndLGroups" runat="server" SelectCommand="with t1 as(
	select tr.*, l.groups_id, l.name from transactions tr inner join [ledger] l on tr.ledger_id=l.id 
	where l.groups_id=0 and CAST(createdOn AS DATE) >= @fromDate AND CAST(createdOn AS DATE) <= @toDate 
) select groups_id as id, name,0 as account_types_id,0 p_and_L_type, 0 as parent_id, (select top 1 isnull(openingBalance, 0) 
                from t1 order by id) as opening,
(sum(case when isDebtor=0 then isnull(amount, 0) else 0 end) - sum(case when isDebtor=1 then isnull(amount, 0) else 0 end)) as total ,  
(select top 1 isnull(closingBalance, 0) from t1 order by id desc) as closing, 1 as ltype 
from t1 group by groups_id, name
union all
   select id, [name],account_types_id as account_types_id,p_and_L_type as p_and_L_type ,parent_id, opening,(dr-cr)  as total,
                closing  * (case when account_types_id=3 then -1 else 1 end ), 0 as ltype from [groups] g 
                CROSS APPLY ParentTrialBalance(@fromDate, @toDate, g.id) 
	        as t where g.parent_id=0 and account_types_id in (3, 4) and closing is not null" 
                ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                <SelectParameters>
                    <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                    <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />
                </SelectParameters>
            </asp:SqlDataSource>

        </div>
    </div>            
    <style>
        .hiddenRow {
            padding: 0px !important;
        }
        .mt-2 {
    margin-top: 0.6rem !important;
}
    </style>

    <div class="card-body">
        <asp:ListView ID="lvProfitAndLoss" Visible="false" runat="server" OnDataBound="lvProfitAndLoss_DataBound">
            <LayoutTemplate>
                    <table id="Table1" runat="server" class="table table-bordered">
                        <tr id="Tr1" runat="server" class="TableHeader">
                            <th id="Td1"  runat="server" style="width: 35%">Particulars</th>
                            <th id="Td2" runat="server" style="width: 15%">Debit</th>
                            <th id="Th2" runat="server" style="width: 20%">Credit</th>
                        </tr>
                         <tr id="ItemPlaceholder" runat="server">
                             </tr>
                         <tfoot>
                            <tr align="right">
                                 <th align="right">
                                    <asp:Literal ID="Literal1" runat="server"> Total</asp:Literal></th>
                                <th align="right">
                                    <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></th>
                                <th align="right">
                                    <asp:Literal ID="ltrCrTotal" runat="server"></asp:Literal></th>
                            </tr>                             
                        </tfoot>
                    </table>
                </LayoutTemplate>
                <ItemTemplate>
                    <tr class="TableData">
                        <td>
                            <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("particulars")%>'>   
                            </asp:Label>                            
                        <td align="right">
                            <asp:Label ID="lbDebit" runat="server" Text='<%# String.Format("{0:0.00}", Eval("debit"))%>'>   
                            </asp:Label>
                        </td>
                        <td align="right">
                            <asp:Label ID="lbCredit" runat="server" Text='<%# String.Format("{0:0.00}", Eval("credit"))%>'>   
                            </asp:Label>
                        </td>                                          
                    </tr>                    
                </ItemTemplate>
                <EmptyDataTemplate>No data available</EmptyDataTemplate>
            </asp:ListView>
            <asp:SqlDataSource ID="SDSProfitandloss" runat="server" SelectCommand="SELECT 
	                                gr.name as particulars,
	                                ROUND(SUM(CASE 
			                                WHEN isDebtor = 1
				                                THEN tr.amount 
			                                END)
	                                ,2) AS debit,
	                                ROUND(SUM(CASE 
			                                WHEN isDebtor = 0
				                                THEN tr.amount 
			                                END)
	                                ,2) AS credit
                                    FROM [transactions] tr
                                    INNER JOIN [ledger] ld ON tr.[ledger_id] = ld.[id]
                                    INNER JOIN [groups] gr ON ld.groups_id = gr.id
                                    WHERE gr.[account_types_id] IN (3,4)
                                    GROUP BY ld.groups_id,gr.name"
                       ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                   </asp:SqlDataSource>                                     
    </div>
</asp:Content>
