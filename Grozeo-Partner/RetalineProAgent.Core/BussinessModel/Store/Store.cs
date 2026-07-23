using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    [Serializable]
    public class Store
    {
        [JsonPropertyName("br_ID")]
        public int BranchId { get; set; }
        [JsonPropertyName("br_Name")]
        public string BranchName { get; set; }
        [JsonPropertyName("br_ReferenceID")]
        public string APIKey { get; set; }

        [JsonPropertyName("br_City")]
        public string City { get; set; }
        [JsonPropertyName("br_District")]
        public int District { get; set; }
        [JsonPropertyName("br_State")]
        public int State { get; set; }
        [JsonPropertyName("br_Address")]
        public string Address { get; set; }
        [JsonPropertyName("br_Fax")]
        public string Fax { get; set; }
        [JsonPropertyName("br_Email")]
        public string Email { get; set; }
        [JsonPropertyName("br_Phone")]
        public string Phone { get; set; }
        [JsonPropertyName("br_Incharge")]
        public string Incharge { get; set; }
        [JsonPropertyName("br_SalesOnline")]
        public int Status { get; set; }
        [JsonPropertyName("branch_shortname")]
        public string ShortName { get; set; }
        [JsonPropertyName("br_key")]
        public string BrKey { get; set; }
        [JsonPropertyName("br_IsUnEditable")]
        public int IsUnEditable { get; set; }
        [JsonPropertyName("br_Lat")]
        public string Lat { get; set; }
        [JsonPropertyName("br_Lng")]
        public string Lng { get; set; }
        [JsonPropertyName("br_pincode")]
        public string Pin { get; set; }
        [JsonPropertyName("br_stockLevel")]
        public int StockLevel { get; set; }
        [JsonPropertyName("br_cpd")]
        public int CPD { get; set; }
        [JsonPropertyName("br_IsCPD")]
        public int IsCPD { get; set; }
        [JsonPropertyName("br_storeGroup")]
        public int StoreGroup { get; set; }
        [JsonPropertyName("br_isdefaultstore")]
        public int IsDefault { get; set; }
        [JsonPropertyName("br_byagent")]
        public int ByAgent { get; set; }
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("ref")]
        public string Key{get; set;}
        [JsonPropertyName("on_off_time")]
        public StoreTime[] OnOffTime { get; set; }
        [JsonPropertyName("br_directDelivery")]
        public int DirectDelivery { get; set; }
        [JsonPropertyName("br_courierDelivery")]
        public int CourierDelivery { get; set; }
        public int DBBranchid { get; set; }
        public int? GSTId { get; set; }
        public int? BankId { get; set; }
        public string GSTIN { get; set; }
        public string Bank { get; set; }
        public int? FSSAI_Id { get; set; }
        public string FSSAI { get; set; }
    }

    [Serializable]
    public class StoreTime {
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("branch_id")]
        public int StoreId { get; set; }
        [JsonPropertyName("br_open_time")]
        public string OnTime { get; set; }
        [JsonPropertyName("br_close_time")]
        public string OffTime { get; set; }
    }

}
