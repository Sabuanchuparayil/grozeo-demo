using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class CategoryData
    {
        [JsonPropertyName("parent_category_id")]
        public int ParentCategoryId { get; set; }

        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }

        [JsonPropertyName("parent_category")]
        public string ParentCategoryName { get; set; }

        [JsonPropertyName("category_name")]
        public string CategoryName { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("subcategories")]
        public List<SubcategoryMaster> Subcategories { get; set; } = new List<SubcategoryMaster>();
    }
}