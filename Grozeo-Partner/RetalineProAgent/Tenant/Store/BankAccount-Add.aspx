<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BankAccount-Add.aspx.cs" Inherits="RetalineProAgent.BankAccount_Add" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/Store/BankAccount">Bank Details</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Bank Account</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
      <a href="/Tenant/Store/BankAccount"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle"> Add Bank Account</h6>
    <p class="mb-0">Input bank account info and submit for verification.</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">

          <div class="form-layout">
            <div class="row row-sm">
              <div class="col-lg-8">
                <div class="form-group">
                  <label class="w-100 text-left tx-dark"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "IBAN" : "Account Number:") %> <span class="tx-danger">*</span></label>                   
                    <asp:TextBox ID="txtAccountNumber" CssClass="form-control" runat="server" autocomplete="off"></asp:TextBox>
                    <asp:RequiredFieldValidator runat="server" ID="rfvAccountNumber" ControlToValidate="txtAccountNumber" CssClass="error_msg_wrap" Display="Dynamic"  ValidationGroup="AddAccount" ForeColor="Red"></asp:RequiredFieldValidator>
                </div> 
              </div><!-- col-4 -->
                <asp:PlaceHolder runat="server" ID="plcifc">
                    <div class="col-lg-4">
                <div class="form-group">
                  <label  class="w-100 text-left tx-dark"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort Code" : "Indian Financial System Code (IFSC)") %> : <span class="tx-danger">*</span></label>                 
                    <asp:TextBox ID="txtIFSC"  CssClass="form-control" runat="server" MaxLength="11" autocomplete="off"></asp:TextBox>
                    <asp:RequiredFieldValidator  runat="server" ControlToValidate="txtIFSC" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Sort / IFS is required" ValidationGroup="AddAccount" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>
              </div><!-- col-4 -->
                </asp:PlaceHolder>              
                <div class="col-12 mt-1">
                <div class="d-inline-block">
                        <asp:Button ID="btnAddBank" runat="server" Text="Submit" OnClick="btnAddBank_Click" CssClass="btn btn-primary mr-2" ValidationGroup="AddAccount" />
                        <a href="/Tenant/Store/BankAccount" class="btn btn-secondary bd-0">Cancel</a>
                    <asp:Label ID="lblResult" runat="server"></asp:Label>
                </div>
              </div>
            </div><!-- row -->


          </div><!-- form-layout -->
        </div><!-- card-body -->
    </div>



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
              <asp:Button CssClass="btn btn-primary" ID="btnSaveChanges" runat="server" OnClick="btnSaveChanges_Click" Text="Save Account" />
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

    <!-- Modal Structure -->
<div class="modal fade" id="modalBank" tabindex="-1" role="dialog" aria-labelledby="modaldemo4Label" aria-hidden="true">
  <div class="modal-dialog w-100" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modaldemo4Label">Account Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="mg-b-5">
             <div class="mb-2">
                <asp:Label runat="server" ID="txtaacountnumber"></asp:Label>
                 <asp:Label runat="server" ID="lblaccountnumber"></asp:Label>
             </div>
            <div class="align-items-center mb-2 input-group">
                <asp:Label runat="server" CssClass="mr-2" Text="<b> Account Name </b> :"></asp:Label>
                <div class="ac_name_inpt">
                      <asp:TextBox runat="server" CssClass="form-control" ID="txtaccountname"></asp:TextBox>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaccountname" CssClass="error_msg_wrap align-content-center" Display="Dynamic" ErrorMessage="Add Account name" ValidationGroup="accountname" ForeColor="Red"></asp:RequiredFieldValidator>
                </div>              
            </div>
              <div runat="server" id="bankdetalis">
                   <div class="mb-2"  >
                  <asp:Label Text="<b> Bank name </b> :" runat="server"></asp:Label>
                   <asp:Label runat="server" ID="lbBank"></asp:Label>
              </div>
              <div class="mb-2">
                  <asp:Label Text="<b> Branch </b> :" runat="server"></asp:Label>
                   <asp:Label runat="server" ID="lbbranch"></asp:Label>
              </div>
              <div class="mb-2">
                  <asp:Label Text="<b> Bankaddress </b> :" runat="server"></asp:Label>
                   <asp:Label runat="server" ID="lblbankaddress"></asp:Label>
              </div>
              </div>
             
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <asp:LinkButton runat="server" ID="btnshowpopup" OnClick="btnshowpopup_Click" ValidationGroup="accountname" Text="Continue" CssClass="btn btn-primary"></asp:LinkButton>
      </div>
    </div>
  </div>
</div>

    <style>
        .ac_name_inpt{
            width:calc(100% - 130px);
        }
    </style>


    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/store/bankaccount";
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Get a reference to the input field
            const inputField = document.getElementsByClassName('sixDigitCode');
            //console.log(inputField);

            // Add an event listener to listen for input changes
            inputField[0].addEventListener('input', function (event) {
                //console.log('jgjgjhg')
                // Get the input value
                let inputValue = event.target.value;

                // Remove any non-digit characters (e.g., hyphens)
                inputValue = inputValue.replace(/\D/g, '');

                // Add hyphen after every two characters
                inputValue = inputValue.replace(/(\d{2})(?=\d{2})/g, '$1-');

                // Update the input field value
                event.target.value = inputValue;
            });

        });
    </script>
</asp:Content>

