using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.Copyright
{
    public class NavigateBackButtonViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke( )
        {
            return View("");
        }
    }
}
