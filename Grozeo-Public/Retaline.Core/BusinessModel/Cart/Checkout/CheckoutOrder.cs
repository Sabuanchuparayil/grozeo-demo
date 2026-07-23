using Retaline.Core.BusinessModel.Order;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class CheckoutOrder
    {
        [JsonPropertyName("order_order_id")]
        public string OrderNum { get; set; }
        [JsonPropertyName("order_group_id")]
        public string OrderGroupId { get; set; }
        [JsonPropertyName("order_branch_type_id")]
        public int BranchTypeId { get; set; }
        [JsonPropertyName("order_customer_id")]
        public int CustomerId { get; set; }
        [JsonPropertyName("order_total_amount")]
        public double OrderTotal { get; set; }
        [JsonPropertyName("order_delivery_charge")]
        public double DeliveryCharge { get; set; }
        [JsonPropertyName("order_courier_charge")]
        public double CourierCharge { get; set; }
        [JsonPropertyName("order_total_gst")]
        public double TotalGST { get; set; }
        [JsonPropertyName("order_branch_id")]
        public int BranchId { get; set; }
        [JsonPropertyName("order_company_id")]
        public int CompanyId { get; set; }
        [JsonPropertyName("subtotal")]
        public double SubTotal { get; set; }
        [JsonPropertyName("order_mrp")]
        public double MRP { get; set; }
        [JsonPropertyName("order_saved_amount")]
        public double SavedAmount { get; set; }
        [JsonPropertyName("order_kfc_amount")]
        public double KFC { get; set; }
        [JsonPropertyName("total")]
        public double Total { get; set; }
        [JsonPropertyName("order_roundoff")]
        public double RoundOff { get; set; }
        [JsonPropertyName("order_method")]
        public int OrderMethod { get; set; }
        [JsonPropertyName("order_type")]
        public int OrderType { get; set; }
        [JsonPropertyName("order_discount_amount")]
        public double Discount { get; set; }
        [JsonPropertyName("order_discount_add_total")]
        public double DiscountTotal { get; set; }
        [JsonPropertyName("order_total_cgst")]
        public double CGST { get; set; }
        [JsonPropertyName("order_total_sgst")]
        public double SGST { get; set; }
        [JsonPropertyName("order_portal_afterpayment_redirecturl")]
        public string AfterPaymentRedirectUrl { get; set; }
        [JsonPropertyName("order_id")]
        public int OrderId { get; set; }

        [JsonPropertyName("highest_priced_image")]
        public string HighestPricedImage { get; set; }
        [JsonPropertyName("highest_priced_itemname")]
        public string HighestPricedItemName { get; set; }
        [JsonPropertyName("total_item_count")]
        public int TotalItems { get; set; }
//[JsonPropertyName("itemDetails": "[{\"item_product_id\":20152,\"item_sales_price\":215.25},{\"item_product_id\":20342,\"item_sales_price\":78.6}]",
//                    public string AfterPaymentRedirectUrl { get; set; }
        [JsonPropertyName("branchDetails")]
        public UserDetails.Branch OrderBranch { get; set; }
        [JsonPropertyName("availableslots")]
        public AvailableSlots[] Slots { get; set; }

        [JsonPropertyName("delivery_status")]
        public int DeliveryStatus { get; set; }
        [JsonPropertyName("itemDetails")]
        public List<OrderItem> ItemDetails { get; set; }

    }
}
