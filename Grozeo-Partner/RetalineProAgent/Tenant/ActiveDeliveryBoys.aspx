<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Active Delivery Boys" AutoEventWireup="true" CodeBehind="ActiveDeliveryBoys.aspx.cs" Inherits="RetalineProAgent.ActiveDeliveryBoys" %>

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
                    &nbsp;<asp:TextBox ID="txtSearchDeliBoy" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
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
                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliverBoy" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryBoy_DataBound" DataSourceID="SDSDeliveryBoy">
                                    <Columns>
                                        <asp:BoundField HeaderText="Driver" DataField="d_Name" SortExpression="d_Name" />
                                        <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address" />
                                        <asp:BoundField HeaderText="Phone" DataField="d_Ph1" SortExpression="d_Ph1" />
                                        <%--<asp:BoundField HeaderText="Auto Schedule" DataField="d_isallowAutoSchedule" SortExpression="d_isallowAutoSchedule" />
                                        <asp:BoundField HeaderText="Manual Schedule" DataField="d_isallowManualSchedule" SortExpression="d_isallowManualSchedule" />--%>
                                        <asp:BoundField HeaderText="Branch" DataField="branch" SortExpression="branch" />
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <asp:Button runat="server" ID="btnAdd" OnClick="btnAssign_Click" CssClass="btn btn-success float-right" Text="Assign" ValidationGroup="AddStore"/>&nbsp;
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        
                                        <%--<asp:HyperLinkField runat="server" ItemStyle-CssClass="btn btn-primary" Text="Assign" ItemStyle-BackColor="Silver" NavigateUrl="~/DeliveryStaffSettings" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/DeliveryBoySettings?id={0}" />--%>
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSDeliveryBoy" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT d_ID, d_Name,CONCAT_WS(',',d_Add1,d_Add2,d_Add3) AS address, d_Ph1,(SELECT br_Name FROM finascop_branch WHERE 
br_id=qd.br_id) AS branch,IF((d_isallowAutoSchedule=1),'Yes','No')  AS d_isallowAutoSchedule,  
IF((d_isallowManualSchedule=1),'Yes','No')  AS d_isallowManualSchedule  FROM qugeo_driver qd INNER JOIN finascop_branch b ON b.br_ID=qd.br_id WHERE b.br_storeGroup = @storegroupid"
        OnSelecting="SDSDeliveryBoy_Selecting">
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
