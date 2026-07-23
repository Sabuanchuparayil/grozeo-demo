using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class Address
    {
        [JsonPropertyName("deli_id")]
        public int Id { get; set; }
        [JsonPropertyName("deli_customer_id")]
        public int CustomerId { get; set; }
        [JsonPropertyName("deli_delivery_pin")]
        public object PINCode { get; set; }
        [JsonPropertyName("deli_house_no")]
        public string HouseNumber { get; set; }
        [JsonPropertyName("deli_house_name")]
        public string HouseName { get; set; }
        [JsonPropertyName("deli_address")]
        public string DeliveryAddress { get; set; }
        [JsonPropertyName("deli_google_address")]
        public string GoogleAddress { get; set; }
        [JsonPropertyName("deli_google_pin")]
        public string GooglePin { get; set; }
        [JsonPropertyName("deli_land_mark")]
        public string Landmark { get; set; }
        [JsonPropertyName("deli_post")]
        public string Post { get; set; }
        [JsonPropertyName("deli_city")]
        public string City { get; set; }
        [JsonPropertyName("deli_state")]
        public string State { get; set; }
        [JsonPropertyName("deli_status")]
        public string Status { get; set; }
        [JsonPropertyName("deli_name")]
        public string Name { get; set; }
        [JsonPropertyName("deli_contact_no")]
        public string ContactNumber { get; set; }
        [JsonPropertyName("deli_is_primary")]
        public int IsPrimary { get; set; }
        [JsonPropertyName("deli_latitude")]
        public double Latitude { get; set; }
        [JsonPropertyName("deli_longitude")]
        public double Longitude { get; set; }
        [JsonPropertyName("deli_type")]
        public string Type { get; set; }
        [JsonPropertyName("deli_DistFromBr")]
        public double DistanceFromBranch { get; set; }
        [JsonPropertyName("deli_br_id")]
        public int BrId { get; set; }
        [JsonPropertyName("deli_district")]
        public string District { get; set; }
        [JsonPropertyName("delivery")]
        public Delivery DeliveryInfo { get; set; }
        [JsonPropertyName("deli_branch_id")]
        public int? BranchId { get; set; }
        [JsonPropertyName("deli_branch_name")]
        public string BranchName { get; set; }
        [JsonPropertyName("deli_branch_address")]
        public string BranchAddr { get; set; }
    }
}
