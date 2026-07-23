using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class PaymentStatus
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("payments")]
        public Payments[] Payments { get; set; }
    }

    public class Payments
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }

    }
}
