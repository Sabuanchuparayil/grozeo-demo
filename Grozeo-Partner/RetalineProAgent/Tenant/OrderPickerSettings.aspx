<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="Create Order Picker" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="OrderPickerSettings.aspx.cs" Inherits="RetalineProAgent.OrderPickerSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Users">Users</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/OrderPicker">Order Picker</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Order Picker</li>--%>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
       <a href="/Tenant/OrderPicker"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><%= (String.IsNullOrEmpty(Request.QueryString["id"]) ? "Create Order Picker" : "Edit Order Picker") %></h6>
        <p class="mb-0"><%= (String.IsNullOrEmpty(Request.QueryString["id"]) ? "Create Order Picker" : "Edit Order Picker") %></p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="/Content/custom/plugins/toastr/toastr.min.js"></script>
    <script src="/Content/custom/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">
          <%--<label class="section-title">Create New Order Picker</label>--%>
          <div class="form-layout">
            <div class="row row-sm">
                <div class="col-sm-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Store: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selBranch" runat="server" DataSourceID="SDSBranches" AppendDataBoundItems="false" OnDataBound="selBranch_DataBound" DataTextField="br_Name" DataValueField="br_ID" CssClass="form-control select2">
                              <asp:ListItem Value="">Select Store</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFirstName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store is required" ValidationGroup="OrderPicker" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3 -->
<asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, CONCAT(' - ', br_Name, br_City) as combinedName FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /><asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>

              <div class="col-sm-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">First Name: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtFirstName" runat="server" CssClass="form-control" placeholder="Enter First Name" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFirstName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="First name is required" ValidationGroup="OrderPicker" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-sm-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Last Name: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtLastName" runat="server" CssClass="form-control" placeholder="Enter Last Name" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLastName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Last name is required" ValidationGroup="OrderPicker" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <%--<div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Phone: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtMobileNumber" runat="server" MaxLength="10" CssClass="form-control txtPhone" placeholder="Enter Phone Number" TextMode="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="nofill"/>
                  <%--<asp:Label ID="lblMessage" CssClass="error_msg_wrap" ForeColor="Red" Font-Bold="true" runat="server"/>--%>
                  <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobileNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Phone number is required" ValidationGroup="OrderPicker" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div>--%><!-- col-4 -->
            <div class="col-md-4">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">Phone: <span class="tx-danger">*</span></label>
                          <div class="country_code_mobile d-flex align-items-center position-relative">
                            <%--<input id="" class="form-control border-0" placeholder="Enter Phone Number" value="9995866697" name="phone" type="tel" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"  maxlength="10"/>--%>
                              <asp:TextBox ID="txtMobileNumber" runat="server" MaxLength="10" CssClass="form-control border-0 PhoneNumbercode restrictmobile" placeholder="Enter Phone Number" TextMode="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="nofill"/>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobileNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Phone number is required" ValidationGroup="OrderPicker" ForeColor="Red"></asp:RequiredFieldValidator>
                              <asp:Label ID="lblMessage" CssClass="error_msg_wrap" ForeColor="Red" runat="server"/>
                          </div>
                        </div>
                      </div>
            <div class="col-md-8">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Email ID: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmailID" runat="server" CssClass="form-control" placeholder="Enter Email ID" TextMode="Email" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group mg-b-10-force">
                  <label class="w-100 text-left tx-dark">Employee ID: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmpID" runat="server" CssClass="form-control" placeholder="Enter Employee ID" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Employee Number: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmpNINumber" runat="server" CssClass="form-control" placeholder="Enter employee number" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                  <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Address 1: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress1" runat="server" CssClass="form-control" placeholder="Enter Address 1" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Address 2: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress2" runat="server" CssClass="form-control" placeholder="Enter Address 2" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Post Code: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtPostCode" runat="server" CssClass="form-control" placeholder="Post Code" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-sm-6 col-lg-4">
                    <div>
                        <label class="ckbox form-control-label mb-1 d-inline-block tx-dark">
                            <input type="checkbox" id="chkManualSchedule" runat="server" checked='<%# Eval("is_allowManualSchedule").Equals("Active") %>'><span>Allow manual schedule</span>
                        </label>
                    </div>
                    <div>
                        <label class="ckbox form-control-label mb-1 d-inline-block tx-dark">
                            <input type="checkbox" id="chkStoreClose" runat="server" checked='<%# Eval("allowStoreClose").Equals("Active") %>' onchange="updateIsOffline(this)"><span>Allow store close</span>
                            <input type="hidden" id="hdnIsOffline" runat="server" />
                        </label>
                    </div>

                </div>
                <!-- col-3 -->
                <div class="col-sm-6 col-lg-4">
                    <div>
                        <label class="ckbox form-control-label mb-1 d-inline-block tx-dark">
                            <input type="checkbox" id="chkAutoSchedule" runat="server" checked='<%# Eval("is_allowAutoSchedule").Equals("Active") %>'><span>Allow auto schedule</span>
                        </label>
                    </div>
                    <div>
                        <label class="ckbox form-control-label mb-1 d-inline-block tx-dark">
                            <input type="checkbox" id="chkInvenCtrl" runat="server" checked='<%# Eval("allowInventoryControl").Equals("Active") %>'><span>Allow inventory control</span>
                        </label>
                    </div>
                </div>
                <!-- col-3 -->
            </div><!-- row -->

            <div class="mt-4">
              <%--<button class="btn btn-primary bd-0" id="btnSubmit" runat="server" onclick="btnAdd_Click">Submit Form</button>--%>
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary mr-1" Text="Submit Form" ValidationGroup="OrderPicker"/>
                <asp:Button runat="server" ID="btnCancel"  CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled" CssClass="btn btn-secondary" Text="Cancel" PostBackUrl="~/Tenant/OrderPicker" ValidationGroup="OrderPicker"/>
              <%--<button class="btn btn-secondary bd-0" ID="btnCancel" runat="server" CausesValidation="false" ValidateRequestMode="Disabled">Cancel</button>--%>
            </div><!-- form-layout-footer -->
          </div><!-- form-layout -->
        </div>
    </div>
    <style>
        .country_code_mobile .error_msg_wrap{
            bottom: -15px;
        }
    </style>
    <script>
function updateIsOffline(checkbox) {
    document.getElementById('<%= hdnIsOffline.ClientID %>').value = 1;
}
    </script>

    
</asp:Content>
