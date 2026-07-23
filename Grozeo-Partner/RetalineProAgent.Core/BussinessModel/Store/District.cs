using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class District
    {
        [JsonPropertyName("dst_Id")]
        public int Id { get; set; }
        [JsonPropertyName("dst_Name")]
        public string Name { get; set; }

    }
}
