using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class OrderItem
    {
        [JsonPropertyName("item_id")]
        public int Id { get; set; }
        [JsonPropertyName("item_order_id")]
        public string OrderId { get; set; }
        [JsonPropertyName("customer_order_id")]
        public int? CustomerOrderId { get; set; }
        [JsonPropertyName("item_product_id")]
        public int? ProductId { get; set; }
        [JsonPropertyName("item_group_id")]
        public int? GroupId { get; set; }
        [JsonPropertyName("item_order_qty")]
        public int? OrderQty { get; set; }
        [JsonPropertyName("item_order_qty_scanned")]
        public int? OrderQtyScanned { get; set; }
        [JsonPropertyName("item_return_qty_sellable")]
        public int? ReturnQtySellable { get; set; }
        [JsonPropertyName("item_return_qty_damaged")]
        public int? ReturnQtyDamaged { get; set; }
        [JsonPropertyName("item_return_qty_damagedinTransit")]
        public int? ReturnQtyDamagedInTransit { get; set; }
        [JsonPropertyName("item_return_qty_requested")]
        public int? ReturnQtyRequested { get; set; }
        [JsonPropertyName("item_price")]
        public double Price { get; set; }
        [JsonPropertyName("item_retail_price")]
        public double RetailPrice { get; set; }
        [JsonPropertyName("item_sales_price")]
        public double SalesPrice { get; set; }
        [JsonPropertyName("item_subcategory_id")]
        public int? SubCategoryId { get; set; }
        [JsonPropertyName("item_package_type_id")]
        public int? PackageTypeId { get; set; }
        [JsonPropertyName("item_is_taxable")]
        public int? IsTaxable { get; set; }
        [JsonPropertyName("item_cgst")]
        public double? CGST { get; set; }
        [JsonPropertyName("item_sgst")]
        public double? SGST { get; set; }
        [JsonPropertyName("item_igst")]
        public double? IGST { get; set; }
        [JsonPropertyName("item_kfc")]
        public double? KFC { get; set; }
        [JsonPropertyName("item_type")]
        public int? Type { get; set; }
        [JsonPropertyName("item_type_offer")]
        public double OfferType { get; set; }
        [JsonPropertyName("item_coupon_code")]
        public string CouponCode { get; set; }
        [JsonPropertyName("bom_id")]
        public int? BomId { get; set; }
        [JsonPropertyName("item_amount")]
        public double Amount { get; set; }
        [JsonPropertyName("item_discount")]
        public double Discount { get; set; }
        [JsonPropertyName("item_discount_total")]
        public double TotalDiscount { get; set; }
        [JsonPropertyName("item_sku_id")]
        public int? SkuId { get; set; }
        [JsonPropertyName("item_status")]
        public string Status { get; set; }

        [JsonPropertyName("item")]
        public SKU Item { get; set; }
        [JsonPropertyName("image")]
        public Image ItemImage { get; set; }
    }
}
