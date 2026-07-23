using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class ShippingAddress
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("order_id")]
        public string OrderId { get; set; }
        [JsonPropertyName("customer_order_id")]
        public int CustomerOrderId { get; set; }
        [JsonPropertyName("order_customer_name")]
        public string CustomerName { get; set; }
        [JsonPropertyName("order_customer_email")]
        public string CustomerEmail { get; set; }
        [JsonPropertyName("order_customer_id")]
        public int CustomerId { get; set; }
        [JsonPropertyName("order_contact_no")]
        public string ContactNo { get; set; }
        [JsonPropertyName("order_house_no")]
        public string HouseNo { get; set; }
        [JsonPropertyName("order_house_name")]
        public string HouseName { get; set; }
        [JsonPropertyName("order_address")]
        public string Address { get; set; }
        [JsonPropertyName("order_land_mark")]
        public string LandMark { get; set; }
        [JsonPropertyName("order_city")]
        public string City { get; set; }
        [JsonPropertyName("order_post")]
        public string Post { get; set; }
        [JsonPropertyName("order_state")]
        public string State { get; set; }
        [JsonPropertyName("order_pin")]
        public object Pin { get; set; }
        [JsonPropertyName("order_country")]
        public string Country { get; set; }
        [JsonPropertyName("order_deli_note")]
        public string DeliNote { get; set; }
        [JsonPropertyName("order_is_free_deli")]
        public int? IsFreeDeli { get; set; }
        [JsonPropertyName("order_latitude")]
        public double Latitude { get; set; }
        [JsonPropertyName("order_longitude")]
        public double Longitude { get; set; }
        [JsonPropertyName("order_distfrombr")]
        public double DistFromBr { get; set; }
    }
}
