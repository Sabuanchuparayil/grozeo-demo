using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class Store
    {
        [JsonPropertyName("br_ID")]
        public int Id { get; set; }
        [JsonPropertyName("br_Name")]
        public string Name { get; set; }

        [JsonPropertyName("br_Address")]
        public string Address { get; set; }
        [JsonPropertyName("br_Phone")]
        public string Phone { get; set; }
        [JsonPropertyName("br_City")]
        public string City { get; set; }
        [JsonPropertyName("br_District")]
        public int? DistrictId { get; set; }
        [JsonPropertyName("br_State")]
        public int? StateId { get; set; }
        [JsonPropertyName("br_Fax")]
        public string Fax { get; set; }
        [JsonPropertyName("br_Email")]
        public string Email { get; set; }
        [JsonPropertyName("br_Incharge")]
        public string InCharge { get; set; }
        [JsonPropertyName("br_status")]
        public string Status { get; set; }
        [JsonPropertyName("branch_shortname")]
        public string ShortName { get; set; }
        [JsonPropertyName("br_key")]
        public string Key { get; set; }
        [JsonPropertyName("br_ReferenceID")]
        public string Ref { get; set; }
        [JsonPropertyName("br_Lat")]
        public string Lat { get; set; }
        [JsonPropertyName("br_Lng")]
        public string Lng { get; set; }
        [JsonPropertyName("br_pincode")]
        public string Pin { get; set; }
        [JsonPropertyName("br_stockLevel")]
        public int? StockLevel { get; set; }
        [JsonPropertyName("br_cpd")]
        public int CPD { get; set; }
        [JsonPropertyName("br_PyramidLevel")]
        public int? PyramidLevel1 { get; set; }
        [JsonPropertyName("br_StoreType")]
        public string StoreType { get; set; }
        [JsonPropertyName("br_RetailType")]
        public string RetailerType { get; set; }
        [JsonPropertyName("br_csdefault")]
        public int CSDefault { get; set; }
        [JsonPropertyName("br_storeGroup")]
        public int StoreGroup { get; set; }
        [JsonPropertyName("br_isdefaultstore")]
        public int IsDefaultStore { get; set; }
        [JsonPropertyName("br_deliveryMode")]
        public int DeliveryMode { get; set; }
        [JsonPropertyName("br_rdrIdExpress")]
        public int IsExpress { get; set; }
        [JsonPropertyName("br_rdrIdSlotted")]
        public int IsSlotted { get; set; }
        [JsonPropertyName("distance")]
        public double? Distance { get; set; }
        [JsonPropertyName("logo")]
        public string Logo { get; set; }
        [JsonPropertyName("banner1")]
        public string Banner1 { get; set; }
        [JsonPropertyName("banner2")]
        public string Banner2 { get; set;}

    }
}
