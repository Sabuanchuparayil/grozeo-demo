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
    public static class DeliveryService
    {
        //public static void DeliveryVoucher(string orderId, string strConnection)
        public static async Task<Result> DeliveryVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            string fsto_id = orderId;
            int storeGroupId = storegroupid;
            TransactionEntry voucher = new TransactionEntry();

            List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
            logparams.Add(new KeyValuePair<string, object>("entityId", fsto_id));
            logparams.Add(new KeyValuePair<string, object>("type", "DeliveryVoucher"));
            logparams.Add(new KeyValuePair<string, object>("status", 0));
            logparams.Add(new KeyValuePair<string, object>("comments", "DeliveryVoucher"));
            string insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
            var result = DataServiceMySql.ExecuteScalar(insertQry, strConnection, logparams);
            int lastId = Convert.ToInt32(result);

            //DataTable tcsValTbl = DataServiceMySql.GetDataTable($"SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS'", strConnection);

            //DataTable tdsValTbl = DataServiceMySql.GetDataTable($"SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDS'", strConnection);

            DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge," +
                $"order_delivery_charge_gst,order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id," +
                $"order_tds, order_tcs, order_tcs_cgst, order_tcs_sgst, order_tcs_igst,order_total_sgst AS sgst, order_total_cgst AS cgst, order_delivery_charge_igst, " +
                $"order_total_igst, order_delivery_charge_cgst, order_delivery_charge_sgst,order_tcs_utgst,order_total_utgst,order_delivery_charge_utgst FROM retaline_customer_order rco " +
                $"INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id =  {fsto_id}", strConnection);

            if(dtOrderDetails == null || dtOrderDetails.Rows.Count <= 0)
            {
                List<KeyValuePair<string, object>> logparams3 = new List<KeyValuePair<string, object>>();
                logparams3.Add(new KeyValuePair<string, object>("comments", "Delivery Voucher of SalesOrder: " + fsto_id + ",Exception : No order found in database against the fsto_id"));
                logparams3.Add(new KeyValuePair<string, object>("status", 3));
                logparams3.Add(new KeyValuePair<string, object>("lastId", lastId));
                string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams3);
                return new Result { statusId = ResultType.Error, message = "Invalid order" };
            }

            // Order details
            DataRow dr = dtOrderDetails.Rows[0];
            string strBranchId = dr["order_branch_id"].ToString();
            // Get Store ref id for finascop
            string[] storeRefId = StoreService.GetStoreRefId(storeGroupId, strConnection, strBranchId);

            if (dtOrderDetails != null && dtOrderDetails.Rows.Count > 0)
            {
                int custOrderId = Convert.ToInt32(dr["order_id"]);
                double amount_before_tax = Math.Round(Convert.ToDouble(dr["amount_before_tax"]), 2);
                double tenantDeliveryCharge = Math.Round(Convert.ToDouble(dr["order_delivery_charge"]) + Convert.ToDouble(dr["order_courier_charge"]) - Convert.ToDouble(dr["order_delivery_charge_gst"]), 2);

                double tenantIGST = Math.Round(Convert.ToDouble(dr["order_delivery_charge_igst"]) + Convert.ToDouble(dr["order_total_igst"]), 2);
                double tenantCGST = Math.Round(Convert.ToDouble(dr["cgst"]) + Convert.ToDouble(dr["order_delivery_charge_cgst"]), 2);
                double tenantSGST = Math.Round(Convert.ToDouble(dr["sgst"]) + Convert.ToDouble(dr["order_delivery_charge_sgst"]), 2);
                double tenantUTGST = Math.Round(Convert.ToDouble(dr["order_total_utgst"]) + Convert.ToDouble(dr["order_delivery_charge_utgst"]), 2);

                double tcs_igst = Math.Round(Convert.ToDouble(dr["order_tcs_igst"]),2);
                double tcs_cgst = Math.Round(Convert.ToDouble(dr["order_tcs_cgst"]),2);
                double tcs_sgst = Math.Round(Convert.ToDouble(dr["order_tcs_sgst"]),2);
                double tcs_utgst = Math.Round(Convert.ToDouble(dr["order_tcs_utgst"]), 2);

                double tdsGst = Math.Round(Convert.ToDouble(dr["order_tds"]),2);

                double roundOff = Math.Round(Convert.ToDouble(dr["order_roundoff"]), 2);
                voucher.StoreGroupName = storeRefId[2];
                voucher.storeGroupId = Convert.ToInt32(storeRefId[1]);
                voucher.storeGroupRefId = storeRefId[0];

                voucher.br_Name_store_group = storeRefId[3];
                voucher.br_ID_store_group = Convert.ToInt32(strBranchId);
                voucher.entry_type = 1;
                voucher.TransactionTypeId = TransactionType.Journal;
                voucher.docTypeID = TransactionType.Journal;
                voucher.narration = "Delivery of Sales Order : " + dr["orders"];
                voucher.reference = "Delivery of Sales Order : " + dr["orders"];
                voucher.entry_RefId = StoreService.getSalesOrderRefId(dr["order_id"].ToString());//StoreService.GenerateRefId();

                voucher.order_order_id = dr["orders"].ToString();
                voucher.order_event = "Delivery";

                voucher.Account = new List<TransactionData>
                            {
                                new TransactionData() { isDebtor = 1, ledgerId = LedgerType.TenantSales, particulars = "Tenant Sales",
                                    amount = amount_before_tax },
                                new TransactionData() { isDebtor = 1, ledgerId = LedgerType.TenantDelivery, particulars = "Tenant Delivery",
                                    amount = tenantDeliveryCharge },


                            };

                if (tenantIGST != 0)
                {
                    voucher.Account.Add(new TransactionData()
                    {
                        isDebtor = 1,
                        ledgerId = LedgerType.TenantIGST,
                        particulars = "Tenant IGST",
                        amount = tenantIGST
                    });
                }

                if (tenantCGST != 0)
                {
                    voucher.Account.Add(new TransactionData()
                    {
                        isDebtor = 1,
                        ledgerId = LedgerType.TenantCGST,
                        particulars = "Tenant CGST",
                        amount = tenantCGST
                    });
                }
                if (tenantSGST != 0)
                {
                    voucher.Account.Add(new TransactionData()
                    {
                        isDebtor = 1,
                        ledgerId = LedgerType.TenantSGST,
                        particulars = "Tenant SGST",
                        amount = tenantSGST
                    });
                }

                if (tenantUTGST != 0)
                {
                    voucher.Account.Add(new TransactionData()
                    {
                        isDebtor = 1,
                        ledgerId = LedgerType.TenantUTGST,
                        particulars = "Tenant UTGST",
                        amount = tenantUTGST
                    });
                }
                voucher.Particulars = new List<TransactionData>
                            {
                                new TransactionData() { isDebtor = 0, ledgerId = LedgerType.TDSonE_commercetransactionsAY22_23,
                                particulars = "TDS on E-commerce transactions", amount = tdsGst }
                            };

                if (tcs_igst != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.TCSIGST,
                        particulars = "TCS IGST",
                        amount = tcs_igst
                    });
                }

                if (tcs_cgst != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.TCSCGST,
                        particulars = "TCS CGST",
                        amount = tcs_cgst
                    });
                }

                if (tcs_sgst != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.TCSSGST,
                        particulars = "TCS SGST",
                        amount = tcs_sgst
                    });
                }
                if (tcs_utgst != 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.TCSUTGST,
                        particulars = "TCS UTGST",
                        amount = tcs_utgst
                    });
                }

                double accountSum = Math.Round(amount_before_tax + tenantDeliveryCharge + tenantIGST + tenantCGST + tenantSGST + tenantUTGST, 2);
                double tenant = (accountSum + roundOff) - (tcs_utgst + tcs_cgst + tcs_sgst + tcs_igst + tdsGst);
                tenant = Math.Round(tenant, 2);

                voucher.Particulars.Add(new TransactionData()
                {
                    isDebtor = 0,
                    ledgerId = LedgerType.RefId,
                    ledgerRefId = storeRefId[0],
                    particulars = storeRefId[2],
                    amount = tenant
                });

                //voucher.Account[0].amount = Math.Round(amount_before_tax + roundOff, 2);

                if (roundOff > 0)
                {
                    voucher.Account.Add(new TransactionData()
                    {
                        isDebtor = 1,
                        ledgerId = LedgerType.TenantSalesOrderRoundOff,
                        particulars = "Tenant Sales Round Off",
                        amount = Math.Abs(roundOff)
                    });
                }

                if (roundOff < 0)
                {
                    voucher.Particulars.Add(new TransactionData()
                    {
                        isDebtor = 0,
                        ledgerId = LedgerType.TenantSalesOrderRoundOff,
                        particulars = "Tenant Sales Round Off",
                        amount = Math.Abs(roundOff)
                    });
                }
                string content = JsonConvert.SerializeObject(voucher);

///////////////////////////////////////////////////////////////////////////////////////////////////////
                List<KeyValuePair<string, object>> fl_logparams = new List<KeyValuePair<string, object>>();
                fl_logparams.Add(new KeyValuePair<string, object>("entityId", fsto_id));
                fl_logparams.Add(new KeyValuePair<string, object>("type", "Order Account Statement"));
                fl_logparams.Add(new KeyValuePair<string, object>("status",0));
                fl_logparams.Add(new KeyValuePair<string, object>("comments", "Order Account Statement of Sales Order: " + dr["orders"] + " TCS and TDS entries."));
                string fl_insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                    $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
                var fl_result = DataServiceMySql.ExecuteScalar(fl_insertQry, strConnection, fl_logparams);
                int fl_lastId = Convert.ToInt32(fl_result);
                try
                {
                    var cBQuery = $"SELECT closingBalance FROM order_account_statement WHERE storeId = {Convert.ToInt32(strBranchId)} ORDER BY id DESC LIMIT 0,1";
                    List<KeyValuePair<string, object>> cb_logparams = new List<KeyValuePair<string, object>>();
                    cb_logparams.Add(new KeyValuePair<string, object>("orderBranchsg", Convert.ToInt32(storeGroupId)));
                    cb_logparams.Add(new KeyValuePair<string, object>("orderBranchId", Convert.ToInt32(strBranchId)));
                    var cb_result = DataServiceMySql.ExecuteScalar(cBQuery, strConnection, cb_logparams);

                    double closingBalance = Convert.ToDouble(cb_result);

                    double tcs = tcs_utgst + tcs_cgst + tcs_sgst + tcs_igst;
                    List<KeyValuePair<string, object>> OASEntry = new List<KeyValuePair<string, object>>();
                    OASEntry.Add(new KeyValuePair<string, object>("orderId", fsto_id));
                    OASEntry.Add(new KeyValuePair<string, object>("orderOrderId", dr["orders"]));
                    OASEntry.Add(new KeyValuePair<string, object>("storeGroupId", Convert.ToInt32(storeGroupId)));
                    OASEntry.Add(new KeyValuePair<string, object>("storeId", Convert.ToInt32(strBranchId)));
                    OASEntry.Add(new KeyValuePair<string, object>("particulars", "TCS"));
                    OASEntry.Add(new KeyValuePair<string, object>("isDebtor", 0));
                    OASEntry.Add(new KeyValuePair<string, object>("amount", tcs));
                    OASEntry.Add(new KeyValuePair<string, object>("openingBalance", Math.Round(closingBalance,2)));
                    string OASinsertQry = $"INSERT INTO order_account_statement(orderId,orderOrderId,storeGroupId,storeId,particulars,isDebtor,amount,openingBalance) " +
                                        $"VALUES(@orderId,@orderOrderId,@storeGroupId,@storeId,@particulars,@isDebtor,@amount,@openingBalance)";
                    var OASresult = DataServiceMySql.ExecuteSql(OASinsertQry, strConnection, OASEntry);
                    
                    OASEntry = new List<KeyValuePair<string, object>>();
                    OASEntry.Add(new KeyValuePair<string, object>("orderId", fsto_id));
                    OASEntry.Add(new KeyValuePair<string, object>("orderOrderId", dr["orders"]));
                    OASEntry.Add(new KeyValuePair<string, object>("storeGroupId", Convert.ToInt32(storeGroupId)));
                    OASEntry.Add(new KeyValuePair<string, object>("storeId", Convert.ToInt32(strBranchId)));
                    OASEntry.Add(new KeyValuePair<string, object>("particulars", "TDS"));
                    OASEntry.Add(new KeyValuePair<string, object>("isDebtor", 0));
                    OASEntry.Add(new KeyValuePair<string, object>("amount", tdsGst));
                    OASEntry.Add(new KeyValuePair<string, object>("openingBalance", Math.Round(closingBalance - tcs, 2)));
                    OASinsertQry = $"INSERT INTO order_account_statement(orderId,orderOrderId,storeGroupId,storeId,particulars,isDebtor,amount,openingBalance) " +
                                        $"VALUES(@orderId,@orderOrderId,@storeGroupId,@storeId,@particulars,@isDebtor,@amount,@openingBalance)";
                    OASresult = DataServiceMySql.ExecuteSql(OASinsertQry, strConnection, OASEntry);

                    List<KeyValuePair<string, object>> fl_logparams2 = new List<KeyValuePair<string, object>>();
                    fl_logparams2.Add(new KeyValuePair<string, object>("comments", "Order Account Statement of SalesOrder: " + dr["orders"] + " TCS and TDS entries Successful."));
                    fl_logparams2.Add(new KeyValuePair<string, object>("status", 1));
                    fl_logparams2.Add(new KeyValuePair<string, object>("lastId", fl_lastId));
                    string fl_updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(fl_updateQry, strConnection, fl_logparams2);
                }
                catch (Exception OAEx)
                {
                    List<KeyValuePair<string, object>> fl_logparams3 = new List<KeyValuePair<string, object>>();
                    fl_logparams3.Add(new KeyValuePair<string, object>("comments", "Order Account Statement of SalesOrder: " + dr["orders"] + " TCS and TDS entries, Exception : " + OAEx));
                    fl_logparams3.Add(new KeyValuePair<string, object>("status", 3));
                    fl_logparams3.Add(new KeyValuePair<string, object>("lastId", fl_lastId));
                    string fl_updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(fl_updateQry, strConnection, fl_logparams3);
                }
///////////////////////////////////////////////////////////////////////////////////////////////////////////
                try
                {
                    string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                    if (String.IsNullOrEmpty(url))
                        url = "https://finascopdataentry.azurewebsites.net/api/";
                    url += "FinascopDataEntry";

                    string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                    if (String.IsNullOrEmpty(key))
                        key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";

                    //string content = (string)JObject.Parse(JsonConvert.SerializeObject(voucher)); //$"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";
                    

                    var client = new RestClient(url);
                    var request = new RestRequest();//api/FinascopDataEntry (Method.Post);
                    request.Method = Method.Post;
                    request.AddHeader("content-type", "application/json");
                    request.AddHeader("x-functions-key", key); //'"P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

                    //request.AddBody("{" + content + "}", "application/json");
                    request.AddBody(content, "application/json");
                    var response = await client.ExecuteAsync<Result>(request).ConfigureAwait(false);
                    //response.Content = JsonConvert.SerializeObject(voucher);

                    List<KeyValuePair<string, object>> logparams2 = new List<KeyValuePair<string, object>>();
                    logparams2.Add(new KeyValuePair<string, object>("comments", "Delivery Voucher: " + dr["orders"] + ",Result:" + JsonConvert.SerializeObject(response) + ",content:" + content));
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
                    logparams3.Add(new KeyValuePair<string, object>("comments", "Delivery Voucher of SalesOrder: " + dr["orders"] + ",Exception :" + ex.Message + ",content:" + content));
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
