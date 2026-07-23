using System;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Inventory
{
    [Serializable]
    public class ItemMaster
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("sku")]
        public string SKU { get; set; }
        [JsonPropertyName("erpid")]
        public string ErpId { get; set; }

    }
}
