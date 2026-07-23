<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Settlement Applications" CodeBehind="Settlementaplications.aspx.cs" Inherits="RetalineProAgent.Finance.Settlementaplications" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Navigations/Accounting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Settlement Application</h6>
    <p class="mb-0">Application For New Settlement Account </p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
     <div class="card-body">
        <div class="table-responsive ">
            <asp:GridView ID="gvSettementappliactions" runat="server" GridLines="None" DataSourceID="SDSSettlementapplications"  BorderColor="#ECECEC" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                <Columns>
                    <asp:BoundField HeaderText="Store Group" DataField="StoreGroup" />
                    <asp:BoundField HeaderText="Date Filling"  DataFormatString="{0:dd/MMM/yyyy}" DataField="createdOn" NullDisplayText="Not Submitted" />
                    <asp:TemplateField HeaderText="Contact Person">
                        <ItemTemplate>
                              <span class='<%# Eval("accountId") == DBNull.Value ? "mark-red" : "" %>'>
                              <%# Eval("SubAcName") == DBNull.Value ? Eval("br_Name") : Eval("SubAcName") %>
                        </ItemTemplate>
                    </asp:TemplateField>  
                    <asp:TemplateField HeaderText="Contact Number">
                        <ItemTemplate>
                            <%# Eval("contactNumber") == DBNull.Value ? Eval("br_phone") : Eval("contactNumber") %>
                        </ItemTemplate>
                    </asp:TemplateField>     
                    <asp:TemplateField HeaderText="Status">
                        <ItemTemplate>
                            <%# 
                        (Eval("status") ?? "").ToString() == "1" ? "Approved" :
                        (Eval("status") ?? "").ToString() == "2" ? "<span style='color:red;font-weight:bold;'>Under Review</span>" :
                        (Eval("status") ?? "").ToString() == "3" ? "Submitted" :
                        (Eval("status") ?? "").ToString() == "4" ? "Rejected" :
                        "Not Submitted"
                            %>
                        </ItemTemplate>
                    </asp:TemplateField>
                    <asp:TemplateField HeaderText="Action" ItemStyle-HorizontalAlign="Left">
                        <ItemTemplate>
                        <asp:LinkButton runat="server" ID="btnview"  CommandArgument='<%# string.IsNullOrEmpty(Eval("accountId")?.ToString()) ? "OID:" + Eval("order_id") : "ID:" + Eval("id") %>' Visible='<%# !string.IsNullOrEmpty(Eval("accountId")?.ToString()) %>' OnClick="btnview_Click"><i class="fa-thin fa-eye"></i></asp:LinkButton>
                        </ItemTemplate>
                    </asp:TemplateField>
                </Columns>
                <EmptyDataTemplate>
                    <div class="text-center">
                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                        <h6 class="mb-3">No account added</h6>
                    </div>
                </EmptyDataTemplate>
                <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
            </asp:GridView>
            <asp:SqlDataSource ID="SDSSettlementapplications"  runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
            SelectCommand ="SELECT ms.id, bg.store_group_name AS StoreGroup,o.order_id,c.createdOn, ms.status,c.accountId,ms.name AS SubAcName, ms.contactNumber, ms.contactEmail, b.br_ID, b.br_Name, b.br_City,br_phone, o.order_order_id, o.order_group_id, o.status_id FROM retaline_customer_order o INNER JOIN finascop_branch b ON o.order_branch_id=b.br_ID INNER JOIN finascop_branch_group bg ON b.br_storegroup = bg.store_group_id
                            LEFT JOIN store_paymentgateway_connect c ON c.branchId=b.br_ID LEFT JOIN MerchantSubaccount ms ON ms.StorePgconnectId = c.id  AND ms.Status <> 3 WHERE o.status_id = 18 GROUP BY br_ID" ProviderName="MySql.Data.MySqlClient">               
            </asp:SqlDataSource>
        </div><!-- table-responsive -->        
    </div><!-- card-body -->
    <div class="modal" id="razorpayaccountDetails" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h4 class="modal-title"> Settlement Sub Account Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Name For Account:</label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtcontactname" runat="server" CssClass="form-control border-0" placeholder="Enter Contact Name For Account" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton7" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                                <asp:HiddenField ID="hdngetpaymentgatewayid" runat="server" />
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Number:</label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtcontactnumber" ClientIDMode="Static" runat="server" CssClass="form-control border-0" placeholder="Enter Contact Number" autocomplete="nofill"></asp:TextBox>
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="lbcontactnimberverify" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Email:</label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtcontactemail" runat="server" CssClass="form-control border-0" placeholder="Enter Contact Email" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton1" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Bank Account No: </label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtbankaccountNo" runat="server" CssClass="form-control border-0" placeholder="Enter Bank Account No" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton2" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">IFSC: </label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtifsc" runat="server" CssClass="form-control border-0" placeholder="Enter IFSC" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton3" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="form-control-label mb-1 w-100 tx-dark">Beneficiary Name:</label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtAccountname" runat="server" CssClass="form-control border-0" placeholder="Enter Account Name" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton6" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Bank Name : </label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtbankname" runat="server" CssClass="form-control border-0" placeholder="Enter Bank Name" autocomplete="nofill" onkeypress="return allowAlphanumericUnderscore(event)" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton4" OnClientClick="return false;">
                                   <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <!-- col-4 -->

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="w-100 text-left tx-dark">Bank Branch:</label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtbranch" runat="server" CssClass="form-control border-0" placeholder="Enter Bank Branch" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton5" OnClientClick="return false;">
                                     <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="w-100 text-left tx-dark">Beneficiary Legal Type: </label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtbeneficiarytype" runat="server" CssClass="form-control border-0" placeholder="Enter Beneficiary Type" autocomplete="nofill" />
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn m-0 p-1 px-2 d-flex align-items-center copytoclipbord" ID="LinkButton8" OnClientClick="return false;">
                                     <i class="fa-light fa-copy" title="Copy"></i>
                                    </asp:LinkButton>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="w-100 text-left tx-dark">GateWay Account Id: </label>
                                <asp:TextBox ID="txtpaymentgatewayaccountId" runat="server" CssClass="form-control"  autocomplete="nofill" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtpaymentgatewayaccountId" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Enter GateWay Account Id" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>

                            </div>
                        </div>

                        <div class="col-sm-6 d-flex align-items-center">
                            <div class="ckboxsec d-flex w-100 mr-4">
                                 <label class="rdiobox mr-3">
                                        <asp:RadioButton ID="rbActivated" runat="server" AutoPostBack="false" GroupName="LocationGroup" />
                                        <span class="p-0">Activated</span>
                                    </label>
                                <label class="rdiobox mr-3">
                                        <asp:RadioButton ID="rbDeactivated" runat="server" AutoPostBack="false" GroupName="LocationGroup" onclick="showPopup();" />
                                        <span class="p-0 text-danger"><strong>Rejected</strong></span>
                                    </label>                             
                            </div>
                            <div class="btn_sec">
                                <asp:LinkButton runat="server" CssClass="btn btn-primary" ID="btnSavebankdetails" OnClick="btnSavebankdetails_Click" ValidationGroup="ValueHead">Submit</asp:LinkButton>
                            </div>
                        </div>
                    </div>
                </div>               
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusPopup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog w-100" role="document">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title">Reason for Reject</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <asp:TextBox ID="txtReason" runat="server" CssClass="form-control" placeholder="Enter reason..." />
                </div>
                <div class="modal-footer">
                    <asp:Button ID="btnSaveReason" runat="server" OnClick="btnSaveReason_Click" CssClass="btn btn-primary" Text="Save" />
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
       document.body.addEventListener('click', function (e) {
           var btn = e.target.closest('.copytoclipbord');
           if (btn) {
               e.preventDefault();
               var textbox = btn.closest('.input-group').querySelector('input, textarea');
               if (textbox) {
                   navigator.clipboard.writeText(textbox.value)
                       .then(() => {
                           var msg = document.createElement('span');
                           msg.innerText = 'Copied!';
                           msg.style.position = 'absolute';
                           msg.style.top = '-20px';
                           msg.style.left = '50%';
                           msg.style.transform = 'translateX(-50%)';
                           msg.style.background = '#000';
                           msg.style.color = '#fff';
                           msg.style.padding = '2px 5px';
                           msg.style.fontSize = '11px';
                           msg.style.borderRadius = '3px';
                           msg.style.opacity = '0';
                           msg.style.transition = 'opacity 0.3s';
                           btn.style.position = 'relative';
                           btn.appendChild(msg);
                           // Show message
                           requestAnimationFrame(() => { msg.style.opacity = '1'; });
                           // Remove after 1s
                           setTimeout(() => {
                               msg.style.opacity = '0';
                               msg.addEventListener('transitionend', () => msg.remove());
                           }, 1000);
                       })
                       .catch(err => console.error("Failed to copy:", err));
               }
           }
       });

        function showPopup() {
            var pgModal = new bootstrap.Modal(document.getElementById('statusPopup'));
            pgModal.show();
        }        
    </script>
  <script type="text/javascript">
    var grid = document.getElementById('<%= gvSettementappliactions.ClientID %>');
    if (grid) {
        var markers = grid.querySelectorAll('.mark-red');
        markers.forEach(function(span) {
            var row = span.closest('tr');
            if (row) row.classList.add('null-account-row');
        });
    }
  </script>




    <style>
        .form-group .input-group {
            background:#e9ecef!important;
        }
            .copy-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #28a745;
        color: #fff;
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        opacity: 1;
        transition: opacity .5s ease-in-out;
        z-index: 9999;
    }
    .copy-toast.fade-out {
        opacity: 0;
    }
        .modal-backdrop.show + div + span + live-preview-root + .modal-backdrop.show,
        .modal-backdrop.show + div + span + .modal-backdrop.show,
        .modal-backdrop.show + span + div + .modal-backdrop.show{
                z-index: 1050;
        }
        #statusPopup {
                z-index: 1051;
        }
        #statusPopup .modal-dialog {
            max-width: 400px;
        }  
        .null-account-row {
    background-color: #f8d7da; /* red background */
    color: #721c24;            /* dark red text */
         }

    </style>

</asp:Content>  
