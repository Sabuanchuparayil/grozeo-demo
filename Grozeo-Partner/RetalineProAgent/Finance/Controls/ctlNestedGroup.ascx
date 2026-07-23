<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctlNestedGroup.ascx.cs" Inherits="RetalineProAgent.Finance.Controls.ctlNestedGroup" %>
<asp:GridView ID="gvGroup" runat="Server" OnSelectedIndexChanged="gvGroup_SelectedIndexChanged" ShowFooter="true" Width="100%" 
    GridLines="Both" FooterStyle-Font-Bold="true" FooterStyle-HorizontalAlign="Right"  
    HeaderStyle-CssClass="gvHeader" CssClass="table table-bordered gridview_table" AlternatingRowStyle-CssClass="none" OnRowDataBound="gvGroup_RowDataBound" OnDataBound="gvGroup_DataBound" 
                AutoGenerateColumns="False" DataSourceID="SDSGroups">
                <Columns>
                    <asp:BoundField DataField="name" ItemStyle-Font-Bold="true"  HeaderText="Particulars" />
                    <asp:BoundField  ItemStyle-HorizontalAlign="Right" DataField="opening" DataFormatString="{0:0.00}" ItemStyle-Font-Bold="true" HeaderText="Opening" />
                    <asp:BoundField  ItemStyle-HorizontalAlign="Right" FooterText="" DataField="dr" DataFormatString="{0:0.00}" ItemStyle-Font-Bold="true" HeaderText="Debit" />
                    <asp:BoundField  ItemStyle-HorizontalAlign="Right" FooterText="" DataField="cr" DataFormatString="{0:0.00}" ItemStyle-Font-Bold="true" HeaderText="Credit" />
                    <%--<asp:BoundField  ItemStyle-HorizontalAlign="Right" DataField="closing" ItemStyle-Font-Bold="true" HeaderText="ClosingBalance" />--%>
                    <asp:TemplateField ItemStyle-HorizontalAlign="Right" HeaderStyle-HorizontalAlign="Right" ItemStyle-Font-Bold="true" HeaderText="Closing">
                        <ItemTemplate> <asp:Literal ID="ltrWarning" runat="server"></asp:Literal>
                            <asp:Literal ID="ltrClosing" runat="server" Text='<%# String.Format("{0:0.00}", Eval("closing")) %>'></asp:Literal>
                            <asp:PlaceHolder ID="plcGroup" runat="server"></asp:PlaceHolder>
                            <%--<asp:LinkButton ID="lbShowChild" runat="server" dataid='<%# Eval("groups_id") %>' CommandName="Select" CssClass="action_arrow tx-center"><i class="fa fa-chevron-down" onclick="hidegridview" aria-hidden="true"></i></asp:LinkButton>
                            </td></tr><tr>
                                <td colspan="7" class="align-middle hiddenRow">                                   
                                    
                                </td>
                            </tr>--%>
                            <asp:PlaceHolder ID="plcSuspenseAccount" runat="server" Visible="false">
                            </td></tr><tr class="Bfoter bg-transparent">
                                <td style="color: #dc3545;"><asp:Literal ID="ltrSAName" runat="server"/></td>
                                <td  align="right" style="color: #dc3545;"><asp:Literal ID="ltrSAOpening" runat="server"/></td>
                                <td  align="right" style="color: #dc3545;"><asp:Literal ID="ltrSACR" runat="server"/></td>
                                <td  align="right" style="color: #dc3545;"><asp:Literal ID="ltrSADR" runat="server"/></td>
                                <td  align="right" style="color: #dc3545;"><asp:Literal ID="ltrSAClosing" runat="server"/></td>
                            </tr>
                            </asp:PlaceHolder>
                        </ItemTemplate>
                    </asp:TemplateField>
                </Columns>
                <EmptyDataTemplate>No data available</EmptyDataTemplate>
            </asp:GridView>
<asp:Repeater ID="rpGroups" runat="server" DataSourceID="SDSGroups">
    <HeaderTemplate></td></tr></HeaderTemplate>
    <ItemTemplate><tr>
        <td style="padding-left:25px" <%# (Eval("ltype").Equals(0) ? "onclick=\"loadpopuptrialbalance(" + Eval("groups_id").ToString() + ")\"" : "" ) %> data-id='<%# Eval("groups_id") %>' ><%# Eval("ltype").Equals(0) ? String.Format("-&nbsp;{0}", Eval("name")) : String.Format("<i>&nbsp;&nbsp;&nbsp;{0} ", Eval("name")) %></td>
        <td style="text-align: right"><%# String.Format("{0:0.00}", Eval("opening")) %></td>
        <td style="text-align: right"><%# String.Format("{0:0.00}", Eval("dr")) %></td>
        <td style="text-align: right"><%# String.Format("{0:0.00}", Eval("cr")) %></td>
        <td style="text-align: right"><%# (MatchClosing(Eval("opening"), Eval("dr"), Eval("cr"), Eval("closing")) ? "" : "<i class=\"fas fa-exclamation-circle text-warning\"></i>" ) %><%# String.Format("{0:0.00}", Eval("closing")) %></td>
       </tr></ItemTemplate>
</asp:Repeater>
<asp:SqlDataSource ID="SDSGroups" runat="server" SelectCommand="LedgerTrialbalance" SelectCommandType="StoredProcedure" 
              OnSelecting="SDSGroups_Selecting"   ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                <SelectParameters>
                    <asp:Parameter Name="fromDate" />
                    <asp:Parameter Name="toDate" />
                    <asp:Parameter Name="parent_id" DefaultValue="-1" />
                </SelectParameters>
            </asp:SqlDataSource>
