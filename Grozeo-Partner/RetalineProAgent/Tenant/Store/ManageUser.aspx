<%@ Page Language="C#" AutoEventWireup="true" Title="Manage User" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ManageUser.aspx.cs" Inherits="RetalineProAgent.ManageUser" %>


<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Users">Users</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Store/Users">Admin Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="Manage User"></asp:Literal></h6>    
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <div class="panel panel-default">
        <div class="panel-heading" runat="server" visible="false">
            <i class="fa fa-bar-chart-o fa-fw"></i>
            <asp:Literal ID="ltrAction" runat="server" Text="New User"></asp:Literal>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="section-wrapper card-body border-0 p-3 shadow_top h-100" style="display: block;">
                        <h6 class="mb-1 tx-dark">User Info</h6>

                        <div class="form-group">
                            <label>Email: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <%--<asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtEmail" Text="*" ErrorMessage="Enter email"></asp:RequiredFieldValidator>--%>
                            <asp:TextBox ID="txtEmail" TextMode="Email" runat="server" CssClass="form-control" placeholder="Enter Email" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Email is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>

                        <%--<div class="form-group" id="divPassword" runat="server">
                                            <label>Temporary Password</label><asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtPassword" Text="*" ErrorMessage="Enter password"></asp:RequiredFieldValidator>
											<asp:TextBox ID="txtPassword" TextMode="Password" runat="server" CssClass="form-control" placeholder="Enter temporary password"/>
                                        </div>--%>

                        <div class="form-group">
                            <label>Mobile: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <%--<asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtMobile" Text="*" ErrorMessage="Enter mobile number"></asp:RequiredFieldValidator>--%>
                            <asp:TextBox ID="txtMobile" TextMode="Number" runat="server" CssClass="form-control PhoneNumbercode restrictmobile" placeholder="Enter 10 digit mobile number" autocomplete="nofill" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobile" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Mobile number is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <asp:PlaceHolder runat="server" ID="plcRoles">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <asp:DropDownList ID="selRoles" AutoPostBack="true" CssClass="form-control" runat="server" ValidationGroup="AddStore" DataSourceID="SDSRoles" DataTextField="RoleName" DataValueField="Id" AppendDataBoundItems="true">
                                            <asp:ListItem Text="Select Role" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                    </div>
                                </asp:PlaceHolder>
                            </div>
                            <div class="col-sm-6">
                                <asp:PlaceHolder runat="server" ID="plcBranches">
                                    <div class="form-group">
                                        <label>Branch</label>
                                        <asp:DropDownList ID="selBranch" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name" ValidationGroup="AddStore" DataValueField="br_ID" CssClass="form-control" runat="server">
                                            <asp:ListItem Text="Select Branch" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selBranch" ErrorMessage="Select Branch" ValidationGroup="AddStore"></asp:RequiredFieldValidator>
                                        <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDS_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                            SelectCommand="SELECT br_ID, br_Name, br_City, br_Address, br_directDelivery, br_courierDelivery FROM finascop_branch WHERE br_storeGroup = @storegroup"
                                            ProviderName="MySql.Data.MySqlClient">
                                            <SelectParameters>
                                                <asp:Parameter Name="storegroup" DefaultValue="-1" />
                                            </SelectParameters>
                                        </asp:SqlDataSource>

                                    </div>
                                </asp:PlaceHolder>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="section-wrapper card-body border-0 p-3 shadow_top">
                        <h6 class="mb-1 tx-dark">Profile</h6>
                        <div class="form-group">
                            <label>Full Name: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <%--<asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtFullName" Text="*" ErrorMessage="Enter name"></asp:RequiredFieldValidator>--%>
                            <asp:TextBox ID="txtFullName" runat="server" CssClass="form-control" placeholder="Enter name" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFullName" CssClass="error_msg_wrap" Display="Dynamic" autocomplete="nofill" ErrorMessage="Full name is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtAddress" runat="server" CssClass="form-control" placeholder="Enter address" autocomplete="nofill" />
                        </div>

                        <div class="row row-sm">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><%=RetalineProAgent.Service.Common.DistrictLabel %></label>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtCity" runat="server" CssClass="form-control" placeholder="Enter district" autocomplete="nofill" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><%=RetalineProAgent.Service.Common.StateLabel %></label>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtState" runat="server" CssClass="form-control" placeholder="Enter state" autocomplete="nofill" />
                                </div>
                            </div>
                        </div>
                        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                            { %>
                        <div class="col-lg-12 p-0" style="display: none;">
                            <% }
                                else
                                { %>
                            <div class="col-lg-12 p-0">
                                <% } %>
                                <div class="form-group">
                                    <label class="form-control-label">Select language Preference: <span class="tx-danger">*</span></label>
                                    <div class="dropdown-container row row-sm">
                                        <div class="dropdown-wrapper col-sm-6 mb-3 mb-sm-0">
                                            <asp:DropDownList ID="selFirstLanguage" runat="server" CssClass="form-control select2" ForeColor="GrayText" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selFirstLanguage_SelectedIndexChanged">
                                                <asp:ListItem Text="Select first preference" Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator ID="rfvFirstLanguage" Enabled="false" runat="server" ControlToValidate="selFirstLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Primary language is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                        <div class="dropdown-wrapper col-sm-6">
                                            <asp:DropDownList ID="selSecondLanguage" runat="server" CssClass="form-control select2" ForeColor="GrayText" AppendDataBoundItems="true">
                                                <asp:ListItem Text="Select second preference" Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator ID="rfvSecondLanguage" Enabled="false" runat="server" ControlToValidate="selSecondLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Secondary language is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <%--<asp:ObjectDataSource ID="ODSRoles" runat="server" SelectMethod="StoreRoles" TypeName="RetalineProAgent.ManageUser"></asp:ObjectDataSource>--%>
                            <asp:SqlDataSource ID="SDSRoles" runat="server" OnSelecting="SDSRoles_Selecting" ConnectionString="<%$ ConnectionStrings:conn %>"
                                SelectCommand="select * from UserRole where RoleType=2 and Id > (select top 1 RoleId from User_UserRole_Mapping m inner join [User] u on u.Id=m.UserId where u.Email like @user order by RoleId asc)">
                                <SelectParameters>
                                    <asp:Parameter Name="usertype" Type="Int32" ConvertEmptyStringToNull="false" DefaultValue="-1" />
                                    <asp:Parameter Name="user" DefaultValue="" />
                                </SelectParameters>
                            </asp:SqlDataSource>

                            <%--                                <div class="form-group">
                                            <label>Photo</label>
											<asp:FileUpload ID="uploadPhoto" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgPhoto" runat="server" style="max-width: 40px; max-height: 40px; width: auto; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgPhoto" runat="server" Visible="false" Text="Delete?" />
                                        </div>--%>
                        </div>
                    </div>

                </div>
                &nbsp;&nbsp;

                            <%--<div class="row">
                                <div class="col-6"></div>
        <div class="col-6">
          <a href="/tenant/store/users" class="btn btn-secondary">Cancel</a>&nbsp;&nbsp;--%>
                <%--<asp:Button ID="btnReset" runat="server" OnClick="btnReset_Click" Text="Cancel" CssClass="btn btn-secondary" />--%>
                <%--<input type="submit" value="Create new Porject" class="btn btn-success float-right">--%>
                <%--<asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-success" Text="Add" ValidationGroup="AddStore"/>&nbsp;&nbsp;
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>
        </div>
      </div>--%>
                <div class="card-footer mt-0 d-flex flex-wrap justify-content-lg-end">
                    <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary mr-2" Text="Save" ValidationGroup="AddStore" />
                    <a href="/tenant/store/users" class="btn btn-secondary">Cancel</a>
                </div>

            </div>
            <!-- /.panel-body -->
        </div>
        <br />
        <br />

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                var primaryDropdown = document.getElementById('<%= selFirstLanguage.ClientID %>');
            var secondaryDropdown = document.getElementById('<%= selSecondLanguage.ClientID %>');

            primaryDropdown.addEventListener('change', function () {
                filterSecondaryDropdown(primaryDropdown, secondaryDropdown);
            });

            function filterSecondaryDropdown(primary, secondary) {
                var selectedPrimaryValue = primary.value;

                for (var i = 0; i < secondary.options.length; i++) {
                    var option = secondary.options[i];
                    if (option.value === selectedPrimaryValue) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'block';
                    }
                }
            }

            // Initial call to filter the secondary dropdown in case a primary value is preselected
            filterSecondaryDropdown(primaryDropdown, secondaryDropdown);
        });
        </script>

        <script>
            $(document).ready(function () {
                $(document).ready(function () {
                    $('.select2').select2();

                    //Bootstrap Duallistbox
                    $('.duallistbox').bootstrapDualListbox();
                });
            });
        </script>

        <style>
            .select2.select2-container {
                width: 100% !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                display: block;
                line-height: 36px;
            }

            .select2-container.select2-container--open {
                z-index: 1050;
            }

            .slim-sticky-sidebar .slim-header {
                z-index: 1051;
            }

            .modal-content {
                max-width: 80vw; /* Adjust as needed */
            }

            /* Increase the size of the dropdowns */
            .large-dropdown {
                font-size: 20px; /* Adjust as needed */
                height: 100%; /* Adjust as needed */
                width: 100%; /* Ensure it takes full width of its container */
            }
        </style>
</asp:Content>
