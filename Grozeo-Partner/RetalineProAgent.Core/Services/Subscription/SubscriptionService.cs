using Amazon.Runtime.Internal;
using log4net;
using System;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.Subscription
{
    public class SubscriptionService
    {
        private static readonly ILog log = LogManager.GetLogger(typeof(SubscriptionService));
        /// <summary>
        /// InitiateSubscription
        /// </summary>
        /// <param name="merchantID">Merchant Id</param>
        /// <param name="packageID">Package Id</param>
        /// <param name="subscriptionID">Subscription id</param>
        /// <param name="requestData">Payment gateway API request data</param>
        /// <param name="status">'Pending', 'Success', 'Failed'</param>
        /// <returns>Log id</returns>
        public static int InitiateSubscription(int merchantID, int priceID, string requestData, string uuid, string paymentGateway, string status= "Pending")
        {
            string strSql = "INSERT INTO Payment_Logs (MerchantID, PackageID, RequestData, uniqId, Status, paymentGateway) OUTPUT INSERTED.LogID VALUES (@MerchantID, @PackageID, @RequestData, @uuid, @status, @paymentGateway)";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("MerchantID", merchantID),
                new KeyValuePair<string, object>("RequestData", requestData),
                new KeyValuePair<string, object>("PackageID", priceID),
                new KeyValuePair<string, object>("uuid", uuid),
                new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("paymentGateway", paymentGateway)
            };

            int logID = (int)DataService.ExecuteScalar(strSql, parmeters: prms); // Get log ID for future updates
            return logID;
        }

        /// <summary>
        /// SubscribeToPackage
        /// </summary>
        /// <param name="merchantID">Merchant id</param>
        /// <param name="packageID">Package Id</param>
        /// <param name="status">'Pending', 'Active', 'Failed', 'Canceled'</param>
        /// <returns>Subscription id</returns>
        public static int SubscribeToPackage(int merchantID, int packageID, string status= "Pending")
        {
            string strSql = "DECLARE @Id AS INT = 0; IF EXISTS(select * from [Merchant_Subscriptions] where [MerchantID] = @MerchantID and [PackageID] = @PackageID) BEGIN" +
				" UPDATE Merchant_Subscriptions SET [Status]= @status, @Id= SubscriptionID, BillingCycleStartDate = GETDATE(), NextBillingDate = DATEADD(MONTH, 1, GETDATE()) " +
                " where [MerchantID] = @MerchantID and [PackageID] = @PackageID; END " +
				" ELSE BEGIN INSERT INTO Merchant_Subscriptions (MerchantID, PackageID, SubscriptionStartDate, BillingCycleStartDate, NextBillingDate) " +
                "VALUES (@MerchantID, @PackageID, GETDATE(), GETDATE(), DATEADD(MONTH, 1, GETDATE())); SET @Id = (select scope_identity()); END; SELECT @Id;";

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() { 
                new KeyValuePair<string, object>("MerchantID", merchantID),
                new KeyValuePair<string, object>("PackageID", packageID),
                new KeyValuePair<string, object>("status", status),
            };

            object obj = DataService.ExecuteScalar(strSql, parmeters: prms);
            int subscriptionId = Convert.ToInt32(obj);

			return subscriptionId;
        }

        /// <summary>
        /// ProcessPayment - Add payment log
        /// </summary>
        /// <param name="responseData">Response from gateway</param>
        /// <param name="logID">Log Id</param>
        /// <param name="TransactionID">Transaction Id</param>
        /// <param name="isSuccessful">Success / Failed</param>
        /// <returns>Subscription Id</returns>
        public static int ProcessPayment(string responseData, int logID, string TransactionID, bool isSuccessful, string paymentGateway)
        {
            int subscriptionid = -1;

			string strSql = "UPDATE Payment_Logs SET ResponseData = @ResponseData, Status = @Status, GatewayTransactionID = @TransactionID, LastUpdatedDate = @UpdateDate WHERE LogID = @LogID and Status not like 'Success'; " +
                "select * from Payment_Logs where LogID = @LogID";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("ResponseData", responseData),
                new KeyValuePair<string, object>("Status", isSuccessful ? "Success" : "Failed"),
                new KeyValuePair<string, object>("TransactionID", TransactionID), // Extract transaction ID from the response
                new KeyValuePair<string, object>("UpdateDate", DateTime.Now),
                new KeyValuePair<string, object>("LogID", logID)
            };

            // Update Payment_Logs with the response and update subscription status accordingly
            DataTable dtLog = DataService.GetDataTable(strSql, parmeters: prms);

            // Activate subscription based on the response
            if (isSuccessful && dtLog != null && dtLog.Rows.Count > 0)
            {
                int merchantID = Convert.ToInt32(dtLog.Rows[0]["MerchantID"]);
                int packageID = Convert.ToInt32(dtLog.Rows[0]["PackageID"]);
                subscriptionid = SubscribeToPackage(merchantID, packageID, "Active");
                string cardNumberHashed = ""; // Get card number hashed from responseData
                SetPaymentSource(merchantID, subscriptionid, "Card", cardNumberHashed);
            }

            return subscriptionid;
        }

        /// <summary>
        /// SetPaymentSource
        /// </summary>
        /// <param name="merchantID">Tenant Id</param>
        /// <param name="subscriptionID">Subscription Id</param>
        /// <param name="SourceDetails">Store card number (hashed), fund details, ledger ID, etc.</param>
        /// <param name="SourceType">'Card', 'Fund', 'Investment', 'Ledger'</param>
        /// <param name="paymentStatus">'Active', 'Failed', 'Inactive'</param>
        public static void SetPaymentSource(int merchantID, int subscriptionID, string SourceType, string SourceDetails = "", string paymentStatus = "Active")
        {
            string strSql = "insert into Payment_Sources(MerchantID, SourceType, SourceDetails) values(@MerchantID, @SourceType, @SourceDetails); " +
                " INSERT INTO Subscription_Payment_Methods (SubscriptionID, SourceID, PaymentStatus) VALUES (@SubscriptionID, (select scope_identity()), @paymentStatus); " +
                " UPDATE AppTenant SET HasPaymentMethod = 1 WHERE Id=@MerchantID";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("SubscriptionID", subscriptionID),
                new KeyValuePair<string, object>("SourceDetails", SourceDetails),
                new KeyValuePair<string, object>("SourceType", SourceType),
                new KeyValuePair<string, object>("MerchantID", merchantID),
                new KeyValuePair<string, object>("paymentStatus", paymentStatus),
            };

            DataService.ExecuteSql(strSql, parmeters: prms);
        }



    }


}
