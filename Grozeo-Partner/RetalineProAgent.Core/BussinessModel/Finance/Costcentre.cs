using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Finance
{
    [Serializable]
    public class Costcentre
    {
        /// <summary>
        /// CostCentreName
        /// </summary>
        public string CostCentreName { get; set; }
        /// <summary>
        /// CostAmount
        /// </summary>
        public double CostAmount { get; set; }
        /// <summary>
        /// ledgerId
        /// </summary>
        public int ledgerId { get; set; }
        /// <summary>
        /// CostCentreId
        /// </summary>
        public int CostCentreId { get; set; }
        /// <summary>
        /// If true then Dr, else Cr.
        /// </summary>
        public int IsDebit { get; set; }

    }
}
