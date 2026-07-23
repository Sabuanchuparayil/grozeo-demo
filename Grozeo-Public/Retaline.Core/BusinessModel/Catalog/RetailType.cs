using System.Security.Permissions;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class RetailType
    {
        [JsonPropertyName("business_category_id")]
        public int Id { get; set; }

        [JsonPropertyName("business_category_name")]
        public string Name { get; set; }
        [JsonPropertyName("status")]
        public int Status { get; set; }
        [JsonPropertyName("business_category_ingroup")]
        public int GroupId { get; set; }
        [JsonPropertyName("rbc_business_type")]
        public string BusinessTypes { get; set; }
        [JsonPropertyName("store_group_id")]
        public int StoreGroupId { get; set; }
        [JsonPropertyName("img")]
        public string ImageUrl {  get; set; }
        public int? DisplayOrder { get; set; }
    }
}
