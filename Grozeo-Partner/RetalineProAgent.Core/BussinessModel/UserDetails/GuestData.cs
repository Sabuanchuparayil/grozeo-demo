using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.UserDetails
{
    public class GuestData
    {
        [JsonPropertyName("brac_id")]
        public int Id { get; set; }
        [JsonPropertyName("brac_branch")]
        public int Branch { get; set; }
        [JsonPropertyName("brac_phone")]
        public string BranchPhone { get; set; }
        [JsonPropertyName("dummy")]
        public bool IsDummy { get; set; }
        [JsonPropertyName("token")]
        public string Token { get; set; }
    }
}
