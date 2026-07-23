<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Transcations.aspx.cs" Title="Transcations" Inherits="RetalineProAgent.Transcations" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transcations</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Transcations"></asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <%--<div class="container-fluid">--%>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <div class="row row-sm">
                           <div class="col-12 col-lg-4">
                               <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Select Period:</label>
                                <asp:PlaceHolder ID="plcSelectbate" runat="server" >
                                    <asp:DropDownList ID="seldate" AutoPostBack="true" CssClass="bd p-2 wd-100p-force" runat="server">
                                        <asp:ListItem Text="select Date"  ></asp:ListItem>
                                        <asp:ListItem Text="Date Range" Value="1" Selected="True"></asp:ListItem>
                                        <asp:ListItem Text=" Month till Date" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Last Month" Value="3"></asp:ListItem>
                                    </asp:DropDownList>
                                    <%--<asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>--%>
                                </asp:PlaceHolder>
                           </div>
                            <div class="col-12 col-lg-8 d-flex align-items-center justify-content-between">
                                <div class=" d-flex align-items-center w-100 date_view_wrap">
                                    <%--<div class="input-group mx-2">
                                        <label for="txtDateFrom" runat="server" class="tx-dark mb-1 w-100">&nbsp;</label>
                                        <asp:TextBox ID="txtDateFrom" runat="server"  CssClass="form-control ht-35" Text="07-02-2023" Enabled="false"></asp:TextBox>
                                    </div>--%>
                                    <div class="input-group mx-2">
                                        <label for="txtDateFrom" runat="server" class="tx-dark mb-1 w-100">Date - From:</label>
                                        <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control ht-35" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>
                                    <div class="input-group mx-2">
                                        <label for="txtDateTo" runat="server" class="tx-dark mb-1 w-100">Date - To:</label>
                                        <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control ht-35" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>
                                </div>
                                <div class="wd-150 ml-3">
                                    <label runat="server">&nbsp;</label>
                                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary ht-35 lh-1" runat="server">Search</asp:LinkButton>
                                </div>
                            </div>   
                        </div>
                    </div>
                </div>
                <%-- <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                            <asp:GridView AutoGenerateColumns="false" ID="gvtranscations" runat="server" CssClass="table" GridLines="None" BorderColor="#ECECEC"
                                AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDStranscations">
                                <Columns>
                                    <asp:BoundField HeaderText="Order ID" DataField="order_order_id" SortExpression="order_order_id" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Branch" DataField="br_Name" SortExpression="br_Name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Date" DataField="order_created_on" SortExpression="order_created_on" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Order Amount" DataField="order_total_amount" SortExpression="order_total_amount" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Payment Mode" DataField="payMode" SortExpression="payMode" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <%--<asp:BoundField HeaderText="PayGatewayId" DataField="order_payment_gateway_refid" SortExpression="order_payment_gateway_refid" />--%>
                <%-- </Columns>
                            </asp:GridView>
                            <asp:SqlDataSource runat="server" ID="SDStranscations" ProviderName="MySql.Data.MySqlClient"
                                SelectCommand=""></asp:SqlDataSource>--%>
            </div>
        </div>
    </div>
    <%--</div>
    </div>--%>
</asp:Content>

