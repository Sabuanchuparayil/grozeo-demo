<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="settleorderdetails.aspx.cs"  MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.Finance.settleorderdetails" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settlement Report</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report">Settled Order Details</asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">Clear and Concise Settled Order Details</p>
    </div>    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
<div class="card">
    <div class="card-header shadow_top">
             <div class="row row-sm">
                <div class="form-group m-0 col-sm-6 col-lg-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-sm-6 col-lg-3 mb-2 mb-lg-0 px-1">
                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                </div>
                <div class="form-group m-0 col-lg-2 px-1">
                    <asp:LinkButton ID="lbtnSearch"  dataid='<%# Eval("id") %>' CssClass="btn btn-lg-block w-lg-100 btn-primary" runat="server">Search</asp:LinkButton>
                </div>
            </div>
        </div>
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
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbOrderNo" runat="server" Text='<%# Eval("entity_id")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("createdOn","{0:dd/MMM/yyy}")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("DisplayLabel")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("dr_amount","{0:n}")%>'></asp:Label>
                                                    </td>                                                   
                                                     <td align="left">
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
                                    <asp:SqlDataSource ID="SDSsettlement" runat="server" ConnectionString="<%$ connectionStrings:FinascopConnection %>" 
                                        SelectCommand="Settledorders" SelectCommandType="StoredProcedure" OnSelecting="SDSsettlement_Selecting">
                                            <SelectParameters>                                                                                       
                                                <asp:Parameter Name="orders"/>
                                                <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                                                <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" /> 
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                </div>
</div>
</asp:Content>
