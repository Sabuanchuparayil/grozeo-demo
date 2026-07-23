using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel
{
    [Serializable]
    public class CostCentreEntryView
    {
        /// <summary>
        /// TransactionId
        /// </summary>
        public int TransactionId { get; set; }
        /// <summary>
        /// LedgerId
        /// </summary>
        public int LedgerId { get; set; }
        /// <summary>
        /// Cost Centre Name
        /// </summary>
        public string costCentreName { get; set; }
        /// <summary>
        /// Cost Centre id
        /// </summary>
        public int costCentreId { get; set; }
        /// <summary>
        /// Cost Allocation Amount
        /// </summary>
        public double amount { get; set; }
    }
}
