<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="SupportTicket.aspx.cs" Inherits="RetalineProAgent.Manage.SupportTicket" %>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

        <div class="card">
        <asp:PlaceHolder ID="plcStoreList" runat="server">
            <div class="card-header shadow_top">
                <div class="row row-sm mt-2">
                    <div class="col-12 col-lg-9">
                        <h6 class="mb-1 tx-dark">Support Tickets</h6>
                        <p class="mg-b-0">Support tickets created.</p>
                    </div>
                </div>
                
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">


                  <asp:GridView ID="gvTickets" AllowPaging="true" PageSize="30" AllowSorting="true" runat="server" CssClass="table table-bordered table-hover" DataSourceID="SDSTickets"
                      AutoGenerateColumns="false">
                      <Columns>
                          <asp:BoundField DataField="ticketNumber" HeaderText="ticketNumber" />
                          <asp:BoundField DataField="supportdate" HeaderText="supportdate" />
                          <asp:BoundField DataField="statusname" HeaderText="Status" />
                          <asp:BoundField DataField="stage" HeaderText="Stage" />
                          <asp:BoundField DataField="typeName" HeaderText="Type" />

<%--                          <asp:TemplateField HeaderText="Status">
                              <ItemTemplate><%# Eval("Status") %></ItemTemplate>
                              <EditItemTemplate>
                                  <asp:DropDownList ID="selStatus" runat="server" SelectedValue='<%# Bind("Status") %>'>
                                      <asp:ListItem Text="Select Status" Value=""></asp:ListItem>
                                      <asp:ListItem Text="Completed" Value="1"></asp:ListItem>
                                      <asp:ListItem Text="SSL Pending" Value="3"></asp:ListItem>
                                      <asp:ListItem Text="In Progress" Value="2"></asp:ListItem>
                                      <asp:ListItem Text="DNS pending" Value="0"></asp:ListItem>
                                  </asp:DropDownList>
                              </EditItemTemplate>
                          </asp:TemplateField>--%>
                          <asp:BoundField DataField="ticketDescription" HeaderText="Description" />
                      </Columns>
                  </asp:GridView>

          </div><!-- table-responsive -->
        </div><!-- card-body -->
            
        </asp:PlaceHolder>
    </div><!-- card -->

                  <asp:SqlDataSource ID="SDSTickets" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  ProviderName="MySql.Data.MySqlClient"
                      SelectCommand="SELECT DATE_FORMAT(t.createdOn, '%d %b %Y') AS supportdate,t.ticketNumber,t.ticketTitle,t.ticketDescription, s.name AS statusname, st.name AS stage, stp.typeName
FROM support_ticket t LEFT JOIN support_ticket_stages st ON st.id=t.ticketStage LEFT JOIN  support_ticket_status s ON s.Id=t.ticketStatus LEFT JOIN support_type stp ON stp.typeId=t.ticketSupTypeId WHERE ticketSuId IN(13, 12) ORDER BY t.createdOn DESC"
                      ></asp:SqlDataSource>




</asp:Content>