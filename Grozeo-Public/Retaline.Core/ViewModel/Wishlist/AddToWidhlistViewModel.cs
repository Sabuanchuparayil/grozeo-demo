using System.Text.Json.Serialization;

namespace Retaline.Core.ViewModel.Wishlist
{
    public class AddToWidhlistViewModel
    {
        [JsonPropertyName("product_id")]
        public int ProductId { get; set; }

        [JsonPropertyName("group_id")]
        public int GroupId { get; set; }
        [JsonPropertyName("branch_id")]
        public int? BranchId { get; set; }
        [JsonPropertyName("order_method")]
        public int? OrderMethod { get; set; }
        [JsonPropertyName("isInCart")]
        public bool? IsInCart { get; set; }
        [JsonPropertyName("branch_type_id")]
        public int? BranchTypeId { get; set; }
        public int? Type { get; set; }
        [JsonPropertyName("source")]
        public int? Source { get; set; }
    }
}
