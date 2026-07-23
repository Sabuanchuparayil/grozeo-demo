using Retaline.Core.BusinessModel.Cart.Checkout;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
	public class OrderGroup
	{
        [JsonPropertyName("orders")]
        public MyOrder[] Orders { get; set; }

        [JsonPropertyName("summary")]
        public OrderSummary GroupSummary { get; set; }

    }

    public class OrderSummary
    {
        [JsonPropertyName("net_amount_payable")]
        public Double NetAmount { get; set; }
        [JsonPropertyName("style")]
        public List<CouponLabel> Labels { get; set; }

    }
}
