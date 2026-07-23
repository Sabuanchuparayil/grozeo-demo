using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Home
{
    public class HomeData
    {
        [JsonPropertyName("home")]
        public List<HomeDetails> Home { get; set; } = new List<HomeDetails>();

        [JsonPropertyName("notification")]
        public string Notification { get; set; }

        [JsonPropertyName("MinVersion")]
        public HomeMinVersion HomeMinVersion { get; set; }

       
    }
}
