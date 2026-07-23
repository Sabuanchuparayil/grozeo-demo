using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class SKU
    {
        [JsonPropertyName("stit_ID")]
        public int ID { get; set; }
        [JsonPropertyName("stit_sku")]
        public string Sku { get; set; }
        [JsonPropertyName("stit_brand_name")]
        public string BrandName { get; set; }

        [JsonPropertyName("stit_itemReturnTime")]
        public double? ReturnTime { get; set; }
        //[JsonPropertyName("stit_custInitiate")]

        [JsonPropertyName("return_details")]
        public string ReturnDetails { get; set; }
        [JsonPropertyName("stit_custInitiate")]
        public int? CustInitiate { get; set; }
    }
}
