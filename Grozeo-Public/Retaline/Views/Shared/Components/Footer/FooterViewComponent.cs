using Microsoft.AspNetCore.Mvc;
using Retaline.Core.ViewModel.Home;

namespace Retaline.Web.Views.Shared.Components.Footer
{
    public class FooterViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()//(HomeDetailsViewModel details)
        {
            return View("Footer");
        }
    }
}
