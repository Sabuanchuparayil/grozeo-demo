using System;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Captcha
{
    public class CaptchaResponse
    {
        [JsonPropertyName("success")]
        public bool Success { get; set; }
        [JsonPropertyName("challenge_ts")]
        public DateTime TimeStamp { get; set; }
        [JsonPropertyName("hostname")]
        public string Host { get; set; }
        [JsonPropertyName("error-codes")]
        public List<string> ErrorCodes { get; set; }
    }
}
