using System;
using System.Collections.Generic;
using System.Text;
using Newtonsoft.Json;

namespace Finascop.BussinessModel
{
    [Serializable]
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
}
