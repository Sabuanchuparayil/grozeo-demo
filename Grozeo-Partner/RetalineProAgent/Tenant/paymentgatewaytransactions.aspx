<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="paymentgatewaytransactions.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.paymentgatewaytransactions" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Order Payment Reports"></asp:Literal>       
    </h6>
        <p class="mb-0">Order Payment</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">       
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvdelivery" runat="server" CssClass="table table-bordered gridview_table" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" DataSourceID="ODSordervaluehead">
                                    <Columns>
                                         <asp:BoundField HeaderText="Payment gateway ref Id" DataField="Paymentrefernceid" SortExpression="Paymentrefernceid"  />
                                         <asp:TemplateField HeaderText="Order ID." SortExpression="order_order_id"><ItemTemplate>
                                            <asp:Label ID="Label3" runat="server" Text='<%#Eval("order_order_id")  %>' ToolTip ='<%# Bind("order_order_id") %>'></asp:Label>
                                            <br /><small>Created On: <b><%# Eval("Orderdate") %></b></small></ItemTemplate></asp:TemplateField>                                        
                                        <asp:BoundField HeaderText="Payment Status" DataField="Status" SortExpression="Status"  />                                                                             
                                             <asp:TemplateField HeaderText="Order Status" SortExpression="namestatus"><ItemTemplate>
                                            <asp:Label ID="Label2" runat="server" Text='<%#Eval("namestatus")  %>' ToolTip ='<%# Bind("namestatus") %>'></asp:Label>
                                            <br /><small>Status: <b><%# Eval("Orderstatus") %></b></small></ItemTemplate></asp:TemplateField>  
                                        <asp:BoundField HeaderText="Payment Amount" DataField="Amount" SortExpression="Amount"  />                                                                             
                                        <asp:BoundField HeaderText="Payment OrderId" DataField="orderid" SortExpression="orderid"  />                                                                                                                                                              
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                                </asp:GridView>    
                 <asp:ObjectDataSource runat="server" ID="ODSordervaluehead" TypeName="RetalineProAgent.Tenant.paymentgatewaytransactions" SelectMethod="LoadOrderValues" >                               
                           </asp:ObjectDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>