using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;
using System.Text.RegularExpressions;
using Microsoft.Extensions.Logging;
using Azure;
using Newtonsoft.Json;

namespace DataEntry
{
    public static class DataService
    {
        private static string ConnectionString
        {
            get
            {
                return Environment.GetEnvironmentVariable("dbconnection");
            }
        }

        public static DataTable GetDataTable(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP = false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            DataTable dt = new DataTable();

            SqlConnection con = new SqlConnection(sqlconnection);
            SqlCommand cmd = new SqlCommand(sql, con);
            if (isSP)
                cmd.CommandType = CommandType.StoredProcedure;

            con.Open();
            try
            {
                if (parmeters != null)
                    FillParams(parmeters, cmd.Parameters);

                dt.Load(cmd.ExecuteReader());
            }
            catch (Exception ex)
            {
                throw ex;
            }
            finally
            {
                con.Close();
            }

            return dt;
        }

        public static int ExecuteSql(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP = false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            int count = -1;
            SqlConnection con = new SqlConnection(sqlconnection);
            SqlCommand cmd = new SqlCommand(sql, con);
            if (isSP)
                cmd.CommandType = CommandType.StoredProcedure;

            con.Open();
            try
            {
                if (parmeters != null)
                    FillParams(parmeters, cmd.Parameters);
                count = cmd.ExecuteNonQuery();
            }
            catch (Exception ex)
            {
                throw ex;
            }
            finally
            {
                con.Close();
            }
            return count;
        }

        public static int ExecuteSP(string sp, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            int count = -1;
            SqlConnection con = new SqlConnection(sqlconnection);
            SqlCommand cmd = new SqlCommand(sp, con);
            cmd.CommandType = CommandType.StoredProcedure;
            con.Open();
            try
            {
                if (parmeters != null)
                    FillParams(parmeters, cmd.Parameters);
                count = cmd.ExecuteNonQuery();
            }
            catch (Exception ex)
            {
                throw ex;
            }
            finally
            {
                con.Close();
            }
            return count;
        }

        public static object ExecuteScalar(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP = false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            object result = null;
            SqlConnection con = new SqlConnection(sqlconnection);
            SqlCommand cmd = new SqlCommand(sql, con);
            if (isSP)
                cmd.CommandType = CommandType.StoredProcedure;

            con.Open();
            try
            {
                if (parmeters != null)
                    FillParams(parmeters, cmd.Parameters);
                result = cmd.ExecuteScalar();
            }
            catch (Exception ex)
            {
                throw ex;
            }
            finally
            {
                con.Close();
            }
            return result;
        }

        public static void FillParams(List<KeyValuePair<String, Object>> parmeters, SqlParameterCollection prms)
        {
            if (parmeters != null)
            {
                foreach (KeyValuePair<String, Object> strparams in parmeters)
                {
                    SqlParameter param = new SqlParameter();
                    param.ParameterName = String.Format("@{0}", strparams.Key);
                    param.Value = strparams.Value;
                    prms.Add(param);
                }
            }
        }


        public static async void BulkInsertData(DataTable dt, string tableName, string sqlconnection = "")
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            try
            {
                using (var connection = new SqlConnection(sqlconnection))
                {
                    await connection.OpenAsync();
                    SqlBulkCopy bulkCopy = new SqlBulkCopy(
        connection,
        //SqlBulkCopyOptions.TableLock | 
        SqlBulkCopyOptions.CheckConstraints |
        //SqlBulkCopyOptions.FireTriggers |
        SqlBulkCopyOptions.UseInternalTransaction,
        null
        );
                    // set the destination table name
                    bulkCopy.DestinationTableName = tableName;
                    //connection.Open();

                    // write the data in the "dataTable"
                    bulkCopy.WriteToServer(dt);
                    connection.Close();
                }
            }
            catch (Exception ex)
            {
            }


        }

        /// <summary>
        /// To get latest serial number for the voucher
        /// </summary>
        /// <param name="docType"></param>
        /// <param name="connection"></param>
        /// <returns></returns>
        public static string GenerateVoucherSerial(int docType)
        {
            SqlConnection connection = new SqlConnection(ConnectionString);
            SqlCommand cmd;
            string DocSerialNumber;
            SqlDataAdapter da2 = new SqlDataAdapter();
            if (connection.State == ConnectionState.Closed)
            {
                connection.Open();
            }
            cmd = new SqlCommand("SELECT dbo.[GenerateVoucherSerial] (@typeId)", connection);
            cmd.Parameters.AddWithValue("@typeId", docType);
            DocSerialNumber = cmd.ExecuteScalar().ToString();

            return DocSerialNumber;
        }


        /// <summary>
        /// Finascop cost centre entries.
        /// </summary>
        /// <param "CostCentreLogData data"></param>
        /// <returns>Result</returns>
        public static Result AddCostCentreEntries(CostCentreLogData data,ILogger log)
        {
            CostCentreLogData costCentreLogData = new CostCentreLogData();
            try
            {
                bool costCentreEntryFailed = false;
                SqlConnection con = new SqlConnection(ConnectionString);
                SqlCommand cmd = con.CreateCommand();
                con.Open();
                SqlTransaction transaction = con.BeginTransaction("ADD_COST_CENTRE_ENTRIES");
                cmd.Transaction = transaction;

                try
                {
                    List<CostCentreEntry> CostCentreEntries = data.CostCentre[0].CostCentreEntries;
                     
                    if (CostCentreEntries != null && CostCentreEntries.Any())
                    {
                        
                        double sumTotalAmount = 0.00;
                       
                        costCentreLogData.order_event = data.order_event;
                        costCentreLogData.order_order_id = data.order_order_id;
                        costCentreLogData.costCentreRule = CostCentreEntries[0].costCentreRule;
                        costCentreLogData.CostCentre = data.CostCentre;
                        string ledger = DataService.GetLedgerNameFromID(data.CostCentre[0].ledgerId);
                        foreach (var costCentreEntry in CostCentreEntries)
                        {
                            if (costCentreEntry.costCentreId == -1 && !String.IsNullOrEmpty(costCentreEntry.costCentreRefId))
                            {
                                costCentreEntry.costCentreId = DataService.GetCostCentreId(costCentreEntry.costCentreRefId);
                            }
                            if (costCentreEntry.costCentreId > 0)
                            {
                                costCentreEntry.costCentreName = DataService.GetCostCentreName(costCentreEntry.costCentreId)?.Trim();
                                if (costCentreEntry.costCentreName?.Trim() == "" || Convert.IsDBNull(costCentreEntry.costCentreName))
                                {
                                    costCentreEntryFailed = true;
                                }
                            }
                            else
                            {
                                costCentreEntryFailed = true;
                            }
                            sumTotalAmount += costCentreEntry.amount;
                        }

                        if (sumTotalAmount != data.CostCentre[0].amount)
                        {
                            costCentreEntryFailed = true;
                        }
                        if (!costCentreEntryFailed)
                        {
                            foreach (var costCentreEntry in CostCentreEntries)
                            {
                                var costCentreEntryQry = $"INSERT INTO [cost_centre_entries] ([cost_centre_name],[cost_centre_id],[ledger_id],[transactions_id]," +
                                                            $"[particulars],[amount],[isDebtor]) VALUES " +
                                                            $"(@cost_centre_name,@cost_centre_id,@ledger_id,@transactions_id," +
                                                            $"@particulars,@amount,@isDebtor)";
                                List<KeyValuePair<string, object>> cstprms = new List<KeyValuePair<string, object>>();
                                cstprms.Add(new KeyValuePair<string, object>("cost_centre_name", costCentreEntry.costCentreName));
                                cstprms.Add(new KeyValuePair<string, object>("cost_centre_id", costCentreEntry.costCentreId));
                                cstprms.Add(new KeyValuePair<string, object>("ledger_id", costCentreEntry.ledgerId));
                                cstprms.Add(new KeyValuePair<string, object>("transactions_id", data.CostCentre[0].transaction_id));
                                cstprms.Add(new KeyValuePair<string, object>("particulars", ledger));
                                cstprms.Add(new KeyValuePair<string, object>("amount", costCentreEntry.amount));
                                cstprms.Add(new KeyValuePair<string, object>("isDebtor", costCentreEntry.isDebtor));

                                //int rowsAffected = DataService.ExecuteSql(costCentreEntryQry, null, cstprms, false);

                                cmd.CommandText = costCentreEntryQry;
                                cmd.Parameters.Clear();
                                FillParams(cstprms, cmd.Parameters);
                                cmd.ExecuteNonQuery();
                            }
                        }
                        else
                        {
                            transaction.Rollback();
                            return new Result() { statusId = ResultType.Failed, message = "Invalid Cost Centre entries found. OR Amount not tallying." };
                        }

                    }
                    
                    transaction.Commit();
                    data.CostCentreEntrystatus = (costCentreEntryFailed == true) ? CostCentreEntryStatus.StatusCode.Failed : CostCentreEntryStatus.StatusCode.Success;
                    JsonSerializerSettings settings = new JsonSerializerSettings
                    {
                        NullValueHandling = NullValueHandling.Ignore
                    };
                    var ccdata = JsonConvert.SerializeObject(costCentreLogData, settings);
                    FinascopLog.UpdateFinascopCostCentre_log (-1, (int)data.CostCentreEntrystatus, data.CostCentreEntrystatus.ToString(), ccdata, costCentreLogData.order_order_id, costCentreLogData.order_event);
                    return new Result() { statusId = ResultType.Success, message = "Updated Cost Centres." };
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error 460: " + ex.Message };
                }
                finally
                {

                    con.Close();
                }
            }
            catch (Exception ex2)
            {
                return new Result() { statusId = ResultType.Error, message = "Execution failure: " + ex2.Message };
            }
            
        }

        /// <summary>
        /// Finascop data entry.
        /// </summary>
        /// <param name="transactionEntry"></param>
        /// <returns></returns>       
        public static Result DataEntry(TransactionEntry transactionEntry)
        {
            List<CostCentreLogData> costCentreLogDataList = new List<CostCentreLogData>();

            try
            {
                SqlConnection con = new SqlConnection(ConnectionString);


                SqlCommand cmd = con.CreateCommand();
                con.Open();
                SqlTransaction transaction = con.BeginTransaction("DATAENTRY");
                cmd.Transaction = transaction;
                int voucher_serial_no_tracker_id = 0;
               
                
                
                try
                {
                    string sql = $"SELECT TOP 1 * FROM  [data_entry] WHERE entry_RefId = @entry_RefId AND [event] = @event";
                    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    lprms.Add(new KeyValuePair<string, object>("entry_RefId", transactionEntry.entry_RefId));
                    lprms.Add(new KeyValuePair<string, object>("event", transactionEntry.order_event));
                    var tbl = GetDataTable(sql, parmeters: lprms);
                    if (tbl != null && tbl.Rows.Count > 0)
                    {
                        return new Result() { statusId = ResultType.Failed, message = "Duplicate RefId." };
                    }


                }
                catch (Exception ex1)
                {
                    return new Result() { statusId = ResultType.Failed, message = $"Duplicate RefId. Caused EXCEPTION.: {ex1}" };
                }

                try
                {
                    string sql = $"INSERT INTO [voucher_serial_no_tracker] (voucher_type_id, docSerialPrefix)" +
                        $" VALUES(@voucher_type_id,dbo.[NuGetVoucherPrefix](@vchr_type_id,@finascopBrID,@vouhcerDate)); SELECT SCOPE_IDENTITY();";
                    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    lprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.TransactionTypeId));
                    lprms.Add(new KeyValuePair<string, object>("vchr_type_id", transactionEntry.TransactionTypeId));
                    lprms.Add(new KeyValuePair<string, object>("finascopBrID", transactionEntry.finascopBrID));
                    lprms.Add(new KeyValuePair<string, object>("vouhcerDate", transactionEntry.voucherDate));
                    cmd.CommandText = sql;
                    cmd.CommandType = CommandType.Text;
                    cmd.Parameters.Clear();
                    FillParams(lprms, cmd.Parameters);
                    voucher_serial_no_tracker_id = System.Convert.ToInt32(cmd.ExecuteScalar());
                }
                catch (Exception exD)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error 496: voucher_type_id: " + transactionEntry.TransactionTypeId + ". Exception :" + exD.Message };
                }

                try
                {
                    string sqlDataEntry = $"INSERT INTO [data_entry] (voucher_type_id,createdOn,amount,narration,doc_serial_nos_typeId,docSerialPrefix," +
                        $"store_group_name,store_group_id,store_group_refId,br_Name_store_group,br_ID_store_group,entry_type,entry_RefId," +
                        $"entity_id, event, voucher_serial_no_tracker_id,blob_storage_folder) " +
                        $"VAlUES ( @voucher_type_id,@createdOn,@amount,@narration,@doc_serial_nos_typeId,dbo.[NuGetVoucherPrefix](@vchr_type_id,@finascopBrID,@createdOn), " +
                        $"@store_group_name,@storegroup_id,@store_group_refId,@br_Name_store_group,@br_ID_store_group,@entry_type," +
                        $"@entry_RefId,@entity_id, @event,@voucher_serial_no_tracker_id,@blob_storage_folder); select SCOPE_IDENTITY();";
                    List<KeyValuePair<string, object>> dprms = new List<KeyValuePair<string, object>>();
                    dprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.TransactionTypeId));
                    dprms.Add(new KeyValuePair<string, object>("amount", transactionEntry.Account.Sum(b => b.amount)));
                    dprms.Add(new KeyValuePair<string, object>("narration", transactionEntry.Narration));
                    dprms.Add(new KeyValuePair<string, object>("doc_serial_nos_typeId", transactionEntry.docTypeID));
                    dprms.Add(new KeyValuePair<string, object>("vchr_type_id", transactionEntry.TransactionTypeId));

                    dprms.Add(new KeyValuePair<string, object>("finascopBrID", transactionEntry.finascopBrID));
                    dprms.Add(new KeyValuePair<string, object>("createdOn", transactionEntry.voucherDate));

                    

                    dprms.Add(new KeyValuePair<string, object>("store_group_name", (transactionEntry.StoreGroupName)));
                    dprms.Add(new KeyValuePair<string, object>("storegroup_id", (transactionEntry.storeGroupId ?? -1)));
                    dprms.Add(new KeyValuePair<string, object>("store_group_refId", transactionEntry.storeGroupRefId));
                    dprms.Add(new KeyValuePair<string, object>("br_Name_store_group", transactionEntry.br_Name_store_group));
                    dprms.Add(new KeyValuePair<string, object>("br_ID_store_group", transactionEntry.br_ID_store_group));
                    dprms.Add(new KeyValuePair<string, object>("entry_type", transactionEntry.entry_type));
                    dprms.Add(new KeyValuePair<string, object>("entry_RefId", transactionEntry.entry_RefId));
                    dprms.Add(new KeyValuePair<string, object>("entity_id", transactionEntry.order_order_id));
                    dprms.Add(new KeyValuePair<string, object>("event", transactionEntry.order_event));
                    dprms.Add(new KeyValuePair<string, object>("voucher_serial_no_tracker_id", voucher_serial_no_tracker_id));
                    dprms.Add(new KeyValuePair<string, object>("blob_storage_folder", transactionEntry.blob_storage_folder?.Trim() ?? ""));
                    cmd.CommandText = sqlDataEntry;
                    cmd.Parameters.Clear();
                    FillParams(dprms, cmd.Parameters);
                    var newid = cmd.ExecuteScalar();
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

                    foreach (TransactionData item in transactionEntry.Account)
                    {
                        if (Math.Round((double)item.amount, 2) == 0)
                        {
                            continue;
                        }
                        string sqlLedgerExists = $"IF EXISTS (select * from [ledger] WHERE id = @ledgerID) select 1 ELSE select 0;";
                        List<KeyValuePair<string, object>> ldrprms = new List<KeyValuePair<string, object>>();
                        ldrprms.Add(new KeyValuePair<string, object>("ledgerID", item.ledgerId));
                        cmd.CommandText = sqlLedgerExists;
                        cmd.Parameters.Clear();
                        cmd.CommandType = CommandType.Text;
                        FillParams(ldrprms, cmd.Parameters);
                        int ledgerExists = System.Convert.ToInt32(cmd.ExecuteScalar());
                        if (ledgerExists == 0)
                        {
                            return new Result() { statusId = ResultType.Error, message = "DB Error 467: Invalid Ledger in Accounts!" };
                        }
                        prms = new List<KeyValuePair<string, object>>();

                        //prms.Add(new KeyValuePair<string, object>("transaction_id", 0));
                        prms.Add(new KeyValuePair<string, object>("data_entry_id", newid));
                        prms.Add(new KeyValuePair<string, object>("ledgerId", item.ledgerId));
                        prms.Add(new KeyValuePair<string, object>("isDebtor", item.isDebtor));
                        prms.Add(new KeyValuePair<string, object>("particulars", item.particulars));
                        prms.Add(new KeyValuePair<string, object>("amount", item.amount));
                        prms.Add(new KeyValuePair<string, object>("storegroup_id", (transactionEntry.storeGroupId ?? -1)));
                        prms.Add(new KeyValuePair<string, object>("reference", transactionEntry.reference));
                        cmd.CommandText = "AddDataEntry";//sqlInsertParticulars;
                        cmd.CommandType = CommandType.StoredProcedure;
                        cmd.Parameters.Clear();
                        FillParams(prms, cmd.Parameters);
                        var transactionID = cmd.ExecuteScalar();

                        //if the entry has cost centre etries
                        List<CostCentreEntry> CostCentreEntries = item.CostCentreEntries;

                        if (CostCentreEntries != null && CostCentreEntries.Any())
                        {
                            bool costCentreEntryFailed = false;
                            double sumTotalAmount = 0;
                            CostCentreLogData costCentreLogData = new CostCentreLogData();
                            costCentreLogData.order_event = transactionEntry.order_event;
                            costCentreLogData.order_order_id = transactionEntry.order_order_id;
                            costCentreLogData.costCentreRule = CostCentreEntries[0].costCentreRule;
                            costCentreLogData.CostCentre = new List<TransactionData>();
                            item.transaction_id = transactionID.ToString();
                            foreach (var costCentreEntry in CostCentreEntries)
                            {
                                if (costCentreEntry.costCentreId == -1 && !String.IsNullOrEmpty(costCentreEntry.costCentreRefId))
                                {
                                    costCentreEntry.costCentreId = GetCostCentreId(costCentreEntry.costCentreRefId);
                                }
                                if (costCentreEntry.costCentreId > 0)
                                {
                                    costCentreEntry.costCentreName = GetCostCentreName(costCentreEntry.costCentreId)?.Trim();
                                    if (costCentreEntry.costCentreName?.Trim() == "" || Convert.IsDBNull(costCentreEntry.costCentreName))
                                    {
                                        costCentreEntryFailed = true;
                                    }

                                }
                                else
                                {
                                    costCentreEntryFailed = true;
                                }
                                sumTotalAmount += costCentreEntry.amount;
                            }

                            if (sumTotalAmount != item.amount)
                            {
                                costCentreEntryFailed = true;
                            }


                            if (!costCentreEntryFailed)
                            {
                                foreach (var costCentreEntry in CostCentreEntries)
                                {
                                    try
                                    {
                                        var costCentreEntryQry = $"INSERT INTO [cost_centre_entries] ([cost_centre_name],[cost_centre_id],[ledger_id],[transactions_id]," +
                                                            $"[particulars],[amount],[isDebtor]) VALUES " +
                                                            $"(@cost_centre_name,@cost_centre_id,@ledger_id,@transactions_id," +
                                                            $"@particulars,@amount,@isDebtor)";
                                        List<KeyValuePair<string, object>> cstprms = new List<KeyValuePair<string, object>>();
                                        cstprms.Add(new KeyValuePair<string, object>("cost_centre_name", costCentreEntry.costCentreName));
                                        cstprms.Add(new KeyValuePair<string, object>("cost_centre_id", costCentreEntry.costCentreId));
                                        cstprms.Add(new KeyValuePair<string, object>("ledger_id", costCentreEntry.ledgerId));
                                        cstprms.Add(new KeyValuePair<string, object>("transactions_id", transactionID));
                                        cstprms.Add(new KeyValuePair<string, object>("particulars", GetLedgerNameFromID(costCentreEntry.ledgerId)));
                                        cstprms.Add(new KeyValuePair<string, object>("amount", costCentreEntry.amount));
                                        cstprms.Add(new KeyValuePair<string, object>("isDebtor", costCentreEntry.isDebtor));

                                        cmd.CommandText = costCentreEntryQry;
                                        cmd.CommandType = CommandType.Text;
                                        cmd.Parameters.Clear();
                                        FillParams(cstprms, cmd.Parameters);
                                        cmd.ExecuteScalar();
                                    }
                                    catch (Exception exD)
                                    {
                                        return new Result() { statusId = ResultType.Error, message = "DB Error 542: " + exD.Message };
                                    }

                                }
                            }
                            item.CostCentreEntrystatus = (costCentreEntryFailed) ? CostCentreEntryStatus.StatusCode.Failed:CostCentreEntryStatus.StatusCode.Success;
                            costCentreLogData.CostCentreEntrystatus = (costCentreEntryFailed) ? CostCentreEntryStatus.StatusCode.Failed : CostCentreEntryStatus.StatusCode.Success;
                            
                            costCentreLogData.CostCentre.Add(item);
                            costCentreLogDataList.Add(costCentreLogData);


                            int cceStatus = (int)costCentreLogData.CostCentreEntrystatus;
                            string cceMessage = costCentreLogData.CostCentreEntrystatus.ToString();
                            JsonSerializerSettings settings = new JsonSerializerSettings
                            {
                                NullValueHandling = NullValueHandling.Ignore
                            };
                            var ccdata = JsonConvert.SerializeObject(costCentreLogData, settings);
                            FinascopLog.FinascopCostCentre_log(-1, cceStatus, cceMessage, ccdata, costCentreLogData.order_order_id, costCentreLogData.order_event);
                        }

                    }

                    foreach (TransactionData item in transactionEntry.Particulars)
                    {
                        if (Math.Round((double)item.amount, 2) == 0)
                        {
                            continue;
                        }
                        string sqlLedgerExists = $"IF EXISTS (select * from [ledger] WHERE id = @ledgerID) select 1 ELSE select 0;";
                        List<KeyValuePair<string, object>> ldrprms = new List<KeyValuePair<string, object>>();
                        ldrprms.Add(new KeyValuePair<string, object>("ledgerID", item.ledgerId));
                        cmd.CommandText = sqlLedgerExists;
                        cmd.CommandType = CommandType.Text;
                        cmd.Parameters.Clear();
                        FillParams(ldrprms, cmd.Parameters);
                        int ledgerExists = System.Convert.ToInt32(cmd.ExecuteScalar());
                        if (ledgerExists == 0)
                        {
                            return new Result() { statusId = ResultType.Error, message = "DB Error 484: Invalid Ledger in Particulars!" };
                        }


                        prms = new List<KeyValuePair<string, object>>();
                        //prms.Add(new KeyValuePair<string, object>("transaction_id", 0));
                        prms.Add(new KeyValuePair<string, object>("data_entry_id", newid));
                        prms.Add(new KeyValuePair<string, object>("ledgerId", item.ledgerId));
                        prms.Add(new KeyValuePair<string, object>("isDebtor", item.isDebtor));
                        prms.Add(new KeyValuePair<string, object>("particulars", item.particulars));
                        prms.Add(new KeyValuePair<string, object>("amount", item.amount));
                        prms.Add(new KeyValuePair<string, object>("storegroup_id", (transactionEntry.storeGroupId ?? -1)));
                        prms.Add(new KeyValuePair<string, object>("reference", transactionEntry.reference));
                        cmd.CommandText = "AddDataEntry";// sqlInsertParticulars;
                        cmd.CommandType = CommandType.StoredProcedure;
                        cmd.Parameters.Clear();
                        FillParams(prms, cmd.Parameters);
                        var transactionID = cmd.ExecuteScalar();

                        //if the entry has cost centre etries
                        List<CostCentreEntry> CostCentreEntries = item.CostCentreEntries;
                        if (CostCentreEntries != null && CostCentreEntries.Any())
                        {
                            bool costCentreEntryFailed = false;
                            double sumTotalAmount = 0;
                            CostCentreLogData costCentreLogData = new CostCentreLogData();
                            costCentreLogData.order_event = transactionEntry.order_event;
                            costCentreLogData.order_order_id = transactionEntry.order_order_id;
                            costCentreLogData.costCentreRule = CostCentreEntries[0].costCentreRule;
                            costCentreLogData.CostCentre = new List<TransactionData>();
                            item.transaction_id = transactionID.ToString();
                            foreach (var costCentreEntry in CostCentreEntries)
                            {
                                if (costCentreEntry.costCentreId == -1 && !String.IsNullOrEmpty(costCentreEntry.costCentreRefId))
                                {
                                    costCentreEntry.costCentreId = GetCostCentreId(costCentreEntry.costCentreRefId);
                                }
                                if (costCentreEntry.costCentreId > 0)
                                {
                                    costCentreEntry.costCentreName = GetCostCentreName(costCentreEntry.costCentreId)?.Trim();
                                    if (costCentreEntry.costCentreName?.Trim() == "" || Convert.IsDBNull(costCentreEntry.costCentreName))
                                    {
                                        costCentreEntryFailed = true;
                                    }
                                }
                                else
                                {
                                    costCentreEntryFailed = true;
                                }
                                sumTotalAmount += costCentreEntry.amount;
                            }
                            if (sumTotalAmount != item.amount)
                            {
                                costCentreEntryFailed = true;
                            }
                            if (!costCentreEntryFailed)
                            {
                                foreach (var costCentreEntry in CostCentreEntries)
                                {
                                    try
                                    {
                                        var costCentreEntryQry = $"INSERT INTO [cost_centre_entries] ([cost_centre_name],[cost_centre_id],[ledger_id],[transactions_id]," +
                                                            $"[particulars],[amount],[isDebtor]) VALUES " +
                                                            $"(@cost_centre_name,@cost_centre_id,@ledger_id,@transactions_id," +
                                                            $"@particulars,@amount,@isDebtor)";
                                        List<KeyValuePair<string, object>> cstprms = new List<KeyValuePair<string, object>>();
                                        cstprms.Add(new KeyValuePair<string, object>("cost_centre_name", costCentreEntry.costCentreName));
                                        cstprms.Add(new KeyValuePair<string, object>("cost_centre_id", costCentreEntry.costCentreId));
                                        cstprms.Add(new KeyValuePair<string, object>("ledger_id", costCentreEntry.ledgerId));
                                        cstprms.Add(new KeyValuePair<string, object>("transactions_id", transactionID));
                                        cstprms.Add(new KeyValuePair<string, object>("particulars", GetLedgerNameFromID(costCentreEntry.ledgerId)));
                                        cstprms.Add(new KeyValuePair<string, object>("amount", costCentreEntry.amount));
                                        cstprms.Add(new KeyValuePair<string, object>("isDebtor", costCentreEntry.isDebtor));

                                        cmd.CommandText = costCentreEntryQry;
                                        cmd.CommandType = CommandType.Text;
                                        cmd.Parameters.Clear();
                                        FillParams(cstprms, cmd.Parameters);
                                        cmd.ExecuteScalar();
                                    }
                                    catch (Exception exD)
                                    {
                                        return new Result() { statusId = ResultType.Error, message = "DB Error 543: " + exD.Message };
                                    }

                                }
                            }
                            item.CostCentreEntrystatus = (costCentreEntryFailed) ? CostCentreEntryStatus.StatusCode.Failed : CostCentreEntryStatus.StatusCode.Success;
                            costCentreLogData.CostCentreEntrystatus = (costCentreEntryFailed) ? CostCentreEntryStatus.StatusCode.Failed : CostCentreEntryStatus.StatusCode.Success;
                            
                            costCentreLogData.CostCentre.Add(item);
                            costCentreLogDataList.Add(costCentreLogData);

                            int cceStatus = (int)costCentreLogData.CostCentreEntrystatus;
                            string cceMessage = costCentreLogData.CostCentreEntrystatus.ToString();
                            JsonSerializerSettings settings = new JsonSerializerSettings
                            {
                                NullValueHandling = NullValueHandling.Ignore
                            };
                            var ccdata = JsonConvert.SerializeObject(costCentreLogData, settings);
                            FinascopLog.FinascopCostCentre_log(-1, cceStatus, cceMessage, ccdata, costCentreLogData.order_order_id, costCentreLogData.order_event);
                        }
                    }

                    try
                    {
                        string sql = $"DECLARE @docSerialPrefix VARCHAR(20); SET @docSerialPrefix = (SELECT [docSerialPrefix] FROM [voucher_serial_no_tracker] WHERE [id] = {voucher_serial_no_tracker_id})" +
                            $"DECLARE @docSerialNo VARCHAR(50); SET  @docSerialNo = dbo.[NuGenerateVoucherSerial](@voucher_type_id,@docSerialPrefix,@voucherDate); " +
                            $"UPDATE [data_entry] SET docSerialNo = @docSerialNo, voucherSlNoString = CONCAT(@docSerialPrefix,@docSerialNo) , financial_year = dbo.[NuGetFinancialYear](@voucherDate) " +
                            $"WHERE voucher_serial_no_tracker_id = @voucher_serial_no_tracker_id; " +
                            $"UPDATE [voucher_serial_no_tracker] SET docSerialNo = @docSerialNo, RefId = @refId , voucherSlNoString  = CONCAT(@docSerialPrefix,@docSerialNo)  " +
                            $"WHERE id = @voucher_serial_no_tracker_id;";
                        List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                        lprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.docTypeID));
                        //lprms.Add(new KeyValuePair<string, object>("docSerialNo", voucher_serial_no));
                        lprms.Add(new KeyValuePair<string, object>("voucher_serial_no_tracker_id", voucher_serial_no_tracker_id));
                        lprms.Add(new KeyValuePair<string, object>("refId", transactionEntry.entry_RefId));
                        lprms.Add(new KeyValuePair<string, object>("voucherDate", transactionEntry.voucherDate));
                        cmd.CommandText = sql;
                        cmd.CommandType = CommandType.Text;
                        cmd.Parameters.Clear();
                        FillParams(lprms, cmd.Parameters);
                        cmd.ExecuteScalar();
                    }
                    catch (Exception exD)
                    {
                        transaction.Rollback();
                        return new Result() { statusId = ResultType.Error, message = "DB Error 432: " + exD.Message };
                    }


                    transaction.Commit();
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error 456: " + ex.Message };
                }
                finally
                {
                    con.Close();
                }
            }
            catch (Exception ex2)
            {
                return new Result() { statusId = ResultType.Failed, message = "Execution failure: " + ex2.Message };
            }
            try
            {
                var result = LogData.InsertCostCenteLogEntry("CostCentreEntry", costCentreLogDataList, transactionEntry.entry_RefId, transactionEntry.order_order_id, transactionEntry.order_event).Result;
            }catch(Exception ex)
            {
                return new Result() { statusId = ResultType.Exception, message = $"Function InsertCostCenteLogEntry failed. {ex.ToString()}" };
            }
            
            return new Result() { statusId = ResultType.Success, message = "Success"};
        }


        public static Result CreateTenantLedger(string name, string mobile, string refid)//, string companyRefId, string branchRefId)
        {
            if (String.IsNullOrEmpty(refid))
            {
                Guid id = Guid.NewGuid();
                refid = id.ToString().ToUpper();
            }
            try
            {

                SqlConnection con = new SqlConnection(ConnectionString);
                SqlCommand cmd = con.CreateCommand();
                con.Open();
                SqlTransaction transaction = con.BeginTransaction("CREATE_LEDGER");
                cmd.Transaction = transaction;

                try
                {
                    string sqlInsertAccount = $"INSERT INTO [ledger] " +
                        $"(name,groups_id, groups_refId, isSystem, isEnabled," +
                        $"company_id, company_refId," +
                        $"branch_id ,branch_refId, " +
                        $"isApiCreated, refId,IsAuto) " +
                        $"VALUES (@name,@groups_id,@groups_refId,@isSystem,@isEnabled, " +
                        $"@company_id,@company_refId," +
                        $"@branch_id,@branch_refId, " +
                        $"@isApiCreated, @refId,@isAuto); select SCOPE_IDENTITY();"
                        ;
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

                    prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    prms.Add(new KeyValuePair<string, object>("groups_id", 195));
                    prms.Add(new KeyValuePair<string, object>("groups_refId", "A2413B4E-23C1-429B-ADD5-31A202FAFBB7"));
                    prms.Add(new KeyValuePair<string, object>("isSystem", "0"));
                    prms.Add(new KeyValuePair<string, object>("isEnabled", "1"));
                    prms.Add(new KeyValuePair<string, object>("company_id", 10));
                    prms.Add(new KeyValuePair<string, object>("company_refId", "663A8057-B03C-414E-8D57-9BA769A9C3FA"));
                    prms.Add(new KeyValuePair<string, object>("branch_id", 1));
                    prms.Add(new KeyValuePair<string, object>("branch_refId", "3714D1E3-737D-4F1E-8831-F440C9895EA3"));
                    prms.Add(new KeyValuePair<string, object>("isApiCreated", "1"));
                    //prms.Add(new KeyValuePair<string, object>("refId", ReferenceId));
                    prms.Add(new KeyValuePair<string, object>("refId", refid));
                    prms.Add(new KeyValuePair<string, object>("isAuto", 1));
                    //prms.Add(new KeyValuePair<string, object>("store_group_entity_id", newid));
                    cmd.CommandText = sqlInsertAccount;
                    cmd.Parameters.Clear();
                    FillParams(prms, cmd.Parameters);
                    var ledger_id = cmd.ExecuteScalar();

                    string sqlInsertCostCentre = $"INSERT INTO [cost_centre] ([name],[cost_category_id],[IsAuto],[refId],[ledger_id])" +
                        $" VALUES (@name,@cost_category_id,@isAuto,@refId,@ledger_id);";

                    List<KeyValuePair<string, object>> cc_prms = new List<KeyValuePair<string, object>>();

                    cc_prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    cc_prms.Add(new KeyValuePair<string, object>("cost_category_id", BusinessObject.CostCategory.Merchants));
                    cc_prms.Add(new KeyValuePair<string, object>("isAuto", 1));
                    cc_prms.Add(new KeyValuePair<string, object>("refId", refid));
                    cc_prms.Add(new KeyValuePair<string, object>("ledger_id", ledger_id));
                    cmd.CommandText = sqlInsertCostCentre;
                    cmd.Parameters.Clear();
                    FillParams(cc_prms, cmd.Parameters);
                    cmd.ExecuteNonQuery();

                    transaction.Commit();
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error 467: " + ex.Message };
                }
                finally
                {

                    con.Close();
                }
            }
            catch (Exception ex2)
            {
                return new Result() { statusId = ResultType.Error, message = "Execution failure: " + ex2.Message };
            }

            return new Result() { statusId = ResultType.Success, message = "Success", refId = refid };
        }


        public static Result CreateGroupLedger(string name, string mobile, string refid, string group_id)
        {
            if (String.IsNullOrEmpty(refid))
            {
                Guid id = Guid.NewGuid();
                refid = id.ToString().ToUpper();
            }
            try
            {

                SqlConnection con = new SqlConnection(ConnectionString);
                SqlCommand cmd = con.CreateCommand();
                con.Open();
                SqlTransaction transaction = con.BeginTransaction("CREATE_LEDGER");
                cmd.Transaction = transaction;

                try
                {
                    string sqlInsertAccount = $"INSERT INTO [ledger] " +
                        $"(name,groups_id, groups_refId, isSystem, isEnabled," +
                        $"company_id, company_refId," +
                        $"branch_id ,branch_refId, " +
                        $"isApiCreated, refId,IsAuto) " +
                        $"VALUES (@name,@groups_id,@groups_refId,@isSystem,@isEnabled, " +
                        $"@company_id,@company_refId," +
                        $"@branch_id,@branch_refId, " +
                        $"@isApiCreated, @refId,@isAuto); select SCOPE_IDENTITY();"
                        ;
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

                    prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    prms.Add(new KeyValuePair<string, object>("groups_id", Convert.ToInt32(group_id)));
                    prms.Add(new KeyValuePair<string, object>("groups_refId", DataService.GetGroupRefIdFromID(Convert.ToInt32(group_id))));
                    prms.Add(new KeyValuePair<string, object>("isSystem", "0"));
                    prms.Add(new KeyValuePair<string, object>("isEnabled", "1"));
                    prms.Add(new KeyValuePair<string, object>("company_id", 10));
                    prms.Add(new KeyValuePair<string, object>("company_refId", "663A8057-B03C-414E-8D57-9BA769A9C3FA"));
                    prms.Add(new KeyValuePair<string, object>("branch_id", 1));
                    prms.Add(new KeyValuePair<string, object>("branch_refId", "3714D1E3-737D-4F1E-8831-F440C9895EA3"));
                    prms.Add(new KeyValuePair<string, object>("isApiCreated", "1"));
                    prms.Add(new KeyValuePair<string, object>("isAuto", 1));
                    //prms.Add(new KeyValuePair<string, object>("refId", ReferenceId));
                    prms.Add(new KeyValuePair<string, object>("refId", refid));
                    //prms.Add(new KeyValuePair<string, object>("store_group_entity_id", newid));
                    cmd.CommandText = sqlInsertAccount;
                    cmd.Parameters.Clear();
                    FillParams(prms, cmd.Parameters);
                    var ledger_id = cmd.ExecuteScalar();

                    string sqlInsertCostCentre = $"INSERT INTO [cost_centre] ([name],[cost_category_id],[IsAuto],[refId],[ledger_id])" +
                        $" VALUES (@name,@cost_category_id,@isAuto,@refId,@ledger_id);";

                    List<KeyValuePair<string, object>> cc_prms = new List<KeyValuePair<string, object>>();

                    cc_prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    cc_prms.Add(new KeyValuePair<string, object>("cost_category_id", BusinessObject.CostCategory.Merchants));
                    cc_prms.Add(new KeyValuePair<string, object>("isAuto", 1));
                    cc_prms.Add(new KeyValuePair<string, object>("refId", refid));
                    cc_prms.Add(new KeyValuePair<string, object>("ledger_id", ledger_id));
                    cmd.CommandText = sqlInsertCostCentre;
                    cmd.Parameters.Clear();
                    FillParams(cc_prms, cmd.Parameters);
                    cmd.ExecuteNonQuery();

                    transaction.Commit();
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error 478: " + ex.Message };
                }
                finally
                {

                    con.Close();
                }
            }
            catch (Exception ex2)
            {
                return new Result() { statusId = ResultType.Error, message = "Execution failure: " + ex2.Message };
            }

            return new Result() { statusId = ResultType.Success, message = "Success", refId = refid };
        }

        public static string GetGroupRefIdFromID(int group_id)
        {

            try
            {
                string sql = $"SELECT [refId] FROM [groups] WHERE id = @group_id";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("group_id", group_id));
                var groupRefId = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(groupRefId);

            }
            catch (Exception ex)
            {
                return "Exception : " + ex.ToString();
            }

        }
        public static int GetLedgerId(string strRef)
        {

            try
            {
                string sql = $"SELECT id FROM [ledger] WHERE refId = @refId";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("refId", strRef));
                var ledgerId = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToInt32(ledgerId);

            }
            catch (Exception ex)
            {

            }

            return 0;
        }

        public static string GetLedgerNameFromID(int ledgerId)
        {

            try
            {
                string sql = $"SELECT name FROM [ledger] WHERE id = @ledgerId";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("ledgerId", ledgerId));
                var ledgerNme = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(ledgerNme);

            }
            catch (Exception ex)
            {

            }

            return "Error";
        }

        public static string GetLedgerName(string strRef)
        {

            try
            {
                string sql = $"SELECT name FROM [ledger] WHERE refId = @refId";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("refId", strRef));
                var ledgerNme = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(ledgerNme);

            }
            catch (Exception ex)
            {

            }

            return "Error";
        }
        public static string GetLedgerName(int id)
        {

            try
            {
                string sql = $"SELECT name FROM [ledger] WHERE id = @id";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("id", id));
                var ledgerNme = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(ledgerNme);

            }
            catch (Exception ex)
            {

            }

            return "Error";
        }

        public static string GetCostCentreName(int id)
        {

            try
            {
                string sql = $"SELECT name FROM [cost_centre] WHERE id = @id";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("id", id));
                var costCentreName = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(costCentreName);

            }
            catch (Exception ex)
            {
                return null;
            }
         
        }

        public static int GetCostCentreId(string costCentreRefId)
        {

            try
            {
                string sql = $"SELECT id FROM [cost_centre] WHERE refId = @refId";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("refId", costCentreRefId));
                var costCentreId = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToInt32(costCentreId);

            }
            catch (Exception ex)
            {

            }
            return -1;
        }

        public static bool isDuplicateEntry(string entry_RefId, string order_event)
        {
            string sql = $"SELECT TOP 1 * FROM  [data_entry] WHERE entry_RefId = @entry_RefId AND [event] = @event";
            List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
            lprms.Add(new KeyValuePair<string, object>("entry_RefId", entry_RefId));
            lprms.Add(new KeyValuePair<string, object>("event", order_event));
            var tbl = GetDataTable(sql, parmeters: lprms);
            if (tbl != null && tbl.Rows.Count > 0)
            {
                return true;
            }
            return false;
        }

        public static bool isDuplicateLog(string order_order_id, string order_event)
        {
            string sql = $"SELECT TOP 1 * FROM  [finascop_log] WHERE [order_order_id] = @order_order_id AND [order_event] = @event";
            List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
            lprms.Add(new KeyValuePair<string, object>("order_order_id", order_order_id));
            lprms.Add(new KeyValuePair<string, object>("event", order_event));
            var tbl = GetDataTable(sql, parmeters: lprms);
            if (tbl != null && tbl.Rows.Count > 0)
            {
                return true;
            }
            return false;
        }

    }
}
