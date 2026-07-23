using System;
using Microsoft.Extensions.Logging;
using System.Collections.Generic;
using System.Data.SqlClient;
using Org.BouncyCastle.Utilities.Collections;

namespace DataEntry
{
    class FinascopLog
    {
        private static string ConnectionString
        {
            get
            {
                return Environment.GetEnvironmentVariable("dbconnection");
            }
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

        public static Result Finascop_log(int id, string type, int status, string comments, ILogger log, string order_order_id, string order_event, string entry_RefId)
        {
            SqlConnection con = new SqlConnection(ConnectionString);
            try
            {
                log.LogInformation("Finascop Log called a database table finascop_log request.");

                if (status < 6) // 6 and > values are used for Ledger Creation API call Logs
                {
                    try
                    {
                        if (DataService.isDuplicateLog(order_order_id, order_event))
                        {
                            return new Result() { statusId = ResultType.Error, message = "Duplicate Log Entry! Aborting." };
                        }
                    }
                    catch (Exception ex)
                    {
                        throw new Exception("Exception caused on Duplicate Log Entry checking.");
                    }
                }

                SqlCommand cmd = con.CreateCommand();
                con.Open();


                string sqlInsertLog = $"INSERT INTO [finascop_log] (entity_id, type, status,comments,order_order_id,order_event,entry_RefId) " +
                    $"VALUES (@entity_id, @type,@status, @comments,@order_order_id,@order_event,@entry_RefId)";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("entity_id", id));
                prms.Add(new KeyValuePair<string, object>("type", type));
                prms.Add(new KeyValuePair<string, object>("status", status));
                prms.Add(new KeyValuePair<string, object>("comments", comments));
                prms.Add(new KeyValuePair<string, object>("order_order_id", order_order_id));
                prms.Add(new KeyValuePair<string, object>("order_event", order_event));
                prms.Add(new KeyValuePair<string, object>("entry_RefId", entry_RefId));
                cmd.CommandText = sqlInsertLog;
                cmd.Parameters.Clear();
                FillParams(prms, cmd.Parameters);
                cmd.ExecuteNonQuery();
            }
            catch (Exception ex2)
            {
                log.LogInformation("DB Table Finascop Log failed to process a request. Exception thrown!");
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
                return new Result() { statusId = ResultType.Failed, message = "Execution failure: " + ex2.Message };
            }
            finally
            {
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
            }
            log.LogInformation("Finascop Log processed a request.");
            return new Result() { statusId = ResultType.Success, message = "DB Success " };
        }

        public static Result FinascopCostCentre_log(int id, int status,string statusMessage, string costCentreLogData, string order_order_id, string order_event)
        {
            SqlConnection con = new SqlConnection(ConnectionString);
            try
            {
                
                SqlCommand cmd = con.CreateCommand();
                con.Open();


                string sqlInsertCostCentreLog = $"INSERT INTO [cost_centre_log] (entity_id, costCentreLogData, costCentreEntryStatus, statusMessage, order_order_id,order_event) " +
                    $"VALUES (@entity_id, @costCentreLogData,@status,@statusMessage, @order_order_id,@order_event)";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("entity_id", id));
                prms.Add(new KeyValuePair<string, object>("costCentreLogData", costCentreLogData));
                prms.Add(new KeyValuePair<string, object>("status", status));
                prms.Add(new KeyValuePair<string, object>("statusMessage", statusMessage));
                prms.Add(new KeyValuePair<string, object>("order_order_id", order_order_id));
                prms.Add(new KeyValuePair<string, object>("order_event", order_event));

                cmd.CommandText = sqlInsertCostCentreLog;
                cmd.Parameters.Clear();
                FillParams(prms, cmd.Parameters);
                cmd.ExecuteNonQuery();
            }
            catch (Exception ex2)
            {
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
                return new Result() { statusId = ResultType.Failed, message = "Execution failure: " + ex2.Message };
            }
            finally
            {
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
            }
                return new Result() { statusId = ResultType.Success, message = "DB Success " };
        }
        public static Result UpdateFinascopCostCentre_log(int id, int status, string statusMessage, string costCentreLogData, string order_order_id, string order_event)
        {
            SqlConnection con = new SqlConnection(ConnectionString);
            try
            {

                SqlCommand cmd = con.CreateCommand();
                con.Open();


                string sqlInsertCostCentreLog = $"UPDATE cost_centre_log SET costCentreLogData = @costCentreLogData," +
                    $"costCentreEntryStatus = @status, statusMessage = @statusMessage WHERE order_order_id = @order_order_id AND order_event = @order_event";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("entity_id", id));
                prms.Add(new KeyValuePair<string, object>("costCentreLogData", costCentreLogData));
                prms.Add(new KeyValuePair<string, object>("status", status));
                prms.Add(new KeyValuePair<string, object>("statusMessage", statusMessage));
                prms.Add(new KeyValuePair<string, object>("order_order_id", order_order_id));
                prms.Add(new KeyValuePair<string, object>("order_event", order_event));

                cmd.CommandText = sqlInsertCostCentreLog;
                cmd.Parameters.Clear();
                FillParams(prms, cmd.Parameters);
                cmd.ExecuteNonQuery();
            }
            catch (Exception ex2)
            {
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
                return new Result() { statusId = ResultType.Failed, message = "Execution failure: " + ex2.Message };
            }
            finally
            {
                if (con != null && con.State != System.Data.ConnectionState.Closed)
                    con.Close();
            }
            return new Result() { statusId = ResultType.Success, message = "DB Success " };
        }

    }
}
