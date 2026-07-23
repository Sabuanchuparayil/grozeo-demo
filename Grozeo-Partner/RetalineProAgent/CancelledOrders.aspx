<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Cancelled Orders" AutoEventWireup="true" CodeBehind="CancelledOrders.aspx.cs" Inherits="RetalineProAgent.CancelledOrders" %>

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
                    &nbsp;<asp:TextBox ID="txtSearchCOrders" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
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
                    <a href="/Tenant/DeliveryStaffCreate" type="button" class="btn btn-info">
    <i class="fa fa-plus"></i>Create Order To Cancel</a><br />
                    </div>
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvCancelledOrders" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvCancelledOrders_DataBound" DataSourceID="SDSCancelledOrder">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="order_order_id" DataNavigateUrlFields="order_order_id" DataNavigateUrlFormatString="~/OnlineOrderDetailsView.aspx?id={0}"
            HeaderText="Order ID" ItemStyle-Width = "150" SortExpression="order_order_id" />
                                        <asp:BoundField HeaderText="Branch" DataField="br_Name" SortExpression="br_Name" />
                                        <asp:BoundField HeaderText="Date" DataField="order_created_on" SortExpression="order_created_on" />
                                        <asp:BoundField HeaderText="Time" DataField="ordertime" SortExpression="ordertime" />
                                        <asp:BoundField HeaderText="Delivery To" DataField="delivery_to" SortExpression="delivery_to" />
                                        <asp:BoundField HeaderText="Reason" DataField="reason" SortExpression="reason" />
                                        <asp:BoundField HeaderText="Cancelled Type" DataField="cancelled_by_type_name" SortExpression="cancelled_by_type_name" />
                                        <asp:BoundField HeaderText="Status" DataField="order_status" SortExpression="order_status" />
                                        <asp:BoundField HeaderText="Cancelled By" DataField="cancelled_by_name" SortExpression="cancelled_by_name" />
                                        <%--<asp:HyperLinkField runat="server" ItemStyle-CssClass="btn btn-primary" Text="Edit" ItemStyle-BackColor="Silver" NavigateUrl="~/DeliveryStaffCreate" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/DeliveryBoySettings?id={0}" />--%>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSCancelledOrder" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,br_Name,
                                                bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,
                                                admin_description AS order_status,(SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,
                                                (SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,order_HasReturn,order_ItemsReturned,
                                                order_ReturnVerified FROM retaline_customer_order bco INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id
                                                INNER JOIN finascop_branch b ON b.br_ID=bco.cust_branch_id WHERE b.br_storeGroup = @storegroupid AND bco.status_id > 0 AND bco.status_id =19 "
        OnSelecting="SDSCancelledOrder_Selecting">
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
