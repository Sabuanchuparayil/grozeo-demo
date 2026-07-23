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
using Finascop.BussinessModel;

namespace Finascop.Services.FinascopServices
{
    public static class OrderCancellation
    {
        public static async Task<Result> OrderCancellationVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            string fsto_id = orderId;
            int storeGroupId = storegroupid;
            TransactionEntry voucher = new TransactionEntry();

            List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
            logparams.Add(new KeyValuePair<string, object>("entityId", fsto_id));
            logparams.Add(new KeyValuePair<string, object>("type", "Order Cancellation"));
            logparams.Add(new KeyValuePair<string, object>("status", 1));
            logparams.Add(new KeyValuePair<string, object>("comments", "Order Cancellation"));
            string insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
            var result = DataServiceMySql.ExecuteScalar(insertQry, strConnection, logparams);
            int lastId = Convert.ToInt32(result);

            DataTable dtRefId = DataServiceMySql.GetDataTable($"SELECT storeRefId FROM finascop_branch_group " +
                $"WHERE store_group_id =  {storeGroupId}", strConnection);

            DataRow dt = dtRefId.Rows[0];
            string storeRefId = dt["storeRefId"].ToString();

            DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,total," +
               $"order_delivery_charge_gst,order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id, payment_mode," +
               $"order_tds, order_tcs, order_tcs_cgst, order_tcs_sgst, order_tcs_igst,order_total_sgst AS sgst, order_total_cgst AS cgst, order_delivery_charge_igst, " +
               $"order_total_igst, order_delivery_charge_cgst, order_delivery_charge_sgst,order_tdr,order_tdr_cgst,order_tdr_sgst,order_tdr_igst FROM retaline_customer_order rco " +
               $"INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id =  {fsto_id}", strConnection);

            if (dtOrderDetails != null && dtOrderDetails.Rows.Count > 0)
            {
                DataRow dr = dtOrderDetails.Rows[0];

                string strBranchId = dr["order_branch_id"].ToString();
                // Get Store ref id for finascop
                string[] storeRefeId = StoreService.GetStoreRefId(storeGroupId, strConnection, strBranchId);

                int custOrderId = Convert.ToInt32(dr["order_id"]);

                voucher.StoreGroupName = storeRefeId[2];
                voucher.storeGroupId = Convert.ToInt32(storeRefeId[1]);
                voucher.storeGroupRefId = storeRefeId[0];

                voucher.br_Name_store_group = storeRefeId[3];
                voucher.br_ID_store_group = Convert.ToInt32(strBranchId);

                voucher.entry_RefId = StoreService.getSalesOrderRefId(orderId);//StoreService.GenerateRefId();
                voucher.entry_type = 1;

                voucher.order_order_id = dr["orders"].ToString();
                voucher.order_event = "OrderCancellation";

                voucher.narration = "Cancellation of Order : " + dr["orders"];
                voucher.reference = "Cancellation of Order : " + dr["orders"];

                if (Convert.ToInt32(dr["payment_mode"]) == 2 || Convert.ToInt32(dr["payment_mode"]) == 3 || Convert.ToInt32(dr["payment_mode"]) == 5)
                {
                    voucher.TransactionTypeId = TransactionType.Journal;
                    voucher.docTypeID = TransactionType.Journal;
                    double TotalOrderAmount = Convert.ToDouble(dr["total"]);

                    voucher.Account = new List<TransactionData>();
                    if (TotalOrderAmount != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.TenantSalesOrder,
                            particulars = "Tenant Sales Order",
                            amount = TotalOrderAmount
                        });
                    }

                    voucher.Particulars = new List<TransactionData>();
                    if (TotalOrderAmount != 0)
                    {
                        voucher.Particulars.Add(new TransactionData()
                        {
                            isDebtor = 0,
                            ledgerId = LedgerType.CustomerWallet,
                            particulars = "Customer Wallet",
                            amount = TotalOrderAmount
                        });
                    }

                }
                else 
                {
                    List<KeyValuePair<string, object>> logparams2 = new List<KeyValuePair<string, object>>();
                    logparams2.Add(new KeyValuePair<string, object>("comments", "Cancellation of Order : " + orderId + ". Payment method does not allow refund."));
                    logparams2.Add(new KeyValuePair<string, object>("status", ("2")));
                    logparams2.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams2);
                    return new Result { statusId = ResultType.Error, message = "Payment method does not allow refund." };

                }
                string content = JsonConvert.SerializeObject(voucher);
                try
                {
                    string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                    if (String.IsNullOrEmpty(url))
                        url = "https://finascopdataentry.azurewebsites.net/api/";
                    url += "FinascopDataEntry";

                    string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                    if (String.IsNullOrEmpty(key))
                        key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";

                    var client = new RestClient(url);
                    var request = new RestRequest();
                    request.Method = Method.Post;
                    request.AddHeader("content-type", "application/json");
                    request.AddHeader("x-functions-key", key);

                    request.AddBody(content, "application/json");
                    var response = await client.ExecuteAsync<Result>(request);
                    response.Content = JsonConvert.SerializeObject(voucher);

                    List<KeyValuePair<string, object>> logparams2 = new List<KeyValuePair<string, object>>();
                    logparams2.Add(new KeyValuePair<string, object>("comments", "Cancellation of Order : " + dr["orders"] + ", Result:" + response + ", content:" + content));
                    logparams2.Add(new KeyValuePair<string, object>("status", (response.IsSuccessful == true ? "1" : "2")));
                    logparams2.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams2);

                    return new Result { statusId = ResultType.Success, message = "Successfull refund of order amount to customer." };
                    //return response.Data;
                }
                catch (Exception ex)
                {

                    List<KeyValuePair<string, object>> logparams3 = new List<KeyValuePair<string, object>>();
                    logparams3.Add(new KeyValuePair<string, object>("comments", "Cancellation of Order : " + dr["orders"] + ", Exception :" + ex.Message + ", content:" + content));
                    logparams3.Add(new KeyValuePair<string, object>("status", 3));
                    logparams3.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams3);
                    return new Result { statusId = ResultType.Error, message = "Invalid order OR Exception :" + ex.Message };
                }



            }
            return new Result { statusId = ResultType.Error, message = "Invalid order"};

        }
    }
}
