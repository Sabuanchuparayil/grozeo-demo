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
    public static class GetTenantTransactions
    {

        public static object getTenantTransactions(string store_group_id, string from_date, string to_date)
        {

            try
            {
                string sql = $"SELECT * FROM [transactions] WHERE [store_group_id] = @store_group_id AND CAST([createdOn] AS date) >= @from_date AND  CAST([createdOn] AS date) <= @to_date";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                if (String.IsNullOrEmpty(from_date) && String.IsNullOrEmpty(to_date))
                {
                    sql = "SELECT * FROM [transactions] WHERE [store_group_id] = @store_group_id and MONTH(CreatedOn) = Month(getutcdate()) and year(CreatedOn) = year(getutcdate())";
                }
                else
                {
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



        [FunctionName("GetTenantTransactions")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            log.LogInformation("C# HTTP trigger function GetTenantTransactions processed a request.");

            string store_group_Refid = req.Query["store_group_Refid"];
            string from_date = req.Query["from_date"];
            string to_date = req.Query["to_date"];

            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
            dynamic data = JsonConvert.DeserializeObject(requestBody);

            //DateTime now = DateTime.Now;
            //var startDate = new DateTime(now.Year, now.Month, 1);
            //var endDate = startDate.AddMonths(1).AddDays(-1);

            

            store_group_Refid = store_group_Refid ?? data?.store_group_Refid;
            int store_group_id = 0;
            //int store_group_id = DataService.GetIdFromRefId(store_group_Refid);
            from_date = from_date ?? data?.from_date;
            to_date = to_date ?? data?.to_date;

            try
            {
                string sql = $"SELECT TOP 1 [store_group_id] FROM [data_entry] WHERE [store_group_refId] = @store_group_refId";
                List<KeyValuePair<string, object>> lprms = new List<KeyValuePair<string, object>>();
                lprms.Add(new KeyValuePair<string, object>("store_group_refId", store_group_Refid.ToString()));
                var sg_id = DataService.ExecuteScalar(sql, parmeters: lprms);
                store_group_id = Convert.ToInt32(sg_id);

            }
            catch (Exception ex)
            {

            }

            var tenant_transactions_json = getTenantTransactions(Convert.ToString(store_group_id), from_date, to_date);

            return new OkObjectResult(tenant_transactions_json);
        }
    }
}
