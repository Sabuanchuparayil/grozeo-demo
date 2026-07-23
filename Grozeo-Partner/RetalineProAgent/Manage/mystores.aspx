<%@ Page Language="C#" AutoEventWireup="true" Title="Stores" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="mystores.aspx.cs" Inherits="RetalineProAgent.mystores" %>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">



          <!-- Default box -->
      <div class="card">
        <div class="card-body p-0">
          
            <asp:ListView ID="lstStores" runat="server" DataSourceID="SDSStores" ItemPlaceholderID="PlaceHolder1" DataKeyNames="Id">
                <LayoutTemplate>
				<table  class="table table-striped projects">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
											<th>Theme</th>
											<th>Status</th>
											<th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="PlaceHolder1" runat="server"></asp:PlaceHolder>
				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>
                    <tr>
						<td style="max-width: 20%;"><%# Eval("Name")%> <%# (String.IsNullOrEmpty(Eval("StoreId").ToString())?"":String.Format("(Store id: {0})", Eval("StoreId"))) %><br />
                           <p style="font-size: 9px;"> <%# (String.IsNullOrEmpty(Eval("hosts").ToString())?"":Eval("hosts").ToString().Replace(",", ", <br/>") )%></p></td>
						<td><%# Eval("Theme")%> <br />
                           <p style="font-size: 9px;">Min Margin: <%# Eval("MinMargin")%></p>
						</td>
						<td> <asp:CheckBox runat="server" Enabled="false" Text="Active" AutoPostBack="True" ID="chkStatus" fieldname="Status" rowId='<%#Eval("Id")%>' Checked='<%# Eval("Status").ToString() == "1" %>' />
                            &nbsp;<asp:CheckBox runat="server" Enabled="false" Text="PWA" AutoPostBack="True" rowId='<%#Eval("Id")%>' fieldname="ShowPWA" Checked='<%# Eval("ShowPWA")%>' /><br />
                            <asp:CheckBox runat="server" Enabled="false" Text="Checkout" AutoPostBack="True" rowId='<%#Eval("Id")%>' fieldname="CanCheckout" Checked='<%# Eval("CanCheckout")%>' />
                            &nbsp;<asp:CheckBox runat="server" Enabled="false" Text="Online Payment" AutoPostBack="True" rowId='<%#Eval("Id")%>' fieldname="OnlinePaymentEnabled" Checked='<%# Eval("OnlinePaymentEnabled")%>' />

						</td><td>
                            <%--<asp:HyperLink runat="server" Visible='<%# (Eval("Name") == "Cart" ? false : true) %>' ID="lnkEdit" CssClass="btn btn-primary btn-sm" Text="Edit" NavigateUrl='<%# String.Format("/managestore?sid={0}", Eval("Id")) %>' rowId='<%#Eval("Id")%>'>
                                <i class="fa fa-pencil-alt">
                              </i>
                              View
                            </asp:HyperLink>--%>

                        </td>
					</tr>
                </ItemTemplate>
            </asp:ListView>

            <asp:SqlDataSource ID="SDSStores" runat="server" OnSelecting="SDSStores_Selecting" ConnectionString="<%$ ConnectionStrings:conn %>"
        SelectCommand="SELECT a.*, Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts 
,s.MinMargin, s.DBConnectionString, s.SelectSql, s.APICode, s.GroupId, s.BusinessType, s.Package, s.Id as tStoreId
FROM AppTenant a left join Store s on s.TenantId=a.Id WHERE @usertype = 0 OR isnull(a.Id, -1) in (SELECT m.StoreGroupId FROM User_UserRole_Mapping m INNER JOIN [User] u on u.Id=m.UserId WHERE u.Email like @user)" 
            >
		<SelectParameters>
            <asp:Parameter Name="usertype" Type="Int32" ConvertEmptyStringToNull="false" DefaultValue="-1" />
            <asp:Parameter Name="user" DefaultValue="" />
        </SelectParameters>
    </asp:SqlDataSource>

        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->

</asp:Content>