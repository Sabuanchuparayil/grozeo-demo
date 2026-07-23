using System;
using System.Collections.Generic;
using System.Data;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Host;
using Microsoft.Extensions.Logging;
using System.Data.SqlClient;
using Newtonsoft.Json;

namespace DataEntry
{
    public class FinascopAudit
    {
        [FunctionName("FinascopAudit")]
        public void Run([TimerTrigger("0 5 0 * * *")]TimerInfo myTimer, ILogger log)
        {
            log.LogInformation($"C# Timer trigger function: FinascopAudit executed at: {DateTime.Now}");

            var yesterday = DateTime.Today.AddDays(-1);
            var today = DateTime.Today;

            // get all storeRefId of all the stores that were successfully created yesterday
            string sql = $"SELECT store_group_id, storeRefId FROM finascop_branch_group WHERE created_on >= @yesterday AND created_on < @today";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("yesterday", yesterday));
            prms.Add(new KeyValuePair<string, object>("today", today));

            DataTable newBranches = DataServiceMySql.GetDataTable(sql, parmeters: prms);
            if (newBranches != null && newBranches.Rows.Count > 0)
            {

                foreach (DataRow newBranch in newBranches.Rows)
                {
                    // check the case whether the finascop_branch_group has refId entered.
                    string storeRefId = newBranch.Field<string>("storeRefId");
                    if (String.IsNullOrEmpty(storeRefId))
                    {
                        //get the ledger with the expected Name
                        int store_group_id = newBranch.Field<int>("store_group_id");
                        string ledNameQry = $"SELECT CONCAT(br_Name,'_',br_Phone) as LedgerName FROM finascop_branch WHERE br_storeGroup = @store_group_id";
                        List<KeyValuePair<string, object>> brprms = new List<KeyValuePair<string, object>>();
                        brprms.Add(new KeyValuePair<string, object>("store_group_id", store_group_id));
                        //ExecuteScalar(string sql, string sqlconnection, List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
                        var res = DataServiceMySql.ExecuteScalar(ledNameQry, "", parmeters: brprms);
                        String ledger_Name = System.Convert.ToString(res);


                        string sqlLedgerrRefId = string.Format($"SELECT refId FROM [ledger] WHERE name = '@ledgeName';");
                        List<KeyValuePair<string, object>> lriprms = new List<KeyValuePair<string, object>>();
                        lriprms.Add(new KeyValuePair<string, object>("ledgeName", ledger_Name));
                        var refId = DataService.ExecuteScalar(sqlLedgerrRefId, parmeters: lriprms);
                        string ledgerRefId = System.Convert.ToString(refId);

                        //check whether the log has already a failure entry of the corresponding ledger creation

                        string sqlLogEntryCheck = string.Format($"SELECT id FROM [finascop_log] WHERE entry_RefId = '@ledRefId';");
                        List<KeyValuePair<string, object>> lecprms = new List<KeyValuePair<string, object>>();
                        lecprms.Add(new KeyValuePair<string, object>("ledRefId", ledgerRefId));
                        var ledId = DataService.ExecuteScalar(sqlLedgerrRefId, parmeters: lriprms);
                        string ledgerId = System.Convert.ToString(ledId);
                        var data = new { name = newBranch.Field<string>("store_group_name"), mobile = ledger_Name.Substring(ledger_Name.IndexOf("_") + 1), refid = ledgerRefId };
                        if (!String.IsNullOrEmpty(ledgerId))
                        {
                            FinascopLog.Finascop_log(-1, "ClientLedgerCreation.(From Audit)", 2, JsonConvert.SerializeObject(data), log, "", "Client Ledger Creation failed", ledgerRefId);
                            continue;
                        }

                    }
                    else
                    {

                        string sqlLedgerExists = $"IF EXISTS (SELECT * FROM [ledger] WHERE refId = @ledgerRefID) SELECT 1 ELSE SELECT 0;";
                        List<KeyValuePair<string, object>> ldrprms = new List<KeyValuePair<string, object>>();
                        ldrprms.Add(new KeyValuePair<string, object>("ledgerRefID", storeRefId));
                        var result = DataService.ExecuteScalar(sqlLedgerExists, parmeters: ldrprms);
                        int ledgerExists = System.Convert.ToInt32(result);
                        if (ledgerExists == 0)
                        {
                            int store_group_id = newBranch.Field<int>("store_group_id");
                            string ledName = $"SELECT CONCAT(br_Name,'_',br_Phone) FROM finascop_branch WHERE br_storeGroup = @store_group_id";
                            List<KeyValuePair<string, object>> brprms = new List<KeyValuePair<string, object>>();
                            brprms.Add(new KeyValuePair<string, object>("store_group_id", store_group_id));
                            var res = DataServiceMySql.ExecuteScalar(ledName, "", parmeters: brprms);
                            String ledger_Name = System.Convert.ToString(res);

                            string sqlLedgerrRefId = string.Format($"SELECT refId FROM [ledger] WHERE name = '@ledgeName';");
                            List<KeyValuePair<string, object>> lriprms = new List<KeyValuePair<string, object>>();
                            lriprms.Add(new KeyValuePair<string, object>("ledgeName", ledger_Name));
                            var refId = DataService.ExecuteScalar(sqlLedgerrRefId, parmeters: lriprms);
                            string ledgerRefId = System.Convert.ToString(refId);

                            string sqlLogEntryCheck = string.Format($"SELECT id FROM [finascop_log] WHERE entry_RefId = '@ledRefId';");
                            List<KeyValuePair<string, object>> lecprms = new List<KeyValuePair<string, object>>();
                            lecprms.Add(new KeyValuePair<string, object>("ledRefId", ledgerRefId));
                            var ledId = DataService.ExecuteScalar(sqlLedgerrRefId, parmeters: lriprms);
                            string ledgerId = System.Convert.ToString(ledId);
                            var data = new { name = newBranch.Field<string>("store_group_name"), mobile = ledger_Name.Substring(ledger_Name.IndexOf("_") + 1), refid = ledgerRefId };
                            if (!String.IsNullOrEmpty(ledgerId))
                            {
                                FinascopLog.Finascop_log(-1, "ClientLedgerCreation.(From Audit)", 2, JsonConvert.SerializeObject(data), log, "", "Client Ledger Creation failed", ledgerRefId);
                                continue;
                            }
                        }
                    }
                }
            }

        }
    }
}
