using System.Text.Json.Serialization;

namespace RetalineProAgent.Core.BussinessModel.API
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
            public string msg { get; set; }
        }
    }
}
