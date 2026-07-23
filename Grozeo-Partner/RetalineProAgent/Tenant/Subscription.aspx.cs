using Microsoft.Ajax.Utilities;
using MySqlX.XDevAPI;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class Subscription : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                try
                {
                    if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
                    {
                        ProcessStripeRequest();
                    }
                    else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains("razorpay"))
                    {
                        ProcessRazorpayRequest();
                    }
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, $"Failure on activating the subscription. {ex.Message}", "danger");
                }

            }
        }

        private void ProcessRazorpayRequest()
        {
            if (Request.Form.Count > 0 && Request.Form.AllKeys.Contains("razorpay_payment_id") && Request.Form.AllKeys.Contains("razorpay_signature"))
            {
                if (Session["RAZORSUBSCRIPTION"] == null)
                    throw new Exception("The subscription session is not active or there is a technical error. Please contact support for more details.");

                dynamic data = Session["RAZORSUBSCRIPTION"];
                if (data == null || data.logID <= 0)
                    throw new Exception("The subscription session is not valid or there is a technical error. Please contact support for more details.");

                try
                {
                    Session.Remove("RAZORSUBSCRIPTION");
                    string rzPaymentId = Request.Form["razorpay_payment_id"], rzSubscriptionId = Request.Form["razorpay_subscription_id"], rzSignature = Request.Form["razorpay_signature"];
                    if (string.IsNullOrEmpty(rzSignature) || string.IsNullOrEmpty(rzPaymentId) || string.IsNullOrEmpty(rzSubscriptionId) || data.pgSubscriptionid != rzSubscriptionId)
                        throw new Exception("The subscription response is invalid or there is a technical error. Please contact support for more details.");

                    DataTable tblPgConfig = DataServiceMySql.GetDataTable("SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0;",
                        parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId) });
                    if (tblPgConfig == null || tblPgConfig.Rows.Count <= 0)
                        throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");

                    string strSecret = tblPgConfig.Rows[0]["key_secret"].ToString();
                    string apiKeyId = tblPgConfig.Rows[0]["key_id"].ToString();
                    if (string.IsNullOrEmpty(strSecret))
                        throw new Exception("Sorry, there is a technical issue happend at the system level on subscription. Please contact support for more details");

                    string strGenSignature = EncryptionService.ComputeHmacSha256(rzPaymentId + "|" + rzSubscriptionId, strSecret);
                    if (string.IsNullOrEmpty(strGenSignature) || strGenSignature != rzSignature) throw new Exception("Invalid operation. The subscription state is not valid");

                    // Process payment success.
                    data.apiKeyId = apiKeyId; data.apiSecret = strSecret; data.isSuccess = true;
                    int result = (new RazorpayService()).ProcessPayment(data, this.CurrentUser.StoreGroupId);
                    if (result > 0)
                        Common.ShowCustomAlert(this.Page, "Success", "Subscription has been active after completing the payment.", true, "/tenant/subscription");
                    else
                        Common.ShowCustomAlert(this.Page, "Failure", "Subscription failed. Please make sure that your payment option is valid and try again later.", true, "/tenant/subscription");
                }
                catch (Exception ex)
                {
                    this.LogError($"Failure on Razorpay subscription response for merchant ({this.CurrentUser.StoreGroupId}) - {ex.Message}");
                    Session.Remove("RAZORSUBSCRIPTION");
                }
            }
        }
        private void ProcessStripeRequest()
        {
            if (!String.IsNullOrEmpty(Request.QueryString["session_id"]))
            {
                try
                {
                    if (Session["STRIPESUBSCRIPTION"] == null)
                        throw new Exception("The subscription session is not active or there is a technical error. Please contact support for more details.");

                    dynamic data = Session["STRIPESUBSCRIPTION"];
                    if (data == null || data.sessionId != Request.QueryString["session_id"])
                        throw new Exception("The subscription session is not valid or there is a technical error. Please contact support for more details.");

                    bool isSuccess = (new StripeService()).CreateSubscriptionSuccess(Request.QueryString["session_id"], this.CurrentUser.StoreGroupId, data.refCode);
                    if (isSuccess)
                        Common.ShowCustomAlert(this.Page, "Success", "Subscription has been active after completing the payment.", true, "/tenant/subscription");
                    else
                        Common.ShowCustomAlert(this.Page, "Failure", "Subscription failed. Please make sure that your payment option is valid and try again later", true, "/tenant/subscription");
                }
                catch (Exception ex)
                {
                    this.LogError($"Subscription error or session id: {Request.QueryString["session_id"]}. " + ex.Message);
                    Common.ShowCustomAlert(this.Page, "Failure", "Subscription failed. There might be a technical issue on the payment options or process. Please make sure that your payment option is valid and try again later", true, "/tenant/subscription");
                }
            }
            else if (!String.IsNullOrEmpty(Request.QueryString["action"]) && Request.QueryString["action"] == "cancel")
            {
                if (Session["STRIPESUBSCRIPTION"] != null && !String.IsNullOrWhiteSpace(Session["STRIPESUBSCRIPTION"].ToString()))
                {
                    Session.Remove("STRIPESUBSCRIPTION");
                    Common.ShowCustomAlert(this.Page, "Failure", "Subscription cancelled. You have cancelled the subscription payment. Please try again later if you prefer to continue", true, "/tenant/subscription");
                }
            }

        }

        protected void SDSSubscriptions_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storegroupid"))
                e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.StoreGroupId;

        }

        public string LogoImage(string key)
        {
            string strLogoPath = "/content/images/p-no-image.png";
            switch (key)
            {
                case "pwa":
                    strLogoPath = "/content/images/logo/pwa_logo.png";
                    break;
                case "andriodapp":
                    strLogoPath = "/content/images/logo/Android_logo.svg";
                    break;
                case "iosapp":
                    strLogoPath = "/content/images/logo/ios-logo.jpg";
                    break;
                case "whatsapporder":
                strLogoPath = "/content/images/logo/WhatsApp_based.png";
                    break;
                case "socialmediaorder":
                strLogoPath = "/content/images/logo/Social_Media_Order.png";
                    break;
                default:
                    strLogoPath = "/content/images/p-no-image.png";
                    break;
            }
            return strLogoPath;
        }

        public string PlanPricingName(string pricingName, string PricePerCycle, string BillingCycle, string Discount)
        {
            string strPricingTitle = pricingName;
            if (!string.IsNullOrEmpty(BillingCycle))
                strPricingTitle += $" ({(String.IsNullOrEmpty(PricePerCycle) ? "" : PricePerCycle + " ")}{BillingCycle})";
            if (!string.IsNullOrEmpty(Discount))
                strPricingTitle += $" {ConfigurationManager.AppSettings.Get("CurrencySymbol")}{Discount} Discount";

            return strPricingTitle;
        }

        protected void btnCancelRenew_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(hidCanelPrId.Value))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid operation. The subscription is not valid for the operation.", "danger");
                return;
            }
            int priceId = 0; try { priceId = Convert.ToInt32(hidCanelPrId.Value); } catch { priceId = 0; }

            List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("merchantId", this.CurrentUser.StoreGroupId), new KeyValuePair<string, object>("priceid", priceId) };

            string sql = "update S_MerchantSubscriptions set AutoRenew=0 where MerchantID=@merchantId and @priceid > 0 and PriceID=@priceid and [Status]=1 and PaymentStatus = 'Paid'";
            var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);
            if (sqlresult > 0)
                Common.ShowCustomAlert(this.Page, "Success", "Auto-renew cancelled successfully", true, "/tenant/subscription");
            else
                Common.ShowCustomAlert(this.Page, "Failure", "Auto-renew cancellation failed by invalid input", false, "/tenant/subscription");

        }

        protected void btnEnableAutoRenew_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(hidEnablePrId.Value))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid operation. The subscription is not valid for the operation.", "danger");
                return;
            }
            int priceId = 0; try { priceId = Convert.ToInt32(hidEnablePrId.Value); } catch { priceId = 0; }

            List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("merchantId", this.CurrentUser.StoreGroupId), new KeyValuePair<string, object>("priceid", priceId) };

            string sql = "update S_MerchantSubscriptions set AutoRenew=1 where MerchantID=@merchantId and @priceid > 0 and PriceID=@priceid and [Status]=1";
            var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);
            if (sqlresult > 0)
                Common.ShowCustomAlert(this.Page, "Success", "Auto-renew enabled successfully", true, "/tenant/subscription");
            else
                Common.ShowCustomAlert(this.Page, "Failure", "Auto-renew enable failed by invalid input", false, "/tenant/subscription");

        }

        protected void ODSPaymentMethods_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            if (e.InputParameters.Contains("merchantId"))
                e.InputParameters["merchantId"] = this.CurrentUser.StoreGroupId;

        }

        protected void btnSubscribe_Click(object sender, EventArgs e)
        {
            var domain = Request.Url.Scheme + "://" + Request.Url.Authority; // Get your website's domain
            int priceId = 0; try { priceId = Convert.ToInt32(hidPriceId.Value); } catch { priceId = 0; }
            if (priceId <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Validation failure", "The subscription selected is not valid.", false);
                return;
            }

            if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
            {
                dynamic session = StripeService.CreateSubscriptionSession(domain, priceId, this.CurrentUser.StoreGroupId);
                string script = $"<script>window.location.href = '{session.url}';" + "</s" + "cript>";
                ClientScript.RegisterStartupScript(this.GetType(), "stripeRedirect", script);

                Session["STRIPESUBSCRIPTION"] = new { domain = domain, priceId = priceId, merchantId = this.CurrentUser.StoreGroupId, sessionId = session.sessionId, refCode = txtSubscriptionRefCode.Text };
            }
            else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains("razorpay"))
            {
                dynamic sessionData = RazorpayService.CreateSubscriptionSession(domain, priceId, this.CurrentUser.StoreGroupId, this.CurrentUser.StoreGroupName, this.CurrentUser.Email);
                sessionData.domain = domain; sessionData.priceId = priceId; sessionData.merchantId = this.CurrentUser.StoreGroupId; sessionData.sessionId = Session.SessionID; sessionData.refCode = txtSubscriptionRefCode.Text;

                Session["RAZORSUBSCRIPTION"] = sessionData;// new { domain = domain, priceId = priceId, merchantId = this.CurrentUser.StoreGroupId, sessionId = Session.SessionID, refCode = txtSubscriptionRefCode.Text, sessionData = sessionData };

                var options = new
                {
                    key = sessionData.apiKeyId, //ConfigurationManager.AppSettings.Get("PaymentGatewaykey"),
                    subscription_id = sessionData.pgSubscriptionid, // "sub_Q2iLYqZDIcvotp", //
                    name = this.CurrentUser.StoreGroupName,
                    description = $"{sessionData.planName} {sessionData.billingCycle} Plan",
                    callback_url = $"{Request.Url.Scheme}://{Request.Url.Authority}{Request.Url.AbsolutePath}",
                };

                string script = "<script>var options = " + Newtonsoft.Json.JsonConvert.SerializeObject(options) + "; var rzp1 = new Razorpay(options); rzp1.open();" + "</s" + "cript>";
                ClientScript.RegisterStartupScript(this.GetType(), "stripeRedirect", script);

            }

        }

    }
}