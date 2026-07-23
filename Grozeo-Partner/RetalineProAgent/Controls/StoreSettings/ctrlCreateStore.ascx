<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlCreateStore.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlCreateStore" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>

        <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>


<div class="card card-body">

          <div class="form-layout">

        <asp:PlaceHolder ID="plcColStoreInfo" runat="server">
          <label class="slim-card-title">Business Settings</label>
          <%--<p class="mg-b-20 mg-sm-b-40">Please input the store short, display name and select business types.</p>--%>

            <div class="row mg-b-5">
              <div class="col-lg-7">
                <div class="form-group">
                  <label class="form-control-label">Business Name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtDisplayName" ReadOnly="true" runat="server" required CssClass="form-control" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder=""/>
                </div>
              </div><!-- col-4 -->
<div class="col-lg-5" runat="server" visible="false">
                <div class="form-group">
                  <label class="form-control-label">Locate store in map: <span class="tx-danger">*</span></label>
                  <div class="input-group input-group"><asp:HiddenField ID="hidMapAddr" runat="server" />
						<asp:TextBox ID="txtLocation" runat="server" ReadOnly="true" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS" required CssClass="form-control" placeholder="Click to load map"/>                  
                  <span class="input-group-append">
                    <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS">Load Map</button>
                  </span>
                </div>
                        <asp:HiddenField ID="hidLat" runat="server" />
                        <asp:HiddenField ID="hidLong" runat="server" />
                </div>
              </div>

              <div runat="server" visible="false" class="col-lg-5">
                <div class="form-group">
                  <label class="form-control-label">Preferred Store Name for URL (max 17 characters): <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtStoreName" runat="server" required CssClass="form-control" MaxLength="17" onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder=""/>
                    <small><asp:Label ClientIDMode="Static" Visible="false" id="lblCustomDomain" runat="server"></asp:Label></small>
                </div>
              </div><!-- col-4 -->
                    <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name,IF((status=1),'Active','Inactive') AS status FROM finascop_business_type"
                ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>
            </div><!-- row -->
        </asp:PlaceHolder>

              <asp:PlaceHolder ID="plcColPBranchInfo" runat="server">
              <%--<label class="slim-card-title">Store Settings</label>--%>
          <%--<div><small class="mg-b-20 mg-sm-b-40">Please ensure the branch location selected in map using the button 'Load Map'.</small></div>--%>

            <div class="row mg-b-5">
              <!-- col-4 -->

              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Store name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtAddr1" runat="server" required CssClass="form-control"  onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder=""/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-9">
                <div class="form-group">
                  <label class="form-control-label">Store Address: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" required placeholder=""/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Pin Code: <span class="tx-danger">*</span></label>
                   <asp:TextBox ID="txtPinCode" TextMode="Number" MaxLength="6" runat="server" required CssClass="form-control" placeholder=""/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label"><%=RetalineProAgent.Service.Common.StateLabel %>: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selState" OnSelectedIndexChanged="selState_SelectedIndexChanged" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" required DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="true" ><asp:ListItem Text="Select State" Value=""></asp:ListItem></asp:DropDownList>
                </div><asp:HiddenField ID="hidState" runat="server" />
              </div><!-- col-4 -->
              <div class="col-lg-5">
                <div class="form-group">
                  <label class="form-control-label"><%=RetalineProAgent.Service.Common.DistrictLabel %>: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" required DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="false" ><asp:ListItem Text="Select District" Value=""></asp:ListItem></asp:DropDownList>
                </div><asp:HiddenField ID="hidDistrict" runat="server" />
              </div><!-- col-4 -->
<asp:PlaceHolder runat="server" Visible="false">
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Bank A/c Number: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtBankAcNo" runat="server" CssClass="form-control" placeholder="Enter Bank account number"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-3">
                <div class="form-group">
                  <label class="form-control-label">Branch IFSC: <span class="tx-danger">*</span></label>
                  <div class="input-group">
						<asp:TextBox ID="txtIFSC" runat="server" CssClass="form-control" placeholder="Enter IFSC"/>
                        <div class="input-group-append"><asp:LinkButton runat="server" CssClass="input-group-text" ID="ISFCSearch" OnClick="ISFCSearch_Click"><i class="fa fa-search"></i></asp:LinkButton></div>
                  </div>
                </div>
              </div><!-- col-4 -->
</asp:PlaceHolder>
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" />
                <div class="col-lg-12"><small><asp:Literal ID="ltrIFSCSearch" runat="server"></asp:Literal></small></div>
            </div><!-- row -->

</asp:PlaceHolder>

                          <div class="row mg-b-5">
              <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label">Primary Retail Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selBusinessTypes" data-placeholder="Choose business type" required runat="server" AppendDataBoundItems="true" OnDataBound="selBusinessTypes_DataBound" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control" style="width: 100%;" ><asp:ListItem Text="Select Primary Retail Category" Value=""></asp:ListItem></asp:DropDownList>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-9 mg-t-20 mg-lg-t-0">
                  <label class="form-control-label">Additional Retail Categories:</label>
                      <asp:ListBox ID="lstBusinessTypes" SelectionMode="Multiple" OnDataBound="lstBusinessTypes_DataBound" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
              </div><!-- col-4 -->


                          </div>


            <div class="form-layout-footer">
              <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Submit" ValidationGroup="AddStore"/>&nbsp;
                <a href="/Tenant/Store/StoreSettings" class="btn btn-secondary bd-0">Cancel</a>
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>

            </div><!-- form-layout-footer -->
          </div><!-- form-layout -->
        </div>

<asp:SqlDataSource ID="SDSState" runat="server" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID">
        <SelectParameters><asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" /></SelectParameters></asp:SqlDataSource>

    
    <div class="col-md-6" id="dvColStoreAdditionalSettings" visible="false" runat="server">

            <div class="card card-info">  
                <div class="card-header">
                <h3 class="card-title">Additional Settings (Optional)</h3>
              </div>
              <div class="card-body">
                  <div class="row">
                                 <div class="col-md-6 form-group">
                                            <label>Site Logo (small)</label>
											<asp:FileUpload ID="uploadLogoWhite" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogoWhite" runat="server" style="max-width: 40px; max-height: 40px; width: auto; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogoWhite" runat="server" Visible="false" Text="Delete?" />
                                        </div>
                                                 <div class="col-md-6 form-group">
                                            <label>Original Logo</label>
											<asp:FileUpload ID="uploadLogo" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogo" runat="server" style="max-width: 40px; max-height: 40px; width: auto; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogo" runat="server" Visible="false" Text="Delete?" />
                                        </div>
</div>
                                <%--<div class="form-group">
                                            <label>Custom colour (optional)</label>
                                        </div>--%>

                  <div class="form-group">
                  <label>Custom colour (optional)</label>

                  <div class="input-group my-colorpicker2">
                    <%--<input type="text" class="form-control">--%>
					<asp:TextBox ID="txtColor" runat="server" CssClass="form-control" placeholder="Click to select color"/>

                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fa fa-square"></i></span>
                    </div>
                  </div>
                  <!-- /.input group -->
                </div>

                  
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
    <div class="col-md-6" id="dvColBranchStatutoryInfo" visible="false" runat="server">
              <div class="card card-info">  
                <div class="card-header">
                <h3 class="card-title">Statutory Info</h3>
              </div>
              <div class="card-body">

                  
                      <div class="row">
                    <div class="col-sm-6">

                    <div class="form-group">
                        <label><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> Number</label>
						<asp:TextBox ID="txtGSTNo" runat="server" CssClass="form-control" placeholder="Enter GST/VAT"/>
                    </div>
                       </div> 
                          <div class="col-sm-6">
                    <div class="form-group">
                        <label>Business PAN</label>
						<asp:TextBox ID="txtBPan" runat="server" CssClass="form-control" placeholder="Enter Business PAN"/>
                    </div>

                        </div>
                      </div>



                    <%--<div class="form-group">
                        <label>Bank Name</label>
						<asp:TextBox ID="txtBankName" runat="server" CssClass="form-control" placeholder="Enter Bank Name"/>
                    </div>
                    <div class="form-group">
                        <label>Bank Branch</label>
						<asp:TextBox ID="txtBankBranch" runat="server" CssClass="form-control" placeholder="Enter Bank Branch"/>
                    </div>--%>

                        

                  </div>

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

    <script type="text/javascript">
        $(function () {
            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = '/Tenant/InventoryMapping';
            });
        });

        function selectstate(stateval) {
            var optionval = $('#<%= selState.ClientID %>').find("option:contains('" + stateval + "')").val();
            if (optionval && optionval != '') {
                $("#<%= selState.ClientID %>").val(optionval);
                $('#<%= selState.ClientID %>').change();
            }
        }

    </script>
