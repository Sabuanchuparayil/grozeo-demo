using Retaline.Core.Infra;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.UserDetails
{
    public class User : BaseEntity
    {
        [JsonPropertyName("cust_id")]
        public int Id { get; set; }
        [JsonPropertyName("cust_branch_id")]
        public int BranchId { get; set; }
        [JsonPropertyName("cust_customer_id")]
        public int CustomerId { get; set; }
        [JsonPropertyName("cust_mobile")]
        public string Mobile { get; set; }
        [JsonPropertyName("cust_email")]
        public string Email { get; set; }
        [JsonPropertyName("cust_customer_name")]
        public string Name { get; set; }
        [JsonPropertyName("cust_ref_code")]
        public string RefferenceCode { get; set; }
        [JsonPropertyName("cust_prom_reward_point")]
        public int PromotionalRewardPoint { get; set; }
        [JsonPropertyName("cust_status")]
        public string Status { get; set; }
        [JsonPropertyName("cust_avatar")]
        public object Avatar { get; set; }
        [JsonPropertyName("cust_walletbalance")]
        public double? WalletBalance { get; set; }
        [JsonPropertyName("token")]
        public string Token { get; set; }
        [JsonPropertyName("delivery_info")]
        public Address PrimaryAddress { get; set; }
        [JsonIgnore]
        public int AddressCount { get; set; }
        [JsonPropertyName("defaultRole")]
        public string DefaultRole { get; set; }
        [JsonPropertyName("email_verified")]
        public int EmailVerified { get; set; }
        [JsonPropertyName("phone_verified")]
        public int PhoneVerified { get; set; }
        [JsonPropertyName("age_verified")]
        public int AgeVerified { get; set; }
        
        public string ImpersonatedOriginalUserData { get; set; }
    }
}
