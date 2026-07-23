using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel
{
    [Serializable]
    public class CostCentreLogData
    {
        public string order_order_id { get; set; }
        public string order_event { get; set; }
        public string costCentreRule { get; set; }
        public List<TransactionData> CostCentre { get; set; }
        public CostCentreEntryStatus.StatusCode CostCentreEntrystatus { get; set; }
    }
}
