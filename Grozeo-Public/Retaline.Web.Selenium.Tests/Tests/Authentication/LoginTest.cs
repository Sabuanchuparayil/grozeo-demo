using OpenQA.Selenium;
using OpenQA.Selenium.Support.UI;
using Retaline.Web.Selenium.Tests.DataModels;
using Retaline.Web.Selenium.Tests.PageElements;
using Retaline.Web.Selenium.Tests.Utilities;
using SeleniumExtras.WaitHelpers;

namespace Retaline.Web.Selenium.Tests.Tests.Authentication
{
    internal class LoginTest : BaseTestManager
    {
        //[Test]
        public void Login()
        {
            HeaderSectionElements headerPageElements = new(driver.Value);
            LoginSectionElements loginSectionElements = new(driver.Value);
            HomePageElements homePageElements = new(driver.Value);
            LoginDataModel testData = LoginDataModel.GetLoginTestData();

            headerPageElements.ProfileClick();
            loginSectionElements.GenerateOtp(testData.Mobile);
            loginSectionElements.ValidateOtp(testData);

            var element = homePageElements.GetBusinessTypeDropdown();
            Assert.IsTrue(element!=null);
        }

    }
}
