using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class OrderStatus
    {
        [JsonPropertyName("status_id")]
        public int? StatusId { get; set; }
        [JsonPropertyName("status")]
        public string Status { get; set; }
    }
}
