using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Home
{
    public class HomeCategory
    {
        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }

        [JsonPropertyName("category_name")]
        public string CategoryName { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("banner_image_url")]
        public object BannerImageUrl { get; set; }

        [JsonPropertyName("parent_category")]
        public int ParentCategory { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("pdt_count")]
        public int PdtCount { get; set; }

        [JsonPropertyName("level")]
        public object Level { get; set; }

        [JsonPropertyName("isSubProduct")]
        public int IsSubProduct { get; set; }

        [JsonPropertyName("subcategory")]
        public List<SubCategory> SubCategories { get; set; } = new List<SubCategory>();
    }
}
