<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Add Retailer" Async="true" AutoEventWireup="true" CodeBehind="AddRetailer.aspx.cs" Inherits="RetalineProAgent.AddRetailer" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Add Retailer</h6>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/CRMRetailers">Retailer</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Retailer</li>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="section-wrapper">
          <%--<label class="section-title">Create New Contact</label>--%>
          <div class="form-layout">
            <div class="row mg-b-5">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="form-control-label">Enter Code: <span class="tx-danger">*</span></label>
                        <div class="d-flex pl-0">
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary d-inline-block w-auto ml-2" runat="server"
                                OnClick="lbtnSearch_Click">Search</asp:LinkButton>
                        </div>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtSearch" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Add Code" ValidationGroup="InsertRetailer" ForeColor="Red"></asp:RequiredFieldValidator>
                    </div>
                </div>
                <!-- col-4 -->
                <div class="col-sm-6 mb-3">
                            <div class="form-group-sm">
                                <label class="form-control-label">Store Name: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtStoreName" runat="server" Enabled="false" CssClass="form-control" autocomplete="off" />
                            </div>
                        </div>
                <div class="col-sm-2 mb-3" runat="server" id="dvCreateCode" visible="false">
                            <div class="form-group-sm">
                                <label class="form-control-label">Invitation Code: <span class="tx-danger">*</span></label>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <asp:TextBox ID="txtGneratedCode" runat="server" Enabled="false" CssClass="form-control" autocomplete="off" />
                            </div>
                        </div>
            </div><!-- row -->
            <div class="form-layout-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnRetailerSubmit_Click" CssClass="btn btn-success" Text="Submit" ValidationGroup="InsertRetailer"/>&nbsp;
            <a href="/Business/CRMRetailers" class="btn btn-secondary">Cancel</a>
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

    
    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Business/CRMRetailers";
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

    <style>
        .select2.select2-container {
            width:100%!important;
        }
    </style>

</asp:Content>
