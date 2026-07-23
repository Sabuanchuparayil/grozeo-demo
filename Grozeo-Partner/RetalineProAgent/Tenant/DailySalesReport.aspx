<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Daily Sales Report" CodeBehind="DailySalesReport.aspx.cs" Inherits="RetalineProAgent.DailySalesReport" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Daily Sales Report</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report"></asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-4">
                                <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Select Period:</label>
                                <asp:PlaceHolder ID="plcSelectbate" runat="server">
                                    <asp:DropDownList ID="seldate" AutoPostBack="true" CssClass="bd p-2 wd-100p-force" runat="server">
                                        <asp:ListItem Text="select Date"></asp:ListItem>
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
                <div class="card-body">
                    <div class="table-responsive mailbox-messages">
                        <asp:GridView AutoGenerateColumns="false" ID="gvdailySalesReport" runat="server" CssClass="table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10">
                            <Columns>
                                <asp:BoundField HeaderText="Date" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="No.of Orders" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Sales" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Delivery" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Taxes" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Total" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Bank Charges" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Delivary charges" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="TCS(GST)" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="TDS(Income Tax)" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Order Refund" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Amount Due" DataField="" SortExpression="" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            </Columns>
                        </asp:GridView>
                    </div>
                </div>
            </div>
        </div>
    </div>
</asp:Content>
