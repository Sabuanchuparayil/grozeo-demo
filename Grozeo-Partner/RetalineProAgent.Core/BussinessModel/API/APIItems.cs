using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.API
{
    public class APIItems<T>
    {
        [JsonPropertyName("value")]
        public T Data { get; set; }
        [JsonPropertyName("min_count")]
        public int MinCount { get; set; }
        [JsonPropertyName("total_count")]
        public int Total { get; set; }
    }
}
