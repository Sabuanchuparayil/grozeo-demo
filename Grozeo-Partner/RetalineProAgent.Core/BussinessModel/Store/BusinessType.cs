using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class BusinessType
    {
        [JsonPropertyName("business_type_id")]
        public int TypeId { get; set; }
        [JsonPropertyName("business_type_name")]
        public string TypeName { get; set; }
    }
}
