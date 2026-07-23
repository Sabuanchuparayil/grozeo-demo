<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Create Retailer Lead" Async="true" AutoEventWireup="true" CodeBehind="RLeadSettings.aspx.cs" Inherits="RetalineProAgent.RLeadSettings" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<%@ Register Src="~/Controls/PopupUpgradeConsent.ascx" TagPrefix="uc1" TagName="PopupUpgradeConsent" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><%= (String.IsNullOrEmpty(Request.QueryString["id"]) ? "Create New Retailer Lead" : "Edit Retailer Lead") %></h6>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item"><a href="/Business/RetailerLeads">Retailer Leads</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Retailer Lead</li>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="section-wrapper">
          <%--<label class="section-title">Create New Contact</label>--%>
          <div class="form-layout">
            <div class="row mg-b-5">
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Store Name: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtStoreName" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter store name" />
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Lead Type: <span class="tx-danger">*</span></label>
                  <%--<asp:ListBox ID="lstContactType" SelectionMode="Multiple" OnDataBound="lstContactType_DataBound" runat="server" DataSourceID="SDSContactType" DataTextField="name" DataValueField="id"
                          CssClass="form-control select2" multiple="multiple"></asp:ListBox>
                    <asp:SqlDataSource runat="server" ID="SDSContactType" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT id,name FROM crm_contact_type WHERE status = 1"></asp:SqlDataSource>--%>
                    <asp:TextBox ID="txtLeadType" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter store name" Enabled="false"/>
                  <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <label class="form-control-label">Locate store in map: <span class="tx-danger">*</span></label>
                  <div class="input-group input-group"><asp:HiddenField ID="hidMapAddr" runat="server" /> 
			      <asp:TextBox ID="txtLocation" runat="server" ReadOnly="true" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS" required CssClass="form-control" placeholder="Click to load map" autocomplete="off"/>                  
                  <span class="input-group-append">
                    <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS">Load Map</button>
                  </span>
                </div>
                        <asp:HiddenField ID="hidLat" runat="server" />
                        <asp:HiddenField ID="hidLong" runat="server" />
                        <asp:HiddenField ID="hidState" runat="server" />
                        <asp:HiddenField ID="hidLocality" runat="server" />
                        <asp:HiddenField ID="hidPlace" runat="server" />
                        <asp:HiddenField ID="hidCountry" runat="server" />
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Location: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddr1" runat="server" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" required CssClass="form-control" placeholder="Enter Store Location" autocomplete="off"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Store Address: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control" placeholder="Enter Store Address" autocomplete="off"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Pin Code: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                   <asp:TextBox ID="txtPinCode" runat="server" required CssClass="form-control" placeholder="Enter Pin Code" autocomplete="off"/>
                </div>
              </div><!-- col-4 -->
              <%--<div class="col-lg-8">
                <div class="form-group">
                  <label class="form-control-label">Address: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter address" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddress" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->--%>
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Contact Person: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtContactPerson" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter contact person" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPerson" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Contact Number: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtMobile" runat="server" autocomplete="off" TextMode="Phone" MaxLength="10" CssClass="form-control" placeholder="Enter contact number" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobile" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact number is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Telephone Number: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtTelephoneNumber" runat="server" autocomplete="off" TextMode="Phone" MaxLength="11" CssClass="form-control" placeholder="Enter telephone number" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtTelephoneNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Telephone number is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group navas">
                  <label class="form-control-label">Retail Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selRetailCategory" runat="server" AutoPostBack="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRetailCat" DataTextField="business_type_name" DataValueField="business_type_id" AppendDataBoundItems="true"><asp:ListItem Text="Select retail category" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource runat="server" ID="SDSRetailCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name FROM finascop_business_type WHERE STATUS = 1 UNION 
                SELECT 0 AS business_type_id,'Others' AS business_type_name FROM finascop_business_type"></asp:SqlDataSource>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-8">
                <div class="form-group">
                  <label class="form-control-label">Email: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmail" runat="server" autocomplete="off" TextMode="Email" CssClass="form-control" placeholder="Enter email" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Email is required" ValidationGroup="InsertContact" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
            </div><!-- row -->
            <div class="form-layout-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnContactSubmit_Click" CssClass="btn btn-success" Text="Submit" ValidationGroup="InsertContact"/>&nbsp;
            <a href="/Business/RetailerLeads" class="btn btn-secondary bd-0" style="height:45px; width:100px">Cancel</a>
            </div>
              </div>
          </div><!-- form-layout -->

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
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
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

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    

    <script>
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            $('.duallistbox').bootstrapDualListbox();
        });
    </script>

    <style>
        .select2.select2-container {
            width:100%!important;
        }
    </style>

</asp:Content>
