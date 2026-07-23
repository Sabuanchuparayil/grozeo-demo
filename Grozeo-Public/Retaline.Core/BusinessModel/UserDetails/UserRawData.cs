using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class UserRawData
    {
        [JsonPropertyName("is_verified")]
        public bool IsVerified { get; set; }
        [JsonPropertyName("is_registered")]
        public bool IsRegisterd { get; set; }
        public string email { get; set; }
        public string refCode { get; set; }

        [JsonPropertyName("user")]
        public User User { get; set; }
    }
}
