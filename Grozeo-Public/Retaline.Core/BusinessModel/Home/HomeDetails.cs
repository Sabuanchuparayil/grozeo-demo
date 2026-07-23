using System.Collections.Generic;
using System.Text.Json;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Home
{
    public class HomeDetails
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }

        [JsonPropertyName("type")]
        public string Type { get; set; }

        [JsonPropertyName("type_id")]
        public int TypeId { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("description")]
        public string Description { get; set; }

        [JsonPropertyName("title")]
        public string Title { get; set; }

        [JsonPropertyName("background_img")]
        public string BackgroundImg { get; set; }

        [JsonPropertyName("is_active")]
        public int IsActive { get; set; }

        [JsonPropertyName("order")]
        public int Order { get; set; }

        //[JsonPropertyName("value")]
        public List<HomeValue> Value { get; set; } = new List<HomeValue>();
        [JsonPropertyName("value")]
        public JsonElement DynamicValue { get; set; }

        [JsonPropertyName("min_count")]
        public int? MinCount { get; set; }

        [JsonPropertyName("total_count")]
        public int? TotalCount { get; set; }


    }
}
