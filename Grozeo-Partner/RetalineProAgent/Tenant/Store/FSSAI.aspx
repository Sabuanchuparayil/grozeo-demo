<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="FSSAI.aspx.cs" Inherits="RetalineProAgent.Tenant.Store.FSSAI" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">FSSAI Details</li>--%>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/BusinessSettings"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">FSSAI</h6>
    <p class="m-0">FSSAI Details</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body">
            <div class="p-3 shadow_top">
                <div class="row row-sm">
                    <div class="col-12 col-lg-9">
                        <h6 class="tx-dark mb-1">FSSAI Number</h6>
<%--                        <p class="mg-b-0">List of FSSAI accounts available. The account can be linked with branch to enable online transaction</p>--%>
                    </div>
                    <div class="col-lg-3 mt-3 mt-lg-0 d-flex align-items-start justify-content-lg-end">
                        <a href="/Tenant/Store/FSSAI-Add" class="btn px-4 d-block d-md-inline-block btn-primary">Add Account <i class="icon ion-plus-circled ml-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="table-responsive ">
                <asp:GridView ID="gvFSSAIAccounts" runat="server" GridLines="None" DataSourceID="SDSFSSAIs" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered">
                    <Columns>
                        <asp:BoundField HeaderText="FSSAI" DataField="FSSAIName" />
                        <asp:BoundField HeaderText="Account #" DataField="AccountNumber" />
                        <asp:BoundField HeaderText="Account Name" DataField="AccountName" />
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
                <asp:SqlDataSource ID="SDSFSSAIs" OnSelecting="SDSFSSAIs_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
                    SelectCommand="Select *, IIF(Verified= 'true', 'Verified', 'NOt Verified') as statusVerify, AccountNumber + ' - '+ FSSAIName as combinedFSSAI from FSSAI where TenantId=@storeId">
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
                        <h6 class="tx-dark mb-1">Stores Connected with FSSAI Number</h6>
                        <p class="mb-0">Stores / Branches with FSSAI Number.</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <asp:GridView ID="gvStores" runat="server" OnRowCommand="gvStores_RowCommand" GridLines="None" DataSourceID="ODSStore" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                    <Columns>
                        <asp:BoundField HeaderText="Store" DataField="BranchName" ReadOnly="true" />
                        <asp:TemplateField HeaderText="FSSAI Number">
                            <ItemTemplate>
                                <asp:Literal ID="ltrFSSAIAccount" runat="server" Text='<%# Eval("FSSAI") %>' Visible='<%# String.IsNullOrEmpty((string)Eval("FSSAI"))? false : true %>'></asp:Literal>
                                <asp:DropDownList ID="selFSSAIAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSFSSAIs" Visible='<%# String.IsNullOrEmpty((string)Eval("FSSAI"))? true : false %>' DataTextField="combinedFSSAI" DataValueField="id" OnSelectedIndexChanged="selFSSAIAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select FSSAI"></asp:ListItem>
                                </asp:DropDownList>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:DropDownList ID="selFSSAIAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSFSSAIs" DataTextField="combinedFSSAI" DataValueField="id" OnSelectedIndexChanged="selFSSAIAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select FSSAI"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="selFSSAIAccount" ErrorMessage="Please select FSSAI Number" ForeColor="Red" ValidationGroup="ChangeFSSAI"></asp:RequiredFieldValidator>
                            </EditItemTemplate>
                        </asp:TemplateField>
                        <asp:TemplateField HeaderText="">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Edit" Text="Change"></asp:LinkButton>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Cancel" Text="Cancel" ValidationGroup="ChangeFSSAI"></asp:LinkButton>
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


