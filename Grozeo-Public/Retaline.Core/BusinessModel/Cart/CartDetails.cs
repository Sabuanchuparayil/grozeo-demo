using Retaline.Core.Http.CustomConverter;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartDetails
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }

        [JsonPropertyName("cart_customer_id")]
        public int CartCustomerId { get; set; }

        [JsonPropertyName("cart_group_id")]
        public int CartGroupId { get; set; }

        [JsonPropertyName("cart_product_id")]
        public int CartProductId { get; set; }

        [JsonPropertyName("cart_branch_id")]
        public int CartBranchId { get; set; }

        [JsonPropertyName("cart_order_qty")]
        public int? CartOrderQty { get; set; }

        [JsonPropertyName("selling_prize")]
        public double? CartPrice { get; set; }

        [JsonPropertyName("cart_retail_price")]
		[JsonConverter(typeof(NullableDoubleConverter))]
		public double? CartRetailPrice { get; set; }

        [JsonPropertyName("cart_sales_price")]
        public double? CartSalesPrice { get; set; }

        [JsonPropertyName("cart_subcategory_id")]
        public object CartSubcategoryId { get; set; }

        [JsonPropertyName("cart_package_type_id")]
        public object CartPackageTypeId { get; set; }

        [JsonPropertyName("cart_is_taxable")]
        public int CartIsTaxable { get; set; }

        [JsonPropertyName("cart_cgst")]
        public double? CartCgst { get; set; }

        [JsonPropertyName("cart_sgst")]
        public double? CartSgst { get; set; }

        [JsonPropertyName("cart_igst")]
        public double? CartIgst { get; set; }

        [JsonPropertyName("cart_kfc")]
        public double? CartKfc { get; set; }

        [JsonPropertyName("cart_discount")]
        public double? CartDiscount { get; set; }

        [JsonPropertyName("cart_sku_id")]
        public object CartSkuId { get; set; }

        [JsonPropertyName("cart_status")]
        public string CartStatus { get; set; }

        [JsonPropertyName("item")]
        public CartItem Item { get; set; } = new CartItem();
        [JsonPropertyName("branch_type_id")]
        public int BranchTypeId { get; set; }
        [JsonPropertyName("stock_at_branch")]
        public int? StockAtBranch { get; set; }
		[JsonConverter(typeof(NullableDoubleConverter))]
		[JsonPropertyName("max_delivery_distance")]
        public double? BranchMaxDeliveryDistance { get; set; }
        [JsonPropertyName("distance")]
		[JsonConverter(typeof(NullableDoubleConverter))]
		public double? Distance { get; set; }

        public bool PrescriptionRequired { get; set; }
    }
}
