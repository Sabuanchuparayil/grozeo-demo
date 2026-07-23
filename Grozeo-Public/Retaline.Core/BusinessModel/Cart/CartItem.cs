using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartItem
    {
        [JsonPropertyName("fsi_uid")]
        public int FsiUid { get; set; }

        [JsonPropertyName("item_group_id")]
        public int ItemGroupId { get; set; }

        [JsonPropertyName("item_name")]
        public string ItemName { get; set; }

        [JsonPropertyName("brand_name")]
        public string BrandName { get; set; }

        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }

        [JsonPropertyName("category_name")]
        public string CategoryName { get; set; }

        [JsonPropertyName("variant")]
        public string Variant { get; set; }

        [JsonPropertyName("item_master")]
        public List<CartItemMaster> ItemMaster { get; set; } = new List<CartItemMaster>();
    }
}
