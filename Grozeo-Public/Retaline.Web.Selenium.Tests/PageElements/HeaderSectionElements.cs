using OpenQA.Selenium;
using SeleniumExtras.PageObjects;

namespace Retaline.Web.Selenium.Tests.PageElements
{
    public class HeaderSectionElements
    {
        private IWebDriver _driver;
        public HeaderSectionElements(IWebDriver driver)
        {
            _driver=driver;
            PageFactory.InitElements(driver, this);
        }

        [FindsBy(How = How.ClassName, Using = "profilemwnu")]
        public IWebElement Profile { get; set; }


        public void ProfileClick()
        {
            Profile.Click();
        }
    }
}
