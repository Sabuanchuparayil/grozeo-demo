using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using RestSharp;
using System.Text.Json;
using RetalineProAgent.Core.Services;
using Finascop.BussinessModel;
using System.Data;
using Newtonsoft.Json;
using Finascop.BussinessModel.Finascop;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Finance;
using System.Data.SqlClient;
using System.Security.Cryptography;


namespace Finascop.Services
{
    public static class StoreService
    {
        /// <summary>
        /// Create Store ledger
        /// </summary>
        /// <param name="name">name</param>
        /// <param name="mobile">mobile no.</param>
        /// <param name="strConnection">Service.UserService.GetAPIConnectionString()</param>
        /// <returns></returns>
        //public static async Task<Result> PackingVoucher(string orderId, string strConnection)//double salesamount, double tenantSales, double tenantDelivery, double tenatCGST,double tenantSOrCTGST)
        //{
        //    string fsto_id = orderId;
        //    TransactionEntry voucher = new TransactionEntry();
        //    DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id, order_id, rco.order_branch_id," +
        //        $" GROUP_CONCAT(DISTINCT order_order_id) AS orders, SUM(item_sales_price) AS selling_price," +
        //        $" SUM(order_total_sgst) AS sgst, SUM(order_total_cgst) AS cgst" +
        //        $" FROM retaline_customer_order_items rcoi" +
        //        $" INNER JOIN finascop_stock_transfer_order fsto" +
        //        $" ON rcoi.customer_order_id = fsto.fstr_id" +
        //        $" INNER JOIN retaline_customer_order rco" +
        //        $" ON order_id = fsto.fstr_id" +
        //        $" WHERE fsto_id = {fsto_id}", strConnection);

        //    if (dtOrderDetails != null && dtOrderDetails.Rows.Count > 0)
        //    {
        //        DataRow dr = dtOrderDetails.Rows[0];


        //        voucher.TransactionTypeId = TransactionType.Journal;

        //        voucher.narration = "Sales Order : " + dr["orders"];
        //        double tenantCustomerWalletAmt = (double)dr["selling_price"] * 4.35 / 100;
        //        double tenantSales = ((double)dr["selling_price"] - ((double)dr["cgst"] + (double)dr["sgst"] + tenantCustomerWalletAmt)) * 86 / 100;
        //        double tenantDeliveryCharge = ((double)dr["selling_price"] - ((double)dr["cgst"] + (double)dr["sgst"] + tenantCustomerWalletAmt)) * 86 / 100;

        //        voucher.Account = new TransactionData() { isDebtor = 1, ledgerId = 20, amount = (double)dr["selling_price"] };
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 22, particulars = "Tenant Sales", amount = tenantSales });
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 23, particulars = "Tenant Delivery", amount = tenantDeliveryCharge });
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 35, particulars = "Tenant CGST", amount = (double)dr["cgst"] });
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 36, particulars = "Tenant SGST/UTGST", amount = (double)dr["sgst"] });
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 56, particulars = "Customer Vallet Example", amount = tenantCustomerWalletAmt });
        //        double roundOff = ((double)dr["selling_price"] - ((double)dr["cgst"] + (double)dr["sgst"] + tenantCustomerWalletAmt + tenantSales + tenantDeliveryCharge));
        //        voucher.Particulars.Add(new TransactionData() { ledgerId = 33, particulars = "Round Off", amount = roundOff });

        //    }

        //    string url = "https://finascopdataentry.azurewebsites.net";

        //    string content = JsonSerializer.Serialize(voucher); //$"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";

        //    var client = new RestClient(url);
        //    var request = new RestRequest();// (Method.Post);
        //    request.Method = Method.Post;
        //    request.AddHeader("content-type", "application/json");
        //    request.AddHeader("x-functions-key", "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

        //    request.AddBody("{" + content + "}", "application/json");
        //    var response = await client.ExecuteAsync<Result>(request);


        //    return response.Data;



        //}


        public static async Task StoreGroupCreate(string storeName, string mobile, int apistoregroupid, string storeRefId)
        {
            string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
            if (String.IsNullOrEmpty(url))
                url = "https://finascopdataentry.azurewebsites.net/api/";
            url += "CreateTenantLedger";

            string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
            if (String.IsNullOrEmpty(key))
                key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";

            //string url = "https://finascopdataentry.azurewebsites.net/api/CreateTenantLedger";
            List<KeyValuePair<string, object>> data = new List<KeyValuePair<string, object>>();
            data.Add(new KeyValuePair<string, object>("name", storeName));
            data.Add(new KeyValuePair<string, object>("mobile", mobile));


            string content = $"\"name\": \"{storeName}\", \"mobile\": \"{mobile}\", \"refid\": \"{storeRefId}\"";

            var client = new RestClient(url);
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            //request.AddHeader("content-type", "application/json");
            request.AddHeader("x-functions-key", key); // "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

            request.AddBody("{" + content + "}", "application/json");
            var findata = await client.ExecuteAsync<Result>(request);
            Result result = JsonConvert.DeserializeObject<Result>(findata.Content);
        }


        public static string GenerateRefId()
        {
            byte[] time = BitConverter.GetBytes(DateTime.UtcNow.ToBinary());
            byte[] key = Guid.NewGuid().ToByteArray();
            byte[] rand = BitConverter.GetBytes(new Random().Next(10000, 90000));
            byte[] hash;

            using (var sha1 = SHA1.Create())
            {
                byte[] data = new byte[time.Length + key.Length + rand.Length];
                Array.Copy(time, data, time.Length);
                Array.Copy(key, 0, data, time.Length, key.Length);
                Array.Copy(rand, 0, data, time.Length + key.Length, rand.Length);
                hash = sha1.ComputeHash(data);
            }

            return Convert.ToBase64String(hash);
        }


        /// <summary>
        /// Get Store group reference id (generated at finascop)
        /// </summary>
        /// <param name="storeGroupId">Store group id</param>
        /// <param name="strConnection">Mysql Connectionstring</param>
        /// <param name="brid">Branch id (Optional)</param>
        /// <returns>Store reference id</returns>
        public static string[] GetStoreRefId(int storeGroupId, string strConnection, string brid = "0")
        {
            
            string storeRefId = ""; string storeGrpId = ""; string storeGrpName = ""; string storeGroupBranchName = "";
            string sqlSelStoreRef = $"SELECT storeRefId FROM finascop_branch_group " +
            $"WHERE store_group_id =  @storegroupid";
            List<KeyValuePair<string, object>> selStoreRefParams = new List<KeyValuePair<string, object>>();
            selStoreRefParams.Add(new KeyValuePair<string, object>("storegroupid", storeGroupId));
            selStoreRefParams.Add(new KeyValuePair<string, object>("brid", brid));
            if (Convert.ToInt32(brid) > 0) // if order was from grozeo
            {
               // sqlSelStoreGrpRef = $"SELECT br_storeGroup FROM finascop_branch WHERE br_ID=@brid";
                sqlSelStoreRef = $"SELECT storeRefId,br_storeGroup,store_group_name,br_Name FROM finascop_branch_group g inner join finascop_branch b on g.store_group_id = b.br_storeGroup" +
                $" WHERE br_ID=@brid";
                
            }

            DataTable dtRefId = DataServiceMySql.GetDataTable(sqlSelStoreRef, strConnection, selStoreRefParams);
            if (dtRefId != null && dtRefId.Rows.Count > 0)
            {
                try
                {
                    DataRow dt = dtRefId.Rows[0];
                    storeRefId = dt["storeRefId"].ToString();
                    storeGrpId = dt["br_storeGroup"].ToString();
                    storeGrpName = dt["store_group_name"].ToString();
                    storeGroupBranchName = dt["br_Name"].ToString();
                }
                catch { }
            }
            string[] streDetails = new[] { storeRefId, storeGrpId, storeGrpName, storeGroupBranchName };
            //return storeRefId;
            return streDetails;
        }

        public static string GetAPIConnectionString()
        {
            return ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString.Replace("{0}", ConfigurationManager.AppSettings["api.DefaultDB"]);
        }
        public static string getSalesOrderRefId(string order_id)
        {
            string query = $"SELECT entry_RefId FROM retaline_customer_order WHERE order_id = @order_id";
            List<KeyValuePair<string, object>> dprms = new List<KeyValuePair<string, object>>();
            dprms.Add(new KeyValuePair<string, object>("order_id", order_id));
            string connString = GetAPIConnectionString();
            var entry_RefId = DataServiceMySql.ExecuteScalar(query, connString, dprms);
            return (string) entry_RefId;
        }


    }
}
