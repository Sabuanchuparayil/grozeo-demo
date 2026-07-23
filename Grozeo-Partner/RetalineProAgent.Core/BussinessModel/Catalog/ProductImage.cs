using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class ProductImage
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }

        [JsonPropertyName("product_id")]
        public int ProductId { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("image_thumb_url")]
        public string ThumbUrl { get; set; }

    }
}
