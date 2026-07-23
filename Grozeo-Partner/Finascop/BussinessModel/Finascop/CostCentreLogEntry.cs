using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using Amazon.DynamoDBv2.Model;



namespace Finascop.BussinessModel
{
    [Serializable]
    public class CostCentreLogEntry
    {
        public Int64 Id { get; set; }
        public string EntityID { get; set; }
        public DateTimeOffset? CreatedOn { get; set; }
        public string CostCentreLogData { get; set; }
        public CostCentreEntryStatus.StatusCode CostCentreEntrystatus { get; set; }
        public string StatusMessage { get; set; }
        public string OrderOrderID { get; set; }
        public string OrderEvent { get; set; }
        public string EntryRefID { get; set; }
        public string LogEditResults{ get; set; }
        public DateTimeOffset? CorrectedOn { get; set; }
    }
}

