using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class WalletInfo
    {
        [JsonPropertyName("order_id")]
        public string OrderNum { get; set; }

        [JsonPropertyName("id")]
        public int OrderId { get; set; }
        [JsonPropertyName("amount_added")]
        public double AmountAdded { get; set; }
        [JsonPropertyName("order_date")]
        public string OrderDate { get; set; }
        [JsonPropertyName("reason")]
        public string Reason { get; set; }
        [JsonPropertyName("closing_balance")]
        public double ClosingBalance { get; set; }
        [JsonPropertyName("opening_balance")]
        public double OpeningBalance { get; set; }
        [JsonPropertyName("branch_name")]
        public string BranchName { get; set; }
    }
}
