using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.UserDetails
{
    public class UserRawData
    {
        [JsonPropertyName("is_verified")]
        public bool IsVerified { get; set; }
        [JsonPropertyName("is_registered")]
        public bool IsRegisterd { get; set; }

        [JsonPropertyName("user")]
        public AppUser AppUser { get; set; }
    }
}
