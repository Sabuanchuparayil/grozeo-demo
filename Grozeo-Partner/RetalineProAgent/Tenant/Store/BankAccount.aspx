<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BankAccount.aspx.cs" Inherits="RetalineProAgent.BankAccount" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bank Details</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/StoreSettings"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Bank Details</h6>
    <p class="m-0">Bank Details</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body">
            <div class="p-3 shadow_top">
                <div class="row row-sm">
                    <div class="col-12 col-lg-9">
                        <h6 class="tx-dark mb-1">Accounts</h6>
                        <p class="mg-b-0">List of bank accounts available. The account can be linked with branch to enable online transaction</p>
                    </div>
                    <div class="col-lg-3 mt-3 mt-lg-0 d-flex align-items-start justify-content-lg-end">
                        <a href="/Tenant/Store/BankAccount-Add" class="btn px-4 d-block d-md-inline-block btn-primary">Add Account <i class="icon ion-plus-circled ml-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="table-responsive ">
                <asp:GridView ID="gvBankAccounts" runat="server" GridLines="None" DataSourceID="SDSBanks" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered">
                    <Columns>
                        <asp:BoundField HeaderText="Bank" DataField="BankName" />
                       <asp:TemplateField>
                           <HeaderTemplate>
                               <%# ConfigurationManager.AppSettings["CountryCode"] == "AE" ? "IBAN #" : "Account #" %>
                           </HeaderTemplate>
                           <ItemTemplate>
                               <%# Eval("AccountNumber") %>
                           </ItemTemplate>
                       </asp:TemplateField>
                   <asp:BoundField HeaderText="Account Name" DataField="AccountName" />
                        <asp:BoundField HeaderText="Branch" DataField="Branch" />
                        <asp:BoundField HeaderText="Status" DataField="statusVerify" />
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
                <asp:SqlDataSource ID="SDSBanks" OnSelecting="SDSBanks_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
                    SelectCommand="Select *, IIF(Verified= 'true', 'Verified', 'Not Verified') as statusVerify, AccountNumber + ' - '+ Branch + '-' + BankName as combinedBank from BankAccount where TenantId=@storeId">
                    <SelectParameters>
                        <asp:Parameter Name="storeId" />
                    </SelectParameters>
                </asp:SqlDataSource>


            </div><!-- table-responsive -->
            
        </div><!-- card-body -->

        <div class="card-body mt-3">
            <div class="p-3 shadow_top">
                <div class="row row-sm">
                    <div class="col-12">
                        <h6 class="tx-dark mb-1">Stores Connected with Bank Account</h6>
                        <p class="mb-0">Stores / Branches with bank accounts. Select bank account for the stores that is missing account, in order to process payouts.</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <asp:GridView ID="gvStores" runat="server" OnRowCommand="gvStores_RowCommand" GridLines="None" DataSourceID="ODSStore" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                    <Columns>
                        <asp:BoundField HeaderText="Store" DataField="BranchName" ReadOnly="true" />
                        <asp:TemplateField>
                            <HeaderTemplate>
                                <%# ConfigurationManager.AppSettings["CountryCode"] == "AE" ? "IBAN" : "Bank Account" %>
                            </HeaderTemplate>
                            <ItemTemplate>
                                <asp:Literal ID="ltrBankAccount" runat="server" Text='<%# Eval("Bank") %>' Visible='<%# String.IsNullOrEmpty(Eval("Bank").ToString())? false : true %>'></asp:Literal>
                                <asp:DropDownList ID="selBankAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSBanks" Visible='<%# String.IsNullOrEmpty(Eval("Bank").ToString())? true : false %>' DataTextField="combinedBank" DataValueField="id" OnSelectedIndexChanged="selBankAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select Bank"></asp:ListItem>
                                </asp:DropDownList>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:DropDownList ID="selBankAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSBanks" DataTextField="combinedBank" DataValueField="id" OnSelectedIndexChanged="selBankAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select Bank"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="selBankAccount" ErrorMessage="Please select bank account" ForeColor="Red" ValidationGroup="ChangeBank"></asp:RequiredFieldValidator>
                            </EditItemTemplate>
                        </asp:TemplateField>
                        <asp:TemplateField HeaderText="">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Edit" Text="Change"></asp:LinkButton>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Cancel" Text="Cancel" ValidationGroup="ChangeBank"></asp:LinkButton>
                            </EditItemTemplate>
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
                <asp:ObjectDataSource ID="ODSStore" runat="server" OnSelecting="ODSStore_Selecting" TypeName="RetalineProAgent.Services.StoreService" SelectMethod="GetStores">
                    <SelectParameters>
                        <asp:Parameter Name="storegroupid" />
                        <asp:Parameter Name="apistoregroupid" />
                        <asp:Parameter Name="all" DefaultValue="true" Type="Boolean" />
                    </SelectParameters>
                </asp:ObjectDataSource>
            </div> <!-- table-responsive -->
            
        </div>
    </div><!-- card -->
</asp:Content>

