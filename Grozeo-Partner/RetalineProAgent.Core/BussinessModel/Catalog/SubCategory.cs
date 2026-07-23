using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class SubCategory
    {
        [JsonPropertyName("sub_category_id")]
        public int SubCategoryId { get; set; }

        [JsonPropertyName("sub_category")]
        public string SubCategoryName { get; set; }

        [JsonPropertyName("sub_category_image")]
        public string SubCategoryImage { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("main_category")]
        public int MainCategory { get; set; }

        [JsonPropertyName("subcat_bmd_id")]
        public object SubcatBmdId { get; set; }

        [JsonPropertyName("subcat_bmd_name")]
        public object SubcatBmdName { get; set; }
    }
}
