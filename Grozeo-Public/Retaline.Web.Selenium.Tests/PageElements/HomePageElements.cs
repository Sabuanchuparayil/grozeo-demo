using OpenQA.Selenium;
using OpenQA.Selenium.Support.UI;
using SeleniumExtras.PageObjects;
using SeleniumExtras.WaitHelpers;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Web.Selenium.Tests.PageElements
{
    internal class HomePageElements
    {
        private IWebDriver _driver;

        public HomePageElements(IWebDriver driver)
        {
            _driver=driver;
        }

        [FindsBy(How = How.Id, Using = "drp-business-type")]
        public IWebElement BusinesTypeDropDown { get; set; }



        public IWebElement GetBusinessTypeDropdown()
        {
            var wait = new WebDriverWait(_driver, new TimeSpan(0, 0, 10));
            var element = wait.Until(ExpectedConditions.ElementIsVisible(By.Id("drp-business-type")));
            return element;
        }
    }
}
