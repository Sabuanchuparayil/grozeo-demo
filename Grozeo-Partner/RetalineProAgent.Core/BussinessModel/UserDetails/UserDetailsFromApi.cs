using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.UserDetails
{
    public class UserDetailsFromApi
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public UserRawData Data { get; set; }
    }
}
