using System;
using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;
using System.Collections.Generic;

namespace DataEntry
{ 

public static class DailySalesReport
    {

            public static object getDailySalesReport(string store_group_id, string from_date, string to_date)
            {

                try
                {
                    string sql = $"";
                    List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                    if (String.IsNullOrEmpty(from_date) && String.IsNullOrEmpty(to_date))
                    {
                        sql = $"SELECT CONVERT(DATE, CreatedOn, 101) AS CreatedOn, " +
                        $"(SELECT COUNT(1) FROM [transactions] itr WHERE itr.ledger_id = 30 AND itr.CreatedOn = tr.CreatedOn AND isDebtor = 0 AND [store_group_id] = @store_group_id GROUP BY [itr].[createdOn]) AS NumberOfOrders," +
                        "SUM(COALESCE(CASE WHEN([tr].ledger_id = 30 AND isDebtor = 0) THEN tr.amount END, 0)) as Sales," +
                        "SUM(COALESCE(CASE WHEN([tr].ledger_id = 32  AND isDebtor = 1) THEN tr.amount END, 0)) as DelCharges," +
                        "SUM(COALESCE(CASE WHEN(isDebtor = 1 AND ([tr].ledger_id = 21 OR[tr].ledger_id = 22 OR[tr].ledger_id = 23 OR [tr].ledger_id = 24 )) THEN tr.amount END, 0)) as Taxes," +
                        "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 54) THEN - tr.amount END, 0)) as BankCharges," +
                        "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 52) THEN - tr.amount END, 0)) as DelCharges," +
                        "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND ([tr].ledger_id = 41  OR[tr].ledger_id = 42  OR [tr].ledger_id = 43 OR [tr].ledger_id = 44 )) THEN - tr.amount END, 0)) as TCS," +
                        "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 57) THEN - tr.amount END, 0)) as TDS," +
                        "SUM(COALESCE(CASE WHEN([tr].ledger_id = 56 AND isDebtor = 0) THEN - tr.amount END, 0)) as OrderRefund " +
                        " FROM [transactions] tr WHERE[store_group_id] = @store_group_id " +
                        " AND MONTH(CreatedOn) = Month(getutcdate()) and year(CreatedOn) = year(getutcdate()) " +
                        " GROUP BY[createdOn]";
                    }
                    else
                    {
                        sql = $"SELECT CONVERT(date, CreatedOn, 101) AS CreatedOn, " +
                        $"(SELECT COUNT(1) FROM [transactions] itr WHERE itr.ledger_id = 30 AND itr.CreatedOn = tr.CreatedOn AND isDebtor = 0 AND [store_group_id] = @store_group_id GROUP BY [itr].[createdOn]) AS NumberOfOrders," +
                            "SUM(COALESCE(CASE WHEN([tr].ledger_id = 30 AND isDebtor = 0) THEN tr.amount END, 0)) as Sales," +
                            "SUM(COALESCE(CASE WHEN([tr].ledger_id = 32  AND isDebtor = 1) THEN tr.amount END, 0)) as DelCharges," +
                            "SUM(COALESCE(CASE WHEN(isDebtor = 1 AND ([tr].ledger_id = 21 OR [tr].ledger_id = 22 OR [tr].ledger_id = 23 OR [tr].ledger_id = 24)) THEN tr.amount END, 0)) as Taxes," +
                            "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 54) THEN - tr.amount END, 0)) as BankCharges," +
                            "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 52) THEN - tr.amount END, 0)) as DelCharges," +
                            "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND ([tr].ledger_id = 41  OR[tr].ledger_id = 42  OR [tr].ledger_id = 43 OR [tr].ledger_id = 44)) THEN - tr.amount END, 0)) as TCS," +
                            "SUM(COALESCE(CASE WHEN(isDebtor = 0 AND [tr].ledger_id = 57) THEN - tr.amount END, 0)) as TDS," +
                            "SUM(COALESCE(CASE WHEN([tr].ledger_id = 56 AND isDebtor = 0) THEN - tr.amount END, 0)) as OrderRefund " +
                            " FROM [transactions] tr WHERE[store_group_id] = @store_group_id " +
                            " AND (isnull(@from_date , '') like '' or [createdOn] >= @from_date) AND  (isnull(@to_date, '') like '' or [createdOn] <= @to_date)" +
                            " GROUP BY[createdOn]";
                    lprms.Add(new KeyValuePair<string, object>("from_date", from_date));
                        lprms.Add(new KeyValuePair<string, object>("to_date", to_date));
                    }


                    lprms.Add(new KeyValuePair<string, object>("store_group_id", Convert.ToInt32(store_group_id)));

                    var ledgerTransactions = DataService.GetDataTable(sql, parmeters: lprms);
                    return ledgerTransactions;

                }
                catch (Exception ex)
                {

                }

                return "Error";
            }

    [FunctionName("DailySalesReport")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            log.LogInformation("C# HTTP trigger function processed a request.");

            string store_group_id = req.Query["store_group_id"];
            string from_date = req.Query["from_date"];
            string to_date = req.Query["to_date"];

            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
            dynamic data = JsonConvert.DeserializeObject(requestBody);

            DateTime now = DateTime.Now;
            var startDate = new DateTime(now.Year, now.Month, 1);
            var endDate = startDate.AddMonths(1).AddDays(-1);

            store_group_id = store_group_id ?? data?.store_group_id;
            from_date = from_date ?? data?.from_date;// ?? startDate.ToString();
            to_date = to_date ?? data?.to_date;// ?? endDate.ToString();

            var tenant_transactions_json = getDailySalesReport(store_group_id, from_date, to_date);

            return new OkObjectResult(tenant_transactions_json);
        }
    }
}
