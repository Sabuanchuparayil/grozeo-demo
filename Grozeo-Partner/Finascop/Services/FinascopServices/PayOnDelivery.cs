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
    public static class PayOnDelivery
    {
        public static async Task<Result> PODVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            string fsto_id = orderId;
            int storeGroupId = storegroupid;
            TransactionEntry voucher = new TransactionEntry();

            List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
            logparams.Add(new KeyValuePair<string, object>("entityId", fsto_id));
            logparams.Add(new KeyValuePair<string, object>("type", "PayOnDeliveryVoucher"));
            logparams.Add(new KeyValuePair<string, object>("status", 0));
            logparams.Add(new KeyValuePair<string, object>("comments", "DeliveryVoucher"));
            string insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
            var result = DataServiceMySql.ExecuteScalar(insertQry, strConnection, logparams);
            int lastId = Convert.ToInt32(result);

            DataTable dtRefId = DataServiceMySql.GetDataTable($"SELECT storeRefId FROM finascop_branch_group " +
                $"WHERE store_group_id =  {storeGroupId}", strConnection);

            DataRow dt = dtRefId.Rows[0];
            string storeRefId = dt["storeRefId"].ToString();

            DataTable dtOrderDetails = DataServiceMySql.GetDataTable($"SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge," +
               $"order_delivery_charge_gst,order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id," +
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

                voucher.entry_RefId = StoreService.getSalesOrderRefId(custOrderId.ToString());//StoreService.GenerateRefId();
                voucher.entry_type = 1;

                voucher.order_order_id = dr["orders"].ToString();
                voucher.order_event = "PayOnDelivery";

                voucher.narration = "Pay On Delivery Voucher : " + dr["orders"] + " On Confirmation.";
                voucher.reference = "Pay On Delivery Voucher : " + dr["orders"];

                if (Convert.ToInt32(dr["payment_mode"]) == 4 || Convert.ToInt32(dr["payment_mode"]) == 6)
                {
                    voucher.TransactionTypeId = TransactionType.BankReceipt;
                    voucher.docTypeID = TransactionType.BankReceipt;
                    double PaidByWalletAmount = Convert.ToDouble(dr["order_wallet_amount"]);

                    DataTable tdrValues = DataServiceMySql.GetDataTable($"SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR'", strConnection);
                    DataTable tdrCGSTValues = DataServiceMySql.GetDataTable($"SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR_CGST'", strConnection);

                    DataRow tdr = tdrValues.Rows[0];
                    DataRow tdrCGST = tdrCGSTValues.Rows[0];

                    double bankCharges = Convert.ToDouble(dr["total"]) * Convert.ToDouble(tdr["cfg_Value"]) / 100;
                    bankCharges = Math.Round(bankCharges, 2);
                    double tdrCGSTval = bankCharges * Convert.ToDouble(tdrCGST["cfg_Value"]) / 100;
                    tdrCGSTval = Math.Round(tdrCGSTval, 2);
                    double bankChargesIGST, bankChargesSGST, bankChargesCGST;
                    if (Convert.ToDouble(dr["order_total_igst"]) > 0)
                    {
                        bankChargesIGST = tdrCGSTval * 2;
                        bankChargesSGST = Convert.ToDouble(0.00);
                        bankChargesCGST = Convert.ToDouble(0.00);
                    }
                    else
                    {
                        bankChargesIGST = Convert.ToDouble(0.00);
                        bankChargesSGST = tdrCGSTval;
                        bankChargesCGST = tdrCGSTval;
                    }
                    double total = Convert.ToDouble(dr["total"]);
                    double totBalToPay = Math.Round(total - PaidByWalletAmount, 2);
                    double amtToBank = totBalToPay - (bankCharges + bankChargesIGST + bankChargesSGST + bankChargesCGST);
                    amtToBank = Math.Round(amtToBank, 2);

                    voucher.Account = new List<TransactionData>();
                    if (amtToBank != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.BANKGOCA,
                            particulars = "Bank GOCA",
                            amount = amtToBank
                        });
                    }
                    if (bankCharges != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.BankChargesTDR,
                            particulars = "Bank Charges TDR",
                            amount = bankCharges
                        });
                    }
                    if (bankChargesCGST != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.CGSTInput,
                            particulars = "CGSTInput",
                            amount = bankChargesCGST
                        });
                    }
                    if (bankChargesSGST != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.SGSTInput,
                            particulars = "SGSTInput",
                            amount = bankChargesSGST
                        });
                    }
                    if (bankChargesIGST != 0)
                    {
                        voucher.Account.Add(new TransactionData()
                        {
                            isDebtor = 1,
                            ledgerId = LedgerType.IGSTInput,
                            particulars = "IGSTInput",
                            amount = bankChargesIGST
                        });
                    }
                    voucher.Particulars = new List<TransactionData>();
                    if (totBalToPay != 0)
                    {
                        voucher.Particulars.Add(new TransactionData()
                        {
                            isDebtor = 0,
                            ledgerId = LedgerType.PODCollectionOther,
                            particulars = "POD Collection",
                            amount = totBalToPay
                        });
                    }

                }
                else if (Convert.ToInt32(dr["payment_mode"]) == 7 || Convert.ToInt32(dr["payment_mode"]) == 1)
                {
                    voucher.TransactionTypeId = TransactionType.Journal;
                    voucher.docTypeID = TransactionType.Journal;

                    double PaidByWalletAmount = Convert.ToDouble(dr["order_wallet_amount"]);
                    double total = Convert.ToDouble(dr["total"]);
                    double totBalToPay = Math.Round(total - PaidByWalletAmount, 2);
                    double courierOrGLP = Math.Round(totBalToPay, 2);

                    voucher.Account = new List<TransactionData>
                            {
                                new TransactionData() {
                                    isDebtor = 1, ledgerId = LedgerType.CourierGLP, particulars = "Courier/GLP",amount = courierOrGLP },

                            };
                    voucher.Particulars = new List<TransactionData>();
                    voucher.Particulars.Add(
                                new TransactionData()
                                {
                                    isDebtor = 0,
                                    ledgerId = LedgerType.PODCollectionOther,
                                    particulars = "POD Collection",
                                    amount = totBalToPay
                                }
                            );

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
                    var response = await client.ExecuteAsync<Result>(request).ConfigureAwait(false);

                    List<KeyValuePair<string, object>> logparams2 = new List<KeyValuePair<string, object>>();
                    logparams2.Add(new KeyValuePair<string, object>("comments", "Pay On Delivery: " + dr["orders"] + ",Result:" + JsonConvert.SerializeObject(response) + ",content:" + content));
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
                    logparams3.Add(new KeyValuePair<string, object>("comments", "Pay On Delivery: " + dr["orders"] + ",Exception :" + ex.Message + ",content:" + content));
                    logparams3.Add(new KeyValuePair<string, object>("status", 3));
                    logparams3.Add(new KeyValuePair<string, object>("lastId", lastId));

                    string updateQry = "UPDATE finascop_log SET comments=@comments,status=@status WHERE id=@lastId";
                    DataServiceMySql.ExecuteSql(updateQry, strConnection, logparams3);
                }



            }
            return new Result { statusId = ResultType.Error, message = "Invalid order" };

        }

        public static async Task<Result> PODCashCollectionVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            return new Result { statusId = ResultType.Error, message = "Invalid order" };
        }
        public static async Task<Result> PODCashSettlementVoucher(string orderId, string strConnection, int storegroupid = -1)
        {
            return new Result { statusId = ResultType.Error, message = "Invalid order" };
        }
    }
}
