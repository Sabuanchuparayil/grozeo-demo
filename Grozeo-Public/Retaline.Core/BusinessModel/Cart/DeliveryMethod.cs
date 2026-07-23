using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart
{
    public class DeliveryMethod
    {
        [JsonPropertyName("home_delivery")]
        public DeliveryMethodProperties HomeDelivery { get; set; }
        [JsonPropertyName("courier_delivery")]
        public DeliveryMethodProperties CourierDelivery { get; set; }

    }

    public class DeliveryMethodProperties
    {
        [JsonPropertyName("percentage")]
        public double Percentage { get; set; }
        [JsonPropertyName("selling_price")]
        public double SellingPrice { get; set; }
        [JsonPropertyName("title")]
        public string Title { get; set; }
        [JsonPropertyName("branch_id")]
        public int BranchId { get; set; }
        [JsonPropertyName("description")]
        public string Description { get; set; }

    }
}
