<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Create Relationship Officer" Async="true" AutoEventWireup="true" CodeBehind="ROSettings.aspx.cs" Inherits="RetalineProAgent.ROSettings" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<%@ Register Src="~/Controls/PopupUpgradeConsent.ascx" TagPrefix="uc1" TagName="PopupUpgradeConsent" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqXcY9rSQa3RtAP8PepcPOMh4FBVuwRRc&libraries=places&v=weekly"></script>
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><%= (String.IsNullOrEmpty(Request.QueryString["id"]) ? "Create New Relationship Officer" : "Edit Relationship Officer") %></h6>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/Resources">Resources</a></li>
    <li class="breadcrumb-item"><a href="/Business/RelationshipOfficer">Relationship Officer</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Relationship Officer</li>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="section-wrapper">
          <%--<label class="section-title">Create New Contact</label>--%>
          <div class="form-layout">
            <div class="row mg-b-5">
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Name: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtName" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter name" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Name is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-8">
                <div class="form-group">
                  <label class="form-control-label">Address 1: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAddress" runat="server" CssClass="form-control" placeholder="Enter address 1" autocomplete="off"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label"><%=RetalineProAgent.Service.Common.StateLabel %>: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selState" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" required DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="true" >
                  </asp:DropDownList>
                </div>
                    <asp:HiddenField ID="hidState" runat="server" />
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label"><%=RetalineProAgent.Service.Common.DistrictLabel %>: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" required DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="false" ></asp:DropDownList>
                </div><asp:HiddenField ID="hidDistrict" runat="server" />
              </div><!-- col-4 -->
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Post Code: <span class="tx-danger">*</span></label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                   <asp:TextBox ID="txtPostCode" runat="server"  CssClass="form-control" placeholder="Enter Post Code" autocomplete="off"/>
                    <asp:RequiredFieldValidator ID="reqpostcode"  runat="server" ControlToValidate="txtPostCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Post code is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Personal Email: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtEmail" runat="server" autocomplete="off" TextMode="Email" CssClass="form-control" placeholder="Enter personal email" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Personal email is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Personal Mobile: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtMobile" runat="server" autocomplete="off" TextMode="Phone" MaxLength="10" CssClass="form-control PhoneNumbercode restrictmobile" placeholder="Enter personal mobile" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobile" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact number is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Emergency Contact Name: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtContactPerson" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter emergency contact person" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPerson" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Emergency contact name is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />
                
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Emergency Contact Number: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtTelephoneNumber" runat="server" autocomplete="off" TextMode="Phone" MaxLength="10" CssClass="form-control PhoneNumbercode restrictmobile" placeholder="Enter emergency contact number" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtTelephoneNumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Emergency contact number is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                 <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Qualification: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selQualification" runat="server" CssClass="form-control select2" ForeColor="GrayText">
                              <asp:ListItem Value="0">Please Select</asp:ListItem>
                              <asp:ListItem Value="Undergraduate">Undergraduate</asp:ListItem>
                              <asp:ListItem Value="Graduate">Graduate</asp:ListItem>
                              <asp:ListItem Value="Postgraduate">Postgraduate</asp:ListItem>
                          </asp:DropDownList>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="selQualification" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Qualification is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Experience (In Years & Months): <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtExp" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter experience" />
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtExp" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Experience is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Blood Group: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtBloodGrp" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter blood group" />
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtBloodGrp" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Blood group is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">License Number: </label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtLicense" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter license number" />
                  <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtLicense" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="License number is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "NI" : 
                  (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "Aadhaar" : 
                  (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "EmiratesID" : ""))) %> Number: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAadhar" runat="server"  autocomplete="off" CssClass="form-control" />
                  <asp:RequiredFieldValidator ID="rfvAadhar" runat="server" ControlToValidate="txtAadhar" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 <%=(ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "hide" : "") %>">
                <div class="form-group">
                  <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Share Code" : "PAN") %>: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtPAN" runat="server" autocomplete="off" CssClass="form-control" />
                  <asp:RequiredFieldValidator ID="rfvPAN"  runat="server" ControlToValidate="txtPAN" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label"><%=(ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "IBAN" : "Bank Account Number") %>: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtAccount" runat="server" autocomplete="off" CssClass="form-control" placeholder="Enter bank account number" />
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAccount" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Bank account number is required" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                 <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort" : 
                  (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "IFSC" : 
                  (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "IBAN" : ""))) %> Code: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtIFSC" runat="server" autocomplete="off" CssClass="form-control" />
                  <asp:RequiredFieldValidator runat="server" ID="rfvCode" ControlToValidate="txtIFSC" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3 <%= (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "hide" : "") %>">
                <div class="form-group">
                  <label class="form-control-label"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Passport/ID Number" : "UPI ID") %>: <span class="tx-danger">*</span></label>
                  <input type="text" style="display:none" />
                  <input type="password" style="display:none" />
                  <asp:TextBox ID="txtUPI" runat="server" autocomplete="off" CssClass="form-control" />
                  <asp:RequiredFieldValidator runat="server"  ID="rfvPassport" ControlToValidate="txtUPI" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="ROInsert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-control-label">Upload Photo(. jpg,. jpeg,. png only):<span class="tx-danger">*</span></label>
                        <input type="file" id="filePhoto" runat="server" accept="image/jpeg, image/jpg, image/png" class="form-control-file fileupload_photo" required/>
                        <span id="photoError" class="error_msg_wrap" style="color: red; display: none;"></span>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-control-label">Upload CV (.pdf only): <span class="tx-danger">*</span></label>
                        <input type="file" id="fileCV" runat="server" accept="application/pdf" class="form-control-file fileupload_cv" required/>
                        <span id="cvError" class="error_msg_wrap" style="color: red; display: none;"></span>
                    </div>
                </div>

            </div><!-- row -->
            <div class="form-layout-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnROSubmit_Click" CssClass="btn btn-success" Text="Submit" ValidationGroup="ROInsert"/>&nbsp;
            <a href="/Business/RelationshipOfficer" class="btn btn-secondary">Cancel</a>
            </div>
              </div>
          </div><!-- form-layout -->

    <asp:SqlDataSource ID="SDSState" runat="server" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state ORDER BY name ASC" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID ORDER BY dst_Name ASC">
        <SelectParameters><asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" /></SelectParameters></asp:SqlDataSource>

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
       document.addEventListener('DOMContentLoaded', function () {
           document.getElementById('btnSubmit').addEventListener('click', function (event) {
               event.preventDefault(); // Prevent form submission

               var photoInput = document.getElementById('filePhoto');
               var cvInput = document.getElementById('fileCV');
               var photoError = document.getElementById('photoError');
               var cvError = document.getElementById('cvError');
               var isValid = true;
               var maxSize = 1 * 1024 * 1024; // 1 MB in bytes

               // Reset error messages
               photoError.style.display = 'none';
               cvError.style.display = 'none';

               // Validate Photo
               if (photoInput.files.length > 0) {
                   var file = photoInput.files[0];
                   var fileType = file.type.toLowerCase();
                   var fileSize = file.size;

                   if (fileSize > maxSize) {
                       photoError.textContent = 'Photo size must not exceed 1 MB.';
                       photoError.style.display = 'block';
                       isValid = false;
                   } else if (!(fileType === 'image/jpeg' || fileType === 'image/jpg' || fileType === 'image/png')) {
                       photoError.textContent = 'Photo must be in JPG, JPEG, or PNG format.';
                       photoError.style.display = 'block';
                       isValid = false;
                   } else {
                       // Perform dimension check (replace with your required dimensions)
                       var img = new Image();
                       img.onload = function () {
                           var width = img.width;
                           var height = img.height;
                           var requiredWidth = 800; // Replace with your required width
                           var requiredHeight = 600; // Replace with your required height

                           if (width !== requiredWidth || height !== requiredHeight) {
                               photoError.textContent = `Photo dimensions must be ${requiredWidth}x${requiredHeight} pixels.`;
                               photoError.style.display = 'block';
                               isValid = false;
                           }
                       };
                       img.src = URL.createObjectURL(file);
                   }
               } else {
                   photoError.textContent = 'Please select a photo to upload.';
                   photoError.style.display = 'block';
                   isValid = false;
               }

               // Validate CV
               if (cvInput.files.length > 0) {
                   var cvFile = cvInput.files[0];
                   var cvFileType = cvFile.type.toLowerCase();
                   var cvFileSize = cvFile.size;

                   if (cvFileSize > maxSize) {
                       cvError.textContent = 'CV size must not exceed 1 MB.';
                       cvError.style.display = 'block';
                       isValid = false;
                   } else if (cvFileType !== 'application/pdf') {
                       cvError.textContent = 'CV must be in PDF format.';
                       cvError.style.display = 'block';
                       isValid = false;
                   }
               } else {
                   cvError.textContent = 'Please select a CV to upload.';
                   cvError.style.display = 'block';
                   isValid = false;
               }

               // Submit form if valid
               if (isValid) {
                   console.log('Form submitted successfully.');
                   
               }
           });
       });
   </script>

    

    <script>
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            $('.duallistbox').bootstrapDualListbox();
        });
    </script>

</asp:Content>
