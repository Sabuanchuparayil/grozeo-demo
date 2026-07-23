<%@ Page Language="C#" AutoEventWireup="true" Title="Latest Orders" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="LatestOrders.aspx.cs" Inherits="RetalineProAgent.LatestOrders" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Latest Orders</h1>
          </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card-header">
              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
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
              <br /><br />
            </div>
        <div class="card-body">

               <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvLatestOrders" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvLatestOrders_DataBound" DataSourceID="SDSLatestOrders">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="fsto_uid" DataNavigateUrlFields="fsto_uid" DataNavigateUrlFormatString="~/Tenant/RecentOrders.aspx?id={0}"
            HeaderText="TO No." ItemStyle-Width = "150" SortExpression="fsto_uid" />
                                        <asp:BoundField HeaderText="Consigner" DataField="fsto_sourceName" SortExpression="fsto_sourceName" />
                                        <asp:BoundField HeaderText="Consignee" DataField="fsto_destinationName" SortExpression="fsto_destinationName" />
                                        <asp:BoundField HeaderText="Date" DataField="fstoCreatedOn" SortExpression="fstoCreatedOn" />
                                        <asp:BoundField HeaderText="Status" DataField="fsto_statusName" SortExpression="fsto_statusName" />
                                        <asp:TemplateField>
                                            <ItemTemplate>

                                                <div class="btn-group">
                    <button type="button" class="btn btn-default">Action</button>
                    <button type="button" class="btn btn-default dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">

                                                <asp:HyperLink runat="server" CssClass="dropdown-item" Visible='<%# (
                                                        Convert.ToInt32(Eval("fsto_status")) == 6  
                                                            ? true: false) %>' NavigateUrl='<%# string.Format("~/Tenant/AssignOrderPicker.aspx?orderid={0}", Eval("fsto_uid")) %>' Text="Assign Order Picker"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="dropdown-item" Visible='<%# (
                                                        Convert.ToInt32(Eval("fsto_status")) == 6  
                                                            ? true: false) %>' NavigateUrl='<%# string.Format("~/Tenant/ManualPacking.aspx?fsto_id={0}", Eval("fsto_id")) %>' Text="Manual Packing"></asp:HyperLink>
                                                <asp:HyperLink runat="server" CssClass="dropdown-item" Visible='<%# (
                                                        Convert.ToInt32(Eval("fsto_status")) == 2 
                                                         ? true: false) %>' NavigateUrl='<%# String.Format("/Tenant/ActiveDeliveryBoys.aspx?orderid={0}", Eval("fsto_uid")) %>' Text="Assign Delivery Staff"></asp:HyperLink>
                                                <asp:HyperLink runat="server"  CssClass="dropdown-item" Visible='<%# (
                                                        Convert.ToInt32(Eval("fsto_status")) == 10 
                                                            ? true: false) %>' Text="Packing Completed"></asp:HyperLink>
                    </div>
                  </div>
                        <asp:Image runat="server" ID="image" ImageAlign="AbsMiddle" width="30" ImageUrl="https://grozeo.azurewebsites.net/images/processing.gif" 
                            Visible='<%# ( (new int[]{5, 11 }).Contains(Convert.ToInt32(Eval("fsto_status")))
                                    ? true: false) %>' />

                                                <%--<asp:HyperLink runat="server" Enabled='<%# (Eval("status_id").Equals(24) ? true: false) %>' Visible='<%# (Eval("status_id").Equals(24) ? true: false) %>' NavigateUrl='<%# String.Format("#?orderid={0}", Eval("order_order_id")) %>' Text="Cancel Order"></asp:HyperLink>--%>
                                                <%--<div class="btn-group">
                    <button type="button" class="btn btn-info">Action</button>
                    <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu " role="menu">
                      <a class="dropdown-item" href="/AssignOrderPicker">Assign Order Picker</a>
                        <a class="dropdown-item" href="/ManualPacking">Manual Packing</a>
                      <a class="dropdown-item" href="/ActiveDeliveryBoys">Assign Delivery Boy</a>--%>
                      <%--<div class="dropdown-divider"></div>--%>
                      <%--<a class="dropdown-item" href="#">Separated link</a>--%>
                    <%--</div>--%>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <%--Enabled='<%# (Eval("status_id").Equals(9) ? true: false) %>' --%>
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <asp:Button ID="btn3" runat="server" OnClick="btn_click3" Text="Manual Packing" />
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <asp:Button ID="btn2" runat="server" OnClick="btn_click2" Text="Delivery Boy" />
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSLatestOrders" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fo.fsto_id AS fsto_id,fo.fsto_uid AS fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) AS fsto_ItemWeight,
(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) AS fsto_ItemVolume,fsto_sourcetype,fsto_destination,fsto_destinationtype,
fsto_isalreadypacked,IF(fsto_ordertype = 1,(SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id),0) AS order_branch_type_id,
CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'BR TO CPD' END AS fsto_ordertype,
CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,
(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) AS fsto_statusName,fsto_status,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') AS fstoCreatedOn,fsto_createdOn,
CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) 
WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) 
WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = fsto_destination) 
WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) END AS fsto_destinationName,
CASE WHEN fsto_ordertype = 1 AND (SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id)= 2 AND 
(SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source) > 0 THEN (SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source)  
WHEN fsto_ordertype = 1 AND (SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id)= 2 AND 
(SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source) = 0 THEN 1 ELSE fsto_source END AS fstosSource,fsto_source,
(SELECT br_Name FROM finascop_branch WHERE br_ID = fstosSource) AS fsto_sourceName FROM finascop_stock_transfer_order  fo INNER JOIN finascop_branch b ON b.br_ID=fo.fsto_source WHERE b.br_storeGroup = @storegroupid 
order by fsto_createdOn desc"
OnSelecting="SDSLatestOrders_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
            
</asp:Content>




