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
using System.Linq;
using System.Data.SqlClient;
using log4net;
using log4net.Config;
using System.Reflection;
using log4net.Appender;

namespace DataEntry
{
    public static class FinascopDataEntry
    {
        private static string ConnectionString
        {
            get
            {
                return Environment.GetEnvironmentVariable("dbconnection");
            }
        }

        private static bool debug_log
        {
            get
            {
                string value = Environment.GetEnvironmentVariable("debug_log");
                return bool.TryParse(value, out bool result) && result;
            }
        }

        private static readonly ILog _log = LogManager.GetLogger(MethodBase.GetCurrentMethod().DeclaringType);

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




        [FunctionName("FinascopDataEntry")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            Result result = null;

            // Load log4net configuration
            var logRepository = LogManager.GetRepository(Assembly.GetEntryAssembly());
            XmlConfigurator.Configure(logRepository, new FileInfo("log4net.config"));


            if (!logRepository.Configured)
            {
                log.LogInformation("log4net configuration failed. Check the log4net.config file.");
            }

            var rootLogger = ((log4net.Repository.Hierarchy.Hierarchy)logRepository).Root;

            // Find the appender (e.g., RollingFileAppender)
            var appender = rootLogger.Appenders.OfType<RollingFileAppender>().FirstOrDefault();

            if (appender != null)
            {
                // Print the absolute path of the log file
                Console.WriteLine("Log file path: " + appender.File);
                log.LogInformation("Log file path: " + appender.File);
            }
            else
            {
                Console.WriteLine("No RollingFileAppender found in the configuration.");
                log.LogInformation("No RollingFileAppender found in the configuration.");
            }




            //string apiKey = Guid.NewGuid().ToString(); // "ref_id"
            //
            bool debug_mode = debug_log;

            if (debug_mode)
            {
                log.LogInformation("C# HTTP trigger function FinascopDataEntry processed a request.");
                _log.Info("C# HTTP trigger function FinascopDataEntry processed a request.");
            }
            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
            TransactionEntry data = new TransactionEntry();
            try
            {
                if (debug_mode)
                    _log.Debug("DeserializeObject started.");
                data = JsonConvert.DeserializeObject<TransactionEntry>(requestBody, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                if (debug_mode)
                    _log.Debug("DeserializeObject completed.");
            }
            catch (Exception ex)
            {
                _log.Error(ex);
                log.LogError("Invalid Entry! Cannot Deserialize Exception. \n " + ex.Message, ex);
                return new OkObjectResult(new Result { statusId = ResultType.Exception, message = "Invalid Entry! Exception : " + ex.Message });
            }
            //FinascopLog.Finascop_log(1, "Data Entry", 1, "RefId :" + apiKey, log, data.order_order_id, data.order_event, data.entry_RefId);
            try
            {
                if (DataService.isDuplicateEntry(data.entry_RefId, data.order_event))
                {
                    if (debug_mode)
                        _log.Debug("Duplicate Entry.");
                    return new OkObjectResult(new Result { statusId = ResultType.Error, message = "Duplicate Entry!" });
                }
            }
            catch (Exception ex2)
            {
                _log.Error(ex2);
                log.LogError(ex2.Message, ex2);

            }
            try
            {

                if (data != null)
                {
                    string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                    string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                    string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                    string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                    bool accDataCorrect = false, partDataCorrect = false;

                    if (data.Account != null)
                    {
                        if (debug_mode)
                            _log.Debug("Checking Account Data started.");
                        accDataCorrect = updateLedgerNames(data.Account);
                        if (debug_mode)
                            _log.Debug("Checking Account Data Finished.");
                    }
                    if (data.Particulars != null)
                    {
                        if (debug_mode)
                            _log.Debug("Checking Particulars Data started.");
                        partDataCorrect = updateLedgerNames(data.Particulars);

                        if (debug_mode)
                            _log.Debug("Checking Particulars Data Finished.");
                    }

                    if (!accDataCorrect || !partDataCorrect)
                    {
                        try
                        {
                            FinascopLog.Finascop_log(-1, "One or more Ledger in Accounts/Particulars is null OR invalid!", 0, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }
                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "One or more Ledger in Accounts/Particulars is null OR invalid!",
                                DataEntryStatus.StatusCode.AccountOrParticularsIsNULL, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);

                        }

                        return new OkObjectResult(new Result { statusId = ResultType.Error, message = "One or more Ledger in Accounts/Particulars is null OR invalid!" });
                    }
                }


                if (data == null || data.Account == null || data.Particulars == null)
                {
                    updateLedgerNames(data.Account);
                    updateLedgerNames(data.Particulars);

                    string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                    string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                    string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                    string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }
                    try
                    {
                        FinascopLog.Finascop_log(-1, "Account or Particulars is null!", 0, strData, log, order_order_id, order_event, entry_RefId);
                    }
                    catch (Exception exLog)
                    {
                        _log.Error(exLog);
                        log.LogError(exLog.Message, exLog);
                    }

                    try
                    {
                        LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Account or Particulars is null!",
                            DataEntryStatus.StatusCode.AccountOrParticularsIsNULL, entry_RefId, order_order_id, order_event);
                    }
                    catch (Exception exLog2)
                    {
                        _log.Error(exLog2);
                        log.LogError(exLog2.Message, exLog2);
                    }

                    return new OkObjectResult(new Result { statusId = ResultType.Error, message = "Account or Particulars is null!" });
                }

                if (Math.Round(data.Particulars.Sum(a => a.amount), 2) != Math.Round(data.Account.Sum(b => b.amount), 2))
                {
                    updateLedgerNames(data.Account);
                    updateLedgerNames(data.Particulars);

                    string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                    string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                    string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                    string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                    string logMessage = "Account " + Math.Round(data.Account.Sum(a => a.amount), 2) + " and Particulars " + Math.Round(data.Particulars.Sum(b => b.amount), 2) + " is not balanced.";
                    try
                    {
                        FinascopLog.Finascop_log(-1, logMessage, 0, strData, log, order_order_id, order_event, entry_RefId);
                    }
                    catch (Exception exLog)
                    {
                        _log.Error(exLog);
                        log.LogError(exLog.Message, exLog);
                    }

                    try
                    {
                        LogData.InsertLogEntry("FinascopDataEntry", strData, false, logMessage,
                            DataEntryStatus.StatusCode.AccountAndParticularsSumsNotBalanced, entry_RefId, order_order_id, order_event);
                    }
                    catch (Exception exLog2)
                    {
                        _log.Error(exLog2);
                        log.LogError(exLog2.Message, exLog2);
                    }

                    return new OkObjectResult(new Result { statusId = ResultType.Failed, message = logMessage });
                }
                if (data.Account.Sum(b => b.amount) <= 0 || data.Particulars.Sum(b => b.amount) <= 0)
                {
                    string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                    string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                    string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                    string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                    try
                    {
                        LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Transaction amount must be greater than 0.",
                            DataEntryStatus.StatusCode.AccountsOrParticularsSumIsZero, entry_RefId, order_order_id, order_event);
                    }
                    catch (Exception exLog)
                    {
                        _log.Error(exLog);
                        log.LogError(exLog.Message, exLog);
                    }

                    return new OkObjectResult(new Result { statusId = ResultType.Failed, message = "Transaction amount must be greater than 0." });
                }

                foreach (TransactionData item in data.Account)
                {
                    // Assign ledger id if ref id is provided.
                    if (item.ledgerId == 0 && !String.IsNullOrEmpty(item.ledgerRefId))
                    {
                        item.ledgerId = DataService.GetLedgerId(item.ledgerRefId);
                        item.particulars = DataService.GetLedgerName(item.ledgerRefId);

                    }
                    if (item.ledgerId == 0)
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Ledger is null OR invalid!",
                            DataEntryStatus.StatusCode.AccountOrParticularsIsNULL, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }


                        return new OkObjectResult(new Result { statusId = ResultType.Error, message = "Ledger is null OR invalid!" });
                    }
                    else
                    {
                        item.particulars = DataService.GetLedgerName(item.ledgerId);
                    }
                }

                foreach (TransactionData item in data.Particulars)
                {
                    if (item.ledgerId <= 0 && !String.IsNullOrEmpty(item.ledgerRefId))
                    {
                        item.ledgerId = DataService.GetLedgerId(item.ledgerRefId);
                        item.particulars = DataService.GetLedgerName(item.ledgerRefId);
                    }
                    if (item.ledgerId == 0)
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Ledger is null OR invalid!",
                                DataEntryStatus.StatusCode.AccountOrParticularsIsNULL, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }

                        return new OkObjectResult(new Result { statusId = ResultType.Error, message = "Ledger is null OR invalid!" });
                    }
                    else
                    {
                        item.particulars = DataService.GetLedgerName(item.ledgerId);
                    }

                }

                if (debug_mode)
                    _log.Debug("DataService.DataEntry started.");
                result = DataService.DataEntry(data);
                if (debug_mode)
                    _log.Debug("DataService.DataEntry finished.");
                if (result == null)
                {
                    if (debug_mode)
                        _log.Debug("DataService.DataEntry result is null.");
                    log.LogError($"Data Entry retuned NULL.", new object[] { result });
                    try
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                        try
                        {
                            FinascopLog.Finascop_log(-1, "Data Entry retuned NULL", 0, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }

                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Data Entry retuned NULL",
                                DataEntryStatus.StatusCode.Failed_UnknownException, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);
                        }

                    }
                    catch (Exception ex)
                    {
                        log.LogError($"Data Entry retuned NULL; Writing Log to db caused exception :" + ex, new object[] { JsonConvert.SerializeObject(data) });
                        _log.Error(ex);
                        log.LogError(ex.Message, ex);

                    }

                }
                else if (result.statusId == ResultType.Success)
                {
                    if (debug_mode)
                        _log.Debug("DataService.DataEntry is successful.");
                    log.LogInformation($"Successfully Executed Data Entry.");
                    try
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }
                        try
                        {
                            FinascopLog.Finascop_log(1, "Successfully Executed Data Entry", 1, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }

                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, true, "Successfully Executed Data Entry",
                                DataEntryStatus.StatusCode.Success, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);
                        }

                    }
                    catch (Exception ex)
                    {
                        log.LogError($"Successfully Executed Data Entry; Writing Log to db caused exception :" + ex, new object[] { JsonConvert.SerializeObject(data) });
                    }
                }
                else if (result.statusId == ResultType.Error)
                {
                    if (debug_mode)
                        _log.Debug("DataService.DataEntry cause error.");
                    log.LogError($"Error Occurred while Data Entry.", new object[] { result });
                    try
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                        try
                        {
                            FinascopLog.Finascop_log(-1, "Error Occurred while Data Entry", 0, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }
                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData + result.message, false, "Error Occurred while Data Entry",
                                DataEntryStatus.StatusCode.Failed_UnknownException, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);
                        }

                    }
                    catch (Exception ex)
                    {
                        _log.Error(ex);
                        log.LogError(ex.Message, ex);
                        log.LogError($"Error Occurred while Data Entry; Writing Log to db caused exception :" + ex, new object[] { JsonConvert.SerializeObject(data) });
                    }
                }
                else if (result.statusId == ResultType.Failed)
                {
                    if (debug_mode)
                        _log.Debug("DataService.DataEntry is failure.");
                    log.LogError($"Failed Data Entry.", new object[] { result });
                    try
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                        try
                        {
                            FinascopLog.Finascop_log(-1, "Failed Data Entry", 0, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }
                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Failed Data Entry",
                                DataEntryStatus.StatusCode.Failed_UnknownException, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);
                        }

                    }
                    catch (Exception ex)
                    {
                        _log.Error(ex);
                        log.LogError($"Failed Data Entry; Writing Log to db caused exception :" + ex, new object[] { JsonConvert.SerializeObject(data) });
                    }
                }
                else
                {
                    if (debug_mode)
                        _log.Debug("DataService.DataEntry caused Unknown Exception.");
                    log.LogError($"Data Entry Failed - Unknown Exception!", new object[] { result });
                    try
                    {
                        string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                        string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                        string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                        string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }
                        try
                        {
                            FinascopLog.Finascop_log(-1, "Data Entry Failed - Unknown Exception!", 0, strData, log, order_order_id, order_event, entry_RefId);
                        }
                        catch (Exception exLog)
                        {
                            _log.Error(exLog);
                            log.LogError(exLog.Message, exLog);
                        }
                        try
                        {
                            LogData.InsertLogEntry("FinascopDataEntry", strData, false, "Data Entry Failed - Unknown Exception",
                                DataEntryStatus.StatusCode.Failed_UnknownException, entry_RefId, order_order_id, order_event);
                        }
                        catch (Exception exLog2)
                        {
                            _log.Error(exLog2);
                            log.LogError(exLog2.Message, exLog2);
                        }
                    }
                    catch (Exception ex)
                    {
                        _log.Error(ex);
                        log.LogError(ex.Message, ex);
                        log.LogError($"Data Entry Failed - Unknown Exception; Writing Log to db caused exception :" + ex, new object[] { JsonConvert.SerializeObject(data) });
                    }
                }


                //       Finascop_log(-1, "Executed Data Entry", 0, JsonConvert.SerializeObject(data), log);

                return new OkObjectResult(result);
            }
            catch (Exception ex)
            {

                _log.Debug("Exception occurred during Data Entry.", ex);
                log.LogError($"Exception occurred during Data Entry.", new object[] { result });
                try
                {
                    string strData = ""; try { strData = JsonConvert.SerializeObject(data); } catch (Exception ex1) { strData = ex1.Message; }
                    string order_order_id = ""; try { order_order_id = data.order_order_id; } catch (Exception ex1) { order_order_id = ex1.Message; }
                    string order_event = ""; try { order_event = data.order_event; } catch (Exception ex1) { order_event = ex1.Message; }
                    string entry_RefId = ""; try { entry_RefId = data.entry_RefId; } catch (Exception ex1) { entry_RefId = ex1.Message; }

                    try
                    {
                        FinascopLog.Finascop_log(-1, "Exception! " + ex.ToString(), 0, strData, log, order_order_id, order_event, entry_RefId);
                    }
                    catch (Exception exLog)
                    {
                        _log.Error(exLog);
                        log.LogError(exLog.Message, exLog);
                    }
                    try
                    {
                        LogData.InsertLogEntry("FinascopDataEntry", strData, true, "Exception occurred Data Entry",
                            DataEntryStatus.StatusCode.Failed_UnknownException, entry_RefId, order_order_id, order_event);
                    }
                    catch (Exception exLog2)
                    {
                        _log.Error(exLog2);
                        log.LogError(exLog2.Message, exLog2);
                    }

                }
                catch (Exception exc)
                {
                    log.LogError($"Exception occurred Data Entry; Writing Log to db caused exception :" + exc, new object[] { JsonConvert.SerializeObject(data) });
                }
                return new OkObjectResult(new Result { statusId = ResultType.Failed, message = $"Exception occurred. {ex.Message}. {ex.ToString()}" });
            }
        }

        private static bool updateLedgerNames(List<TransactionData> accountHeads)
        {
            bool successStatus = true;
            foreach (TransactionData item in accountHeads)
            {
                // Assign ledger id if ref id is provided.
                if (item.ledgerId == 0 && !String.IsNullOrEmpty(item.ledgerRefId))
                {
                    item.ledgerId = DataService.GetLedgerId(item.ledgerRefId);
                }
                if (item.ledgerId > 0)
                {
                    item.particulars = DataService.GetLedgerName(item.ledgerId);
                }
                else
                {
                    successStatus = false;
                }
            }

            return successStatus;
        }
    }
}
