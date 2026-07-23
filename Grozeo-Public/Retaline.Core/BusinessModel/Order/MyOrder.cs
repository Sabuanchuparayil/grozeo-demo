using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Order
{
    public class MyOrder
    {
        [JsonPropertyName("order_id")]
        public int OrderId { get; set; }
        [JsonPropertyName("order_order_id")]
        public string OrderKey { get; set; }
        [JsonPropertyName("status_id")]
        public int StatusId { get; set; }
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("created_at")]
        public string CreatedOn { get; set; }
        [JsonPropertyName("order_status_addinfo")]
        public string AddInfo { get; set; }
        [JsonPropertyName("order_trackURL")]
        public string TrackUrl { get; set; }

        [JsonPropertyName("order_delivered_date")]
        public string DeliveredDate { get; set; }
        [JsonPropertyName("order_total")]
        public double OrderTotal { get; set; }
        [JsonPropertyName("order_subtotal")]
        public double SubTotal { get; set; }
        [JsonPropertyName("order_delivery_charge")]
        public double DeliveryCharge { get; set; }
        [JsonPropertyName("bank_reference_id")]
        public string BankReferenceId { get; set; }
        [JsonPropertyName("payment_mode")]
        public int PaymentMode { get; set; }
        [JsonPropertyName("order_branch_id")]
        public int BranchId { get; set; }
        [JsonPropertyName("order_can_cancel")]
        public string CanCancel { get; set; }
        [JsonPropertyName("order_customer_cancel_till")]
        public string CancelTime { get; set; }
        [JsonPropertyName("order_time_now")]
        public string CurAPITime { get; set; }
        [JsonPropertyName("order_history")]
        public OrderHistory[] OrderHistories { get; set; }

        [JsonPropertyName("branch_name")]
        public string BranchName { get; set; }
        [JsonPropertyName("branch_location")]
        public string BranchLocation { get; set; }
        [JsonPropertyName("branch_phone")]
        public string BranchPhone { get; set; }
        [JsonPropertyName("highest_priced_image")]
        public string TopImageUrl { get; set; }
        [JsonPropertyName("highest_priced_itemname")]
        public string TopItemName { get; set; }
        [JsonPropertyName("total_item_count")]
        public int ItemsCount { get; set; }
        [JsonPropertyName("style")]
        public Style[] Styles { get; set; }

        [JsonPropertyName("delivery_type")]
        public string DeliveryType { get; set; }

        [JsonPropertyName("order_notes")]
        public string OrderNotes { get; set; }
    }
}
