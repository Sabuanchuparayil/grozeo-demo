using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class Wallet
    {
        [JsonPropertyName("wallet_balance")]
        public double Balance { get; set; }
        [JsonPropertyName("spent_details")]
        public List<WalletInfo> SpendDetails { get; set; } = new List<WalletInfo>();
        [JsonPropertyName("recieve_details")]
        public List<WalletInfo> RecieveDetails { get; set; } = new List<WalletInfo>();
        [JsonPropertyName("wallet_details")]
        public List<WalletInfo> WalletDetails { get; set; } = new List<WalletInfo>();
        public string FromDate { get; set; }
        public string ToDate { get; set; }

    }


}
