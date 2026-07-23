using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.OrderPacking
{
    public class ManualPacking
    {
        public string status { get; set; }
        public PackingList packinglist { get; set; }
        
    }

    public class PackingList
    {
        public string[] packingNumber { get; set; }
        public int? fstoId { get; set; }
        public int? fstoOrderType { get; set; }
    }

    // {\"status\":\"ok\",\"packinglist\":{\"packingNumber\":[\"2302220001\\/1\\/1\"],\"BoxDetails\":[{\"rpckm_id\":1,\"rpckm_name\":\"Paper Bag\"},{\"rpckm_id\":2,\"rpckm_name\":\"Plastic Bag\"}],\"fstoId\":103131,\"fstoOrderType\":1}}


}
