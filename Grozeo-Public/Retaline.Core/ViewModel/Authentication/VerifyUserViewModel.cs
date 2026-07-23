using System.Text.Json.Serialization;

namespace Retaline.Core.ViewModel.Authentication
{
    public class VerifyUserViewModel
    {
        [JsonPropertyName("mobile")]
        public string Mobile { get; set; }
        [JsonPropertyName("otp")]
        public string Otp { get; set; }
        [JsonPropertyName("branch_group")]
        public int GroupId { get; set; }
        [JsonPropertyName("email")]
        public string Email { get; set; }
        public int usePsw { get; set; }
        public string psw { get; set; }
        public string refCode { get; set; }
    }
}
