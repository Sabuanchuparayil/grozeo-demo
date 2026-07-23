using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Inventory
{
    public class Brand
    {
        [JsonPropertyName("brand_id")]
        public int BrandId { get; set; }
        [JsonPropertyName("brand_name")]
        public string BrandName { get; set; }
        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }
        [JsonPropertyName("img_name")]
        public string ImageName { get; set; }
        [JsonPropertyName("top_brand")]
        public int? TopBrandh { get; set; }
        [JsonPropertyName("brand_status")]
        public int? Status { get; set; }
    }
}
