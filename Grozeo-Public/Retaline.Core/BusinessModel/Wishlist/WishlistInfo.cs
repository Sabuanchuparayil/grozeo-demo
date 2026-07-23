using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Wishlist
{
    public class WishlistInfo
    {
        [JsonPropertyName("current_page")]
        public int CurrentPage { get; set; }

        [JsonPropertyName("data")]
        //public List<WishListItem> Wishlist { get; set; }
        public List<Product> Wishlist { get; set; }

        [JsonPropertyName("first_page_url")]
        public string FirstPageUrl { get; set; }
        [JsonPropertyName("from")]
        public int From { get; set; }
        [JsonPropertyName("last_page")]
        public int LastPage { get; set; }
        [JsonPropertyName("last_page_url")]
        public string LastPageUrl { get; set; }
        [JsonPropertyName("next_page_url")]
        public string NextPageUrl { get; set; }
        [JsonPropertyName("path")]
        public string Path { get; set; }
        [JsonPropertyName("per_page")]
        public int PerPage { get; set; }
        [JsonPropertyName("prev_page_url")]
        public string PrevPageUrl { get; set; }
        [JsonPropertyName("to")]
        public int To { get; set; }
        [JsonPropertyName("total")]
        public int Total { get; set; }

    }
}
