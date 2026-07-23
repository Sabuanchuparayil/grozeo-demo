using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Edge;
using OpenQA.Selenium.Firefox;
using System.Configuration;
using WebDriverManager.DriverConfigs.Impl;

namespace Retaline.Web.Selenium.Tests.Utilities
{
    internal class BaseTestManager
    {
        public ThreadLocal<IWebDriver> driver = new();

        [SetUp]

        public void StartBrowser()
        {
            string browserName = ConfigurationManager.AppSettings["browser"];
            browserName ??= "";
            InitBrowser(browserName);

            driver.Value.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(5);

            driver.Value.Manage().Window.Maximize();
            driver.Value.Url =ConfigurationManager.AppSettings["testUrl"];


        }

        public IWebDriver getDriver()
        {
            return driver.Value;
        }

        public void InitBrowser(string browserName)
        {

            switch (browserName)
            {

                case "Firefox":

                    new WebDriverManager.DriverManager().SetUpDriver(new FirefoxConfig());
                    driver.Value = new FirefoxDriver();
                    break;



                case "Chrome":

                    new WebDriverManager.DriverManager().SetUpDriver(new ChromeConfig());
                    driver.Value = new ChromeDriver();
                    break;


                case "Edge":

                    driver.Value = new EdgeDriver();
                    break;

            }
        }


        [TearDown]
        public void AfterTest()
        {
            driver.Value.Quit();
        }

    }
}
