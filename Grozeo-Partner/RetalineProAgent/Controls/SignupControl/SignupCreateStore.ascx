<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="SignupCreateStore.ascx.cs" Inherits="RetalineProAgent.Controls.SignupControl.SignupCreateStore" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>

            <div class="login_head">
              <h2 class="mb-0">Welcome <asp:Literal ID="ltrGstOrganization" runat="server"></asp:Literal></h2>
              <p class="login-box-msg"><asp:Literal ID="ltrGstAddress" runat="server"></asp:Literal></p>
            </div>

            <div class="loginform_wrap">
                <label>Create your first store here</label>
              <div class="row row-sm ">

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtStoreName" runat="server" autocomplete="off" CssClass="form-control mb-3"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Store Name"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtStoreName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store name is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                    
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <input id="txtContactPerson" runat="server" type="text" class="form-control mb-3" name="ContactPerson " autocomplete="off" placeholder="Contact Name">
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPerson" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact person is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtContactPhone" runat="server" autocomplete="off" CssClass="form-control mb-3 restrictmobile" placeholder="Telephone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPhone" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact phone is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                    
                </div>

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox runat="server" Enabled="false" ReadOnly="true" id="txtLoginEmail" TextMode="Email" CssClass="form-control mb-3" autocomplete="off" placeholder="Email (User ID)" ></asp:TextBox>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLoginEmail" Enabled="false" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact email is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <div class="input-group">
                        <asp:DropDownList ID="selBusinessTypes" AutoPostBack="true" data-placeholder="Choose business type" runat="server" AppendDataBoundItems="true" DataSourceID="SDSBusinessCategories" DataTextField="business_category_name" DataValueField="business_category_id"
                          CssClass="form-control" style="width: 100%;"><asp:ListItem Text="Select Business Category" Value=""></asp:ListItem></asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selBusinessTypes" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select Business Category" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="input-group">
                                          <asp:ListBox ID="lstBusinessTypes" ClientIDMode="Static" SelectionMode="Multiple" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
                        <asp:CustomValidator ControlToValidate="lstBusinessTypes" ClientValidationFunction="businessTypeValidation" ValidateEmptyText="true" runat="server" CssClass="col-12 error_msg_wrap" ErrorMessage="Please select retail category" ValidationGroup="CreateStore" />
                    </div>
                </div>
                  <div class="col-12 mt-2">
                  <label><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Search / " : "") %>Enter Store Address</label>
                </div>
                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                      { %>

                  <div class="col-12 d-flex flex-wrap position-relative">
                  <div class="d-flex flex-wrap w-100" id="postcode_lookup_signup">

                  </div>
                  <div class="input-group w-100">
                        <asp:TextBox ID="txtAddr1UK" runat="server" autocomplete="off" CssClass="form-control mb-3 w-100 mx-wd-100p-force"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="Select Your Address"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr1UK" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Address is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                  </div>

                </div>


                      <div class="input-group">
                      </div>
                  <% } %>

                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtLocation" onfocus="if(!authentication.properties.mapTriggered){$('#ADDRESS').modal('show'); authentication.properties.mapTriggered=true;}" runat="server" data-toggle="modal" data-backdrop="static" autocomplete="off" data-keyboard="false" data-target="#ADDRESS" required CssClass="form-control mb-3" placeholder="Click to load map"/>
                        <i class="icon_map"></i>
                        <asp:HiddenField ID="hidMapAddr" runat="server" />
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <asp:TextBox ID="txtPinCode" autocomplete="off" runat="server" CssClass="form-control mb-3" placeholder="Postcode"/>
                        <asp:RequiredFieldValidator runat="server" ID="rqdpostcod" Enabled="false"  ControlToValidate="txtPinCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Post code is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>

                  <% if (ConfigurationManager.AppSettings.Get("CountryCode") != "UK")
                      { %>
                 <div class="col-12">
                   <div class="input-group">
                        <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control mb-3 w-100 mx-wd-100p-force" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder="Address"/>
                        <asp:RequiredFieldValidator runat="server"   ControlToValidate="txtAddr2" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Address is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>

                   </div>
                </div>
                  <% } %>

                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:TextBox ID="txtAddr3" runat="server" required CssClass="form-control mb-3" placeholder="Street / Locality"/>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr3" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please enter address part" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>

                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:TextBox ID="txtAddr4" runat="server" CssClass="form-control mb-3 w-100 mx-wd-100p-force" autocomplete="off" placeholder="Additional address (Optional)"/>
                    </div>
                </div>



                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:DropDownList ID="selState" OnSelectedIndexChanged="selState_SelectedIndexChanged" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control mb-3" style="width: 100%;" AppendDataBoundItems="true" ></asp:DropDownList>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selState" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select state" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                        <asp:HiddenField ID="hidState" runat="server" />
                    </div>
                </div>
                <div class="col-12 col-md-6">   
                    <div class="input-group">
                        <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control mb-3 mb-md-0" style="width: 100%;" AppendDataBoundItems="false" ></asp:DropDownList>
                        <asp:HiddenField ID="hidDistrict" runat="server" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="selDistrict" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Please select district" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                </div>
                  <div class="col-12 col-md-6">
                    <div class="input-group">
                     <asp:TextBox runat="server" CssClass="form-control" ID="txtRaferralcode" placeholder="Referral Code (Optional)"></asp:TextBox>
                     <asp:RequiredFieldValidator runat="server" Enabled="false" ID="rfvreferral" ControlToValidate="txtRaferralcode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Referral Code is required" ValidationGroup="CreateStore"></asp:RequiredFieldValidator>
                    </div>
                  </div>
                   <div class="col-12 col-md-6">
                         <div class="col-6 ckbox w-100 mb-2 mb-md-0 ml-2 mt-2">
                    <asp:CheckBox ID="chkAcceptTerms" runat="server" CssClass="tx-12" />
                    <span class="tx-12"> <strong class="fw-normal">I accept the <a class="text-secondary" href="javascript:void(0)" data-toggle="modal" data-backdrop="static" autocomplete="off" data-keyboard="false" data-target="#EnrollmentAgreement">Terms of Enrollment</a></strong></span> 
                        <asp:CustomValidator ID="CustomValidatorChkAcceptTerms" runat="server"
    
    OnServerValidate="CustomValidatorChkAcceptTerms_ServerValidate"
    ClientValidationFunction="CheckBoxRequired_ClientValidate"
    ErrorMessage="Please accept the terms and conditions"
    ForeColor="Red" CssClass="error_msg_wrap" ValidationGroup="CreateStore"
    Display="Dynamic">
</asp:CustomValidator>
                  </div>
                    <asp:HyperLink ID="hlGoHome" runat="server" NavigateUrl="/signup" Visible="false" Text="Verify" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140"></asp:HyperLink>
                   </div>
                  <div class="col-12 d-flex flex-wrap justify-content-end">
                   <div class="formtbtn"><asp:Button ID="btnSubmitAccount" ValidationGroup="CreateStore" OnClientClick="if(validateCreateStore()){$(this).closest('form').attr('childobj', this.id);}else{return false;}" runat="server" CssClass="btn btn-primary btn-block btn-drk-green mx-w-140" Text="Create Store" OnClick="btnSubmitAccount_Click" />
                  </div>
                  </div>
              </div>

            </div><!--loginform_wrap-->                    
    <asp:SqlDataSource ID="SDSBusinessCategories" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT * FROM retaline_business_category bc WHERE Store_group_Id=0 AND `status`=1 AND EXISTS(SELECT * FROM finascop_business_type bt WHERE FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
    ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>

                <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt WHERE EXISTS(SELECT * FROM retaline_business_category bc WHERE business_category_id= @catid AND Store_group_Id=0 AND FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
                ProviderName="MySql.Data.MySqlClient"><SelectParameters><asp:ControlParameter ControlID="selBusinessTypes" ConvertEmptyStringToNull="false" Name="catid" /></SelectParameters></asp:SqlDataSource>

<asp:SqlDataSource ID="SDSState" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state ORDER BY name ASC" ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID ORDER BY dst_Name ASC">
        <SelectParameters><asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" /></SelectParameters></asp:SqlDataSource>


        <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
                        <asp:HiddenField ID="hidLat" runat="server" />
                        <asp:HiddenField ID="hidLong" runat="server" />
                        <asp:HiddenField ID="HiddenField1" runat="server" />

    <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />

        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        { %>

<script src="https://cdn.getaddress.io/scripts/getaddress-find-2.0.0.min.js"></script>
<script>
    getAddress.find(
        'postcode_lookup_signup',
        'kYRwv8Lf0kKFQlypo1P9pw38210',
        {
            input: {
                id: 'getaddress_input',  /* The id of the textbox' */
                name: 'getaddress_input',  /* The name of the textbox' */
                class: 'form-control mb-3 me-0 me-sm-3',  /* The class of the textbox' */
                label: 'Enter Postcode'  /* The label of the textbox' */
            },
            button: {
                id: 'getaddress_button',  /* The id of the botton' */
                class: 'btn btn-primary btn-drk-green mb-3 ms-0 ms-sm-1',  /* The class of the botton' */
                label: 'Find Address',  /* The label of the botton' */
                disabled_message: 'Find Address'  /* The disabled message of the botton' */
            },
            dropdown: {
                id: 'getaddress_dropdown',  /* The id of the dropdown' */
                class: 'form-control min-margin-8',  /* The class of the dropdown' */
                select_message: 'Select your Address',  /* The select message of the dropdown' */
                template: ''  /* The suggestion template of the dropdown' (see Autocomplete API)*/
            },
        }
    );
    document.addEventListener("getaddress-find-address-selected", function (e) {
        const result = [
            e.address.line_1,
            e.address.line_2,
            e.address.line_3,
            e.address.district,
        ]
            .filter((elem) => elem !== "")
            .join(", ");
        $("#<%= txtAddr1UK.ClientID%>").val(result);

        $("#<%= selState.ClientID%> option").filter(function () {
            return $(this).text() == e.address.country;
        }).prop("selected", true);
        $("#<%= hidDistrict.ClientID%>").val(e.address.county);
        $("#<%= hidLat.ClientID%>").val(e.address.latitude);
        $("#<%= hidLong.ClientID%>").val(e.address.longitude);
        $("#<%= txtPinCode.ClientID%>").val(e.address.postcode);
        $('#<%= ctrlAddressMap1.LocationTxtClientId%>').val(result);
        $('#<%= txtLocation.ClientID%>').val(result);

        $('#<%= selState.ClientID %>').change();

    });

</script>


    <%} %>




<script type="text/javascript">
    function CheckBoxRequired_ClientValidate(sender, e)
    {
        e.IsValid = $("#<%= chkAcceptTerms.ClientID %>").is(':checked');
    }

    function selectstate(stateval) {
        var optionval = $('#<%= selState.ClientID %>').find("option:contains('" + stateval + "')").val();
            if (optionval && optionval != '') {
                $("#<%= selState.ClientID %>").val(optionval);
                if (typeof (Page_ClientValidate) == 'function') {
                    Page_ClientValidate('novalidate');
                }
            $('#<%= selState.ClientID %>').change();
        }
    }

</script>

<div class="modal fade" id="EnrollmentAgreement">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content tx-size-sm">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h5 class="modal-title tx-dark mb-0">GROZEO GLOBAL HOLDINGS LTD TERMS AND CONDITIONS OF SERVICE</h5>
      </div>
      <div class="modal-body pd-20">
          <div class="row row-sm">
        <div class="col-12">    

          <p>These Terms and Conditions ("Terms") constitute a legal agreement between you and Grozeo ("Grozeo," "we," "our," or "us") and govern your access to and use of our website, app, and other software solutions (collectively, the "Services"). By accessing or using the Services, you agree to be bound by these Terms.</p>

          <h6>1. Accepting the Terms</h6>
          <p>By accessing or using the Services, you agree to these Terms and our Privacy Policy, which is incorporated into these Terms by reference. If you do not agree to these Terms or the Privacy Policy, you must not use or access the Services.</p>

          <h6>2. Changes to the Terms</h6>
          <p>We may modify these Terms from time to time. If we make material changes to these Terms, we will notify you by posting the updated Terms on our website or through other reasonable means. Your continued use of the Services after the effective date of the updated Terms constitutes your agreement to the updated Terms.</p>

          <h6>3. Use of the Services</h6>
          <p>You may use the Services only for lawful purposes and in accordance with these Terms. You must not use the Services in any way that violates any applicable federal, state, local, or international law or regulation (including, without limitation, any laws regarding the export of data or software to and from the United Kingdom or other countries).</p>

          <h6>4. Account Registration</h6>
          <p>To access certain features of the Services, you may be required to create an account with Grozeo. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete. You are responsible for maintaining the confidentiality of your account and password and for restricting access to your computer or mobile device. You agree to accept responsibility for all activities that occur under your account or password.</p>

          <h6>5. Intellectual Property</h6>
          <p>The Services and all content and materials included on or otherwise made available through the Services, including, without limitation, the Grozeo logo, all designs, text, graphics, pictures, information, data, software, sound files, other files and the selection and arrangement thereof (collectively, "Grozeo Content") are the property of Grozeo or its licensors or users and are protected by United Kingdom and international copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>

          <h6>6. User Content</h6>
          <p>You may submit or upload content, including but not limited to, text, images, videos, and other materials (collectively, "User Content") to the Services. By submitting or uploading User Content to the Services, you grant Grozeo a non-exclusive, transferable, sub-licensable, royalty-free, worldwide license to use, copy, modify, create derivative works based on, distribute, publicly display, publicly perform, and otherwise exploit in any manner such User Content in all formats and distribution channels now known or hereafter devised (including in connection with the Services and Grozeo's business and on third-party sites and services), without further notice to or consent from you, and without the requirement of payment to you or any other person or entity.</p>

          <h6>7. Prohibited Uses</h6>
          <p style="margin-bottom: 0;">You may use the Services only for lawful purposes and in accordance with these Terms. You agree not to use the Services:</p>
          <ul>
            <li style="margin-bottom: .5rem;">In any way that violates any applicable federal, state, local, or international law or regulation.</li>
            <li style="margin-bottom: .5rem;">For the purpose of exploiting, harming, or attempting to exploit or harm minors in any way by exposing them to inappropriate content, asking for personally identifiable information, or otherwise.</li>
            <li style="margin-bottom: .5rem;">To transmit, or procure the sending of, any advertising or promotional material, including any "junk mail," "chain letter," "spam," or any other similar solicitation.</li>
            <li style="margin-bottom: .5rem;">To impersonate or attempt to impersonate Grozeo, a Grozeo employee, another user, or any other person or entity (including, without limitation, by using email addresses or screen names associated with any of the foregoing).</li>
            <li style="margin-bottom: .5rem;">To engage in any other conduct that restricts or inhibits anyone's use or enjoyment of the Services, or which, as determined by Grozeo, may harm Grozeo or users of the Services or expose them to liability.</li>
          </ul>

          <h6>8. Termination</h6>
          <p>Grozeo may terminate your access to and use of the Services at any time and for any reason without notice or liability to you. Upon any termination, discontinuation, or cancellation of the Services or your account, the following provisions of these Terms will survive: Sections 5 through 12.</p>

          <h6>9. Disclaimer of Warranties</h6>
          <p>THE SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT. GROZEO DOES NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED OR ERROR-FREE, THAT DEFECTS WILL BE CORRECTED, OR THAT THE SERVICES OR THE SERVER(S) THAT MAKE THE SERVICES AVAILABLE ARE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS. GROZEO DOES NOT WARRANT OR MAKE ANY REPRESENTATIONS REGARDING THE USE OR THE RESULTS OF THE USE OF THE SERVICES IN TERMS OF THEIR CORRECTNESS, ACCURACY, RELIABILITY, OR OTHERWISE. YOU ASSUME ALL RESPONSIBILITY AND RISK FOR YOUR USE OF THE SERVICES.</p>

          <h6>10. Limitation of Liability</h6>
          <p>IN NO EVENT SHALL GROZEO, ITS DIRECTORS, OFFICERS, EMPLOYEES, OR AGENTS BE LIABLE TO YOU OR ANY THIRD PARTY FOR ANY DAMAGES WHATSOEVER, INCLUDING, WITHOUT LIMITATION, INDIRECT, INCIDENTAL, SPECIAL, PUNITIVE, OR CONSEQUENTIAL DAMAGES, ARISING OUT OF OR IN CONNECTION WITH YOUR USE OR INABILITY TO USE THE SERVICES, EVEN IF GROZEO HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. GROZEO'S LIABILITY TO YOU FOR ANY CAUSE WHATSOEVER, AND REGARDLESS OF THE FORM OF THE ACTION, WILL AT ALL TIMES BE LIMITED TO THE AMOUNT PAID, IF ANY, BY YOU TO GROZEO FOR THE SERVICES DURING THE TERM OF YOUR USE OF THE SERVICES.</p>

          <h6>11. Indemnification</h6>
          <p>You agree to indemnify, defend, and hold harmless Grozeo, its affiliates, licensors, and service providers, and its and their respective officers, directors, employees, contractors, agents, licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to your violation of these Terms or your use of the Services.</p>
          
          <h6>12. Governing Law and Jurisdiction</h6>
          <p>These Terms and any dispute or claim arising out of or in connection with them or their subject matter or formation (including non-contractual disputes or claims) shall be governed by and construed in accordance with the laws of the United Kingdom. The courts of the United Kingdom shall have exclusive jurisdiction to settle any dispute or claim that arises out of or in connection with these Terms or their subject matter or formation (including non-contractual disputes or claims).</p>

          <h6>13. Entire Agreement and Severability</h6>
          <p>These Terms constitute the entire agreement between you and Grozeo regarding the Services and supersede all prior and contemporaneous agreements, proposals, or representations, whether written or oral. If any provision of these Terms is held to be invalid, illegal, or unenforceable in any respect under any applicable law or rule in any jurisdiction, such invalidity, illegality, or unenforceability will not affect the validity, legality, or enforceability of any other provision of these Terms, and these Terms will be reformed, construed, and enforced in such jurisdiction as if such invalid, illegal, or unenforceable provision had never been contained herein.</p>

          <h6>14. Waiver</h6>
          <p>No waiver of any provision of these Terms will be deemed a further or continuing waiver of such provision or any other provision, and any failure to assert any right or provision under these Terms will not constitute a waiver of such right or provision.</p>

          <h6>15. Assignment</h6>
          <p>You may not assign or transfer these Terms or any rights granted hereunder, by operation of law or otherwise, without Grozeo's prior written consent, and any attempt by you to do so without such consent will be null and of no effect. Grozeo may assign or transfer these Terms or any rights granted hereunder without restriction or notification.</p>

          <h6>16. Third-Party Services</h6>
          <p>The Services may contain links to or integrate with third-party websites, applications, and services ("Third-Party Services") that are not owned or controlled by Grozeo. Grozeo does not endorse or assume any responsibility for any such Third-Party Services. If you access any Third-Party Services from the Services, you do so at your own risk and you agree that Grozeo will have no liability arising from your use of or access to any Third-Party Services.</p>

          <h6>17. Export Control</h6>
          <p>You agree to comply with all applicable export and import control laws and regulations of the United Kingdom and other applicable jurisdictions, and not to transfer, export, or re-export directly or indirectly, any content, including software, provided through the Services to any country or destination prohibited by such laws and regulations.</p>

          <h6>18. Survival</h6>
          <p>The provisions of these Terms that by their nature should survive the termination of these Terms shall survive such termination, including but not limited to Sections 5 through 12.</p>

          <h6>19. Apps</h6>
          <ol>
            <li style="margin-bottom: .5rem;">App Store Terms: You acknowledge that your use of the Services on the Apple App Store or Android App Store is subject to the terms and conditions of the respective app store. You agree to comply with all applicable terms and conditions of the app store and acknowledge that these terms and conditions are incorporated by reference into these Terms.</li>
            <li style="margin-bottom: .5rem;">App Store License: You acknowledge that the license granted to you under these Terms is limited to the right to use the Services as provided by Grozeo and does not include any right to use the Services on the Apple App Store or Android App Store. You agree to comply with all applicable app store license terms and conditions.</li>
            <li style="margin-bottom: .5rem;">End User License Agreement: If Grozeo provides an End User License Agreement (EULA) for the Services on the Apple App Store or Android App Store, you agree to comply with the terms and conditions of the EULA, which is incorporated by reference into these Terms.</li>
            <li style="margin-bottom: .5rem;">App Store Disclaimer: Grozeo disclaims any and all warranties or liabilities related to the Services on the Apple App Store or Android App Store, and you acknowledge that Grozeo has no responsibility for the availability, performance, or security of the Services on these platforms.</li>
            <li style="margin-bottom: .5rem;">App Store Review and Rating: If you choose to rate or review the Services on the Apple App Store or Android App Store, you agree to do so in compliance with the terms and conditions of the app store and in a manner that is accurate, truthful, and does not violate any applicable laws or regulations.</li>
          </ol>

          <h6>20. Cross-Sales and Promotions</h6>
          <ol>
            <li style="margin-bottom: .5rem;">The User acknowledges and agrees that Grozeo will provide cross-sales and promotional activities with third-party entities ("Partners"). These activities may offer the User and User customers the opportunity to engage with, purchase, or subscribe to products or services offered by Partners.</li>
            <li style="margin-bottom: .5rem;">In facilitating these cross-sales and promotional endeavors, the User expressly consents to the sharing of their business, their customer, and order information with Partners, solely for the purposes of order fulfillment, product delivery, and enhancing the User's experience with the Services and marketing related activities. </li>
          </ol>

          <h6>21. Data Utilization for Customer Sign-Up Simplification</h6>
          <ol>
            <li style="margin-bottom: .5rem;">For the purpose of easing the process of User and User Customer sign-up on Partner platforms, Grozeo may store Users' and User's customer data, including, but not limited to, personal information, contact details, address, and preferences, within the Grozeo database infrastructure.</li>
            <li style="margin-bottom: .5rem;">By consenting to these Terms, the User grants Grozeo the explicit authority to utilize such stored data to simplify the User's and User customer sign-up and authentication process on third-party platforms, leveraging Grozeo's technology.</li>
            <li style="margin-bottom: .5rem;">The User and User Customers retains the right to opt-out of such data sharing and usage at any point. To exercise this right, the User shall contact Grozeo via the designated support channels. Further information on data management practices is delineated in the Grozeo Privacy Policy.</li>
          </ol>

          <h6>22. Responsibilities and Liabilities</h6>
          <p>The User acknowledges that any transactions, interactions, or engagements with Partners through cross-sales or promotional activities are governed by the terms and conditions of the respective Partners. Grozeo disclaims any liability for the products, services, or content provided by Partners.</p>
          <p>The User agrees to indemnify and hold harmless Grozeo, its affiliates, officers, agents, and employees from any claim, demand, loss, damage, cost, or liability arising from the User's and User's Customer engagement in cross-sales or promotions or from the sharing or use of the User's and User’s Customer data in accordance with these Terms.</p>

          <h6>23. Amendment of Terms</h6>
          <p>Grozeo reserves the right to amend these Terms at any time to reflect changes to its cross-sales, cross-promotions, and data-sharing practices. Such amendments will be effective immediately upon posting the revised Terms on the Grozeo website or notifying Users through other reasonable means. Continued use of the Services following such modifications constitutes the User's acceptance of the new Terms.</p>

          <h6>Contact Us</h6>
          <p>If you have any questions about these Terms or the Services, please contact us at support@grozeo.com or info@grozeo.com</p>
          <p>Thank you for using Grozeo!</p>

        </div><!--col-12-->

          </div>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
