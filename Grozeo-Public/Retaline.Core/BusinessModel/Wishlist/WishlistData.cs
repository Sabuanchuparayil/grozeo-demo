using Retaline.Core.BusinessModel.Cart;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Wishlist
{
    public class WishlistData
    {
        [JsonPropertyName("cart")]
        public List<CartDetails> Cart { get; set; } = new List<CartDetails>();

        [JsonPropertyName("price")]
        public Price Price { get; set; }

        [JsonPropertyName("wishlist")]
        public List<WishlistDetails> Wishlist { get; set; }
    }
}
