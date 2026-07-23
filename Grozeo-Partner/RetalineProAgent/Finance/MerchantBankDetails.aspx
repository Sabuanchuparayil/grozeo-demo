<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="MerchantBankDetails.aspx.cs" Inherits="RetalineProAgent.Finance.MerchantBankDetails" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <a href="/Finance/Navigations/MiscellaneousReport"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="">
        <h6 class="slim-pagetitle">Merchant Bank Details </h6>
        <p class="mb-0"> View and Download merchant bank details from here</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    
     <div class="card-body">
    <div class="table-responsive">
        <asp:GridView AutoGenerateColumns="false" DataSourceID="DSMerchantBankDetails" ID="gvMerchantBankDetails" runat="server" CssClass="table table-bordered gridview_table" GridLines="none" BorderColor="#ECECEC"
            AllowPaging="true" AllowSorting="false" HorizontalScrollBarMode = "Visible" verticalScrollBarMode="Visible"
            ShowFooter="false" PagerSettings-Visible="true" PageSize="10" >

            <Columns>                                      
                      
                <asp:BoundField HeaderText="Merchant Account Name" DataField="MerchantAccountName" SortExpression="MerchantAccountName" />
                <asp:BoundField HeaderText="Store Branch" DataField="storeBranch" SortExpression="storeBranch" />
                <asp:BoundField HeaderText="Account Name" DataField="AccountName" SortExpression="AccountName" />
                <asp:BoundField HeaderText="Account Number" DataField="AccountNumber" SortExpression="AccountNumber" />
                <asp:BoundField HeaderText="Bank Name"  DataField="BankName" SortExpression="BankName" />

            </Columns>  

            <EmptyDataTemplate>
                <div class="text-center">
                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                    <h6 class="mb-3">No record available</h6>
                </div>
            </EmptyDataTemplate>
            <PagerStyle CssClass="cssPager" />
            <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
        </asp:GridView>
    </div>
</div>

 <div class="card-footer d-flex flex-wrap justify-content-lg-end">
 <asp:LinkButton ID="btnDownload"  runat="server" Enabled="true" OnClick="btnDownload_Click"   CssClass="btn btn-primary py-1 mr-2">Download</asp:LinkButton>
 </div>  
    
        
          
  <asp:SqlDataSource ID="DSMerchantBankDetails" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
    SelectCommand=" select a.[Name] as MerchantAccountName, CanCheckout as [Checkout Enabled], OnlinePaymentEnabled as [Online Paymnet], PODEnabled as [POD Enabled], b.[Location] as storeBranch, b.Addr as BranchAddress, g.gstin, ba.AccountName, ba.AccountNumber, ba.SWIFT, ba.BankName, ba.Branch, ba.BankAddress, a.StoreId as storegroupid, b.APIBranchId as branchId, s.StorePhone as [Phone], s.StoreEmail as [Email], s.[Name] as [Contact Name]
from AppTenant a inner join Store s on s.TenantId=a.Id inner join StoreBranch b on b.StoreId=a.Id
left join BankAccount ba on b.BankId=ba.Id left join GST g on b.GSTId=g.id order by a.[Name], b.[Location]">
      
</asp:SqlDataSource>

</asp:Content>
      