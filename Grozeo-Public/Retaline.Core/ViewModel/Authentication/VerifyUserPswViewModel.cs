using System.Text.Json.Serialization;
namespace Retaline.Core.ViewModel.Authentication
{
    public class VerifyUserPswViewModel
    {
        [JsonPropertyName("psw-mobile")]
        public string Mobile { get; set; }
        [JsonPropertyName("psw-email")]
        public string Email { get; set; }
        [JsonPropertyName("mobile-password")]
        public string Password { get; set; }
        [JsonPropertyName("psw-type")]
        public int type {  get; set; }
    }
}