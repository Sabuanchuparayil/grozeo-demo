using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class CategoryGroup
    {
        [JsonPropertyName("groupid")]
        public int GroupId { get; set; }
        [JsonPropertyName("item_name")]
        public string Name { get; set; }
        [JsonPropertyName("itemDisplayName")]
        public string DisplayName { get; set; }
        [JsonPropertyName("iteamGroupImage")]
        public string GroupImage { get; set; }
    }
}
