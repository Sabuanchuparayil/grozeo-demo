using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Home
{
    public class ItemMaster
    {
        [JsonPropertyName("stit_ID")]
        public int StitID { get; set; }

        [JsonPropertyName("item_count")]
        public double ItemCount { get; set; }
        [JsonPropertyName("item_name")]
        public string ItemName { get; set; }

        [JsonPropertyName("package_name")]
        public string PackageName { get; set; }

        [JsonPropertyName("mrp")]
        public double Mrp { get; set; }

        [JsonPropertyName("selling_prize")]
        public double SellingPrice { get; set; }

        [JsonPropertyName("stit_fsiuid")]
        public int StitFsiuid { get; set; }

        [JsonPropertyName("quantity")]
        public string Quantity { get; set; }

        [JsonPropertyName("percentage")]
        public double Percentage { get; set; }

        [JsonPropertyName("main_image")]
        public List<MainImage> MainImage { get; set; }

        [JsonPropertyName("default_value")]
        public int DefaultValue { get; set; }

        [JsonPropertyName("stock_available")]
        public double StockAvailable { get; set; }

        [JsonPropertyName("godown_itemId")]
        public int GodownItemId { get; set; }

        [JsonPropertyName("off_badge_value")]
        public string OffBadgeValue { get; set; }
        [JsonPropertyName("branch_id")]
        public object BranchId { get; set; }
        [JsonPropertyName("branch_type_id")]
        public int? BranchTypeId { get; set; }
        [JsonPropertyName("br_storeGroup")]
        public int StoreGroupId { get; set; }
        public ItemMaster()
        {
            MainImage = new List<MainImage>();
        }
        [JsonPropertyName("stit_SKU")]
        public string SKU { get; set; }
        [JsonPropertyName("stit_foodtype")]
        public int FoodType { get; set; }
        [JsonPropertyName("variantGroupId")]
        public int? VariantGroupId { get; set; }
        [JsonPropertyName("stit_unit")]
        public int? UnitId { get; set; }
        [JsonPropertyName("stit_itemName")]
        public string ProductName { get; set; }
        [JsonPropertyName("stit_product_variant")]
        public string ProductVariant { get; set; }
        [JsonPropertyName("quantity_unit")]
        public Unit ProductUnit { get; set; }
        [JsonPropertyName("hasAgeVerification")]
        public int? HasAgeVerification { get; set; }

    }
}
