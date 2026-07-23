using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class Image
    {
        [JsonPropertyName("id")]
        public int ID { get; set; }
        [JsonPropertyName("product_id")]
        public int ProductID { get; set; }
        [JsonPropertyName("image_url")]
        public string URL { get; set; }
        [JsonPropertyName("image_thumb_url")]
        public string ThumbUrl { get; set; }
        [JsonPropertyName("created_at")]
        public string CreatedOn { get; set; }
        [JsonPropertyName("updated_at")]
        public string UpdatedOn { get; set; }
        [JsonPropertyName("image_type")]
        public int Type { get; set; }
        [JsonPropertyName("bucket_name")]
        public string BucketName { get; set; }
        [JsonPropertyName("bucket_path")]
        public string BucketPath { get; set; }
    }
}
