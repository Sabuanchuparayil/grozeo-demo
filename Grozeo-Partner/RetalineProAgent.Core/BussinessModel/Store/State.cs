using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class State
    {
        [JsonPropertyName("st_ID")]
        public int Id { get; set; }
        [JsonPropertyName("st_name")]
        public string Name { get; set; }
    }
}
