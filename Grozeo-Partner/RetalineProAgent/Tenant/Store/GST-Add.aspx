<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" EnableViewState="true" CodeBehind="GST-Add.aspx.cs" Inherits="RetalineProAgent.GST_Add" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/store/gst"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></a></li>
    <li class="breadcrumb-item active" aria-current="page">Add <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></li>--%>
    <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
    <a href=" /Tenant/Store/GST"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
   
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> Add <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %> Account</h6>
        <p class="mb-0">Verify <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %> with OTP</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <asp:PlaceHolder ID="plcAddGST" runat="server">
        <div class="card">
            <div class="card-body p-3 shadow_top">
            <p class="mb-2">Input <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %> and submit for verification.</p>

            <div class="form-layout">
                <div class="row row-sm">
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label class="w-100 text-left tx-dark"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %>: <span class="tx-danger">*</span></label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtGST" CssClass="form-control" runat="server" autocomplete="off"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtGST" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="GSTIN / VAT is required" ValidationGroup="ADDGST" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-12 mt-2">
                        <div class="d-inline-block">
                            <asp:Button ID="btnAddGST" runat="server" Text="Submit" OnClick="btnAddGST_Click" CssClass="btn btn-primary mr-1" ValidationGroup="ADDGST" />
                            <a href="/Tenant/store/gst" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    <!-- col-4 -->
                </div>
                <!-- row -->

                <div class="form-layout-footer">
                    <asp:Label ID="lblResult" runat="server"></asp:Label>
                </div>
                <!-- form-layout-footer -->

            </div>
            <!-- form-layout -->
        </div>
        <!-- card body -->
        </div>
        
</asp:PlaceHolder>

<asp:PlaceHolder ID="plcGSTOTP" runat="server" Visible="false">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <p class="mb-2 tx-dark">We have sent an OTP (One Time Password) to the mobile number <asp:Literal ID="ltrGSTMaskedMobile" runat="server"></asp:Literal> and Email ID 
                        <asp:Literal ID="ltrGSTMaskedEmail" runat="server"></asp:Literal> attached to your <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %>.&nbsp;
                        <asp:LinkButton ID="lbtnResentOTP" runat="server" CssClass="tx-bold" OnClick="lbtnResentOTP_Click">Send OTP again</asp:LinkButton>
                    </p>
                    <div class="form-layout">
                        <div class="row row-sm form-row">

                            <div class="input-group mb-3 w-100">
                                <input id="txtGSTNum" runat="server" type="text" class="form-control col-12 col-lg-6" name="Phone" placeholder="" disabled>
                            </div>

                            <div class="onetime_mobile_otpcod">

                                <div id="otp" class="inputs d-flex flex-row justify-content-between mb-2 otpinput" style="margin-left: -6px; margin-bottom: 0 !important;">
                                    <div class="input-group wd-100">
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <input runat="server" class="m-2 text-center form-control rounded-0 border-left-0 border-right-0 border-top-0 tx-28" required type="text" id="go1" maxlength="1" autocomplete="off" />
                                    </div>
                                    <div class="input-group wd-100">
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <input runat="server" class="m-2 text-center form-control rounded-0 border-left-0 border-right-0 border-top-0 tx-28" required type="text" id="go2" maxlength="1" autocomplete="off" />
                                    </div>
                                    <div class="input-group wd-100">
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <input runat="server" class="m-2 text-center form-control rounded-0 border-left-0 border-right-0 border-top-0 tx-28" required type="text" id="go3" maxlength="1" autocomplete="off" />
                                    </div>
                                    <div class="input-group wd-100">
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <input runat="server" class="m-2 text-center form-control rounded-0 border-left-0 border-right-0 border-top-0 tx-28" required type="text" id="go4" maxlength="1" autocomplete="off" />
                                    </div>

                                </div>

                            </div>
                            <!--onetime_mobile_otpcod-->

                        </div>

                        <div class="form-row">
                            <div class="col-12 mt-4 ">
                                <asp:Button ID="btnGSTOTPVerify" runat="server" CssClass="btn btn-primary btn-inline-block" Text="Submit" OnClick="btnGSTOTPVerify_Click" />
                                <asp:HyperLink runat="server" NavigateUrl="/Tenant/store/gst" CssClass="btn btn-secondary btn-inline-block ml-3">Cancel</asp:HyperLink>
                            </div>
                        </div>

                        <div class="form-layout-footer">
                            <asp:Label ID="lblOTPResult" runat="server"></asp:Label>
                        </div>
                        <!-- form-layout-footer -->

                    </div>
                    <!-- form-layout -->
                </div>
            </div>
        </div>
    </div>




</asp:PlaceHolder>

<asp:PlaceHolder ID="plcSignupGSTSuccess" runat="server" Visible="false">
<div class="section-wrapper">
          <label class="section-title">Add <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> Account</label>
          <p class="mg-b-20 mg-sm-b-40"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> verification completed.</p>

          <div class="form-layout">
            <div class="row mg-b-25">
            </div><!-- row -->

                    <div class="form-layout-footer">
                        <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %> verification is completed. Please go to the <a href="/Tenant/store/gst"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> list</a> to view the new <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> added.
                    </div><!-- form-layout-footer -->

          </div><!-- form-layout -->
        </div><!-- section-wrapper -->

</asp:PlaceHolder>



<div id="modaldemo1" class="modal fade">

      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold"><asp:Literal ID="ltrPopupTitle" runat="server"></asp:Literal></h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-25">
              <asp:Literal ID="ltrModelBodyContent" runat="server"></asp:Literal>
            <%--<h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Why We Use Electoral College, Not Popular Vote</a></h5>
            <p class="mg-b-5">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>--%>
          </div>
          <div class="modal-footer">
              <asp:Button CssClass="btn btn-primary" ID="btnSaveChanges" runat="server" Text="Save Account" />
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

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
                window.location.href = "/Tenant/store/gst";
            });
        });

        function OTPInput() {
            const inputs = document.querySelectorAll('#otp > div > *[id]');
            for (let i = 0; i < inputs.length; i++) { inputs[i].addEventListener('keydown', function (event) { if (event.key === "Backspace") { inputs[i].value = ''; if (i !== 0) inputs[i - 1].focus(); } else { if (i === inputs.length - 1 && inputs[i].value !== '') { return true; } else if (event.keyCode > 47 && event.keyCode < 58) { inputs[i].value = event.key; if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } else if (event.keyCode > 64 && event.keyCode < 91) { inputs[i].value = String.fromCharCode(event.keyCode); if (i !== inputs.length - 1) inputs[i + 1].focus(); event.preventDefault(); } } }); }
        } OTPInput();


        var autofocusobj = '<asp:Literal ID="ltrAutoFocusObj" runat="server"/>';
        $(document).ready(function () {
            if (autofocusobj != '')
                $('#' + autofocusobj).focus();
        });


    </script>


</asp:Content>

