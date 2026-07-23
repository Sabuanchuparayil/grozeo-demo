using Amazon.Runtime.Internal.Transform;
using log4net;
using Microsoft.Azure.Management.WebSites.Models;
using Newtonsoft.Json;
using RetalineProAgent.Base;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Core.Services.Subscription;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web;
using System.Web.Http;
using System.Windows.Input;

namespace RetalineProAgent.Controller
{
	public class UpgradeController : ApiController
	{
        private static readonly ILog log = LogManager.GetLogger(typeof(UpgradeController));

        // GET api/<controller>
        public IEnumerable<string> Get()
		{
			return new string[] { "value1", "value2" };
		}

		// GET api/<controller>/5
		public string Get(int id)
		{
			return "value";
		}

		// POST api/<controller>
		//public void Post([FromBody] string value)
		//{
		//}

		public IHttpActionResult EnableSubscription([FromBody] SubscriptionModel content) {
			if (content == null || String.IsNullOrEmpty(content.token))
				return Json(new { result = 0, status = "Error", message = "Invalid token" });

			var captchaResult = Core.Services.APIService.VerifyToken(content.token);
			if (!captchaResult.Success)
				return Json(new { result = 0, status = "Error", message = "Invalid captcha" });

			Service.User user = Service.UserService.CachedDefaultUser;
			if (user == null || String.IsNullOrEmpty(user.Phone))
				return Json(new { result = 0, status = "Error", message = "Invalid user. Please re-login with your credentials and try again." });

			if (!(content.subscId > 0))
			{
				return Json(new { result = 0, status = "Error", message = "Invalid subscription. The subscription may be expired or not available at the moment. Please contact support for more details." });
			}

			List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("storeid", user.StoreGroupId), new KeyValuePair<string, object>("subscriptionid", content.subscId) };
			string sql = "declare @groupid int= (select top 1 isnull(p.GroupId, 1) as GroupId from S_MerchantSubscriptions ms inner join S_PlanPricing p on ms.PriceID=p.PlanPricingID inner join S_SubscriptionPlans s on ms.PlanID=s.Id where MerchantID=@storeId and ms.Status=1 and ms.PaymentStatus='Paid' and s.[Type]=0 order by p.GroupId desc); " +
                "select s.Id, s.PlanName, s.[Description], s.[Type], p.PlanPricingID, p.GroupId, p.BillingCycle, p.PricePerCycle, p.DurationInDays, p.Discount, isnull(ms.PriceID, -1) as MSPriceId" +
				", ms.StartDate as MSStartDate, ms.ExpiryDate as MSExpiryDate, ms.PaymentStatus as MSPaymentStatus from S_SubscriptionPlans s " +
                "INNER join S_PlanPricing p on p.PlanID=s.Id left join (select top 1 * from S_MerchantSubscriptions where MerchantID=@storeId and PlanID=@subscriptionid and [Status]=1 " +
				"and PaymentStatus='Paid') ms on 1=1 where S.Id= @subscriptionid and s.[Status] > 0 and ([Type] = 0 or p.GroupId= isnull(@groupid,1));";

			var tblSubscriptionPrice = DataService.GetDataTable(sql, "", input);
			if (tblSubscriptionPrice == null || tblSubscriptionPrice.Rows.Count <= 0)
				return Json(new { result = 0, status = "Failure", message = "The subscription data is not available at the moment. Please contact support for more details." });


			//var lstSubscriptionPrices = tblSubscriptionPrice.AsEnumerable().Select(r => new { SubscriptionId = Convert.ToInt32(r["Id"]), Name = r["PlanName"].ToString(), Description = r["Description"], PlanType = Convert.ToInt32(r["Type"]), PriceId = Convert.ToInt32(r["PlanPricingID"]), GroupId = Convert.ToInt32("GroupId"), BillingCycle = r["BillingCycle"].ToString(), PricePerCycle = r["PricePerCycle"].ToString(), DurationInDays = r["DurationInDays"].ToString(), Discount = r["Discount"].ToString() }).ToList();
			List<dynamic> lstSubscriptionPrices = new List<dynamic>();

			foreach (DataRow r in tblSubscriptionPrice.Rows)
			{
				var sbrData = new { SubscriptionId = Convert.ToInt32(r["Id"]), 
					Name = r["PlanName"].ToString(), 
					Description = r["Description"], 
					PlanType = Convert.ToInt32(r["Type"]), 
					PriceId = Convert.ToInt32(r["PlanPricingID"]), 
					GroupId = Convert.ToInt32(r["GroupId"]), 
					BillingCycle = r["BillingCycle"].ToString(), 
					PricePerCycle = r["PricePerCycle"].ToString(), 
					DurationInDays = r["DurationInDays"].ToString(), 
					Discount = r["Discount"].ToString(),
					CurPriceId= Convert.ToInt32(r["MSPriceId"])
				};

				lstSubscriptionPrices.Add(sbrData);
			}

			if (ConfigurationManager.AppSettings.Get("PaymentGateway").EndsWith(".revolut.com"))
			{
				//string baseUrl = Common.GetFullUrl("/");
				var revolutResult = (new RevolutService()).PaymentRequest(user.StoreGroupId, 1, 500);
				if (revolutResult != null && revolutResult.token != null && !String.IsNullOrEmpty(revolutResult.token.ToString()))
				{
					return Json(new { token = revolutResult.token.ToString(), pg= "revolut", subcriptionPrices= lstSubscriptionPrices });
				}
			}
			else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
			{
                var tblStripeConfig = DataServiceMySql.GetDataTable("SELECT phishable_key, currency FROM `finascop_company_stripe` LIMIT 1");
                if (tblStripeConfig == null || tblStripeConfig.Rows.Count <= 0 || string.IsNullOrEmpty(tblStripeConfig.Rows[0]["phishable_key"].ToString()))
                    throw new Exception("Failure: The system cannot proceed with the subscription at the moment.");
                string stripeKey = tblStripeConfig.Rows[0]["phishable_key"].ToString();
                //string stripeKey = ConfigurationManager.AppSettings.Get("PaymentGatewaykey");

				return Json(new { phishablekey = stripeKey, pg = "Stripe", subcriptionPrices = lstSubscriptionPrices });
			}
            else
            {
                return Json(new { phishablekey = ConfigurationManager.AppSettings.Get("PaymentGatewaykey"), pg = ConfigurationManager.AppSettings.Get("PaymentGateway"), subcriptionPrices = lstSubscriptionPrices });
            }
            return Json(new { result = 0, status = "Error", message = "Sorry, subscription is not activated. Please contact support for more details." });
		}

		[HttpGet]
		public IHttpActionResult SubscriptionFeatures(int subscriptionId)
		{
			StripeService stripeService = new StripeService();
			var features = stripeService.PlanFeatures(subscriptionId);
            return Json(new { result=1, data=features });
        }

		[HttpPost]
		public IHttpActionResult UpgradeStore([FromBody] AuthModel content)
		{
			if (content == null || String.IsNullOrEmpty(content.token))
				return Json(new { result = 0, status = "Error", message = "Invalid token" });

			var captchaResult = Core.Services.APIService.VerifyToken(content.token);
			if (!captchaResult.Success)
				return Json(new { result = 0, status = "Error", message = "Invalid captcha" });

			Service.User user = Service.UserService.CachedDefaultUser;
			if (user == null || String.IsNullOrEmpty(user.Phone))
				return Json(new { result = 0, status = "Error", message = "Invalid user. Please re-login with your credentials and try again." });

			if ((content.type ?? 1) == 1)
			{
				List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
				input.Add(new KeyValuePair<string, object>("id", user.StoreGroupId));
				var tblTenant = DataService.GetDataTable("select isnull(PackageId, 1) from AppTenant where Id=@id and PackageId > 1", "", input);
				if (tblTenant != null && tblTenant.Rows.Count > 0)
					return Json(new { result = 0, status = "Failure", message = "The store was upgraded already. Please re-login to reflect the changes." });
			}

			if (String.IsNullOrEmpty(content.mobile) && String.IsNullOrEmpty(content.otp))
			{
				string strUpgradePrice = ConfigurationManager.AppSettings.Get("UpgradePrice");
				if (String.IsNullOrEmpty(strUpgradePrice))
					strUpgradePrice = "500";

				if (!String.IsNullOrEmpty(ConfigurationManager.AppSettings.Get("PaymentGateway")))
				{
					List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
					input.Add(new KeyValuePair<string, object>("id", user.StoreGroupId));
					DataTable tblPaymentMethods = DataService.GetDataTable("select count(*) from Payment_Sources where MerchantID = @id", "", input);
					if (tblPaymentMethods != null && tblPaymentMethods.Rows.Count > 0)
					{
						if (ConfigurationManager.AppSettings.Get("PaymentGateway").EndsWith(".revolut.com"))
						{
							//string baseUrl = Common.GetFullUrl("/");
							var revolutResult = (new RevolutService()).PaymentRequest(user.StoreGroupId, 1, 500);
							if (revolutResult != null && revolutResult.token != null && !String.IsNullOrEmpty(revolutResult.token.ToString()))
							{
								return Json(new { result = 0, status = "Failure", addPaymentMethod = 1, token = revolutResult.token.ToString(), orderId = revolutResult.orderId.ToString(), id = revolutResult.id.ToString(), message = "Add payment method to subscribe." });
							}
						}
						else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
						{
							string stripeKey = ConfigurationManager.AppSettings.Get("PaymentGatewaykey");
							return Json(new { result = 0, status = "Failure", addPaymentMethod = 1, phishablekey = stripeKey, message = "Add payment method to subscribe." });

						}

					}
				}

				Dictionary<string, string> additionalValues = new Dictionary<string, string>();
				additionalValues.Add("amount", strUpgradePrice);
				var result = Core.Services.APIService.GetOtp(user.Phone, storegroupid: user.APIStoreId, templateid: 25, additionalParams: additionalValues);
				if (result != null)
					return Json(new { result = 1, status = "Success", message = "OTP send successfully" });
				else
					return Json(new { result = 0, status = "Failure", message = "OTP sending failed. Please re-login and try again or contact support for more details" });
			}
			else if (!String.IsNullOrEmpty(content.otp))
			{
				var result = Core.Services.APIService.VerifyOtp(user.Phone, content.otp);
				if (result != null && result.Data != null && result.Data.IsVerified)
				{
					List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
					sqlparams.Add(new KeyValuePair<string, object>("id", user.StoreGroupId));
					sqlparams.Add(new KeyValuePair<string, object>("username", user.FullName));
					sqlparams.Add(new KeyValuePair<string, object>("userid", user.Id));

					string sql = "INSERT INTO UpgradeHistory(TenantId, PackageId, [Name], CreatedBy, CreatedUserId) VALUES(@id, 2, 'Scale', @username, @userid); UPDATE AppTenant SET PackageId = 2 WHERE Id=@id";
					var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);
					Service.UserService.CachedDefaultUser = null;
					if (sqlresult > 0)
						return Json(new { result = 1, status = "Success", message = "Upgrade completed successfully" });
					else
						return Json(new { result = 0, status = "Failure", message = "Execution failed. Please re-login and try again or contact support." });
				}
				else
				{
					return Json(new { result = 0, status = "Error", message = "Invalid OTP or verification failed!!" });
				}

			}

			return Json(new { result = 0, status = "Error", message = "Sorry, there is a technical error happened. Please re-login with valid credetials and try again or contact support" });

		}

		/// <summary>
		/// UPgrade by payment response.
		/// </summary>
		/// <param name="paymentObj"></param>
		/// <returns></returns>
		[HttpPost]
		public IHttpActionResult UpgradeStoreByPaymentId([FromBody] object paymentObj)
		{

			dynamic data = JsonConvert.DeserializeObject(JsonConvert.SerializeObject(paymentObj));


			//var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(paymentObj), new
			//{
			//	id = string.Empty, // gateway id
			//	orderId = string.Empty, // guid
			//});

			if (data == null || data.id == null || String.IsNullOrEmpty(data.id.ToString()))
				return Json(new { result = 0, status = "Error", message = "Invalid operation. Please contact support for more details." });

			Service.User user = Service.UserService.CachedDefaultUser;
			if (user == null || String.IsNullOrEmpty(user.Phone))
				return Json(new { result = 0, status = "Error", message = "Invalid user. Please re-login with your credentials and try again." });

			try
			{
				int subscriptionId = 0;
				IPaymentService paymentService = null;
				if (ConfigurationManager.AppSettings.Get("PaymentGateway").EndsWith(".revolut.com"))
					paymentService = new RevolutService();
				else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
					paymentService = new StripeService();

				if(paymentService == null)
					return Json(new { result = 0, status = "Error", message = "Add card failed. Please verify your submit or try again later" });

				subscriptionId = paymentService.ProcessPayment(data, user.StoreGroupId);
				if (subscriptionId < 1)
					return Json(new { result = 0, status = "Error", message = "Add card failed. Please verify your submit or try again later" });
			}
			catch (Exception ex)
			{
				log.Error(ex);
				return Json(new { result = 0, status = "Error", message = "Add card failed. Please verify your submit or try again later" });
			}

			//List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
			//sqlparams = new List<KeyValuePair<string, object>>();
			//sqlparams.Add(new KeyValuePair<string, object>("id", user.StoreGroupId));
			//sqlparams.Add(new KeyValuePair<string, object>("username", user.FullName));
			//sqlparams.Add(new KeyValuePair<string, object>("userid", user.Id));

			//string sql = "INSERT INTO UpgradeHistory(TenantId, PackageId, [Name], CreatedBy, CreatedUserId) VALUES(@id, 2, 'Scale', @username, @userid); " +
			//	"UPDATE AppTenant SET PackageId = 2 WHERE Id=@id";
			//var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);
			//Service.UserService.CachedDefaultUser = null;
			//if (sqlresult > 0)
				return Json(new { result = 1, status = "Success", message = "Subscription completed successfully" });
			//else
			//	return Json(new { result = 0, status = "Failure", message = "Partially completed. Please re-login and try again or contact support." });
		}

		// PUT api/<controller>/5
		public void Put(int id, [FromBody] string value)
		{
		}

		// DELETE api/<controller>/5
		public void Delete(int id)
		{
		}
	}


	public class SubscriptionModel
	{
		public int subscId { get; set; }
		public int priceId { get; set; }
		public string token { get; set; }
		public int? type { get; set; } = 1;
		public string invitationcode { get; set; }
	}



}