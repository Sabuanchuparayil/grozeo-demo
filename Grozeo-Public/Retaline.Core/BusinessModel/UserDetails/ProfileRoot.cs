using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class ProfileRoot
    {

        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public User Data { get; set; }

        public ProfileRoot()
        {
            Data = new User();
        }
    }
}
