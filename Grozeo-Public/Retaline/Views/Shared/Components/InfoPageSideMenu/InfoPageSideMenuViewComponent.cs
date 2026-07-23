using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.InfoPageSideMenu
{
    public class InfoPageSideMenuViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(string blade = "")
        {
            return View("InfoPageSideMenu", blade);
        }
    }
}
    