using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class DiseaseProduct
    {

        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("screen")]
        public string Screen { get; set; }
        [JsonPropertyName("type")]
        public string ItemType { get; set; }
        [JsonPropertyName("type_id")]
        public int TypeId { get; set; }
        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }
        [JsonPropertyName("description")]
        public string Description { get; set; }
        [JsonPropertyName("title")]
        public string Title { get; set; }
        [JsonPropertyName("background_img")]
        public string BackgroundImage { get; set; }
        [JsonPropertyName("is_active")]
        public int IsActive { get; set; }
        [JsonPropertyName("sub_id")]
        public int SubId { get; set; }
        [JsonPropertyName("order")]
        public int Order { get; set; }
        [JsonPropertyName("delivery_type")]
        public int DeliveryType { get; set; }
        [JsonPropertyName("value")]
        public List<Product> Products { get; set; }
        [JsonPropertyName("total_count")]
        public int Total { get; set; }
        [JsonPropertyName("min_count")]
        public int MinCount { get; set; }
        [JsonPropertyName("pagenate_details")]
        public PaginationInfo Pagination { get; set; }

    }
}
