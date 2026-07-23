<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="PaymentConfig.aspx.cs" Inherits="RetalineProAgent.Tenant.PaymentConfig" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
     <a href="/Navigations/Settlement"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            Payment Gateway - <%= GetPaymentGatewayName() %>
        </h6>
        <p class="mb-0">Create / link your <%= GetPaymentGatewayName() %> payment gateway for accepting online payments.</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <asp:Panel ID="pnlNoneSubAccount" runat="server" CssClass="card">
            <div class="card-body">
        <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-lg-8">
                    <h6 class="tx-dark mb-1">Platform Payment Processing</h6>
                    <p class="tx-11">Common payment gateway is applicable for all transactions.</p>
                </div>
                <div class="col-lg-12 col-lg-12">
<p class="card-text mg-b-8 tx-12">Direct payment gateway connection is not provided at this stage. All transactions will be processed through the platform's <%= GetPaymentGatewayName() %> account.</p>
<p class="card-text mg-b-8 tx-12">Settlements to your bank account will be made after the settlement delay of business days, which is allotted to your merchant account as per our standard operating procedures.</p>
<p class="card-text mg-b-8 tx-12">Please note that gateway charges will be applied to each transaction. These charges vary based on your selected package and are in accordance with our platform's terms and conditions.</p>
<p class="card-text mg-b-8 tx-12">For more detailed information regarding settlement schedules, transaction charges, or alternative payment solutions, please contact our support team.</p>

                </div>
            </div>
        </div>
        </div>

    </asp:Panel>

    <asp:Panel ID="pnlSubAccounts" CssClass="card" runat="server">
    <div class="card-body">
        <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-lg-9">
                    <h6 class="tx-dark mb-1">Your <%= GetPaymentGatewayName() %> Payment Gateway Accounts</h6>
                    <p class="mg-b-0">List of the <%= GetPaymentGatewayName() %> payment gateway accounts added. The account can be linked with store/s to enable online transaction</p>
                </div>
                <div class="col-lg-3 mt-3 mt-lg-0 d-flex align-items-start justify-content-lg-end">                    
                    <a class="btn px-4 d-block d-md-inline-block btn-primary" href="javascript:void(0)" data-toggle="modal" data-target='<%= ConfigurationManager.AppSettings["PaymentGateway"] == "razorpay" ? "#razorpayaddaccount" : "#PGConnectModalPopup" %>'>Add Account <i class="icon ion-plus-circled ml-2"></i></a>
                </div>
            </div>
        </div>
        <div class="table-responsive ">
            <asp:GridView ID="gvConnectAccounts" runat="server" GridLines="None" DataSourceID="SDSLinkedAccount"  BorderColor="#ECECEC" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                <Columns>
                    <asp:BoundField HeaderText="Gate Way Name" DataField="PgName" />
                    <asp:BoundField HeaderText="Account #" DataField="accountId" NullDisplayText="Not Updated" />
                    <asp:BoundField HeaderText="Account Name" DataField="bankAccountName" />
                    <asp:TemplateField HeaderText="WithDrawal Bank"><ItemTemplate><%# String.Format("{0}-{1}", Eval("bankAccountNum"), Eval("bankName")) %></ItemTemplate></asp:TemplateField>
                    <asp:TemplateField HeaderText="Status">
                        <ItemTemplate>
                            <%# Eval("status").ToString() == "1" ? "Approved" :
                            Eval("status").ToString() == "2" ? "<span style='color:red;font-weight:bold;'>Under Review</span>" :
                            Eval("status").ToString() == "3" ? "Submitted" :
                            Eval("status").ToString() == "4" ? "Rejected <span class='tx-danger'>*</span><i class='fa-regular tx-info fa-circle-info tooltipinfo popover-trigger' tabindex='0' data-toggle='popover' title='Rejected due to' data-html='true' data-content=\"" + Eval("remark") + "\"></i>" :
                            "In Progress"
                            %>
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
            <asp:SqlDataSource ID="SDSLinkedAccount" OnSelecting="SDSLinkedAccount_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="Select *,(SELECT Remark FROM `MerchantSubaccount` ms WHERE ms.StorepgconnectId= sc.id) as remark from store_paymentgateway_connect sc  where storeGroupId=@storeId and `status` IN( 1,2,3,4)" ProviderName="MySql.Data.MySqlClient">
                <SelectParameters>
                    <asp:Parameter Name="storeId" />
                </SelectParameters>
            </asp:SqlDataSource>

            <asp:SqlDataSource ID="SDSStores" OnSelecting="SDSLinkedAccount_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT t1.br_ID, t1.br_Name, COALESCE(t2_match.id, t2_default.id) AS pgId, COALESCE(t2_match.accountId, t2_default.accountId) AS pgAccountId,    
    COALESCE(t2_match.status, t2_default.status) AS pgStatus, COALESCE(t2_match.bankName, t2_default.bankName) AS pgBankName,
    COALESCE(t2_match.bankAccountName, t2_default.bankAccountName) AS pgBankAccountName, COALESCE(t2_match.bankAccountNum, t2_default.bankAccountNum) AS pgBankAccountNum
FROM finascop_branch t1 LEFT JOIN store_paymentgateway_connect t2_match ON t1.br_storegroup = t2_match.storeGroupId AND `status`=1 AND FIND_IN_SET(t1.br_ID, t2_match.branchId) > 0
LEFT JOIN (SELECT * FROM store_paymentgateway_connect WHERE storeGroupId=@storeId AND `status`=1 ORDER BY id LIMIT 1) t2_default ON t2_default.storeGroupId = t1.br_storeGroup WHERE t1.br_storeGroup=@storeId;" ProviderName="MySql.Data.MySqlClient">
                <SelectParameters>
                    <asp:Parameter Name="storeId" />
                </SelectParameters>
            </asp:SqlDataSource>


        </div><!-- table-responsive -->
        
    </div><!-- card-body -->

    <div class="card-body mt-3">
        <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12">
                    <h6 class="tx-dark mb-1">Stores Connected with Payment Gateway Account</h6>
                    <p class="mb-0">Stores / Branches with gateway accounts. Select gateway account for the stores that is missing connect, in order to process payouts.</p>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <asp:GridView ID="gvStores" runat="server" GridLines="None" DataSourceID="SDSStores" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                <Columns>
                    <asp:BoundField HeaderText="Store" DataField="br_Name" ReadOnly="true" />
                    <asp:TemplateField HeaderText="Gateway Account">                        
                        <ItemTemplate>
                            <asp:Literal ID="ltrConnectAccount" runat="server" Text='<%# Eval("pgAccountId") %>' Visible='<%# String.IsNullOrEmpty(Eval("pgAccountId").ToString())? false : true %>'></asp:Literal>
                            <asp:DropDownList ID="selGatewayAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("br_ID") %>' DataSourceID="SDSApgccountid" Visible='<%# String.IsNullOrEmpty(Eval("pgAccountId").ToString())? true : false %>' DataTextField="accountId" DataValueField="id" OnSelectedIndexChanged="selGatewayAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                <asp:ListItem Text="Select Payment Account"></asp:ListItem>
                            </asp:DropDownList>
                        </ItemTemplate>
                        <EditItemTemplate>
                            <asp:DropDownList ID="selGatewayAccount" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("br_ID") %>' DataSourceID="SDSApgccountid" DataTextField="accountId" DataValueField="id" OnSelectedIndexChanged="selGatewayAccount_SelectedIndexChanged" AppendDataBoundItems="true">
                                <asp:ListItem Text="Select Payment Account"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="selGatewayAccount" ErrorMessage="Please select gateway account" ForeColor="Red" ValidationGroup="ChangeBank"></asp:RequiredFieldValidator>
                        </EditItemTemplate>
                    </asp:TemplateField>
                    <asp:TemplateField HeaderText="">
                        <ItemTemplate>
                            <asp:LinkButton runat="server" CommandName="Edit" Text="Change"></asp:LinkButton>
                        </ItemTemplate>
                        <EditItemTemplate>
                            <asp:LinkButton runat="server" CommandName="Cancel" Text="Cancel" ValidationGroup="ChangeBank"></asp:LinkButton>
                        </EditItemTemplate>
                    </asp:TemplateField>
                </Columns>
                <EmptyDataTemplate>
                    <div class="text-center">
                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                        <h6 class="mb-3">No record available</h6>
                    </div>
                </EmptyDataTemplate>
                <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
            </asp:GridView>
            
        </div> <!-- table-responsive -->
        
    </div>

    </asp:Panel>
                <asp:SqlDataSource ID="SDSApgccountid" OnSelecting="SDSLinkedAccount_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT * FROM  store_paymentgateway_connect sc WHERE storeGroupId=@storeId AND `status`=1" ProviderName="MySql.Data.MySqlClient">
                <SelectParameters>
                    <asp:Parameter Name="storeId" />
                </SelectParameters>
            </asp:SqlDataSource>


 <div class="modal fade" id="PGConnectModalPopup" tabindex="-1" role="dialog" aria-labelledby="PGConnectModalPopupTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-uppercase tx-dark" id="select_payment_title">Connect Your Payment Gateway</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
              <p class="card-text mg-b-8 tx-11">Link your existing <%= GetPaymentGatewayName() %> account or create a new one to enable online payments. This will allow your store to securely collect payments for customer orders.</p>
          </div>
            <div class="modal-btn d-inline-block"><br />
                <h1 class="tx-info"><%= GetPaymentGatewayName() %></h1>
            </div>
        </div>

          <div class="modal-btn d-inline-block">
              <asp:Button ID="btnConnect" CssClass="btn btn-primary" runat="server" Text="Connect" OnClick="btnConnect_Click" />
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>


      </div>
    </div>
  </div>
 </div>

      <div class="modal" id="razorpayaddaccount" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">

                <div class="modal-header">
                    <h4 class="modal-title">Add New Settlement Account</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row row-sm">
                        <div class="col-12">
                            <div class="input-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Name For Account: <span class="tx-danger">*</span></label>
                              <asp:TextBox ID="txtcontactname" runat="server" CssClass="form-control" placeholder="Enter Contact Name For Account" autocomplete="nofill" />
                                <asp:RequiredFieldValidator runat="server"  ControlToValidate="txtcontactname" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact Name For Account" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Number: <span class="tx-danger">*</span></label>
                                <div class="input-group loadmapbox ">                               
                                    <asp:TextBox ID="txtcontactnumber" runat="server" TextMode="Phone" CssClass="form-control border-0" placeholder="Enter Contact Number"  autocomplete="nofill"></asp:TextBox>
                                <asp:LinkButton runat="server" CssClass="input-group-append verify_btn btn btn-outline-secondary m-1 py-0 d-flex align-items-center"  ID="lbcontactnimberverify" OnClientClick="sendOtp(); return false;">
                                   Verify
                                    </asp:LinkButton>
                                    <asp:HiddenField ID="hdnContactVerified" runat="server" />                                  
                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtcontactnumber" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Enter Contact Number" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                                </div>  
                                  <asp:CustomValidator ID="cvContactVerified" runat="server" ErrorMessage="Please verify your contact number." ClientValidationFunction="validateContactVerified" Display="Dynamic" ForeColor="Red" ValidationGroup="ValueHead" >
                                    </asp:CustomValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
 
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Contact Email: <span class="tx-danger">*</span></label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtcontactemail" runat="server" TextMode="Email" CssClass="form-control border-0" placeholder="Enter Contact Email" autocomplete="nofill" />
                                  <asp:LinkButton runat="server" CssClass="input-group-append verify_btn btn btn-outline-secondary m-1 py-0 d-flex align-items-center" ID="btncontemailverify" OnClientClick="sendEmailOtp(); return false;">
                                    Verify
                                  </asp:LinkButton>
                                   <asp:HiddenField ID="hdncontactemailverify" runat="server" Value="0" />                                  
                                <asp:RequiredFieldValidator runat="server" Visible="false" ControlToValidate="txtcontactemail" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Contact Email" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>                                  
                                </div>
                                <asp:CustomValidator ID="cvcontactemailverify"  runat="server" ErrorMessage="Please verify your Email" ClientValidationFunction="validateContactEmailVerified" Display="Dynamic" ForeColor="Red" ValidationGroup="ValueHead">
                                </asp:CustomValidator>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-group mb-2">
                                <label class="w-100 text-left tx-dark">Bank Account No: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtbankaccountNo" runat="server" CssClass="form-control" placeholder="Enter Bank Account No" autocomplete="nofill" />
                                <asp:RequiredFieldValidator runat="server"  ControlToValidate="txtbankaccountNo" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Bank Account No" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>                                
                            </div>
                        </div>
                        <!-- col-4 -->
 
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">IFSC: <span class="tx-danger">*</span></label>
                                <div class="input-group loadmapbox ">
                                    <asp:TextBox ID="txtifsc" runat="server" CssClass="form-control border-0" placeholder="Enter IFSC" autocomplete="nofill" />
                                     <asp:HiddenField ID="hdnIfsc" runat="server" Value="0" />                                  
                                    <asp:LinkButton runat="server" CssClass="input-group-append verify_btn btn btn-outline-secondary m-1 py-0 d-flex align-items-center" ID="btnverifyifsc" OnClientClick="verifyIfsc(); return false;">
                                    Verify
                                    </asp:LinkButton>
                                    <asp:RequiredFieldValidator  runat="server" ControlToValidate="txtifsc" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Enter IFSC" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>                                 
                                </div>
                                <asp:CustomValidator ID="cvifc"  runat="server" ErrorMessage="Please verify Bank Details" ClientValidationFunction="validateIfscVerified" Display="Dynamic" ForeColor="Red" ValidationGroup="ValueHead">
                                </asp:CustomValidator>
                            </div>
                        </div>
 
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="form-control-label mb-1 w-100 tx-dark">Beneficiary Name:</label>
                                <asp:TextBox ID="txtAccountname" runat="server" ReadOnly="true" CssClass="form-control" placeholder="Account Holder Name As per Bank" autocomplete="nofill"/>
                                <asp:RequiredFieldValidator runat="server"  ControlToValidate="txtAccountname" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Account Name" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                         <asp:HiddenField ID="hdnAccountname" runat="server" />
                        <asp:HiddenField ID="hdnBankname" runat="server" />
                        <asp:HiddenField ID="hdnBranch" runat="server" />
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="w-100 text-left tx-dark">Bank Name : <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtbankname" runat="server" ReadOnly="true"  CssClass="form-control" placeholder="Bank Name" autocomplete="nofill" onkeypress="return allowAlphanumericUnderscore(event)" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbankname" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Bank Name" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                        <!-- col-4 -->
 
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="w-100 text-left tx-dark">Bank Branch: <span class="tx-danger">*</span></label>
                               <asp:TextBox ID="txtbranch" runat="server" ReadOnly="true"  CssClass="form-control" placeholder="Bank Branch" autocomplete="nofill" />
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbranch" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Bank Branch" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
 
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="w-100 text-left tx-dark">Beneficiary Legal Type: <span class="tx-danger">*</span></label>
                                <asp:DropDownList runat="server" CssClass="form-control" ID="ddlbeneficiarytype">
                                    <asp:ListItem Text="Select Beneficiary LegalType" Value="0"></asp:ListItem>
                                    <asp:ListItem Text="Private Limited" Value="1"></asp:ListItem>
                                    <asp:ListItem Text="Proprietorship" Value="2"></asp:ListItem>
                                    <asp:ListItem Text="Partnership" Value="3"></asp:ListItem>
                                    <asp:ListItem Text="Individual" Value="4"></asp:ListItem>
                                    <asp:ListItem Text="Public Limited" Value="5"></asp:ListItem>
                                    <asp:ListItem Text="LLP" Value="6"></asp:ListItem>
                                    <asp:ListItem Text="Trust" Value="7"></asp:ListItem>
                                    <asp:ListItem Text="Society" Value="8"></asp:ListItem>
                                    <asp:ListItem Text="NGO" Value="9"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlbeneficiarytype" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Bank Branch" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div> 
                        <div class="col-12 d-flex align-items-center">
                            <div class="ckboxsec w-100 mr-4">
                                <label class="ckbox form-control-label mb-0">
                                    <asp:CheckBox ID="chkTerms" runat="server" />
                                    <span>I agree to the Terms & Conditions and authorize Grozeo to process payments and settlements. I confirm the information provided is accurate</span>
                                </label>
                            </div>
                            <asp:CustomValidator ID="cvTerms" runat="server" EnableClientScript="true" ErrorMessage="You must agree to the Terms & Conditions" ForeColor="Red" ClientValidationFunction="validateTerms" ValidationGroup="ValueHead" Display="Dynamic">
                            </asp:CustomValidator>
                            <div class="btn_sec">
                                <asp:LinkButton runat="server" CssClass="btn btn-primary" ID="btnSavebankdetails" OnClick="btnSavebankdetails_Click" ValidationGroup="ValueHead">Submit</asp:LinkButton>
                            </div>
                        </div>
                         
                    </div>
              </div>               
            </div>
        </div>
    </div>

     <div class="modal" id="razorpayotpveriy" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                  <div class="modal-header">
                    <h4 class="modal-title">Enter OTP received</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="emailverification">
                        <div class="form-group m-0 otp_input_sec px-4">
                            <div class="form-row m-0 justify-content-center" id="emailverify" runat="server">
                                <div class="input-group justify-content-center">
                                    <div class="divOuter">
                                        <div class="divInner">
                                            <asp:TextBox runat="server" ID="txtOTP" CssClass="otp_input_field partitioned" MaxLength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"></asp:TextBox>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtOTP" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="OTP is required" ValidationGroup="OTPverify" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!--form-row-->
                            <div class="form-row m-0 justify-content-center">
                                <div class="formtbtn d-flex justify-content-center mt-4">
                                    <asp:LinkButton runat="server" ID="btnverify" CssClass="btn btn-primary mx-1 px-3" OnClientClick="return false;" ValidationGroup="OTPverify">Verify</asp:LinkButton>
                                </div>
                            </div>
                            <!--form-row-->
                        </div>
                        <!--form-group-->
                    </div>

                </div>                 
                </div>
            </div>
         </div>

    <div class="modal" id="razorpayemailotpverify" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                  <div class="modal-header">
                    <h4 class="modal-title">Enter OTP received</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="emailverification">
                        <div class="form-group m-0 otp_input_sec px-4">
                            <div class="form-row m-0 justify-content-center" id="Div1" runat="server">
                                <div class="input-group justify-content-center">
                                    <div class="divOuter">
                                        <div class="divInner">
                                            <asp:TextBox runat="server" ID="txtemailotpverify" CssClass="otp_input_field partitioned" MaxLength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" autocomplete="off"></asp:TextBox>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtemailotpverify" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="OTP is required" ValidationGroup="OTPverify" ForeColor="Red"></asp:RequiredFieldValidator>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!--form-row-->
                            <div class="form-row m-0 justify-content-center">
                                <div class="formtbtn d-flex justify-content-center mt-4">
                                    <asp:LinkButton runat="server" ID="btnotpemailverify" CssClass="btn btn-primary mx-1 px-3" OnClientClick="return false;" ValidationGroup="OTPverify">Verify</asp:LinkButton>
                                </div>
                            </div>
                            <!--form-row-->
                        </div>
                        <!--form-group-->
                    </div>

                </div>                 
                </div>
            </div>
         </div>



    <script>

        function validateTerms(source, args) {
            var chk = $("#<%= chkTerms.ClientID %>").prop("checked");
            args.IsValid = chk;  
        }

        // Step 1: Send OTP
        function sendOtp() {
            var input = $("#<%=txtcontactnumber.ClientID%>").val();
            let verifynumberBtn = document.getElementById("<%= lbcontactnimberverify.ClientID %>");

            if (input.trim() === "") {
                alert("Please enter mobile number");
                return;
            }
            onSuccess = function (response) {
                if (response.status == 'Success') {
                    $("#razorpayotpveriy").modal("show");
                }
                else if (response.status === 'Verified') {
                    // OTP verified successfully

                    // Disable the contact number textbox
                       $("#<%= txtcontactnumber.ClientID %>").prop("readonly", true);
                   document.getElementById("<%= hdnContactVerified.ClientID %>").value = "1";
                    verifynumberBtn.classList.add("hide");
                } 
            }
            onError = function (data) {
                alert('Operation failed');
            };
            retMaster.ajax.JSONRequest('/api/Home/Sendotp', 'POST', {input: input}, onSuccess, onError);                
        }
        $(document).ready(function () {
            $("#<%=btnverify.ClientID%>").click(function () {
                var mobile = $("#<%=txtcontactnumber.ClientID%>").val();
                var otp = $("#<%=txtOTP.ClientID%>").val(); 
                let verifynumberBtn = document.getElementById("<%= btncontemailverify.ClientID %>");


                if (otp.trim() === "") {
                    alert("Please enter OTP");
                    return;
                }
                var onSuccess = function (response) {
                    if (response.status === 'Success') {
                        $("#razorpayotpveriy").modal('hide');
                        $("#<%= txtcontactnumber.ClientID %>").prop("readonly", true);
                        document.getElementById("<%= hdnContactVerified.ClientID %>").value = "1";
                        verifynumberBtn.classList.add("hide");


                    } else {
                        alert('Invalid OTP, please try again.');
                    }
                };

                var onError = function () {
                    alert('Verification failed. Please try again.');
                };

                retMaster.ajax.JSONRequest('/api/Home/VerifyOtp', 'POST', { mobile: mobile, otp: otp }, onSuccess, onError);
            });

            $("#<%=btnotpemailverify.ClientID%>").click(function () {
                var email = $("#<%=txtcontactemail.ClientID%>").val();
                var emailotp = $("#<%=txtemailotpverify.ClientID%>").val();
                let verifyemailBtn = document.getElementById("<%= btncontemailverify.ClientID %>");

                if (emailotp.trim() === "") {
                    alert("Please enter OTP");
                    return;
                }
                var onSuccess = function (response) {
                    if (response.status === 'Success') {
                        $("#razorpayemailotpverify").modal('hide');
                        $("#<%= txtcontactemail.ClientID %>").prop("readonly", true);
                        document.getElementById("<%= hdncontactemailverify.ClientID %>").value = "1";
                        verifyemailBtn.classList.add("hide");

                    } else {
                        alert('Invalid OTP, please try again.');
                    }
                };

                var onError = function () {
                    alert('Verification failed. Please try again.');
                };

                retMaster.ajax.JSONRequest('/api/Home/Emailotpverify', 'POST', { email: email, emailotp: emailotp }, onSuccess, onError);
            });

        });
        // email otp verification 
        function sendEmailOtp() {
            var inputemail = $("#<%=txtcontactemail.ClientID%>").val();
            let verifyemailBtn = document.getElementById("<%= btncontemailverify.ClientID %>");
            if (inputemail.trim() === "") {
                alert("Please enter mobile number");
                return;
            }
            onSuccess = function (response) {
                if (response.status == 'Success') {
                    $("#razorpayemailotpverify").modal("show");
                }
                else if (response.status === 'Verified') {
                    // OTP verified successfully

                    // Disable the contact number textbox
                   $("#<%= txtcontactemail.ClientID %>").prop("readonly", true);
                      document.getElementById("<%= hdncontactemailverify.ClientID %>").value = "1";
                    verifyemailBtn.classList.add("hide");
                }
             }
             onError = function (data) {
                 alert('Operation failed');
             };
            retMaster.ajax.JSONRequest('/api/Home/sendemailotp', 'POST', { inputmail: inputemail }, onSuccess, onError);
        }

        function verifyIfsc() {
            let accountNo = document.getElementById("<%= txtbankaccountNo.ClientID %>").value;
            let ifsc = document.getElementById("<%= txtifsc.ClientID %>").value;

            let accountName = document.getElementById("<%= txtAccountname.ClientID %>");
            let bankName = document.getElementById("<%= txtbankname.ClientID %>");
            let branch = document.getElementById("<%= txtbranch.ClientID %>");
            let verifybankBtn = document.getElementById("<%= btnverifyifsc.ClientID %>");


            if (!accountNo || !ifsc) {
                alert("Please enter Account Number and IFSC");
                return;
            }

            var onSuccess = function (response) {
                if (response.status === 'Success') {                   
                    accountName.value = response.data.Name;
                    bankName.value = response.data.Bank;
                    branch.value = response.data.Branch;
                    document.getElementById("<%= hdnAccountname.ClientID %>").value = response.data.Name;
                    document.getElementById("<%= hdnBankname.ClientID %>").value = response.data.Bank;
                    document.getElementById("<%= hdnBranch.ClientID %>").value = response.data.Branch;
                    accountName.readOnly = true;
                    bankName.readOnly = true;
                    branch.readOnly = true;
                    $("#<%= txtbankaccountNo.ClientID %>").prop("readonly", true);
                    $("#<%= txtifsc.ClientID %>").prop("readonly", true);
                   document.getElementById('<%= hdnIfsc.ClientID %>').value = "1";
                    verifybankBtn.classList.add("hide");

                } else {
                    alert('Invalid Details');
                }
            };

            var onError = function () {
                alert('Verification failed. Please try again.');
            };
            retMaster.ajax.JSONRequest('/api/Home/Bankdetails', 'POST', { bankaccountnumber: accountNo, Ifsc: ifsc }, onSuccess, onError);           
        }
//verification of contact number
        function validateContactVerified(source, args) {
            var verified = document.getElementById("<%= hdnContactVerified.ClientID %>").value;
            args.IsValid = (verified === "1"); 
        }
//verification of contact email
        function validateContactEmailVerified(source, args) {
            var isVerified = document.getElementById("<%= hdncontactemailverify.ClientID %>").value;
            args.IsValid = (isVerified === "1"); 
        }
//verification IFSC
      function validateIfscVerified(source, args) {
              var isVerified = document.getElementById("<%= hdnIfsc.ClientID %>").value;
             args.IsValid = (isVerified === "1");
         }
       
    </script>

    <style>
        .verify_btn{
            line-height:100%;
        }
        #razorpayotpveriy.modal {
            z-index: 1052;
        }
         #razorpayemailotpverify.modal {
            z-index: 1052;
        }
        .modal-backdrop.show + .modal-backdrop.show, .modal-backdrop.show + live-preview-root + .modal-backdrop.show{
            z-index: 1051;
        }
    </style>



</asp:Content>
