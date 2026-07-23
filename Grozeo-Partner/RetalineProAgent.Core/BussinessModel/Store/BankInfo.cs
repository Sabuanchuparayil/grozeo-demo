using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class BankInfo
    {
        [JsonPropertyName("NEFT")] 
        public bool NEFT { get; set; }
        [JsonPropertyName("UPI")]
        public bool UPI { get; set; }
        [JsonPropertyName("SWIFT")]
        public string SWIFT { get; set; }
        [JsonPropertyName("ADDRESS")]
        public string Address { get; set; }
        [JsonPropertyName("BANKCODE")]
        public string BankCode { get; set; }
        [JsonPropertyName("BANK")]
        public string Bank { get; set; }
        [JsonPropertyName("IFSC")]
        public string IFSC { get; set; }
        [JsonPropertyName("MICR")]
        public object MICR { get; set; }
        [JsonPropertyName("BRANCH")]
        public string Branch { get; set; }
        [JsonPropertyName("CITY")]
        public string City { get; set; }
        [JsonPropertyName("CENTRE")]
        public string Centre { get; set; }
        [JsonPropertyName("DISTRICT")]
        public string District { get; set; }
        [JsonPropertyName("STATE")]
        public string State { get; set; }
        [JsonPropertyName("STDCODE")]
        public object StdCode { get; set; }
        [JsonPropertyName("CONTACT")]
        public object Contact { get; set; }

    }
}
