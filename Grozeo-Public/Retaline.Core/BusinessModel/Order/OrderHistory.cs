using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class OrderHistory
    {
        [JsonPropertyName("order_id")]
        public int OrderId { get; set; }
        [JsonPropertyName("order_status")]
        public int OrderStatusId { get; set; }
        [JsonPropertyName("created_at")]
        public string CreatedOn { get; set; }
        [JsonPropertyName("get_order_status")]
        public OrderStatus Status { get; set; }
        [JsonPropertyName("status")]
        public string StatusMessage { get; set; }
    }
}
