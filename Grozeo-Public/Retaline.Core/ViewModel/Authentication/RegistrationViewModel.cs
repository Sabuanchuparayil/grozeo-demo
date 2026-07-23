using System.Text.Json.Serialization;

namespace Retaline.Core.ViewModel.Authentication
{
    public class RegistrationViewModel
    {
        [JsonPropertyName("deli_latitude")]
        public int DeliLatitude { get; set; }

        [JsonPropertyName("deli_longitude")]
        public int DeliLongitude { get; set; }

        [JsonPropertyName("email")]
        public string Email { get; set; }

        [JsonPropertyName("name")]
        public string Name { get; set; }

        [JsonPropertyName("mobile")]
        public string Mobile { get; set; }
        [JsonPropertyName("password")]
        public string Password { get; set; }
        public string refCode { get; set; }
        
    }
}
