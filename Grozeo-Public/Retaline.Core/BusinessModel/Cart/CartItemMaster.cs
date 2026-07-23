using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartItemMaster
    {
        [JsonPropertyName("stit_ID")]
        public int? StitID { get; set; }

        [JsonPropertyName("item_count")]
        public double? ItemCount { get; set; }

        [JsonPropertyName("package_name")]
        public string PackageName { get; set; }

        [JsonPropertyName("mrp")]
        public double? Mrp { get; set; }

        [JsonPropertyName("selling_price")]
        public double? SellingPrice { get; set; }

        [JsonPropertyName("stit_fsiuid")]
        public int? StitFsiuid { get; set; }

        [JsonPropertyName("quantity")]
        public string Quantity { get; set; }

        [JsonPropertyName("percentage")]
        public double? Percentage { get; set; }

        [JsonPropertyName("main_image")]
        public List<MainImage> MainImage { get; set; } = new List<MainImage>();

        [JsonPropertyName("default_value")]
        public int? DefaultValue { get; set; }

        [JsonPropertyName("stock_available")]
        public double? StockAvailable { get; set; }

        [JsonPropertyName("godown_itemId")]
        public int? GodownItemId { get; set; }

        [JsonPropertyName("off_badge_value")]
        public string OffBadgeValue { get; set; }
        [JsonPropertyName("stit_SKU")]
        public string SKU { get; set; }
        [JsonPropertyName("variantGroupId")]
        public int? VariantGroupId { get; set; }
        [JsonPropertyName("stit_unit")]
        public int? UnitId {  get; set; }
        [JsonPropertyName("stit_itemName")]
        public string ProductName {  get; set; }
        [JsonPropertyName("stit_product_variant")]
        public string ProductVariant { get; set; }
        [JsonPropertyName("quantity_unit")]
        public Unit ProductUnit { get; set; }

    }
}