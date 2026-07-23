using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.API
{
    public class APIModel<T>
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("data")]
        public T Data { get; set; }
        [JsonPropertyName("error")]
        public Error Failure { get; set; }
        public class Error
        {
            [JsonPropertyName("msg")]
            public object msg { get; set; }
        }
    }
}
