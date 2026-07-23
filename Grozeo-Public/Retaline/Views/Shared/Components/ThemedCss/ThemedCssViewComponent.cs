using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.ThemedCss
{
    public class ThemedCssViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("ThemedCss");
        }
    }
}
