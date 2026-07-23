using System;
using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;

namespace DataEntry
{
    public static class CreateTenantLedger
    {
        [FunctionName("CreateTenantLedger")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            log.LogInformation("C# HTTP trigger function: CreateLedger processed a request.");
            string strRequestData = "";
            try { 
            
            string name = req.Query["name"];
            string mobile = req.Query["mobile"];
            string refid = req.Query["refid"];


            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
                strRequestData += "requestBody: " + requestBody;
                strRequestData += $", name = {name}, mobile = {mobile}, refid = {refid}";
            dynamic data = JsonConvert.DeserializeObject(requestBody);
            name = name ?? data?.name;
            mobile = mobile ?? data?.mobile;
            refid = refid ?? data?.refid;

            if (String.IsNullOrEmpty(refid))
            {
                Guid id = Guid.NewGuid();
                refid = id.ToString().ToUpper();
            }

                Result res = DataService.CreateTenantLedger(name,mobile, refid);
            //public static Result Finascop_log(int id, string type, int status, string comments, ILogger log, string order_order_id, string order_event, string entry_RefId)
            
            if (res.statusId == ResultType.Success)
            {
                    FinascopLog.Finascop_log(-1, "Client Ledger Creation", 8, JsonConvert.SerializeObject(data), log, "", "Client Ledger Creation successful.", refid);
                    var logResult = await LogData.InsertLogEntry("Client Ledger Creation", JsonConvert.SerializeObject(data), true, "Successfully Created Ledger.", 
                    DataEntryStatus.StatusCode.Success, refid, mobile, "CreateTenantLedger");
            }
            else
            {
                    log.LogError("Failed to create ledger ", new object[] { res });
                FinascopLog.Finascop_log(-1, "Client Ledger Creation", 9, JsonConvert.SerializeObject(data), log, "", "Client Ledger Creation failed.", refid);
                await LogData.InsertLogEntry("Client Ledger Creation", JsonConvert.SerializeObject(data), false, "Failed to Create Ledger.", 
                    DataEntryStatus.StatusCode.Failed_UnknownException, refid, mobile, "CreateTenantLedger");
            }
            return  new OkObjectResult(res);

            }
            catch(Exception ex)
            {
                log.LogError(ex.Message, new object[] { strRequestData });
                return new BadRequestObjectResult(new Result { statusId= ResultType.Error, message= ex.Message+ ", additional data: "+ strRequestData });
            }

        }
    }
}
