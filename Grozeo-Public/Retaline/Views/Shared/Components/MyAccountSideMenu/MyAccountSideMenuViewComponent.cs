using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.MyAccountSideMenu
{
    public class MyAccountSideMenuViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(string blade = "")
        {
            return View("MyAccountSideMenu", blade);
        }
    }
}
