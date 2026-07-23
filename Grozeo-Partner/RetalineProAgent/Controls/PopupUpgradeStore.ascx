<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="PopupUpgradeStore.ascx.cs" Inherits="RetalineProAgent.Controls.PopupUpgradeStore" %>
      <div id="modalupgrade" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold"><asp:Literal ID="ltrHeadContent" runat="server" Text="Upgrade subscription"></asp:Literal></h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-20">
            <h5 class=" lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary"><asp:Literal ID="ltrTitleContent" runat="server" Text="Upgrade your package and experience the advanced features provided"></asp:Literal></a></h5>
            <p class="mg-b-5"><asp:Literal ID="ltrBodyContent1" runat="server" Text="You are trying to change your package from Starter to Scale. Under Scale package, you can set up a custom domain for your group of stores, create more stores and products, can request to publish your branded mobile applications, enjoy discounted payment gateway transactions, earn more customer support credits and many more."></asp:Literal> </p>
            <br /><p class="mg-b-5"><asp:Literal runat="server" ID="ltrBodyContent2"></asp:Literal></p>



            <%--<div class="section-wrapper upgrade_otp p-3" style="display:none;">
              <label class="section-title">Enter the OTP received in mobile</label>
              <p class="mg-b-20 mg-sm-b-40">OTP has been send to your registred mobile number.</p>

                <div class="form-layout form-layout-4 otp_input_sec">
                    <div class="row">
                        <div class="col-12 d-flex flex-wrap align-items-end justify-content-center justify-content-sm-start mg-t-10 mg-sm-t-0">
                            <div class="divOuter">
                                <div class="divInner">
                                    <asp:TextBox ID="txtUpgradeTOP" ClientIDMode="Static" CssClass="partitioned" runat="server" autocomplete="off" placeholder="" MaxLength="4" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                </div>
                            </div>
                            <div class="upgradeotpbtnsec mt-3 mt-sm-0 ml-0 ml-sm-3 text-align d-flex">
                                <asp:PlaceHolder ID="plcUpgradeOTPClientButton" runat="server">
                                    <input type="submit" value="Verify" id="btnUpgradeVerifyOTP" class="btn btn-primary btn-drk-green"></asp:PlaceHolder>
                                <asp:Button runat="server" ID="btnVerifyConsentOTP" Visible="false" Text="Verify" CssClass="btn btn-primary btn-drk-green" OnClick="btnVerifyConsentOTP_Click" />

                            </div>
                        </div>


                    </div>
                    <!-- row -->

                </div>
                <!-- form-layout -->
                <asp:Label ID="lblResult" runat="server" ForeColor="Red"></asp:Label>
            </div>--%>
              <!-- section-wrapper -->



          </div><!-- modal-body -->
          <div class="modal-footer">

              <a href="/tenant/subscription" class="btn btn-primary">Upgrade</a>
            <%--<button type="button" class="btn btn-primary" id="upgradebtn_submit">Upgrade</button>--%>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<script type="text/javascript">
    function upgradestoreFailureOtp(msg) {
        <%--$('#<%= lblResult.ClientID%>').html(msg);--%>
        $('#modalupgrade').find('div.modal-footer').hide();
        $('#modalupgrade').find('.upgrade_otp').show();
        $('#txtUpgradeTOP').focus();
        $('#modalupgrade').modal('show');
    }
</script>

<%--<%if (!CurrentUser.HasPaymentMethod && ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".revolut.com"))
    { %>

<script src="<%= String.Format("{0}/embed.js", ConfigurationManager.AppSettings.Get("PaymentGateway")) %>"></script>
<script type="text/javascript">
    retMaster.properties.paymentGateway = 'revolut';
</script>

<% }
    else if (!CurrentUser.HasPaymentMethod && ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
    { %>

<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
    retMaster.properties.paymentGateway = 'Stripe';
</script>

      <div id="modalpayment" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Upgrade subscription - Add Card</h6>
          </div>
          <div class="modal-body pd-20">
            <h5 class=" lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Add payment method for activating the subscription</a></h5>
            <p class="mg-b-5">Add your card to set up your subscription payment method. No charges will be applied today; billing will begin on your subscription start date next month. By adding your card now, you ensure uninterrupted access to premium features when your subscription becomes active.</p>



            <div class="section-wrapper upgrade_otp p-3">
                <div class="form-layout form-layout-4 otp_input_sec">
                    <div class="row">
                        <div class="col-12 d-flex flex-wrap align-items-end justify-content-center justify-content-sm-start mg-t-10 mg-sm-t-0">
                            <div class="divOuter">
                                <div class="divInner" id="upgrademodelcontent">
                                    
                                </div>
                            </div>
                            
                        </div>


                    </div>
                    <!-- row -->

                </div>
                                <asp:Label ID="lblPaymentResult" ClientIDMode="Static" runat="server" ForeColor="Red"></asp:Label>

            </div><!-- section-wrapper -->

          </div><!-- modal-body -->
          <div class="modal-footer">

  <button type="button" class="btn btn-primary" id="upgradebtn_savecard">Upgrade</button>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


      <div id="modalsubscription_selectpayment" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold" id="select_payment_title"></h6>
          </div>
          <div class="modal-body pd-20">
            <div class="row mb-3" id="row_subscription_price"></div>
            <div class="row mb-3"><asp:TextBox ID="txtSubscriptionCoupon" runat="server" autocomplete="off" placeholder="Coupon code"></asp:TextBox></div>

          </div><!-- modal-body -->
          <div class="modal-footer">

  <button type="button" class="btn btn-primary" id="selectPrice">Upgrade</button>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->


<%} %>--%>

