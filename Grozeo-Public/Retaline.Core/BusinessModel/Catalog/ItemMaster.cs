//using System.Collections.Generic;
//using System.Text.Json.Serialization;

//namespace Retaline.Core.BusinessModel.Catalog
//{
//    public class ItemMaster
//    {
//        [JsonPropertyName("stit_ID")]
//        public int StitId { get; set; }

//        [JsonPropertyName("stit_fsiuid")]
//        public int StitFsiUId { get; set; }
//        [JsonPropertyName("item_count")]
//        public double ItemCount { get; set; }
//        [JsonPropertyName("item_name")]
//        public string ItemName { get; set; }
//        [JsonPropertyName("quantity")]
//        public string Quantity { get; set; }
//        [JsonPropertyName("package_name")]
//        public string PackageName { get; set; }
//        [JsonPropertyName("itemId")]
//        public int ItemId { get; set; }
//        [JsonPropertyName("short_description")]
//        public string ShortDesc { get; set; }
//        [JsonPropertyName("long_description")]
//        public string LongDesc { get; set; }
//        [JsonPropertyName("percentage")]
//        public double Percentage { get; set; }

//        [JsonPropertyName("default_value")]
//        public int DefaultValue { get; set; }
//        [JsonPropertyName("main_image")]
//        public ProductImage[] MainImage { get; set; }

//        [JsonPropertyName("additional_image")]
//        public ProductImage[] AdditionalImages { get; set; }
//        [JsonPropertyName("stock_available")]
//        public double StockAvailable { get; set; }
//        [JsonPropertyName("mrp")]
//        public double? MRP { get; set; }
//        [JsonPropertyName("selling_price")]
//        public double? SellingPrice { get; set; }
//        [JsonPropertyName("selling_prize")]
//        public double? SellingPrice2 { get; set; }
//        [JsonPropertyName("godown_itemId")]
//        public int GodownItemId { get; set; }
//        [JsonPropertyName("off_badge_value")]
//        public string OffBadgeValue { get; set; }
//        [JsonPropertyName("item_master")]
//        public List<ItemMasterVarient> Item { get; set; } = new List<ItemMasterVarient>();

//        [JsonPropertyName("branch_id")]
//        public int? BranchId { get; set; }
//        [JsonPropertyName("branch_type_id")]
//        public int? BranchTypeId { get; set; }

//        [JsonPropertyName("IsItemGroup")]
//        public int? IsGroupItem { get; set; }
//        [JsonPropertyName("groupDisplayName")]
//        public string GroupDisplayName { get; set; }
//        [JsonPropertyName("iteamGroupImage")]
//        public string GroupImage { get; set; }
//        [JsonPropertyName("groupId")]
//        public int GroupId { get; set; }
//        [JsonPropertyName("stit_SKU")]
//        public string SKU { get; set; }
//        [JsonPropertyName("stit_foodtype")]
//        public int FoodType { get; set; }
//        [JsonPropertyName("countryorgin")]
//        public string Country { get; set; }
//        [JsonPropertyName("br_storeGroup")]
//        public int StoreGroupId { get; set; }

//        [JsonPropertyName("stit_preparation_use")]
//        public string PreparationAndUse { get; set; }
//        [JsonPropertyName("stit_allergens")]
//        public string Allergens { get; set; }
//        [JsonPropertyName("stit_ingredients")]
//        public string Ingredients { get; set; }
//        [JsonPropertyName("stit_itemReturnTime")]
//        public double? ReturnTime { get; set; }
//        [JsonPropertyName("stit_nutritionlabel")]
//        public string NutritionLabel { get; set; }

//        [JsonPropertyName("stit_category_name")]
//        public string CategoryName { get; set; }
//        [JsonPropertyName("stit_brand_name")]
//        public string BrandName { get; set; }
//        [JsonPropertyName("branch_name")]
//        public string StoreName { get; set;}
//        [JsonPropertyName("variantGroupId")]
//        public int? VariantGroupId { get; set; }
//        [JsonPropertyName("stit_unit")]
//        public int? UnitId { get; set; }
//        [JsonPropertyName("stit_itemName")]
//        public string ProductName { get; set; }
//        [JsonPropertyName("stit_product_variant")]
//        public string ProductVariant {  get; set; }
//        [JsonPropertyName("quantity_unit")]
//        public Unit ProductUnit { get; set; }
//    }

//}
