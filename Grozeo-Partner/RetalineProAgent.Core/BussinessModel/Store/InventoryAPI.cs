using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class InventoryAPI
    {
        [JsonPropertyName("Qty")]
        public double Qty { get; set; }
        [JsonPropertyName("MRP")]
        public double MRP { get; set; }
        [JsonPropertyName("selling_price")]
        public double SellingPrice { get; set; }
        [JsonPropertyName("erpId")]
        public string ErpId { get; set; }
    }
}