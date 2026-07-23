<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="BalanceSheet.aspx.cs" Inherits="RetalineProAgent.BalanceSheet" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
         <a href="/Finance/Navigations/Reports"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">    
        <h6 class="slim-pagetitle">Balance Sheet</h6>
        <p class="mb-0">You can see Balance Sheet here</p>  
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

    <div class="card">
        <div class="card-header shadow_top">
           <%-- <h3 class="card-title">Balance Sheet: <asp:Literal ID="ltrFinancialYearDate" runat="server"></asp:Literal> </h3>--%>
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
                    <asp:LinkButton ID="lbtnSearch" dataid='<%# Eval("id") %>' CssClass="btn w-lg-100 btn-primary" OnClick="lbtnSearch_Click" runat="server">Search</asp:LinkButton>
                </div>
            </div>
            </div>
               
        <div id="accordion" class="card-body">
            <div class="table-responsive mailbox-messages">
                <table class="table table-borderless gridview_table">
                    <tr id="Tr1" runat="server" class="TableHeader">
                        <th id="Td1"  runat="server" style="width: 35%">Liabilities</th>
                        <th id="Td2" runat="server" style="width: 15%; text-align: right;"><asp:Literal ID="ltrLcHeader" runat="server" Text="Amount"></asp:Literal></th>
                        <th id="Th1"  runat="server" style="width: 35%; border-left: solid 1px">Assets</th>
                        <th id="Th3" runat="server" style="width: 15%; text-align: right;"><asp:Literal ID="ltrAHeader" runat="server" Text="Amount"></asp:Literal></th>
                    </tr>
                    <asp:Repeater ID="rptBalanceSheet" runat="server" OnItemDataBound="rptBalanceSheet_ItemDataBound">
                            <ItemTemplate>
                                <tr class="TableData">
                                    <td><%# Eval("Particulars1") %>
                                        <asp:Repeater ID="rptChild1" runat="server"><HeaderTemplate><table class="table table-borderless" style="margin-bottom: 0px;"></HeaderTemplate>
                                            <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%; text-align: right;"><%# String.Format("{0:0.00}", ConvertToMinus(Eval("total")) ) %></td></tr></ItemTemplate>
                                            <FooterTemplate><tr><td style="padding: 0px"></td><td style="border-top: solid 1px;padding: 0px"></td></tr></table></FooterTemplate>
                                        </asp:Repeater>
                                    </td>
                                    <td align="right" style="text-align: right; font-weight: bold"><%# String.Format("{0:0.00}", ConvertToMinus(Eval("Amt1"))) %></td>                                     
                                    <td style="border-left: solid 1px"><%# Eval("Particulars2") %>
                                        <asp:Repeater ID="rptChild2" runat="server"><HeaderTemplate><table class="table table-borderless" style="margin-bottom: 0px;"></HeaderTemplate>
                                            <ItemTemplate><tr><td><%# Eval("name") %></td><td style="width: 15%; text-align: right;"><%# String.Format("{0:0.00}", Eval("total")) %></td></tr></ItemTemplate>
                                            <FooterTemplate><tr><td style="padding: 0px"></td><td style="border-top: solid 1px; padding: 0px"></td></tr></table></FooterTemplate>
                                        </asp:Repeater>
                                    </td>
                                    <td align="right" style="text-align: right; font-weight: bold"><%# String.Format("{0:0.00}", Eval("Amt2")) %></td>                                     
                                </tr>
                            </ItemTemplate>
                            <FooterTemplate>
                                <asp:PlaceHolder ID="plcSuspenseAccount" runat="server" Visible="false">
                                <tr class="TableData"><td><asp:Literal ID="ltrSpL" runat="server"></asp:Literal></td>
                                    <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrSpLAmt" runat="server"></asp:Literal></td>                                     
                                    <td style="border-left: solid 1px"><asp:Literal ID="ltrSpA" runat="server"></asp:Literal></td>
                                    <td align="right" style="text-align: right; font-weight: bold"><asp:Literal ID="ltrSpAAmt" runat="server"></asp:Literal></td>                                     
                                </tr>

                                </asp:PlaceHolder>
                                <tr class="TableData" style="background-color: #E6E6E6; color: #717171;">
                                    <td><b>Total</b></td>
                                    <td align="right" style="text-align: right; font-weight: bold;"><asp:Literal ID="ltrLTotal" runat="server"></asp:Literal></td>                                     
                                    <td style="border-left: solid 1px"><b>Total</b></td>
                                    <td align="right" style="text-align: right; font-weight: bold;"><asp:Literal ID="ltrATotal" runat="server"></asp:Literal></td>                                     
                                </tr>
                            </FooterTemplate>
                        </asp:Repeater>   
                <tfoot> </tfoot>
            </table>
            </div>
            </div>  
            <asp:SqlDataSource ID="SDSBalancesheetGroups" runat="server" OnSelecting="SDSBalancesheetGroups_Selecting" SelectCommand="Balancesheet" SelectCommandType="StoredProcedure" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                <SelectParameters>
                    <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                    <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />
                </SelectParameters>
            </asp:SqlDataSource>
        </div>

    <style>
        .table .table {
            background-color:transparent;
        }
        .card-table .table td > .table {
            min-width:auto;
            width:100%;
        }
    </style>

</asp:Content>

