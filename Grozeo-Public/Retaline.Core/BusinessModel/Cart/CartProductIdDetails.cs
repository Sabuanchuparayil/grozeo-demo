using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartProductIdDetails
    {
        [JsonPropertyName("cart_product_id")]
        public int CartProductId { get; set; }

        [JsonPropertyName("cart_order_qty")]
        public int CartOrderQty { get; set; }
        [JsonPropertyName("order_method")]
        public string OrderMethod { get; set; }
    }
}
