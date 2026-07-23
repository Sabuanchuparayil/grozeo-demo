<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ManageBusinessSettings.aspx.cs" Inherits="RetalineProAgent.ManageBusinessSettings" %>

<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<%@ Register Src="~/Controls/PopupUpgradeConsent.ascx" TagPrefix="uc1" TagName="PopupUpgradeConsent" %>


<asp:Content ContentPlaceHolderID="head" runat="server">
     <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
     <script src="/content/lib/select2/js/select2.full.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <link href="/Content/lib/jt.timepicker/css/jquery.timepicker.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
    <script src="/Content/lib/jt.timepicker/js/jquery.timepicker.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/BusinessSettings"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Configuration</h6>
        <p class="mb-0">Store Management Made Easy</p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <uc1:PopupUpgradeConsent runat="server" ID="PopupUpgradeConsent1" UpgradeName="Add Store Consent" />
    <div class="card">
        <asp:PlaceHolder ID="plcStoreList" runat="server">
            <div class="card-header shadow_top" runat="server" visible="false">
                <div class="row row-sm mt-2">
                    <div class="col-12 col-md-6">
                        <h6 class="mb-1 tx-dark">Manage Stores and Business Settings</h6>
                        <%--<p class="mg-b-0">Stores registered. New store can be added using the add store option, based on the package selected.</p>--%>
                        <% if (this.CurrentUser.TenantType != 1)
                            {  %>
                        <p class="mg-b-0 text-info">The merchant account is registered as Affiliate. Only products without GST enabled will be listed.</p>
                        <% } %>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0 d-flex align-items-start justify-content-sm-end" style="gap: 10px;">
                        <asp:LinkButton runat="server" ID="btnAddStore" OnClick="btnAddStore_Click" CssClass="btn px-4 d-block d-md-inline-block btn-primary"> Add Store<i class="icon ion-plus-circled ml-2"></i></asp:LinkButton>
                        <%--<a href="/Tenant/ManageBusinessInfo" class="btn btn-primary btn-sm">Manage Business Settings</a>--%>
                    </div>
                </div>
            </div>
            <!-- card-header -->

            <div class="card-body bg-transparent" runat="server" visible="false">
                <div class="div_store_cout mb-3">
                    <div class="table-responsive bg-white">
                        <asp:Repeater runat="server" ID="rptBranches" OnItemDataBound="rptBranches_ItemDataBound" DataSourceID="ODSStore">
                            <HeaderTemplate>
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Store Name</th>
                                            <th>Location</th>
                                            <th>Contact No.</th>
                                            <th width="86px">Status</th>
                                            <th width="60px"></th>
                                            <th width="140"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            </HeaderTemplate>
                            <ItemTemplate>
                                <tr>
                                    <td>
                                        <%# Eval("BranchName") %><br />
                                        <small>
                                            <i runat="server" title="Courier Delivery" visible='<%# (Convert.ToString(Eval("CourierDelivery"))  == "1" ? true : false) %>' class="fa fa-truck" aria-hidden="true"></i>
                                            <i runat="server" title="Express Delivery" visible='<%# (Convert.ToString(Eval("DirectDelivery"))  == "1" ? true : false) %>' class="fa fa-motorcycle" aria-hidden="true"></i>
                                            <%= GSTLabel %>: <%# Eval("GSTIN") %>
                                            <%# (String.IsNullOrEmpty((string)Eval("FSSAI")) ? "" : 
                                    String.Format(", FSSAI: {0}", 
                                    (Eval("FSSAI").ToString().Contains("-")? 
                                      Eval("FSSAI").ToString().Substring(0, Eval("FSSAI").ToString().IndexOf("-")).Trim() 
                                      :Eval("FSSAI").ToString()))) %>
                                        </small></td>
                                    <td><a href="https://maps.google.com/?q=<%# Eval("Lat") %>,<%# Eval("Lng") %>" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;<%# Eval("Address") %>
                                        <br />
                                        <small>Bank: <%# Eval("Bank") %></small>, <i class="ion-ios-timer tx-purple"></i>&nbsp;<small>
                                            <asp:Repeater ID="rptTiming" runat="server">
                                                <ItemTemplate><%# Eval("OnTime") %> - <%# Eval("OffTime") %></ItemTemplate>
                                                <SeparatorTemplate>, </SeparatorTemplate>
                                            </asp:Repeater>
                                            <asp:Literal ID="ltrNoTiming" Visible="false" runat="server" Text="All time"></asp:Literal>
                                        </small>
                                    </td>
                                    <td>
                                        <%# Eval("Phone") %><br />
                                        <small>In Charge: <%# Eval("Incharge") %></small></td>
                                    <td data-bootstrap-switch>
                                        <div class="toggle-wrapper">
                                            <div class="toggle toggle-light success" data-toggle-on="<%# Eval("Status").Equals(1) ? "true" : "false" %>"></div>
                                        </div>
                                        <asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" Style="display: none;" AutoPostBack="true" runat="server" brid='<%# Eval("BranchId") %>' Checked='<%# Eval("Status").Equals(1) %>' />
                                    </td>
                                    <td runat="server" visible="true" style="vertical-align: middle;">
                                        <asp:LinkButton ID="lbtnEditStore" runat="server" brid='<%# Eval("DBBranchid") %>' OnClick="lbtnEditStore_Click" CssClass="btn btn-primary btn-sm"><i class="fa fa-pencil-alt mr-1"></i> Edit</asp:LinkButton>
                                        <%--<asp:HyperLink runat="server" ID="lnkEdit" CssClass="btn btn-primary btn-sm" Text="Edit" NavigateUrl='<%# String.Format("/BranchSettings?brid={0}&id={1}", Eval("BranchId"), DBStoreId((int)Eval("BranchId"))) %>'>
                      <i class="fa fa-pencil-alt"></i> Edit
                  </asp:HyperLink>--%>
                                        <%--&nbsp;<a href="javascript:void(0)" onclick="showAPI('<%# Eval("APIKey") %>', '<%# Eval("BranchName") %>')" Class="btn btn-info btn-sm">API</a>--%>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <a href="/tenant/deliveryrulesnew?brid=<%# Eval("BranchId") %>" class="btn btn-primary btn-sm"><i class="fa fa-pencil-alt mr-1"></i>Set Delivery Rule</a>
                                        <%--<asp:LinkButton ID="lblDelivRule" runat="server" brid='<%# Eval("BranchId") %>' OnClick="lblDelivRule_Click" CssClass="btn btn-primary btn-sm"><i class="fa fa-pencil-alt mr-1"></i>Set Delivery Rule</asp:LinkButton>--%>
                                    </td>
                                </tr>
                            </ItemTemplate>
                            <FooterTemplate>
                                </tbody></table>
                            </FooterTemplate>
                        </asp:Repeater>
                        <asp:ObjectDataSource ID="ODSStore" runat="server" OnSelecting="ODSStore_Selecting" TypeName="RetalineProAgent.Services.StoreService" SelectMethod="GetStores">
                            <SelectParameters>
                                <asp:Parameter Name="storegroupid" />
                                <asp:Parameter Name="apistoregroupid" />
                                <asp:Parameter Name="all" DefaultValue="true" Type="Boolean" />
                            </SelectParameters>
                        </asp:ObjectDataSource>

                        <%--<label id="lblTest" runat="server"></label>--%>
                    </div>
                    <!-- table-responsive -->
                    <div class="w-100 text-right pr-1 mt-1">
                        <a style="color-rendering: red; text-decoration: underline;" class="" href="/Tenant/Branches">
                            <asp:Label ID="lblStoreCount" runat="server" />
                        </a>
                    </div>
                    <asp:SqlDataSource ID="SDSBranches" OnSelecting="SDSStore_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
                        SelectCommand="select br.*, ba.BankName, ba.AccountNumber, ba.Branch, ba.AccountName, gst.gstin from StoreBranch br left join gst on br.GSTId=gst.id left join BankAccount ba on br.BankId=ba.id
where br.StoreId=@storeId">
                        <SelectParameters>
                            <asp:Parameter Name="storeId" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>

            </div>

            <!--test2_div-->

            <div class="divManageBusinessSettings">

                <h6 class="mb-2 tx-dark">Manage Business Settings</h6>

                <div class="bg-white p-3 shadow_top rounded-10">


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

                    </div>

                    <div class="row row-sm">
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

                        <div class="col-12 ">
                            <label class="form-control-label">Selected Retail Categories:</label>
                            <a href="/Tenant/managebusinesstype" onclick="return confirm('The current page will be redirected to a new page. Any modifications made before to saving will be lost. Are you sure?');" style="float: right;">Manage</a>
                            <asp:ListBox ID="lstSelectedTypes" SelectionMode="Multiple" runat="server" DataSourceID="SDSSelectedBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id"
                                CssClass="form-control select2" multiple="multiple" disabled="disabled" OnDataBound="lstSelectedTypes_DataBound"></asp:ListBox>
                        </div>
                        <!-- col-12 -->
                        <%--</div>--%>
                    </div>
                    <div class="row row-sm mt-3">
                        <div class="col-12">
                            <div class="form-layout-footer">
                                <asp:Button runat="server" ID="btnEditStore" OnClick="btnEditStore_Click" CssClass="btn btn-primary bd-0" Text="Submit" ValidationGroup="AddStore" />&nbsp;
                           <a href="/Navigations/SettingsMenu" class="btn btn-secondary bd-0">Cancel</a>
                                <br />
                                <asp:Label ID="Label1" Font-Bold="true" runat="server" />
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </asp:PlaceHolder>
    </div>                                     
    <asp:PlaceHolder ID="plcStoreSettings" runat="server">
        <div class="card mb-3">
            <div class="card-body p-3 shadow_top">
                <div class="form-layout">

                    <label class="col-12 p-0 m-0 tx-dark">
                        <asp:Literal runat="server" ID="ltrAddTitle" Text="Add new store"></asp:Literal></label>
                    <div><small class="mg-b-20 mg-sm-b-40">Please ensure the branch location selected in map using the button 'Load Map'.</small></div>

                    <div class="row row-sm">
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark">Store Name: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtAddr1" runat="server" onchange="this.value = this.value.replace(/[\u{0080}-\u{FFFF}]/gu, '')" CssClass="form-control" placeholder="Enter Store Location" autocomplete="off" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr1" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store name / location is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <!-- col-4 -->

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 tx-dark">Store Contact Name: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtCnName" runat="server" CssClass="form-control" placeholder="Enter store contact name" autocomplete="off" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtCnName" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store contact name is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 tx-dark">Store Contact No.: <span class="tx-danger">*</span></label>
                                <div class="country_code_mobile d-flex align-items-center position-relative">
                                    <asp:TextBox ID="txtCnNo" runat="server" MaxLength="12" CssClass="form-control border-0 PhoneNumbercode restrictmobile" placeholder="Enter store contact no." TextMode="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="nofill" />
                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtCnNo" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store contact no. is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 tx-dark">Store Email: </label>
                                <asp:TextBox ID="txtCnEmail" runat="server" CssClass="form-control" placeholder="Store email" autocomplete="off" />
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark">Locate store in map: <span class="tx-danger">*</span></label>
                                <div class="input-group input-group loadmapbox" style="overflow: hidden;">
                                    <asp:HiddenField ID="hidMapAddr" runat="server" />
                                    <asp:TextBox ID="txtLocation" runat="server" ReadOnly="true" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS" CssClass="form-control rounded-0 border-0" placeholder="Click to load map" />
                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLocation" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Location is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-primary rounded-0 border-0 btn-flat" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#ADDRESS"><i class="fa fa-map-marker mr-1"></i>Map</button>
                                    </span>
                                </div>
                                <asp:HiddenField ID="hidLat" runat="server" />
                                <asp:HiddenField ID="hidLong" runat="server" />
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark">Building Number/ Name: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtAddr2" runat="server" CssClass="form-control" placeholder="Enter Store Address" autocomplete="off" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr2" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Store address is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark">Street/ Locality: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtAddr3" runat="server" CssClass="form-control" placeholder="Enter Store Address" autocomplete="off" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtAddr3" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Street is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark">Additional Address (Optional): <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtAddr4" runat="server" CssClass="form-control" placeholder="Enter Store Address" autocomplete="off" />
                            </div>
                        </div>
                        <!-- col-4 -->

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark"><%= CodeLabel %> : <%--Pin Code:--%> <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtPinCode" runat="server" CssClass="form-control" placeholder="Enter Pin Code" autocomplete="off" />
                                <asp:RequiredFieldValidator ID="rqdpincode" runat="server" ControlToValidate="txtPinCode" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Pin code is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6 col-lg-3" runat="server">
                            <div class="form-group mg-b-10-force">
                                <label class="form-control-label mb-1 w-100 tx-dark"><%=RetalineProAgent.Service.Common.StateLabel %> : <%--State:--%> <span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selState" OnDataBound="selState_DataBound" AutoPostBack="true" runat="server" DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                                    CssClass="form-control" Style="width: 100%;" AppendDataBoundItems="true">
                                    <%--<asp:ListItem Text="Select State" Value=""></asp:ListItem>--%>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ID="rfvstate" ControlToValidate="selState" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                            <asp:HiddenField ID="hidState" runat="server" />
                        </div>
                        <!-- col-4 -->

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label mb-1 w-100 tx-dark"><%=RetalineProAgent.Service.Common.DistrictLabel %> : <%--District:--%> <span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selDistrict" OnDataBound="selDistrict_DataBound" runat="server" DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                                    CssClass="form-control" Style="width: 100%;" AppendDataBoundItems="false">
                                    <%--<asp:ListItem Text="Select District" Value=""></asp:ListItem>--%>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ID="rfvdistrict" ControlToValidate="selDistrict" CssClass="error_msg_wrap" Display="Dynamic" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                            <asp:HiddenField ID="hidDistrict" runat="server" />
                        </div>
                        <!-- col-4 -->
                        <div class="col-lg-9">
                            <div class="row row-sm mg-b-5">
                                <div class="col-12 col-sm-5">
                                    <div class="form-group">
                                        <label class="form-control-label">Grozeo Area:</label>
                                        <asp:TextBox ID="txtGrozeoArea" runat="server" CssClass="form-control" placeholder="" />
                                        <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtGrozeoArea" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Grozeo area is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-7">
                                    <div class="form-group">
                                        <label class="form-control-label" style="width: 100%;">
                                            Relationship Officer:
                          <span class="addhsnpopup" data-toggle="modal" runat="server" data-target="#hsnsearch" style="float: right; font-weight: normal; color: #797867; cursor: pointer;">Add/Change</span></label>
                                        <asp:TextBox ID="txtRO" runat="server" CssClass="form-control" onchange="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" placeholder="" />
                                        <%--<asp:RequiredFieldValidator runat="server" ControlToValidate="txtRO" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Relatioship officer is required" ValidationGroup="AddStore" ForeColor="Red"></asp:RequiredFieldValidator>--%>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="col-12 col-lg-6">
                            <div class="form-group d-flex">
                                <label class="ckbox w-auto mr-4">
                                    <input id="cbexpressdelivery" checked runat="server"  type="checkbox">
                                    <span class="tx-bold">Express Delivery</span>
                                </label>

                                <label class="ckbox w-auto">
                                    <input id="cbcourierdelivery" checked runat="server" class="chk check_delivery_share" type="checkbox">
                                    <span class="tx-bold">Courier Delivery</span>
                                </label>
                            </div>                            
                        </div>

                        <uc1:ctrlAddressMap runat="server" ID="ctrlAddressMap1" />
                        <div class="col-lg-12">
                            <small>
                                <asp:Literal ID="ltrIFSCSearch" runat="server"></asp:Literal></small>
                        </div>
                        <div class="col-12">
                            <div class="d-inline-block">
                                <asp:Button runat="server" ID="btnEdit" OnClick="btnEdit_Click" CssClass="btn btn-primary bd-0" Text="Save" />
                                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Submit" ValidationGroup="AddStore" />&nbsp;
                                <a href="/Tenant/branches" class="btn btn-secondary bd-0">Cancel</a>
                                <asp:Label ID="lblMessage" runat="server" />
                            </div>
                        </div>
                    </div>
                    <!-- row -->
                    <!-- form-layout-footer -->
                </div>
                <!-- form-layout -->
            </div>
            <!-- card-body -->
        </div>
        <!-- card -->



        <asp:SqlDataSource ID="SDSGst" OnSelecting="SDSStore_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
            SelectCommand="select gstin, id from GST where tenantid = @storeId">
            <SelectParameters>
                <asp:Parameter Name="storeId" />
            </SelectParameters>
        </asp:SqlDataSource>
        <asp:SqlDataSource ID="SDSBank" OnSelecting="SDSStore_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
            SelectCommand="select Id, BankName, AccountNumber, AccountName, Branch, AccountNumber + ' - ' + BankName + ' - ' + Branch as combinedName from BankAccount where TenantId = @storeId">
            <SelectParameters>
                <asp:Parameter Name="storeId" />
            </SelectParameters>
        </asp:SqlDataSource>


        <asp:SqlDataSource ID="SDSState" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state ORDER BY name ASC" ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>
        <asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
            SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID ORDER BY dst_Name ASC">
            <SelectParameters>
                <asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" />
            </SelectParameters>
        </asp:SqlDataSource>


        <div class="card">
            <div class="card-body" runat="server" id="onOffTime" visible="false">
                <div class="p-3 shadow_top">
                    <div class="row row-sm">
                        <div class="col-6">
                            <label class="col-12 p-0 m-0 tx-dark">On/Off Time</label>
                            <p class="mg-b-0">Store working time.</p>
                        </div>
                        <div class="col-6 d-flex align-items-start justify-content-end">
                            <a href="#" class="btn px-4 d-block d-md-inline-block btn-primary" data-toggle="modal" data-target="#modalonofftime">Add <i class="icon ion-plus-circled ml-2"></i></a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <asp:GridView ID="gvOnOffTime" runat="server" GridLines="None" DataKeyNames="id" DataSourceID="SDSOnOffTime" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                        <Columns>
                            <asp:BoundField HeaderText="Time From" DataField="br_open_time" />
                            <asp:BoundField HeaderText="Time To" DataField="br_close_time" />
                            <asp:TemplateField HeaderStyle-Width="55">
                                <ItemTemplate>
                                    <asp:Button runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this time?')" Text="Delete" CssClass="btn btn-block btn-danger btn-sm" formnovalidate />
                                </ItemTemplate>
                            </asp:TemplateField>
                        </Columns>
                        <EmptyDataTemplate>
                            <div class="text-center">
                                <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                <h6 class="mb-3">No record available</h6>
                            </div>
                        </EmptyDataTemplate>
                    </asp:GridView>
                    <asp:SqlDataSource ID="SDSOnOffTime" OnSelecting="SDSOnOffTime_Selecting" OnDeleting="SDSOnOffTime_Deleting" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT bt.* FROM branch_timings bt INNER JOIN finascop_branch AS b ON b.br_ID=bt.branch_id WHERE bt.branch_id=@brid AND b.br_storeGroup= @storegroupid order by br_open_time"
                        DeleteCommand="DELETE FROM branch_timings WHERE id=@id and branch_id in(select br_ID from finascop_branch where br_storeGroup= @storegroupid)">
                        <DeleteParameters>
                            <asp:Parameter Name="storegroupid" />
                        </DeleteParameters>
                        <SelectParameters>
                            <asp:Parameter Name="brid" DefaultValue="-1" />
                            <asp:Parameter Name="storegroupid" />
                        </SelectParameters>
                    </asp:SqlDataSource>

                </div>
                <!-- table-responsive -->
            </div>
            <!-- card-body -->
        </div>
    </asp:PlaceHolder>                  
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
    <div id="modalonofftime" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">

                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title tx-dark mb-3">Add Start and Close Time</h5>

                    <div class="row row-sm">

                        <div class="col-lg-6">
                            <div class="form-group">

                                <label class="tx-dark  mb-1">From:</label>
                                <div class="w-100">
                                    <div class="input-group">
                                        <div class="input-group-prepend timeinptbox">
                                            <div class="input-group-text rounded" style="background-color: #E5F0E3;">
                                                <i class="fa fa-clock-o tx-16 lh-0 op-6"></i>
                                            </div>
                                            <div>
                                                <asp:TextBox ID="txtTimeFrom" runat="server" CssClass="form-control timectrl border-0" placeholder="Time From" ValidationGroup="OnOffTime" />
                                                <asp:RequiredFieldValidator runat="server" Display="Dynamic" ErrorMessage="Time from is required" ControlToValidate="txtTimeFrom" ForeColor="Red" ValidationGroup="OnOffTime"></asp:RequiredFieldValidator>
                                            </div>
                                        </div>
                                        <!-- input-group-prepend -->
                                    </div>
                                </div>
                                <!-- wd-150 -->

                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">

                                <label class="tx-darck mb-1">To:</label>
                                <div class="w-100">
                                    <div class="input-group">
                                        <div class="input-group-prepend timeinptbox">
                                            <div class="input-group-text rounded" style="background-color: #E5F0E3;">
                                                <i class="fa fa-clock-o tx-16 lh-0 op-6"></i>
                                            </div>
                                            <div>
                                                <asp:TextBox ID="txtTimeTo" runat="server" CssClass="form-control timectrl border-0" placeholder="Time To" />
                                                <asp:RequiredFieldValidator runat="server" Display="Dynamic" ErrorMessage="Time to is required" ControlToValidate="txtTimeTo" ForeColor="Red" ValidationGroup="OnOffTime"></asp:RequiredFieldValidator>
                                            </div>
                                        </div>
                                        <!-- input-group-prepend -->

                                    </div>
                                </div>
                                <!-- wd-150 -->


                            </div>

                        </div>

                        <div class="col-12">
                            <div class="modal-btn d-inline-block">
                                <asp:Button ID="btnAddTime" CssClass="btn btn-primary mr-2" OnClick="btnAddTime_Click" ValidationGroup="OnOffTime" runat="server" Text="Save" formnovalidate />
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <%--<h5 class="lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Why We Use Electoral College, Not Popular Vote</a></h5>
            <p class="mg-b-5">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>--%>
                </div>
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
        <!-- modal -->
        <!-- BASIC MODAL -->
    <div id="hsnsearch" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 tx-18">
                <div class="modal-header">
                    <h4 class="modal-title" style="font-size: 16px; color: #333;">Add or Change Relationship Officer</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="section-wrapper p-0 border-0">
                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Connect with your Relationship Officer</label>
                                    <div class="d-flex">
                                        <asp:TextBox ID="txtMobile" runat="server" CssClass="form-control restrictmobile" placeholder="Provide the mobile number"></asp:TextBox>
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMobile" CssClass="error_msg_wrap b--0" Display="Dynamic" ErrorMessage="Enter a mobile number" ValidationGroup="InsertMobile" ForeColor="Red"></asp:RequiredFieldValidator>
                                        <asp:LinkButton ID="lnkGo" runat="server" CssClass="btn btn-inline-block btn-primary ml-2" Text="GO" ValidationGroup="InsertMobile" CausesValidation="true" OnClick="lnkGo_Click"></asp:LinkButton>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="position-relative">
                                    <asp:Label ID="lblRODetails" runat="server" CssClass="form-control custom-label-width" Visible="false"></asp:Label>
                                    <asp:HiddenField ID="hdnRoId" runat="server" />
                                    <asp:HiddenField ID="hdnId" runat="server" />
                                    <asp:HiddenField ID="hdbranchId" runat="server" />
                                    <asp:HiddenField ID="hdAreaId" runat="server" />
                                    <asp:HiddenField ID="hdnRoArea" runat="server" />
                                    <asp:HiddenField ID="hdnmobile" runat="server" />
                                    <asp:Button ID="btnConnect" runat="server" Visible="false" CssClass="btn btn-primary position-absolute" Text="Connect" OnClick="btnConnect_Click" Style="top: 10px; right: 10px;" ValidationGroup="InsertMobile" CausesValidation="true" />
                                    <asp:Button ID="btnHiddenConfirm" runat="server" Style="display: none;" OnClick="btnHiddenConfirm_Click" />
                                </div>
                            </div>
                        </div>
                        <!--row-->
                    </div>
                    <!--section-wrapper-->
                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
        <%--<label class="slim-card-title">Business Settings</label>--%>
        <%--<p class="mg-b-20 mg-sm-b-40">Please input the store short, display name and select business types.</p>--%>
            <!-- col-6 -->           
            <!-- col-6 -->
        <asp:SqlDataSource ID="SDSSelectedBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
            SelectCommand="SELECT bt.business_type_id,bt.business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt
INNER JOIN finascop_branch_group_business_type sbt ON bt.business_type_id = sbt.business_type_id AND sbt.store_group_id=@storeid"
            OnSelecting="SDSSelectedBusinessTypes_Selecting">
            <SelectParameters>
                <asp:Parameter Name="storeid" DefaultValue="0" />
            </SelectParameters>
        </asp:SqlDataSource>
        <style>
            .timeinptbox {
                width: 100%;
                display: flex;
                border: 1px solid #7f7f7f;
                border-radius: 8px !important;
            }

            .country_code_mobile .error_msg_wrap {
                bottom: -15px;
            }
        </style>

        <script type="text/javascript">
            $(function () {

                // hide modal with effect
                $('#modaldemo4').on('hidden.bs.modal', function (e) {
                    window.location.href = "/Tenant/ManageBusinessSettings";
                });

                $('.timectrl').timepicker();
            });
        

        
            $(function () {
                // hide modal with effect
                $('#modaldemo4').on('hidden.bs.modal', function (e) {
                    /*window.location.href ='/Tenant/ManageBusinessInfo'*/
                });
            });

    <% if (!rbCheckoutEnabled.Enabled)
        {  %>

            $('#lblChkenable').on("click", function () {
                if ($('#<%= rbCheckoutEnabled.ClientID %>').prop('disabled')) {
                alert("This activity is linked to pending tasks awaiting completion. Kindly utilize the navigation button located at the top of the screen to ensure all pending tasks are addressed before proceeding.");
                return false;
            }
        });

        
        <% } %>

        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        {  %>
        
            $('#lblPODenable, #lblPODdisable').on("click", function () {
                alert("POD is not available at the moment.");
                return false;
            });

        
        <% } %>

        
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
                      
       
            $('.toggle').toggles(
                {
                    //on: true,
                    height: 26
                },
                //checkbox:
            );
            $('.toggle').on('toggle', function (e, active) {
                $(this).closest('td').find('input[type=checkbox]').trigger('click');
                $(this).addClass('processing_loader');
                //if (active) {
                //    console.log('Toggle is now ON!');
                //} else {
                //    console.log('Toggle is now OFF!');
                //}
            });


            function selectstate(stateval) {
                var optionval = $('#<%= selState.ClientID %>').find("option:contains('" + stateval + "')").val();
            if (optionval && optionval != '') {
                $("#<%= selState.ClientID %>").val(optionval);
                 $('#<%= selState.ClientID %>').change();
                }
            }
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
