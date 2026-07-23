using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Cart
{
    public class CartRoot
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("data")]
        public CartData Data { get; set; } = new CartData();
    }
}
