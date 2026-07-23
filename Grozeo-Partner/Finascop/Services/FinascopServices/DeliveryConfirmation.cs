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

namespace Finascop.Services
{
    public static class DeliveryConfirmation
    {
        public static async Task<Result> DeliveryConfirmationVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            string fsto_id = orderId;
            int storeGroupId = storegroupid;
            TransactionEntry voucher = new TransactionEntry();

            List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
            logparams.Add(new KeyValuePair<string, object>("entityId", fsto_id));
            logparams.Add(new KeyValuePair<string, object>("type", "DeliveryConfirmationVoucher"));
            logparams.Add(new KeyValuePair<string, object>("status", 0));
            logparams.Add(new KeyValuePair<string, object>("comments", "DeliveryConfirmationVoucher"));
            string insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
            var result = DataServiceMySql.ExecuteScalar(insertQry, strConnection, logparams);
            int lastId = Convert.ToInt32(result);

            DataTable dtRefId = DataServiceMySql.GetDataTable($"SELECT storeRefId FROM finascop_branch_group " +
                $"WHERE store_group_id =  {storeGroupId}", strConnection);

            DataRow dt = dtRefId.Rows[0];
            string storeRefId = dt["storeRefId"].ToString();

            DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id,fstr_id,order_delivery_charge,storegroup_id,order_courier_charge," +
               $"order_delivery_charge_gst,order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id," +
               $"order_tds, order_tcs, order_tcs_cgst, order_tcs_sgst, order_tcs_igst,order_total_sgst AS sgst, order_total_cgst AS cgst, order_delivery_charge_igst, " +
               $"order_total_igst, order_delivery_charge_cgst, order_delivery_charge_sgst,order_tdr,order_tdr_cgst,order_tdr_sgst,order_tdr_igst FROM retaline_customer_order rco " +
               $"INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id =  {fsto_id}", strConnection);

            // Order details
            DataRow dr = dtOrderDetails.Rows[0];
            DataTable tenetexpenseDetails = DataServiceMySql.GetDataTable($"SELECT delivery_income,diIGST,diCGST,diSGST,diUTGST,pgIGST,pgCGST,pgSGST,pgUTGST,tenantExpense " +
                $"FROM tenant_income_expense WHERE orderId = {dr["fstr_id"]}", strConnection);
            DataRow ted = tenetexpenseDetails.Rows[0];

            string strBranchId = dr["order_branch_id"].ToString();

            // Get Store ref id for finascop
            string[] storeRefeId = StoreService.GetStoreRefId(storeGroupId, strConnection, strBranchId);

            if (dtOrderDetails != null && dtOrderDetails.Rows.Count > 0)
            {

                double tenantDeliveryCharge = Convert.ToDouble(ted["delivery_income"]);
                double tdrOnlinePayment = Convert.ToDouble(dr["order_tdr"]);
                double tdrIGST = 0; double tdrCGST = 0; double tdrSGST = 0;


                tdrIGST = Convert.ToDouble(ted["pgIGST"]);
                tdrCGST = Convert.ToDouble(ted["pgCGST"]);
                tdrSGST = Convert.ToDouble(ted["pgSGST"]);

                double tenant = Convert.ToDouble(ted["tenantExpense"]);

                voucher.StoreGroupName = storeRefeId[2];
                voucher.storeGroupId = Convert.ToInt32(storeRefeId[1]);
                voucher.storeGroupRefId = storeRefeId[0];

                voucher.br_Name_store_group = storeRefeId[3];
                voucher.br_ID_store_group = Convert.ToInt32(strBranchId);
                voucher.entry_type = 1;
                voucher.TransactionTypeId = TransactionType.Journal;
                voucher.docTypeID = TransactionType.Journal;
                voucher.narration = "Delivery of Sales Order : " + dr["orders"] + " On Confirmation.";
                voucher.reference = "Delivery Confirmation : " + dr["orders"];
                voucher.entry_RefId = StoreService.getSalesOrderRefId(dr["order_id"].ToString());//StoreService.GenerateRefId();

                voucher.order_order_id = dr["orders"].ToString();
                voucher.order_event = "DeliveryConfirmation";

                voucher.Account = new List<TransactionData>
                            {
                                new TransactionData() { isDebtor = 1, ledgerId = LedgerType.RefId, particulars = storeRefeId[2],ledgerRefId = storeRefeId[0],
                                    amount = Math.Round(tenant,2) },

                            };
                voucher.Particulars = new List<TransactionData>();

                if (tdrOnlinePayment > 0)
                    voucher.Particulars.Add(
                                new TransactionData()
                                {
                                    isDebtor = 0,
                                    ledgerId = LedgerType.TDROnlineTransactions,
                                    particulars = "TDR Online Transactions",
                                    amount = Math.Round(tdrOnlinePayment, 2)
                                }
                            );

                if (Math.Round(tenantDeliveryCharge, 2) != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.DeliveryChargesIncome,
                        particulars = "Delivery Income",
                        amount = tenantDeliveryCharge
                    });
                }

                if (tdrIGST != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.IGST,
                        particulars = "IGST",
                        amount = tdrIGST
                    });
                }

                if (tdrSGST != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.SGST,
                        particulars = "SGST",
                        amount = tdrSGST
                    });
                }

                if (tdrCGST != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.CGST,
                        particulars = "CGST",
                        amount = tdrCGST
                    });
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

                    //request.AddBody("{" + content + "}", "application/json");
                    request.AddBody(content, "application/json");
                    var response = await client.ExecuteAsync<Result>(request);

                    List<KeyValuePair<string, object>> logparams2 = new List<KeyValuePair<string, object>>();
                    logparams2.Add(new KeyValuePair<string, object>("comments", "Delivery Confirmation Voucher: " + dr["orders"] + ",Result:" + JsonConvert.SerializeObject(response) + ",content:" + content));
                    logparams2.Add(new KeyValuePair<string, object>("status", (response.IsSuccessful == true ? "1" : "2")));
                    logparams2.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams2);

                    return response.Data;
                }
                catch (Exception ex)
                {
                    //ex.Message

                    List<KeyValuePair<string, object>> logparams3 = new List<KeyValuePair<string, object>>();
                    logparams3.Add(new KeyValuePair<string, object>("comments", "Delivery Confirmation Voucher: " + dr["orders"] + ",Exception :" + ex.Message + ",content:" + content));
                    logparams3.Add(new KeyValuePair<string, object>("status", 3));
                    logparams3.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams3);
                }
            }

            return new Result { statusId = ResultType.Error, message = "Invalid order" };

        }
    }
}
