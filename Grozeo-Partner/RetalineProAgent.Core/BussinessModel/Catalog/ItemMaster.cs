using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class ItemMaster
    {
        [JsonPropertyName("stit_ID")]
        public int StitId { get; set; }

        [JsonPropertyName("stit_fsiuid")]
        public int StitFsiUId { get; set; }
        [JsonPropertyName("item_count")]
        public int ItemCount { get; set; }
        [JsonPropertyName("item_name")]
        public string ItemName { get; set; }
        [JsonPropertyName("quantity")]
        public string Quantity { get; set; }
        [JsonPropertyName("package_name")]
        public string PackageName { get; set; }
        [JsonPropertyName("itemId")]
        public int ItemId { get; set; }
        [JsonPropertyName("short_description")]
        public string ShortDesc { get; set; }
        [JsonPropertyName("long_description")]
        public string LongDesc { get; set; }
        [JsonPropertyName("percentage")]
        public double Percentage { get; set; }

        [JsonPropertyName("default_value")]
        public int DefaultValue { get; set; }
        [JsonPropertyName("main_image")]
        public ProductImage[] MainImage { get; set; }

        [JsonPropertyName("additional_image")]
        public ProductImage[] AdditionalImages { get; set; }
        [JsonPropertyName("stock_available")]
        public int StockAvailable { get; set; }
        [JsonPropertyName("mrp")]
        public double MRP { get; set; }
        [JsonPropertyName("selling_price")]
        public double SellingPrice { get; set; }
        [JsonPropertyName("godown_itemId")]
        public int GodownItemId { get; set; }
        [JsonPropertyName("off_badge_value")]
        public string OffBadgeValue { get; set; }

    }
}
