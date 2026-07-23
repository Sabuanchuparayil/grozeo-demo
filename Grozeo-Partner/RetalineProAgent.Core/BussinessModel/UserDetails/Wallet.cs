using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace ODOCart.Core.BussinessModel.UserDetails
{
    public class Wallet
    {
        [JsonPropertyName("wallet_balance")]
        public double Balance { get; set; }
        [JsonPropertyName("spent_details")]
        public List<WalletInfo> SpendDetails { get; set; }
        [JsonPropertyName("recieve_details")]
        public List<WalletInfo> RecieveDetails { get; set; }
    }
    public class WalletInfo
    {
        [JsonPropertyName("order_id")]
        public string OrderNum { get; set; }

        [JsonPropertyName("id")]
        public int OrderId { get; set; }
        [JsonPropertyName("amount_added")]
        public string AmountAdded { get; set; }
        [JsonPropertyName("order_date")]
        public string OrderDate { get; set; }
        [JsonPropertyName("reason")]
        public string Reason { get; set; }

    }

}
