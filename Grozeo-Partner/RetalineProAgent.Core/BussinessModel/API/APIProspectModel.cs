using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.API
{
    public class APIProspectModel
    {
        [JsonPropertyName("result")]
        public int Result { get; set; }
        [JsonPropertyName("status")]
        public string Status { get; set; }
        [JsonPropertyName("message")]
        public string Message { get; set; }
        [JsonPropertyName("url")]
        public string URL { get; set; }
        [JsonPropertyName("error")]
        public Error Failure { get; set; }
        public class Error
        {
            [JsonPropertyName("msg")]
            public string msg { get; set; }
        }
    }
}
