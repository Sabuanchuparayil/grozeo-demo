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
using Newtonsoft.Json.Linq;
using System.Configuration;
using Newtonsoft.Json;
using RetalineProAgent.Core.BussinessModel.Finance;

namespace Finascop.Services
{
    public static class PackingService
    {
        /// <summary>
        /// Create Packing ledger
        /// </summary>
        /// <param name="orderId">order id</param>
        /// <param name="strConnection">Service.UserService.GetAPIConnectionString()</param>
        /// <returns></returns>
        public static async Task<Result> PackingVoucher(string orderId, string strConnection)
        {
            string fsto_id = orderId;
            TransactionEntry voucher = new TransactionEntry();

            DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id,storegroup_id,order_delivery_charge,order_courier_charge, " +
                $"order_order_id AS orders, order_total_amount AS selling_price, " +
                $"order_total_sgst AS sgst, order_total_cgst AS cgst,order_sales_margin " +
                $"FROM retaline_customer_order rco " +
                $"INNER JOIN finascop_stock_transfer_order fsto " +
                $"ON rco.order_id = fsto.fstr_id " +
                $"WHERE fsto_id =  {fsto_id}", strConnection);
            DataRow dr = dtOrderDetails.Rows[0];
            string strBranchId = dr["order_branch_id"].ToString();
            int storeGroupId = (int)dr["storegroup_id"];

            // Get Store ref id for finascop
            string[] storeRefId = StoreService.GetStoreRefId(storeGroupId, strConnection, strBranchId);

            if (dtOrderDetails != null && dtOrderDetails.Rows.Count > 0)
            {
                

                voucher.TransactionTypeId = TransactionType.Journal;
                voucher.docTypeID = TransactionType.Journal;

                voucher.StoreGroupName = storeRefId[2];
                voucher.storeGroupId = Convert.ToInt32(storeRefId[1]);
                voucher.storeGroupRefId = storeRefId[0];

                voucher.br_Name_store_group = storeRefId[3];
                voucher.br_ID_store_group = Convert.ToInt32(strBranchId);

                voucher.order_order_id = dr["orders"].ToString();
                voucher.order_event = "Packing";

                voucher.narration = "Packing of Sales Order : " + dr["orders"];
                voucher.reference = "Packing of Sales Order : " + dr["orders"];

                voucher.entry_RefId = StoreService.getSalesOrderRefId(orderId);//StoreService.GenerateRefId();

                double tenantCustomerWalletAmt = 0.00;
                double basePrice = (Math.Round(Convert.ToDouble(dr["selling_price"]), 2) - (Math.Round(Convert.ToDouble(dr["cgst"]), 2)
                    + Math.Round(Convert.ToDouble(dr["sgst"]), 2) + Math.Round(Convert.ToDouble(tenantCustomerWalletAmt), 2)));
                double tenantDeliveryCharge = Math.Round(Convert.ToDouble(dr["order_delivery_charge"]) + Convert.ToDouble(dr["order_courier_charge"]), 2);
                double tenantSales = Math.Round(basePrice - tenantDeliveryCharge, 2);
                double roundOff = (Math.Round(Convert.ToDouble(dr["selling_price"]), 2) -
                    (Math.Round(Convert.ToDouble(dr["cgst"]), 2) + Math.Round(Convert.ToDouble(dr["sgst"]), 2) +
                    tenantCustomerWalletAmt + tenantSales + tenantDeliveryCharge));
                
                voucher.Account = new List<TransactionData> {
                    new TransactionData() { isDebtor = 1, ledgerId = LedgerType.TenantSalesOrder, 
                        particulars="Tenant Sales Order",amount = Math.Round(Convert.ToDouble(dr["selling_price"]), 2) }
                    };

                voucher.Particulars = new List<TransactionData> {
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TenantSales, 
                        particulars = "Tenant Sales", amount = Math.Round(Convert.ToDouble(tenantSales),2) },
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TenantDelivery, 
                        particulars = "Tenant Delivery", amount = Math.Round(Convert.ToDouble(tenantDeliveryCharge),2) },
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TenantSalesRoundOff, 
                        particulars = "Tenant Sales order Round Off", amount = Math.Round(Convert.ToDouble(roundOff),2) },
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TenantCGST, 
                        particulars = "Tenant CGST", amount = Math.Round(Convert.ToDouble(dr["cgst"]),2) },
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TenantSGST, 
                        particulars = "Tenant SGST", amount = Math.Round(Convert.ToDouble(dr["sgst"]),2) },
                    new TransactionData() { isDebtor = 0, ledgerId = LedgerType.CustomerWallet,
                        particulars = "Customer Wallet", amount = Math.Round(Convert.ToDouble(tenantCustomerWalletAmt),2) }
                };
            }


            string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
            if (String.IsNullOrEmpty(url))
                url = "https://finascopdataentry.azurewebsites.net/api/";
            url += "FinascopDataEntry";

            string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
            if (String.IsNullOrEmpty(key))
                key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";

            //string content = (string)JObject.Parse(JsonConvert.SerializeObject(voucher)); //$"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";
            string content =JsonConvert.SerializeObject(voucher);

            var client = new RestClient(url);
            var request = new RestRequest();//api/FinascopDataEntry (Method.Post);
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("x-functions-key", key); //'"P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

            //request.AddBody("{" + content + "}", "application/json");
            request.AddBody(content , "application/json");
            var response = await client.ExecuteAsync<Result>(request);


            return response.Data;
        }
    
        private static string MarginDistribuions(string orderId, string strConnection, int isDebtor)
        {
            DataTable dtAllocations = DataServiceMySql.GetDataTable($"SELECT DISTINCT ocda.id," +
                    $"(SELECT costcentre_id FROM cost_distribution WHERE id = ocda.distribution_id) AS costCentreId, ledgerid AS ledgerId," +
                    $" allocation_amount AS amount, {isDebtor} AS isDebtor " +
                    $" FROM order_cost_distribution_allocations ocda INNER JOIN cost_distribution cd ON ocda.rule_id = cd.rule_id "+
                    $" INNER JOIN cost_distribution_rule cdr ON cd.rule_id = cdr.id " +
                    $" WHERE ocda.order_id =  {orderId}", strConnection);

            string JSONString = string.Empty;
            JSONString = JsonConvert.SerializeObject(dtAllocations);
            return JSONString;
        }
    }
}
