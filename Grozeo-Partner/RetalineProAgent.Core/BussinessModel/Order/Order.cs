using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Order
{
    public class Order
    {               
            [JsonPropertyName("order_id")]
            public int? orderid { get; set; }
            [JsonPropertyName("order_order_id")]
            public string ordergroupid { get; set; }
            [JsonPropertyName("order_group_id")]
            public int? paymentmode { get; set; }
            [JsonPropertyName("payment_mode")]
            public int? statusid { get; set; }
            [JsonPropertyName("status_id")]
            public string ordermethod { get; set; }
            [JsonPropertyName("order_method")]
            public string storegroup { get; set; }
            [JsonPropertyName("storegroup_id")]
            public string deliveryruleid { get; set; }
            [JsonPropertyName("delivery_rule_id")]
            public int? deliveryruletype { get; set; }
            [JsonPropertyName("delivery_rule_type")]
            public string orderconfirmedon { get; set; }
            [JsonPropertyName("order_confirmed_on")]
            public int? orderdeliveredon { get; set; }
            [JsonPropertyName("order_delivered_date")]
            public int? Total { get; set; }
          
    }
}
