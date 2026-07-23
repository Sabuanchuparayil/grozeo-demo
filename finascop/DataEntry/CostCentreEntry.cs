using System;
using System.Collections.Generic;
using System.Text;
using Azure;
using Newtonsoft.Json;

namespace DataEntry
{
    [JsonObject(IsReference = true)]
    public class CostCentreEntry
    {
        /// <summary>
        /// Cost Centre Name
        /// </summary>
        public string costCentreName { get; set; }
        /// <summary>
        /// Cost Centre Rule
        /// </summary>
        public string costCentreRule { get; set; }
        /// <summary>
        /// Cost Centre ID
        /// </summary>
        public int costCentreId { get; set; }
        /// <summary>
        /// Cost Centre Reference ID
        /// </summary>
        public string costCentreRefId { get; set; }
        /// <summary>
        /// Ledger ID
        /// </summary>
        public int ledgerId { get; set; }
        /// <summary>
        /// Transaction ID
        /// </summary>
        public int transactionId { get; set; }
        /// <summary>
        /// Cost Allocation Amount
        /// </summary>
        public double amount { get; set; }
        /// <summary>
        /// Particulars
        /// </summary>
        public string particulars { get; set; }
        /// <summary>
        /// Is Debit
        /// </summary>
        public int isDebtor { get; set; }
    }
    [Serializable]
    public class CostCentreLogData
    {
        public string RowKey { get; set; }  
        public string order_order_id { get; set; }
        public string order_event { get; set; }
        public string costCentreRule { get; set; }
        public List<TransactionData> CostCentre { get; set; }
        public CostCentreEntryStatus.StatusCode CostCentreEntrystatus { get; set; }
        public String ETag { get; set; }
    }

  }
