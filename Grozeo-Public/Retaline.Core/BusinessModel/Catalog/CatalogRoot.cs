using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class CatalogRoot
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("data")]
        public List<CategoryData> Data { get; set; } = new List<CategoryData>();
    }
}
