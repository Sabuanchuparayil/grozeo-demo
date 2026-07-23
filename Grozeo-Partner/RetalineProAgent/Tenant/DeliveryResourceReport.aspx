<%@ Page Language="C#" AutoEventWireup="true" Title="Delivery Resource Report" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="DeliveryResourceReport.aspx.cs" Inherits="RetalineProAgent.DeliveryResourceReport" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Sales Report</h1>
          </div>
    <script></script>
    
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

                                <asp:GridView AutoGenerateColumns="false" ID="gvDeliveryResourceReport" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDeliveryResourceReport_DataBound" DataSourceID="SDSDeliveryResourceReport">
                                    <Columns>
                                        <asp:BoundField HeaderText="Driver" DataField="d_name" SortExpression="d_name" />
                                        <asp:BoundField HeaderText="Address" DataField="address" SortExpression="address" />
                                        <asp:BoundField HeaderText="Phone" DataField="d_Ph1" SortExpression="d_Ph1" />
                                        <asp:BoundField HeaderText="Auto Schedule" DataField="d_isallowAutoSchedule" SortExpression="d_isallowAutoSchedule" />
                                        <asp:BoundField HeaderText="Manual Schedule" DataField="d_isallowManualSchedule" SortExpression="d_isallowManualSchedule" />
                                        <asp:BoundField HeaderText="Branch" DataField="branch" SortExpression="branch" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSDeliveryResourceReport" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT d_ID, d_Name,CONCAT_WS(',',d_Add1,d_Add2,d_Add3) AS address,
d_Ph1,(SELECT br_Name FROM finascop_branch WHERE br_id=qugeo_driver.br_id) 
AS branch,IF((d_isallowAutoSchedule=1),'Yes','No')  AS d_isallowAutoSchedule,  
IF((d_isallowManualSchedule=1),'Yes','No')  AS d_isallowManualSchedule FROM qugeo_driver INNER JOIN finascop_branch b ON b.br_ID=br_id WHERE b.br_storeGroup = @storegroupid"
        OnSelecting="SDSDeliveryResourceReport_Selecting">
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



