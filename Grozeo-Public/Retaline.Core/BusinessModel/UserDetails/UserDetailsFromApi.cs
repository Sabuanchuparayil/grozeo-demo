using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class UserDetailsFromApi
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public UserRawData Data { get; set; }
        public APIError error { get; set; }
    }

    public class APIError
    {
        public string msg { get; set; }
    }
}
