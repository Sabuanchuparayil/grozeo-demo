using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.HeaderCart
{
    public class HeaderCartViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View();
        }
    }
}
