<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Advertisement" AutoEventWireup="true" CodeBehind="Advertisement.aspx.cs" Inherits="RetalineProAgent.Advertisement" %>

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
                    &nbsp;<asp:TextBox ID="txtSearchAdvt" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
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
                    <a href="/DeliveryStaffCreate" type="button" class="btn btn-info">
    <i class="fa fa-plus"></i>Create Advertisement</a><br />
                    </div>
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvAdvertisement" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvAdvertisement_DataBound" DataSourceID="SDSAdvertisement">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="adv_title" DataNavigateUrlFields="adv_title" DataNavigateUrlFormatString="~/OrderPackingDetails.aspx?id={0}"
            HeaderText="Title" ItemStyle-Width = "150" SortExpression="adv_title" />
                                        <asp:BoundField HeaderText="Adzone Name" DataField="adzone_name" SortExpression="adzone_name" />
                                        <%--<asp:HyperLinkField runat="server" ItemStyle-CssClass="btn btn-primary" Text="Edit" ItemStyle-BackColor="Silver" NavigateUrl="~/DeliveryStaffCreate" DataNavigateUrlFields="d_ID" DataNavigateUrlFormatString="~/DeliveryBoySettings?id={0}" />--%>
                                    </Columns>
                                </asp:GridView>

                 <asp:SqlDataSource runat="server" ID="SDSAdvertisement" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                   SelectCommand = "SELECT adv_id,adv_title,adv_status,adzone_name,aa.adzone_id AS ad_id FROM 
                     app_advertisements aa INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id AND adv_status <> 2">
                 </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
