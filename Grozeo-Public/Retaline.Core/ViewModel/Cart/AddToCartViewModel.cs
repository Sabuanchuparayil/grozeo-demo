using System.Text.Json.Serialization;

namespace Retaline.Core.ViewModel.Cart
{
    public class AddToCartViewModel
    {
        [JsonPropertyName("cart_product_id")]
        public int CartProductId { get; set; }

        [JsonPropertyName("cart_order_qty")]
        public int CartOrderQty { get; set; }

        [JsonPropertyName("cart_group_id")]
        public int CartGroupId { get; set; }
        [JsonPropertyName("order_method")]
        public int OrderMethod { get; set; }
        [JsonPropertyName("type")]
        public int OrderType { get; set; }
        [JsonPropertyName("cart_branch_id")]
        public int? BranchId { get; set; }
        [JsonPropertyName("branch_type_id")]
        public int? BranchType { get; set; }
        [JsonPropertyName("cart_id")]
        public int? CartId { get; set; }
    }
}
