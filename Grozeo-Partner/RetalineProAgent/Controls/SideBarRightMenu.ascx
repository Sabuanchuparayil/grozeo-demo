<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="SideBarRightMenu.ascx.cs" Inherits="RetalineProAgent.Controls.SideBarRightMenu" %>

      <div class="dropdown dropdown-a switch_storeGP" style="width:40px"> <!-- Switch Store Group -->
        
          <asp:DropDownList runat="server" ID="selSwitchStore" DataSourceID="SDSStoreAccounts" AutoPostBack="true" CssClass="selectpicker" data-live-search="true" data-container="switch_storeGP" DataTextField="Name" DataValueField="Id" OnSelectedIndexChanged="selSwitchStore_SelectedIndexChanged"></asp:DropDownList>
        <%--<select class="selectpicker" data-live-search="true" data-container="switch_storeGP">
          <option data-tokens="ketchup mustard">Store One</option>
          <option data-tokens="mustard">Store Two</option>
          <option data-tokens="frosting">Store Three</option>
          <option data-tokens="Four">Store Four</option>
          <option data-tokens="Five">Store Five</option>
          <option data-tokens="six">Store six</option>
          <option data-tokens="navas store">navas store</option>
          <option data-tokens="frosting1">Store eight</option>
          <option data-tokens="frosting2">Store nine</option>
          <option data-tokens="frosting3">Store ten</option>
          <option data-tokens="frosting4">Store frosting4</option>
          <option data-tokens="frosting5">Store frosting5</option>
          <option data-tokens="Shilpa">Shilpa</option>
          <option data-tokens="PVR shop">PVR shop</option>
          <option data-tokens="frosting7">Store frosting7</option>
          <option data-tokens="frosting8">Store frosting8</option>
          <option data-tokens="frosting9">Store frosting9</option>
          <option data-tokens="frosting11">Store frosting11</option>
          <option data-tokens="PVR Store">PVR Store</option>
          <option data-tokens="frosting13">Store frosting13</option>
          <option data-tokens="frosting14">Store frosting14</option>
          <option data-tokens="frosting15">Store frosting115</option>
          <option data-tokens="frosting16">Store frosting16</option>
          <option data-tokens="frosting17">Store frosting17</option>
          <option data-tokens="Maveli Store">Maveli Store</option>
          <option data-tokens="PVR">PVR</option>
          
        </select>--%>



      </div>


            <%--<asp:Repeater runat="server" DataSourceID="SDSStoreAccounts">
                <HeaderTemplate><ul class="nav nav-pills nav-sidebar flex-column" style="display: block;"></HeaderTemplate>
                <ItemTemplate>

                    <asp:LinkButton runat="server" OnClick="btnSwitchStore" lbltext='<%# Eval("Name") %>' storeid='<%# Eval("Id") %>' CssClass="dropdown-link"  
                            style='<%# (this.CurrentUser.StoreGroupId.Equals(Eval("Id")) ?"font-weight: bold;":"") %>'>
                                          <div class="media">
                    <img src="<%# (Eval("logoImage")) %>"" style="max-width: 30px; max-height: 30px; width: auto; height: auto" alt="">
                    <div class="media-body">
                      <p><span class="<%# (this.CurrentUser.StoreGroupId.Equals(Eval("Id")) ?"tx-black":"") %>"><%# Eval("Name") %></span></p>
                    </div>
                  </div><!-- media -->

                  <p></p>
                        </asp:LinkButton>

                    
                </ItemTemplate>
                <FooterTemplate></ul></FooterTemplate>
            </asp:Repeater>--%>

    <asp:SqlDataSource runat="server" ID="SDSStoreAccounts" OnSelecting="SDSStoreAccounts_Selecting" OnSelected="SDSStoreAccounts_Selected" 
        ConnectionString="<%$ ConnectionStrings:conn %>"
         SelectCommand="Select Id,CONCAT([Name],'  ','(', Id, '_', StoreId, ')') as Name , logoImage, logoSmall from AppTenant WHERE ApiID= @apiid and isnull(StoreId, -1)>0 and ( @usertype = 0 OR isnull(Id, -1) in (SELECT M.StoreGroupId FROM User_UserRole_Mapping M INNER JOIN [User] u on u.Id=M.UserId WHERE u.Email like @user)) order by Id desc">
        <SelectParameters>
            <asp:Parameter Name="usertype" Type="Int32" ConvertEmptyStringToNull="false" DefaultValue="-1" />
            <asp:Parameter Name="user" DefaultValue="" />
            <asp:Parameter Name="apiid" DefaultValue="1" />
        </SelectParameters>
    </asp:SqlDataSource>
