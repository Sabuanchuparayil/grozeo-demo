using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class Delivery
    {
        [JsonPropertyName("pincode")]
        public int? Pincode { get; set; }
        [JsonPropertyName("isActive")]
        public int? IsActive { get; set; }

    }
}
