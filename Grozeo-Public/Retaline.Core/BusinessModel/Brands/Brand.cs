using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Brands
{
    public class Brand
    {
        [JsonPropertyName("brand_id")]
        public int Id { get; set; }
        [JsonPropertyName("brand_name")]
        public string Name { get; set; }
        [JsonPropertyName("manufacture_id")]
        public int ManufactureId { get; set; }
        [JsonPropertyName("img_url")]
        public string ImageURL { get; set; }
        [JsonPropertyName("img_name")]
        public string ImageName { get; set; }
        [JsonPropertyName("top_brand")]
        public int IsTopBrand { get; set; }
        [JsonPropertyName("status")]
        public string Status { get; set; }

    }
}
