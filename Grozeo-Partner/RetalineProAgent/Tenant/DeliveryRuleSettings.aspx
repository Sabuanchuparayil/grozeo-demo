<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Delivery Rules" AutoEventWireup="true" CodeBehind="DeliveryRuleSettings.aspx.cs" Inherits="RetalineProAgent.DeliveryRuleSettings" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/DeliveryRules">Delivery Rules</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create New Delivery Rule</li>--%>
    <a href="/Tenant/DeliveryRules"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Create Delivery Rule</h6>
        <p class="mb-0">Create delivery rule</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body p-3 shadow_top">
            <%--<h6 class="mb-1">Create New Delivery Rule</h6>--%>
          <div class="form-layout">
            <div class="row row-sm">
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="form-control-label mb-1 w-100 tx-dark">Delivery Mode: <span class="tx-danger">*</span>
                  </label>
                  <asp:DropDownList ID="selDelivMode" runat="server" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="">Please Select</asp:ListItem>
                              <asp:ListItem Value="1">Courier</asp:ListItem>
                              <asp:ListItem Value="2">Express Delivery</asp:ListItem>
                              <asp:ListItem Value="3">Scheduled Delivery</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selDelivMode" ErrorMessage="Please select mode" ValidationGroup="Insert" Display="Dynamic"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-6">
                <div class="form-group">
                  <label class="form-control-label mb-1 w-100 tx-dark">Calculation Mode: <span class="tx-danger">*</span>
                  </label>
                  <asp:DropDownList ID="selCalMode" runat="server" AutoPostBack="true" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText">
                              <asp:ListItem Value="">Please Select</asp:ListItem>
                              <asp:ListItem Value="1">Distance Rate</asp:ListItem>
                              <asp:ListItem Value="2">Rate</asp:ListItem>
                          </asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ForeColor="Red" ControlToValidate="selCalMode" ErrorMessage="Please select type" ValidationGroup="Insert" Display="Dynamic"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
               
                <asp:PlaceHolder runat="server" ID="plHld1" Visible="false">
                    <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="fromid1">KM From</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtFrom1" runat="server"  CssClass="form-control" placeholder="0.00 KM" ValidationGroup="Insert" TextMode="Number" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtFrom1" ErrorMessage="KM From is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmid1">KM To</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtTo1" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 KM" ValidationGroup="Insert" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtTo1" ErrorMessage="KM To is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                    <asp:CompareValidator ValueToCompare="1" ControlToValidate="txtTo1" Operator="GreaterThanEqual" runat="server" ErrorMessage="KM To should be greater than 1" ForeColor="Red" ValidationGroup="Insert"></asp:CompareValidator>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmrsid1">KM <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %></asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRs1" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 Rs" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRs1" ErrorMessage="Amount is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                </asp:PlaceHolder>
                    
                <asp:PlaceHolder runat="server" ID="plHld2" Visible="false">
                    <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="fromid2">KM From</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtFrom2" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 KM" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmid2">KM To</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtTo2" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 KM" autocomplete="nofill"/>
                    <asp:CompareValidator ValueToCompare="1" ControlToValidate="txtTo2" Operator="GreaterThanEqual" runat="server" ErrorMessage="KM To should be greater than 1" ForeColor="Red" ValidationGroup="Insert"></asp:CompareValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmrsid2">KM <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %></asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRs2" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 Rs" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                </asp:PlaceHolder>
              
                <asp:PlaceHolder runat="server" ID="plHld3" Visible="false">
                    <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="fromid3">KM From</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtFrom3" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 KM" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmid3">KM To</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtTo3" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 KM" autocomplete="nofill"/>
                    <asp:CompareValidator ValueToCompare="1" ControlToValidate="txtTo3" Operator="GreaterThanEqual" runat="server" ErrorMessage="KM To should be greater than 1" ForeColor="Red" ValidationGroup="Insert"></asp:CompareValidator>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="kmrsid3">KM <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %></asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRs3" runat="server" TextMode="Number" CssClass="form-control" placeholder="0.00 Rs" autocomplete="nofill"/>
                </div>
              </div><!-- col-4 -->
                
                </asp:PlaceHolder>
                
                <asp:PlaceHolder runat="server" ID="plHld4" Visible="false">
                    <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="rateid">Rate / KM</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRate" TextMode="Number" runat="server" CssClass="form-control" placeholder="Rate / KM" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRate" ErrorMessage="Rate / KM is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                    <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="minrateid">Min. Rate</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtMinRate" TextMode="Number" runat="server" CssClass="form-control" placeholder="Min. Rate" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMinRate" ErrorMessage="Min. rate is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3-->
                <div class="col-lg-4">
                <div class="form-group">
                  <asp:Label CssClass="form-control-label mb-1 w-100 tx-dark" runat="server" ID="maxrateid">Max. Rate</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtMaxRate" TextMode="Number" runat="server" CssClass="form-control" placeholder="Max. Rate" autocomplete="nofill"/>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtMaxRate" ErrorMessage="Max. rate is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3 -->
                </asp:PlaceHolder>
                <div class="col-lg-2 mb-2 mb-lg-0">
                    <asp:CheckBox ID="isfree" TextAlign="Left" CssClass="mr-1" AutoPostBack="true" runat="server" Checked='<%# Eval("rdr_isfreeDeliveryCbx").Equals("Active") %>'/>
                <span class="form-control-label mb-0 w-100 tx-dark">Free Delivery</span>
                </div><!-- col-3 -->
                <div class="col-lg-2">
                <div class="form-group mb-3 mb-lg-0">
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRs" runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" CssClass="form-control" placeholder="Above Rs." autocomplete="nofill" Visible="false"/>
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRs" ErrorMessage="Above Rs. is required" Display="Dynamic" ValidationGroup="Insert" ForeColor="Red" Visible="false" ID="rsValid"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-3 -->
                <div class="col-lg-2 d-flex align-item-center chkbox_alig">
                    <asp:RadioButton ID="rbAllStores" CssClass="form-control-label mb-0 w-100 tx-dark mt-1" Checked="true" GroupName="rbgStore" onclick="if ($(this).is(':checked')) $('#dvselectstore').hide(); else $('#dvselectstore').show(); " runat="server" Text="All Stores" />
                </div><!-- col-3 -->
                <div class="col-lg-2 d-flex align-item-center chkbox_alig  mb-3 mb-lg-0">
                    <asp:RadioButton ID="rbSelectStore" CssClass="form-control-label mb-0 w-100 tx-dark mt-1" GroupName="rbgStore" onclick="if ($(this).is(':checked')) $('#dvselectstore').show(); else $('#dvselectstore').hide(); " runat="server" Text="Select Store" />
                </div><!-- col-3 -->
                <div class="col-lg-4 mb-3 mb-lg-0" id="dvselectstore" style='<%= rbAllStores.Checked ? "display: none" : "" %>'>
                  <%--<label class="form-control-label">Select Store:</label>--%>
                    <asp:DropDownList ID="selBranch" runat="server" AutoPostBack="True" CssClass="form-control select2 select2-hidden-accessible" ForeColor="GrayText" DataSourceID="SDSBranch" DataTextField="br_Name" DataValueField="br_ID"><asp:ListItem Text="Select store" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ID="SDSBranch" runat="server"
                SelectCommand="SELECT br_ID,br_Name FROM finascop_branch WHERE br_storeGroup=@storegroup"
                    OnSelecting="SDSBranch_Selecting"
                ProviderName="MySql.Data.MySqlClient">
                    <SelectParameters>
            <asp:Parameter Name="storegroup" />
        </SelectParameters>
                </asp:SqlDataSource>
              </div><!-- col-4 -->
                
                <div class="col-lg-12">
                <div class="form-group">
                    <asp:Label CssClass="form-control-label d-inline-block mb-1 w-100 tx-dark" runat="server" ID="lblRuleName">Rule Name</asp:Label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                  <asp:TextBox ID="txtRuleName" runat="server" CssClass="form-control" placeholder="Enter rule name" autocomplete="nofill" />
                  <asp:RequiredFieldValidator runat="server" ControlToValidate="txtRuleName" ForeColor="Red" ErrorMessage="Please enter name" ValidationGroup="Insert" Display="Dynamic"></asp:RequiredFieldValidator>

                </div>
              </div><!-- col-3 -->
            </div><!-- row -->
            <div class="mt-1">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary mr-2" Text="Submit" ValidationGroup="Insert"/>
                <a href="/Tenant/DeliveryRules" class="btn btn-secondary">Cancel</a>
            </div>
          </div><!-- form-layout -->
        </div><!-- card-body -->
        </div><!-- card -->

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
                window.location.href = "/Tenant/DeliveryRules";
            });
        });
    </script>

    <style>
        .chkbox_alig input {
            margin-right:5px;
        }
        .chkbox_alig label{
            margin:0px;
        }
    </style>
    
</asp:Content>