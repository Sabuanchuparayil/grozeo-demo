using OpenQA.Selenium;
using OpenQA.Selenium.Support.UI;
using Retaline.Web.Selenium.Tests.DataModels;
using SeleniumExtras.PageObjects;

namespace Retaline.Web.Selenium.Tests.PageElements
{
    internal class LoginSectionElements
    {
        private readonly IWebDriver _driver;

        public LoginSectionElements(IWebDriver driver)
        {
            _driver = driver;
            PageFactory.InitElements(driver, this);
        }

        [FindsBy(How = How.Id, Using = "txt-mobile")]
        public IWebElement Mobile { get; set; }

        [FindsBy(How = How.Id, Using = "btn-generate-otp")]
        public IWebElement GenerateOtpButton { get; set; }


        [FindsBy(How = How.Id, Using = "txt-otp-first")]
        public IWebElement FirstOtpTextBox { get; set; }


        [FindsBy(How = How.Id, Using = "txt-otp-second")]
        public IWebElement SecondOtpTextBox { get; set; }


        [FindsBy(How = How.Id, Using = "txt-otp-third")]
        public IWebElement ThirdOtpTextBox { get; set; }


        [FindsBy(How = How.Id, Using = "txt-otp-fourth")]
        public IWebElement FourthOtpTextBox { get; set; }

        [FindsBy(How = How.Id, Using = "btn-verify-otp")]
        public IWebElement VerifyOtpButton { get; set; }


        public void GenerateOtp(string mobile)
        {
            Mobile.SendKeys(mobile);
            GenerateOtpButton.Click();
        }

        public void ValidateOtp(LoginDataModel testData)
        {
            FirstOtpTextBox.SendKeys(testData.FirstOtpValue);
            SecondOtpTextBox.SendKeys(testData.SecondOtpValue);
            ThirdOtpTextBox.SendKeys(testData.ThirdOtpValue);
            FourthOtpTextBox.SendKeys(testData.FourthOtpValue);
            VerifyOtpButton.Click();
        }

        public void WaitToLoadHomePage()
        {
            WebDriverWait wait = new WebDriverWait(_driver, TimeSpan.FromSeconds(8));
            wait.Until(SeleniumExtras.WaitHelpers.ExpectedConditions.ElementIsVisible(By.Id("drp-business-type")));
        }

    }
}
