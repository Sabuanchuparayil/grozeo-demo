using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Order
{
    public class Order
    {
        //[JsonPropertyName("order_primary_key")]
        public int Id { get; set; }
        [JsonPropertyName("order_order_id")]
        public string OrderNum { get; set; }
        [JsonPropertyName("order_id")]
        public string OrderId { get; set; }
        [JsonPropertyName("order_group_id")]
        public string OrderGroupId { get; set; }
        [JsonPropertyName("order_date")]
        public string OrderDate { get; set; }
        [JsonPropertyName("order_datetime")]
        public string OrderDateTime { get; set; }

        [JsonPropertyName("branch_name")]
        public string BranchName { get; set; }
        [JsonPropertyName("branch_location")]
        public string BranchLocation { get; set; }
        [JsonPropertyName("branch_phone")]
        public string BranchPhone { get; set; }


        [JsonPropertyName("order_delivered_date")]
        public string DeliveryDate { get; set; }

        [JsonPropertyName("order_shipping_address")]
        public ShippingAddress[] ShippingAddresses { get; set; }
        [JsonPropertyName("order_items")]
        public OrderItem[] Items { get; set; }
        [JsonPropertyName("order_total")]
        public string OrderTotal { get; set; }
        [JsonPropertyName("order_subtotal")]
        public string SubTotal { get; set; }
        [JsonPropertyName("order_kfc")]
        public string KFC { get; set; }
        [JsonPropertyName("order_roundoff")]
        public double? RoundOff { get; set; }
        [JsonPropertyName("order_shipping_charge")]
        public string ShippingCharge { get; set; }
        [JsonPropertyName("order_total_gst")]
        public string TotalGST { get; set; }
        [JsonPropertyName("order_total_cgst")]
        public double? TotalCGST { get; set; }
        [JsonPropertyName("order_total_sgst")]
        public double? TotalSGST { get; set; }
        [JsonPropertyName("order_wallet_amount")]
        public double? WalletAmount { get; set; }
        [JsonPropertyName("order_amount")]
        public string Amount { get; set; }
        [JsonPropertyName("order_discount")]
        public double? Discount { get; set; }
        [JsonPropertyName("payment_mode_val")]
        public int? PaymentModeVal { get; set; }
        [JsonPropertyName("payment_mode")]
        public string PaymentMode { get; set; }
        [JsonPropertyName("order_trackURL")]
        public string TrackUrl { get; set; }
        [JsonPropertyName("order_status")]
        public OrderStatus Status { get; set; }
        [JsonPropertyName("order_primary_key")]
        public int? PrimaryKey { get; set; }
        [JsonPropertyName("order_DeliveryDriver")]
        public string DeliDriver { get; set; }
        [JsonPropertyName("order_DeliveryDriverNumber")]
        public string DeliDriverNo { get; set; }
        [JsonPropertyName("style")]
        public Style[] Styles { get; set; }
        [JsonPropertyName("order_customer_cancel_till")]
        public string CancelTime { get; set; }
        [JsonPropertyName("order_time_now")]
        public string CurAPITime { get; set; }
        [JsonPropertyName("order_can_cancel")]
        public bool? CanCancel { get; set; }
        [JsonPropertyName("order_deliveryslot_time")]
        public string SlotTime { get; set; }
        [JsonPropertyName("order_deliveryslot_date")]
        public string SlotDate { get; set; }
        [JsonPropertyName("order_payment_status")]
        public string PaymentStatus { get; set; }
        [JsonPropertyName("isReturnAvailable")]
        public int IsReturnAvailable { get; set; }
        [JsonPropertyName("returnCanStart")]
        public int ReturnCanStart { get; set; }

        [JsonPropertyName("delivery_type")]
        public string DeliveryType { get; set; }

    }
}
