<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Vehicle History" AutoEventWireup="true" CodeBehind="VehicleHistory.aspx.cs" Inherits="RetalineProAgent.VehicleHistory" %>

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
                   <%-- <a href="/DeliveryStaffCreate" type="button" class="btn btn-info">
    <i class="fa fa-plus"></i>Create Delivery Rules</a><br />--%>
                    </div>
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvVehicleHistory" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10"> 
                                    <%--OnDataBound="gvLiveVehicles_DataBound" DataSourceID="SDSLiveVehicles">--%>
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="vehno" DataNavigateUrlFields="vehno" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Vehicle No." ItemStyle-Width = "150" SortExpression="vehno" />
                                        <asp:BoundField HeaderText="Driver" DataField="drivername" SortExpression="drivername" />
                                        <asp:BoundField HeaderText="Mobile" DataField="mobno" SortExpression="mobno" />
                                        <asp:BoundField HeaderText="Login Time" DataField="logintime" SortExpression="logintime" />
                                        <asp:BoundField HeaderText="Assigned Weight" DataField="assgwt" SortExpression="assgwt" />
                                        <asp:BoundField HeaderText="Current Weight" DataField="currwt" SortExpression="currwt" />
                                        <asp:BoundField HeaderText="Assigned Volume" DataField="assgvol" SortExpression="assgvol" />
                                        <asp:BoundField HeaderText="Current Volume" DataField="currvol" SortExpression="currvol" />
                                        <asp:BoundField HeaderText="KM. Covered" DataField="kmcovered" SortExpression="kmcovered" />
                                        <asp:BoundField HeaderText="Total Jobs" DataField="totjobs" SortExpression="totjobs" />
                                        <asp:BoundField HeaderText="Jobs Completed" DataField="jobscompleted" SortExpression="jobscompleted" />
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <tb data-bootstrap-switch><asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" AutoPostBack="true" runat="server" itemid='<%# Eval("id") %>' Checked='<%# Eval("status").Equals("Active") %>'/></tb>
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                    </Columns>
                                </asp:GridView>

                                <%--<asp:SqlDataSource runat="server" ID="SDSLiveVehicles" ProviderName="MySql.Data.MySqlClient"
                                 SelectCommand = "SELECT quor_RefNo AS booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') AS booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,
quor_PickupLocation AS source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,
quor_DeliveryLocation AS destination,
IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','')) AS drivetype,
quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,
dls_DelStatus ,quor_Status,
CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,
DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') AS quor_ScheduleOpeningTime 
FROM  qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status 
WHERE   quor_slot_id = 0 AND quor_Pickupbr_id =@branchid"
OnSelecting="SDSLiveVehicles_Selecting">
        <SelectParameters>
            <asp:Parameter Name="branchid" />
            <asp:ControlParameter Name="search" ControlID="txtSearch" Type="String" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>--%>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
