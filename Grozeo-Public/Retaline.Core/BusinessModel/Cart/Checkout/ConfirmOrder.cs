using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class ConfirmOrder
    {
        [JsonPropertyName("order_status")]
        public bool OrderStatus { get; set; }
        [JsonPropertyName("payment_mode")]
        public string PaymentMode { get; set; }
        [JsonPropertyName("message")]
        public string Message { get; set; }
        [JsonPropertyName("payment_gateway")]
        public string PaymentGateway { get; set; }
        [JsonPropertyName("order_id")]
        public int OrderId { get; set; }
        [JsonPropertyName("order_order_id")]
        public string OrderNum { get; set; }
        [JsonPropertyName("order_group_id")]
        public string OrderGroupId { get; set; }
        [JsonPropertyName("details")]
        public PaymentInfo PaymentDetails { get; set; }
        [JsonPropertyName("key_id")]
        public string KeyId { get; set; }
    }

    public class PaymentInfo
    {
        [JsonPropertyName("id")]
        public string Id { get; set; }
        [JsonPropertyName("longurl")]
        public string LongUrl { get; set; }

        [JsonPropertyName("key")]
        public string KeyId { get; set; }
        [JsonPropertyName("amount")]
        public double? Amount { get; set; }
        [JsonPropertyName("name")]
        public string Name { get; set; }
        [JsonPropertyName("description")]
        public string Description { get; set; }
        [JsonPropertyName("image")]
        public string Image { get; set; }
        [JsonPropertyName("prefill")]
        public RazorPayPrefill Prefill { get; set; }
        [JsonPropertyName("notes")]
        public RazorPayNotes Notes { get; set; }
        [JsonPropertyName("theme")]
        public RazorPayTheme Theme { get; set; }
        [JsonPropertyName("order_id")]
        public string OrderId { get; set; }
        [JsonPropertyName("client_secret")]
        public string ClientSecret { get; set; }
        [JsonPropertyName("token")]
        public string Token { get; set; }
    }

    public class RazorPayPrefill
    {
        [JsonPropertyName("name")]
        public string Name { get; set; }
        [JsonPropertyName("email")]
        public string Email { get; set; }
        [JsonPropertyName("contact")]
        public string Contant { get; set; }
    }
    public class RazorPayNotes
    {
        [JsonPropertyName("address")]
        public string Address { get; set; }
        [JsonPropertyName("merchant_order_id")]
        public string MerchantOrderId { get; set; }
    }
    public class RazorPayTheme
    {
        [JsonPropertyName("color")]
        public string Color { get; set; }
    }

}
