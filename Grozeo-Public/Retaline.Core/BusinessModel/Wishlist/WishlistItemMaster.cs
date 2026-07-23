using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Wishlist
{
    public class WishlistItemMaster
    {
        [JsonPropertyName("stit_ID")]
        public int StitID { get; set; }

        [JsonPropertyName("stit_fsiuid")]
        public int StitFsiuid { get; set; }

        [JsonPropertyName("quantity")]
        public string Quantity { get; set; }

        [JsonPropertyName("package_name")]
        public string PackageName { get; set; }

        [JsonPropertyName("itemId")]
        public int ItemId { get; set; }

        [JsonPropertyName("short_description")]
        public string ShortDescription { get; set; }

        [JsonPropertyName("long_description")]
        public string LongDescription { get; set; }

        [JsonPropertyName("main_image")]
        public List<ProductImage> MainImage { get; set; }

        [JsonPropertyName("stock_available")]
        public double StockAvailable { get; set; }

        [JsonPropertyName("selling_price")]
        public double SellingPrice { get; set; }

        [JsonPropertyName("mrp")]
        public double? Mrp { get; set; }

        [JsonPropertyName("godown_itemId")]
        public int GodownItemId { get; set; }

        [JsonPropertyName("default_value")]
        public int? DefaultValue { get; set; }
    }
}
