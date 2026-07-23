using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class ItemMasterVarient
    {
        [JsonPropertyName("stit_ID")]
        public int StitId { get; set; }

        [JsonPropertyName("stit_fsiuid")]
        public int StitFsiUId { get; set; }
        [JsonPropertyName("quantity")]
        public string Quantity { get; set; }
        [JsonPropertyName("main_image")]
        public ProductImage[] MainImage { get; set; }
        [JsonPropertyName("stock_available")]
        public double StockAvailable { get; set; }
        [JsonPropertyName("mrp")]
        public double? MRP { get; set; }
        [JsonPropertyName("selling_price")]
        public double? SellingPrice { get; set; }
        [JsonPropertyName("selling_prize")]
        public double? SellingPrice2 { get; set; }
        [JsonPropertyName("branch_id")]
        public int? BranchId { get; set; }
        [JsonPropertyName("branch_type_id")]
        public int? BranchTypeId { get; set; }
        [JsonPropertyName("br_storeGroup")]
        public int StoreGroupId { get; set; }
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


    }
}
