using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Azure;
using Azure.Data.Tables;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;

namespace DataEntry
{
    public static class LogData
    {

        private static string NoSQLLogConnectionString
        {
            get
            {
                return Environment.GetEnvironmentVariable("NOSQL_CONNECTION_STRING");
            }
        }

        private static string costCentreLogTable
        {
            get
            {
                return Environment.GetEnvironmentVariable("finascopCostCentreLogTable");
            }
        }
        private static string finascopLog
        {
            get
            {
                return Environment.GetEnvironmentVariable("finascopLog");
            }
        }
        public static async Task<Response> InsertLogEntry(String partitionKey,String jsonDataEntryData,Boolean success, 
            String statusMessage, DataEntryStatus.StatusCode dataEntryStatus, String auditCrossRefKey, String refEntityID, String eventName)
        {
            TableServiceClient tableServiceClient = new TableServiceClient(NoSQLLogConnectionString);

            TableClient tableClient = tableServiceClient.GetTableClient(finascopLog);
            await tableClient.CreateIfNotExistsAsync();
            //Guid.NewGuid().ToString();
            var logentry = new LogEntry()
            {
                PartitionKey = partitionKey,
                RowKey = Guid.NewGuid().ToString(),
                Fin_DataEntry = jsonDataEntryData,
                StatusMessage = statusMessage,
                DataEntryStatus = dataEntryStatus,
                AuditCrossRefKey = auditCrossRefKey,
                Event = eventName,
                RefEntityID = refEntityID,
                Success = success,
                Timestamp = DateTime.Now,
                ETag= ETag.All
            };

            // Add new item to server-side table
            var result = await tableClient.AddEntityAsync<LogEntry>(logentry);

            var newresult = result.ToString();

            return result;
        }

        public static async Task<Response> InsertCostCenteLogEntry(String partitionKey, List<CostCentreLogData> CostCentreData, String auditCrossRefKey, String refEntityID, String eventName)

        {
            TableServiceClient tableServiceClient = new TableServiceClient(NoSQLLogConnectionString);

            TableClient tableClient = tableServiceClient.GetTableClient(costCentreLogTable);
            await tableClient.CreateIfNotExistsAsync();

            Response result = null;

            foreach (CostCentreLogData costEntry in CostCentreData)
            {
                var logentry = new CostCentreLogEntry()
                {
                    PartitionKey = partitionKey,
                    RowKey = Guid.NewGuid().ToString(),
                    CostCentreData = JsonConvert.SerializeObject(costEntry),
                    CostCentreEntryStatus = costEntry.CostCentreEntrystatus,
                    AuditCrossRefKey = auditCrossRefKey,
                    Event = eventName,
                    RefEntityID = refEntityID,
                    Success = (costEntry.CostCentreEntrystatus == CostCentreEntryStatus.StatusCode.Success) ? true : false,
                    Timestamp = DateTime.Now,
                    ETag = ETag.All
                };
                result = await tableClient.AddEntityAsync<CostCentreLogEntry>(logentry);
                var newresult = result.ToString();
            }
            return result;
        }

        public static async Task<Response> UpdateCostCenteLogEntry(String partitionKey,ETag ETag,String RowKey, CostCentreLogData costCentreEntry, String auditCrossRefKey, String refEntityID, String eventName,ILogger log)
        {
            Response result = null;
            try
            {
                TableServiceClient tableServiceClient = new TableServiceClient(NoSQLLogConnectionString);

                TableClient tableClient = tableServiceClient.GetTableClient(costCentreLogTable);
                await tableClient.CreateIfNotExistsAsync();

                var logentry = new CostCentreLogEntry()
                {
                    PartitionKey = partitionKey,
                    RowKey = RowKey,
                    CostCentreData = JsonConvert.SerializeObject(costCentreEntry),
                    CostCentreEntryStatus = costCentreEntry.CostCentreEntrystatus,
                    AuditCrossRefKey = auditCrossRefKey,
                    Event = eventName,
                    RefEntityID = refEntityID,
                    Success = (costCentreEntry.CostCentreEntrystatus == CostCentreEntryStatus.StatusCode.Success) ? true : false,
                    Timestamp = DateTime.Now,
                    ETag = ETag,
                };
                result = await tableClient.UpdateEntityAsync<CostCentreLogEntry>(logentry, logentry.ETag);
            }catch(Exception ex)
            {
                log.LogError($"Exception at UpdateCostCenteLogEntry. Details : {ex}");
            }
            
            
            return result;
        }
    }

    public  class LogEntry : ITableEntity
    {
    public string RowKey { get; set; } = default!;

    public string PartitionKey { get; set; } = default!;

    public string Fin_DataEntry { get; set; } = default!;

    public DataEntryStatus.StatusCode DataEntryStatus { get; set; } = default!;

    public string StatusMessage { get; set; } = default!;

    public bool Success { get; set; }

    public ETag ETag { get; set; } = default!;

    public DateTimeOffset? Timestamp { get; set; } = default!;
        // ETag ITableEntity.ETag { get; set; } = default!;
    public string AuditCrossRefKey { get; set; } = default!;
    public string RefEntityID { get; set; } = default!;
    public string Event { get; set; } = default!;
    }

    public class CostCentreLogEntry : ITableEntity
    {
        public string RowKey { get; set; } = default!;

        public string PartitionKey { get; set; } = default!;

        public string CostCentreData { get; set; } = default!;

        public CostCentreEntryStatus.StatusCode CostCentreEntryStatus { get; set; } = default!;

        public string StatusMessage { get; set; } = default!;

        public bool Success { get; set; }

        public ETag ETag { get; set; } = default!;

        public DateTimeOffset? Timestamp { get; set; } = default!;
        // ETag ITableEntity.ETag { get; set; } = default!;
        public string AuditCrossRefKey { get; set; } = default!;
        public string RefEntityID { get; set; } = default!;
        public string Event { get; set; } = default!;
    }

}
