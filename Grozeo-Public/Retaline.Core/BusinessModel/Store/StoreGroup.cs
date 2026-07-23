using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Store
{
    public class StoreGroup
    {
        [JsonPropertyName("store_group_id")]
        public int Id { get; set; }
        [JsonPropertyName("store_group_name")]
        public string Name { get; set; }
        [JsonPropertyName("distance")]
        public double MinDistance { get; set; }
        [JsonPropertyName("business_types")]
        public string BusinessType { get; set; }
        [JsonPropertyName("logoUrl")]
        public string Logo { get; set; }


    }
}
