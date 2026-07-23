<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Spot Return" AutoEventWireup="true" CodeBehind="SpotReturn.aspx.cs" Inherits="RetalineProAgent.SpotReturn" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/PendingOrders">Packing & Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Spot Return</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
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
                    &nbsp;<asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
                    <asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>
                    &nbsp;
<div class="float-right">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fa fa-chevron-right"></i>
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
                                <asp:GridView AutoGenerateColumns="false" ID="gvSpotReturn" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvSpotReturn_DataBound" DataSourceID="SDSSpotReturn">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="order_order_id" DataNavigateUrlFields="order_order_id" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Order ID" ItemStyle-Width = "150" SortExpression="order_order_id" />
                                        <asp:BoundField HeaderText="Date" DataField="order_created_on" SortExpression="order_created_on" />
                                        <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" />
                                        <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
                                        <asp:BoundField HeaderText="Order Status" DataField="order_status" SortExpression="order_status" />
                                        <asp:BoundField HeaderText="Actions" />
                                    </Columns><EmptyDataTemplate>No record available</EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSSpotReturn" ProviderName="MySql.Data.MySqlClient" OnSelected="SDSSpotReturn_Selected" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,br_Name,bco.status_id AS STATUS,
DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(CAST(created_at AS TIME),'%r') AS ordertime,
admin_description AS order_status,(SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,
(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,order_HasReturn,order_ItemsReturned,
order_ReturnVerified FROM retaline_customer_order bco INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
INNER JOIN finascop_branch b ON b.br_ID=bco.order_branch_id WHERE b.br_storeGroup = @storegroupid AND order_HasReturn = 1 AND bco.status_id > 0"
                                    OnSelecting="SDSSpotReturn_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
