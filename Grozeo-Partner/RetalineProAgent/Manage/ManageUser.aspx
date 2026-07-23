<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="ManageUser.aspx.cs" Inherits="RetalineProAgent.Manage.ManageUser" %>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="container-fluid">

                <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                              <div class="row row-sm mt-2">

                <div class="col-12 col-sm-6 col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearchProduct" runat="server">Search by:</label>
                    <asp:TextBox ID="txtSearchUser" runat="server" autocomplete="off" CssClass="form-control" placeholder="User name"></asp:TextBox>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom" runat="server">Role Type:</label>
                    <asp:DropDownList ID="selRoleType" runat="server" CssClass="form-control select2" DataSourceID="SDSRoleType" DataTextField="TypeName" DataValueField="Id" AppendDataBoundItems="true" >
                        <asp:ListItem Text="All types" Value="-1"></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:conn %>" runat="server" ID="SDSRoleType" SelectCommand="select * from UserRoleType" ></asp:SqlDataSource>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 form-group mb-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateTo" runat="server">Role:</label>
                    <asp:DropDownList ID="selRole" runat="server" CssClass="form-control select2" DataSourceID="SDSRole" DataTextField="RoleName" DataValueField="Id" OnDataBound="selRole_DataBound">
                        <asp:ListItem Text="All roles" Value="-1"></asp:ListItem>
                    </asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:conn %>" runat="server" ID="SDSRole" SelectCommand="select * from UserRole where isnull(@roleId, 0) <= 1 or RoleType=@roleId" >
                        <SelectParameters><asp:ControlParameter Name="roleId" ControlID="selRoleType" DefaultValue="0" ConvertEmptyStringToNull="false" /></SelectParameters></asp:SqlDataSource>
                </div>
                <div class="col-4 col-sm-6 col-lg-2 d-flex align-items-end">
                    <label class="mb-0">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary" runat="server">Search</asp:LinkButton>
                    <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2" Text="Reset" />

                    </div>

            </div>
                                  <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:conn %>" runat="server" ID="SDSUserRoles" OnSelecting="SDSUserRoles_Selecting" 
                                      SelectCommand="select r.*, m.RoleId from UserRole r left join user_userRole_mapping m on m.RoleId=r.Id and m.UserId=@userId" >
    <SelectParameters><asp:Parameter Name="userId" /></SelectParameters></asp:SqlDataSource>

              </div>
                <label ID="lbRole2" runat="server"><%# Eval("RoleName") %></label>
              <!-- ./card-header -->
              <div class="card-body">
                  <asp:GridView ID="gvUsers" AllowPaging="true" PageSize="30" AllowSorting="true" DataKeyNames="Id" OnRowEditing="gvUsers_RowEditing" OnRowUpdating="gvUsers_RowUpdating" OnRowDataBound="gvUsers_RowDataBound" runat="server" CssClass="table table-bordered table-hover" DataSourceID="SDSUsers" AutoGenerateColumns="false">
                      <Columns>
                          <asp:TemplateField HeaderText="User">
                              <ItemTemplate>
                                  <%# Eval("UserName") %>
                                  <small>Email: <%# Eval("Email") %>, <br />Phone: <%# Eval("Mobile") %>
                                      <%# (Eval("Roles").ToString().Contains("Tenant: ")?$"Store: {Eval("StoreGroupName")}, Id: {Eval("StoreGroupId")}":"") %>
                                      <%# (Eval("Roles").ToString().Contains("Business: ")?$"Area Id: {Eval("AreaId")}":"") %>

                                  </small>
                              </ItemTemplate></asp:TemplateField>
                          <asp:TemplateField HeaderText="Roles">
                              <ItemTemplate><%# Eval("Roles") %></ItemTemplate>
                              <EditItemTemplate>
                                  <asp:Repeater ID="rptRoles" runat="server" OnItemDataBound="rptRoles_ItemDataBound" DataSourceID="SDSUserRoles">
                                      <ItemTemplate>
                                          <input ID="ckRole" type="checkbox" selId='<%# Eval("RoleId") %>' runat="server" value='<%# Eval("Id") %>' />
                                            <label ID="lbRole" runat="server"><%# Eval("RoleName") %></label>
                                            <br />
                                      </ItemTemplate>
                                  </asp:Repeater>
                                  <%--<asp:CheckBoxList ID="clRoles" runat="server" DataTextField="RoleName" DataValueField="Id" OnDataBound="clRoles_DataBound" >
                                  </asp:CheckBoxList>--%>
                              </EditItemTemplate>
                          </asp:TemplateField>
                          <asp:TemplateField>
                              <ItemTemplate>
                                  <asp:LinkButton runat="server" Text="Edit" CommandName="Edit"></asp:LinkButton>
                              </ItemTemplate>
                              <EditItemTemplate>
                                  <asp:Button ID="UpdateButton" runat="server" Text="Update" CommandName="Update" />
                    <asp:Button ID="CancelButton" runat="server" Text="Cancel" CommandName="Cancel" />
                              </EditItemTemplate>
                          </asp:TemplateField>
                          
                      </Columns>
                  </asp:GridView>

              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->

        <asp:SqlDataSource ID="SDSUsers" runat="server"  ConnectionString="<%$ ConnectionStrings:conn %>" UpdateCommand=" " OnUpdating="SDSUsers_Updating"
        SelectCommand="SELECT u.Id, u.FullName AS UserName, u.Email, u.Mobile, u.StoreGroupName, u.StoreGroupId, u.AreaId, STUFF( (SELECT ', ' + CASE WHEN LAG(rt.TypeName) OVER (PARTITION BY urm.userId, rt.TypeName ORDER BY r.RoleName) = rt.TypeName THEN r.RoleName
         ELSE rt.TypeName + ': ' + r.RoleName END FROM user_userRole_mapping urm JOIN userRole r ON urm.roleId = r.Id JOIN UserRoleType rt ON r.RoleType = rt.Id
         WHERE urm.userId = u.Id ORDER BY rt.TypeName, r.RoleName FOR XML PATH('')), 1, 2, '' ) AS Roles FROM [user] u 
            where (isnull(@role, 0) <= 0 or exists(select * from user_userRole_mapping where Userid=u.Id and RoleId=@role) )  and (isnull(@roleType, 0) <= 0 or exists((select * from user_userRole_mapping m inner join userRole r on m.RoleId=r.Id where m.UserId=u.Id and r.RoleType=@roleType)) ) and (isnull(@user, '') like '' or u.FullName like concat('%',@user, '%')) ;" >
		<SelectParameters>
            <asp:ControlParameter ControlID="txtSearchUser" Name="user" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selRoleType" Name="roleType" DefaultValue="0" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter ControlID="selRole" Name="role" DefaultValue="0" ConvertEmptyStringToNull="false" />
        </SelectParameters><UpdateParameters><asp:Parameter Name="Roles" /><asp:Parameter Name="Id" /></UpdateParameters>
    </asp:SqlDataSource>
    

    </div>
</asp:Content>