using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;
using RetalineProAgent.Core.Services.Finance;
using RetalineProAgent.Core.BussinessModel.Catalog;

namespace RetalineProAgent.Core.Services
{
    public static class DataService
    {
        private static string ConnectionString { 
            get
            {
                return ConfigurationManager.ConnectionStrings["localConnection"].ConnectionString;
            } 
        }

        public static DataTable GetDataTable(string sql, string sqlconnection="", List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            DataTable dt = new DataTable();

            SqlConnection con = new SqlConnection(sqlconnection);
            SqlCommand cmd = new SqlCommand(sql, con);
            if(isSP)
                cmd.CommandType = CommandType.StoredProcedure;

            con.Open();
            try
            {
                if (parmeters != null)
                    FillParams(parmeters, cmd.Parameters);

                dt.Load(cmd.ExecuteReader());
            }
            catch(Exception ex) {
                throw ex;
            }
            finally
            {
                con.Close();
            }

            return dt;
        }

        public static int ExecuteSql(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
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

        public static object ExecuteScalar(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
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


        public static async void InventoryMapingBulkInsert(DataTable dt, string sqlconnection="")
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
                    bulkCopy.DestinationTableName = "InventoryMapping";
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
        /// APIConnectionString - return the mysql connection string to access API database.
        /// The function get the database name as input parameter and get the remaining connection string from config file and return the resolved full string by combine both.
        /// </summary>
        /// <param name="APIDB">Encrypted Database name or connection string. If it is a full connection string then just return it.</param>
        /// <returns>Mysql DB connection string</returns>
        public static string APIConnectionString(string APIDB)
        {
            // If the input parameter APIDB is a complete connection string then return it.
            if (APIDB.Replace(" ", "").ToLower().Contains("server="))
                return APIDB;

            string mySqlCon = ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString;

            return String.Format(mySqlCon, EncryptionService.DecryptText(APIDB));

        }
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
            cmd = new SqlCommand("SELECT dbo.GenerateVoucherSerial(@typeId)", connection);
            cmd.Parameters.AddWithValue("@typeId", docType);
            DocSerialNumber = cmd.ExecuteScalar().ToString();

            return DocSerialNumber;
        }


        /// <summary>
        /// Finascop data entry.
        /// </summary>
        /// <param name="transactionEntry"></param>
        /// <returns></returns>
        public static Result DataEntry(BussinessModel.Transaction.TransactionEntry transactionEntry)
        {
            try
            {
                string finascopSqlCon = ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString;
                SqlConnection con = new SqlConnection(finascopSqlCon);

                SqlCommand cmd = con.CreateCommand();
                con.Open();
                SqlTransaction transaction = con.BeginTransaction("DATAENTRY");
                cmd.Transaction = transaction;
                int voucher_serial_no_tracker_id = 0;

                try
                {
                    string sql = $"SELECT TOP 1 * FROM  [data_entry] WHERE entry_RefId = @entry_RefId";
                    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    lprms.Add(new KeyValuePair<string, object>("entry_RefId", transactionEntry.entry_RefId));
                    var tbl = GetDataTable(sql, finascopSqlCon, parmeters: lprms);
                    if (tbl != null && tbl.Rows.Count > 0)
                    {
                        return new Result() { statusId = ResultType.Failed, message = "Duplicate RefId." };
                    }


                }
                catch (Exception exD)
                {

                }

                try
                {
                    string sql = $"INSERT INTO [voucher_serial_no_tracker] (voucher_type_id, docSerialPrefix)" +
                        $" VALUES(@voucher_type_id,dbo.[GetVoucherPrefix](@vchr_type_id)); SELECT SCOPE_IDENTITY();";
                    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    lprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.TransactionTypeId));
                    lprms.Add(new KeyValuePair<string, object>("vchr_type_id", transactionEntry.TransactionTypeId));
                    cmd.CommandText = sql;
                    cmd.CommandType = CommandType.Text;
                    cmd.Parameters.Clear();
                    FillParams(lprms, cmd.Parameters);
                    voucher_serial_no_tracker_id = System.Convert.ToInt32(cmd.ExecuteScalar());
                }
                catch (Exception exD)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error: " + exD.Message };
                }

                try
                {
                    string sqlDataEntry = $"INSERT INTO [data_entry] (voucher_type_id,amount,narration,doc_serial_nos_typeId,docSerialPrefix," +
                        $"store_group_name,store_group_id,store_group_refId,br_Name_store_group,br_ID_store_group,entry_type,entry_RefId," +
                        $"entity_id, event, voucher_serial_no_tracker_id,blob_storage_folder) " +
                        $"VAlUES ( @voucher_type_id,@amount,@narration,@doc_serial_nos_typeId,dbo.[GetVoucherPrefix](@vchr_type_id), " + 
                        $"@store_group_name,@storegroup_id,@store_group_refId,@br_Name_store_group,@br_ID_store_group,@entry_type," +
                        $"@entry_RefId,@entity_id, @event,@voucher_serial_no_tracker_id,@blob_storage_folder); select SCOPE_IDENTITY();";
                    List<KeyValuePair<string, object>> dprms = new List<KeyValuePair<string, object>>();
                    dprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.TransactionTypeId));
                    dprms.Add(new KeyValuePair<string, object>("amount", transactionEntry.Account.Sum(b => b.amount)));
                    dprms.Add(new KeyValuePair<string, object>("narration", transactionEntry.Narration));
                    dprms.Add(new KeyValuePair<string, object>("doc_serial_nos_typeId", transactionEntry.docTypeID));
                    dprms.Add(new KeyValuePair<string, object>("vchr_type_id", transactionEntry.TransactionTypeId));
                    dprms.Add(new KeyValuePair<string, object>("store_group_name", transactionEntry.StoreGroupName));
                    dprms.Add(new KeyValuePair<string, object>("storegroup_id", transactionEntry.storeGroupId ?? -1));
                    dprms.Add(new KeyValuePair<string, object>("store_group_refId", transactionEntry.storeGroupRefId));
                    dprms.Add(new KeyValuePair<string, object>("br_Name_store_group", transactionEntry.br_Name_store_group));
                    dprms.Add(new KeyValuePair<string, object>("br_ID_store_group", transactionEntry.br_ID_store_group));
                    dprms.Add(new KeyValuePair<string, object>("entry_type", 2));
                    dprms.Add(new KeyValuePair<string, object>("entry_RefId", transactionEntry.entry_RefId));
                    dprms.Add(new KeyValuePair<string, object>("entity_id", transactionEntry.order_order_id));
                    dprms.Add(new KeyValuePair<string, object>("event", transactionEntry.order_event));
                    dprms.Add(new KeyValuePair<string, object>("voucher_serial_no_tracker_id", voucher_serial_no_tracker_id));
                    dprms.Add(new KeyValuePair<string, object>("blob_storage_folder", transactionEntry.blob_storage_folder));
                    //string newVoucherSerialNo = DataService.GenerateVoucherSerial(transactionEntry.docTypeID);
                    //var docSerialPrefix = newVoucherSerialNo.Substring(0,newVoucherSerialNo.IndexOf('_'));
                    //dprms.Add(new KeyValuePair<string, object>("docSerialPrefix", docSerialPrefix));
                    //var docSerialNo = newVoucherSerialNo.Substring(newVoucherSerialNo.IndexOf('_') + 1);
                    //dprms.Add(new KeyValuePair<string, object>("docSerialNo", docSerialNo));
                    cmd.CommandText = sqlDataEntry;
                    cmd.Parameters.Clear();
                    FillParams(dprms, cmd.Parameters);
                    var newid = cmd.ExecuteScalar();
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

                    foreach (var item in transactionEntry.Account)
                    {
                        //string sqlInsertParticulars = $"INSERT INTO [transactions] (openingBalance,data_entry_id, ledger_id, isDebtor,particulars, amount, store_group_id) " +
                        //    $"VALUES ((select isnull(closingBalance, 0) as clb from transactions where id = (select top 1 id from transactions where ledger_id = @ledgerId order by id desc))," +
                        //    $"@data_entry_id,@ledgerId, @isDebtor,@particulars, @amount, @storegroup_id)";
                        //string sqlInsertParticulars = "AddDataEntry";
                        if (item.amount == 0)
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
                            return new Result() { statusId = ResultType.Error, message = "DB Error: Invalid Ledger in Accounts!" };
                        }
                        prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("data_entry_id", newid));
                        prms.Add(new KeyValuePair<string, object>("ledgerId", item.ledgerId));
                        prms.Add(new KeyValuePair<string, object>("isDebtor", item.isDebtor));
                        prms.Add(new KeyValuePair<string, object>("particulars", item.particulars));
                        prms.Add(new KeyValuePair<string, object>("amount", item.amount));
                        prms.Add(new KeyValuePair<string, object>("reference", item.reference));
                        prms.Add(new KeyValuePair<string, object>("storegroup_id", (transactionEntry.storeGroupId ?? -1)));
                        cmd.CommandText = "AddDataEntry";//sqlInsertParticulars;
                        cmd.CommandType = CommandType.StoredProcedure;
                        cmd.Parameters.Clear();
                        FillParams(prms, cmd.Parameters);
                        cmd.ExecuteNonQuery();
                        if (item.costCentreEntries != null) 
                        {
                            foreach (var costentry in item.costCentreEntries)
                            {
                                string sqlcoast = $"DECLARE @Transactionid int;SET @Transactionid=(select id from transactions where ledger_id=@ledgerId and data_entry_id=@data_entry_id)" +
                                    $"INSERT INTO [cost_centre_entries] (cost_centre_name,cost_centre_id,ledger_id,transactions_id,particulars,amount,isDebtor)" +
                                    $"values (@CostCentreName,@CostCentreId,@ledgerId,@Transactionid,@particulars,@CostAmount,@IsDebit)";
                                List<KeyValuePair<string, object>> cprms = new List<KeyValuePair<string, object>>();
                                cprms.Add(new KeyValuePair<string, object>("CostCentreName", GetCostCentreName(costentry.CostCentreId)));
                                cprms.Add(new KeyValuePair<string, object>("CostCentreId",costentry.CostCentreId));
                                cprms.Add(new KeyValuePair<string, object>("ledgerId",costentry.ledgerId));
                                cprms.Add(new KeyValuePair<string, object>("CostAmount", costentry.CostAmount));
                                cprms.Add(new KeyValuePair<string, object>("IsDebit",item.isDebtor));
                                cprms.Add(new KeyValuePair<string, object>("particulars",item.particulars));
                                cprms.Add(new KeyValuePair<string, object>("data_entry_id",newid));
                                cmd.CommandText = sqlcoast;
                                cmd.CommandType = CommandType.Text;
                                cmd.Parameters.Clear();
                                FillParams(cprms, cmd.Parameters);
                                cmd.ExecuteScalar();

                            }
                        }
                        
                    }

                    foreach (var item in transactionEntry.Particulars)
                    {
                        //string sqlInsertParticulars = $"INSERT INTO [transactions] (openingBalance,data_entry_id, ledger_id, isDebtor,particulars, amount, store_group_id) " +
                        //    $"VALUES ((select isnull(closingBalance, 0) as clb from transactions where id = (select top 1 id from transactions where ledger_id = @ledgerId order by id desc))," +
                        //    $"@data_entry_id,@ledgerId, @isDebtor,@particulars, @amount, @storegroup_id)";
                        if (item.amount == 0)
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
                            return new Result() { statusId = ResultType.Error, message = "DB Error: Invalid Ledger in Particulars!" };
                        }
                        prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("data_entry_id", newid));
                        prms.Add(new KeyValuePair<string, object>("ledgerId", item.ledgerId));
                        prms.Add(new KeyValuePair<string, object>("isDebtor", item.isDebtor));
                        prms.Add(new KeyValuePair<string, object>("particulars", item.particulars));
                        prms.Add(new KeyValuePair<string, object>("amount", item.amount));
                        prms.Add(new KeyValuePair<string, object>("reference", item.reference));
                        prms.Add(new KeyValuePair<string, object>("storegroup_id", (transactionEntry.storeGroupId ?? -1)));
                        cmd.CommandText = "AddDataEntry";// sqlInsertParticulars;
                        cmd.CommandType = CommandType.StoredProcedure;
                        cmd.Parameters.Clear();
                        FillParams(prms, cmd.Parameters);
                        cmd.ExecuteNonQuery();
                        if (item.costCentreEntries != null)
                        {
                            foreach (var costentry in item.costCentreEntries)
                            {
                                string sqlcoast = $"DECLARE @Transactionid int;SET @Transactionid=(select id from transactions where ledger_id=@ledgerId and data_entry_id=@data_entry_id);" +
                                    $"INSERT INTO [cost_centre_entries] (cost_centre_name,cost_centre_id,ledger_id,transactions_id,particulars,amount,isDebtor)" +
                                    $"values (@CostCentreName,@CostCentreId,@ledgerId,@Transactionid,@particulars,@CostAmount,@IsDebit)";
                                List<KeyValuePair<string, object>> cprms = new List<KeyValuePair<string, object>>();
                                cprms.Add(new KeyValuePair<string, object>("CostCentreName", GetCostCentreName(costentry.CostCentreId)));
                                cprms.Add(new KeyValuePair<string, object>("CostCentreId",costentry.CostCentreId));
                                cprms.Add(new KeyValuePair<string, object>("ledgerId",costentry.ledgerId));
                                cprms.Add(new KeyValuePair<string, object>("CostAmount",costentry.CostAmount));
                                cprms.Add(new KeyValuePair<string, object>("IsDebit",item.isDebtor));
                                cprms.Add(new KeyValuePair<string, object>("particulars",item.particulars));
                                cprms.Add(new KeyValuePair<string, object>("data_entry_id",newid));
                                cmd.CommandText = sqlcoast;
                                cmd.CommandType = CommandType.Text;
                                cmd.Parameters.Clear();
                                FillParams(cprms,cmd.Parameters);
                                cmd.ExecuteScalar();

                            }
                        }
                        
                    }

                    try
                    {
                        string sql = $"DECLARE @docSerialNo int; SET  @docSerialNo = DBO.[GenerateVoucherSerial](@voucher_type_id); " +
                            $"UPDATE [data_entry] SET docSerialNo = @docSerialNo " +
                            $"WHERE voucher_serial_no_tracker_id = @voucher_serial_no_tracker_id; " +
                            $"UPDATE [voucher_serial_no_tracker] SET docSerialNo = @docSerialNo, RefId = @refId " +
                            $"WHERE id = @voucher_serial_no_tracker_id;";
                        List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                        lprms.Add(new KeyValuePair<string, object>("voucher_type_id", transactionEntry.docTypeID));
                        //lprms.Add(new KeyValuePair<string, object>("docSerialNo", voucher_serial_no));
                        lprms.Add(new KeyValuePair<string, object>("voucher_serial_no_tracker_id", voucher_serial_no_tracker_id));
                        lprms.Add(new KeyValuePair<string, object>("refId", transactionEntry.entry_RefId));
                        cmd.CommandText = sql;
                        cmd.CommandType = CommandType.Text;
                        cmd.Parameters.Clear();
                        FillParams(lprms, cmd.Parameters);
                        cmd.ExecuteScalar();
                    }
                    catch (Exception exD)
                    {
                        transaction.Rollback();
                        return new Result() { statusId = ResultType.Error, message = "DB Error: " + exD.Message };
                    }
                    //try
                    //{
                    //    string sqlcoast = $"INSERT INTO [cost_centre_entries] (cost_centre_name,cost_centre_id,ledger_id,transactions_id,particulars,amount,isDebtor)" +
                    //  $"values (@CostCentreName,@CostCentreId,@ledgerId,0,,@CostAmount,@IsDebit)";
                    //    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    //    lprms.Add(new KeyValuePair<string, object>("CostCentreName",cost));
                    //}
                    //catch
                    //{
                       
                    //}


                    transaction.Commit();
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error: " + ex.Message };
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

            return new Result() { statusId = ResultType.Success, message = "Success" };
        }


        public static Result CreateTenantLedger(string name, string mobile)//, string companyRefId, string branchRefId)
        {

            Guid id = Guid.NewGuid();
            string ReferenceId = id.ToString().ToUpper();

            try
            {
                string finascopSqlCon = ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString;
                SqlConnection con = new SqlConnection(finascopSqlCon);
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
                        $"isApiCreated, refId) " +
                        $"VALUES (@name,@groups_id,@groups_refId,@isSystem,@isEnabled, " +
                        $"@company_id,@company_refId," +
                        $"@branch_id,@branch_refId, " +
                        $"@isApiCreated, @refId)";
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

                    prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    prms.Add(new KeyValuePair<string, object>("groups_id", 56));
                    prms.Add(new KeyValuePair<string, object>("groups_refId", "3C3A8C20-4929-420F-84C6-14971F0B0404"));
                    prms.Add(new KeyValuePair<string, object>("isSystem", "0"));
                    prms.Add(new KeyValuePair<string, object>("isEnabled", "1"));
                    prms.Add(new KeyValuePair<string, object>("company_id", 10));
                    prms.Add(new KeyValuePair<string, object>("company_refId", "663A8057-B03C-414E-8D57-9BA769A9C3FA"));
                    prms.Add(new KeyValuePair<string, object>("branch_id", 1));
                    prms.Add(new KeyValuePair<string, object>("branch_refId", "3714D1E3-737D-4F1E-8831-F440C9895EA3"));
                    prms.Add(new KeyValuePair<string, object>("isApiCreated", "1"));
                    prms.Add(new KeyValuePair<string, object>("refId", ReferenceId));
                    cmd.CommandText = sqlInsertAccount;
                    cmd.Parameters.Clear();
                    FillParams(prms, cmd.Parameters);
                    cmd.ExecuteNonQuery();

                    Guid ccid = Guid.NewGuid();
                    string cost_centre_refid = ccid.ToString().ToUpper();
                    string sqlInsertCostCentre = $"INSERT INTO [cost_centre] ([name],[cost_category_id],[refId])" +
                        $" VALUES (@name,@cost_category_id,@refId);";

                    List<KeyValuePair<string, object>> cc_prms = new List<KeyValuePair<string, object>>();

                    cc_prms.Add(new KeyValuePair<string, object>("name", name + "_" + mobile));
                    cc_prms.Add(new KeyValuePair<string, object>("cost_category_id", BussinessModel.Finance.CostCategory.Merchants));
                    cc_prms.Add(new KeyValuePair<string, object>("refId", cost_centre_refid));
                    cmd.CommandText = sqlInsertCostCentre;
                    cmd.Parameters.Clear();
                    FillParams(cc_prms, cmd.Parameters);
                    cmd.ExecuteNonQuery();

                    transaction.Commit();
                }
                catch (Exception ex)
                {
                    transaction.Rollback();
                    return new Result() { statusId = ResultType.Error, message = "DB Error: " + ex.Message };
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

            return new Result() { statusId = ResultType.Success, message = "Success", refId = ReferenceId };
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

        private static string GetCostCentreName(int costCentreId )
        {
            try
            {
                string sql = $"SELECT name FROM [cost_centre] WHERE id = @id";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("id", costCentreId));
                var costCentreName = ExecuteScalar(sql, parmeters: lprms);
                return Convert.ToString(costCentreName);

            }
            catch (Exception ex)
            {

            }
            return "Error";
        }

    }
}