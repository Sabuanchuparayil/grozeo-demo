<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Delivery Jobs" AutoEventWireup="true" CodeBehind="DeliveryJobs.aspx.cs" Inherits="RetalineProAgent.DeliveryJobs" %>

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
                    &nbsp;<asp:TextBox ID="txtDeliJobs" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
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
                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliveryJobs" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryJobs_DataBound" DataSourceID="SDSDeliveryJobs">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="booking_no" DataNavigateUrlFields="booking_no" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Order No." ItemStyle-Width = "150" SortExpression="booking_no" />
                                        <asp:BoundField HeaderText="Order Date" DataField="orgOrderDate" SortExpression="orgOrderDate" />
                                        <asp:BoundField HeaderText="Created Date" DataField="booked_at" SortExpression="booked_at" />
                                        <asp:BoundField HeaderText="Type" DataField="quor_TypeName" SortExpression="quor_TypeName" />
                                        <asp:BoundField HeaderText="Status" DataField="dls_DelStatus" SortExpression="dls_DelStatus" />
                                        <asp:BoundField HeaderText="Customer" DataField="quor_DeliveryName" SortExpression="quor_DeliveryName" />
                                        <asp:BoundField HeaderText="Customer Contact" DataField="quor_DeliveryPhone" SortExpression="quor_DeliveryPhone" />
                                        <asp:BoundField HeaderText="Contact NO" DataField="quor_PickupPhone" SortExpression="quor_PickupPhone" />
                                        <%--<asp:TemplateField>
                                            <ItemTemplate>
                                                <tb data-bootstrap-switch><asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" AutoPostBack="true" runat="server" itemid='<%# Eval("id") %>' Checked='<%# Eval("status").Equals("Active") %>'/></tb>
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSDeliveryJobs" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT quor_RefNo AS booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') AS booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,
quor_PickupLocation AS source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,
quor_DeliveryLocation AS destination,
IF(quor_Status=22,'PICKUP', IF(quor_Status=31,'DELIVERY','')) AS drivetype,
quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,
dls_DelStatus ,quor_Status,
CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,
DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') AS quor_ScheduleOpeningTime 
FROM  qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status 
INNER JOIN finascop_branch b ON b.br_ID=quor_Pickupbr_id WHERE b.br_storeGroup = @storegroupid AND quor_slot_id = 0"
OnSelecting="SDSDeliveryJobs_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter Name="search" ControlID="txtDeliJobs" Type="String" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
