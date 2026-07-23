<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="SettlementReportDownload.aspx.cs" Inherits="RetalineProAgent.Tenant.Finance.SettlementReportDownload" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <a href="/Finance/Navigations/MiscellaneousReport"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="">
        <h6 class="slim-pagetitle">Settlement Data</h6>
        <p class="mb-0">Download settlement data from here</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm justify-content-between">
                <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                    <div class="form-group mb-0">
                        <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                        <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                    <div class="form-group mb-0">
                        <label for="txtToDate" class="tx-dark" runat="server">To</label>
                        <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                </div>
                <div class="col-12 col-lg-4 d-flex align-items-end">
                     <asp:Button ID="btnSearch" text="Search" runat="server" CssClass="btn btn-primary" OnClick="btnSearch_Click"/>
                </div>
            </div>
          </div>
        </div>
     <div class="card-body">
    <div class="table-responsive">
        <asp:GridView AutoGenerateColumns="false" DataSourceID="SDSSettlementDownload" ID="gvSettlementDownload" runat="server" CssClass="table table-bordered gridview_table" GridLines="none" BorderColor="#ECECEC"
            AllowPaging="true" AllowSorting="false" HorizontalScrollBarMode = "Visible" verticalScrollBarMode="Visible"
            ShowFooter="false" PagerSettings-Visible="true" PageSize="10" >
            <Columns>                                      
                      
                <asp:BoundField HeaderText="Store Group Name" DataField="store_group_name" SortExpression="store_group_name" />
                <asp:BoundField HeaderText="Branch Name" DataField="br_name" SortExpression="br_name" />
                <asp:BoundField HeaderText="Order Group ID" DataField="order_group_id" SortExpression="order_group_id" />
                <asp:BoundField HeaderText="Order ID" DataField="order_order_id" SortExpression="order_order_id" />
                <asp:BoundField HeaderText="Created On" DataFormatString="{0:dd-MMMM-yyyy}" DataField="createdOn" SortExpression="createdOn" />
                <asp:BoundField HeaderText="Order Confirmed On" DataFormatString="{0:dd-MMMM-yyyy}" DataField="order_confirmed_on" SortExpression="order_confirmed_on" />
                <asp:BoundField HeaderText="Delivered Time" DataFormatString="{0:dd-MMMM-yyyy}" DataField="quor_DeliveredTime" SortExpression="quor_DeliveredTime" />
                <asp:BoundField HeaderText="Delivery Confirmation Time" DataFormatString="{0:dd-MMMM-yyyy}" DataField="quor_DeliveryConfTime" SortExpression="quor_DeliveryConfTime" />
                <asp:BoundField HeaderText="Payment Code" DataField="paymentcode" SortExpression="paymentcode" />
                <asp:BoundField HeaderText="Admin Description" DataField="admin_description" SortExpression="admin_description" />
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
    
        
          
  <asp:SqlDataSource ID="SDSSettlementDownload" runat="server" ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSSettlementDownload_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="getSettlementData" SelectCommandType="StoredProcedure">
      <SelectParameters>
          <asp:ControlParameter Name="fromDate" ControlID="txtFromDate" ConvertEmptyStringToNull="false" />
          <asp:ControlParameter Name="toDate" ControlID="txtToDate" ConvertEmptyStringToNull="false" />
      </SelectParameters>
</asp:SqlDataSource>

</asp:Content>
   
   