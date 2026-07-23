<%@ Page Language="C#" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Delivery Staffs" AutoEventWireup="true" CodeBehind="DeliveryStaffCreate.aspx.cs" Inherits="RetalineProAgent.DeliveryStaffCreate" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Users">Users</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/DeliveryStaffs">Delivery Staffs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Delivery Staff</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
    <a href="/Tenant/DeliveryStaffs"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><%= (String.IsNullOrEmpty(Request.QueryString["id"]) ? "Create Delivery Staff" : "Edit Delivery Staff") %></h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="/Content/custom/plugins/toastr/toastr.min.js"></script>
    <script src="/Content/custom/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">
          <%--<label class="section-title">Create New Delivery Boy</label>--%>
          <div class="form-layout">
            <div class="row row-sm">
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Store: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selBranch" runat="server" DataSourceID="SDSBranches" AppendDataBoundItems="false" OnDataBound="selBranch_DataBound" DataTextField="br_Name" DataValueField="br_ID" CssClass="form-control select2">
                              <asp:ListItem Value="">Select Store</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFirstName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3 -->
                <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, CONCAT(' - ', br_Name, br_City) as combinedName FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /><asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">First Name: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtFirstName" runat="server" CssClass="form-control" placeholder="Enter first name" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFirstName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="First name is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Last Name: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtLastName" runat="server" CssClass="form-control" placeholder="Enter Last Name" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLastName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Last name is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Date Of Birth: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtDOB" runat="server" CssClass="form-control" TextMode="Date"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDOB" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Date of birth is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Address 1: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress1" runat="server" CssClass="form-control" placeholder="Enter address 1" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group mg-b-10-force">
                  <label class="w-100 text-left tx-dark">Address 2: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress2" runat="server" CssClass="form-control" placeholder="Enter address 2" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Post Code: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtPostCode" runat="server" CssClass="form-control" placeholder="Enter post code" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4" runat="server" visible="false">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Employee ID: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmpID" runat="server" CssClass="form-control" placeholder="Enter employee ID" autocomplete="nofill"/>
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
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Email ID: </label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmailID" runat="server" CssClass="form-control" placeholder="Enter email ID" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                <%--<div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Phone: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtPhone" runat="server" MaxLength="10" CssClass="form-control" placeholder="Enter phone" TextMode="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="nofill"/>
                    <asp:Label ID="lblMessage" CssClass="error_msg_wrap" ForeColor="Red" Font-Bold="true" runat="server"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtPhone" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Phone number is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div>--%><!-- col-4 -->
                <div class="col-lg-4">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">Phone: <span class="tx-danger">*</span></label>
                          <div class="country_code_mobile d-flex align-items-center position-relative">
                            <%--<input id="" class="form-control border-0" placeholder="Enter Phone Number" value="9995866697" name="phone" type="tel" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"  maxlength="10"/>--%>
                              <asp:TextBox ID="txtMobileNumber" runat="server" MaxLength="10" CssClass="form-control  border-0 PhoneNumbercode restrictmobile" placeholder="Enter Phone Number" TextMode="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="nofill"/>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobileNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Phone number is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                              <asp:Label ID="lblMessage" CssClass="error_msg_wrap" ForeColor="Red" runat="server"/>
                          </div>
                        </div>
                      </div>
                <asp:PlaceHolder ID="plclicence" Visible="false" runat="server">
                <div class="col-lg-4 mb-1">
                    <asp:CheckBox ID="chkvalidlicense" TextAlign="Left" runat="server" OnCheckedChanged="chkvalidlicense_CheckedChanged"  AutoPostBack="true"/>
                <span class="form-control-label mb-1 w-100 tx-dark">Delivery staff has a Valid Driving license</span>
                </div><!-- col-3 -->
                  </asp:PlaceHolder>
                 <div class="col-lg-4 mb-1">
                  <asp:CheckBox ID="chkManualSchedule" TextAlign="Left" runat="server" Checked='<%# Eval("is_allowManualSchedule1").Equals("Active") %>'/>
                <span class="form-control-label mb-1 w-100 tx-dark">Allow manual schedule</span>
                </div><!-- col-3 -->
                    
                <div class="col-lg-4 mb-1">
                    <asp:CheckBox ID="chkAutoSchedule" TextAlign="Left" runat="server" Checked='<%# Eval("is_allowAutoSchedule1").Equals("Active") %>'/>
                      <span class="form-control-label mb-1 w-100 tx-dark">Allow auto schedule</span>
                </div><!-- col-3 -->  
                
                <div class="col-12">
                    <div class="row">
                        <div class="col-lg-3">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">License: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtLicense" runat="server" Enabled="true" CssClass="form-control" placeholder="Enter License" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ID="rqdLicense" ControlToValidate="txtLicense" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="License is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">License Validity: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtLicenseValidity" Enabled="true" runat="server" CssClass="form-control" TextMode="Date" autocomplete="nofill"/>
                  <asp:RequiredFieldValidator runat="server" ID="reqLicenseValidity" ControlToValidate="txtLicenseValidity" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="License validity date is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->               
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Coverage km:</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtCoverageKM" runat="server" CssClass="form-control" placeholder="Enter coverage km" autocomplete="nofill"/>
                    <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtCoverageKM" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Coverage KM date is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                </div>
              </div><!-- col-3-->
                <div class="col-lg-2">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark">Employee Type:</label>
                  <asp:DropDownList ID="selEmpType" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="0">Please Select</asp:ListItem>
                              <asp:ListItem Value="OwnEmployee">Own Employee</asp:ListItem>
                              <asp:ListItem Value="HiredEmployee">Hired Employee</asp:ListItem>
                          </asp:DropDownList>
                  <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="selEmpType" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Employee type is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                </div>
              </div><!-- col-3 -->
                        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                   { %>
                     <div class="col-lg-8"  style="display:none;">                   
                   <% }
                    else
                    { %>
                    <div class="col-lg-8">
                     <% } %>  
                            <div class="form-group">
                                <label class="form-control-label">Select language Preference: <span class="tx-danger">*</span></label>
                                <div class="dropdown-container row row-sm">
                                    <div class="dropdown-wrapper col-lg-4">
                                        <asp:DropDownList ID="selFirstLanguage" runat="server" CssClass="form-control select2" ForeColor="GrayText" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selFirstLanguage_SelectedIndexChanged">
                                            <asp:ListItem Text="Select first preference" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:RequiredFieldValidator ID="rfvFirstLanguage" Enabled="false" runat="server" ControlToValidate="selFirstLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Primary language is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                                    </div>
                                    <div class="dropdown-wrapper col-lg-4">
                                        <asp:DropDownList ID="selSecondLanguage" runat="server" CssClass="form-control select2" ForeColor="GrayText" AppendDataBoundItems="true">
                                            <asp:ListItem Text="Select second preference" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:RequiredFieldValidator ID="rfvSecondLanguage" Enabled="false" runat="server" ControlToValidate="selSecondLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Secondary language is required" ValidationGroup="DeliveryBoy" ForeColor="Red"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div><!-- row -->
            <div class="mt-4">
                <div class="d-inline-block">
                    <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary mr-1" Text="Save" ValidationGroup="DeliveryBoy"/>
                </div>
                <div class="d-inline-block">
                    <a href="/Tenant/DeliveryStaffs" class="btn btn-secondary">Cancel</a>
                </div>
                
            </div>
          </div><!-- form-layout -->
        </div>
    </div>
    



    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Cancel</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/DeliveryStaffs";
            });
        });
    </script>

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
        .country_code_mobile .error_msg_wrap{
            bottom: -15px;
        }
        .dropdown-container {
    display: flex;
    gap: 10px;
    align-items: center;
}

.dropdown-container .select2 {
    flex: 1;
}
    </style>

</asp:Content>