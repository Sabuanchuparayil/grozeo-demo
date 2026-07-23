using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartNearestItem
    {
        [JsonPropertyName("stit_id")]
        public int? StitID { get; set; }

        [JsonPropertyName("item_count")]
        public double? ItemCount { get; set; }

        [JsonPropertyName("mrp")]
        public string Mrp { get; set; }

        [JsonPropertyName("selling_price")]
        public string SellingPrice { get; set; }

        [JsonPropertyName("mindistance")]
        public double? Distance { get; set; }
        [JsonPropertyName("branch_id")]
        public int? BranchId { get; set; }
    }
}