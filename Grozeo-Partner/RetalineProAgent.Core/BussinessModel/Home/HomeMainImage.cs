using System.Text.Json.Serialization;

namespace ODOCart.Core.BussinessModel.Home
{
    public class MainImage
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }

        [JsonPropertyName("product_id")]
        public int ProductId { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("image_thumb_url")]
        public string ImageThumbUrl { get; set; }
    }
}
