<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Scheduled Jobs" AutoEventWireup="true" CodeBehind="ScheduledJobs.aspx.cs" Inherits="RetalineProAgent.ScheduledJobs" %>

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
                                <asp:GridView AutoGenerateColumns="false" ID="gvScheduledJobs" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvScheduledJobs_DataBound" DataSourceID="SDSScheduledJobs">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="fsto_uid" DataNavigateUrlFields="fsto_uid" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="TO No." ItemStyle-Width = "150" SortExpression="fsto_uid" />
                                        <asp:BoundField HeaderText="Consigner" DataField="fsto_sourceName" SortExpression="fsto_sourceName" />
                                        <asp:BoundField HeaderText="Consignee" DataField="fsto_destinationName" SortExpression="fsto_destinationName" />
                                        <asp:BoundField HeaderText="Order Date" DataField="fstoCreatedOn" SortExpression="fstoCreatedOn" />
                                        <asp:BoundField HeaderText="Delivery Date" DataField="slotDate" SortExpression="slotDate" />
                                        <asp:BoundField HeaderText="Slot Time" DataField="slotTime" SortExpression="slotTime" />
                                        <asp:BoundField HeaderText="Type" DataField="fsto_ordertype" SortExpression="fsto_ordertype" />
                                        <asp:BoundField HeaderText="Status" DataField="fsto_statusName" SortExpression="fsto_statusName" />
                                        <%--<asp:BoundField HeaderText="Action" DataField="branch" SortExpression="branch" />--%>
                                        <%--<asp:HyperLinkField runat="server" ItemStyle-CssClass="btn btn-primary" Text="Edit" ItemStyle-BackColor="Silver" NavigateUrl="~/DeliveryStaffCreate" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/DeliveryBoySettings?id={0}" />--%>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSScheduledJobs" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fo.fsto_id AS fsto_id,fo.fsto_uid AS fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd 
WHERE fo.fsto_id= fd.fsto_id) AS fsto_ItemWeight,(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE 
fo.fsto_id= fd.fsto_id) AS fsto_ItemVolume,fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype,
(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_sourceName,fsto_isalreadypacked,
CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' 
WHEN fsto_ordertype=3 THEN 'BR TO CPD' END AS fsto_ordertype,
CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,
(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) AS fsto_statusName,fsto_status,
DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') AS fstoCreatedOn,fsto_createdOn,CASE WHEN fsto_ordertype = 0 THEN 
(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) 
WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) 
WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = fsto_destination) 
WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) END AS fsto_destinationName,
IF(fsto_ordertype = 1,(SELECT order_slot_date FROM retaline_customer_order WHERE order_id = fstr_id),'-') AS slotDate,
IF(fsto_ordertype = 1,(SELECT CONCAT(DATE_FORMAT(rbds_time_from,'%h:%i %p'),'-',DATE_FORMAT(rbds_time_to,'%h:%i %p')) 
FROM retaline_branch_delivery_slot WHERE rbds_id = (SELECT order_slot_id FROM retaline_customer_order WHERE order_id = fstr_id)),'-') AS slotTime  
FROM finascop_stock_transfer_order  fo WHERE fsto_status=11 AND fsto_source=@branchid"
                                    OnSelecting="SDSScheduledJobs_Selecting">
        <SelectParameters>
            <asp:Parameter Name="branchid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
