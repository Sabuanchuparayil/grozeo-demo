<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="View Order Details" AutoEventWireup="true" CodeBehind="ViewOrderDetails.aspx.cs" Inherits="RetalineProAgent.ViewOrderDetails" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/PendingOrders">Sales</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Order Details</li>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
          <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                  <div class="float-right">

                      <div class="card-tools">
                <div class="input-group input-group-sm">
                    <%--&nbsp;
                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search"></asp:TextBox> 
                    <asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>--%>
                    
<div class="float-right ml-3 tx-dark">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                              <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm page-link">
                      <i class="fa fa-angle-left"></i>
                      </asp:LinkButton>
                              <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm page-link">
                          <i class="fa fa-angle-right"></i>
                      </asp:LinkButton>
                  </div>
                  <!-- /.btn-group -->
                        </div>
                    
                </div>
                  
              </div> 
                </div><br />
                    
                    </div>
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvViewOrdDetails" runat="server" CssClass="table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvViewOrdDetails_DataBound" DataSourceID="SDSViewOrdDetails">
                                    <Columns>
                                        <asp:TemplateField HeaderText = "Sl NO." ItemStyle-Width="100" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Item Name" DataField="stitSKU" SortExpression="stitSKU" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Quantity" DataField="bcod_Count" SortExpression="bcod_Count" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Scanned Qty" DataField="bcod_scannedcount" SortExpression="bcod_scannedcount" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSViewOrdDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fstod_id AS bcod_id,fsto_id AS bcor_id,fsto_ItemId AS stit_ID,fsto_ItemQty AS bcod_Count,
                                                  (SELECT stit_SKU FROM finascop_stock_itemmaster im WHERE im.stit_ID = oi.fsto_ItemId) AS stitSKU,
                                                  fsto_pkdQty AS bcod_scannedcount FROM finascop_stock_transfer_order_details oi WHERE fsto_id=@fsto_id"
       OnSelecting="SDSViewOrdDetails_Selecting">
        <SelectParameters>
            <asp:Parameter Name="fsto_id" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
