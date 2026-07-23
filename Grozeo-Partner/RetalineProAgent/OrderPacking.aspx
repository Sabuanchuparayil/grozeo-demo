<%@ Page Language="C#" AutoEventWireup="true" Title="Order Packing" MasterPageFile="~/AgentMaster.Master" Async="true"  CodeBehind="OrderPacking.aspx.cs" Inherits="RetalineProAgent.OrderPacking" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Order Packing</h1>
          </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
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

                                <asp:GridView AutoGenerateColumns="false" ID="gvOrderPacking" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvOrderPacking_DataBound" DataSourceID="SDSOrderPacking">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="fsto_uid" DataNavigateUrlFields="fsto_uid" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="TO No." ItemStyle-Width = "150" SortExpression="fsto_uid" />
                                        <asp:BoundField HeaderText="Store" DataField="fsto_sourceName" SortExpression="fsto_sourceName" />
                                        <asp:BoundField HeaderText="Customer" DataField="fsto_destinationName" SortExpression="fsto_destinationName" />
                                        <asp:BoundField HeaderText="Date" DataField="fstoCreatedOn" SortExpression="fstoCreatedOn" />
                                        <asp:BoundField HeaderText="Weight" DataField="fsto_ItemWeight" SortExpression="fsto_ItemWeight" />
                                        <asp:BoundField HeaderText="Volume" DataField="fsto_ItemVolume" SortExpression="fsto_ItemVolume" />
                                        <asp:BoundField HeaderText="Type" DataField="fsto_ordertype" SortExpression="fsto_ordertype" />
                                        <asp:BoundField HeaderText="Status" DataField="fsto_statusName" SortExpression="fsto_statusName" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSOrderPacking" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="SELECT fo.fsto_id AS fsto_id,fo.fsto_uid AS fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) AS fsto_ItemWeight,
(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) AS fsto_ItemVolume,fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype,
(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_sourceName,fsto_isalreadypacked,
CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'BR TO CPD' END AS fsto_ordertype,
CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,
(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) AS fsto_statusName,fsto_status,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') AS fstoCreatedOn,fsto_createdOn,
CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) 
WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) 
WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = fsto_destination) 
WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) END AS fsto_destinationName 
FROM finascop_stock_transfer_order  fo INNER JOIN finascop_branch b ON b.br_ID=fo.fsto_source WHERE b.br_storeGroup = @storegroupid AND fsto_status NOT IN (9,11)"
        OnSelecting="SDSOrderPacking_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter Name="search" ControlID="txtSearch" Type="String" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
              </div>
            </div>
    </div>
</asp:Content>




