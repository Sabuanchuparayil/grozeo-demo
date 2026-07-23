using Retaline.Core.BusinessModel.Catalog;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Wishlist
{
    public class WishlistDetails
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }

        [JsonPropertyName("customer_id")]
        public int CustomerId { get; set; }

        [JsonPropertyName("group_id")]
        public int GroupId { get; set; }

        [JsonPropertyName("product_id")]
        public int ProductId { get; set; }

        [JsonPropertyName("branch_id")]
        public int BranchId { get; set; }

        [JsonPropertyName("order_method")]
        public int OrderMethod { get; set; }
        //public WishListItem ItemOld { get; set; } = new WishListItem();

        [JsonPropertyName("item")]
        public Product Item { get; set; }

        [JsonPropertyName("branch_type_id")]
        public int? BranchTypeId { get; set; }
        [JsonPropertyName("source")]
        public string Source { get; set; }
    }
}
