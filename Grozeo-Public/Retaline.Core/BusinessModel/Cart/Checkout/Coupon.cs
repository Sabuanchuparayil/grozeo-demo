using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class Coupon
    {
        //[JsonPropertyName("net_amount_payable")]
        //public Double? NetAmount { get; set; }
        [JsonPropertyName("labels")]
        public List<CouponLabel> Labels { get; set; }
        
    }

    
}
