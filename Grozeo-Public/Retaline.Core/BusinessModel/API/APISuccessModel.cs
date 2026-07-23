using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.API
{
    public class APISuccessModel
    {
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("msg")]
        public string Message { get; set; }
        [JsonPropertyName("error")]
        public Error Failure { get; set; }
    }

    public class Error
    {
        public string msg { get; set; }
    }
}
