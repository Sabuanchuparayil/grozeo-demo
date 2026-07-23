using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class BusinessType
    {
        [JsonPropertyName("business_type_id")]
        public int Id { get; set; }

        [JsonPropertyName("business_type_name")]
        public string Name { get; set; }
    }
}
