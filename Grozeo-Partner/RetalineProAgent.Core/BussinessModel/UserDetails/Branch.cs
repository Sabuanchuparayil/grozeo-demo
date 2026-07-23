using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace ODOCart.Core.BussinessModel.UserDetails
{
    public class Branch
    {
        [JsonPropertyName("br_id")]
        public int Id { get; set; }
        [JsonPropertyName("br_name")]
        public string Name { get; set; }
    }
}
