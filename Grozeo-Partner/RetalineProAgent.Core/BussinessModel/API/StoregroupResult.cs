using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.API
{
    public class StoregroupResult
    {
        [JsonPropertyName("store_group_id")]
        public string StoreGroupId { get; set; }
        [JsonPropertyName("store_group_name")]
        public string StoreGroupName { get; set; }
        [JsonPropertyName("status")]
        public string Status { get; set; }

    }
}
