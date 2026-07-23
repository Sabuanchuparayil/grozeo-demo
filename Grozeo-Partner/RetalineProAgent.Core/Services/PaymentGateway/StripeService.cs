using Amazon.Runtime.Internal.Util;
using Google.Protobuf.Collections;
using log4net;
using Newtonsoft.Json;
using Org.BouncyCastle.Asn1.Ocsp;
using RetalineProAgent.Core.BussinessModel.Inventory;
//using RetalineProAgent.Core.Services.Subscription;
using SendGrid.Helpers.Mail;
using Stripe;
using Stripe.Checkout;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Dynamic;
using System.Linq;
using System.Numerics;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices.ComTypes;
using System.Text;
using System.Threading.Tasks;
using ThirdParty.Json.LitJson;

namespace RetalineProAgent.Core.Services.PaymentGateway
{
	public class StripeService: IPaymentService
	{
        private static readonly ILog log = LogManager.GetLogger(typeof(StripeService));
        public dynamic PaymentRequest(int merchantId, int packageId, decimal amount, string paymentMehtodId="")
		{
			throw new NotImplementedException();
		}

		public List<dynamic> PlanFeatures(int planId)
		{
			List<dynamic> planFeatures = new List<dynamic>();
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("planId", planId) };
            var tblSubscriptions = DataService.GetDataTable("select f.FeatureName, f.[Description], f.FeatureType, pf.[Value], isnull(pf.FeatureLimit, 0) as FeatureLimit from S_PlanFeatures pf inner join S_Features f on pf.FeatureID=f.Id where f.[Status] > 0 and pf.PlanID=@planId;", "", input);
            if (tblSubscriptions == null || tblSubscriptions.Rows.Count <= 0)
                return default;

			foreach (DataRow dr in tblSubscriptions.Rows)
			{
				string strFeatureName = dr["FeatureName"].ToString();
				string strDescription = dr["Description"].ToString();
				int featureType = 0; try { featureType = Convert.ToInt32(dr["FeatureType"]); } catch { featureType = 0; }
				string strValue = dr["Value"].ToString();
				int featureLimit = 0; try { featureLimit = Convert.ToInt32(dr["FeatureLimit"]); } catch { featureLimit = 0;}
				planFeatures.Add(new { FeatureName = strFeatureName, Description = strDescription, FeatureType = featureType, Value = strValue, FeatureLimit = featureLimit });
            }

			return planFeatures;
        }

        public List<dynamic> PaymentMethods(int merchantId)
		{
            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                return default;
            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("merchantId", merchantId) };
			var tblSubscriptions = DataService.GetDataTable("select ms.PGCustomerId, s.PlanName, p.BillingCycle from S_MerchantSubscriptions ms left join S_SubscriptionPlans s on ms.PlanID=s.Id left join S_PlanPricing p on ms.PriceID=p.PlanPricingID where MerchantID=@merchantId and ms.[Status]=1;", "", input);
			if (tblSubscriptions == null || tblSubscriptions.Rows.Count <= 0)
				return default;
			List<dynamic> dnPaymentMethods = new List<dynamic>();
			List<PaymentMethod> paymentMethods = new List<PaymentMethod>();
            StripeConfiguration.ApiKey = strSecretKey;
            var options = new CustomerPaymentMethodListOptions {  };
            var service = new CustomerPaymentMethodService();

			foreach (DataRow dr in tblSubscriptions.Rows)
			{
				try
				{
					string strPGCustId = dr["PGCustomerId"].ToString();
					if (string.IsNullOrEmpty(strPGCustId))
						continue;

					string strPlanName = dr["PlanName"].ToString();
					string strBillingCycle = dr["BillingCycle"].ToString();

					StripeList<PaymentMethod> custpaymentMethods = service.List(
                    strPGCustId,
						options);
                    dnPaymentMethods.AddRange(custpaymentMethods.Select(p=> new { Last4= p.Card.Last4, Brand=p.Card.Brand, Funding= p.Card.Funding, ExpYear=p.Card.ExpYear, 
						ExpMonth = p.Card.ExpMonth, Created =p.Created, Type=p.Type, PlanName=strPlanName, BillingCycle=strBillingCycle}).ToList());
				}
				catch { }
			}

			return dnPaymentMethods;
        }

		public int ProcessPayment(dynamic data, int merchantId)
		//public int ProcessPayment(string paymentid, string orderId, int merchantId)
		{
			if (data == null || data.id == null || data.id.id == null || string.IsNullOrEmpty(data.id.ToString()) || string.IsNullOrEmpty(data.id.id.ToString()) || merchantId < 1)
				throw new Exception("Invalid operation");
			
			if(String.IsNullOrEmpty(data.priceId.ToString()))
				throw new Exception("Invalid subscription");

			int priceId = Convert.ToInt32(data.priceId);
 			string paymentMethod = data.id.id.ToString();
			if(priceId <= 0)
				throw new Exception("Error: Invalid subscription");

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("priceid", priceId), new KeyValuePair<string, object>("storeId", merchantId) };
			var tblSubscriptionPrice = DataService.GetDataTable("select p.*, s.[Type] as PlanType, s.PlanName, ms.PGType, ms.PGSubscriptionId, ms.PaymentStatus as MSPaymentStatus, ms.[Status] as MSStatus, " +
                "ms.PriceID as MSPriceId, (select top 1 PGCustomerId from S_MerchantSubscriptions where MerchantID=@storeId and isnull(PGCustomerId, '') <> '') as PGCustomerId " +
                "from S_PlanPricing p inner join S_SubscriptionPlans s on p.PlanID=s.Id left join S_MerchantSubscriptions ms on ms.PlanID=p.PlanID and ms.MerchantID=@storeId and ms.[Status]=1 where PlanPricingID = @priceid", "", input);
			if (tblSubscriptionPrice == null || tblSubscriptionPrice.Rows.Count <= 0)
				throw new Exception("The subscription data is not available at the moment. Please contact support for more details.");

			int planType = -1; try { planType = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanType"]); } catch { planType = -1; }
			int oldPriceId = -1; try { oldPriceId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["MSPriceId"]); } catch { oldPriceId = 0; }
			int planGroupId = -1; try { planGroupId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["GroupId"]); } catch { planGroupId = -1; }
			int planId = 0; try { planId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanID"]); } catch { planId = 0; }

			if(priceId == oldPriceId)
				throw new Exception("Failure on duplication. The package is already active. Please select a different package.");

			string pgPriceId = tblSubscriptionPrice.Rows[0]["GatewayPriceid"].ToString();
			string oldPGsubscriptionId = tblSubscriptionPrice.Rows[0]["PGSubscriptionId"].ToString();
			// int SGId = 0; try { SGId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["MerchantId"]); } catch(Exception ex1) { }
			string pgCustomerId = tblSubscriptionPrice.Rows[0]["PGCustomerId"].ToString();
			string strOrderId = ""; try{if (data.orderId != null) strOrderId = data.orderId.ToString();}catch { strOrderId = ""; }
			string oldSubscriptionPriceStatus = tblSubscriptionPrice.Rows[0]["MSPaymentStatus"].ToString();
            string oldSubscriptionStatus = tblSubscriptionPrice.Rows[0]["MSStatus"].ToString();
			string planName = tblSubscriptionPrice.Rows[0]["PlanName"].ToString();

			string refCode = ""; try{if (data.refCode != null) refCode = data.refCode.ToString();}catch { refCode = ""; }

            if (String.IsNullOrEmpty(pgPriceId) || merchantId <= 0)
                throw new Exception("Failure: The subscription upgrade cannot proceed at the moment. Please contact support for more details.");

			if (oldSubscriptionPriceStatus == "Paid" && oldSubscriptionStatus == "1" && oldPriceId == priceId)
				throw new Exception("Failure: Duplicate subscription. There is an active subscription for the same package in your account.");

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");
            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();

            string strRequestData = JsonConvert.SerializeObject(data);
			int keyId = InitiateSubscription(merchantId, planId, priceId, strRequestData, "0", !string.IsNullOrEmpty(oldPGsubscriptionId));
            if (keyId <= 0)
                throw new Exception("Failure: create subscription failed due to some unexpected errors. Please contact support or try later after some time.");

			// Stripe API call
            Stripe.Subscription subscription = CreateSubscription(merchantId, pgCustomerId, pgPriceId, paymentMethod, strSecretKey);
            if (subscription == null)
                throw new Exception("Error: create subscription failed due to some unexpected errors. Please contact support or try later after some time.");

            bool isSuccessful = (new string[] { "trialing", "active" }).Contains(subscription.Status.ToLower());
			if (!isSuccessful)
			{
				log.Error($"Create subsction failed with status: {subscription.Status}, {JsonConvert.SerializeObject(subscription)}");
				throw new Exception($"Failure: Create subscription failed with status: {subscription.Status}");
			}
            // Enable the subscription in DB.
            CompletePayment(merchantId, planId, priceId, subscription, keyId, planName, isSuccessful, refCode);

            try
            {
				// Cancel the old subscription in stripe since new one created.
				if (oldPriceId > 0)//(isSuccessful && !string.IsNullOrEmpty(oldPGsubscriptionId))
					CancelSubscription(oldPGsubscriptionId, strSecretKey, merchantId, planId, oldPriceId, planName);
			}
			catch(Exception ex) { log.Error(ex); }

			if (planType == 0 && planGroupId > 0)
				UpdateFeatureSubscriptions(planGroupId, merchantId, strSecretKey);
			//return subscriptionid;
			return 1;
		}

		private Stripe.Subscription CreateSubscription(int storeGroupId, string pgCustomerId, string pgPriceId, string paymentMethod, string strSecretKey)
		{
			try
			{
                StripeConfiguration.ApiKey = strSecretKey;
				if (String.IsNullOrEmpty(pgCustomerId))
				{
					var customer = CreateCustomer(storeGroupId, paymentMethod, strSecretKey);
					if (customer == null || string.IsNullOrEmpty(customer.Id))
						throw new Exception("Invalid operation. The payment process failed due to some unexpected errors. Please try later or contact support for more details.");
					pgCustomerId = customer.Id;
				}
				else
				{
					var newMethod = AddPaymentMethod(pgCustomerId, paymentMethod, strSecretKey);					
                }
				var options = new SubscriptionCreateOptions
				{
					Customer = pgCustomerId,
					Items = new List<SubscriptionItemOptions>
					{
						new SubscriptionItemOptions { Price = pgPriceId, }, 
					}, TrialPeriodDays = 0,
                    // PaymentBehavior = "default_incomplete", // Adjust based on your needs
                    DefaultPaymentMethod = paymentMethod
                };

				var service = new Stripe.SubscriptionService();
				Stripe.Subscription subscription = service.Create(options);
                //bool isSuccessful = (subscription != null && (new string[] { "trialing", "active" }).Contains(subscription.Status.ToLower()));
				return subscription;
			}
			catch(Exception ex) { 
				log.Error(ex);
			}
			return default;
        }

		private Stripe.Subscription CancelSubscription(string subscriptionId, string strSecretKey, int merchantId, int planId, int oldPriceId, string planName)
		{
			Stripe.Subscription subscription = null;

            try
			{
				StripeConfiguration.ApiKey = strSecretKey;
				var service = new Stripe.SubscriptionService();
				subscription = service.Cancel(subscriptionId);
			}
			catch( Exception ex)
			{
				log.Error($"Error on CancelSubscription - subscriptionId: {subscriptionId}, merchantId: {merchantId}, planId: {planId}, oldPriceId: {oldPriceId}");
			}
			string strSql = "update S_MerchantSubscriptions set [Status] = 0, UpdatedOn = GETUTCDATE() where MerchantID = @merchantId and PriceID=@PriceId; " +
                " INSERT INTO Payment_Logs (MerchantID, SubscriptionID, PackageID, ResponseData, Status, paymentGateway, [Description]) VALUES (@merchantId, @planID, @PriceId, @ResponseData, " +
                "'Success', 'Stripe', CONCAT('Cancelled by the change on subscription plan: ', @PlanName))";
            List<KeyValuePair<string, object>> prm = new List<KeyValuePair<string, object>>() {
                            new KeyValuePair<string, object>("PriceId", oldPriceId), new KeyValuePair<string, object>("merchantId", merchantId),
                            new KeyValuePair<string, object>("PlanName", planName), new KeyValuePair<string, object>("planID", planId),
                            new KeyValuePair<string, object>("ResponseData", JsonConvert.SerializeObject(subscription)) };
            DataService.ExecuteSql(strSql, parmeters: prm);

            return subscription;
        }
		private void UpdateFeatureSubscriptions(int groupId, int merchantId, string pgSKey)
		{
			string sql = "select ms.*, p.*, s.PlanName, s.[Status] as SubscriptionStatus, np.PlanPricingID as newPriceId, np.PricePerCycle as newPricePerCycle, np.GatewayPriceid as newPGPriceId " +
				"from S_MerchantSubscriptions ms inner join S_SubscriptionPlans s on ms.PlanID=s.Id inner join S_PlanPricing p on p.GroupId <> @groupId and ms.PriceID=p.PlanPricingID " +
				" left join S_PlanPricing np on np.GroupId=@groupId and np.PlanID=ms.PlanID and np.BillingCycle=p.BillingCycle " +
				" where ms.[Status]=1 and ms.MerchantID=@merchantId and p.GroupId <> @groupId and ms.PaymentStatus like 'Paid'; ";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("groupId", groupId), new KeyValuePair<string, object>("merchantId", merchantId) };
            var tblSubscriptions = DataService.GetDataTable(sql, "", input);

			if (tblSubscriptions == null || tblSubscriptions.Rows.Count <= 0)
				return;

			foreach(DataRow dr in tblSubscriptions.Rows)
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
					int planId = 0; try { planId = Convert.ToInt32(dr["PlanID"]); } catch { planId = 0; }

					// Cancel the subscription if new pg price id is null. Otherwise update the subscription with new price id in both stripe and db.
					if (String.IsNullOrEmpty(newPGPriceId))
					{
						// Cancel subscription since it is available part of the new upgrade/downgrade
						Stripe.Subscription subscription = CancelSubscription(pgSubscriptionId, pgSKey, merchantId, planId, oldPriceId, planName);
					}
					else
					{
						// Update subscription price corresponding to the price id of the new upgrade/downgrade
						int newPriceId = 0; try { newPriceId = Convert.ToInt32(dr["newPriceId"]); } catch { newPriceId = -1; }
						Stripe.Subscription subscription = UpdateSubscriptionPrice(newPGPriceId, pgSubscriptionId, pgSKey, merchantId, planId, oldPriceId, newPriceId, planName);
						// Stripe will not allow a subscription item updated unless it's status is active. The update function will return null in case if there is an issue in the update.
                        if (subscription == null)
						{
							log.Error($"Update price failed. merchantId: {merchantId}, planId: {planId}, oldPriceId: {oldPriceId}, newPriceId:{newPriceId}");
                            CancelSubscription(pgSubscriptionId, pgSKey, merchantId, planId, oldPriceId, planName);
                        }
                    }
                }
				catch(Exception ex)
				{
					log.Error(new Exception($"Error on UpdateFeatureSubscriptions: Merchant id - {merchantId}, group id - {groupId} ", ex));
				}
            }

        }

		/// <summary>
		/// Update subscription item price.
		/// </summary>
		/// <param name="pgPriceId"></param>
		/// <param name="pgSubscriptionid"></param>
		/// <param name="strSecretKey"></param>
		/// <param name="merchantId"></param>
		/// <param name="planId"></param>
		/// <param name="oldPriceId"></param>
		/// <param name="newPriceId"></param>
		/// <param name="planName"></param>
		/// <returns></returns>
		private Stripe.Subscription UpdateSubscriptionPrice(string pgPriceId, string pgSubscriptionid, string strSecretKey, int merchantId, int planId, int oldPriceId, int newPriceId, string planName)
		{
			try
			{
				if (string.IsNullOrEmpty(pgSubscriptionid))
					throw new Exception("Update Subscription Price Failure: Invalid subscription");

				StripeConfiguration.ApiKey = strSecretKey;
				if (String.IsNullOrEmpty(pgPriceId))
					throw new Exception("Update Subscription Price Failure. The price data is not valid for this operation.");

				Stripe.Subscription subscription = null;
                try
				{
					var options = new SubscriptionItemUpdateOptions
					{
						Price = pgPriceId
					};

					var service = new SubscriptionService();
					subscription = service.Get(pgSubscriptionid);
					var item = subscription.Items.FirstOrDefault();

					var itemService = new SubscriptionItemService();
					SubscriptionItem subscriptionItem = itemService.Update(item.Id, options);
					item = subscriptionItem;
				}
				catch(Exception ex1)
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

		private PaymentMethod AddPaymentMethod(string customerId, string paymentMethod, string apiKey)
		{
            var options = new PaymentMethodAttachOptions { Customer = customerId };
            var service = new PaymentMethodService();
            PaymentMethod newmethod= service.Attach(paymentMethod, options);

			return newmethod;
        }
		private Customer CreateCustomer(int merchantId, string paymentMethod, string apiKey)
		{
			string strSql = "select isnull(s.DisplayName, a.[Name]) as StoreName, s.StoreEmail, s.StorePhone, s.StoreAddress, a.StoreId from AppTenant a " +
				"inner join Store s on s.TenantId=a.Id where a.Id = @storeId;";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("storeId", merchantId) };
            var tblMerchant = DataService.GetDataTable(strSql, "", input);
            if (tblMerchant == null || tblMerchant.Rows.Count <= 0)
                throw new Exception("The merchant data is not available at the moment. Please try later after some time or contact support for more details.");
			string name = tblMerchant.Rows[0]["StoreName"].ToString();
			string email = tblMerchant.Rows[0]["StoreEmail"].ToString();
			string phone = tblMerchant.Rows[0]["StorePhone"].ToString();
			string address = tblMerchant.Rows[0]["StoreAddress"].ToString();
			string merchantName = String.Format("{0} ({1}_{2})", name, merchantId, tblMerchant.Rows[0]["StoreId"]);
			try
			{
				StripeConfiguration.ApiKey = apiKey;
				var options = new CustomerCreateOptions
				{
					Name = merchantName,
					Email = email,
					Phone = phone,
					Description = address,
					PaymentMethod = paymentMethod,
					InvoiceSettings = new CustomerInvoiceSettingsOptions() { DefaultPaymentMethod = paymentMethod, }
				};
				var service = new CustomerService();
				var customer = service.Create(options);
				return customer;
			}
			catch(Exception ex)
			{
				log.Error(ex);
				throw new Exception("Error: The payment method is not valid or customer data is not available.");
			}
        }

        /// <summary>
        /// ProcessPayment - Add payment log
        /// </summary>
        /// <param name="responseData">Response from gateway</param>
        /// <param name="logID">Log Id</param>
        /// <param name="TransactionID">Transaction Id</param>
        /// <param name="isSuccessful">Success / Failed</param>
        /// <returns>Subscription Id</returns>
        private int CompletePayment(int tenantId, int subsciptionId, int priceId, dynamic subscription, int logID, string planName, bool isSuccess, string refCode="")
        {
			string TransactionID = subscription.LatestInvoiceId;
			
            string responseData = JsonConvert.SerializeObject(subscription);

            string sqlMerchantSubscription = "INSERT INTO S_MerchantSubscriptions(MerchantID,PlanID,PriceID,StartDate,ExpiryDate,PaymentStatus, PGSubscriptionId, RefCode, PGCustomerId, CouponCode) VALUES(@MerchantID, @planID, @PriceID, @StartDate, @ExpiryDate, @PaymentStatus, @PGSubscriptionId, @RefCode, @PGCustomerId, @refCode); " +
                "UPDATE AppTenant SET PackageId = (select top 1 GroupId from S_PlanPricing where PlanPricingID=@PriceID) WHERE Id=@MerchantID; ";
            //string sqlMerchantSubscription = " UPDATE S_MerchantSubscriptions SET PriceID = @PriceID, StartDate=@StartDate, ExpiryDate=@ExpiryDate, PaymentStatus=@PaymentStatus, " +
            //    "PGSubscriptionId=@PGSubscriptionId, RefCode=@RefCode, PGCustomerId=@PGCustomerId, CouponCode = @refCode WHERE MerchantID = @MerchantID AND PlanID = @PlanID; " +
            //    "UPDATE AppTenant SET PackageId = (select top 1 GroupId from S_PlanPricing where PlanPricingID=@PriceID) WHERE Id=@MerchantID; ";

            string strSql = "UPDATE Payment_Logs SET ResponseData = @ResponseData, Status = @Status, GatewayTransactionID = @TransactionID, LastUpdatedDate = @UpdateDate, [Description]= CONCAT('Cancelled by the change on subscription plan: ', @PlanName) WHERE LogID = @LogID and Status not like 'Success'; " +
				(isSuccess ? sqlMerchantSubscription : "") +
                "select * from Payment_Logs where LogID = @LogID";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("ResponseData", responseData),
                new KeyValuePair<string, object>("Status", isSuccess ? "Success" : "Failed"),
                new KeyValuePair<string, object>("TransactionID", TransactionID), // Extract transaction ID from the response
                new KeyValuePair<string, object>("UpdateDate", DateTime.Now),
                new KeyValuePair<string, object>("LogID", logID), 
				new KeyValuePair<string, object>("refCode", refCode),
				new KeyValuePair<string, object>("PlanName", planName)
            };
			if (isSuccess)
			{
				prms.Add(new KeyValuePair<string, object>("MerchantID", tenantId));
                prms.Add(new KeyValuePair<string, object>("PlanID", subsciptionId));
                prms.Add(new KeyValuePair<string, object>("PriceID", priceId));
                prms.Add(new KeyValuePair<string, object>("StartDate", subscription.CurrentPeriodStart));
                prms.Add(new KeyValuePair<string, object>("ExpiryDate", subscription.CurrentPeriodEnd));
                prms.Add(new KeyValuePair<string, object>("PaymentStatus", "Paid"));
                prms.Add(new KeyValuePair<string, object>("PGSubscriptionId", subscription.Id));
                prms.Add(new KeyValuePair<string, object>("PGCustomerId", subscription.CustomerId));
            }
			try
			{
				// Update Payment_Logs with the response and update subscription status accordingly
				DataTable dtLog = DataService.GetDataTable(strSql, parmeters: prms);

				// Activate subscription based on the response
				if (isSuccess && dtLog != null && dtLog.Rows.Count > 0)
				{
					int merchantID = Convert.ToInt32(dtLog.Rows[0]["MerchantID"]);
					int packageID = Convert.ToInt32(dtLog.Rows[0]["PackageID"]);
					//int sbcrid = Services.Subscription.SubscriptionService.SubscribeToPackage(merchantID, packageID, "Active");
					string cardNumberHashed = ""; // Get card number hashed from responseData
												  // Services.Subscription.SubscriptionService.SetPaymentSource(merchantID, sbcrid, "Card", cardNumberHashed);
					return 1; //sbcrid;
				}
			}
			catch(Exception ex)
			{
				log.Error(ex);
				throw new Exception("Execution failed. The subscription failed due to some unexpected failures.");
			}
            return 0;
        }

        public static int InitiateSubscription(int merchantID, int planID, int priceID, string requestData, string uuid, bool isUpdate)
        {
			//string sqlAdditional = "if(not exists(select * from S_MerchantSubscriptions where MerchantID=@MerchantID and PriceID=@PackageID)) begin INSERT INTO S_MerchantSubscriptions(MerchantID,PlanID,PriceID,StartDate,ExpiryDate,PaymentStatus) VALUES(@MerchantID, @planID, @PackageID, getutcdate(), getutcdate(), 'Initialized'); end ";
            string strSql = " INSERT INTO Payment_Logs (MerchantID, SubscriptionID, PackageID, RequestData, uniqId, Status, paymentGateway) OUTPUT INSERTED.LogID VALUES (@MerchantID, @planID, @PackageID, @RequestData, @uuid, @status, @paymentGateway)";
			//if (!isUpdate)
			//	strSql = sqlAdditional + strSql;

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("MerchantID", merchantID),
                new KeyValuePair<string, object>("RequestData", requestData),
                new KeyValuePair<string, object>("PackageID", priceID),
				new KeyValuePair<string, object>("planID", planID),
                new KeyValuePair<string, object>("uuid", uuid),
                new KeyValuePair<string, object>("status", "Pending"),
                new KeyValuePair<string, object>("paymentGateway", "Stripe")
            };

            int logID = (int)DataService.ExecuteScalar(strSql, parmeters: prms); // Get log ID for future updates
            return logID;
        }

		public static dynamic CreateSubscriptionSession(string domain, int priceId, int merchantId)
		{
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("priceid", priceId), new KeyValuePair<string, object>("storeId", merchantId) };
            var tblSubscriptionPrice = DataService.GetDataTable("select p.*, s.[Type] as PlanType, s.PlanName, ms.PGType, ms.PGSubscriptionId, ms.PaymentStatus as MSPaymentStatus, ms.[Status] as MSStatus, " +
                "ms.PriceID as MSPriceId, (select top 1 PGCustomerId from S_MerchantSubscriptions where MerchantID=@storeId and isnull(PGCustomerId, '') <> '') as PGCustomerId " +
                "from S_PlanPricing p inner join S_SubscriptionPlans s on p.PlanID=s.Id left join S_MerchantSubscriptions ms on ms.PlanID=p.PlanID and ms.MerchantID=@storeId and ms.[Status]=1 where PlanPricingID = @priceid", "", input);
            if (tblSubscriptionPrice == null || tblSubscriptionPrice.Rows.Count <= 0)
                throw new Exception("The subscription data is not available at the moment. Please contact support for more details.");

			int planType = -1; try { planType = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanType"]); } catch { planType = -1; }
			int oldPriceId = -1; try { oldPriceId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["MSPriceId"]); } catch { oldPriceId = 0; }
			int planGroupId = -1; try { planGroupId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["GroupId"]); } catch { planGroupId = -1; }
			int planId = 0; try { planId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanID"]); } catch { planId = 0; }
            string oldPGsubscriptionId = tblSubscriptionPrice.Rows[0]["PGSubscriptionId"].ToString();

            string pgPriceId = tblSubscriptionPrice.Rows[0]["GatewayPriceid"].ToString();
            //var domain = Request.Url.Scheme + "://" + Request.Url.Authority; // Get your website's domain

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");
            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();

            StripeConfiguration.ApiKey = strSecretKey;

            var options = new SessionCreateOptions
            {
                PaymentMethodTypes = new List<string>
            {
                "card",
            },
                LineItems = new List<SessionLineItemOptions>
            {
                new SessionLineItemOptions
                {
                    Price = pgPriceId,
                    Quantity = 1,
                },
            },
                Mode = "subscription",
                SuccessUrl = domain + "/tenant/subscription?session_id={CHECKOUT_SESSION_ID}",
                CancelUrl = domain + "/tenant/subscription?action=cancel",
            };

            var service = new SessionService();
            Session session = service.Create(options);
            string strRequestData = JsonConvert.SerializeObject(session);
            InitiateSubscription(merchantId, planId, priceId, strRequestData, session.Id, !string.IsNullOrEmpty(oldPGsubscriptionId));

            dynamic result = new System.Dynamic.ExpandoObject();
			result.url = session.Url;
			result.sessionId = session.Id;
            return result;
        }

		public bool CreateSubscriptionSuccess(string sessionId, int merchantId, string refCode)
		{
            if (string.IsNullOrEmpty(sessionId))
            {
				throw new Exception("Invalid operation. The session is not available");
            }

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");
            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();

            StripeConfiguration.ApiKey = strSecretKey;
            var service = new SessionService();
            Session session = service.Get(sessionId);

            if (session.PaymentStatus == "paid" || session.Status == "complete")
            {
                // Subscription successful. You can retrieve customer and subscription details.
                var subscriptionId = session.SubscriptionId;
                var customerId = session.CustomerId;

				string sql = "select top 1 * from Payment_Logs where uniqId=@sessionId";
                var tblStripeLog = DataService.GetDataTable(sql, parmeters: new List<KeyValuePair<string, object>> {new KeyValuePair<string, object>("sessionId", sessionId) });
				if (tblStripeLog == null || tblStripeLog.Rows.Count <= 0)
					throw new Exception("There is a technical failure in the subscription mapping.Please contact support for more details.");

				int priceId = 0; try { priceId = Convert.ToInt32(tblStripeLog.Rows[0]["PackageID"]); } catch { priceId = 0; }
				int planId = 0; try { planId = Convert.ToInt32(tblStripeLog.Rows[0]["SubscriptionID"]); } catch {  planId = 0; }
				int keyId = 0; try { keyId= Convert.ToInt32(tblStripeLog.Rows[0]["LogID"]); } catch { keyId = 0; }

                List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("priceid", priceId), new KeyValuePair<string, object>("storeId", merchantId) };
                var tblSubscriptionPrice = DataService.GetDataTable("select p.*, s.[Type] as PlanType, s.PlanName, ms.PGType, ms.PGSubscriptionId, ms.PaymentStatus as MSPaymentStatus, ms.[Status] as MSStatus, " +
                    "ms.PriceID as MSPriceId, (select top 1 PGCustomerId from S_MerchantSubscriptions where MerchantID=@storeId and isnull(PGCustomerId, '') <> '') as PGCustomerId " +
                    "from S_PlanPricing p inner join S_SubscriptionPlans s on p.PlanID=s.Id left join S_MerchantSubscriptions ms on ms.PlanID=p.PlanID and ms.MerchantID=@storeId and ms.[Status]=1 where PlanPricingID = @priceid", "", input);
                if (tblSubscriptionPrice == null || tblSubscriptionPrice.Rows.Count <= 0)
                    throw new Exception("The subscription data is not available at the moment. Please contact support for more details.");

                int planType = -1; try { planType = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanType"]); } catch { planType = -1; }
                int oldPriceId = -1; try { oldPriceId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["MSPriceId"]); } catch { oldPriceId = 0; }
                int planGroupId = -1; try { planGroupId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["GroupId"]); } catch { planGroupId = -1; }
				//int planId = 0; try { planId = Convert.ToInt32(tblSubscriptionPrice.Rows[0]["PlanID"]); } catch { planId = 0; }
				string planName = tblSubscriptionPrice.Rows[0]["PlanName"].ToString();
                string oldPGsubscriptionId = tblSubscriptionPrice.Rows[0]["PGSubscriptionId"].ToString();

                string pgPriceId = tblSubscriptionPrice.Rows[0]["GatewayPriceid"].ToString();

                dynamic result = new System.Dynamic.ExpandoObject();
                result.LatestInvoiceId = session.InvoiceId;
                result.CurrentPeriodStart = session.Created;
                result.CurrentPeriodEnd = session.ExpiresAt;
                result.Id = session.SubscriptionId;
                result.CustomerId = session.CustomerId;
				result.Data = session.StripeResponse;

                // Enable the subscription in DB.
                CompletePayment(merchantId, planId, priceId, result, keyId, planName, true, refCode);

                try
                {
                    // Cancel the old subscription in stripe since new one created.
                    if (oldPriceId > 0)//(isSuccessful && !string.IsNullOrEmpty(oldPGsubscriptionId))
                        CancelSubscription(oldPGsubscriptionId, strSecretKey, merchantId, planId, oldPriceId, planName);
                }
                catch (Exception ex) { log.Error(ex); }

                if (planType == 0 && planGroupId > 0)
                    UpdateFeatureSubscriptions(planGroupId, merchantId, strSecretKey);


                return true;
            }
            else
            {
				return false;
            }

        }

		/// <summary>
		/// 
		/// </summary>
		/// <returns></returns>
		public static dynamic Connect_InitializeSubAccount(string domain, int storeGroupId)
		{

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
			if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
				throw new Exception("Operation failed. There is a technical failure happened related to the payment configurations. Please try again later or contact support for more details.");

            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();
			StripeConfiguration.ApiKey = strSecretKey;// "STRIPE_SECRET_KEY";

            var accountOptions = new AccountCreateOptions
            {
                Type = "express",
            };

            var accountService = new AccountService();
            var account = accountService.Create(accountOptions);

            var accountLinkOptions = new AccountLinkCreateOptions
            {
                Account = account.Id,
                RefreshUrl = $"{domain}/tenant/paymentconfig?type=0", // type 0 = refresh URL
                ReturnUrl = $"{domain}/tenant/paymentconfig?type=1", // type 1 = return URL
                Type = "account_onboarding",
            };

            var accountLinkService = new AccountLinkService();
            var accountLink = accountLinkService.Create(accountLinkOptions);
			string strRequestData = JsonConvert.SerializeObject(accountLink);

			string strSql = "INSERT INTO store_paymentgateway_connect(pgType, pgName, accountId, storeGroupId, `status`, requestData) " +
                "VALUES(@pgType, @pgName, @accountId, @storeGroupId, @status, @requestData);  select LAST_INSERT_ID()";

			var logId = DataServiceMySql.ExecuteScalar(strSql, parmeters: new List<KeyValuePair<string, object>> {
				new KeyValuePair<string, object>("pgType", 1), new KeyValuePair<string, object>("pgName", "stripe"), new KeyValuePair<string, object>("accountId", account.Id),
				new KeyValuePair<string, object>("storeGroupId", storeGroupId), new KeyValuePair<string, object>("status", 0), new KeyValuePair<string, object>("requestData", strRequestData)
			});

            dynamic result = new ExpandoObject();
            result.url = accountLink.Url;
            result.logId = logId;
			result.accountId = account.Id;
            return result;

        }

		public static void Connect_LinkSubAccount(string json, string stripeSignature, int apiStoreId)
		{
            try
            {
                var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT webhook_key FROM `finascop_company_stripe` LIMIT 1");
                if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                    throw new Exception("Operation failed. There is a technical failure happened related to the payment configurations. Please try again later or contact support for more details.");

                var tblAccountExisting = DataServiceMySql.GetDataTable("SELECT pg_subAccountId FROM `finascop_branch_group` where store_group_id = @storeId limit 1", parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storeId", apiStoreId) });
                if (tblAccountExisting == null || tblAccountExisting.Rows.Count <= 0)
                    throw new Exception("Invalid store operation. There is a technical failure happened related to the payment configurations. Please try again later or contact support for more details.");

				string strExistingId = tblAccountExisting.Rows[0]["pg_subAccountId"].ToString();
				if (!string.IsNullOrEmpty(strExistingId))
					throw new Exception("The process cannot execute at this time because of the store is already linked with sub account.");

                string endpointSecret = tblStripeConfig.Rows[0]["webhook_key"].ToString(); // "YOUR_WEBHOOK_SECRET"; // Replace with webhook secret
                var stripeEvent = EventUtility.ConstructEvent(json, stripeSignature, endpointSecret);

                if (stripeEvent.Type == "account.updated") //Stripe.Events.AccountUpdated)
                {
                    var account = stripeEvent.Data.Object as Account;
                    string accountId = account.Id;

					string strSql = "UPDATE finascop_branch_group SET pg_subAccountId = @accountId WHERE store_group_id = @storeId";
					DataServiceMySql.ExecuteSql(strSql, parmeters: new List<KeyValuePair<string, object>> {new KeyValuePair<string, object>("accountId", accountId), new KeyValuePair<string, object>("storeId", apiStoreId) });
                }
                else
                {
					throw new Exception("Invalid event type. The process cannot execute");
                    //lblMessage.Text = "Event Type: " + stripeEvent.Type;
                }
            }
            catch (StripeException ex)
            {
				log.Error($"Stripe exception on linkSubAccount for merchant id: {apiStoreId}", ex);
				throw new Exception("Invalid operation. The payment gateway failed to execute the add account");
                //"Stripe Exception: " + ex.Message;
                //Response.StatusCode = 400; // Bad Request
            }
            catch (Exception ex)
            {
                log.Error($"Error on linkSubAccount for merchant id: {apiStoreId}", ex);
				throw new Exception("There is a technical failure on connect account. Please contact support for more details.");
                //"General Exception: " + ex.Message;
                //Response.StatusCode = 500; // Internal Server Error
            }


        }

        public static dynamic CheckAccountStatus(int logId, string connectedAccountId)
        {
			if (logId <= 0 || string.IsNullOrEmpty(connectedAccountId))
				throw new Exception("Invalid account linking.");

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, secret_key, currency FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0)
                throw new Exception("Operation failed. There is a technical failure happened related to the payment configurations. Please try again later or contact support for more details.");

            string strSecretKey = tblStripeConfig.Rows[0]["secret_key"].ToString();
            StripeConfiguration.ApiKey = strSecretKey; // "STRIPE_SECRET_KEY";

            //string connectedAccountId = GetConnectedAccountIdFromDatabase();
            dynamic result = new ExpandoObject();
            try
            {
                DataTable tblStripeConnect = DataServiceMySql.GetDataTable("SELECT * from store_paymentgateway_connect where id=@logId and `status` = 0", parmeters: new List<KeyValuePair<string, object>> {new KeyValuePair<string, object>("logId", logId) });
				if(tblStripeConnect == null || tblStripeConnect.Rows.Count <= 0 || tblStripeConnect.Rows[0]["accountId"].ToString() != connectedAccountId)
				{
                    result.success = 0;
                    result.msg = "Invalid account linking. Please try again later.";
					return result;
                }

                var accountService = new AccountService();
                var account = accountService.Get(connectedAccountId);

				

                if (account.ChargesEnabled && account.PayoutsEnabled)
                {
					string bankName = "", bankAccountName = "", bankAccountNum = "";
					try
					{
						BankAccount bank = account.ExternalAccounts.Data.Where(d => d is BankAccount && (d as BankAccount).AccountId == connectedAccountId).Select(d=> d as BankAccount).FirstOrDefault();
						if (bank != null)
						{
							try
							{
								bankName = bank.BankName; 
								bankAccountName = bank.AccountHolderName;
								bankAccountNum = bank.Last4;
							}
							catch { }
						}
					}
					catch { }
					string strSql = "UPDATE store_paymentgateway_connect SET `status` = 1, responseData = @responseObj, bankName = @bankName, bankAccountName=@bankAccountName, bankAccountNum = @bankAccountNum WHERE id=@logId";
					string strResponse = JsonConvert.SerializeObject(account);
					DataServiceMySql.ExecuteSql(strSql, parmeters: new List<KeyValuePair<string, object>> { 
						new KeyValuePair<string, object>("logId", logId), 
						new KeyValuePair<string, object>("responseObj", strResponse),
						new KeyValuePair<string, object>("bankName", bankName),
						new KeyValuePair<string, object>("bankAccountName", bankAccountName),
						new KeyValuePair<string, object>("bankAccountNum", bankAccountNum)
					});
                    result.success = 1;
                    result.msg = "Account connected successfully!";
					return result;
                }
                else
                {
                    result.success = 0;
                    result.msg = "Failure, connection incomplete. Please check your dashboard.";
                }
            }
            catch (StripeException ex)
            {
                result.success = 0;
                result.msg = "Failure, Error checking account status.";
            }

			return result;
        }

		public static void ConvertObject(string strObj)
		{
			var account = JsonConvert.DeserializeObject<Stripe.Account>(strObj);
			if(account != null)
			{

			}

        }

        public static List<(string AccountId, string TransferId, bool IsSuccess, string ErrorMessage)> TransferToAccounts(Dictionary<string, decimal> accountAmountMap, string currency = "gbp")
        {
            if (accountAmountMap == null || accountAmountMap.Count == 0)
                throw new ArgumentException("No accounts provided for transfer.");

            var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT secret_key FROM `finascop_company_stripe` LIMIT 1");
            if (tblStripeConfig == null || tblStripeConfig.Rows.Count == 0)
                throw new InvalidOperationException("Payment configuration is missing. Please contact support.");

            string secretKey = tblStripeConfig.Rows[0]["secret_key"]?.ToString()?.Trim();
            if (string.IsNullOrEmpty(secretKey))
                throw new InvalidOperationException("Stripe secret key is empty. Please check the payment configuration.");

            StripeConfiguration.ApiKey = secretKey;
            var transferService = new TransferService();
            var results = new List<(string AccountId, string TransferId, bool IsSuccess, string ErrorMessage)>();

            foreach (var entry in accountAmountMap)
            {
                string accountId = entry.Key?.Trim();
                decimal amount = entry.Value;

                if (string.IsNullOrWhiteSpace(accountId) || amount <= 0)
                    continue;

                var transferOptions = new TransferCreateOptions
                {
                    Amount = (long)Math.Round(amount * 100),
                    Currency = currency.ToLower(),
                    Destination = accountId,
                    Description = $"Transfer to connected account {accountId}"
                };

                try
                {
                    var transfer = transferService.Create(transferOptions);
                    results.Add((accountId, transfer.Id, true, null));
                }
                catch (StripeException ex)
                {
                    results.Add((accountId, null, false, ex.StripeError?.Message ?? "Stripe error"));
                }
                catch (Exception ex)
                {
                    results.Add((accountId, null, false, ex.Message));
                }
            }

            return results;
        }


    }

}
