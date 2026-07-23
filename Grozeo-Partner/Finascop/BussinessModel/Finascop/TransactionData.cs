using Finascop.BussinessModel;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel
{
    [Serializable]
    public class TransactionData
    {
        public LedgerType ledgerId { get; set; }
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
        /// Ledger Reference ID
        /// </summary>
        public string ledgerRefId { get; set; }
        /// <summary>
        /// transaction Reference ID
        /// </summary>
        public string transaction_id { get; set; }
        /// <summary>
        /// Cost Centre Entries
        /// </summary>
        public List<CostCentreEntry> CostCentreEntries { get; set; }
    }
}
