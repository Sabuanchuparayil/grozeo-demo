using System;
using System.Collections.Generic;
using System.Text;
using Newtonsoft.Json;
namespace DataEntry
{
    /// <summary>
    /// Transaction input data defines the ledger type and affected value.
    /// </summary>
    [JsonObject(IsReference = true)]
    public class TransactionData
    {         
        /// <summary>
        /// Ledger ID
        /// </summary>
        public int ledgerId { get; set; }
        /// <summary>
        /// Ledger Reference ID
        /// </summary>
        public string ledgerRefId { get; set; }
        /// <summary>
        /// Transaction amount
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
        /// <summary>
        /// Cost Centre Entries
        /// </summary>
        public List<CostCentreEntry> CostCentreEntries { get; set; }
        public string transaction_id { get; set; }
        public CostCentreEntryStatus.StatusCode CostCentreEntrystatus { get; set; }
    }
}
