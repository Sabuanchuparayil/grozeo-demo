using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class GuestRoot
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public GuestData Data { get; set; }
    }
}
