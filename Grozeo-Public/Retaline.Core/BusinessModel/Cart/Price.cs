using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class Price
    {
        [JsonPropertyName("total_gst")]
        public string TotalGst { get; set; }

        [JsonPropertyName("total_cgst")]
        public string TotalCgst { get; set; }

        [JsonPropertyName("total_sgst")]
        public string TotalSgst { get; set; }

        [JsonPropertyName("total_kfc")]
        public string TotalKfc { get; set; }

        [JsonPropertyName("basket_price")]
        public string BasketPrice { get; set; }

        [JsonPropertyName("delivery_charge")]
        public string DeliveryCharge { get; set; }

        [JsonPropertyName("total")]
        public string Total { get; set; }

        [JsonPropertyName("total_selling")]
        public string TotalSelling { get; set; }

        [JsonPropertyName("total_mrp")]
        public string TotalMrp { get; set; }

        [JsonPropertyName("total_saved")]
        public string TotalSaved { get; set; }

        [JsonPropertyName("deliverychargesslabmax")]
        public string Deliverychargesslabmax { get; set; }

        [JsonPropertyName("deliverychargescurrenttotal")]
        public string Deliverychargescurrenttotal { get; set; }
    }
}
