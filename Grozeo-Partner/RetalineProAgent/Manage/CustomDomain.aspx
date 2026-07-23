<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="CustomDomain.aspx.cs" Inherits="RetalineProAgent.Manage.CustomDomain" %>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

        <div class="card">
        <asp:PlaceHolder ID="plcStoreList" runat="server">
            <div class="card-header shadow_top">
                <div class="row row-sm mt-2">
                    <div class="col-12 col-lg-9">
                        <h6 class="mb-1 tx-dark">Custom Domains</h6>
                        <p class="mg-b-0">Custom domains added by tenants with status.</p>
                    </div>
                </div>
                
<div class="row">
  <div class="col-3 col-sm-3">DNS Settings - IP*: <asp:TextBox ID="txtIP" runat="server" CssClass="form-control" ValidationGroup="DNSSettings"></asp:TextBox><asp:RequiredFieldValidator ValidationGroup="DNSSettings" runat="server" ControlToValidate="txtIP" ErrorMessage="IP record is required" Text="*" Display="Dynamic" SetFocusOnError="true"></asp:RequiredFieldValidator> 
  </div>
  <div class="col-3 col-sm-3">
      TXT*: <asp:TextBox ID="txtTXTRecord" runat="server" CssClass="form-control" ValidationGroup="DNSSettings"></asp:TextBox><asp:RequiredFieldValidator runat="server" ValidationGroup="DNSSettings" ControlToValidate="txtTXTRecord" ErrorMessage="TXT record is required" Text="*" Display="Dynamic" SetFocusOnError="true"></asp:RequiredFieldValidator>
  </div>
    <div class="col-lg-2 d-flex align-items-start justify-content-lg-end mt-3">
        <asp:LinkButton runat="server" OnClick="btnUpdateDNSSettings_Click" ID="btnUpdateDNSSettings" CssClass="btn px-4 d-block d-md-inline-block btn-primary" ValidationGroup="DNSSettings"> Edit<i class="icon ion-plus-circled ml-2"></i></asp:LinkButton>       
    </div>
</div>

        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">


                  <asp:GridView ID="gvDomains" AllowPaging="true" PageSize="30" AllowSorting="true" runat="server" CssClass="table table-bordered table-hover" DataSourceID="SDSCustomDomain"
                      AutoGenerateColumns="false" DataKeyNames="Id">
                      <Columns>
                          <asp:TemplateField HeaderText="Domain">
                              <ItemTemplate>
                                  <small>Store: <b><%# Eval("tenantName") %> (<%# Eval("tenantId") %>)</b>, <br />url: <b><%# Eval("url") %></b> </small>
                              </ItemTemplate>
                          </asp:TemplateField>
                          <asp:TemplateField HeaderText="Status">
                              <ItemTemplate><%# GetStatusText(Eval("Status")) %><br /><small>Created On: <b><%# Eval("CreatedOn") %></b></small></ItemTemplate>
                              <EditItemTemplate>
                                  <asp:DropDownList ID="selStatus" runat="server" SelectedValue='<%# Bind("Status") %>'>
                                      <asp:ListItem Text="Select Status" Value=""></asp:ListItem>
                                      <asp:ListItem Text="Completed" Value="1"></asp:ListItem>
                                      <asp:ListItem Text="SSL Pending" Value="3"></asp:ListItem>
                                      <asp:ListItem Text="In Progress" Value="2"></asp:ListItem>
                                      <asp:ListItem Text="DNS pending" Value="0"></asp:ListItem>
                                  </asp:DropDownList>
                                  <br /><small>Created On: <b><%# Eval("CreatedOn") %></b></small>
                              </EditItemTemplate>
                          </asp:TemplateField>
                          <asp:TemplateField HeaderText="DNS">
                              <ItemTemplate>
                                  Domain: <b><%# Eval("Domain") %></b>, IP: <%# Eval("AllocatedIP") %><br />TXT: <%# Eval("TXTRecord") %>
                              </ItemTemplate>
                          </asp:TemplateField>
                          <asp:CommandField EditText="Edit" HeaderText="Action" ShowEditButton="true" />
                      </Columns>
                  </asp:GridView>

          </div><!-- table-responsive -->
        </div><!-- card-body -->
            
        </asp:PlaceHolder>
    </div><!-- card -->

                  <asp:SqlDataSource ID="SDSCustomDomain" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>" 
                      SelectCommand="select d.*, t.Name as tenantName, t.Id as tenantId, (select top 1 HostAddress from Host where TenantId=t.Id) as url from CustomDomain d inner join AppTenant t on d.Tenantid=t.Id"
                      UpdateCommand="UPDATE CustomDomain SET [Status]=@Status WHERE Id=@Id; 
                      INSERT INTO Host(TenantId, StoreId, HostAddress, [Status]) select top 1 TenantId, (select top 1 Id from Store where TenantId= c.TenantId), Domain, 1 from CustomDomain c 
                      where c.Id=@Id and @status in(1, 3) and not exists(select * from Host where HostAddress= c.Domain);"
                      ><UpdateParameters><asp:Parameter Name="Status" /></UpdateParameters>
                  </asp:SqlDataSource>


</asp:Content>