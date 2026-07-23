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
    public static class CreateGroupLedger
    {
        [FunctionName("CreateGroupLedger")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            log.LogInformation("C# HTTP trigger function: CreateGroupLedger processed a request.");
            string strRequestData = "";
            try
            {

                string name = req.Query["name"];
                string mobile = req.Query["mobile"];
                string refid = req.Query["refid"];
                string group_id = req.Query["group_id"];

                string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
                strRequestData += "requestBody: " + requestBody;
                strRequestData += $", name = {name}, mobile = {mobile}, refid = {refid}, group_id = {group_id}";
                dynamic data = JsonConvert.DeserializeObject(requestBody);
                name = name ?? data?.name;
                mobile = mobile ?? data?.mobile;
                refid = refid ?? data?.refid;
                group_id = group_id ?? data?.group_id;

                if (String.IsNullOrEmpty(refid))
                {
                    Guid id = Guid.NewGuid();
                    refid = id.ToString().ToUpper();
                }

                Result res = DataService.CreateGroupLedger(name, mobile, refid, group_id);
                //public static Result Finascop_log(int id, string type, int status, string comments, ILogger log, string order_order_id, string order_event, string entry_RefId)

                if (res.statusId == ResultType.Success)
                {
                    FinascopLog.Finascop_log(-1, "Group Ledger Creation", 6, JsonConvert.SerializeObject(data), log, "", "Group Ledger Creation successful.", refid);
                    var logResult = await LogData.InsertLogEntry("Group Ledger Creation", JsonConvert.SerializeObject(data), true, "Successfully Created Group Ledger.",
                        DataEntryStatus.StatusCode.Success, refid, mobile, "CreateGroupLedger");
                }
                else
                {
                    log.LogError("Failed to create group ledger ", new object[] { res });
                    FinascopLog.Finascop_log(-1, "Group Ledger Creation", 7, JsonConvert.SerializeObject(data), log, "", "Group Ledger Creation failed.", refid);
                    await LogData.InsertLogEntry("Group Ledger Creation", JsonConvert.SerializeObject(data), false, "Failed to Create Group Ledger.",
                        DataEntryStatus.StatusCode.Failed_UnknownException, refid, mobile, "CreateGroupLedger");
                }
                return new OkObjectResult(res);

            }
            catch (Exception ex)
            {
                log.LogError(ex.Message, new object[] { strRequestData });
                return new BadRequestObjectResult(new Result { statusId = ResultType.Error, message = ex.Message + ", additional data: " + strRequestData });
            }
        }
    }
}
