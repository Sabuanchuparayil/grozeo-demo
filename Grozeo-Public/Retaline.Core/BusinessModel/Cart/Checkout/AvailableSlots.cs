using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class AvailableSlots
    {
        [JsonPropertyName("slot")]
        public string Slot { get; set; }
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("day")]
        public string Day { get; set; }
        [JsonPropertyName("date")]
        public string Date { get; set; }
        [JsonPropertyName("max")]
        public int Max { get; set; }
    }
}
