using log4net;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Configuration;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Razorpay.Api;
using System.Data;

namespace RetalineProAgent.Core.Services.PaymentGateway
{
    public class RazorpayService : IPaymentService
    {
        private static readonly ILog log = LogManager.GetLogger(typeof(StripeService));

        public dynamic PaymentRequest(int merchantId, int packageId, decimal amount, string paymentMehtodId = "") {
            return null;
        }
        public int ProcessPayment(dynamic data, int merchantId) {
            //dynamic data = resultData.data;
            string responseData = JsonConvert.SerializeObject(data);
            bool isSuccess = Convert.ToBoolean(data.isSuccess);
            int planID = Convert.ToInt32(data.planID);
            string sqlMerchantSubscription = "INSERT INTO S_MerchantSubscriptions(MerchantID,PlanID,PriceID,StartDate,ExpiryDate,PaymentStatus, PGSubscriptionId, RefCode, PGCustomerId, CouponCode) VALUES(@MerchantID, @planID, @PriceID, @StartDate, @ExpiryDate, @PaymentStatus, @PGSubscriptionId, @RefCode, @PGCustomerId, @refCode); " +
                "UPDATE AppTenant SET PackageId = (select top 1 GroupId from S_PlanPricing where PlanPricingID=@PriceID) WHERE Id=@MerchantID; ";

            string strSql = "UPDATE Payment_Logs SET ResponseData = @ResponseData, Status = @Status, LastUpdatedDate = @UpdateDate, [Description]= CONCAT('Cancelled by the change on subscription plan: ', @PlanName) WHERE LogID = @LogID and Status not like 'Success'; " +
                (isSuccess ? sqlMerchantSubscription : "") + "select * from Payment_Logs where LogID = @LogID";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("ResponseData", responseData),
                new KeyValuePair<string, object>("Status", isSuccess ? "Success" : "Failed"),
                //new KeyValuePair<string, object>("TransactionID", TransactionID), // Extract transaction ID from the response
                new KeyValuePair<string, object>("UpdateDate", DateTime.Now),
                new KeyValuePair<string, object>("LogID", data.logID),
                new KeyValuePair<string, object>("refCode", data.refCode),
                new KeyValuePair<string, object>("PlanName", data.planName)
            };
            if (isSuccess)
            {
                DateTime startDate = DateTime.Now; try { startDate = DateTimeOffset.FromUnixTimeSeconds(data.CurrentPeriodStart).ToLocalTime(); } catch { startDate = DateTime.Now; }
                DateTime endDate = DateTime.Now.AddMonths(24); try { endDate = DateTimeOffset.FromUnixTimeSeconds(data.CurrentPeriodEnd).ToLocalTime(); } catch { endDate = DateTime.Now.AddMonths(24); }

                prms.Add(new KeyValuePair<string, object>("MerchantID", merchantId));
                prms.Add(new KeyValuePair<string, object>("PlanID", data.planID));
                prms.Add(new KeyValuePair<string, object>("PriceID", data.priceId));
                prms.Add(new KeyValuePair<string, object>("StartDate", startDate));
                prms.Add(new KeyValuePair<string, object>("ExpiryDate", endDate));
                prms.Add(new KeyValuePair<string, object>("PaymentStatus", "Paid"));
                prms.Add(new KeyValuePair<string, object>("PGSubscriptionId", data.pgSubscriptionid));
                prms.Add(new KeyValuePair<string, object>("PGCustomerId", data.CustomerId));
            }
            try
            {
                // Update Payment_Logs with the response and update subscription status accordingly
                DataTable dtLog = DataService.GetDataTable(strSql, parmeters: prms);

                // Activate subscription based on the response
                //if (isSuccess && dtLog != null && dtLog.Rows.Count > 0)
                //{
                //    int merchantID = Convert.ToInt32(dtLog.Rows[0]["MerchantID"]);
                //    int packageID = Convert.ToInt32(dtLog.Rows[0]["PackageID"]);
                //    return 1; //sbcrid;
                //}

                List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("priceid", data.priceId), new KeyValuePair<string, object>("storeId", merchantId) };
                var tblSubscriptionPrice = DataService.GetDataTable("select p.*, s.[Type] as PlanType, s.PlanName, ms.PGType, ms.PGSubscriptionId, ms.PaymentStatus as MSPaymentStatus, ms.[Status] as MSStatus, " +
                    "ms.PriceID as MSPriceId, (select top 1 PGCustomerId from S_MerchantSubscriptions where MerchantID=@storeId and isnull(PGCustomerId, '') <> '') as PGCustomerId " +
                    "from S_PlanPricing p inner join S_SubscriptionPlans s on p.PlanID=s.Id left join S_MerchantSubscriptions ms on ms.PlanID=p.PlanID and ms.MerchantID=@storeId and ms.[Status]=1 where PlanPricingID = @priceid", "", input);
                if (tblSubscriptionPrice == null || tblSubscriptionPrice.Rows.Count <= 0)
                    throw new Exception("The subscription data is not available at the moment. Please contact support for more details.");

                int planType = -1; try { planType = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanType"]); } catch { planType = -1; }
                int oldPriceId = -1; try { oldPriceId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["MSPriceId"]); } catch { oldPriceId = 0; }
                int planGroupId = -1; try { planGroupId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["GroupId"]); } catch { planGroupId = -1; }
                string oldPGsubscriptionId = tblSubscriptionPrice.Rows[0]["PGSubscriptionId"].ToString();

                try
                {
                    // Cancel the old subscription in stripe since new one created.
                    if (oldPriceId > 0)//(isSuccessful && !string.IsNullOrEmpty(oldPGsubscriptionId))
                        CancelSubscription(oldPGsubscriptionId, merchantId, planID, oldPriceId, data.planName, data.apiKeyId, data.apiSecret);
                }
                catch (Exception ex) { log.Error(ex); }

                try
                {
                    if (planType == 0 && planGroupId > 0)
                        UpdateFeatureSubscriptions(planGroupId, merchantId, data.apiKeyId, data.apiSecret);
                }
                catch(Exception ex1) { 
                    log.Error(ex1);
                }

                return 1;
            }
            catch (Exception ex)
            {
                log.Error(ex);
                throw new Exception("Execution failed. The subscription failed due to some unexpected failures.");
            }

            return 0;
        }

        public static dynamic CreateSubscriptionSession(string domain, int priceId, int merchantId, string merchantName="", string merchantEmail="")
        {
            var tblPriceConfig = DataService.GetDataTable("select top 1 p.*, s.PlanName from S_PlanPricing p inner join S_SubscriptionPlans s on p.PlanID=s.Id where p.PlanPricingID=@priceId", parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("priceId", priceId) });
            if (tblPriceConfig == null || tblPriceConfig.Rows.Count <= 0)
                throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");

            var tblRzConfig = DataServiceMySql.GetDataTable("SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");
            if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");

            string apiKeySecret = tblRzConfig.Rows[0]["key_secret"].ToString();
            string apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();
            if (string.IsNullOrEmpty(apiKeySecret))
                throw new Exception("Sorry, there is a technical issue happend at the system level on subscription. Please contact support for more details");

            int planId = 0; try { planId = Convert.ToInt32(tblPriceConfig.Rows[0]["PlanID"]); } catch { planId = 0; }
            string pgPriceId = tblPriceConfig.Rows[0]["GatewayPriceid"].ToString();
            string planName = tblPriceConfig.Rows[0]["PlanName"].ToString();
            string billingCycle = tblPriceConfig.Rows[0]["BillingCycle"].ToString();
            double planPrice = 0; try { planPrice = Convert.ToDouble(tblPriceConfig.Rows[0]["PricePerCycle"]); } catch { planPrice = 0; }
            double durationDays = 0; try { durationDays = Convert.ToDouble(tblPriceConfig.Rows[0]["DurationInDays"]); } catch { durationDays = 0; }

            if (planPrice <= 0)
                throw new Exception("Invalid plan price selection. Please contact support for more details!");
            planPrice = planPrice * 100;

            int totalCount = 60; // 60 times for monthly or 5 times for annual.
            if (billingCycle.ToLower() == "annual")
                totalCount = 5;

            RazorpayClient client = new RazorpayClient(apiKeyId, apiKeySecret);
            Dictionary<string, object> subscriptionRequest = new Dictionary<string, object>();
            subscriptionRequest.Add("plan_id", pgPriceId);
            subscriptionRequest.Add("total_count", totalCount);
            subscriptionRequest.Add("quantity", 1);
            subscriptionRequest.Add("customer_notify", 1);
            subscriptionRequest.Add("start_at", DateTimeOffset.Now.AddHours(0.5).ToUnixTimeSeconds());
            subscriptionRequest.Add("expire_by", DateTimeOffset.Now.AddDays(billingCycle.ToLower() == "annual" ? 365 * 5 : 60 * 30).ToUnixTimeSeconds());
            Dictionary<string, object> linesItem = new Dictionary<string, object>();
            Dictionary<string, object> item = new Dictionary<string, object>();
            item.Add("name", $"{planName} {billingCycle}");
            item.Add("amount", planPrice);
            item.Add("currency", "INR");
            linesItem.Add("item", item);
            object[] addons = new object[] { linesItem };
            subscriptionRequest.Add("addons", addons);
            // subscriptionRequest.Add("offer_id", "offer_LFw2SqDBi8kf53");
            //Dictionary<string, object> notes = new Dictionary<string, object>();
            // notes.Add("notes_key_1", "Tea, Earl Grey, Hot");
            // notes.Add("notes_key_2", "Tea, Earl Grey… decaf.");
            //subscriptionRequest.Add("notes", notes);

            var subscription = client.Subscription.Create(subscriptionRequest);
            string subscriptionId = subscription["id"].ToString();
            string shortUrl = subscription["short_url"].ToString();

            

            string strSql = " INSERT INTO Payment_Logs (MerchantID, SubscriptionID, PackageID, RequestData, uniqId, Status, paymentGateway) OUTPUT INSERTED.LogID VALUES (@MerchantID, @planid, @PackageID, @RequestData, @uuid, @status, @paymentGateway)";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("MerchantID", merchantId),
                new KeyValuePair<string, object>("RequestData", ""),
                new KeyValuePair<string, object>("PackageID", priceId),
                new KeyValuePair<string, object>("planid", planId),
                new KeyValuePair<string, object>("uuid", subscriptionId),
                new KeyValuePair<string, object>("status", "Pending"),
                new KeyValuePair<string, object>("paymentGateway", "razorpay")
            };

            int logID = (int)DataService.ExecuteScalar(strSql, parmeters: prms); // Get log ID for future updates



            dynamic result = new System.Dynamic.ExpandoObject();
            result.pgPriceId = pgPriceId;
            result.pgSubscriptionid = subscriptionId;
            result.logID = logID;
            result.planName = planName;
            result.billingCycle = billingCycle;
            result.apiKeyId = apiKeyId;
            result.priceId = priceId;
            result.planID = planId;
            result.CurrentPeriodStart = subscriptionRequest["start_at"];
            result.CurrentPeriodEnd = subscriptionRequest["expire_by"];
            result.CustomerId = "";

            return result;

        }

        private void UpdateFeatureSubscriptions(int groupId, int merchantId, string apiKeyId="", string apiSecret="")
        {
            if(string.IsNullOrEmpty(apiKeyId) || string.IsNullOrEmpty(apiSecret))
            {
                var tblRzConfig = DataServiceMySql.GetDataTable("SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");
                if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                    throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");

                apiSecret = tblRzConfig.Rows[0]["key_secret"].ToString();
                apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();
                if (string.IsNullOrEmpty(apiKeyId) || string.IsNullOrEmpty(apiSecret))
                    throw new Exception("Sorry, there is a technical issue happend at the system level on subscription. Please contact support for more details");
            }

            string sql = "select ms.*, p.*, s.PlanName, s.[Status] as SubscriptionStatus, np.PlanPricingID as newPriceId, np.PricePerCycle as newPricePerCycle, np.GatewayPriceid as newPGPriceId " +
                "from S_MerchantSubscriptions ms inner join S_SubscriptionPlans s on ms.PlanID=s.Id inner join S_PlanPricing p on p.GroupId <> @groupId and ms.PriceID=p.PlanPricingID " +
                " left join S_PlanPricing np on np.GroupId=@groupId and np.PlanID=ms.PlanID and np.BillingCycle=p.BillingCycle " +
                " where ms.[Status]=1 and ms.MerchantID=@merchantId and p.GroupId <> @groupId and ms.PaymentStatus like 'Paid'; ";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("groupId", groupId), new KeyValuePair<string, object>("merchantId", merchantId) };
            var tblSubscriptions = DataService.GetDataTable(sql, "", input);

            if (tblSubscriptions == null || tblSubscriptions.Rows.Count <= 0)
                return;

            foreach (DataRow dr in tblSubscriptions.Rows)
            {
                try
                {
                    int planStatus = -1; try { planStatus = Convert.ToInt32(dr["SubscriptionStatus"]); } catch { planStatus = -1; }
                    int oldPriceId = 0; try { oldPriceId = Convert.ToInt32(dr["PlanPricingID"]); } catch { oldPriceId = -1; }
                    if (planStatus <= 0 || oldPriceId <= 0)
                        continue;

                    string pgPriceId = dr["GatewayPriceid"].ToString();
                    string pgSubscriptionId = dr["PGSubscriptionId"].ToString();
                    string newPGPriceId = dr["newPGPriceId"].ToString();
                    string planName = dr["PlanName"].ToString();
                    string billingCycle = dr["BillingCycle"].ToString();
                    int planId = 0; try { planId = Convert.ToInt32(dr["PlanID"]); } catch { planId = 0; }

                    // Cancel the subscription if new pg price id is null. Otherwise update the subscription with new price id in both stripe and db.
                    if (String.IsNullOrEmpty(newPGPriceId))
                    {
                        // Cancel subscription since it is available part of the new upgrade/downgrade
                        CancelSubscription(pgSubscriptionId, merchantId, planId, oldPriceId, planName, apiKeyId, apiSecret);
                    }
                    else
                    {
                        // Update subscription price corresponding to the price id of the new upgrade/downgrade
                        int newPriceId = 0; try { newPriceId = Convert.ToInt32(dr["newPriceId"]); } catch { newPriceId = -1; }
                        var subscription = UpdateSubscriptionPrice(newPGPriceId, pgSubscriptionId, merchantId, planId, oldPriceId, newPriceId, planName, billingCycle, apiKeyId, apiSecret);
                        // Stripe will not allow a subscription item updated unless it's status is active. The update function will return null in case if there is an issue in the update.
                        if (subscription == null)
                        {
                            log.Error($"Update price failed. merchantId: {merchantId}, planId: {planId}, oldPriceId: {oldPriceId}, newPriceId:{newPriceId}");
                            CancelSubscription(pgSubscriptionId, merchantId, planId, oldPriceId, planName, apiKeyId, apiSecret);
                        }
                    }
                }
                catch (Exception ex)
                {
                    log.Error(new Exception($"Error on UpdateFeatureSubscriptions: Merchant id - {merchantId}, group id - {groupId} ", ex));
                }
            }

        }

        public dynamic ProcessRequest(NameValueCollection requestData, int merchantId) {



            return null; 
        }

        private void CancelSubscription(string subscriptionId, int merchantId, int planId, int oldPriceId, string planName, string apiKeyId = "", string apiSecret="")
        {
            if (string.IsNullOrEmpty(apiKeyId) || string.IsNullOrEmpty(apiSecret))
            {
                var tblRzConfig = DataServiceMySql.GetDataTable("SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");
                if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                    throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");

                apiSecret = tblRzConfig.Rows[0]["key_secret"].ToString();
                apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();
                if (string.IsNullOrEmpty(apiKeyId) || string.IsNullOrEmpty(apiSecret))
                    throw new Exception("Sorry, there is a technical issue happend at the system level on subscription. Please contact support for more details");
            }

            RazorpayClient client = new RazorpayClient(apiKeyId, apiSecret);
            Dictionary<string, object> param = new Dictionary<string, object>();
            param.Add("cancel_at_cycle_end", 1);
            Razorpay.Api.Subscription subscription = client.Subscription.Fetch(subscriptionId).Cancel(param);

            string strSql = "update S_MerchantSubscriptions set [Status] = 0, UpdatedOn = GETUTCDATE() where MerchantID = @merchantId and PriceID=@PriceId; " +
    " INSERT INTO Payment_Logs (MerchantID, SubscriptionID, PackageID, ResponseData, Status, paymentGateway, [Description]) VALUES (@merchantId, @planID, @PriceId, @ResponseData, " +
    "'Success', 'Stripe', CONCAT('Cancelled by the change on subscription plan: ', @PlanName))";
            List<KeyValuePair<string, object>> prm = new List<KeyValuePair<string, object>>() {
                            new KeyValuePair<string, object>("PriceId", oldPriceId), new KeyValuePair<string, object>("merchantId", merchantId),
                            new KeyValuePair<string, object>("PlanName", planName), new KeyValuePair<string, object>("planID", planId),
                            new KeyValuePair<string, object>("ResponseData", JsonConvert.SerializeObject(subscription)) };
            DataService.ExecuteSql(strSql, parmeters: prm);
        }

        private Razorpay.Api.Subscription UpdateSubscriptionPrice(string pgPriceId, string pgSubscriptionid, int merchantId, int planId, int oldPriceId, int newPriceId, string planName, string billingCycle, string apiKeyId, string apiSecret)
        {
            try
            {
                if (string.IsNullOrEmpty(pgSubscriptionid))
                    throw new Exception("Update Subscription Price Failure: Invalid subscription");
                
                if (String.IsNullOrEmpty(pgPriceId))
                    throw new Exception("Update Subscription Price Failure. The price data is not valid for this operation.");

                int totalCount = 60; // 60 times for monthly or 5 times for annual.
                if (billingCycle.ToLower() == "annual")
                    totalCount = 5;

                Razorpay.Api.Subscription subscription = null;
                try
                {
                    RazorpayClient client = new RazorpayClient(apiKeyId, apiSecret);
                    string subscriptionId = pgSubscriptionid;
                    Dictionary<string, object> param = new Dictionary<string, object>();
                    param.Add("plan_id", pgPriceId);
                    //param.Add("offer_id", "offer_JHD834hjbxzhd38d");
                    param.Add("quantity", 1);
                    param.Add("remaining_count", (billingCycle.ToLower() == "annual" ? 365 * 5 : 60 * 30));
                    param.Add("start_at", DateTimeOffset.Now.ToUnixTimeSeconds());
                    param.Add("schedule_change_at", "now");
                    param.Add("customer_notify", 1);

                    subscription = client.Subscription.Fetch(subscriptionId).Edit(param);
                }
                catch (Exception ex1)
                {
                    log.Error(ex1);
                }
                // Update the subscription with the new price id in database.
                string strSql = "update S_MerchantSubscriptions set PriceID=@newPriceId, UpdatedOn = GETUTCDATE() where MerchantID = @merchantId and PriceID=@PriceId; " +
                    " INSERT INTO Payment_Logs (MerchantID, SubscriptionID, PackageID, ResponseData, Status, paymentGateway, [Description]) VALUES (@merchantId, @planID, @newPriceId, @ResponseData, " +
                    "'Success', 'Stripe', CONCAT('Subscription price updated by the change on subscription plan: ', @PlanName))";
                List<KeyValuePair<string, object>> prm = new List<KeyValuePair<string, object>>() {
                            new KeyValuePair<string, object>("PriceId", oldPriceId), new KeyValuePair<string, object>("merchantId", merchantId),
                            new KeyValuePair<string, object>("PlanName", planName), new KeyValuePair<string, object>("planID", planId),
                            new KeyValuePair<string, object>("ResponseData", JsonConvert.SerializeObject(subscription)), new KeyValuePair<string, object>("newPriceId", newPriceId) };
                DataService.ExecuteSql(strSql, parmeters: prm);

                return subscription;
            }
            catch (Exception ex)
            {
                log.Error(ex);
            }
            return default;
        }
                               
       public static List<(string AccountId, string TransferId, bool IsSuccess, string ErrorMessage)>TransferToAccounts(Dictionary<string, decimal> accountAmountMap, string paymentId, string currency = "INR")
       {
            var results = new List<(string AccountId, string TransferId, bool IsSuccess, string ErrorMessage)>();

            try
            {
                List<(string OrderId, double AmountDue, string OrderPaymentId,string accountid)> payments = new List<(string, double, string,string)>();

                foreach (var orders in accountAmountMap)
                {
                    string compositeKey = orders.Key?.Trim(); // "transferId-payAccountId"
                    decimal amount = orders.Value;

                    // Split the composite key back
                    var parts = compositeKey.Split('-');
                    string transferId = parts.Length > 0 ? parts[0] : string.Empty;
                    string accountId = parts.Length > 1 ? parts[1] : string.Empty;
                    // Example: build SQL params
                    var sqlPrdParams = new List<KeyValuePair<string, object>>
                    {
                        new KeyValuePair<string, object>("id", transferId),                        
                    };
                    string getorderid = "SELECT o.amount_due,o.order_id,order_payment_gateway_refid FROM retaline_customer_order co INNER JOIN merchant_settlements_order o ON o.order_id=co.order_id INNER JOIN merchant_settlements ms ON ms.ref_id=o.ms_ref_id  INNER JOIN finance_transaction_log tl ON tl.ms_id=ms.id  WHERE tl.ft_id= @id";
                    DataTable dtorderpaymentid = DataServiceMySql.GetDataTable(getorderid,parmeters: sqlPrdParams);
                    var payment = dtorderpaymentid.AsEnumerable().Select(row => ( OrderId: row["order_id"]?.ToString() ?? "", RazorAmountDue: double.TryParse(row["amount_due"]?.ToString(), out var amt) ? amt : 0, OrderPaymentId: row["order_payment_gateway_refid"]?.ToString() ?? "",AccountId: accountId )).ToList();
                    foreach (var p in payment)
                    {
                        var result = TransferOnPayment(p.OrderPaymentId,p.AccountId, p.RazorAmountDue);
                        if(result != null)
                        {
                            results.AddRange(((List<Transfer>)result ?? new List<Transfer>())
                                 .Select(t =>
                                 {
                                     dynamic dto = JsonConvert.DeserializeObject<dynamic>(t.Attributes.ToString());
                                     return (Recipient: (string)dto.recipient,PaymentTransferId: (string)dto.id,Success: true, Status: (string)dto.status);
                                 }));
                        }
                    }
                    string errorMessage = string.Empty;
                }
            }
            catch (Exception ex)
            {
                // optional: you could add a failed entry
                results.Add((string.Empty, string.Empty, false, ex.Message));
            }

            return results;
       }

        private static object TransferOnPayment(string rzPaymentId, string toAccountId, double amount, string currency="INR")
        {
            var results = new List<(string AccountId, string TransferId, bool IsSuccess, string ErrorMessage)>();
            var tblRzConfig = DataServiceMySql.GetDataTable(
                "SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");

            if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                throw new Exception("Failure: Razorpay configuration not found.");

            string apiKeySecret = tblRzConfig.Rows[0]["key_secret"].ToString();
            string apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();

            if (string.IsNullOrEmpty(apiKeySecret) || string.IsNullOrEmpty(apiKeyId))
                throw new Exception("Failure: Razorpay keys missing from configuration.");


            RazorpayClient client = new RazorpayClient(apiKeyId, apiKeySecret);
            var transfersList = new List<Dictionary<string, object>>();            
            transfersList.Add(new Dictionary<string, object>
            {
                        { "account", toAccountId },
                        { "amount", (int)Math.Round(amount * 100) }, // INR → paise
                        { "currency", currency },
                        { "notes", new Dictionary<string, string>
                           {
                             { "description", $"Settlement payout to {toAccountId}" }
                           }
                        }
            });


            if (transfersList.Count == 0)
                return "";
            var transferData = new Dictionary<string, object>
            {
                { "transfers", transfersList }
            };
            var payment = client.Payment.Fetch(rzPaymentId);
            // This returns a List<Transfer>
            List<Transfer> transferResponse = payment.Transfer(transferData);
            return transferResponse;

        }
        /// <summary>
        /// Get last 10 transaction from razorpay
        /// </summary>
        /// <returns></returns>
        /// <exception cref="Exception"></exception>
        public List<(string PaymentId, string OrderId, decimal Amount, string Status, DateTime createdate, string contactnumber, String email, string ErrorMessage)> Gettransaction()
        {
            var tblRzConfig = DataServiceMySql.GetDataTable(
                "SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");

            if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                throw new Exception("Failure: Razorpay configuration not found.");

            string apiKeySecret = tblRzConfig.Rows[0]["key_secret"].ToString();
            string apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();

            if (string.IsNullOrEmpty(apiKeySecret) || string.IsNullOrEmpty(apiKeyId))
                throw new Exception("Failure: Razorpay keys missing from configuration.");
            RazorpayClient client = new RazorpayClient(apiKeyId, apiKeySecret);
            var result = new List<(string PaymentId, string OrderId, decimal Amount, string Status,DateTime createdate,string contactnumber,String email, string ErrorMessage)>();

            try
            {
                var options = new Dictionary<string, object>
                {
                        { "count", 50 },   // last 10 records
                        { "skip", 0 }    // from the latest
                        
                };

                List<Payment> payments = client.Payment.All(options);

                if (payments == null || payments.Count == 0)
                {
                    result.Add((string.Empty, string.Empty, 0, string.Empty, DateTime.MinValue, string.Empty, string.Empty, "No payments found"));
                    return result;
                }               
                foreach (var payment in payments)
                {
                    try
                    {
                        string paymentId = Convert.ToString(payment["id"]);
                        string orderId = payment["order_id"] != null ? Convert.ToString(payment["order_id"]) : "";
                        string status = payment["status"] != null ? Convert.ToString(payment["status"]) : "";
                        decimal amts = 0;
                        if (payment["amount"] != null)
                        {
                            decimal temp;
                            if (decimal.TryParse(Convert.ToString(payment["amount"]), out temp))
                            {
                                amts = temp/100;
                            }
                        }
                        DateTime createdAt = DateTime.MinValue;
                        long unixTime = 0;

                        if (payment["created_at"] != null &&
                            long.TryParse(payment["created_at"].ToString(), out unixTime))
                        {
                            createdAt = DateTimeOffset.FromUnixTimeSeconds(unixTime).DateTime;
                        }
                        string email = payment["email"]?.ToString() ?? "";
                        string contact = payment["contact"]?.ToString() ?? "";
                        result.Add((paymentId, orderId, amts, status, createdAt, contact, email, string.Empty));
                    }
                    catch (Exception innerEx)
                    {
                        result.Add((string.Empty, string.Empty, 0, string.Empty, DateTime.MinValue, string.Empty, string.Empty, $"Payment parse error: {innerEx.Message}"));
                    }
                }
            }
            catch (Exception ex)
            {
                // If API call fails completely, return error entry
                result.Add((string.Empty, string.Empty, 0, string.Empty, DateTime.MinValue, string.Empty, string.Empty, $"API error: {ex.Message}"));
            }

            return result;
        }
        /// <summary>
        /// GetSubscriptionDetails.Implementing it for need change as soon as possible
        /// </summary>
        /// <param name="subscriptionId"></param>
        /// <param name="apiKeyId"></param>
        /// <param name="apiSecret"></param>
        /// <returns></returns>
        /// <exception cref="Exception"></exception>
        public object GetSubscriptionDetails(string subscriptionId)
        {
            try
            {
                var tblRzConfig = DataServiceMySql.GetDataTable("SELECT * FROM `finascop_company_razorpay` WHERE storegroup_id = 0 LIMIT 1");
                if (tblRzConfig == null || tblRzConfig.Rows.Count <= 0)
                    throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");
                string apiSecret = tblRzConfig.Rows[0]["key_secret"].ToString();
                string apiKeyId = tblRzConfig.Rows[0]["key_id"].ToString();
                if (string.IsNullOrEmpty(apiKeyId) || string.IsNullOrEmpty(apiSecret))
                    throw new Exception("Sorry, there is a technical issue happend at the system level on subscription. Please contact support for more details");
                RazorpayClient client = new RazorpayClient(apiKeyId, apiSecret);
                var invoices = client.Invoice.All(new Dictionary<string, object>
                {
                    { "subscription_id", subscriptionId },                  
                });

                if (invoices.Count > 0)
                {
                    string paymentId = invoices[0]["payment_id"]?.ToString();
                    if (paymentId!=null)
                    {
                        var invoicesResponse = client.Payment.Fetch(paymentId);
                        return invoicesResponse;
                    }
                }
                
                return null;

            }
            catch(Exception ex)
            {
                return null;
            }
           
        }
    }
}
