using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class Branch
    {
        [JsonPropertyName("br_id")]
        public int Id { get; set; }
        [JsonPropertyName("br_name")]
        public string Name { get; set; }

        [JsonPropertyName("br_address")]
        public string Address { get; set; }
        [JsonPropertyName("br_phone")]
        public string Phone { get; set; }

    }
}
