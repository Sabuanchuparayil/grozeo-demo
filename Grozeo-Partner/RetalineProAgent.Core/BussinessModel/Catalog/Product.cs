using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class Product
    {
        [JsonPropertyName("fsi_uid")]
        public int Id { get; set; }
        [JsonPropertyName("item_group_id")]
        public int GroupId { get; set; }

        [JsonPropertyName("item_name")]
        public string Name { get; set; }
        [JsonPropertyName("brand_name")]
        public string BrandName { get; set; }
        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }
        [JsonPropertyName("category_name")]
        public string CategoryName { get; set; }
        [JsonPropertyName("variant")]
        public string Variant { get; set; }
        [JsonPropertyName("item_master")]
        public List<ItemMaster> Item { get; set; } = new List<ItemMaster>();

    }
}
