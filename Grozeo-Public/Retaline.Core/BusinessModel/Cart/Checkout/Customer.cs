using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class Customer
    {
        [JsonPropertyName("order_order_id")]
        public string OrderId { get; set; }
        [JsonPropertyName("order_customer_id")]
        public int CustomerId { get; set; }
        [JsonPropertyName("order_total_amount")]
        public double TotalAmount { get; set; }
        [JsonPropertyName("order_delivery_charge")]
        public double DeliveryCharge { get; set; }
        [JsonPropertyName("order_courier_charge")]
        public double CourierCharge { get; set; }
        [JsonPropertyName("order_total_gst")]
        public double TotalGST { get; set; }
        [JsonPropertyName("order_total_cgst")]
        public double TotalCGST { get; set; }
        [JsonPropertyName("order_total_sgst")]
        public double TotalSGST { get; set; }
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
        public double KFCAmount { get; set; }
        [JsonPropertyName("total")]
        public double Total { get; set; }
        [JsonPropertyName("order_roundoff")]
        public double RoundOff { get; set; }
        [JsonPropertyName("order_app_version")]
        public string AppVersion { get; set; }
        [JsonPropertyName("order_app_os")]
        public string AppOS { get; set; }
        [JsonPropertyName("order_id")]
        public int Id { get; set; }
        [JsonPropertyName("payment_gateway")]
        public string PaymentGateway { get; set; }
        [JsonPropertyName("availableslots")]
        public AvailableSlots[] Slots { get; set; }
    }
}
