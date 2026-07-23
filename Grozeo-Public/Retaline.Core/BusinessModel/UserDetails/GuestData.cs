using System;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
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
        public string FrontEndToken { get; set; }
        public string GuestLatitude { get; set; }
        public string GuestLongitude {  get; set; }
        public string GuestLocality {  get; set; }
        
        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
    }
}
