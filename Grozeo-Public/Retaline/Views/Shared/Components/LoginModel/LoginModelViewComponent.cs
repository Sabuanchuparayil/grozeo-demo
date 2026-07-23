using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.LoginModel
{
    public class LoginModelViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("LoginModel");
        }

    }
}
