using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Home
{
    public class HomeRoot
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public HomeData Data { get; set; }
    }
}
