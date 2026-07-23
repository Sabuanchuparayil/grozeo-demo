<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ManageBusinessInfo.aspx.cs" Inherits="RetalineProAgent.ManageBusinessInfo" %>


<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
        <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item"><a href="/tenant/store/storesettings">Business Details</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Business Settings</li>--%>
        <%--<a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
        <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    </asp:PlaceHolder>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Edit Business Settings</h6>
    <p class="m-0">Business Settings</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body p-3 shadow_top">

            <div class="form-layout">

                <%--<label class="slim-card-title">Business Settings</label>--%>
                <%--<p class="mg-b-20 mg-sm-b-40">Please input the store short, display name and select business types.</p>--%>

                <div class="row row-sm mg-b-5">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-control-label">Business Name: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtDisplayName" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder="" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDisplayName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Business name is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-6 -->

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-control-label">Contact person name: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtContactName" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact person name is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-6 -->



                    <%--              <asp:Panel ID="pnlBCategories" Visible="false" runat="server" CssClass="col-lg-4">              
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label">Business Category: <span class="tx-danger">*</span></label>
                  <asp:DropDownList ID="selBusinessTypes" AutoPostBack="true" data-placeholder="Choose business type" required runat="server" AppendDataBoundItems="true" 
                      OnDataBound="selBusinessTypes_DataBound" DataSourceID="SDSBusinessCategories" DataTextField="business_category_name" DataValueField="business_category_id"
                          CssClass="form-control" style="width: 100%;" ><asp:ListItem Text="Select Business Category" Value=""></asp:ListItem></asp:DropDownList>
                </div>
              </asp:Panel><!-- col-4 -->
              <asp:Panel ID="pnlRCategories" Visible="false" runat="server" CssClass="col-lg-8 mg-t-20 mg-lg-t-0">              
                  <label class="form-control-label">Retail Categories:</label>
                      <asp:ListBox ID="lstBusinessTypes" SelectionMode="Multiple" OnDataBound="lstBusinessTypes_DataBound" runat="server" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                          CssClass="form-control select2" multiple="multiple" ></asp:ListBox>
              </asp:Panel><!-- col-4 -->--%>
                </div>
                <%--<asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="SDSBusinessTypes_Selecting"
    SelectCommand="SELECT business_type_id,business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt 
    WHERE EXISTS(SELECT * FROM retaline_business_category bc WHERE business_category_id= @catid AND Store_group_Id=0 AND FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0) AND NOT EXISTS(SELECT * FROM finascop_branch_group_business_type WHERE business_type_id = bt.business_type_id AND store_group_id=@storeid)"
    ProviderName="MySql.Data.MySqlClient">
    <SelectParameters><asp:ControlParameter ControlID="selBusinessTypes" ConvertEmptyStringToNull="false" Name="catid" />
        <asp:Parameter Name="storeid" DefaultValue="0" /></SelectParameters></asp:SqlDataSource>--%>

                <%--<asp:SqlDataSource ID="SDSBusinessCategories" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
    SelectCommand="SELECT * FROM retaline_business_category bc WHERE Store_group_Id=0 AND `status`=1 AND EXISTS(SELECT * FROM finascop_business_type bt WHERE FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
    ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>--%>

                <asp:SqlDataSource ID="SDSSelectedBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                    SelectCommand="SELECT bt.business_type_id,bt.business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt
                    INNER JOIN finascop_branch_group_business_type sbt ON bt.business_type_id = sbt.business_type_id AND sbt.store_group_id=@storeid"
                    OnSelecting="SDSSelectedBusinessTypes_Selecting">
                    <SelectParameters>
                        <asp:Parameter Name="storeid" DefaultValue="0" />
                    </SelectParameters>
                </asp:SqlDataSource>

                <div class="row row-sm mg-b-5">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-control-label">Contact Email: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtContactEmail" runat="server" CssClass="form-control" placeholder="" TextMode="Email" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactEmail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact email is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-control-label">Contact Phone: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtContactPhone" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtContactPhone" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact phone is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-4 -->

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="form-control-label">Contact Address: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtAddr" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" placeholder="" />
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact address is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!-- col-12 -->

                    <div class="col-12 mb-3">
                        <label class="form-control-label">Selected Retail Categories:</label>
                        <a href="/Tenant/managebusinesstype" onclick="return confirm('The current page will be redirected to a new page. Any modifications made before to saving will be lost. Are you sure?');" style="float: right;">Manage</a>
                        <asp:ListBox ID="lstSelectedTypes" SelectionMode="Multiple" runat="server" DataSourceID="SDSSelectedBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                            CssClass="form-control select2" multiple="multiple" disabled="disabled" OnDataBound="lstSelectedTypes_DataBound"></asp:ListBox>
                    </div>
                    <!-- col-12 -->
                </div>
                <div class="row row-sm mg-b-5">
                    <div class="col-lg-3">
                        <div class="form-group p-2 border rounded border-secondary">

                            <label class="form-control-label">Checkout</label>
                            <div class="d-flex mt-2">
                                <label class="rdiobox mr-4" id="lblChkenable">
                                    <asp:RadioButton ID="rbCheckoutEnabled" runat="server" GroupName="rbCheckout" />
                                    <span>Enabled</span>
                                </label>
                                <label class="rdiobox">
                                    <asp:RadioButton ID="rbCheckoutDisabled" runat="server" Checked="true" GroupName="rbCheckout" />
                                    <span>Disabled</span>
                                </label>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="d-flex p-2 flex-wrap flex-sm-nowrap border border-secondary rounded mb-3">
                            <div class="form-group w-100 mb-0">
                                <label class="form-control-label">Online Payment</label>
                                <div class="d-flex mt-2">
                                    <label class="rdiobox mr-4">
                                        <asp:RadioButton ID="rbPayOnlineEnabled" runat="server" GroupName="rbPayOnline" />
                                        <span>Enabled</span>
                                    </label>
                                    <label class="rdiobox">
                                        <asp:RadioButton ID="rbPayOnlineDisabled" runat="server" GroupName="rbPayOnline" />
                                        <span>Disabled</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group w-100 mb-0 mt-3 mt-sm-0">
                                <label class="form-control-label">Pay on Delivery</label>
                                <div class="d-flex mt-2">
                                    <label class="rdiobox mr-4" id="lblPODenable">
                                        <asp:RadioButton ID="rbPODEnabled" runat="server" GroupName="rbPOD" />
                                        <span>Enabled</span>
                                    </label>
                                    <label class="rdiobox" id="lblPODdisable">
                                        <asp:RadioButton ID="rbPODDisabled" runat="server" GroupName="rbPOD" />
                                        <span>Disabled</span>
                                    </label>
                                </div>

                            </div>
                        </div>

                    </div>


                    <div class="col-lg-3" runat="server" id="showSponsored" visible="false">
                        <div class="form-group p-2 border rounded border-secondary">
                            <label class="form-control-label">Sponsored Products</label>
                            <div class="d-flex mt-2">
                                <label class="rdiobox mr-4">
                                    <asp:RadioButton ID="rdEnabled" runat="server" GroupName="rbgSponsored" />
                                    <span>Enabled</span>
                                </label>
                                <label class="rdiobox">
                                    <asp:RadioButton ID="rdDisabled" runat="server" GroupName="rbgSponsored" />
                                    <span>Disabled</span>
                                </label>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="row row-sm mg-b-5">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form-control-label">Facebook: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtFBUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Facebook url" />

                        </div>
                    </div>
                    <!-- col-4 -->
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form-control-label">X (Twitter): <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtTwitterUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Twitter url" />
                        </div>
                    </div>
                    <!-- col-4 -->

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form-control-label">Instagram: <span class="tx-danger">*</span></label>
                            <asp:TextBox ID="txtInstaUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Instagram url" />
                        </div>
                    </div>
                    <!-- col-4 -->


                </div>

                <div class="form-layout-footer">
                    <asp:Button runat="server" ID="btnEditStore" OnClick="btnEditStore_Click" CssClass="btn btn-primary bd-0" Text="Submit" ValidationGroup="AddStore" />&nbsp;
                <a href="/Navigations/SettingsMenu" class="btn btn-secondary bd-0">Cancel</a>
                    <br />
                    <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />

                </div>
                <!-- form-layout-footer -->
            </div>
            <!-- form-layout -->

        </div>

    </div>

    <!-- BASIC MODAL -->
    <!-- modal -->


    <div id="modaldemo5" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-body tx-center pd-y-20 pd-x-20">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
                    <h4 class="tx-danger mg-b-20">
                        <asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
                    <p class="mg-b-20 mg-x-20">
                        <asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal>
                    </p>
                    <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-body tx-center pd-y-20 pd-x-20">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
                    <h4 class="tx-success tx-semibold mg-b-20">
                        <asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
                    <p class="mg-b-20 mg-x-20">
                        <asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal>
                    </p>

                    <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
                </div>
                <!-- modal-body -->
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <script type="text/javascript">
        $(function () {
            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = '/Tenant/ManageBusinessInfo'
            });
        });

    </script>

    <% if (!rbCheckoutEnabled.Enabled)
        {  %>
    <script>
        $('#lblChkenable').on("click", function () {
            if ($('#<%= rbCheckoutEnabled.ClientID %>').prop('disabled')) {
                alert("This activity is linked to pending tasks awaiting completion. Kindly utilize the navigation button located at the top of the screen to ensure all pending tasks are addressed before proceeding.");
                return false;
            }
        });

    </script>
    <% } %>

    <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        {  %>
    <script>
        $('#lblPODenable, #lblPODdisable').on("click", function () {
            alert("POD is not available at the moment.");
            return false;
        });

    </script>
    <% } %>

    <script>
        $(document).ready(function () {
            $('.select2').select2();

            //Bootstrap Duallistbox
            //$('.duallistbox').bootstrapDualListbox();
            $('#<%= lstSelectedTypes.ClientID%>').prop("disabled", true);
        });
        $('form').on('submit', function () {
            $('#<%= lstSelectedTypes.ClientID%>').prop("disabled", false);
        });
            <%--$('#<%= selBusinessTypes.ClientID%>').on('change', function () {
                $('#<%= lstSelectedTypes.ClientID%>').prop("disabled", false);
            });--%>


        $('#<%= rbCheckoutEnabled.ClientID %>').click(function () {
            if ($(this).prop('disabled')) {
                alert("This activity is linked to pending tasks awaiting completion. Kindly utilize the navigation button located at the top of the screen to ensure all pending tasks are addressed before proceeding.");
                return false;
            }

            var cnfrm = confirm("This action will enable placement of orders in your store. Please make sure that all pending actions are completed and there is no pending action alert listed for you in the top banner, before proceeding. Otherwise the action will be discarded. Are you certain you wish to proceed?");
            if (cnfrm != true) {
                return false;
            }
            $('#<%= rbPayOnlineDisabled.ClientID %>').prop("disabled", false);
            $('#<%= rbPODDisabled.ClientID %>').prop("disabled", false);
            $('#<%= rbPayOnlineEnabled.ClientID %>').prop("disabled", false);
            $('#<%= rbPODEnabled.ClientID %>').prop("disabled", false);

            $('#<%= rbPayOnlineEnabled.ClientID %>').prop("checked", true);
            return true;
        });
        $('#<%= rbCheckoutDisabled.ClientID %>').click(function () {
            if ($(this).prop('disabled')) {
                alert("This activity is linked to pending tasks awaiting completion. Kindly utilize the navigation button located at the top of the screen to ensure all pending tasks are addressed before proceeding.");
                return false;
            }

            var cnfrm = confirm("This action will enable placement of orders in your store. Please make sure that all pending actions are completed and there is no pending action alert listed for you in the top banner, before proceeding. Otherwise the action will be discarded. Are you certain you wish to proceed?");
            if (cnfrm != true) {
                return false;
            }
            $('#<%= rbPayOnlineDisabled.ClientID %>').prop("checked", true);
            $('#<%= rbPODDisabled.ClientID %>').prop("checked", true);

            $('#<%= rbPayOnlineDisabled.ClientID %>').prop("disabled", true);
            $('#<%= rbPODDisabled.ClientID %>').prop("disabled", true);
            $('#<%= rbPayOnlineEnabled.ClientID %>').prop("disabled", true);
            $('#<%= rbPODEnabled.ClientID %>').prop("disabled", true);

            return true;
        });


    </script>

    <style>
        .position-relative {
            position: relative;
        }

        .position-absolute {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }

        .custom-label-width {
            height: 50px;
            display: inline-block;
            white-space: pre-line;
            word-wrap: break-word;
            padding: 5px;
        }
    </style>

</asp:Content>
