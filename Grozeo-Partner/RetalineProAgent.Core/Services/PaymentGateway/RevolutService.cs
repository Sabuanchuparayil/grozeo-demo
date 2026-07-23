using Newtonsoft.Json;
using RestSharp;
using RetalineProAgent.Core.Services.Subscription;
using SendGrid.Helpers.Mail;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Dynamic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.PaymentGateway
{
    public class RevolutService: IPaymentService
	{
		/// <summary>
		/// PaymentRequest
		/// </summary>
		/// <param name="amount"></param>
		/// <returns></returns>
		public dynamic PaymentRequest(int merchantId, int packageId, decimal amount, string paymentMehtodId = "")
        {
            // "merchant_order_ext_ref": "[orderId]", "payment_methods": ["card"]
            string orderId = Guid.NewGuid().ToString(); // "[orderId]";
            string jsonData = "{\"amount\": 0, \"currency\": \"USD\", \"save_payment_method_for_merchant\": true, \"merchant_order_ext_ref\": \"[orderId]\", \"payment_methods\": [\"card\"]}";

            int keyId = SubscriptionService.InitiateSubscription(merchantId, 1, jsonData, orderId, "Revolut");
            //jsonData = jsonData.Replace("[orderId]", keyId.ToString());

            string revolutUrl = ConfigurationManager.AppSettings.Get("PaymentGateway") + "/api/orders";
            var client = new RestClient(revolutUrl);
            var request = new RestRequest();
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("Accept", "application/json");
			request.AddHeader("Authorization", String.Format("Bearer {0}", ConfigurationManager.AppSettings.Get("PaymentGatewaykey")));
            request.AddHeader("Revolut-Api-Version", "2024-09-01");

            request.AddBody(jsonData, "application/json");
            var response = client.Execute<dynamic>(request);
            if(response != null && !String.IsNullOrEmpty(response.Content)) {
                DataService.ExecuteSql("update Payment_Logs set ResponseData=@responseData where LogID=@keyId", parmeters: new List<KeyValuePair<string, object>> {new KeyValuePair<string, object>("keyId", keyId), new KeyValuePair<string, object>("responseData", response.Content) });
                dynamic revolutResult = Newtonsoft.Json.JsonConvert.DeserializeObject<dynamic>(response.Content);
				if (revolutResult != null && revolutResult.token != null && !String.IsNullOrEmpty(revolutResult.token.ToString()))
				{
					dynamic obj = new ExpandoObject();
                    obj.token = revolutResult.token.ToString();
                    obj.orderId = orderId; // keyId;
                    obj.id = revolutResult.id;
                    return obj;
				}
            }

            return response;

        }

        /// <summary>
        /// ProcessPayment
        /// </summary>
        /// <param name="data">Dynamic object</param>
        /// <param name="merchantId"></param>
        /// <returns></returns>
        /// <exception cref="Exception"></exception>
		public int ProcessPayment(dynamic data, int merchantId)
        {
			if (data == null || data.id == null || string.IsNullOrEmpty(data.id.ToString()) || data.orderId == null || string.IsNullOrEmpty(data.orderId.ToString()) || merchantId < 1)
				throw new Exception("Invalid operation");

            string strPaymentId = data.id.ToString(); string strOrderId = data.orderId.ToString();

			List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>(){new KeyValuePair<string, object>("MerchantID", merchantId),
				new KeyValuePair<string, object>("id", strOrderId), new KeyValuePair<string, object>("paymentId", strPaymentId)};
			DataTable dtLog = DataService.GetDataTable("select * from Payment_Logs where uniqId = @id and MerchantID= @MerchantID and responseData like concat('%', @paymentId, '%') and Status = 'Pending'", parmeters: sqlparams);
            if (dtLog == null || dtLog.Rows.Count < 1)
                throw new Exception("Invalid operation. Please contact support for more details");
				//return Json(new { result = 0, status = "Error", message = "Invalid operation. Please contact support for more details" });

			var options = new RestClientOptions(ConfigurationManager.AppSettings.Get("PaymentGateway"))
			{
				MaxTimeout = -1,
			};
			var client = new RestClient(options);
			var request = new RestRequest($"{ConfigurationManager.AppSettings.Get("PaymentGateway")}/api/orders/{strPaymentId}/payments", Method.Get);
			request.AddHeader("Accept", "application/json");
			request.AddHeader("Authorization", String.Format("Bearer {0}", ConfigurationManager.AppSettings.Get("PaymentGatewaykey")));
			RestResponse response = client.Execute(request);
            if (response == null || String.IsNullOrEmpty(response.Content))
                throw new Exception("Operation failed. Gateway error occurred!");

			List<RevolutPayment> content = Newtonsoft.Json.JsonConvert.DeserializeObject<List<RevolutPayment>>(response.Content);
			int subscriptionid = SubscriptionService.ProcessPayment(JsonConvert.SerializeObject(content), Convert.ToInt32(dtLog.Rows[0]["LogID"]), strOrderId, content.Any(c => c.state == "completed"), "Revolut");

			return subscriptionid;

        }
    }

    public class RevolutPayment
    {
        public string id { get; set; }
        public string order_id { get; set; }
        public string state { get; set; }
        public RevolutPaymentMethod payment_method { get; set; }
    }

    public class RevolutPaymentMethod
    {
        public string type { get; set; }
        public string brand { get; set; }
        public int last_four { get; set; }
    }

}
