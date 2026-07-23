using RetalineProAgent.Core.BussinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Home
{
    public class HomeValue
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
        public List<ItemMaster> ItemMaster { get; set; }

        [JsonPropertyName("image")]
        public string Image { get; set; }

        [JsonPropertyName("description")]
        public string Description { get; set; }

        [JsonPropertyName("value")]
        public List<HomeValue> Value { get; set; }

        [JsonPropertyName("id")]
        public int? Id { get; set; }

        [JsonPropertyName("parent_category_id")]
        public int? ParentCategoryId { get; set; }

        [JsonPropertyName("parent_category_name")]
        public string ParentCategoryName { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("subcategories")]
        public List<SubcategoryMaster> Subcategories { get; set; }

        [JsonPropertyName("brand_id")]
        public int? BrandId { get; set; }

        [JsonPropertyName("img_name")]
        public object ImgName { get; set; }

        [JsonPropertyName("top_brand")]
        public int? TopBrand { get; set; }

        [JsonPropertyName("brand_status")]
        public int? BrandStatus { get; set; }

        [JsonPropertyName("min_count")]
        public int? MinCount { get; set; }

        [JsonPropertyName("total_count")]
        public int? TotalCount { get; set; }

        public HomeValue()
        {
            Subcategories = new List<SubcategoryMaster>();
            Value = new List<HomeValue>();
        }

    }
}
