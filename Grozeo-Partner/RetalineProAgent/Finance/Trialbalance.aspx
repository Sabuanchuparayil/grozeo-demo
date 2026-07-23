<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" MaintainScrollPositionOnPostback="true" Title="Trial Balance" CodeBehind="~/Finance/Trialbalance.aspx.cs"   Inherits="RetalineProAgent.Finance.Trialbalance" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/Reports"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<%--<%@ Register Src="~/Controls/ctlNestedGroup.ascx" TagPrefix="uc1" TagName="ctlNestedGroup" %>--%>
<%@ Register Src="~/Finance/Controls/ctlNestedGroup.ascx" TagPrefix="uc1" TagName="ctlNestedGroup" %>


<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Trial Balance</h6>
    <p class="mb-0">You can see Trial Balance here</p>
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
                <div class="form-group m-0 col-sm-6 col-lg-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-sm-6 col-lg-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-lg-2 px-1">
                    <asp:LinkButton ID="lbtnSearch" OnClick="lbtnSearch_Click" dataid='<%# Eval("id") %>' CssClass="btn btn-lg-block w-lg-100 btn-primary" runat="server">Search</asp:LinkButton>
                </div>
            </div>
        </div>         
        <div id="accordion" class="card-body">
                        <div class="table-responsive">
                            <uc1:ctlNestedGroup runat="server" showtotal="true" ParentId="0" id="ctlNestedGroup1" />

            <asp:GridView Visible="false" ID="gvGroup" runat="Server" OnSelectedIndexChanged="gvGroup_SelectedIndexChanged" Width="100%" GridLines="None" HeaderStyle-CssClass="gvHeader" CssClass="table table-bordered" AlternatingRowStyle-CssClass="gvAltRow" OnDataBound="gvGroup_DataBound"
                AutoGenerateColumns="False">
                <Columns>
                    <asp:TemplateField>
                        <ItemTemplate>
                        </ItemTemplate>
                    </asp:TemplateField>
                    <asp:BoundField DataField="name" HeaderText="Groupname" />
                    <asp:BoundField DataField="opening" HeaderText="OpeningBalance" />
                    <asp:BoundField DataField="dr" HeaderText="Debit" />
                    <asp:BoundField DataField="cr" HeaderText="Credit" />
                    <asp:BoundField DataField="closing" HeaderText="ClosingBalance" />
                    <asp:TemplateField>
                        <ItemTemplate>
                            <asp:LinkButton ID="lbShowChild" runat="server" dataid='<%# Eval("parent_id") %>' CommandName="Select" CssClass="action_arrow tx-center"><i class="fa fa-chevron-down" onclick="hidegridview" aria-hidden="true"></i></asp:LinkButton>
                            </td></tr><tr>
                                <td colspan="7" class="align-middle hiddenRow">                                   
                                    
                                </td>
                            </tr>
                            <%--<div class="action_arrow tx-center"  data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne" ><i class="fa fa-chevron-down" aria-hidden="true"></i>
             </div>--%>
                        </ItemTemplate>
                    </asp:TemplateField>
                </Columns>
                <EmptyDataTemplate>No data available</EmptyDataTemplate>
            </asp:GridView>
                            </div>
            <asp:SqlDataSource ID="SDSGroups" runat="server" SelectCommand="TrialBalance" SelectCommandType="StoredProcedure"
                ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                <SelectParameters>
                    <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                    <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />
                    <asp:Parameter Name="parent_id" DefaultValue="102" />
                </SelectParameters>
            </asp:SqlDataSource>
            <asp:SqlDataSource ID="SDSChildGroup" runat="server" SelectCommand="TrialBalance" SelectCommandType="StoredProcedure"
                ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">
                <SelectParameters>
                    <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                    <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />
                    <asp:Parameter Name="parent_id" DefaultValue="-1"/>
                </SelectParameters>
            </asp:SqlDataSource>
        </div>
    </div>            
    <style>
        .hiddenRow {
            padding: 0px !important;
        }
        @media (min-width: 576px) {
           #personalModal .modal-dialog {
                max-width: 900px;
            }
        }
    </style>

     <div class="modal fade" id="personalModal" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" >
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">                    
                    <div id="dvpopuptrialbalance">
                    </div>
                </div>

            </div>
        </div>
    </div>
<script type="text/javascript">
    function loadpopuptrialbalance(prid) {
        $('#dvpopuptrialbalance').html('<div>Loading .. </div>');
        $('#personalModal').modal('show');
        var datestring = '<%= String.Format("&dtfrom={0}&dtto={1}", Convert.ToDateTime(txtFromDate.Text).ToString("yyyy/MM/dd"), Convert.ToDateTime(txtToDate.Text).ToString("yyyy/MM/dd")) %>';
        $('#dvpopuptrialbalance').load('/Finance/popuptrialbalance?prid=' + prid + datestring);
        }
</script>
    <style>
        .mt-2 {
    margin-top: 0.6rem !important;
}
        .modal button.close {
            margin: 0;
        }
        .Bfoter {
            background-color: #E6E6E6; color: #717171;
        }
    </style>
</asp:Content>
