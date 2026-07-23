using Retaline.Core.BusinessModel.Wishlist;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartData
    {
        [JsonPropertyName("data")]
        public List<CartDetails> Cart { get; set; }

        [JsonPropertyName("cart")]
        public List<CartDetails> CartMini { get; set; }

        [JsonPropertyName("price")]
        public Price Price { get; set; }
        [JsonPropertyName("price_details")]
        public List<Checkout.CouponLabel> PriceLabels { get; set; }

        [JsonPropertyName("wishlist")]
        public List<WishlistDetails> Wishlist { get; set; } = new List<WishlistDetails>();
        [JsonPropertyName("nearestitems")]
        public List<CartNearestItem> NearestItems { get; set; }
    }
}
