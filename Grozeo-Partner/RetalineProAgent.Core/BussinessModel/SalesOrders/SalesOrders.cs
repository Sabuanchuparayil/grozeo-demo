using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.SalesOrders
{
    public class SalesOrders
    {
        [JsonPropertyName("order_order_id")]
        public int OrdID { get; set; }
        [JsonPropertyName("status_id,")]
        public int OrdStatus { get; set; }
        [JsonPropertyName("order_status_addinfo")]
        public string OrdStatusInfo { get; set; }
        [JsonPropertyName("order_trackURL")]
        public string OrdTrackURL { get; set; }

    }
}
