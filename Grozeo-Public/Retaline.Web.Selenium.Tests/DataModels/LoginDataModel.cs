using Newtonsoft.Json.Linq;
using System.Text.Json.Serialization;

namespace Retaline.Web.Selenium.Tests.DataModels
{
    internal class LoginDataModel
    {
        [JsonPropertyName("mobile")]
        public string Mobile { get; set; } = "";
        [JsonPropertyName("firstOtpValue")]
        public string FirstOtpValue { get; set; } = "";
        [JsonPropertyName("secondOtpValue")]
        public string SecondOtpValue { get; set; } = "";
        [JsonPropertyName("thirdOtpValue")]
        public string ThirdOtpValue { get; set; } = "";
        [JsonPropertyName("fourthOtpValue")]
        public string FourthOtpValue { get; set; } = "";

        public static LoginDataModel GetLoginTestData()
        {
            var json = File.ReadAllText("TestData/testData.json");
            var jsonObject = JObject.Parse(json);
            LoginDataModel testData = jsonObject["login"]?.ToObject<LoginDataModel>();
            return testData;
        }
    }
}
