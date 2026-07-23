using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Home
{
    public class HomeRoot
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public HomeData Data { get; set; }
    }
}
