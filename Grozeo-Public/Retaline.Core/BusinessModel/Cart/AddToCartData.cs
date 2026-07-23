using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class AddToCartData
    {
        [JsonPropertyName("cart_product_ids")]
        public List<CartProductIdDetails> CartProductIds { get; set; } = new List<CartProductIdDetails>();
    }
}
